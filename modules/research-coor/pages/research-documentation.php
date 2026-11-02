<div class="research-documentation-page">
    <div class="module-header">
        <h1>Research Documentation Management System</h1>
        <p>Office of Research Coordination | Research Documentation Management</p>
    </div>

    <div class="documentation-notice" id="documentationNotice" role="status" aria-live="polite">
        Document records and files are stored in this browser on this device.
    </div>

    <section class="documentation-stats" aria-label="Document summary">
        <article><span>Active Documents</span><strong id="documentCountActive">0</strong></article>
        <article><span>Proposal Documents</span><strong id="documentCountProposals">0</strong></article>
        <article><span>Research Outputs</span><strong id="documentCountOutputs">0</strong></article>
        <article><span>Archived Documents</span><strong id="documentCountArchived">0</strong></article>
    </section>

    <nav class="documentation-tabs" role="tablist" aria-label="Research documentation sections">
        <button type="button" class="documentation-tab active" role="tab" aria-selected="true" data-document-tab="library">Document Library</button>
        <button type="button" class="documentation-tab" role="tab" aria-selected="false" data-document-tab="upload">Upload / Edit Document</button>
        <button type="button" class="documentation-tab" role="tab" aria-selected="false" data-document-tab="archive">Document Archive</button>
    </nav>

    <section class="documentation-panel active" id="documentation-panel-library" role="tabpanel">
        <div class="documentation-section-heading">
            <div><h2>Research Document Library</h2><p>Browse proposal documents, manuscripts, ethics records, progress updates, and final outputs.</p></div>
        </div>
        <div class="documentation-filters">
            <label>Search documents
                <input type="search" id="documentSearch" class="documentation-control" placeholder="File, title, reference, or linked record">
            </label>
            <label>Category
                <select id="documentCategoryFilter" class="documentation-control">
                    <option value="">All categories</option>
                    <option>Research Proposal Documents</option><option>Research Manuscripts</option>
                    <option>Research Chapters and Appendices</option><option>Final Research Papers</option>
                    <option>Supporting Research Documents</option><option>Research Ethics Documents</option>
                    <option>Informed Consent Documents</option><option>Plagiarism Check Documents</option>
                    <option>Compliance Evidence</option><option>Research Progress Documents</option>
                    <option>Research Accomplishment Reports</option><option>Other Research Documents</option>
                </select>
            </label>
            <label>Linked record
                <select id="documentLinkFilter" class="documentation-control">
                    <option value="">All records</option><option value="proposal">Proposals</option><option value="project">Projects</option><option value="unlinked">Unlinked</option>
                </select>
            </label>
        </div>
        <div class="documentation-table-wrap">
            <table class="documentation-table">
                <thead><tr><th>Document</th><th>Category</th><th>Linked Record</th><th>Uploaded</th><th>File Size</th><th>Actions</th></tr></thead>
                <tbody id="documentationRecordsBody"><tr><td colspan="6" class="documentation-empty">No research documents uploaded yet.</td></tr></tbody>
            </table>
        </div>
    </section>

    <section class="documentation-panel" id="documentation-panel-upload" role="tabpanel" hidden>
        <div class="documentation-section-heading">
            <div><h2 id="documentFormHeading">Upload Research Document</h2><p>Choose a category and attach a supporting file to a proposal or research project.</p></div>
            <button type="button" class="documentation-button" data-document-cancel hidden>Cancel Edit</button>
        </div>
        <form id="documentationForm" class="documentation-form" data-custom-submit>
            <input type="hidden" name="documentId" id="documentId">
            <div class="documentation-form-section">
                <h3>Document Information</h3>
                <div class="documentation-form-grid">
                    <label class="documentation-field full">Document Title *
                        <input class="documentation-control" name="title" maxlength="200" required placeholder="e.g. Ethics Clearance Certificate">
                    </label>
                    <label class="documentation-field">Document Category *
                        <select class="documentation-control" name="category" required>
                            <option value="">Select category</option>
                            <option>Research Proposal Documents</option><option>Research Manuscripts</option>
                            <option>Research Chapters and Appendices</option><option>Final Research Papers</option>
                            <option>Supporting Research Documents</option><option>Research Ethics Documents</option>
                            <option>Informed Consent Documents</option><option>Plagiarism Check Documents</option>
                            <option>Compliance Evidence</option><option>Research Progress Documents</option>
                            <option>Research Accomplishment Reports</option><option>Other Research Documents</option>
                        </select>
                    </label>
                    <label class="documentation-field">Document Version / Chapter
                        <input class="documentation-control" name="version" maxlength="100" placeholder="e.g. Version 2, Chapter 3">
                    </label>
                    <label class="documentation-field">Related Proposal
                        <select class="documentation-control" name="proposalId" id="documentProposalSelect">
                            <option value="">No proposal linked</option>
                        </select>
                    </label>
                    <label class="documentation-field">Related Research Project
                        <select class="documentation-control" name="projectId" id="documentProjectSelect">
                            <option value="">No project linked</option>
                        </select>
                    </label>
                    <label class="documentation-field">Document Date
                        <input class="documentation-control" type="date" name="documentDate">
                    </label>
                    <label class="documentation-field">Uploaded By
                        <input class="documentation-control" name="uploadedBy" maxlength="140" placeholder="Researcher or coordinator">
                    </label>
                    <label class="documentation-field full">Notes / Description
                        <textarea class="documentation-control" name="description" rows="4" maxlength="3000" placeholder="Describe this version, its purpose, or any handling notes."></textarea>
                    </label>
                    <label class="documentation-field full">Select Document File *
                        <input class="documentation-control documentation-file-input" type="file" name="file" id="documentationFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png" required>
                        <small>Allowed: PDF, Word, Excel, PowerPoint, text, CSV, JPG, PNG. Maximum 25 MB per file. Editing may keep the existing file without re-upload.</small>
                    </label>
                    <p class="documentation-existing-file full" id="existingDocumentFile" hidden></p>
                </div>
            </div>
            <div class="documentation-form-actions">
                <button type="button" class="documentation-button" data-document-cancel>Cancel</button>
                <button type="submit" class="documentation-button documentation-button-primary" id="saveDocumentButton"><i class="fas fa-save"></i> Save Document</button>
            </div>
        </form>
    </section>

    <section class="documentation-panel" id="documentation-panel-archive" role="tabpanel" hidden>
        <div class="documentation-section-heading">
            <div><h2>Archived Research Documents</h2><p>Archived files remain downloadable and can be restored to the library.</p></div>
        </div>
        <div class="documentation-table-wrap">
            <table class="documentation-table">
                <thead><tr><th>Document</th><th>Category</th><th>Linked Record</th><th>Archived</th><th>Actions</th></tr></thead>
                <tbody id="documentationArchiveBody"><tr><td colspan="5" class="documentation-empty">No documents in the archive.</td></tr></tbody>
            </table>
        </div>
    </section>

    <div class="documentation-modal" id="documentDetailsModal" aria-hidden="true">
        <div class="documentation-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="documentDetailsHeading">
            <header><h2 id="documentDetailsHeading">Document Details</h2><button type="button" data-document-close aria-label="Close">&times;</button></header>
            <div id="documentDetailsBody"></div>
            <footer>
                <button type="button" class="documentation-button" data-document-close>Close</button>
                <button type="button" class="documentation-button documentation-button-primary" data-document-modal-download>Download</button>
            </footer>
        </div>
    </div>
</div>