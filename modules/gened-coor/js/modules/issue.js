const IssuesController = 'controllers/IssueController.php';

const CONCERN_STATUS_LABELS = {
    draft: 'Draft',
    submitted: 'Submitted',
    reviewed: 'Reviewed',
    resolved: 'Resolved',
    dismissed: 'Dismissed',
};

// Attachment rules — keep in sync with FileStorage.php (the server re-checks everything).
const CONCERN_MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;
const CONCERN_ALLOWED_EXTENSIONS   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

// ── INIT ──────────────────────────────────────────────────────────────────────
function initConcernModule() {
    const moduleRoot = document.querySelector('.module-content[data-is-directress]');
    if (!moduleRoot) return;

    // Prevents the same element from getting a listener attached twice when
    // init() runs more than once on unchanged DOM.
    function bindOnce(el, event, handler) {
        if (!el || el.dataset.concernBound === '1') return;
        el.addEventListener(event, handler);
        el.dataset.concernBound = '1';
    }

    loadConcerns();

    const logForm = document.getElementById('concern-log-form');

    // --- Create modal wiring ---
    const modalOverlay  = document.getElementById('concern-modal-overlay');
    const openModalBtn  = document.getElementById('concern-open-modal');
    const closeModalBtn = document.getElementById('concern-modal-close');

    const openCreateModal = () => {
        logForm?.reset();
        document.getElementById('concern-issue-id').value = '';
        modalOverlay?.classList.add('active');
    };
    const closeCreateModal = () => {
        modalOverlay?.classList.remove('active');
        const formError = document.getElementById('concern-form-error');
        if (formError) formError.style.display = 'none';
    };

    bindOnce(openModalBtn, 'click', openCreateModal);
    bindOnce(closeModalBtn, 'click', closeCreateModal);
    bindOnce(modalOverlay, 'click', (e) => {
        if (e.target === modalOverlay) closeCreateModal();
    });

    // --- Optional attachment: check type/size before the user even submits ---
    const attachmentInput = document.getElementById('concern-file');
    bindOnce(attachmentInput, 'change', () => {
        const file  = attachmentInput.files[0];
        const error = file ? validateConcernAttachment(file) : null;
        if (error) {
            attachmentInput.value = '';
            const formError = document.getElementById('concern-form-error');
            if (formError) {
                formError.textContent   = error;
                formError.style.display = 'block';
            }
        }
    });

    // --- View/PDF modal wiring ---
    const viewOverlay = document.getElementById('concern-view-modal-overlay');
    const viewClose   = document.getElementById('concern-view-modal-close');
    const pdfOverlay  = document.getElementById('concern-pdf-modal-overlay');
    const pdfClose    = document.getElementById('concern-pdf-modal-close');
    const pdfFrame    = document.getElementById('concern-pdf-frame');

    const closeViewModal = () => viewOverlay?.classList.remove('active');
    const closePdfModal  = () => {
        pdfOverlay?.classList.remove('active');
        if (pdfFrame) pdfFrame.src = '';
    };

    bindOnce(viewClose, 'click', closeViewModal);
    bindOnce(viewOverlay, 'click', (e) => {
        if (e.target === viewOverlay) closeViewModal();
    });
    bindOnce(pdfClose, 'click', closePdfModal);
    bindOnce(pdfOverlay, 'click', (e) => {
        if (e.target === pdfOverlay) closePdfModal();
    });

    if (!window.concernEscBound) {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeCreateModal();
            closeViewModal();
            closePdfModal();
        });
        window.concernEscBound = true;
    }

    // --- Save as Draft ---
    const draftBtn = document.getElementById('concern-draft-btn');
    bindOnce(draftBtn, 'click', () => submitConcernForm('save_draft'));

    // --- Submit ---
    bindOnce(logForm, 'submit', (e) => {
        e.preventDefault();
        submitConcernForm('submit');
    });

    // --- Table row "View" clicks (delegated, survives re-renders) ---
    if (!window.concernViewDelegationBound) {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.concern-view-btn');
            if (btn) viewConcern(btn.dataset.issueId);
        });
        window.concernViewDelegationBound = true;
    }

    // --- Department filter ---
    const deptFilter = document.getElementById('concern-department-filter');
    bindOnce(deptFilter, 'change', () => loadConcerns());

    // --- Status filter ---
    const statusFilter = document.getElementById('concern-filter');
    bindOnce(statusFilter, 'change', () => loadConcerns());

    // --- Search ---
    const search = document.getElementById('concern-search');
    bindOnce(search, 'input', () => loadConcerns());
}

