<div class="ethics-compliance-page">
    <div class="module-header">
        <div>
            <h1><i class="fas fa-shield-heart" aria-hidden="true"></i> Ethics and Compliance</h1>
            <p>Manage ethics clearances, consent and plagiarism evidence, policy checks, and project compliance records</p>
        </div>
    </div>

    <section class="ethics-guide" aria-labelledby="ethicsGuideTitle">
        <div class="ethics-guide-heading">
            <span class="ethics-guide-icon"><i class="fas fa-circle-info" aria-hidden="true"></i></span>
            <div>
                <h2 id="ethicsGuideTitle">Purpose and how this module works</h2>
                <p>Keep a traceable ethics and compliance record for each research project. Upload evidence here and it will also appear in Research Documentation.</p>
            </div>
        </div>
        <ol class="ethics-guide-steps">
            <li><strong>Link a project</strong><span>Select an active Research Project so clearance and evidence stay connected to its project record.</span></li>
            <li><strong>Record clearance and checks</strong><span>Track committee application, decision, consent, plagiarism, policy review, and required documents.</span></li>
            <li><strong>Upload supporting evidence</strong><span>Attach ethics, informed consent, plagiarism, or compliance files; they are indexed in the shared Document Library.</span></li>
            <li><strong>Update and archive</strong><span>Save status changes to keep a dated history. Archive records instead of deleting them.</span></li>
        </ol>
        <p class="ethics-storage-note"><i class="fas fa-laptop" aria-hidden="true"></i> Data and uploaded files are stored in this browser on this device, consistent with the connected Research modules.</p>
    </section>

    <div class="ethics-notice" id="ethicsNotice" role="status" aria-live="polite">Ethics records are saved in this browser.</div>

    <section class="ethics-stats" aria-label="Ethics and compliance summary">
        <article><span>Total records</span><strong id="ethicsCountTotal">0</strong></article>
        <article><span>Under review</span><strong id="ethicsCountReview">0</strong></article>
        <article><span>Approved clearances</span><strong id="ethicsCountApproved">0</strong></article>
        <article><span>Action required</span><strong id="ethicsCountAction">0</strong></article>
        <article><span>Archived records</span><strong id="ethicsCountArchived">0</strong></article>
    </section>

    <nav class="ethics-tabs" role="tablist" aria-label="Ethics and compliance sections">
        <button type="button" class="ethics-tab active" role="tab" aria-selected="true" data-ethics-tab="records"><i class="fas fa-list-check" aria-hidden="true"></i> Clearance Records</button>
        <button type="button" class="ethics-tab" role="tab" aria-selected="false" data-ethics-tab="register"><i class="fas fa-file-circle-plus" aria-hidden="true"></i> Register / Update</button>
        <button type="button" class="ethics-tab" role="tab" aria-selected="false" data-ethics-tab="evidence"><i class="fas fa-file-shield" aria-hidden="true"></i> Ethics Documents</button>
        <button type="button" class="ethics-tab" role="tab" aria-selected="false" data-ethics-tab="archive"><i class="fas fa-box-archive" aria-hidden="true"></i> Archive &amp; History</button>
    </nav>

    <section class="ethics-panel active" id="ethics-panel-records" role="tabpanel">
        <div class="ethics-section-heading">
            <div><h2>Ethics clearance and compliance status</h2><p>Search, filter, and update a project's clearance and policy status.</p></div>
            <button type="button" class="ethics-button ethics-button-primary" data-ethics-new><i class="fas fa-plus" aria-hidden="true"></i> New record</button>
        </div>
        <div class="ethics-filters">
            <label>Search records<input type="search" id="ethicsSearch" class="ethics-control" placeholder="Project, application no., committee"></label>
            <label>Clearance status
                <select id="ethicsStatusFilter" class="ethics-control">
                    <option value="">All statuses</option><option>Draft</option><option>Submitted</option><option>Under Review</option><option>Revision Required</option><option>Approved</option><option>Rejected</option><option>Expired</option>
                </select>
            </label>
            <label>Compliance
                <select id="ethicsComplianceFilter" class="ethics-control">
                    <option value="">All compliance statuses</option><option>Compliant</option><option>For Review</option><option>Non-compliant</option>
                </select>
            </label>
        </div>
        <div class="ethics-table-wrap">
            <table class="ethics-table">
                <thead><tr><th>Project / Application</th><th>Clearance status</th><th>Consent</th><th>Plagiarism</th><th>Policy compliance</th><th>Expiry</th><th>Actions</th></tr></thead>
                <tbody id="ethicsRecordsBody"><tr><td colspan="7" class="ethics-empty">No ethics clearance records yet. Register a record to start tracking compliance.</td></tr></tbody>
            </table>
        </div>
    </section>

    <section class="ethics-panel" id="ethics-panel-register" role="tabpanel" hidden>
        <div class="ethics-section-heading">
            <div><h2 id="ethicsFormHeading">Register ethics clearance</h2><p>Connect an ethics clearance and compliance review to a research project.</p></div>
            <button type="button" class="ethics-button" data-ethics-cancel hidden>Cancel edit</button>
        </div>
        <form id="ethicsForm" class="ethics-form" data-custom-submit>
            <input type="hidden" name="recordId" id="ethicsRecordId">
            <section class="ethics-form-section">
                <h3>Project and clearance details</h3>
                <div class="ethics-form-grid">
                    <label class="ethics-field">Research project *<select class="ethics-control" name="projectId" id="ethicsProjectSelect" required><option value="">Loading projects…</option></select><small>Project options come from Research Projects. Register a project there first if the list is empty.</small></label>
                    <label class="ethics-field">Related proposal (optional)<select class="ethics-control" name="proposalId" id="ethicsProposalSelect"><option value="">No proposal linked</option></select><small>Proposal options come from Research Proposals.</small></label>
                    <label class="ethics-field">Ethics application / clearance no.<input class="ethics-control" name="applicationNo" maxlength="100" placeholder="e.g. REC-2026-001"></label>
                    <label class="ethics-field">Reviewing committee<input class="ethics-control" name="committee" maxlength="160" placeholder="Research Ethics Committee"></label>
                    <label class="ethics-field">Clearance status *
                        <select class="ethics-control" name="status" required><option>Draft</option><option>Submitted</option><option>Under Review</option><option>Revision Required</option><option>Approved</option><option>Rejected</option><option>Expired</option></select>
                    </label>
                    <label class="ethics-field">Application submitted<input class="ethics-control" type="date" name="submittedDate"></label>
                    <label class="ethics-field">Decision date<input class="ethics-control" type="date" name="decisionDate"></label>
                    <label class="ethics-field">Clearance expiry date<input class="ethics-control" type="date" name="expiryDate"></label>
                </div>
            </section>

            <section class="ethics-form-section">
                <h3>Ethics and compliance checks</h3>
                <div class="ethics-form-grid">
                    <label class="ethics-field">Informed consent status
                        <select class="ethics-control" name="consentStatus"><option>Not Required</option><option>Required</option><option>Pending</option><option>Obtained</option><option>Verified</option></select>
                    </label>
                    <label class="ethics-field">Plagiarism check status
                        <select class="ethics-control" name="plagiarismStatus"><option>Pending</option><option>Checked</option><option>Action Required</option><option>Not Required</option></select>
                    </label>
                    <label class="ethics-field">Similarity score (%)<input class="ethics-control" type="number" name="similarityScore" min="0" max="100" step="0.1" placeholder="Not recorded"></label>
                    <label class="ethics-field">Policy compliance
                        <select class="ethics-control" name="policyStatus"><option>For Review</option><option>Compliant</option><option>Non-compliant</option></select>
                    </label>
                    <label class="ethics-field">Last policy review<input class="ethics-control" type="date" name="policyReviewedDate"></label>
                    <label class="ethics-field ethics-field-full">Compliance notes<textarea class="ethics-control" name="notes" rows="3" maxlength="3000" placeholder="Record required actions, policy references, review notes, or follow-up dates."></textarea></label>
                </div>
            </section>

            <section class="ethics-form-section">
                <h3>Compliance requirements</h3>
                <p class="ethics-section-help">Update each requirement as evidence is prepared and reviewed. Requirement status is saved with the clearance history.</p>
                <div class="ethics-requirements-grid">
                    <label>Ethics application
                        <select class="ethics-control" name="reqApplication"><option>Pending</option><option>Submitted</option><option>Verified</option><option>Not Applicable</option></select>
                    </label>
                    <label>Informed consent forms
                        <select class="ethics-control" name="reqConsent"><option>Pending</option><option>Submitted</option><option>Verified</option><option>Not Applicable</option></select>
                    </label>
                    <label>Research instruments / protocol
                        <select class="ethics-control" name="reqProtocol"><option>Pending</option><option>Submitted</option><option>Verified</option><option>Not Applicable</option></select>
                    </label>
                    <label>Plagiarism report
                        <select class="ethics-control" name="reqPlagiarism"><option>Pending</option><option>Submitted</option><option>Verified</option><option>Not Applicable</option></select>
                    </label>
                    <label>Data privacy / security plan
                        <select class="ethics-control" name="reqPrivacy"><option>Pending</option><option>Submitted</option><option>Verified</option><option>Not Applicable</option></select>
                    </label>
                    <label>Other committee requirements
                        <select class="ethics-control" name="reqOther"><option>Pending</option><option>Submitted</option><option>Verified</option><option>Not Applicable</option></select>
                    </label>
                </div>
            </section>

            <section class="ethics-form-section">
                <h3>Attach supporting evidence <span class="ethics-optional">Optional</span></h3>
                <p class="ethics-section-help">Evidence is also saved to the shared Research Documentation Library and linked to this project.</p>
                <div class="ethics-form-grid">
                    <label class="ethics-field">Document category
                        <select class="ethics-control" name="evidenceCategory"><option>Research Ethics Documents</option><option>Informed Consent Documents</option><option>Plagiarism Check Documents</option><option>Compliance Evidence</option></select>
                    </label>
                    <label class="ethics-field">Evidence file
                        <input class="ethics-control ethics-file-input" type="file" name="evidenceFile" id="ethicsEvidenceFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png">
                        <small>PDF, Office, text, CSV, JPG, or PNG; maximum 25 MB.</small>
                    </label>
                    <label class="ethics-field ethics-field-full">Evidence title<input class="ethics-control" name="evidenceTitle" maxlength="200" placeholder="Required when attaching a file"></label>
                </div>
            </section>
            <div class="ethics-form-actions">
                <button type="button" class="ethics-button" data-ethics-cancel>Cancel</button>
                <button type="submit" class="ethics-button ethics-button-primary" id="ethicsSaveButton"><i class="fas fa-save" aria-hidden="true"></i> Save clearance record</button>
            </div>
        </form>
    </section>

    <section class="ethics-panel" id="ethics-panel-evidence" role="tabpanel" hidden>
        <div class="ethics-section-heading"><div><h2>Ethics and compliance documents</h2><p>Evidence uploaded here is shared with the Research Documentation Library.</p></div><a class="ethics-button" href="?page=research-documentation" data-page="research-documentation"><i class="fas fa-folder-open" aria-hidden="true"></i> Open document library</a></div>
        <div class="ethics-table-wrap">
            <table class="ethics-table">
                <thead><tr><th>Document</th><th>Category</th><th>Linked project</th><th>Uploaded</th><th>File size</th><th>Actions</th></tr></thead>
                <tbody id="ethicsEvidenceBody"><tr><td colspan="6" class="ethics-empty">No ethics or compliance documents uploaded yet.</td></tr></tbody>
            </table>
        </div>
    </section>

    <section class="ethics-panel" id="ethics-panel-archive" role="tabpanel" hidden>
        <div class="ethics-section-heading"><div><h2>Archived clearance records</h2><p>Archived items are retained with their history and can be restored.</p></div></div>
        <div class="ethics-table-wrap">
            <table class="ethics-table">
                <thead><tr><th>Project / Application</th><th>Clearance status</th><th>Policy compliance</th><th>Archived</th><th>History</th><th>Actions</th></tr></thead>
                <tbody id="ethicsArchiveBody"><tr><td colspan="6" class="ethics-empty">No archived ethics records.</td></tr></tbody>
            </table>
        </div>
        <div class="ethics-archive-evidence">
            <h3>Archived ethics documents</h3>
            <div class="ethics-table-wrap">
                <table class="ethics-table">
                    <thead><tr><th>Document</th><th>Category</th><th>Linked project</th><th>Archived</th><th>Actions</th></tr></thead>
                    <tbody id="ethicsArchiveEvidenceBody"><tr><td colspan="5" class="ethics-empty">No archived ethics documents.</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="ethics-archive-evidence">
            <h3>Compliance status history</h3>
            <div class="ethics-table-wrap">
                <table class="ethics-table">
                    <thead><tr><th>Project / Application</th><th>Action</th><th>Clearance status</th><th>Date and time</th></tr></thead>
                    <tbody id="ethicsHistoryBody"><tr><td colspan="4" class="ethics-empty">No compliance history recorded yet.</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<link rel="stylesheet" href="css/pages/ethics-compliance.css">
