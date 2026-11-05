<?php
ob_start();
require_once __DIR__ . '/../classes/FacultyProfileManager.php';
require_once __DIR__ . '/../../../database/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$conn = $database->getConnection();
$facultyManager = new FacultyProfileManager($conn);

$allowedRoles = ['School Directress', 'Admin College Coordinator', 'College Coordinator'];
$roleName = $_SESSION['role_name'] ?? '';

function fmJson($payload) {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }

    if (!is_array($input)) {
        $input = [];
    }

    $response = ['success' => false, 'message' => 'Invalid request.'];

    if (!in_array($roleName, $allowedRoles, true)) {
        fmJson(['success' => false, 'message' => 'Forbidden.']);
    }

    $action = isset($input['action']) ? trim((string)$input['action']) : '';

    if ($action === 'get_faculty_directory') {
        $search = trim((string)($input['search'] ?? ''));
        $employmentType = trim((string)($input['employment_type'] ?? ''));
        $rows = $facultyManager->getFacultyDirectory($search, $employmentType);
        $response = ['success' => true, 'data' => $rows];
    }

    if ($action === 'get_faculty_full_profile') {
        $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;
        if ($employeeId <= 0) {
            $response = ['success' => false, 'message' => 'Invalid employee.'];
        } else {
            $profile = $facultyManager->getFacultyFullProfile($employeeId);
            $response = $profile ? ['success' => true, 'data' => $profile] : ['success' => false, 'message' => 'Faculty not found.'];
        }
    }

    if ($action === 'get_faculty_shift_schedule' && isset($input['employee_id'])) {
        $employeeId = (int)$input['employee_id'];
        $schedule = $facultyManager->getFacultyShiftScheduleByEmployeeId($employeeId);
        $response = $schedule ? ['success' => true, 'schedule' => $schedule] : ['success' => false, 'message' => 'No active shift schedule found.'];
    }

    if ($action === 'get_employee_engagement_type' && isset($input['employee_id'])) {
        $employeeId = (int)$input['employee_id'];
        $employee = $facultyManager->getEmployeeById($employeeId);
        if (!$employee) {
            $response = ['success' => false, 'message' => 'Employee not found.'];
        } elseif ((int)($employee['is_archived'] ?? 1) !== 0 || ($employee['employment_status'] ?? '') !== 'Active') {
            $response = ['success' => false, 'message' => 'Employee is not active.'];
        } elseif (!in_array(($employee['employment_type'] ?? ''), ['Part-time'], true)) {
            $response = ['success' => false, 'message' => 'Employee is not eligible for engagement.'];
        } else {
            $response = ['success' => true, 'employment_type' => $employee['employment_type']];
        }
    }

    if ($action === 'add_engagement') {
        $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;
        $title = isset($input['title']) ? trim((string)$input['title']) : '';
        $organization = isset($input['organization']) ? trim((string)$input['organization']) : '';
        $startDate = isset($input['start_date']) ? trim((string)$input['start_date']) : '';
        $endDate = isset($input['end_date']) ? trim((string)$input['end_date']) : '';

        if ($employeeId <= 0 || $title === '' || $organization === '' || $startDate === '' || $endDate === '') {
            $response = ['success' => false, 'message' => 'Missing required fields.'];
        } else {
            $selectedEmployee = $facultyManager->getEmployeeById($employeeId);
            if (!$selectedEmployee) {
                $response = ['success' => false, 'message' => 'Selected employee could not be found.'];
            } elseif ((int)($selectedEmployee['is_archived'] ?? 1) !== 0 || ($selectedEmployee['employment_status'] ?? '') !== 'Active') {
                $response = ['success' => false, 'message' => 'Selected employee is not active and cannot be assigned an engagement.'];
            } elseif (!in_array(($selectedEmployee['employment_type'] ?? ''), ['Part-time'], true)) {
                $response = ['success' => false, 'message' => 'Selected employee is not eligible for an engagement.'];
            } elseif ($facultyManager->hasActiveEngagementForEmployee($employeeId)) {
                $response = ['success' => false, 'message' => 'This employee already has an active engagement record.'];
            } else {
                $engagementType = $selectedEmployee['employment_type'];
                $insertId = $facultyManager->addEngagement([
                    'employee_id' => $employeeId,
                    'engagement_type' => $engagementType,
                    'title' => $title,
                    'organization' => $organization,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'Pending',
                    'outcome' => 'Not Applicable',
                ]);

                $response = $insertId
                    ? ['success' => true, 'message' => 'Engagement created successfully.', 'engagement_id' => $insertId]
                    : ['success' => false, 'message' => 'Unable to create engagement.'];
            }
        }
    }

    if ($action === 'approve_engagement' && isset($input['engagement_id'])) {
        $engagementId = (int)$input['engagement_id'];
        $approvedBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $ok = $facultyManager->approveEngagement($engagementId, $approvedBy);
        $response = $ok ? ['success' => true, 'message' => 'Engagement approved successfully.'] : ['success' => false, 'message' => 'Unable to approve engagement.'];
    }

    if ($action === 'mark_completed_engagement' && isset($input['engagement_id'])) {
        $engagementId = (int)$input['engagement_id'];
        $outcome = isset($input['outcome']) ? trim((string)$input['outcome']) : 'Not Applicable';
        $allowed = ['Continue', 'Regularize', 'End Engagement', 'Not Applicable'];
        if (!in_array($outcome, $allowed, true)) {
            $outcome = 'Not Applicable';
        }
        $ok = $facultyManager->markEngagementCompleted($engagementId, $outcome);
        $response = $ok ? ['success' => true, 'message' => 'Engagement marked completed successfully.'] : ['success' => false, 'message' => 'Unable to mark engagement as completed.'];
    }

    if ($action === 'archive_engagement' && isset($input['engagement_id'])) {
        $engagementId = (int)$input['engagement_id'];
        $ok = $facultyManager->archiveEngagement($engagementId);
        $response = $ok ? ['success' => true, 'message' => 'Engagement archived successfully.'] : ['success' => false, 'message' => 'Unable to archive engagement.'];
    }

    if ($action === 'generate_certificate' && isset($input['engagement_id'])) {
        $engagementId = (int)$input['engagement_id'];
        $ok = $facultyManager->generateEngagementCertificate($engagementId);
        $response = $ok ? ['success' => true, 'message' => 'Certificate generated successfully.'] : ['success' => false, 'message' => 'Unable to generate certificate.'];
    }

    if ($action === 'get_engagement' && isset($input['engagement_id'])) {
        $engagementId = (int)$input['engagement_id'];
        $engagement = $facultyManager->getEngagementById($engagementId);
        if ($engagement) {
            $employeeId = (int)($engagement['employee_id'] ?? 0);
            $employee = $employeeId > 0 ? $facultyManager->getEmployeeById($employeeId) : null;
            $response = [
                'success' => true,
                'engagement' => $engagement,
                'employee' => $employee,
            ];
        } else {
            $response = ['success' => false, 'message' => 'Engagement not found.'];
        }
    }

    if ($action === 'get_faculty_education' && isset($input['employee_id'])) {
        $employeeId = trim((string)$input['employee_id']);
        $educationSummary = $facultyManager->getFacultyEducationSummary() ?? [];
        $matchedEducation = null;

        foreach ($educationSummary as $education) {
            $comparisonKey = $education['employee_code'] ?? null;
            if ((string)$comparisonKey === $employeeId) {
                $matchedEducation = $education;
                break;
            }

            $comparisonId = $education['employee_id'] ?? null;
            if ((string)$comparisonId === $employeeId) {
                $matchedEducation = $education;
                break;
            }
        }

        $facultyRequirements = [];
        $parsedEmployeeId = (int)$employeeId;
        if ($parsedEmployeeId > 0) {
            $facultyRequirements = $facultyManager->getFacultyRequirements($parsedEmployeeId) ?? [];
        }

        $response = $matchedEducation
            ? ['success' => true, 'records' => $matchedEducation['records'] ?? [], 'requirements' => $facultyRequirements]
            : ['success' => false, 'message' => 'No records found.'];
    }

    if ($action === 'get_faculty_training' && isset($input['employee_id'])) {
        $employeeId = trim((string)$input['employee_id']);
        $trainingSummary = $facultyManager->getFacultyTrainingSummary() ?? [];
        $matchedTraining = null;

        foreach ($trainingSummary as $training) {
            $comparisonKey = $training['employee_code'] ?? null;
            if ((string)$comparisonKey === $employeeId) {
                $matchedTraining = $training;
                break;
            }

            $comparisonId = $training['employee_id'] ?? null;
            if ((string)$comparisonId === $employeeId) {
                $matchedTraining = $training;
                break;
            }
        }

        $response = $matchedTraining
            ? ['success' => true, 'records' => $matchedTraining['records'] ?? []]
            : ['success' => false, 'message' => 'No records found.'];
    }

    if ($action === 'get_panel_content' && isset($input['tab'])) {
        $tab = strtolower(trim((string)$input['tab']));
        $facultyList = $facultyManager->getAllFacultyCredentials() ?? [];
        $educationSummary = $facultyManager->getFacultyEducationSummary() ?? [];
        $trainingSummary = $facultyManager->getFacultyTrainingSummary() ?? [];
        $eligibleEmployees = $facultyManager->getEligibleEngagementEmployees() ?? [];

        if ($tab === 'credentials') {
            $response = ['success' => true, 'html' => renderCredentialsPanel($facultyList)];
        } elseif ($tab === 'attainment') {
            $response = ['success' => true, 'html' => renderAttainmentPanel($educationSummary)];
        } elseif ($tab === 'trainings') {
            $response = ['success' => true, 'html' => renderTrainingsPanel($trainingSummary, $eligibleEmployees)];
        } else {
            $response = ['success' => false, 'message' => 'Invalid tab.'];
        }
    }

    if ($action === 'search_records' && isset($input['table'])) {
        $tableName = (string)($input['table'] ?? '');
        $term = isset($input['term']) ? strtolower(trim((string)$input['term'])) : '';
        $filter = isset($input['filter']) ? strtolower(trim((string)$input['filter'])) : 'all';
        $facultyList = $facultyManager->getAllFacultyCredentials() ?? [];
        $educationSummary = $facultyManager->getFacultyEducationSummary() ?? [];
        $trainingSummary = $facultyManager->getFacultyTrainingSummary() ?? [];
        $html = '';

        if ($tableName === 'credentialsTable') {
            $html = renderCredentialsTableRows($facultyList, $term);
        } elseif ($tableName === 'attainmentTable') {
            $html = renderAttainmentTableRows($educationSummary, $term);
        } elseif ($tableName === 'trainingsTable') {
            $html = renderTrainingTableRows($trainingSummary, $term, $filter);
        }

        $response = ['success' => true, 'html' => $html];
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$facultyList = $facultyManager->getAllFacultyCredentials() ?? [];
$educationSummary = $facultyManager->getFacultyEducationSummary() ?? [];
$trainingSummary = $facultyManager->getFacultyTrainingSummary() ?? [];
$eligibleEmployees = $facultyManager->getEligibleEngagementEmployees() ?? [];
$requirementStatusSummary = $facultyManager->getFacultyRequirementStatusSummary() ?? [];

$employmentTypeStats = [
    'Full-time' => 0,
    'Part-time' => 0,
];

foreach ($facultyList as $faculty) {
    $employmentType = trim((string)($faculty['employment_type'] ?? ''));

    if ($employmentType === 'Full-time') {
        $employmentTypeStats['Full-time']++;
    } elseif ($employmentType === 'Part-time') {
        $employmentTypeStats['Part-time']++;
    }
}

$documentStatusStats = [];
if (!empty($requirementStatusSummary)) {
    $statusLabels = [
        'Complete' => 'Complete',
        'Pending' => 'Pending',
        'Incomplete / Missing' => 'Incomplete / Missing',
    ];

    foreach ($statusLabels as $statusKey => $label) {
        $count = (int)($requirementStatusSummary[$statusKey] ?? 0);
        if ($count > 0) {
            $documentStatusStats[] = [
                'label' => $label,
                'count' => $count,
            ];
        }
    }
}

function normalizeFacultySearchText($value) {
    return strtolower(trim(strip_tags((string)$value)));
}

function renderCredentialsRow($faculty) {
    $middle = '';
    if (!empty($faculty['middle_name'])) {
        $middle = ' ' . strtoupper(substr(trim($faculty['middle_name']), 0, 1)) . '.';
    }
    $suffix = !empty($faculty['suffix']) ? ' ' . htmlspecialchars($faculty['suffix']) : '';
    $fullName = htmlspecialchars(trim($faculty['first_name'] . $middle . ' ' . $faculty['last_name'] . $suffix));

    ob_start();
    ?>
    <tr data-faculty-id="<?= (int)$faculty['faculty_id'] ?>"
        data-employee-id="<?= (int)($faculty['employee_id'] ?? 0) ?>"
        data-employee-code="<?= htmlspecialchars($faculty['employee_code']) ?>"
        data-faculty-name="<?= $fullName ?>"
        data-department="<?= htmlspecialchars($faculty['department']) ?>"
        data-email="<?= htmlspecialchars($faculty['email']) ?>"
        data-created-at="<?= htmlspecialchars($faculty['created_at']) ?>"
        data-position-id="<?= htmlspecialchars($faculty['position_id']) ?>"
        data-position-name="<?= htmlspecialchars($faculty['position_name'] ?? '') ?>"
        data-employment-type="<?= htmlspecialchars($faculty['employment_type']) ?>"
        data-employment-status="<?= htmlspecialchars($faculty['employment_status']) ?>">
        <td><?= htmlspecialchars((string)($faculty['employee_id'] ?? 'N/A')) ?></td>
        <td><?= $fullName ?></td>
        <td><?= htmlspecialchars($faculty['department']) ?></td>
        <td><?= htmlspecialchars($faculty['position_name'] ?? $faculty['position_id']) ?></td>
        <td><?= htmlspecialchars($faculty['employment_type']) ?></td>
        <td><?= htmlspecialchars($faculty['employment_status']) ?></td>
        <td class="actions">
            <button type="button" class="btn btn-sm btn-info view-profile-btn">View</button>
            <button type="button" class="btn btn-sm btn-secondary shift-schedule-btn" data-employee-id="<?= (int)($faculty['employee_id'] ?? 0) ?>">Shift Schedule</button>
        </td>
    </tr>
    <?php
    return ob_get_clean();
}

function renderAttainmentRow($education) {
    $recordsJson = json_encode($education['records'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    ob_start();
    ?>
    <tr data-record-type="education" data-employee-code="<?= htmlspecialchars($education['employee_code']) ?>" data-employee-id="<?= (int)($education['employee_id'] ?? 0) ?>" data-faculty-name="<?= htmlspecialchars($education['faculty_name']) ?>" data-department="<?= htmlspecialchars($education['department'] ?? 'Not provided') ?>" data-records='<?= htmlspecialchars($recordsJson, ENT_QUOTES, 'UTF-8') ?>'>
        <td><?= htmlspecialchars((string)($education['employee_id'] ?? 'N/A')) ?></td>
        <td><?= htmlspecialchars($education['faculty_name']) ?></td>
        <td><?= htmlspecialchars($education['highest_degree']) ?></td>
        <td><?= htmlspecialchars($education['school_name']) ?></td>
        <td><?= htmlspecialchars($education['year_graduated']) ?></td>
        <td class="actions"><button type="button" class="btn btn-sm btn-info view-attainment-btn">View</button></td>
    </tr>
    <?php
    return ob_get_clean();
}

function renderTrainingRow($training) {
    $recordsJson = json_encode($training['records'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    ob_start();
    ?>
    <tr data-record-type="<?= htmlspecialchars($training['record_type'] ?? 'empty') ?>"
        data-record-source="<?= htmlspecialchars($training['record_source'] ?? '') ?>"
        data-record-id="<?= htmlspecialchars((string)($training['record_id'] ?? '')) ?>"
        data-record-status="<?= htmlspecialchars($training['status'] ?? '') ?>"
        data-record-outcome="<?= htmlspecialchars($training['outcome'] ?? '') ?>"
        data-employee-code="<?= htmlspecialchars($training['employee_code'] ?? '') ?>"
        data-faculty-name="<?= htmlspecialchars($training['faculty_name'] ?? '') ?>"
        data-employment-type="<?= htmlspecialchars($training['employment_type'] ?? 'N/A') ?>"
        data-employment-status="<?= htmlspecialchars($training['employment_status'] ?? 'N/A') ?>"
        data-records='<?= htmlspecialchars($recordsJson, ENT_QUOTES, 'UTF-8') ?>'>
        <td><?= htmlspecialchars((string)($training['employee_id'] ?? 'N/A')) ?></td>
        <td><?= htmlspecialchars($training['faculty_name'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($training['type'] ?? 'No record yet') ?></td>
        <td><?= htmlspecialchars($training['title'] ?? 'No record yet') ?></td>
        <td><?= htmlspecialchars($training['organization'] ?? 'No record yet') ?></td>
        <td><?= htmlspecialchars($training['date_period'] ?? 'No record yet') ?></td>
        <td><?= htmlspecialchars($training['status'] ?? 'No record yet') ?></td>
        <td class="actions">
            <button type="button" class="btn btn-sm btn-info view-training-btn">View</button>
            <?php if (($training['record_source'] ?? '') === 'cc_certification_engagements'): ?>
                <?php
                    $statusValue = $training['status'] ?? '';
                    $workflowButtons = '';

                    if ($statusValue === 'Pending') {
                        $workflowButtons .= '<button type="button" class="btn btn-sm btn-success approve-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Approve</button>';
                    } elseif (in_array($statusValue, ['Approved', 'Ongoing'], true)) {
                        $workflowButtons .= '<button type="button" class="btn btn-sm btn-warning complete-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Mark Completed</button>';
                    } elseif ($statusValue === 'Completed') {
                        $workflowButtons .= '<button type="button" class="btn btn-sm btn-primary generate-certificate-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Generate Certificate</button>';
                        $workflowButtons .= '<button type="button" class="btn btn-sm btn-secondary archive-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Archive</button>';
                    }

                    echo $workflowButtons;
                ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php
    return ob_get_clean();
}

function renderCredentialsTableRows($facultyList, $term = '') {
    $rowsHtml = '';

    foreach ($facultyList as $faculty) {
        $searchableText = implode(' ', [
            $faculty['employee_code'] ?? '',
            $faculty['first_name'] ?? '',
            $faculty['middle_name'] ?? '',
            $faculty['last_name'] ?? '',
            $faculty['suffix'] ?? '',
            $faculty['department'] ?? '',
            $faculty['position_id'] ?? '',
            $faculty['employment_type'] ?? '',
            $faculty['employment_status'] ?? '',
        ]);

        if ($term !== '' && stripos($searchableText, $term) === false) {
            continue;
        }

        $rowsHtml .= renderCredentialsRow($faculty);
    }

    if ($rowsHtml === '') {
        return '<tr class="no-data"><td colspan="7" class="text-center text-muted" style="padding: 20px;">No faculty records available.</td></tr>';
    }

    return $rowsHtml;
}

function renderAttainmentTableRows($educationSummary, $term = '') {
    $rowsHtml = '';

    foreach ($educationSummary as $education) {
        $searchableText = implode(' ', [
            $education['employee_code'] ?? '',
            $education['faculty_name'] ?? '',
            $education['highest_degree'] ?? '',
            $education['school_name'] ?? '',
            $education['year_graduated'] ?? '',
            $education['department'] ?? '',
        ]);

        if ($term !== '' && stripos($searchableText, $term) === false) {
            continue;
        }

        $rowsHtml .= renderAttainmentRow($education);
    }

    if ($rowsHtml === '') {
        return '<tr class="no-data"><td colspan="6" class="text-center text-muted" style="padding: 20px;">No educational attainment records yet.</td></tr>';
    }

    return $rowsHtml;
}

function renderTrainingTableRows($trainingSummary, $term = '', $filter = 'all') {
    $rowsHtml = '';

    foreach ($trainingSummary as $training) {
        $statusValue = strtolower((string)($training['status'] ?? ''));
        if ($filter === 'archived') {
            if ($statusValue !== 'archived') {
                continue;
            }
        } elseif ($filter === 'completed') {
            if ($statusValue !== 'completed') {
                continue;
            }
        } elseif ($filter === 'active') {
            if (!in_array($statusValue, ['pending', 'ongoing', 'for review', 'approved'], true)) {
                continue;
            }
        } else {
            if ($statusValue === 'archived') {
                continue;
            }
        }

        $searchableText = implode(' ', [
            $training['employee_code'] ?? '',
            $training['faculty_name'] ?? '',
            $training['type'] ?? '',
            $training['title'] ?? '',
            $training['organization'] ?? '',
            $training['date_period'] ?? '',
            $training['status'] ?? '',
        ]);

        if ($term !== '' && stripos($searchableText, $term) === false) {
            continue;
        }

        $rowsHtml .= renderTrainingRow($training);
    }

    if ($rowsHtml === '') {
        return '<tr class="no-data"><td colspan="8" class="text-center text-muted" style="padding: 20px;">No trainings or certifications recorded yet.</td></tr>';
    }

    return $rowsHtml;
}

function renderCredentialsPanel($facultyList) {
    ob_start();
    ?>
    <div class="academics-panel fm-panel active" id="fm-panel-credentials">
        <div class="section-header">
            <h3>Faculty Credentials</h3>
            <p class="text-muted" style="margin: 0;"><small>Browse faculty account details and employment status placeholders.</small></p>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchCredentials" placeholder="Search faculty credentials...">
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="credentialsTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Employment Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facultyList)): ?>
                            <?php foreach ($facultyList as $faculty): ?>
                                <?php echo renderCredentialsRow($faculty); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="7" class="text-center text-muted" style="padding: 20px;">No faculty records available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderAttainmentPanel($educationSummary) {
    ob_start();
    ?>
    <div class="academics-panel fm-panel" id="fm-panel-attainment">
        <div class="section-header">
            <h3>Educational Attainment</h3>
            <p class="text-muted" style="margin: 0;"><small>Track each faculty's highest credential.</small></p>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchAttainment" placeholder="Search attainment records...">
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="attainmentTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Highest Degree</th>
                            <th>School</th>
                            <th>Year Graduated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($educationSummary)): ?>
                            <?php foreach ($educationSummary as $education): ?>
                                <?php echo renderAttainmentRow($education); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="6" class="text-center text-muted" style="padding: 20px;">No educational attainment records yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderTrainingsPanel($trainingSummary, $eligibleEmployees = []) {
    ob_start();
    ?>
    <div class="academics-panel fm-panel" id="fm-panel-trainings">
        <div class="section-header">
            <h3>Trainings & Certifications</h3>
                <p class="text-muted" style="margin: 0;"><small>Record faculty trainings and certificates.</small></p>
                <div style="margin-top:8px;"><button id="addEngagementBtn" type="button" class="btn btn-sm btn-primary">Add Engagement</button></div>
        </div>

        <div class="search-filter-container training-filter-container">
            <div class="search-box">
                <input type="text" id="searchTrainings" placeholder="Search trainings and certifications...">
            </div>
            <div class="training-status-filters" role="group" aria-label="Training status filter">
                <button type="button" class="training-status-filter active" data-training-filter="all">All</button>
                <button type="button" class="training-status-filter" data-training-filter="active">Active</button>
                <button type="button" class="training-status-filter" data-training-filter="completed">Completed</button>
                <button type="button" class="training-status-filter" data-training-filter="archived">Archived</button>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="trainingsTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Organization</th>
                            <th>Date/Period</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($trainingSummary)): ?>
                            <?php foreach ($trainingSummary as $training): ?>
                                <?php echo renderTrainingRow($training); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="8" class="text-center text-muted" style="padding: 20px;">No trainings or certifications recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
<div class="module-header">
    <h1>Faculty Management</h1>
    <p>View and manage faculty profiles, educational attainment, and trainings.</p>
</div>
<div class="module-content fm-page" id="fmRoot">
    <header class="fm-header">
        <div>
            <h1>Faculty Management</h1>
            <p>Overview of faculty credentials, education, and trainings.</p>
        </div>
    </header>

    <section id="fmOverview" class="fm-overview">
        <div class="fm-stat-grid">
            <?php foreach ($employmentTypeStats as $label => $count): ?>
                <article class="fm-stat-card">
                    <span><?= htmlspecialchars($label) ?></span>
                    <strong><?= (int)$count ?></strong>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($documentStatusStats)): ?>
            <div class="fm-stat-grid fm-stat-grid-secondary">
                <?php foreach ($documentStatusStats as $status): ?>
                    <article class="fm-stat-card fm-stat-card-soft">
                        <span><?= htmlspecialchars($status['label']) ?></span>
                        <strong><?= (int)$status['count'] ?></strong>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="fm-toolbar">
            <div class="fm-search-wrap">
                <input id="fmSearch" type="text" placeholder="Search faculty..." aria-label="Search faculty" />
            </div>
            <div class="fm-select-wrap">
                <label for="fmEmploymentFilter" class="fm-label">Employment Type</label>
                <select id="fmEmploymentFilter" aria-label="Filter by employment type">
                    <option value="All">All</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                </select>
            </div>
        </div>

        <div id="fmEmptyState" class="fm-empty-state">Loading faculty directory…</div>

        <div class="fm-table-shell">
            <table class="fm-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Faculty Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Employment Type</th>
                        <th>Status</th>
                        <th>Highest Degree</th>
                        <th>Trainings</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="fmTableBody"></tbody>
            </table>
        </div>
    </section>

    <section id="fmDetail" class="fm-detail" hidden>
        <button type="button" class="fm-back-btn" data-fm-action="back">← Back to faculty</button>

        <div id="fmDetailState" class="fm-detail-state" hidden>Loading faculty details…</div>

        <div id="fmDetailContent" class="fm-detail-content" hidden>
            <div class="fm-profile-hero">
                <div class="fm-avatar" id="fmDetailAvatar">?</div>
                <div class="fm-profile-meta">
                    <div class="fm-name-row">
                        <h2 id="fmDetailName">Faculty Name</h2>
                        <span id="fmDetailStatus" class="fm-status-badge">Active</span>
                    </div>
                    <p id="fmDetailSubline" class="fm-subline">Employee · Position · Department</p>
                </div>
                <div class="fm-detail-actions">
                    <button type="button" class="fm-btn fm-btn-primary" data-fm-action="shift-schedule" id="fmDetailShiftScheduleBtn" hidden>Shift Schedule</button>
                    <button type="button" class="fm-btn fm-btn-secondary" data-fm-action="add-engagement" id="fmDetailAddEngagementBtn" hidden>Add Engagement</button>
                </div>
            </div>

            <div class="fm-info-grid">
                <div class="fm-info-box">
                    <span>Employee ID</span>
                    <strong id="fmDetailEmployeeId">N/A</strong>
                </div>
                <div class="fm-info-box">
                    <span>Position</span>
                    <strong id="fmDetailPosition">N/A</strong>
                </div>
                <div class="fm-info-box">
                    <span>Department</span>
                    <strong id="fmDetailDepartment">N/A</strong>
                </div>
                <div class="fm-info-box">
                    <span>Email</span>
                    <strong id="fmInfoEmail">N/A</strong>
                </div>
                <div class="fm-info-box">
                    <span>Created</span>
                    <strong id="fmInfoCreated">N/A</strong>
                </div>
                <div class="fm-info-box">
                    <span>Employment Type</span>
                    <strong id="fmInfoEmploymentType">N/A</strong>
                </div>
                <div class="fm-info-box">
                    <span>Employment Status</span>
                    <strong id="fmInfoEmploymentStatus">N/A</strong>
                </div>
            </div>

            <div class="fm-panel-stack">
                <section class="fm-panel-card">
                    <div class="fm-panel-header">
                        <h3>Profile</h3>
                    </div>
                    <table class="fm-inner-table">
                        <tbody>
                            <tr><th>Employee ID</th><td id="fmProfileEmployeeId">N/A</td></tr>
                            <tr><th>Faculty Name</th><td id="fmProfileName">N/A</td></tr>
                            <tr><th>Department</th><td id="fmProfileDepartment">N/A</td></tr>
                            <tr><th>Email</th><td id="fmProfileEmail">N/A</td></tr>
                            <tr><th>Position</th><td id="fmProfilePosition">N/A</td></tr>
                            <tr><th>Employment Type</th><td id="fmProfileEmploymentType">N/A</td></tr>
                            <tr><th>Employment Status</th><td id="fmProfileEmploymentStatus">N/A</td></tr>
                        </tbody>
                    </table>
                </section>

                <section class="fm-panel-card">
                    <div class="fm-panel-header">
                        <h3>Educational Attainment</h3>
                    </div>
                    <table class="fm-inner-table">
                        <thead>
                            <tr><th>Level</th><th>School</th><th>Course</th><th>Year</th></tr>
                        </thead>
                        <tbody id="fmEducationTableBody">
                            <tr><td colspan="4">No education records found.</td></tr>
                        </tbody>
                    </table>
                </section>

                <section class="fm-panel-card">
                    <div class="fm-panel-header">
                        <h3>Trainings & Certifications</h3>
                    </div>
                    <div class="fm-status-switcher" role="tablist" aria-label="Training status filter">
                        <button type="button" class="fm-status-toggle is-active" data-fm-status-filter="all">All</button>
                        <button type="button" class="fm-status-toggle" data-fm-status-filter="active">Active</button>
                        <button type="button" class="fm-status-toggle" data-fm-status-filter="completed">Completed</button>
                        <button type="button" class="fm-status-toggle" data-fm-status-filter="archived">Archived</button>
                    </div>
                    <table class="fm-inner-table">
                        <thead>
                            <tr><th>Type</th><th>Title</th><th>Organization</th><th>Date</th><th>Status</th><th>Outcome</th><th>Action</th></tr>
                        </thead>
                        <tbody id="fmTrainingTableBody">
                            <tr><td colspan="7">No training records found.</td></tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </section>
</div>

<div id="fmAddEngagementModal" class="modal" hidden>
    <div class="modal-dialog" style="max-width: 560px;">
        <div class="modal-header">
            <h5 class="modal-title">Add Engagement</h5>
            <button type="button" class="modal-close" data-fm-action="close-add-engagement" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="fmAddEngagementForm" data-custom-submit>
                <div class="form-group">
                    <label for="fmAddEngagementEmployee">Employee</label>
                    <input id="fmAddEngagementEmployee" class="form-control" type="text" readonly />
                    <input id="fmAddEngagementEmployeeId" type="hidden" />
                </div>

                <div class="form-group">
                    <label for="fmAddEngagementTitle">Title</label>
                    <input id="fmAddEngagementTitle" name="title" class="form-control" required />
                </div>

                <div class="form-group">
                    <label for="fmAddEngagementOrganization">Organization</label>
                    <input id="fmAddEngagementOrganization" name="organization" class="form-control" required />
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fmAddEngagementStartDate">Start Date</label>
                        <input id="fmAddEngagementStartDate" name="start_date" type="date" class="form-control" required />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fmAddEngagementEndDate">End Date</label>
                        <input id="fmAddEngagementEndDate" name="end_date" type="date" class="form-control" required />
                    </div>
                </div>

                <div style="margin-top:12px; text-align:right;">
                    <button type="button" class="btn btn-secondary" data-fm-action="close-add-engagement">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Engagement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="fmShiftScheduleModal" class="modal" hidden>
    <div class="modal-dialog" style="max-width: 760px;">
        <div class="modal-header">
            <h5 class="modal-title">Shift Schedule</h5>
            <button type="button" class="modal-close" data-fm-action="close-shift-schedule" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="fmShiftScheduleContent" class="fm-shift-content"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-fm-action="close-shift-schedule">Close</button>
        </div>
    </div>
</div>

<div id="fmOutcomeModal" class="modal" hidden>
    <div class="modal-dialog" style="max-width: 420px;">
        <div class="modal-header">
            <h5 class="modal-title">Complete Engagement</h5>
            <button type="button" class="modal-close" data-fm-action="close-outcome-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="fmOutcomeSelect">Outcome</label>
                <select id="fmOutcomeSelect" class="form-control">
                    <option value="Continue">Continue</option>
                    <option value="Regularize">Regularize</option>
                    <option value="End Engagement">End Engagement</option>
                    <option value="Not Applicable">Not Applicable</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-fm-action="close-outcome-modal">Cancel</button>
            <button id="fmOutcomeConfirmBtn" type="button" class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>


