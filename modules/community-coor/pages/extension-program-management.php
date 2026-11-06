<?php
require_once(__DIR__ . '/../classes/ProgramManager.php');
require_once(__DIR__ . '/../classes/SubjectManager.php');
require_once(__DIR__ . '/../classes/SectionManager.php');
require_once(__DIR__ . '/../classes/FacultyManager.php');
require_once(__DIR__ . '/../../../database/db.php');

$database = new Database();
$conn = $database->getConnection();

$programManager = new ProgramManager($conn);
$subjectManager = new SubjectManager($conn);
$sectionManager = new SectionManager($conn);
$facultyManager = new FacultyManager($conn);

// Fetch data
$programs = $programManager->getAllPrograms() ?? [];
$subjects = $subjectManager->getAllSubjects() ?? [];
$subjectCatalog = $subjectManager->getAllSubjectAssignments() ?? [];
$subjectAssignmentsById = [];
foreach ($subjectCatalog as $subjectRow) {
    $subjectId = $subjectRow['id'];
    if (!isset($subjectAssignmentsById[$subjectId])) {
        $subjectAssignmentsById[$subjectId] = [];
    }
    $subjectAssignmentsById[$subjectId][] = $subjectRow;
}
$sectionsStmt = $sectionManager->getAll();
$sections = $sectionsStmt ? $sectionsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
$sectionSchoolYears = [];
foreach ($sections as $section) {
    if (!empty($section['school_year'])) {
        $sectionSchoolYears[$section['school_year']] = true;
    }
}
$facultyLoads = $facultyManager->getFacultyLoad() ?? [];

$schoolYearOptions = [];
$semesterOptions = [];
try {
    $schoolYearStmt = $conn->query("SELECT id, name, is_active FROM rgr_school_years WHERE is_active = 1 ORDER BY id DESC");
    if ($schoolYearStmt) {
        $schoolYearOptions = $schoolYearStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        error_log('school years query failed: ' . print_r($conn->errorInfo(), true));
    }

    $semesterStmt = $conn->query("SELECT id, name, school_year_id, is_active FROM rgr_semesters WHERE is_active = 1 ORDER BY id ASC");
    if ($semesterStmt) {
        $semesterOptions = $semesterStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        error_log('semesters query failed: ' . print_r($conn->errorInfo(), true));
    }
} catch (Exception $e) {
    error_log('Add Section options failed: ' . $e->getMessage());
}

