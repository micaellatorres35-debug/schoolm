<?php $pageTitle = 'GenEd Activities'; ?>
<div class="module-header gc-activities-header ge-curriculum-header">
    <div>
        <h1>GenEd Activities</h1>
        <p>Create activities for assigned subjects, select eligible teachers, and track approval status.</p>
    </div>
    <button type="button" class="btn btn-primary" id="gcActivityAddButton" hidden>
        <i class="fas fa-plus" aria-hidden="true"></i> Add Activity
    </button>
</div>

<section class="module-content academics-management-page gc-activities-page" id="gcAcademicActivities">
    <div class="stats-grid gc-activity-summary" aria-label="GenEd activity totals">
        <article class="stat-card gc-activity-stat">
            <div><h6>Total Activities</h6><h3 id="gcActivityTotal">—</h3></div>
            <span class="gc-activity-stat-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card gc-activity-stat">
            <div><h6>Pending Approval</h6><h3 id="gcActivityPending">—</h3></div>
            <span class="gc-activity-stat-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card gc-activity-stat">
            <div><h6>Approved</h6><h3 id="gcActivityApproved">—</h3></div>
            <span class="gc-activity-stat-icon"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card gc-activity-stat">
            <div><h6>Completed</h6><h3 id="gcActivityCompleted">—</h3></div>
            <span class="gc-activity-stat-icon"><i class="fas fa-flag-checkered" aria-hidden="true"></i></span>
        </article>
    </div>

    <p class="gc-activity-notice" id="gcActivityNotice" role="status" aria-live="polite">Loading GenEd activities...</p>

    <div class="card compact-filter-card gc-activity-filter-card">
        <div class="card-body gc-activity-filter-grid">
            <label>
                <span>Academic Year</span>
                <select id="gcActivitySchoolYear" class="form-select"><option value="">Loading academic year...</option></select>
            </label>
            <label>
                <span>Semester</span>
                <select id="gcActivitySemester" class="form-select"><option value="">All Semesters</option></select>
            </label>
            <label>
                <span>Department</span>
                <select id="gcActivityDepartment" class="form-select"><option value="">All Departments</option></select>
            </label>
            <label>
                <span>Section</span>
                <select id="gcActivitySection" class="form-select"><option value="">All Sections</option></select>
            </label>
            <label>
                <span>GenEd Subject</span>
                <select id="gcActivitySubject" class="form-select"><option value="">All Subjects</option></select>
            </label>
            <label>
                <span>Assigned Teacher</span>
                <select id="gcActivityFaculty" class="form-select"><option value="">All Faculty</option></select>
            </label>
            <label>
                <span>Category</span>
                <select id="gcActivityCategoryFilter" class="form-select"><option value="">All Categories</option></select>
            </label>
            <label>
                <span>Status</span>
                <select id="gcActivityStatusFilter" class="form-select"><option value="">All Statuses</option></select>
            </label>
            <label class="gc-activity-search">
                <span>Search</span>
                <input type="search" id="gcActivitySearch" class="form-control" placeholder="Search activities, subjects, sections, or faculty">
            </label>
        </div>
    </div>

    <div class="gc-activity-table-heading">
        <h2>Activities by Subject and Section</h2>
        <span id="gcActivityResultCount">0 records</span>
    </div>
    <div class="table-card gc-activity-table-card">
        <div class="table-responsive">
            <table id="gcActivitiesTable">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>GenEd Subject</th>
                        <th>Department</th>
                        <th>Section</th>
                        <th>Assigned Teachers</th>
                        <th>Category</th>
                        <th>Activity Date</th>
                        <th>Academic Period</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="10" class="gc-activity-empty">Loading activities...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal" id="gcActivityModal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="gcActivityModalTitle">
            <div class="modal-header">
                <h2 class="modal-title" id="gcActivityModalTitle">Add GenEd Activity</h2>
                <button type="button" class="modal-close" data-close-activity aria-label="Close">&times;</button>
            </div>
            <form id="gcActivityForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="gcActivityTitle" class="form-label">Activity Title</label>
                        <input type="text" id="gcActivityTitle" class="form-control" maxlength="200" required>
                    </div>
                    <div class="form-group">
                        <label for="gcActivityAssignment" class="form-label">Subject and Section</label>
                        <select id="gcActivityAssignment" class="form-select" required>
                            <option value="">Loading eligible subject assignments...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gcActivityTeachers" class="form-label">Eligible Teachers</label>
                        <select id="gcActivityTeachers" class="form-select" multiple size="5" required disabled></select>
                        <small class="text-muted">Only active teachers assigned to this subject and section are listed. Hold Ctrl to select multiple teachers.</small>
                    </div>
                    <div class="gc-activity-form-grid">
                        <div class="form-group">
                            <label for="gcActivityCategory" class="form-label">Category</label>
                            <select id="gcActivityCategory" class="form-select" required></select>
                        </div>
                        <div class="form-group" id="gcExamPeriodGroup" hidden>
                            <label for="gcExamPeriod" class="form-label">Exam Period</label>
                            <select id="gcExamPeriod" class="form-select">
                                <option value="">Select exam period</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gcActivityDate" class="form-label">Activity Date</label>
                            <input type="date" id="gcActivityDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="gcActivityDescription" class="form-label">Description</label>
                        <textarea id="gcActivityDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <p class="gc-activity-form-message" id="gcActivityFormMessage" role="status" aria-live="polite"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-close-activity>Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</section>
