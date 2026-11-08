<link rel="stylesheet" href="css/pages/evaluation-assessment.css">
<div class="evaluation-page">
    <header class="evaluation-header">
        <div>
            <span class="evaluation-eyebrow">Office of Research Coordination</span>
            <h1>Research Evaluation and Assessment</h1>
            <p>Record panel evaluations, recommendations, revision requirements, and defense outcomes linked to registered research projects.</p>
        </div>
        <a class="evaluation-button evaluation-button-primary" href="?page=research-project" data-page="research-project"><i class="fas fa-folder-open" aria-hidden="true"></i> Research Projects</a>
    </header>

    <div class="evaluation-notice" id="evaluationNotice" role="status" aria-live="polite">Evaluation records are stored in the research database.</div>

    <section class="evaluation-stats" aria-label="Evaluation summary">
        <article><span>Panel Evaluations</span><strong id="evaluationCountTotal">0</strong></article>
        <article><span>Awaiting Decision</span><strong id="evaluationCountPending">0</strong></article>
        <article><span>Revision Required</span><strong id="evaluationCountRevision">0</strong></article>
        <article><span>Revisions Overdue</span><strong id="evaluationCountOverdue">0</strong></article>
        <article><span>Defenses Passed</span><strong id="evaluationCountPassed">0</strong></article>
    </section>

    <nav class="evaluation-tabs" role="tablist" aria-label="Evaluation sections">
        <button type="button" class="evaluation-tab active" role="tab" aria-selected="true" data-evaluation-tab="records"><i class="fas fa-list-check" aria-hidden="true"></i> Panel Results</button>
        <button type="button" class="evaluation-tab" role="tab" aria-selected="false" data-evaluation-tab="record"><i class="fas fa-clipboard-check" aria-hidden="true"></i> Record Evaluation</button>
        <button type="button" class="evaluation-tab" role="tab" aria-selected="false" data-evaluation-tab="forms"><i class="fas fa-file-circle-check" aria-hidden="true"></i> Evaluation Forms</button>
    </nav>

    <section class="evaluation-panel active" id="evaluation-panel-records" role="tabpanel">
        <div class="evaluation-section-heading"><div><h2>Panel Evaluation Results</h2><p>Each row represents one panel member's assessment of a registered research project.</p></div></div>
        <div class="evaluation-filters">
            <label>Search records<input type="search" class="evaluation-control" id="evaluationSearch" placeholder="Project, registration number, panel member"></label>
            <label>Decision
                <select class="evaluation-control" id="evaluationOutcomeFilter">
                    <option value="">All decisions</option><option>Pending</option><option>Passed</option><option>Passed with Revisions</option><option>Re-defense Required</option><option>Failed</option>
                </select>
            </label>
            <label>Revision status
                <select class="evaluation-control" id="evaluationRevisionFilter">
                    <option value="">All revision statuses</option><option>Not Required</option><option>Pending</option><option>In Progress</option><option>Completed</option><option>Overdue</option>
                </select>
            </label>
        </div>
        <div class="evaluation-table-wrap">
            <table class="evaluation-table">
                <thead><tr><th>Project</th><th>Panel Member</th><th>Evaluation Form</th><th>Score</th><th>Decision</th><th>Revision</th><th>Defense Outcome</th><th>Actions</th></tr></thead>
                <tbody id="evaluationRecordsBody"><tr><td colspan="8" class="evaluation-empty">Loading evaluation records…</td></tr></tbody>
            </table>
        </div>
        <p class="evaluation-footnote"><i class="fas fa-circle-info" aria-hidden="true"></i> Project details are linked to Research Projects. Supporting documents can be managed in <a href="?page=research-documentation" data-page="research-documentation">Research Documentation</a>.</p>
    </section>

    <section class="evaluation-panel" id="evaluation-panel-record" role="tabpanel" hidden>
        <div class="evaluation-section-heading"><div><h2 id="evaluationFormHeading">Record Panel Evaluation</h2><p>Save each panel member's result separately to maintain a clear review trail.</p></div><button type="button" class="evaluation-button" data-evaluation-cancel hidden>Cancel Edit</button></div>
        <form id="panelEvaluationForm" class="evaluation-form" data-custom-submit>
            <input type="hidden" name="id">
            <div class="evaluation-form-section">
                <h3>Project and Panel</h3>
                <div class="evaluation-form-grid">
                    <label class="evaluation-field evaluation-field-full">Research Project *
                        <select class="evaluation-control" name="projectId" required><option value="">Loading registered projects…</option></select>
                    </label>
                    <label class="evaluation-field">Evaluation Form *
                        <select class="evaluation-control" name="formId" required><option value="">Loading evaluation forms…</option></select>
                    </label>
                    <label class="evaluation-field">Panel Member *
                        <input class="evaluation-control" name="panelMember" maxlength="150" required placeholder="Panelist name">
                    </label>
                    <label class="evaluation-field">Panel Role
                        <input class="evaluation-control" name="panelRole" maxlength="80" placeholder="e.g. Chair, Adviser, Member">
                    </label>
                    <label class="evaluation-field">Evaluation Date *
                        <input class="evaluation-control" type="date" name="evaluationDate" required>
                    </label>
                    <label class="evaluation-field">Overall Score (calculated from criteria)
                        <input class="evaluation-control" type="number" name="score" min="0" max="100" step="0.01" readonly placeholder="Rate each criterion">
                    </label>
                    <label class="evaluation-field">Panel Recommendation *
                        <select class="evaluation-control" name="recommendation" required>
                            <option value="">Select recommendation</option><option>Pending</option><option>Passed</option><option>Passed with Revisions</option><option>Re-defense Required</option><option>Failed</option>
                        </select>
                    </label>
                </div>
                <div class="evaluation-criteria" id="evaluationCriteriaContainer" aria-live="polite">
                    <p class="evaluation-criteria-empty">Choose an evaluation form to rate its criteria.</p>
                </div>
            </div>
            <div class="evaluation-form-section">
                <h3>Comments and Revision Monitoring</h3>
                <div class="evaluation-form-grid">
                    <label class="evaluation-field evaluation-field-full">Panel Comments<textarea class="evaluation-control" name="comments" rows="3" maxlength="5000" placeholder="Panel notes and observations"></textarea></label>
                    <label class="evaluation-field evaluation-field-full">Recommendations<textarea class="evaluation-control" name="recommendations" rows="3" maxlength="5000" placeholder="Recommended improvements or next steps"></textarea></label>
                    <label class="evaluation-field evaluation-field-full">Revision Requirements<textarea class="evaluation-control" name="revisionRequirements" rows="3" maxlength="5000" placeholder="List required revisions; leave empty if none"></textarea></label>
                    <label class="evaluation-field">Revision Status
                        <select class="evaluation-control" name="revisionStatus" required><option>Not Required</option><option>Pending</option><option>In Progress</option><option>Completed</option></select>
                    </label>
                    <label class="evaluation-field">Revision Due Date<input class="evaluation-control" type="date" name="revisionDueDate"></label>
                </div>
            </div>
            <div class="evaluation-form-section">
                <h3>Defense Outcome</h3>
                <div class="evaluation-form-grid">
                    <label class="evaluation-field">Defense Date<input class="evaluation-control" type="date" name="defenseDate"></label>
                    <label class="evaluation-field">Defense Outcome *
                        <select class="evaluation-control" name="defenseOutcome" required><option value="">Select outcome</option><option>Scheduled</option><option>Passed</option><option>Passed with Revisions</option><option>Re-defense Required</option><option>Failed</option><option>Not Applicable</option></select>
                    </label>
                </div>
            </div>
            <div class="evaluation-form-actions"><button type="reset" class="evaluation-button">Clear Form</button><button type="submit" class="evaluation-button evaluation-button-primary"><i class="fas fa-save" aria-hidden="true"></i> Save Panel Evaluation</button></div>
        </form>
    </section>

    <section class="evaluation-panel" id="evaluation-panel-forms" role="tabpanel" hidden>
        <div class="evaluation-section-heading"><div><h2>Manage Evaluation Forms</h2><p>Create reusable evaluation forms and maintain the criteria used by research panels.</p></div></div>
        <div class="evaluation-forms-layout">
            <form id="evaluationTemplateForm" class="evaluation-form evaluation-template-form" data-custom-submit>
                <input type="hidden" name="id">
                <h3 id="evaluationTemplateHeading">Create Evaluation Form</h3>
                <label class="evaluation-field">Form Name *<input class="evaluation-control" name="name" maxlength="150" required placeholder="e.g. Final Defense Evaluation"></label>
                <label class="evaluation-field">Evaluation Stage *
                    <select class="evaluation-control" name="stage" required><option value="">Select stage</option><option>Proposal Defense</option><option>Progress Review</option><option>Final Defense</option><option>Other</option></select>
                </label>
                <label class="evaluation-field">Evaluation Criteria *<textarea class="evaluation-control" name="criteria" rows="7" maxlength="5000" required placeholder="One criterion per line, e.g. Research objectives"></textarea><small>Enter one criterion per line. The selected form is linked to every panel result.</small></label>
                <div class="evaluation-form-actions"><button type="reset" class="evaluation-button">Clear</button><button type="submit" class="evaluation-button evaluation-button-primary">Save Form</button></div>
            </form>
            <div class="evaluation-template-list">
                <h3>Available Forms</h3>
                <div id="evaluationTemplatesList"><p class="evaluation-empty">Loading evaluation forms…</p></div>
            </div>
        </div>
    </section>
</div>