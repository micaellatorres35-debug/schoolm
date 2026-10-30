<?php
/**
 * FileStorage — one place that knows where uploaded/generated files live.
 *
 * Rules:
 *  - The database stores only a RELATIVE path (e.g. "reports/pdf/report_12_1760000000.pdf").
 *  - The absolute location is built here, so it is correct on XAMPP (Windows) and
 *    on the live Linux server without touching any other code.
 *  - Files are never linked to directly. They are streamed through a controller
 *    action after a session/role check (see ReportController action "file").
 *
 * Storage location (first match wins):
 *   1. define('SMS_STORAGE_PATH', '/home/yourdomain.com/sms_storage');   // e.g. in config
 *   2. environment variable SMS_STORAGE_PATH
 *   3. <project root>/storage
 *
 * On CyberPanel, option 1 or 2 pointing to a folder ABOVE public_html is best.
 */
class FileStorage {

    const MAX_BYTES = 10485760; // 10 MB

    // Extensions allowed for user attachments => acceptable real (finfo) MIME types.
    public static $allowed = [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/CDFV2', 'application/x-ole-storage', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/msword', 'application/CDFV2', 'application/x-ole-storage', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
    ];

    // Content-Type we send when serving a stored file (decided by extension, never by the client).
    public static $mimeByExt = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    // ── Locations ───────────────────────────────────────────────────────
    public static function root() {
        // this file lives in <root>/modules/<module>/classes/
        return str_replace('\\', '/', dirname(__DIR__, 3));
    }

    public static function base() {
        if (defined('SMS_STORAGE_PATH') && SMS_STORAGE_PATH) {
            return rtrim(str_replace('\\', '/', SMS_STORAGE_PATH), '/');
        }
        $env = getenv('SMS_STORAGE_PATH');
        if ($env) {
            return rtrim(str_replace('\\', '/', $env), '/');
        }
        return self::root() . '/storage';
    }

    public static function ensureDir($dir) {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }
        return is_writable($dir);
    }

    // Safety net: if the storage folder is inside the web root, block direct access.
    private static function protectBase() {
        $base = self::base();
        if (!is_dir($base)) {
            return;
        }
        $ht = $base . '/.htaccess';
        if (!file_exists($ht)) {
            @file_put_contents($ht, "Require all denied\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n");
        }
        $idx = $base . '/index.html';
        if (!file_exists($idx)) {
            @file_put_contents($idx, '');
        }
    }

    // ── Resolving a stored relative path to a real file ─────────────────
    // Returns the absolute path, or null if missing / outside the allowed folders.
    public static function resolve($relative) {
        $relative = ltrim(str_replace('\\', '/', (string) $relative), '/');
        if ($relative === '' || strpos($relative, '..') !== false) {
            return null;
        }

        $candidates = [self::base() . '/' . $relative];
        // Legacy rows saved by the old code: "uploads/reports/pdf/..." under the project root.
        if (strpos($relative, 'uploads/') === 0) {
            $candidates[] = self::root() . '/' . $relative;
        }

        $allowedRoots = array_filter([
            realpath(self::base()),
            realpath(self::root() . '/uploads'),
        ]);

        foreach ($candidates as $path) {
            if (!is_file($path)) {
                continue;
            }
            $real = realpath($path);
            if (!$real) {
                continue;
            }
            foreach ($allowedRoots as $root) {
                if (strpos($real, $root . DIRECTORY_SEPARATOR) === 0) {
                    return $real;
                }
            }
        }
        return null;
    }

    // ── Uploads ─────────────────────────────────────────────────────────
    public static function hasUpload($file) {
        return is_array($file) && (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
    }

    // Returns null when the upload is fine, otherwise a user-facing error message.
    public static function validateUpload($file) {
        switch ($file['error'] ?? UPLOAD_ERR_NO_FILE) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The attachment is too large (maximum 10 MB).';
            case UPLOAD_ERR_PARTIAL:
                return 'The attachment was only partly uploaded. Please try again.';
            default:
                return 'The attachment could not be uploaded (server error code ' . (int) $file['error'] . ').';
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return 'Invalid upload.';
        }
        if ((int) $file['size'] > self::MAX_BYTES) {
            return 'The attachment is too large (maximum 10 MB).';
        }

        $ext = self::extensionOf($file['name']);
        if (!isset(self::$allowed[$ext])) {
            return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
        }

        $mime = self::detectMime($file['tmp_name']);
        if ($mime !== '' && !in_array($mime, self::$allowed[$ext], true)) {
            return 'The file content does not match its file type.';
        }

        return null;
    }

    // Moves an already-validated upload into storage. Returns the RELATIVE path.
    public static function store($file, $subDir, $prefix) {
        $subDir = trim(str_replace('\\', '/', $subDir), '/');
        $dir    = self::base() . '/' . $subDir;

        if (!self::ensureDir($dir)) {
            throw new \RuntimeException('Storage folder is missing or not writable: ' . $dir);
        }
        self::protectBase();

        $ext  = self::extensionOf($file['name']);
        $name = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix) . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new \RuntimeException('Could not move the uploaded file into storage.');
        }
        @chmod($dir . '/' . $name, 0640);

        return $subDir . '/' . $name;
    }

    // Writes raw bytes (e.g. a generated PDF). Returns the RELATIVE path.
    public static function storeBytes($bytes, $subDir, $fileName) {
        $subDir = trim(str_replace('\\', '/', $subDir), '/');
        $dir    = self::base() . '/' . $subDir;

        if (!self::ensureDir($dir)) {
            throw new \RuntimeException('Storage folder is missing or not writable: ' . $dir);
        }
        self::protectBase();

        if (file_put_contents($dir . '/' . $fileName, $bytes) === false) {
            throw new \RuntimeException('Could not write file to storage.');
        }
        return $subDir . '/' . $fileName;
    }

    public static function delete($relative) {
        $abs = self::resolve($relative);
        if ($abs) {
            @unlink($abs);
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────
    public static function extensionOf($name) {
        return strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
    }

    public static function mimeFor($name) {
        return self::$mimeByExt[self::extensionOf($name)] ?? 'application/octet-stream';
    }

    public static function cleanName($name) {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name);
        return mb_substr($name, 0, 150);
    }

    private static function detectMime($path) {
        if (!function_exists('finfo_open')) {
            return '';
        }
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if (!$f) {
            return '';
        }
        $m = finfo_file($f, $path);
        finfo_close($f);
        return $m ?: '';
    }

    // ── Streaming ───────────────────────────────────────────────────────
    // Sends the file to the browser and ends the request.
    public static function stream($absPath, $downloadName, $mime, $download = false) {
        // Anything still buffered (even a single stray space) would corrupt a PDF.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $safeName = preg_replace('/[^A-Za-z0-9._ -]/', '_', (string) $downloadName);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($absPath));
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $safeName . '"');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN'); // lets the in-modal <iframe> viewer load it
        header('Cache-Control: private, max-age=0, must-revalidate');

        readfile($absPath);
        exit;
    }
}