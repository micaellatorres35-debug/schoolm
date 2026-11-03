<div class="research-project-page academics-management-page">
    <div class="module-header">
        <div>
            <h1><i class="fas fa-flask"></i> Research Project Management</h1>
            <p>Register research projects and view records saved in the research database.</p>
        </div>
    </div>

    <div class="research-storage-notice" id="researchStorageNotice" role="status" aria-live="polite">
        Research projects are saved in rc_projects.
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h6>Total Projects</h6>
            <h3 id="researchTotalCount">0</h3>
        </div>
        <div class="stat-card subjects">
            <h6>Under Review</h6>
            <h3 id="researchReviewCount">0</h3>
        </div>
        <div class="stat-card sections">
            <h6>Ongoing</h6>
            <h3 id="researchOngoingCount">0</h3>
        </div>
        <div class="stat-card faculty">
            <h6>Completed</h6>
            <h3 id="researchCompletedCount">0</h3>
        </div>
    </div>

    <div class="research-tabs" role="tablist" aria-label="Research project sections">
        <button type="button" class="research-tab active" id="research-tab-overview" role="tab" aria-selected="true" data-project-tab="overview">
            <i class="fas fa-folder-open"></i> Project Overview
        </button>
        <button type="button" class="research-tab" id="research-tab-registration" role="tab" aria-selected="false" data-project-tab="registration">
            <i class="fas fa-file-signature"></i> Project Registration
        </button>
    </div>

    <section class="research-panel active" id="research-panel-overview" role="tabpanel" aria-labelledby="research-tab-overview">
        <div class="section-header">
            <div><h3>Registered Research Projects</h3></div>
            <button type="button" class="btn btn-primary" data-project-open-registration>
                <i class="fas fa-plus"></i> Register Project
            </button>
        </div>

        <div class="research-filters">
            <div class="research-search">
                <label for="researchProjectSearch">Search projects</label>
                <input type="search" id="researchProjectSearch" class="form-control" placeholder="Search title, registration no., or project leader">
            </div>
            <div>
                <label for="researchCategoryFilter">Category</label>
                <select id="researchCategoryFilter" class="form-select"><option value="">All Categories</option></select>
            </div>
            <div>
                <label for="researchStatusFilter">Status</label>
                <select id="researchStatusFilter" class="form-select"><option value="">All Statuses</option></select>
            </div>
            <div>
                <label for="researchClassificationFilter">Classification</label>
                <select id="researchClassificationFilter" class="form-select"><option value="">All Classifications</option></select>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="researchProjectsTable">
                    <thead>
                        <tr>
                            <th>Registration No.</th>
                            <th>Project Title</th>
                            <th>Category / Classification</th>
                            <th>Project Leader</th>
                            <th>Timeline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="researchProjectsBody">
                        <tr><td colspan="7" class="empty-state">Loading research projects…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="research-panel" id="research-panel-registration" role="tabpanel" aria-labelledby="research-tab-registration" hidden>
        <div class="section-header">
            <div>
                <h3 id="researchRegistrationHeading">Research Project Registration</h3>
                <p class="text-muted">These fields are saved to the existing rc_projects table.</p>
            </div>
            <button type="button" class="btn btn-outline-secondary" data-project-cancel-edit hidden>Cancel Edit</button>
        </div>

        <form id="researchProjectForm" class="research-form" data-custom-submit>
            <input type="hidden" name="projectId" id="researchProjectId" value="">
            <div class="research-form-section">
                <h4>Research Project Details</h4>
                <div class="research-form-grid">
                    <div class="research-field research-field-full">
                        <label for="researchTitle">Research Title <span class="required">*</span></label>
                        <input type="text" id="researchTitle" name="title" class="form-control" maxlength="255" required>
                    </div>
                    <div class="research-field">
                        <label for="researchCategory">Research Category <span class="required">*</span></label>
                        <select id="researchCategory" name="category" class="form-select" required>
                            <option value="">Loading categories…</option>
                        </select>
                    </div>
                    <div class="research-field">
                        <label for="researchClassification">Research Classification <span class="required">*</span></label>
                        <select id="researchClassification" name="classification" class="form-select" required>
                            <option value="">Loading classifications…</option>
                        </select>
                    </div>
                    <div class="research-field research-field-full">
                        <label for="researchStudentId">Project Leader <span class="required">*</span></label>
                        <select id="researchStudentId" name="studentId" class="form-select" required>
                            <option value="">Loading students…</option>
                        </select>
                        <small class="research-field-help">Student options come from records in enr_students.</small>
                    </div>
                    <div class="research-field">
                        <label for="researchTimeline">Project Timeline <span class="required">*</span></label>
                        <input type="text" id="researchTimeline" name="timeline" class="form-control" maxlength="100" placeholder="e.g. 2026-01-01 to 2026-12-31" required>
                    </div>
                    <div class="research-field">
                        <label for="researchStatus">Project Status <span class="required">*</span></label>
                        <select id="researchStatus" name="status" class="form-select" required>
                            <option value="">Loading statuses…</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="research-form-actions">
                <button type="reset" class="btn btn-outline-secondary" id="resetResearchProjectForm">Clear Form</button>
                <button type="submit" class="btn btn-primary" id="saveResearchProjectButton"><i class="fas fa-save"></i> Register Project</button>
            </div>
        </form>
    </section>

    <div class="research-modal" id="researchProjectDetailsModal" aria-hidden="true">
        <div class="research-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="researchDetailsTitle">
            <div class="research-modal-header">
                <h3 id="researchDetailsTitle">Project Details</h3>
                <button type="button" class="research-modal-close" data-close-project-details aria-label="Close">&times;</button>
            </div>
            <div class="research-modal-body" id="researchProjectDetailsBody"></div>
            <div class="research-modal-footer">
                <button type="button" class="btn btn-secondary" data-close-project-details>Close</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="css/pages/research-project.css">