// Handles both hard refresh and sidebar navigation
window.addEventListener('page:loaded', initConcernModule);
document.addEventListener('DOMContentLoaded', initConcernModule);

// ── ATTACHMENT / FILE HELPERS ─────────────────────────────────────────────────
function validateConcernAttachment(file) {
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!CONCERN_ALLOWED_EXTENSIONS.includes(ext)) {
        return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
    }
    if (file.size > CONCERN_MAX_ATTACHMENT_BYTES) {
        return `That file is ${concernFormatFileSize(file.size)}. The maximum is 10 MB.`;
    }
    return null;
}

function concernFormatFileSize(bytes) {
    const n = Number(bytes) || 0;
    if (n >= 1024 * 1024) return (n / (1024 * 1024)).toFixed(1) + ' MB';
    if (n >= 1024)        return Math.round(n / 1024) + ' KB';
    return n + ' B';
}

// Files are never linked by their disk path. They are always requested through the
// controller, which checks the session/role and streams the bytes.
function concernFileUrl(issueId, kind, download = false) {
    const params = new URLSearchParams({ action: 'file', issue_id: issueId, kind });
    if (download) params.set('download', '1');
    return `${IssuesController}?${params.toString()}`;
}

// ── SUBMIT / DRAFT ────────────────────────────────────────────────────────────
async function submitConcernForm(action) {
    const logForm   = document.getElementById('concern-log-form');
    const formError = document.getElementById('concern-form-error');
    const submitBtn = document.getElementById('concern-submit-btn');
    const draftBtn  = document.getElementById('concern-draft-btn');

    formError.style.display = 'none';
    submitBtn.disabled = true;
    draftBtn.disabled  = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = action === 'submit' ? 'Submitting…' : submitBtn.textContent;

    // FormData includes the optional "file" input automatically (multipart/form-data).
    // Do NOT set a Content-Type header manually — the browser adds the multipart boundary.
    const formData = new FormData(logForm);
    formData.set('action', action);

    try {
        const res  = await fetch(IssuesController, { method: 'POST', body: formData });
        const json = await res.json();

        if (!json.success && json.message?.toLowerCase().includes('session expired')) {
            setTimeout(() => window.location.href = '/sms/index.php', 1500);
            return;
        }

        if (json.success) {
            document.getElementById('concern-modal-overlay')?.classList.remove('active');
            if (json.debug) console.error('Concern debug:', json.debug);
            showToast(action === 'submit' ? 'Concern logged successfully!' : 'Draft saved.', 'success');
            if (json.warning) showToast(json.warning, 'error');
            loadConcerns();
        } else {
            if (json.debug) console.error('Concern debug:', json.debug);
            formError.textContent   = json.message;
            formError.style.display = 'block';
        }
    } catch (err) {
        console.error(err);
        formError.textContent   = 'A network error occurred. Please try again.';
        formError.style.display = 'block';
    } finally {
        submitBtn.disabled    = false;
        draftBtn.disabled     = false;
        submitBtn.textContent = originalLabel;
    }
}

