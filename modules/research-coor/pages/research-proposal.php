<div class="research-proposal-page">
    <div class="module-header">
        <h1>Research Proposal Management System</h1>
        <p>Office of Research Coordination | Research Proposal Management</p>
    </div>

    <section class="proposal-stats" aria-label="Proposal summary">
        <article><span>Total Proposals</span><strong id="proposalCountTotal">0</strong></article>
        <article><span>For Review</span><strong id="proposalCountReview">0</strong></article>
        <article><span>Revision Requested</span><strong id="proposalCountRevision">0</strong></article>
        <article><span>Approved</span><strong id="proposalCountApproved">0</strong></article>
        <article><span>Rejected</span><strong id="proposalCountRejected">0</strong></article>
    </section>

    <nav class="proposal-tabs" role="tablist" aria-label="Proposal management sections">
        <button type="button" class="proposal-tab active" role="tab" aria-selected="true" data-proposal-tab="records">Proposal Records</button>
        <button type="button" class="proposal-tab" role="tab" aria-selected="false" data-proposal-tab="registration">Registration / Resubmission</button>
        <button type="button" class="proposal-tab" role="tab" aria-selected="false" data-proposal-tab="review">Review &amp; Evaluation</button>
        <button type="button" class="proposal-tab" role="tab" aria-selected="false" data-proposal-tab="history">History &amp; Documentation</button>
    </nav>

    <section class="proposal-panel active" id="proposal-panel-records" role="tabpanel">
        <div class="proposal-section-heading">
            <div><h2>Proposal Submission Records</h2><p>Track each proposal from initial registration to its final decision.</p></div>
        </div>
        <div class="proposal-filters">
            <label>Search proposals<input id="proposalSearch" type="search" class="proposal-control" placeholder="Title, reference number, or researcher"></label>
            <label>Status
                <select id="proposalStatusFilter" class="proposal-control">
                    <option value="">All statuses</option><option>Draft</option><option>Submitted</option>
                    <option>Under Review</option><option>Revision Requested</option><option>Resubmitted</option>
                    <option>Approved</option><option>Rejected</option>
                </select>
            </label>
            <label>Category
                <select id="proposalCategoryFilter" class="proposal-control">
                    <option value="">All categories</option><option>Basic Research</option><option>Applied Research</option>
                    <option>Action Research</option><option>Developmental Research</option>
                    <option>Institutional Research</option><option>Other</option>
                </select>
            </label>
        </div>
        <div class="proposal-table-wrap">
            <table class="proposal-table">
                <thead><tr><th>Reference</th><th>Proposal</th><th>Researcher</th><th>Category</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="proposalRecordsBody"><tr><td colspan="7" class="proposal-empty">No proposals registered yet.</td></tr></tbody>
            </table>
        </div>
    </section>

    <section class="proposal-panel" id="proposal-panel-registration" role="tabpanel" hidden>
        <div class="proposal-section-heading">
            <div><h2 id="proposalFormHeading">Research Proposal Registration</h2><p>Enter proposal details. Use a document link or repository reference for supporting files.</p></div>
            <button type="button" class="proposal-button" data-proposal-cancel hidden>Cancel Edit</button>
        </div>
        <form id="proposalForm" class="proposal-form" data-custom-submit>
            <input type="hidden" name="proposalId" id="proposalId">
            <div class="proposal-form-section">
                <h3>Proposal Information</h3>
                <div class="proposal-form-grid">
                    <label class="proposal-field full">Research Title *<input class="proposal-control" name="title" maxlength="220" required></label>
                    <label class="proposal-field full">Abstract *<textarea class="proposal-control" name="abstract" rows="5" maxlength="6000" required></textarea></label>
                    <label class="proposal-field">Research Category *
                        <select class="proposal-control" name="category" required>
                            <option value="">Select category</option><option>Basic Research</option><option>Applied Research</option>
                            <option>Action Research</option><option>Developmental Research</option><option>Institutional Research</option><option>Other</option>
                        </select>
                    </label>
                    <label class="proposal-field">Classification *
                        <select class="proposal-control" name="classification" required>
                            <option value="">Select classification</option><option>Institutional</option><option>Faculty</option>
                            <option>Student</option><option>Collaborative</option><option>Funded</option><option>Unfunded</option><option>Other</option>
                        </select>
                    </label>
                    <label class="proposal-field full">Research Objectives<textarea class="proposal-control" name="objectives" rows="3" maxlength="3000"></textarea></label>
                    <label class="proposal-field full">Methodology / Project Description<textarea class="proposal-control" name="methodology" rows="4" maxlength="5000"></textarea></label>
                </div>
            </div>
            <div class="proposal-form-section">
                <h3>Submission and Researcher Details</h3>
                <div class="proposal-form-grid">
                    <label class="proposal-field">Project Leader / Proponent *<input class="proposal-control" name="leader" maxlength="140" required></label>
                    <label class="proposal-field">Email *<input class="proposal-control" type="email" name="email" maxlength="180" required></label>
                    <label class="proposal-field">Department / Unit<input class="proposal-control" name="department" maxlength="140"></label>
                    <label class="proposal-field">Research Adviser<input class="proposal-control" name="adviser" maxlength="140"></label>
                    <label class="proposal-field full">Co-researchers (one name per line)<textarea class="proposal-control" name="researchers" rows="3" maxlength="2000"></textarea></label>
                    <label class="proposal-field">Planned Start Date<input class="proposal-control" type="date" name="startDate"></label>
                    <label class="proposal-field">Target Completion Date<input class="proposal-control" type="date" name="targetDate"></label>
                    <label class="proposal-field">Requested Budget<input class="proposal-control" type="number" name="budget" min="0" step="0.01" placeholder="0.00"></label>
                    <label class="proposal-field">Funding Source<input class="proposal-control" name="fundingSource" maxlength="180"></label>
                    <label class="proposal-field full">Supporting Document Link / Repository Reference
                        <input class="proposal-control" type="url" name="documentLink" maxlength="1000" placeholder="https://...">
                        <small>Records the location of submitted documents; file upload is not stored in this browser-only module.</small>
                    </label>
                </div>
            </div>
            <div class="proposal-form-actions">
                <button type="button" class="proposal-button" data-proposal-save-draft>Save as Draft</button>
                <button type="submit" class="proposal-button proposal-button-primary" id="proposalSubmitButton">Submit Proposal</button>
            </div>
        </form>
    </section>

    <section class="proposal-panel" id="proposal-panel-review" role="tabpanel" hidden>
        <div class="proposal-section-heading"><div><h2>Proposal Review and Evaluation</h2><p>Record assessment, request revisions, and monitor approval decisions.</p></div></div>
        <div class="proposal-review-layout">
            <div class="proposal-review-select">
                <label for="proposalReviewSelect">Select a proposal</label>
                <select id="proposalReviewSelect" class="proposal-control"><option value="">Select a proposal for review</option></select>
                <div id="proposalReviewSummary" class="proposal-summary"><p class="proposal-empty">Select a proposal to review.</p></div>
            </div>
            <form id="proposalReviewForm" class="proposal-review-form" data-custom-submit>
                <input type="hidden" name="proposalId">
                <label>Assessment Result<select class="proposal-control" name="decision" required>
                    <option value="">Select outcome</option><option value="Revision Requested">Revision Requested</option>
                    <option value="Approved">Approve</option><option value="Rejected">Reject</option>
                </select></label>
                <div class="proposal-score-grid">
                    <label>Relevance (1–5)<input class="proposal-control" type="number" name="relevance" min="1" max="5" step="1"></label>
                    <label>Methodology (1–5)<input class="proposal-control" type="number" name="methodologyScore" min="1" max="5" step="1"></label>
                    <label>Feasibility (1–5)<input class="proposal-control" type="number" name="feasibility" min="1" max="5" step="1"></label>
                </div>
                <label>Reviewer / Evaluator<input class="proposal-control" name="reviewer" maxlength="140" required></label>
                <label>Assessment and Reviewer Comments *<textarea class="proposal-control" name="comments" rows="5" maxlength="4000" required></textarea></label>
                <label>Revision Instructions (required for revision request)<textarea class="proposal-control" name="revisionInstructions" rows="3" maxlength="3000"></textarea></label>
                <label>Assessment Document Link / Reference<input class="proposal-control" type="url" name="assessmentLink" maxlength="1000" placeholder="https://..."></label>
                <button type="submit" class="proposal-button proposal-button-primary">Save Assessment Result</button>
            </form>
        </div>
    </section>

    <section class="proposal-panel" id="proposal-panel-history" role="tabpanel" hidden>
        <div class="proposal-section-heading"><div><h2>Proposal History and Documentation</h2><p>View submission records, resubmissions, assessment results, and decisions.</p></div></div>
        <label class="proposal-history-select">Select a proposal
            <select id="proposalHistorySelect" class="proposal-control"><option value="">Select a proposal</option></select>
        </label>
        <div id="proposalHistoryContent" class="proposal-history-content"><p class="proposal-empty">Choose a proposal to view its record history.</p></div>
    </section>

    <div class="proposal-modal" id="proposalDetailsModal" aria-hidden="true">
        <div class="proposal-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="proposalDetailsHeading">
            <header><h2 id="proposalDetailsHeading">Proposal Information</h2><button type="button" data-proposal-close aria-label="Close">&times;</button></header>
            <div id="proposalDetailsBody"></div>
            <footer><button type="button" class="proposal-button" data-proposal-close>Close</button></footer>
        </div>
    </div>
</div>