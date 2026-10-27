<?php
declare(strict_types=1);

/**
 * SDM Central File API
 *
 * POST actions:
 *   action=upload   + file=<file> + app_id=<app>
 *   action=download + file_id=<32-character ID> + app_id=<app>
 *   action=delete   + file_id=<32-character ID> + app_id=<app>
 *
 * Authentication:
 *   Authorization: Bearer <APP_SECRET>
 *   X-SDM-App-ID: <APP_ID>
 *
 * Config:
 *   /home/sdm.bcp-sms.com/api-secrets.php
 */

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');

const MAX_FILE_BYTES = 10485760; // 10 MB

const ALLOWED_EXTENSIONS = [
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'
];

function respond(int $status, array $data): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function requestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function bearerToken(): string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($header === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization']
            ?? $headers['authorization']
            ?? '';
    }

    if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
        return trim($matches[1]);
    }

    return '';
}

function requestAppId(): string
{
    $headerId = trim(
        (string)($_SERVER['HTTP_X_SDM_APP_ID'] ?? '')
    );
    $postId = trim((string)($_POST['app_id'] ?? ''));

    // Reject conflicting identity values.
    if ($headerId !== '' && $postId !== '' &&
        !hash_equals($headerId, $postId)) {
        respond(400, ['error' => 'Conflicting application IDs.']);
    }

    return $headerId !== '' ? $headerId : $postId;
}

function cleanOriginalName(string $name): string
{
    $name = basename(str_replace('\\', '/', $name));
    $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';
    $name = trim($name);

    if ($name === '' || $name === '.' || $name === '..') {
        return 'document';
    }

    // Limit metadata filename length.
    return substr($name, 0, 180);
}

function detectMime(string $path): string
{
    if (!class_exists('finfo')) {
        respond(500, ['error' => 'File type validation is unavailable.']);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path);

    if (!is_string($mime) || $mime === '') {
        respond(415, ['error' => 'Could not determine the file type.']);
    }

    return strtolower($mime);
}

/**
 * Verify extension and basic file signatures.
 *
 * DOC and XLS use the legacy OLE compound-file signature.
 * DOCX and XLSX use ZIP signatures. When ZipArchive is available,
 * also check for the expected Office document entry.
 */
function validateFileContent(string $path, string $extension): string
{
    $mime = detectMime($path);

    $handle = fopen($path, 'rb');

    if ($handle === false) {
        respond(500, ['error' => 'Could not inspect the uploaded file.']);
    }

    $signature = fread($handle, 16);
    fclose($handle);

    if ($signature === false) {
        respond(415, ['error' => 'Could not inspect the uploaded file.']);
    }

    $isOle = str_starts_with(
        $signature,
        hex2bin('D0CF11E0A1B11AE1')
    );

    $isZip = str_starts_with($signature, "PK\x03\x04")
        || str_starts_with($signature, "PK\x05\x06")
        || str_starts_with($signature, "PK\x07\x08");

    $isJpeg = str_starts_with($signature, "\xFF\xD8\xFF");
    $isPng = str_starts_with(
        $signature,
        "\x89PNG\r\n\x1A\n"
    );

    switch ($extension) {
        case 'pdf':
            if ($mime !== 'application/pdf') {
                respond(415, ['error' => 'The file is not a PDF.']);
            }

            if (!str_starts_with($signature, '%PDF-')) {
                respond(415, ['error' => 'Invalid PDF signature.']);
            }
            break;

        case 'doc':
        case 'xls':
            $allowedMimes = [
                'application/msword',
                'application/vnd.ms-excel',
                'application/x-ole-storage',
                'application/x-cfb',
                'application/cdfv2',
                'application/octet-stream',
            ];

            if (!$isOle || !in_array($mime, $allowedMimes, true)) {
                respond(415, [
                    'error' => 'The file is not a recognized legacy Office document.'
                ]);
            }
            break;

        case 'docx':
        case 'xlsx':
            if (!$isZip ||
                !in_array($mime, [
                    'application/zip',
                    'application/x-zip',
                    'application/octet-stream',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ], true)) {
                respond(415, [
                    'error' => 'The file is not a recognized Office Open XML document.'
                ]);
            }

            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                $opened = $zip->open($path);

                if ($opened !== true) {
                    respond(415, ['error' => 'Invalid Office document archive.']);
                }

                $expectedEntry = $extension === 'docx'
                    ? 'word/document.xml'
                    : 'xl/workbook.xml';

                $valid = $zip->locateName($expectedEntry) !== false;
                $zip->close();

                if (!$valid) {
                    respond(415, [
                        'error' => 'The file does not contain the expected Office document.'
                    ]);
                }
            }
            break;

        case 'jpg':
        case 'jpeg':
            if (!$isJpeg || $mime !== 'image/jpeg') {
                respond(415, ['error' => 'The file is not a valid JPEG image.']);
            }
            break;

        case 'png':
            if (!$isPng || $mime !== 'image/png') {
                respond(415, ['error' => 'The file is not a valid PNG image.']);
            }
            break;

        default:
            respond(415, ['error' => 'This file extension is not supported.']);
    }

    return $mime;
}

