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
    <!-- Page Header -->
    <div class="module-header">
        <div>
            <h1>Academics Management</h1>
            <p>Monitor programs, curriculum, subjects, sections, and faculty load</p>
        </div>
    </div>


    <div class="module-content">
    <div class="stats-grid">
        <div class="stat-card">
            <h6>Programs</h6>
            <h3><?php echo $stats['programs']; ?></h3>
        </div>
        <div class="stat-card subjects">
            <h6>Subjects</h6>
            <h3><?php echo $stats['subjects']; ?></h3>
        </div>
        <div class="stat-card sections">
            <h6>Sections</h6>
            <h3><?php echo $stats['sections']; ?></h3>
        </div>
        <div class="stat-card faculty">
            <h6>Faculty Load</h6>
            <h3><?php echo $stats['faculty']; ?></h3>
        </div>
    </div>

    <!-- Tabs -->
    <div class="academics-tabs">
        <button class="academics-tab active" data-tab="programs">
            <i class="fas fa-book"></i> Programs & Curriculum
        </button>
        <button class="academics-tab" data-tab="subjects">
            <i class="fas fa-list"></i> Subjects
        </button>
        <button class="academics-tab" data-tab="sections">
            <i class="fas fa-layer-group"></i> Sections
        </button>
        <button class="academics-tab" data-tab="faculty">
            <i class="fas fa-users"></i> Faculty Load
        </button>
    </div>

    <!-- TAB: Programs & Curriculum -->
    <div class="academics-panel active" id="tab-programs">
        <div class="section-header">
            <h3>Programs & Curriculum</h3>
            <p class="text-muted" style="margin: 0;"><small>Read-only monitoring</small></p>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchPrograms" placeholder="Search programs...">
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="programsTable">
                    <thead>
                        <tr>
                            <th>Program Code</th>
                            <th>Program Name</th>
                            <th>Effective Year</th>
                            <th>Active Curriculum</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($programs)): ?>
                            <?php foreach ($programs as $prog): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prog['code'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($prog['name'] ?? 'Program'); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($prog['effective_year']) ? $prog['effective_year'] : 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($prog['is_active']) ? ($prog['is_active'] == 1 ? 'Active' : 'Inactive') : 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewCurriculumDetails(<?php echo (int)($prog['id'] ?? 0); ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No programs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Subjects -->
    <div class="academics-panel" id="tab-subjects">
        <div class="section-header">
            <h3>Subject Catalog</h3>
        </div>

        <div class="card mb-3 compact-filter-card">
            <div class="card-body py-3 px-3 px-md-4">
                <!-- Filter Row 1: Search, Course, Year Level -->
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-6 filter-col">
                        <label for="searchSubjects" class="form-label">Search</label>
                        <input type="text" id="searchSubjects" class="form-control" placeholder="Search subjects...">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 filter-col">
                        <label for="filterSubjectCourse" class="form-label">Course</label>
                        <select id="filterSubjectCourse" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($programs as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog['code']); ?>"><?php echo htmlspecialchars($prog['code']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 filter-col">
                        <label for="filterSubjectYear" class="form-label">Year Level</label>
                        <select id="filterSubjectYear" class="form-select">
                            <option value="">All Year Levels</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                </div>
                <!-- Filter Row 2: Semester, Status, Reset Filters -->
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-md-4 filter-col">
                        <label for="filterSubjectSemester" class="form-label">Semester</label>
                        <select id="filterSubjectSemester" class="form-select">
                            <option value="">All Semesters</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 filter-col">
                        <label for="filterSubjectStatus" class="form-label">Status</label>
                        <select id="filterSubjectStatus" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end filter-col">
                        <button type="button" id="resetSubjectFilters" class="btn btn-outline-secondary w-100">Reset Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="subjectsTable" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Units</th>
                            <th>Lecture Hours</th>
                            <th>Laboratory Hours</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($subjectCatalog)): ?>
                            <?php foreach ($subjectCatalog as $subj): ?>
                                <tr data-year-level="<?php echo htmlspecialchars($subj['year_level'] ?? ''); ?>"
                                    data-semester="<?php echo htmlspecialchars($subj['semester'] ?? ''); ?>"
                                    data-course="<?php echo htmlspecialchars($subj['course_code'] ?? ''); ?>"
                                    data-status="<?php echo htmlspecialchars(!empty($subj['is_active']) ? 'active' : 'inactive'); ?>">
                                    <td><?php echo htmlspecialchars($subj['code'] ?? 'SUBJ101'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['name'] ?? 'Subject'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['units'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['lecture_hours'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['lab_hours'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['course_code'] ?: 'Unassigned'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['year_level'] ? ($subj['year_level'] === '1' ? '1st Year' : ($subj['year_level'] === '2' ? '2nd Year' : ($subj['year_level'] === '3' ? '3rd Year' : ($subj['year_level'] === '4' ? '4th Year' : $subj['year_level'])))) : 'Unassigned'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['semester'] ? $subj['semester'] . ' Semester' : 'Unassigned'); ?></td>
                                    <td>
                                        <?php if ($subj['curriculum_id'] && $subj['is_active'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($subj['curriculum_id']): ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewSubjectDetails('<?php echo htmlspecialchars($subj['id'] ?? ''); ?>')">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">No subjects found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Sections -->
    <div class="academics-panel" id="tab-sections">
        <div class="section-header">
            <h3>Manage Sections</h3>
        </div>

        <div class="card mb-3 compact-filter-card" id="sectionsFilterCard">
            <div class="card-body py-3 px-3 px-md-4">
                <!-- Filter Row 1: Search (larger) and Program -->
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-8 filter-col">
                        <label for="searchSections" class="form-label">Search Section Code</label>
                        <input type="text" id="searchSections" class="form-control" placeholder="Search section code...">
                    </div>
                    <div class="col-12 col-md-4 filter-col">
                        <label for="filterSectionProgram" class="form-label">Course / Program</label>
                        <select id="filterSectionProgram" class="form-select">
                            <option value="">All Programs</option>
                            <?php foreach ($programs as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog['code']); ?>"><?php echo htmlspecialchars($prog['code']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Filter Row 2: Year Level | Semester | Reset (inline on desktop) -->
                <div class="row g-2">
                    <div class="col-12 col-md-4 filter-col">
                        <div style="display:flex; gap:15px;">
                            <div style="flex:1;">
                                <label for="filterSectionYear" class="form-label">Year Level</label>
                                <select id="filterSectionYear" class="form-select">
                                    <option value="">All Year Levels</option>
                                    <option value="1st">1st Year</option>
                                    <option value="2nd">2nd Year</option>
                                    <option value="3rd">3rd Year</option>
                                    <option value="4th">4th Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 filter-col">
                        <div style="display:flex; gap:15px;">
                            <div style="flex:1;">
                                <label for="filterSectionSemester" class="form-label">Semester</label>
                                <select id="filterSectionSemester" class="form-select">
                                    <option value="">All Semesters</option>
                                    <option value="1st">1st Semester</option>
                                    <option value="2nd">2nd Semester</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end filter-col">
                        <div style="width:100%; display:flex; gap:10px;">
                            <button type="button" id="resetSectionFilters" class="btn btn-outline-secondary" style="flex:1;">Reset Filters</button>
                            <button type="button" id="addSectionBtn" class="btn btn-primary" style="flex:1;" onclick="openAddSectionModal()">Add Section</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="sectionsTable">
                    <thead>
                        <tr>
                            <th>Section Code</th>
                            <th>Year Level</th>
                            <th>Semester</th>
                            <th>School Year</th>
                            <th>Adviser</th>
                            <th>Total Students</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sections)): ?>
                            <?php foreach ($sections as $sec): ?>
                                <?php
                                    $sectionId = $sec['id'] ?? '';
                                    $sectionProgram = $sec['program'] ?? 'N/A';
                                    $sectionYearLevel = $sec['grade_level'] ?? 'N/A';
                                    $sectionSemester = $sec['semester_name'] ?? 'N/A';
                                    $sectionSchoolYear = $sec['school_year_name'] ?? 'N/A';
                                    $adviserName = trim($sec['adviser_name'] ?? '') ?: 'Not Assigned';
                                    $totalStudents = $sec['total_students'] ?? '0';
                                    $statusText = (!empty($sec['status']) && strtolower($sec['status']) === 'inactive') ? 'Inactive' : 'Active';
                                    $statusBadgeClass = $statusText === 'Active' ? 'badge-success' : 'badge-secondary';
                                    $adviserActionLabel = $adviserName === 'Not Assigned' ? 'Assign Adviser' : 'Reassign Adviser';
                                ?>
                                <tr data-section-id="<?php echo htmlspecialchars($sectionId); ?>"
                                    data-program="<?php echo htmlspecialchars($sectionProgram); ?>"
                                    data-year-level="<?php echo htmlspecialchars($sectionYearLevel); ?>"
                                    data-semester="<?php echo htmlspecialchars($sectionSemester); ?>"
                                    data-school-year="<?php echo htmlspecialchars($sectionSchoolYear); ?>"
                                    data-adviser="<?php echo htmlspecialchars($adviserName); ?>"
                                    data-total-students="<?php echo htmlspecialchars($totalStudents); ?>"
                                    data-status="<?php echo htmlspecialchars($statusText); ?>">
                                    <td><?php echo htmlspecialchars($sec['section_code'] ?? 'N/A'); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($sectionYearLevel); ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($sectionSemester); ?></span></td>
                                    <td><?php echo htmlspecialchars($sectionSchoolYear); ?></td>
                                    <td><?php echo htmlspecialchars($adviserName); ?></td>
                                    <td><?php echo htmlspecialchars($totalStudents); ?></td>
                                    <td><span class="badge <?php echo $statusBadgeClass; ?>"><?php echo htmlspecialchars($statusText); ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSectionDetails(this.closest('tr'))">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openAssignAdviserModal(this.closest('tr'))">
                                                <i class="bi bi-person-badge"></i> <?php echo $adviserActionLabel; ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No sections found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Faculty Load -->
    <div class="academics-panel" id="tab-faculty">
        <div class="section-header">
            <h3>Manage Faculty Load</h3>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchFaculty" placeholder="Search faculty...">
            </div>
            <select class="filter-select" id="filterLoadStatus">
                <option value="">All Status</option>
                <option value="Fully Loaded">Fully Loaded</option>
                <option value="Underloaded">Underloaded</option>
                <option value="Overloaded">Overloaded</option>
            </select>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="facultyTable">
                    <thead>
                        <tr>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Assigned Sections</th>
                            <th>Assigned Subjects</th>
                            <th>Teaching Units</th>
                            <th>Maximum Load</th>
                            <th>Load Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facultyLoads)): ?>
                            <?php foreach ($facultyLoads as $fac): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?></td>
                                    <td><?php echo intval($fac['assigned_sections'] ?? 0); ?></td>
                                    <td><?php echo intval($fac['assigned_subjects'] ?? 0); ?></td>
                                    <td><?php echo intval($fac['teaching_units'] ?? 0); ?></td>
                                    <td><?php echo intval($fac['max_load'] ?? 15); ?></td>
                                    <td>
                                        <?php
                                        $loadStatus = $fac['load_status'] ?? 'Underloaded';
                                        $badgeClassMap = [
                                            'Underloaded' => 'badge-warning',
                                            'Fully Loaded' => 'badge-success',
                                            'Overloaded' => 'badge-danger'
                                        ];
                                        $badgeClass = $badgeClassMap[$loadStatus] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($loadStatus); ?></span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-sm btn-info" onclick="openAssignFacultyLoadModal('<?php echo $fac['id'] ?? 'N/A'; ?>', '<?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?>', '<?php echo intval($fac['teaching_units'] ?? 0); ?>', '<?php echo intval($fac['max_load'] ?? 15); ?>', '<?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?>')">
                                                Assign Instructor
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="openAssignFacultySubjectModal('<?php echo $fac['id'] ?? 'N/A'; ?>', '<?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?>', '<?php echo intval($fac['teaching_units'] ?? 0); ?>', '<?php echo intval($fac['max_load'] ?? 15); ?>', '<?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?>')">
                                                Assign Subjects
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="viewFacultyLoadDetails('<?php echo $fac['id'] ?? 'N/A'; ?>', '<?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?>', '<?php echo intval($fac['teaching_units'] ?? 0); ?>', '<?php echo intval($fac['max_load'] ?? 15); ?>', '<?php echo intval($fac['assigned_sections'] ?? 0); ?>', '<?php echo intval($fac['assigned_subjects'] ?? 0); ?>', '<?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?>')">
                                                View Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No faculty loads found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    </div>
    <!-- Statistics Cards -->
    
<!-- MODALS -->

<!-- Curriculum Details Modal -->
<div class="modal" id="curriculumDetailsModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">Curriculum Details</h5>
            <button type="button" class="modal-close" onclick="closeCurriculumModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Program</label>
                <input type="text" class="form-control" id="currProgramName" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Curriculum</label>
                <input type="text" class="form-control" id="currVersion" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Effective Year</label>
                <input type="text" class="form-control" id="currUnits" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" id="currStatus" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Subject Flow</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto;">
                    <div id="subjectFlowList"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Enrollment & Curriculum Summary</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <div id="enrollmentSummaryContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subject Details Modal -->
<div class="modal" id="subjectDetailsModal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Subject Details</h5>
            <button type="button" class="modal-close" onclick="closeSubjectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="card mb-3">
                <div class="card-header">Subject Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subjCode" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Units</label>
                            <input type="text" class="form-control" id="subjUnits" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lecture Hours</label>
                            <input type="text" class="form-control" id="subjLectureHours" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subjName" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Laboratory Hours</label>
                            <input type="text" class="form-control" id="subjLabHours" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Curriculum Assignment</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="subjectAssignmentTable">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Curriculum Name</th>
                                    <th>Effective Year</th>
                                    <th>Year Level</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="subjectAssignmentBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Loading assignments...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <p id="subjectAssignmentStatus" class="mb-0"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal" id="sectionModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title" id="sectionModalTitle">Add Section</h5>
            <button type="button" class="modal-close" onclick="closeSectionModal()">&times;</button>
        </div>
        <form id="sectionForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Section Code <span class="required">*</span></label>
                    <input type="text" class="form-control" id="sectionCode" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Grade Level <span class="required">*</span></label>
                    <input type="text" class="form-control" id="sectionGradeLevel" maxlength="50" placeholder="e.g., 1st Year" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Program <span class="required">*</span></label>
                    <select class="form-control" id="sectionProgram" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programs as $prog): ?>
                            <option value="<?php echo (int)($prog['id'] ?? 0); ?>"><?php echo htmlspecialchars(($prog['code'] ?? '') . ' - ' . ($prog['name'] ?? '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">School Year <span class="required">*</span></label>
                    <select class="form-control" id="sectionSchoolYear" required>
                        <option value="">Select School Year</option>
                        <?php foreach ($schoolYearOptions as $schoolYear): ?>
                            <option value="<?php echo (int)($schoolYear['id'] ?? 0); ?>"><?php echo htmlspecialchars($schoolYear['name'] ?? ''); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Semester <span class="required">*</span></label>
                    <select class="form-control" id="sectionSemester" required disabled>
                        <option value="">Select Semester</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveSectionBtn">Save Section</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Adviser Modal -->
<div class="modal" id="adviserModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title" id="adviserModalTitle">Assign Adviser</h5>
            <button type="button" class="modal-close" onclick="closeAdviserModal()">&times;</button>
        </div>
        <form id="adviserForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="adviserSectionId">

                <div class="card mb-3">
                    <div class="card-header">Section Information</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Section Code</label>
                                <p class="mb-0" id="adviserSectionCode">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year Level</label>
                                <p class="mb-0" id="adviserSectionYear">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Semester</label>
                                <p class="mb-0" id="adviserSectionSemester">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School Year</label>
                                <p class="mb-0" id="adviserSectionSchoolYear">N/A</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Current Adviser</div>
                    <div class="card-body">
                        <p class="mb-0" id="adviserCurrentAdviser">Not Assigned</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Faculty Assignment</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label" for="adviserSelect">Select Faculty Member <span class="required">*</span></label>
                            <select class="form-control" id="adviserSelect" required>
                                <option value="">Choose Faculty Member</option>
                                <?php foreach ($facultyLoads as $faculty): ?>
                                    <option value="<?php echo htmlspecialchars($faculty['faculty_name']); ?>" data-id="<?php echo htmlspecialchars($faculty['id']); ?>">
                                        <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAdviserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="adviserActionButton">Assign Adviser</button>
            </div>
        </form>
    </div>
</div> 

<!-- Section Details Modal -->
<div class="modal" id="sectionDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title">Section Details</h5>
            <button type="button" class="modal-close" onclick="closeSectionDetailsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="card mb-3">
                <div class="card-header">Section Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Code</label>
                            <p class="mb-0" id="sectionDetailsCode">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year Level</label>
                            <p class="mb-0" id="sectionDetailsYear">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <p class="mb-0" id="sectionDetailsSemester">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">School Year</label>
                            <p class="mb-0" id="sectionDetailsSchoolYear">N/A</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Enrollment</div>
                <div class="card-body">
                    <p class="mb-0" id="sectionDetailsTotalStudents">0</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Faculty</div>
                <div class="card-body">
                    <p class="mb-0" id="sectionDetailsAdviser">Not Assigned</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <span class="badge badge-success" id="sectionDetailsStatus">Active</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeSectionDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- Add/Edit Faculty Load Modal -->
<div class="modal" id="facultyLoadModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title" id="facultyLoadModalTitle">Assign Faculty Load</h5>
            <button type="button" class="modal-close" onclick="closeFacultyLoadModal()">&times;</button>
        </div>
        <form id="facultyLoadForm">
            <div class="modal-body">
                <input type="hidden" id="facultyLoadId">
                <div class="form-group">
                    <label class="form-label">Faculty Member <span class="required">*</span></label>
                    <select class="form-control" id="facultySelect" required>
                        <option value="">Select Faculty</option>
                        <option value="Dr. Maria Santos">Dr. Maria Santos</option>
                        <option value="Prof. Juan Reyes">Prof. Juan Reyes</option>
                        <option value="Dr. Rita Ocampo">Dr. Rita Ocampo</option>
                        <option value="Prof. Ramon Miguel">Prof. Ramon Miguel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Section/Class <span class="required">*</span></label>
                    <select class="form-control" id="loadSectionSelect" required>
                        <option value="">Select Section</option>
                        <option value="IT-1A">IT-1A</option>
                        <option value="IT-1B">IT-1B</option>
                        <option value="CS-2A">CS-2A</option>
                        <option value="EN-3A">EN-3A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject Code <span class="required">*</span></label>
                    <input type="text" class="form-control" id="loadSubject" placeholder="e.g., SUBJ101" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Load (Units) <span class="required">*</span></label>
                    <input type="number" class="form-control" id="loadUnits" min="1" max="6" value="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Schedule <span class="required">*</span></label>
                    <input type="text" class="form-control" id="loadSchedule" placeholder="e.g., MWF 9:00-10:30" required>
                </div>
                <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 15px; display: none;" id="conflictWarning">
                    <small><strong>⚠️ Schedule Conflict Detected:</strong> This faculty member has another class during this time.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFacultyLoadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Load</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Sections Modal -->
<div class="modal" id="assignFacultyLoadModal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Assign Instructor</h5>
            <button type="button" class="modal-close" onclick="closeAssignFacultyLoadModal()">&times;</button>
        </div>
        <form id="assignFacultyLoadForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="assignLoadFacultyId">
                
                <!-- Faculty Information Section -->
                <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0d6efd;">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">Faculty Information</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Faculty Name</label>
                            <input type="text" class="form-control" id="assignLoadFacultyName" readonly>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" id="assignLoadDepartment" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Assignment Details Section -->
                <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">Assignment Details</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">School Year <span class="required">*</span></label>
                            <select class="form-select" id="assignLoadSchoolYear" required>
                                <option value="">Select School Year</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Semester <span class="required">*</span></label>
                            <select class="form-select" id="assignLoadSemester" required disabled>
                                <option value="">Select Semester</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Sections <span class="required">*</span></label>
                        <select class="form-select" id="assignLoadSection" multiple size="8" required>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple sections.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignFacultyLoadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Instructor Assignments</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Faculty Subjects Modal -->
<div class="modal" id="assignFacultySubjectModal">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h5 class="modal-title">Assign Subjects</h5>
            <button type="button" class="modal-close" onclick="closeAssignFacultySubjectModal()">&times;</button>
        </div>
        <form id="assignFacultySubjectForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="assignSubjectFacultyId">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0d6efd;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="form-label">Faculty Name</label>
                            <input type="text" class="form-control" id="assignSubjectFacultyName" readonly>
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" id="assignSubjectDepartment" readonly>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div>
                            <label class="form-label">Maximum Load</label>
                            <input type="text" class="form-control" id="assignSubjectMaxLoad" readonly>
                        </div>
                        <div>
                            <label class="form-label">Current Teaching Load</label>
                            <input type="text" class="form-control" id="assignSubjectCurrentLoad" readonly>
                        </div>
                        <div>
                            <label class="form-label">Remaining Load</label>
                            <input type="text" class="form-control" id="assignSubjectRemainingLoad" readonly>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div>
                            <label class="form-label">Section <span class="required">*</span></label>
                            <select class="form-select" id="assignSubjectSection" required>
                                <option value="">Select Section</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Subject <span class="required">*</span></label>
                            <select class="form-select" id="assignSubjectSubject" required disabled>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignFacultySubjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Subject Assignment</button>
            </div>
        </form>
    </div>
</div>

<!-- Faculty Load Details Modal -->
<div class="modal" id="facultyLoadDetailsModal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Faculty Load Details</h5>
            <button type="button" class="modal-close" onclick="closeFacultyLoadDetailsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Faculty Information Section -->
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0d6efd;">
                <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">Faculty Information</h6>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Faculty Name</strong></label>
                        <p id="detailFacultyName" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6;"></p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Department</strong></label>
                        <p id="detailDepartment" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6;"></p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Maximum Load</strong></label>
                        <p id="detailMaxLoad" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: 600;"></p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Teaching Units</strong></label>
                        <p id="detailTeachingUnits" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: 600;"></p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Remaining Load</strong></label>
                        <p id="detailRemainingLoad" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #28a745;"></p>
                    </div>
                </div>
            </div>

            <!-- Assigned Sections Section -->
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Assigned Instructor Sections <span id="detailAssignedSectionsCount" style="background: #0d6efd; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">0</span></h6>
                <div id="detailAssignedSections" style="margin: 0; padding: 10px; background: white; border-radius: 3px; border: 1px solid #dee2e6; color: #6c757d;">
                    No instructor sections assigned
                </div>
            </div>
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Assigned Adviser Sections <span id="detailAssignedAdviserSectionsCount" style="background: #0d6efd; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">0</span></h6>
                <div id="detailAssignedAdviserSections" style="margin: 0; padding: 10px; background: white; border-radius: 3px; border: 1px solid #dee2e6; color: #6c757d;">
                    No adviser sections assigned
                </div>
            </div>

            <!-- Assigned Subjects Section -->
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Assigned Subjects <span id="detailAssignedSubjectsCount" style="background: #0d6efd; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">0</span></h6>
                <div id="detailAssignedSubjects" style="margin: 0; padding: 10px; background: white; border-radius: 3px; border: 1px solid #dee2e6; color: #6c757d;">No subjects assigned</div>
            </div>

            <!-- Class Schedule Section -->
            <div style="padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Schedule Status</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: white; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Subject Code</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Section</th>
                                <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Day</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Time</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Room</th>
                            </tr>
                        </thead>
                        <tbody id="detailScheduleList">
                            <tr>
                                <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">No schedules assigned</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <details style="margin-top: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <summary style="cursor: pointer; font-weight: 600; color: #333; font-size: 1rem;">Faculty Load History</summary>
                <div id="detailLoadHistory" style="margin-top: 15px; color: #6c757d;">Loading historical assignments...</div>
            </details>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeFacultyLoadDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- Section Students Modal -->
<div class="modal" id="sectionStudentsModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">Section Students</h5>
            <button type="button" class="modal-close" onclick="closeSectionStudentsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" class="form-control" id="sectionStudentSearch" placeholder="Search students..." style="margin-bottom: 15px;">
            <div style="max-height: 400px; overflow-y: auto;">
                <table style="width: 100%; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 8px; text-align: left;">Student ID</th>
                            <th style="padding: 8px; text-align: left;">Name</th>
                            <th style="padding: 8px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="sectionStudentsList">
                        <tr>
                            <td colspan="3" style="padding: 8px; text-align: center; color: #6c757d;">No students loaded</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="sectionSemesterOptionsData">
<?php echo json_encode($semesterOptions, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>
</script>

<script type="application/json" id="subjectCatalogDetailsData"><?php echo json_encode($subjectAssignmentsById, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?></script>

<link rel="stylesheet" href="css/pages/academics-management.css">