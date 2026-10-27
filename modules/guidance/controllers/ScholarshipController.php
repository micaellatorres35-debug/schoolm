<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Scholarship.php';
include_once __DIR__ . '/../classes/User.php';
// NOTE: paths mirror CasesController.php — adjust depth/folder names if
// ScholarshipController.php doesn't live at the same location.

header('Content-Type: application/json');

// ── Guard: only accept authenticated requests, Guidance counselors only ──────
$userClass = new User();
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}
// TODO: if User::userSession() doesn't already scope by department/role,
// add an explicit check here (e.g. $userInfo['department_id'] == 8)
// matching however Directress-only gating is enforced in the SD module.

$scholarshipClass = new Scholarship();
$action = $_REQUEST['action'] ?? '';

// ── Router ────────────────────────────────────────────────────────────────
switch ($action) {

    // ------------------------------------------------------------------
    // GET list (filtered + paginated)
    // ------------------------------------------------------------------
    case 'list':
        $filters = [
            'search'            => trim($_GET['search'] ?? ''),
            'status'            => $_GET['status'] ?? '',
            'eligibility_basis' => $_GET['eligibility_basis'] ?? '',
            'coverage_type'     => $_GET['coverage_type'] ?? '',
            'counselor_id'      => $_GET['counselor_id'] ?? '',
        ];
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $pageSize = 10;

        $result        = $scholarshipClass->getList($filters, $page, $pageSize);
        $scholarships  = array_map('formatScholarshipRow', $result['rows']);

        echo json_encode([
            'success' => true,
            'data' => [
                'scholarships' => $scholarships,
                'pagination' => [
                    'total'      => $result['total'],
                    'page'       => $page,
                    'pageSize'   => $pageSize,
                    'count'      => count($scholarships),
                    'totalPages' => (int) ceil($result['total'] / $pageSize),
                ],
            ],
        ]);
        break;

    // ------------------------------------------------------------------
    // GET details
    // ------------------------------------------------------------------
    case 'details':
        $scholarshipId = (int) ($_GET['scholarship_id'] ?? 0);
        if (!$scholarshipId) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id is required.']);
            exit;
        }

        $overview = $scholarshipClass->getOverview($scholarshipId);
        if (!$overview) {
            echo json_encode(['success' => false, 'message' => 'Scholarship application not found.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => formatScholarshipDetail($overview)]);
        break;

    // ------------------------------------------------------------------
    // GET counselors
    // ------------------------------------------------------------------
    case 'counselors':
        echo json_encode(['success' => true, 'data' => $scholarshipClass->getCounselors()]);
        break;

    // ------------------------------------------------------------------
    // POST mark_under_review — Applied -> Under Review
    // ------------------------------------------------------------------
    case 'mark_under_review':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        if (!$scholarshipId) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id is required.']);
            exit;
        }

        $ok = $scholarshipClass->markUnderReview($scholarshipId);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Application marked as Under Review.' : 'Could not update — application may not be in Applied status.',
        ]);
        break;

    // ------------------------------------------------------------------
    // POST review — Under Review -> Approved/Rejected
    // ------------------------------------------------------------------
    case 'review':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        $decision = $input['decision'] ?? '';

        if (!$scholarshipId || !in_array($decision, ['approve', 'reject'], true)) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id and a valid decision (approve/reject) are required.']);
            exit;
        }

        $reviewerId = $userInfo['employee_id'] ?? (int) ($input['reviewer_id'] ?? 0);
        if (!$reviewerId) {
            echo json_encode(['success' => false, 'message' => 'Could not determine reviewer identity.']);
            exit;
        }

        $ok = $scholarshipClass->reviewApplication($scholarshipId, $decision, $reviewerId, trim($input['notes'] ?? ''));
        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ('Application ' . ($decision === 'approve' ? 'approved.' : 'rejected.'))
                : 'Could not update — application may not be in Under Review status.',
        ]);
        break;

    // ------------------------------------------------------------------
    // POST activate — Approved -> Active
    // ------------------------------------------------------------------
    case 'activate':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        if (!$scholarshipId) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id is required.']);
            exit;
        }

        $ok = $scholarshipClass->activate($scholarshipId);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Scholarship activated.' : 'Could not update — application may not be in Approved status.',
        ]);
        break;

    // ------------------------------------------------------------------
    // POST finalize — Active -> Completed/Terminated
    // ------------------------------------------------------------------
    case 'finalize':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        $outcome = $input['outcome'] ?? '';

        if (!$scholarshipId || !in_array($outcome, ['completed', 'terminated'], true)) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id and a valid outcome (completed/terminated) are required.']);
            exit;
        }

        $ok = $scholarshipClass->finalize($scholarshipId, $outcome, trim($input['notes'] ?? ''));
        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ('Scholarship marked as ' . ($outcome === 'completed' ? 'Completed.' : 'Terminated.'))
                : 'Could not update — application may not be in Active status.',
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

// ── Formatting helpers ───────────────────────────────────────────────────
function formatScholarshipRow(array $row): array
{
    $row['applied_at_display']  = date('M d, Y', strtotime($row['applied_at']));
    $row['reviewed_at_display'] = !empty($row['reviewed_at']) ? date('M d, Y', strtotime($row['reviewed_at'])) : null;
    $row['award_amount_display'] = $row['fixed_amount'] !== null ? number_format((float) $row['fixed_amount'], 2) : null;
    return $row;
}

function formatScholarshipDetail(array $row): array
{
    $row['applied_at_display']  = date('M d, Y g:i A', strtotime($row['applied_at']));
    $row['reviewed_at_display'] = !empty($row['reviewed_at']) ? date('M d, Y g:i A', strtotime($row['reviewed_at'])) : null;
    $row['award_amount_display'] = $row['fixed_amount'] !== null ? number_format((float) $row['fixed_amount'], 2) : null;
    return $row;
}