// Only POST requests are supported.
if (requestMethod() !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Use POST for API requests.']);
}

// Load the private configuration.
$configFile = '/home/sdm.bcp-sms.com/api-secrets.php';

if (!is_file($configFile)) {
    respond(500, ['error' => 'API configuration unavailable.']);
}

$config = require $configFile;

if (
    !is_array($config) ||
    !is_string($config['storage_path'] ?? null) ||
    !is_array($config['apps'] ?? null)
) {
    respond(500, ['error' => 'API configuration is invalid.']);
}

// Authenticate a specific registered application.
$appId = requestAppId();
$providedSecret = bearerToken();

if (
    !preg_match('/^[a-z0-9_-]{2,50}$/', $appId) ||
    $providedSecret === '' ||
    !isset($config['apps'][$appId]) ||
    !is_array($config['apps'][$appId]) ||
    ($config['apps'][$appId]['enabled'] ?? false) !== true ||
    !is_string($config['apps'][$appId]['secret'] ?? null) ||
    $config['apps'][$appId]['secret'] === '' ||
    !hash_equals(
        $config['apps'][$appId]['secret'],
        $providedSecret
    )
) {
    respond(401, ['error' => 'Unauthorized.']);
}

$storageRoot = rtrim(
    str_replace('\\', '/', $config['storage_path']),
    '/'
);

if ($storageRoot === '') {
    respond(500, ['error' => 'Storage path is invalid.']);
}

$filesDir = $storageRoot . '/api-files';
$metadataDir = $storageRoot . '/.api-metadata';

foreach ([$filesDir, $metadataDir] as $directory) {
    if (
        !is_dir($directory) &&
        !mkdir($directory, 0750, true) &&
        !is_dir($directory)
    ) {
        respond(500, ['error' => 'Private storage is unavailable.']);
    }

    if (!is_writable($directory)) {
        respond(500, ['error' => 'Private storage is not writable.']);
    }
}

$action = (string)($_POST['action'] ?? '');

