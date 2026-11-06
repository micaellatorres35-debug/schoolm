<?php $pageTitle = 'General Education Curriculum'; ?>
<div class="module-header ge-curriculum-header">
    <div>
        <h1>General Education Curriculum</h1>
        <p>Manage GE subjects and faculty assignments.</p>
    </div>
    <button type="button" class="btn btn-primary" id="geAddSubjectBtn" data-ge-header-action="subjects">
        <i class="fas fa-plus"></i> Add Subject
    </button>
    <button type="button" class="btn btn-primary" id="geAssignFacultyBtn" data-ge-header-action="assignments" hidden>
        <i class="fas fa-user-plus"></i> Assign Faculty
    </button>
</div>

<div class="module-content academics-management-page ge-curriculum-page" id="geCurriculumModule">
    <div class="stats-grid ge-stats-grid" data-ge-summary="subjects" aria-label="Active General Education subject totals">
        <article class="stat-card ge-stat-card">
            <div><h6>Total Subjects</h6><h3 id="geTotalSubjects">--</h3></div>
            <span class="ge-stat-icon"><i class="fas fa-book-open" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card ge-stat-card">
            <div><h6>Total Units</h6><h3 id="geTotalUnits">--</h3></div>
            <span class="ge-stat-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card ge-stat-card">
            <div><h6>Lecture</h6><h3 id="geTotalLecture">--</h3></div>
            <span class="ge-stat-icon"><i class="fas fa-chalkboard" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card ge-stat-card">
            <div><h6>Laboratory</h6><h3 id="geTotalLaboratory">--</h3></div>
            <span class="ge-stat-icon"><i class="fas fa-flask" aria-hidden="true"></i></span>
        </article>
    </div>

    <div class="stats-grid ge-stats-grid" data-ge-summary="assignments" aria-label="Faculty assignment totals" hidden>
        <article class="stat-card ge-stat-card">
            <div><h6>Total Faculty</h6><h3 id="geTotalFaculty">--</h3><small>All faculty members</small></div>
            <span class="ge-stat-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card ge-stat-card">
            <div><h6>Sections</h6><h3 id="geTotalAssignedSections">--</h3><small>Assigned sections</small></div>
            <span class="ge-stat-icon"><i class="fas fa-users-rectangle" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card ge-stat-card">
            <div><h6>Subjects</h6><h3 id="geTotalAssignedSubjects">--</h3><small>Assigned subjects</small></div>
            <span class="ge-stat-icon"><i class="fas fa-book" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card ge-stat-card">
            <div><h6>Underloaded</h6><h3 id="geUnderloadedFaculty">--</h3><small>Below teaching load</small></div>
            <span class="ge-stat-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
        </article>
    </div>

    <div class="card compact-filter-card ge-filter-card" data-ge-filters="subjects">
        <div class="card-body ge-filter-grid">
            <div>
                <label for="geSubjectCategoryFilter" class="form-label">Category</label>
                <select id="geSubjectCategoryFilter" class="form-select">
                    <option value="all">All Categories</option>
                    <option value="general-education">General Education</option>
                    <option value="filipino">Filipino</option>
                    <option value="nstp">NSTP</option>
                    <option value="pe">Physical Education</option>
                </select>
            </div>
            <div>
                <label for="geSubjectStatusFilter" class="form-label">Status</label>
                <select id="geSubjectStatusFilter" class="form-select">
                    <option value="all">All Statuses</option>
                    <option value="active" selected>Active</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card compact-filter-card ge-filter-card" data-ge-filters="assignments" hidden>
        <div class="card-body ge-filter-grid">
            <div>
                <label for="geFacultyDepartmentFilter" class="form-label">Department</label>
                <select id="geFacultyDepartmentFilter" class="form-select">
                    <option value="all">All Departments</option>
                </select>
            </div>
            <div>
                <label for="geFacultyLoadFilter" class="form-label">Load Status</label>
                <select id="geFacultyLoadFilter" class="form-select">
                    <option value="all">All</option>
                    <option value="Underloaded">Underloaded</option>
                    <option value="Fully Loaded">Fully Loaded</option>
                    <option value="Overloaded">Overloaded</option>
                </select>
            </div>
        </div>
    </div>

    <div class="academics-tabs" role="tablist" aria-label="General Education Curriculum">
        <button type="button" class="academics-tab" data-ge-tab="subjects" role="tab" aria-selected="false">GE Subject Management</button>
        <button type="button" class="academics-tab active" data-ge-tab="assignments" role="tab" aria-selected="true">
            <i class="fas fa-chalkboard-teacher"></i> Faculty Assignments
        </button>
    </div>

    <section class="academics-panel" id="ge-panel-subjects" data-ge-panel="subjects">
        <div class="section-header">
            <h3>GE Subjects</h3>
        </div>
        <div class="search-filter-container">
            <div class="search-box">
                <input type="search" id="geSubjectSearch" placeholder="Search subjects..." aria-label="Search General Education subjects">
            </div>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table id="geSubjectsTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Units</th>
                            <th>Lecture</th>
                            <th>Laboratory</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="7" class="text-center text-muted">Loading GenEd subjects...</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="academics-panel active" id="ge-panel-assignments" data-ge-panel="assignments">
        <div class="section-header">
            <h3>Faculty Assignments</h3>
            <p class="text-muted" style="margin: 0;"><small>Assign faculty to sections, then assign GE curriculum subjects.</small></p>
        </div>
        <div class="search-filter-container ge-assignment-search">
            <div class="search-box">
                <input type="search" id="geFacultyAssignmentSearch" placeholder="Search faculty assignments..." aria-label="Search faculty assignments">
            </div>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table id="facultyAssignmentsTable">
                    <thead>
                        <tr>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Sections</th>
                            <th>Subjects</th>
                            <th>Teaching Units</th>
                            <th>Load Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center text-muted">Loading faculty assignments...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div>

