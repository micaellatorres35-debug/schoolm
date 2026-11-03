<?php $pageTitle = 'General Education Curriculum'; ?>
<div class="module-header">
    <div>
        <h1>General Education Curriculum</h1>
    </div>
</div>

<div class="module-content" id="geCurriculumModule">
    <div class="academics-tabs" role="tablist" aria-label="General Education Curriculum">
        <button type="button" class="academics-tab active" data-ge-tab="subjects">GE Subject Management</button>
        <button type="button" class="academics-tab" data-ge-tab="prerequisites">Prerequisites</button>
        <button type="button" class="academics-tab" data-ge-tab="mapping">Curriculum Mapping</button>
    </div>

    <section class="academics-panel active" id="ge-panel-subjects" data-ge-panel="subjects">
        <div class="section-header">
            <h3>GE Subjects</h3>
            <button type="button" class="btn btn-primary" id="geAddSubjectBtn"><i class="fas fa-plus"></i> Add Subject</button>
        </div>
        <div class="search-filter-container">
            <div class="search-box">
                <input type="search" id="geSubjectSearch" placeholder="Search GE subjects..." aria-label="Search GE subjects">
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
                    <tbody><tr><td colspan="7" class="text-center text-muted">Loading GE subjects...</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="academics-panel" id="ge-panel-prerequisites" data-ge-panel="prerequisites">
        <div class="section-header"><h3>Subject Prerequisites</h3></div>
        <form id="gePrerequisiteForm" class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label for="gePrerequisiteSubject" class="form-label">Subject</label>
                        <select id="gePrerequisiteSubject" class="form-select" required><option value="">Select GE subject</option></select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="gePrerequisiteRequired" class="form-label">Prerequisite</label>
                        <select id="gePrerequisiteRequired" class="form-select" required><option value="">Select prerequisite subject</option></select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </div>
                <div id="gePrerequisiteMessage" class="mt-2" role="status" aria-live="polite"></div>
            </div>
        </form>
        <div class="table-card">
            <div class="table-responsive">
                <table id="gePrerequisitesTable">
                    <thead><tr><th>Subject</th><th>Prerequisite</th><th>Action</th></tr></thead>
                    <tbody><tr><td colspan="3" class="text-center text-muted">Loading prerequisites...</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="academics-panel" id="ge-panel-mapping" data-ge-panel="mapping">
        <div class="section-header"><h3>Curriculum Mapping</h3></div>
        <form id="geMappingForm" class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="geMappingCurriculum" class="form-label">Curriculum</label>
                        <select id="geMappingCurriculum" class="form-select" required><option value="">Select curriculum</option></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="geMappingSubject" class="form-label">GE Subject</label>
                        <select id="geMappingSubject" class="form-select" required><option value="">Select GE subject</option></select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="geMappingYear" class="form-label">Year Level</label>
                        <select id="geMappingYear" class="form-select" required>
                            <option value="">Year</option>
                            <option value="1">1st</option><option value="2">2nd</option>
                            <option value="3">3rd</option><option value="4">4th</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="geMappingSemester" class="form-label">Semester</label>
                        <select id="geMappingSemester" class="form-select" required>
                            <option value="">Semester</option>
                            <option value="First">First</option><option value="Second">Second</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Add Mapping</button>
                </div>
                <div id="geMappingMessage" class="mt-2" role="status" aria-live="polite"></div>
            </div>
        </form>
        <div class="table-card">
            <div class="table-responsive">
                <table id="geMappingsTable">
                    <thead><tr><th>Curriculum</th><th>Subject</th><th>Year</th><th>Semester</th><th>Units</th><th>Action</th></tr></thead>
                    <tbody><tr><td colspan="6" class="text-center text-muted">Loading mappings...</td></tr></tbody>
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
                        <input type="text" id="geSubjectCode" class="form-control" maxlength="250" pattern="GE[A-Za-z0-9-]*" required>
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