if ($action === 'upload') {
    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        respond(400, ['error' => 'A file is required.']);
    }

    $upload = $_FILES['file'];

    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        respond(400, ['error' => 'Upload failed. Check the file and size.']);
    }

    $tmp = (string)($upload['tmp_name'] ?? '');
    $size = (int)($upload['size'] ?? 0);
    $originalName = cleanOriginalName(
        (string)($upload['name'] ?? '')
    );

    if (!is_uploaded_file($tmp)) {
        respond(400, ['error' => 'Invalid uploaded file.']);
    }

    if ($size < 1 || $size > MAX_FILE_BYTES) {
        respond(413, ['error' => 'Files must not exceed 10 MB.']);
    }

    // Re-check the actual temporary file size.
    $actualSize = filesize($tmp);

    if ($actualSize === false || $actualSize !== $size) {
        respond(400, ['error' => 'Uploaded file size is inconsistent.']);
    }

    $extension = strtolower(
        pathinfo($originalName, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
        respond(415, ['error' => 'This file extension is not supported.']);
    }

    $mime = validateFileContent($tmp, $extension);

    try {
        $fileId = bin2hex(random_bytes(16));
    } catch (Throwable $e) {
        respond(500, ['error' => 'Could not generate a file identifier.']);
    }

    $filePath = $filesDir . '/' . $fileId . '.' . $extension;
    $metadataPath = $metadataDir . '/' . $fileId . '.json';

    if (!move_uploaded_file($tmp, $filePath)) {
        respond(500, ['error' => 'Could not save the file.']);
    }

    chmod($filePath, 0640);

    $metadata = [
        'file_id' => $fileId,
        'app_id' => $appId,
        'original_name' => $originalName,
        'extension' => $extension,
        'mime' => $mime,
        'size' => $size,
        'created_at' => gmdate('c'),
    ];

    $encoded = json_encode(
        $metadata,
        JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );

    $written = file_put_contents(
        $metadataPath,
        $encoded,
        LOCK_EX
    );

    if ($written === false || $written !== strlen($encoded)) {
        @unlink($filePath);
        @unlink($metadataPath);
        respond(500, ['error' => 'Could not save file metadata.']);
    }

    chmod($metadataPath, 0640);

    respond(201, [
        'success' => true,
        'file_id' => $fileId,
        'filename' => $originalName,
        'extension' => $extension,
        'mime' => $mime,
        'size' => $size,
    ]);
}

// Download and delete require a valid file ID.
$fileId = (string)($_POST['file_id'] ?? '');

if (!preg_match('/^[a-f0-9]{32}$/', $fileId)) {
    respond(400, ['error' => 'A valid file_id is required.']);
}

$metadataPath = $metadataDir . '/' . $fileId . '.json';

if (!is_file($metadataPath)) {
    respond(404, ['error' => 'File not found.']);
}

$metadataRaw = file_get_contents($metadataPath);
$metadata = is_string($metadataRaw)
    ? json_decode($metadataRaw, true)
    : null;

if (
    !is_array($metadata) ||
    ($metadata['file_id'] ?? '') !== $fileId ||
    !isset($metadata['extension']) ||
    !in_array($metadata['extension'], ALLOWED_EXTENSIONS, true)
) {
    respond(404, ['error' => 'File not found.']);
}

// Files from the old shared-secret API have no app_id.
// They are deliberately inaccessible until ownership is explicitly migrated.
if (
    !isset($metadata['app_id']) ||
    !is_string($metadata['app_id']) ||
    !hash_equals($metadata['app_id'], $appId)
) {
    respond(404, ['error' => 'File not found.']);
}

$extension = $metadata['extension'];
$filePath = $filesDir . '/' . $fileId . '.' . $extension;

if ($action === 'download') {
    if (!is_file($filePath) || !is_readable($filePath)) {
        respond(404, ['error' => 'File not found.']);
    }

    $actualSize = filesize($filePath);

    if (
        $actualSize === false ||
        $actualSize < 1 ||
        $actualSize > MAX_FILE_BYTES
    ) {
        respond(500, ['error' => 'Stored file size is invalid.']);
    }

    $mime = (string)($metadata['mime'] ?? 'application/octet-stream');

    // The filename is generated by the server, not supplied by the caller.
    $downloadName = $fileId . '.' . $extension;

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . $actualSize);
    header(
        'Content-Disposition: attachment; filename="' .
        $downloadName . '"'
    );
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;
}

if ($action === 'delete') {
    if (!is_file($filePath)) {
        respond(404, ['error' => 'File not found.']);
    }

    // Do not remove metadata if deleting the file itself fails.
    if (!unlink($filePath)) {
        respond(500, ['error' => 'Could not delete the file.']);
    }

    if (is_file($metadataPath) && !unlink($metadataPath)) {
        // The file is already deleted. Report cleanup failure honestly.
        respond(500, [
            'error' => 'File deleted, but metadata cleanup failed.',
            'file_id' => $fileId,
        ]);
    }

    respond(200, [
        'success' => true,
        'file_id' => $fileId,
    ]);
}

respond(400, ['error' => 'Unsupported action.']);
