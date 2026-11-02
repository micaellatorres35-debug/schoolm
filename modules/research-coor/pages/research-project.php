<div class="research-project-page academics-management-page">
    <div class="module-header">
        <div>
            <h1><i class="fas fa-flask"></i> Research Project Management</h1>
            <p>Register, monitor, complete, and archive institutional research projects</p>
        </div>
    </div>

    <div class="research-storage-notice" id="researchStorageNotice" role="status" aria-live="polite">
        Project records are saved in this browser.
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
        <button type="button" class="research-tab" id="research-tab-monitoring" role="tab" aria-selected="false" data-project-tab="monitoring">
            <i class="fas fa-chart-line"></i> Progress & Completion
        </button>
        <button type="button" class="research-tab" id="research-tab-archive" role="tab" aria-selected="false" data-project-tab="archive">
            <i class="fas fa-box-archive"></i> Project Archive
        </button>
    </div>

    <section class="research-panel active" id="research-panel-overview" role="tabpanel" aria-labelledby="research-tab-overview">
        <div class="section-header">
            <div>
                <h3>Registered Research Projects</h3>
            </div>
        </div>

        <div class="research-filters">
            <div class="research-search">
                <label for="researchProjectSearch">Search projects</label>
                <input type="search" id="researchProjectSearch" class="form-control" placeholder="Search title, registration no., or investigator">
            </div>
            <div>
                <label for="researchCategoryFilter">Category</label>
                <select id="researchCategoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    <option value="Basic Research">Basic Research</option>
                    <option value="Applied Research">Applied Research</option>
                    <option value="Action Research">Action Research</option>
                    <option value="Developmental Research">Developmental Research</option>
                    <option value="Institutional Research">Institutional Research</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label for="researchStatusFilter">Status</label>
                <select id="researchStatusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft">Draft</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Approved">Approved</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div>
                <label for="researchClassificationFilter">Classification</label>
                <select id="researchClassificationFilter" class="form-select">
                    <option value="">All Classifications</option>
                    <option>Institutional</option>
                    <option>Faculty</option>
                    <option>Student</option>
                    <option>Collaborative</option>
                    <option>Funded</option>
                    <option>Unfunded</option>
                    <option>Other</option>
                </select>
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
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="researchProjectsBody">
                        <tr><td colspan="8" class="empty-state">No research projects registered yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="research-panel" id="research-panel-registration" role="tabpanel" aria-labelledby="research-tab-registration" hidden>
        <div class="section-header">
            <div>
                <h3 id="researchRegistrationHeading">Research Project Registration</h3>
                <p class="text-muted">Enter the project details, team, adviser, and planned timeline.</p>
            </div>
            <button type="button" class="btn btn-outline-secondary" data-project-cancel-edit hidden>Cancel Edit</button>
        </div>

        <form id="researchProjectForm" class="research-form" data-custom-submit>
            <input type="hidden" name="projectId" id="researchProjectId" value="">
            <div class="research-form-section">
                <h4>Research Title and Description</h4>
                <div class="research-form-grid">
                    <div class="research-field research-field-full">
                        <label for="researchTitle">Research Title <span class="required">*</span></label>
                        <input type="text" id="researchTitle" name="title" class="form-control" maxlength="200" required>
                    </div>
                    <div class="research-field research-field-full">
                        <label for="researchDescription">Project Description <span class="required">*</span></label>
                        <textarea id="researchDescription" name="description" class="form-control" rows="4" maxlength="3000" required></textarea>
                    </div>
                    <div class="research-field research-field-full">
                        <label for="researchAbstract">Research Abstract</label>
                        <textarea id="researchAbstract" name="abstract" class="form-control" rows="5" maxlength="5000" placeholder="Summarize the research problem, objectives, methods, and expected outcomes."></textarea>
                    </div>
                    <div class="research-field">
                        <label for="researchCategory">Research Category <span class="required">*</span></label>
                        <select id="researchCategory" name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <option>Basic Research</option>
                            <option>Applied Research</option>
                            <option>Action Research</option>
                            <option>Developmental Research</option>
                            <option>Institutional Research</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="research-field">
                        <label for="researchClassification">Research Classification <span class="required">*</span></label>
                        <select id="researchClassification" name="classification" class="form-select" required>
                            <option value="">Select classification</option>
                            <option>Institutional</option>
                            <option>Faculty</option>
                            <option>Student</option>
                            <option>Collaborative</option>
                            <option>Funded</option>
                            <option>Unfunded</option>
                            <option>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="research-form-section">
                <h4>Project Leader Information</h4>
                <div class="research-form-grid">
                    <div class="research-field">
                        <label for="researchInvestigator">Project Leader / Lead Researcher <span class="required">*</span></label>
                        <input type="text" id="researchInvestigator" name="investigator" class="form-control" maxlength="120" required>
                    </div>
                    <div class="research-field">
                        <label for="researchLeaderEmail">Leader Email</label>
                        <input type="email" id="researchLeaderEmail" name="leaderEmail" class="form-control" maxlength="160">
                    </div>
                    <div class="research-field">
                        <label for="researchLeaderContact">Contact Number</label>
                        <input type="tel" id="researchLeaderContact" name="leaderContact" class="form-control" maxlength="40">
                    </div>
                    <div class="research-field">
                        <label for="researchLeaderDepartment">Department / Unit</label>
                        <input type="text" id="researchLeaderDepartment" name="leaderDepartment" class="form-control" maxlength="120">
                    </div>
                </div>
            </div>

            <div class="research-form-section">
                <h4>Assigned Researchers and Adviser</h4>
                <div class="research-form-grid">
                    <div class="research-field research-field-full">
                        <label for="researchResearchers">Assigned Researchers</label>
                        <textarea id="researchResearchers" name="researchers" class="form-control" rows="3" maxlength="1500" placeholder="One researcher per line (name and optional email)"></textarea>
                        <small class="research-field-help">Ilagay ang bawat researcher sa sariling linya.</small>
                    </div>
                    <div class="research-field">
                        <label for="researchAdviser">Research Adviser</label>
                        <input type="text" id="researchAdviser" name="adviser" class="form-control" maxlength="120">
                    </div>
                    <div class="research-field">
                        <label for="researchAdviserEmail">Adviser Email</label>
                        <input type="email" id="researchAdviserEmail" name="adviserEmail" class="form-control" maxlength="160">
                    </div>
                </div>
            </div>

            <div class="research-form-section">
                <h4>Research Project Timeline and Status</h4>
                <div class="research-form-grid">
                    <div class="research-field">
                        <label for="researchStartDate">Planned Start Date <span class="required">*</span></label>
                        <input type="date" id="researchStartDate" name="startDate" class="form-control" required>
                    </div>
                    <div class="research-field">
                        <label for="researchTargetDate">Target Completion Date <span class="required">*</span></label>
                        <input type="date" id="researchTargetDate" name="targetDate" class="form-control" required>
                    </div>
                    <div class="research-field">
                        <label for="researchStatus">Initial Project Status <span class="required">*</span></label>
                        <select id="researchStatus" name="status" class="form-select" required>
                            <option value="Draft">Draft</option>
                            <option value="Submitted" selected>Submitted</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Approved">Approved</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="research-milestone-heading">
                    <div>
                        <h5>Planned Milestones</h5>
                        <p class="text-muted">Add key activities and their target dates.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addResearchMilestone">
                        <i class="fas fa-plus"></i> Add Milestone
                    </button>
                </div>
                <div id="researchMilestones" class="research-milestones">
                    <div class="research-milestone-row">
                        <label class="sr-only">Milestone title</label>
                        <input type="text" class="form-control milestone-title" maxlength="160" placeholder="e.g. Literature review" required>
                        <label class="sr-only">Milestone target date</label>
                        <input type="date" class="form-control milestone-date" aria-label="Milestone target date" required>
                        <button type="button" class="milestone-remove" aria-label="Remove milestone" title="Remove milestone">&times;</button>
                    </div>
                </div>
            </div>

            <div class="research-form-actions">
                <button type="reset" class="btn btn-outline-secondary" id="resetResearchProjectForm">Clear Form</button>
                <button type="submit" class="btn btn-primary" id="saveResearchProjectButton"><i class="fas fa-save"></i> Register Project</button>
            </div>
        </form>
    </section>

    <section class="research-panel" id="research-panel-monitoring" role="tabpanel" aria-labelledby="research-tab-monitoring" hidden>
        <div class="section-header">
            <div>
                <h3>Project Timeline &amp; Monitoring</h3>
                <p class="text-muted">Review milestones, update project status, and record monitoring notes.</p>
            </div>
        </div>
        <div class="research-monitoring-layout">
            <div class="research-monitoring-list">
                <label for="monitoringProjectSelect">Select a project</label>
                <select id="monitoringProjectSelect" class="form-select">
                    <option value="">Select a registered project</option>
                </select>
                <div id="monitoringProjectSummary" class="research-monitoring-summary">
                    <div class="empty-state">Register or select a project to view its timeline and monitoring details.</div>
                </div>
            </div>
            <div class="research-monitoring-detail" id="researchMonitoringDetail">
                <div class="empty-state">Project milestone and monitoring details will appear here.</div>
            </div>
        </div>
    </section>

    <section class="research-panel" id="research-panel-archive" role="tabpanel" aria-labelledby="research-tab-archive" hidden>
        <div class="section-header">
            <div>
                <h3>Archived Research Projects</h3>
                <p class="text-muted">Archived records are retained and can be restored at any time.</p>
            </div>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table id="researchArchivedProjectsTable">
                    <thead>
                        <tr>
                            <th>Registration No.</th>
                            <th>Project Title</th>
                            <th>Project Leader</th>
                            <th>Archived On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="researchArchivedProjectsBody">
                        <tr><td colspan="5" class="empty-state">No projects in the archive.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
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