<div class="modal" id="geSubjectModal">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h5 class="modal-title" id="geSubjectModalTitle">Add GE Subject</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="geSubjectForm">
            <div class="modal-body">
                <input type="hidden" id="geSubjectId">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="geSubjectCode" class="form-label">Subject Code</label>
                        <input type="text" id="geSubjectCode" class="form-control" maxlength="250" pattern="(GE([A-Za-z0-9-]+| [A-Za-z0-9-]+( [A-Za-z0-9-]+)*)|FIL [1-3]|NSTP [1-2]|PE [1-2])" placeholder="e.g. GE 10" title="Use a GE code (including codes such as GE 1 or GE Elec 1), FIL 1-3, NSTP 1-2, or PE 1-2." required>
                    </div>
                    <div class="col-12 col-md-8">
                        <label for="geSubjectName" class="form-label">Subject Name</label>
                        <input type="text" id="geSubjectName" class="form-control" maxlength="250" required>
                    </div>
                    <div class="col-12">
                        <label for="geSubjectDescription" class="form-label">Subject Description</label>
                        <textarea id="geSubjectDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="geSubjectUnits" class="form-label">Units</label>
                        <input type="number" id="geSubjectUnits" class="form-control" min="0" max="30" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="geSubjectLecture" class="form-label">Lecture Hours</label>
                        <input type="number" id="geSubjectLecture" class="form-control" min="0" max="60" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="geSubjectLab" class="form-label">Laboratory Hours</label>
                        <input type="number" id="geSubjectLab" class="form-control" min="0" max="60" required>
                    </div>
                </div>
                <div id="geSubjectMessage" class="mt-2" role="status" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Subject</button>
            </div>
        </form>
    </div>
</div>

<div id="fmAssignSectionsModal" class="modal">
    <div class="modal-dialog" style="max-width: 720px;">
        <div class="modal-header">
            <h5 class="modal-title">Assign Faculty to Sections</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="fmAssignSectionsForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="fmAssignSectionsFacultyId">
                <div class="form-group">
                    <label class="form-label">Faculty</label>
                    <input type="text" class="form-control" id="fmAssignSectionsFacultyName" readonly>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 form-group">
                        <label for="fmAssignSectionsSchoolYear" class="form-label">School Year</label>
                        <select id="fmAssignSectionsSchoolYear" class="form-select" required>
                            <option value="">Loading school years...</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="fmAssignSectionsSemester" class="form-label">Semester</label>
                        <select id="fmAssignSectionsSemester" class="form-select" required disabled>
                            <option value="">Select semester</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="fmAssignSectionsList" class="form-label">Sections</label>
                    <select id="fmAssignSectionsList" class="form-select" multiple size="7" required disabled></select>
                    <small class="text-muted">Hold Ctrl to select multiple sections.</small>
                </div>
                <div id="fmAssignSectionsMessage" class="text-muted" role="status" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Section Assignments</button>
            </div>
        </form>
    </div>
</div>

<div id="fmAssignSubjectModal" class="modal">
    <div class="modal-dialog" style="max-width: 640px;">
        <div class="modal-header">
            <h5 class="modal-title">Assign Subject</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="fmAssignSubjectForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="fmAssignSubjectFacultyId">
                <div class="form-group">
                    <label class="form-label">Faculty</label>
                    <input type="text" class="form-control" id="fmAssignSubjectFacultyName" readonly>
                </div>
                <div class="form-group">
                    <label for="fmAssignSubjectSection" class="form-label">Assigned Section</label>
                    <select id="fmAssignSubjectSection" class="form-select" required disabled>
                        <option value="">Loading assigned sections...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fmAssignSubjectSubject" class="form-label">Curriculum Subject</label>
                    <select id="fmAssignSubjectSubject" class="form-select" required disabled>
                        <option value="">Select a section first</option>
                    </select>
                </div>
                <div id="fmAssignSubjectMessage" class="text-muted" role="status" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary" disabled>Save Subject Assignment</button>
            </div>
        </form>
    </div>
</div>

<div id="fmFacultyAssignmentsViewModal" class="modal">
    <div class="modal-dialog" style="max-width: 1100px;">
        <div class="modal-header">
            <h5 class="modal-title">Faculty Assignments</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="mb-3"><strong>Faculty:</strong> <span id="fmFacultyAssignmentsViewName"></span></p>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Units</th>
                            <th>Academic Period</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Schedule Status</th>
                        </tr>
                    </thead>
                    <tbody id="fmFacultyAssignmentsViewBody">
                        <tr><td colspan="8" class="text-center text-muted">Select a faculty member to view assignments.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>