// ── LOAD CONCERNS ─────────────────────────────────────────────────────────────
async function loadConcerns() {
    const deptFilter    = document.getElementById('concern-department-filter');
    const statusFilter  = document.getElementById('concern-filter');
    const search        = document.getElementById('concern-search');
    const department_id = deptFilter && deptFilter.value !== '' ? deptFilter.value : '';
    const status         = statusFilter && statusFilter.value !== '' ? statusFilter.value : '';
    const searchTerm     = search ? search.value.trim() : '';
    const tbody          = document.querySelector('.concern-table tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="muted">Loading…</td></tr>`;

    const params = new URLSearchParams({ action: 'list' });
    if (department_id !== '') params.set('department_id', department_id);
    if (status !== '') params.set('status', status);
    if (searchTerm !== '') params.set('search', searchTerm);

    try {
        const res  = await fetch(`${IssuesController}?${params.toString()}`);
        const json = await res.json();

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load concerns.</td></tr>`;
            return;
        }

        if (!json.data?.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="muted">No concerns found.</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(row => `
            <tr data-issue-id="${esc(row.issue_id)}">
                <td>${esc(row.issue_id)}</td>
                <td>${esc(row.title)}</td>
                <td>${esc(row.department_name)}</td>
                <td>${esc(row.submitted_by)}</td>
                <td><span class="badge badge-${esc(row.status)}">${esc(CONCERN_STATUS_LABELS[row.status] ?? row.status)}</span></td>
                <td>${esc(row.submitted_on ?? 'N/A')}</td>
                <td>
                    <div class="actions">
                        <button type="button" class="btn-view concern-view-btn" data-issue-id="${esc(row.issue_id)}">View</button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        console.error('loadConcerns error:', err);
        tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load concerns.</td></tr>`;
    }
}

// ── VIEW / REVIEW / RESOLUTION ────────────────────────────────────────────────
async function viewConcern(issueId) {
    const overlay = document.getElementById('concern-view-modal-overlay');
    const body    = document.getElementById('concern-view-modal-body');
    if (!overlay || !body) return;

    body.innerHTML = '<p class="muted">Loading…</p>';
    overlay.classList.add('active');

    try {
        const res  = await fetch(`${IssuesController}?action=get&issue_id=${encodeURIComponent(issueId)}`);
        const json = await res.json();

        if (!json.success) {
            body.innerHTML = `<p class="muted">${esc(json.message)}</p>`;
            return;
        }

        renderConcernDetail(json.data);
    } catch (err) {
        console.error('viewConcern error:', err);
        body.innerHTML = '<p class="muted">Failed to load concern.</p>';
    }
}

function renderConcernDetail(concern) {
    const moduleRoot   = document.querySelector('.module-content[data-is-directress]');
    const isDirectress = moduleRoot?.dataset.isDirectress === '1';
    const body         = document.getElementById('concern-view-modal-body');
    const statusLabel  = CONCERN_STATUS_LABELS[concern.status] ?? concern.status;

    let reviewSection = '';
    if (isDirectress && concern.status === 'submitted') {
        reviewSection = `
            <div class="concern-review-box">
                <label for="concern-review-notes">Review Notes (optional)</label>
                <textarea id="concern-review-notes" rows="2" placeholder="Any notes for this review"></textarea>
                <div class="form-actions">
                    <button type="button" class="btn-log" id="concern-mark-reviewed" data-issue-id="${esc(concern.issue_id)}">Mark as Reviewed</button>
                </div>
            </div>`;
    } else if (isDirectress && concern.status === 'reviewed') {
        reviewSection = `
            <div class="concern-review-box">
                <label for="concern-decision-notes">Resolution Notes (optional)</label>
                <textarea id="concern-decision-notes" rows="2" placeholder="Any notes on how this was handled"></textarea>
                <div class="form-actions">
                    <button type="button" class="btn-resolve concern-decide-btn" data-decision="resolved" data-issue-id="${esc(concern.issue_id)}">Mark Resolved</button>
                    <button type="button" class="btn-cancel concern-decide-btn" data-decision="dismissed" data-issue-id="${esc(concern.issue_id)}">Dismiss</button>
                </div>
            </div>`;
    }

    // The server no longer sends file paths — only has_pdf / has_attachment flags.
    const pdfButton = concern.has_pdf
        ? `<button type="button" class="btn-view" id="concern-open-pdf" data-issue-id="${esc(concern.issue_id)}">View PDF</button>`
        : `<span class="muted">PDF not yet generated</span>`;

    const attachmentLine = concern.has_attachment
        ? `<span class="concern-attachment-info">
                <span class="concern-attachment-name">${esc(concern.attachment_name ?? 'Attachment')}${concern.attachment_size ? ` (${esc(concernFormatFileSize(concern.attachment_size))})` : ''}</span>
                <button type="button" class="btn-view" id="concern-open-attachment"
                        data-issue-id="${esc(concern.issue_id)}" data-mime="${esc(concern.attachment_mime ?? '')}">View</button>
                <a class="btn-view concern-link-btn" href="${esc(concernFileUrl(concern.issue_id, 'attachment', true))}">Download</a>
           </span>`
        : `<span class="muted">No attachment</span>`;

    const aiSummaryBox = `
        <div class="concern-ai-box">
            <div class="concern-ai-box-header">
                <strong>AI Summary</strong>
                <button type="button" class="btn-view" id="concern-summarize-btn" data-issue-id="${esc(concern.issue_id)}">
                    ${concern.ai_summary ? 'Regenerate' : 'Generate Summary'}
                </button>
            </div>
            <p id="concern-ai-summary-text" class="muted">${concern.ai_summary ? esc(concern.ai_summary) : 'No AI summary yet.'}</p>
        </div>`;

    body.innerHTML = `
        <div class="concern-detail-row"><span class="concern-detail-label">Title</span><span>${esc(concern.title)}</span></div>
        <div class="concern-detail-row"><span class="concern-detail-label">Department</span><span>${esc(concern.department_name ?? 'N/A')}</span></div>
        <div class="concern-detail-row"><span class="concern-detail-label">Submitted By</span><span>${esc(concern.submitted_by_name ?? 'N/A')}</span></div>
        <div class="concern-detail-row"><span class="concern-detail-label">Status</span><span class="badge badge-${esc(concern.status)}">${esc(statusLabel)}</span></div>
        <div class="concern-detail-row"><span class="concern-detail-label">Attachment</span>${attachmentLine}</div>

        <h4>Details</h4>
        <p>${esc(concern.details)}</p>

        <h4>Desired Resolution</h4>
        <p>${esc(concern.desired_resolution) || '<span class="muted">None provided.</span>'}</p>

        ${aiSummaryBox}

        <div class="form-actions">${pdfButton}</div>

        ${reviewSection}
    `;

    document.getElementById('concern-open-pdf')?.addEventListener('click', (e) => {
        openConcernFileModal(concernFileUrl(e.currentTarget.dataset.issueId, 'pdf'), 'Concern PDF');
    });
    document.getElementById('concern-open-attachment')?.addEventListener('click', (e) => {
        openConcernAttachment(e.currentTarget.dataset.issueId, e.currentTarget.dataset.mime);
    });
    document.getElementById('concern-summarize-btn')?.addEventListener('click', (e) => {
        summarizeConcern(e.currentTarget.dataset.issueId);
    });
    document.getElementById('concern-mark-reviewed')?.addEventListener('click', (e) => {
        const notes = document.getElementById('concern-review-notes')?.value ?? '';
        reviewConcern(e.currentTarget.dataset.issueId, notes);
    });
    body.querySelectorAll('.concern-decide-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const notes = document.getElementById('concern-decision-notes')?.value ?? '';
            decideConcern(e.currentTarget.dataset.issueId, e.currentTarget.dataset.decision, notes);
        });
    });
}

// Opens any controller file URL in the in-modal viewer (PDFs and images render in the iframe).
function openConcernFileModal(url, title = 'Concern PDF') {
    const overlay = document.getElementById('concern-pdf-modal-overlay');
    const frame   = document.getElementById('concern-pdf-frame');
    const heading = document.getElementById('concern-pdf-modal-title');
    if (!overlay || !frame || !url) return;
    if (heading) heading.textContent = title;
    frame.src = url;
    overlay.classList.add('active');
}

// PDFs and images preview in the modal; Word/Excel files can't, so they download instead.
function openConcernAttachment(issueId, mime) {
    const previewable = mime === 'application/pdf' || String(mime).startsWith('image/');
    if (previewable) {
        openConcernFileModal(concernFileUrl(issueId, 'attachment'), 'Attachment');
    } else {
        window.location.href = concernFileUrl(issueId, 'attachment', true);
    }
}

async function reviewConcern(issueId, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'review');
        formData.append('issue_id', issueId);
        formData.append('notes', notes);

        const res  = await fetch(IssuesController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast('Marked as reviewed.', 'success');
            viewConcern(issueId);
            loadConcerns();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('reviewConcern error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function decideConcern(issueId, decision, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'decide');
        formData.append('issue_id', issueId);
        formData.append('decision', decision);
        formData.append('notes', notes);

        const res  = await fetch(IssuesController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast(`Concern ${decision}.`, 'success');
            viewConcern(issueId);
            loadConcerns();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('decideConcern error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function summarizeConcern(issueId) {
    const btn  = document.getElementById('concern-summarize-btn');
    const text = document.getElementById('concern-ai-summary-text');
    if (btn) { btn.disabled = true; btn.textContent = 'Summarizing…'; }

    try {
        const res  = await fetch(`${IssuesController}?action=summarize&issue_id=${encodeURIComponent(issueId)}`, { method: 'POST' });
        const json = await res.json();

        if (json.success) {
            if (text) text.textContent = json.summary;
            if (btn) btn.textContent = 'Regenerate';
        } else {
            showToast(json.message, 'error');
            if (btn) btn.textContent = 'Generate Summary';
        }
    } catch (err) {
        console.error('summarizeConcern error:', err);
        showToast('A network error occurred.', 'error');
        if (btn) btn.textContent = 'Generate Summary';
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── TOAST ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    const bg = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';

    // Stack above any toast that is still visible (e.g. "logged" + a PDF warning).
    const bottom = 24 + document.querySelectorAll('.concern-toast').length * 64;

    const toast = document.createElement('div');
    toast.className = 'concern-toast';
    toast.textContent = message;
    toast.style.cssText = `
        position:fixed; bottom:${bottom}px; right:24px; z-index:9999;
        background:${bg}; color:#fff; padding:12px 20px;
        border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.2);
        font-size:.9rem; max-width:320px;
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ── HELPERS ───────────────────────────────────────────────────────────────────
function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}