// Sample statistics
$stats = [
    'programs' => count($programs),
    'subjects' => count($subjects),
    'sections' => count($sections),
    'faculty' => count($facultyLoads)
];
?>
<div id="epmRoot" class="epm-page">
    <div class="epm-shell">
        <div class="epm-header">
            <div class="epm-header-copy">
                <h1>Extension Program Management</h1>
                <p>Manage and organize community extension programs.</p>
            </div>
            <button type="button" class="epm-btn epm-btn-primary epm-add-program-btn">+ Add New Program</button>
        </div>

        <div class="epm-stats" aria-label="Program statistics">
            <div class="epm-stat-card">
                <div class="epm-stat-label">Total Programs</div>
                <div class="epm-stat-value">24</div>
                <div class="epm-stat-meta">Registered programs</div>
            </div>
            <div class="epm-stat-card">
                <div class="epm-stat-label">Planning</div>
                <div class="epm-stat-value">5</div>
                <div class="epm-stat-meta">In proposal stage</div>
            </div>
            <div class="epm-stat-card">
                <div class="epm-stat-label">Ongoing</div>
                <div class="epm-stat-value">8</div>
                <div class="epm-stat-meta">Active implementation</div>
            </div>
            <div class="epm-stat-card">
                <div class="epm-stat-label">Completed</div>
                <div class="epm-stat-value">11</div>
                <div class="epm-stat-meta">Finished projects</div>
            </div>
        </div>

        <div class="epm-tabs" aria-label="Program navigation tabs">
            <button type="button" class="epm-tab active" data-epm-tab="programs">Extension Programs</button>
            <button type="button" class="epm-tab" data-epm-tab="planning">Program Planning</button>
            <button type="button" class="epm-tab" data-epm-tab="monitoring">Monitoring &amp; Evaluation</button>
        </div>

        <section class="epm-panel active" data-epm-panel="programs">
            <div class="epm-section-header">
                <div>
                    <h2 class="epm-panel-title">Extension Programs</h2>
                </div>
            </div>

            <div class="epm-filters" aria-label="Program filters">
                <div class="epm-filter-group epm-filter-search">
                    <label for="epmProgramSearch" class="epm-label">Program Title</label>
                    <input id="epmProgramSearch" class="epm-input" type="text" placeholder="Search program title..." />
                </div>

                <div class="epm-filter-group">
                    <label for="epmCategoryFilter" class="epm-label">Program Category</label>
                    <select id="epmCategoryFilter" class="epm-select">
                        <option value="All Categories">All Categories</option>
                        <option value="Literacy">Literacy</option>
                        <option value="Health">Health</option>
                        <option value="Livelihood">Livelihood</option>
                        <option value="Environmental">Environmental</option>
                        <option value="Community Development">Community Development</option>
                        <option value="Other Outreach">Other Outreach</option>
                    </select>
                </div>

                <div class="epm-filter-group">
                    <label for="epmStatusFilter" class="epm-label">Program Status</label>
                    <select id="epmStatusFilter" class="epm-select">
                        <option value="All Status">All Status</option>
                        <option value="Planning">Planning</option>
                        <option value="Proposed">Proposed</option>
                        <option value="Approved">Approved</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="epm-table-wrap">
                <table class="epm-table" aria-label="Extension programs table">
                    <thead>
                        <tr>
                            <th>Program Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Target Beneficiaries</th>
                            <th>Implementation Period</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="epmProgramTableBody"></tbody>
                </table>
            </div>
        </section>

        <section class="epm-panel" data-epm-panel="planning">
            <div class="epm-section-header">
                <div>
                    <h2 class="epm-panel-title">Program Planning</h2>
                </div>
                <button type="button" class="epm-btn epm-btn-primary epm-open-proposal-modal">+ Create Program Proposal</button>
            </div>

            <div class="epm-table-wrap">
                <table class="epm-table" aria-label="Program proposals table">
                    <thead>
                        <tr>
                            <th>Proposal Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Lead Office</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="epmProposalTableBody"></tbody>
                </table>
            </div>
        </section>

        <section class="epm-panel" data-epm-panel="monitoring">
            <div class="epm-section-header">
                <div>
                    <h2 class="epm-panel-title">Monitoring &amp; Evaluation</h2>
                </div>
            </div>

            <div class="epm-monitor-grid">
                <div class="epm-monitor-card">
                    <h3>Program Status Monitoring</h3>
                    <div class="epm-monitor-row">
                        <span>Livelihood Support Program</span>
                        <span class="epm-monitor-status epm-status-ongoing">Ongoing</span>
                    </div>
                    <div class="epm-progress-wrap">
                        <div class="epm-progress-bar" style="width: 68%"></div>
                    </div>
                    <p class="epm-monitor-text">Implementation Progress: 68%</p>
                </div>

                <div class="epm-monitor-card">
                    <h3>Activities and Accomplishments</h3>
                    <ul class="epm-list">
                        <li>12 community sessions completed</li>
                        <li>185 beneficiaries reached</li>
                        <li>5 partner agencies engaged</li>
                    </ul>
                </div>

                <div class="epm-monitor-card">
                    <h3>Program Evaluation</h3>
                    <p class="epm-monitor-text">Evaluation Results: Strong participation and visible community impact across target communities.</p>
                    <div class="epm-evaluation-box">
                        <strong>Beneficiary Feedback:</strong>
                        <p>Programs are relevant, timely, and well-supported by the community volunteers and local partners.</p>
                    </div>
                </div>

                <div class="epm-monitor-card">
                    <h3>Recommendations</h3>
                    <ul class="epm-list">
                        <li>Increase digital tracking for volunteer attendance</li>
                        <li>Expand livelihood modules to adjacent barangays</li>
                        <li>Strengthen monitoring schedule for the next cycle</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <div id="epmProposalModal" class="epm-modal" aria-hidden="true">
        <div class="epm-modal-backdrop" data-epm-close="modal"></div>
        <div class="epm-modal-card" role="dialog" aria-modal="true" aria-labelledby="epmModalTitle">
            <button type="button" class="epm-modal-close" data-epm-close="modal" aria-label="Close">×</button>
            <div class="epm-modal-body">
                <h3 id="epmModalTitle">Create Program Proposal</h3>
                <div class="epm-form-grid">
                    <div class="epm-field">
                        <label class="epm-label">Program Description</label>
                        <textarea class="epm-textarea">Community-led literacy and digital skills program designed to improve learning access across barangays.</textarea>
                    </div>
                    <div class="epm-field">
                        <label class="epm-label">Objectives</label>
                        <textarea class="epm-textarea">Improve literacy, strengthen digital access, and build local facilitators.</textarea>
                    </div>
                    <div class="epm-field">
                        <label class="epm-label">Expected Outcomes</label>
                        <textarea class="epm-textarea">Higher learning engagement, improved learner retention, and stronger community participation.</textarea>
                    </div>
                    <div class="epm-field">
                        <label class="epm-label">Target Beneficiaries</label>
                        <input class="epm-input" type="text" value="200 learners and 25 volunteer facilitators" />
                    </div>
                    <div class="epm-field">
                        <label class="epm-label">Implementation Plan</label>
                        <textarea class="epm-textarea">Three-phase rollout with site validation, training, and community-based delivery.</textarea>
                    </div>
                    <div class="epm-field">
                        <label class="epm-label">Proposed Schedule</label>
                        <input class="epm-input" type="text" value="January 2027 to April 2027" />
                    </div>
                    <div class="epm-field epm-field-full">
                        <label class="epm-label">Required Resources</label>
                        <textarea class="epm-textarea">Learning materials, training kits, internet connectivity support, venue access, and volunteer coordination.</textarea>
                    </div>
                </div>
                <div class="epm-modal-actions">
                    <button type="button" class="epm-btn epm-btn-secondary" data-epm-close="modal">Close</button>
                    <button type="button" class="epm-btn epm-btn-primary">Save Proposal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="sectionSemesterOptionsData">
<?php echo json_encode($semesterOptions, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>
</script>

<script type="application/json" id="subjectCatalogDetailsData"><?php echo json_encode($subjectAssignmentsById, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?></script>

