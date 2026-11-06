<?php
$pageTitle = 'Gened Activities';
?>
<div class="module-header">
    <div>
        <h1>    GenED Activities</h1>
        <p>Plan and track academic activities for each semester.</p>
    </div>
    <button type="button" class="btn btn-primary" id="gcAddActivityButton">
        <i class="fas fa-plus"></i> Add Activity
    </button>
</div>

<div class="module-content" id="gcAcademicActivities">
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <h6>Total Activities</h6>
                <h3 id="gcTotalActivities">0</h3>
                <small class="text-muted">All activities this semester</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <h6>Pending Approval</h6>
                <h3 id="gcPendingActivities">0</h3>
                <small class="text-muted">Waiting for admin / college coordinator</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <h6>Upcoming</h6>
                <h3 id="gcUpcomingActivities">0</h3>
                <small class="text-muted">Approved activities with future dates</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <h6>Completed</h6>
                <h3 id="gcCompletedActivities">0</h3>
                <small class="text-muted">Activities marked complete</small>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="gcActivitySchoolYear" class="form-label">School Year</label>
                    <select id="gcActivitySchoolYear" class="form-select"></select>
                </div>
                <div class="col-12 col-md-6">
                    <label for="gcActivitySemester" class="form-label">Semester</label>
                    <select id="gcActivitySemester" class="form-select"></select>
                </div>
            </div>
        </div>
    </div>

    <nav class="academics-tabs gc-activity-categories" aria-label="Activity categories">
        <button type="button" class="academics-tab active" data-activity-category="">All Activities</button>
        <button type="button" class="academics-tab" data-activity-category="Common Exams">Common Exams</button>
        <button type="button" class="academics-tab" data-activity-category="Book Reports">Book Reports</button>
        <button type="button" class="academics-tab" data-activity-category="Quiz Bees &amp; Contests">Quiz Bees &amp; Contests</button>
        <button type="button" class="academics-tab" data-activity-category="Research &amp; Paper Presentations">Research &amp; Paper Presentations</button>
        <button type="button" class="academics-tab" data-activity-category="Orientation &amp; Curriculum Review">Orientation &amp; Curriculum Review</button>
        <button type="button" class="academics-tab" data-activity-category="Meetings &amp; Training">Meetings &amp; Training</button>
    </nav>

    <div class="table-card">
        <div class="table-responsive">
            <table id="gcActivitiesTable">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Approval Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="7" class="text-center text-muted">Loading activities...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div id="gcActivitiesMessage" class="mt-3" role="status" aria-live="polite"></div>
</div>

<div class="modal" id="gcActivityModal">
    <div class="modal-dialog" style="max-width: 680px;">
        <div class="modal-header">
            <h5 class="modal-title">Add Gened  Activity</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="gcActivityForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="gcActivityTitle" class="form-label">Activity Title</label>
                    <input type="text" id="gcActivityTitle" class="form-control" maxlength="200" required>
                </div>
                <div class="form-group">
                    <label for="gcActivityCategory" class="form-label">Category</label>
                    <select id="gcActivityCategory" class="form-select" required>
                        <option value="">Select category</option>
                        <option>Common Exams</option>
                        <option>Book Reports</option>
                        <option>Quiz Bees &amp; Contests</option>
                        <option>Research &amp; Paper Presentations</option>
                        <option>Orientation &amp; Curriculum Review</option>
                        <option>Meetings &amp; Training</option>
                    </select>
                </div>
                <div class="form-group" id="gcExamPeriodGroup" hidden>
                    <label for="gcExamPeriod" class="form-label">Common Exam / Item Analysis</label>
                    <select id="gcExamPeriod" class="form-select">
                        <option value="">Select exam type</option>
                        <option>Prelim</option>
                        <option>Midterm</option>
                        <option>Final</option>
                        <option>Item Analysis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gcActivityDate" class="form-label">Activity Date</label>
                    <input type="date" id="gcActivityDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="gcActivityDescription" class="form-label">Details</label>
                    <textarea id="gcActivityDescription" class="form-control" rows="3"></textarea>
                </div>
                <p class="text-muted mb-0">New activities will be submitted as Pending Approval.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary" id="gcActivitySubmit">Submit Activity</button>
            </div>
        </form>
    </div>
</div>
