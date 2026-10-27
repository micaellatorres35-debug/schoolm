const ReportController = 'controllers/ReportController.php';

const STATUS_LABELS = {
    draft: 'Draft',
    submitted: 'Submitted',
    reviewed: 'Reviewed',
    approved: 'Approved',
    rejected: 'Rejected',
};

// Attachment rules — keep in sync with FileStorage.php (the server re-checks everything).
const RSM_MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;
const RSM_ALLOWED_EXTENSIONS   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

// ── INIT ──────────────────────────────────────────────────────────────────────
function initReportModule() {
    const page = document.querySelector('.rsm-page');
    if (!page) return;

    const isDirectress = page.dataset.isDirectress === '1';

    loadReports();

    const reportForm = document.getElementById('report-form');

    // Prevents the same element from getting a listener attached twice when
    // init() runs more than once on unchanged DOM (e.g. DOMContentLoaded +
    // page:loaded both firing on a hard load with no container swap between them)
    function bindOnce(el, event, handler) {
        if (!el || el.dataset.rsmBound === '1') return;
        el.addEventListener(event, handler);
        el.dataset.rsmBound = '1';
    }

    // --- Create modal wiring ---
    const modalOverlay  = document.getElementById('rsm-modal-overlay');
    const openModalBtn  = document.getElementById('rsm-open-modal');
    const closeModalBtn = document.getElementById('rsm-modal-close');

    const openCreateModal = () => {
        reportForm?.reset();
        document.getElementById('report-id').value = '';
        syncAttachmentLabel();
        modalOverlay?.classList.add('active');
    };
    const closeCreateModal = () => {
        modalOverlay?.classList.remove('active');
        const formError = document.getElementById('report-form-error');
        if (formError) formError.style.display = 'none';
    };

    bindOnce(openModalBtn, 'click', openCreateModal);
    bindOnce(closeModalBtn, 'click', closeCreateModal);
    bindOnce(modalOverlay, 'click', (e) => {
        if (e.target === modalOverlay) closeCreateModal();
    });

    // --- Optional attachment picker ---
    const attachmentInput = document.getElementById('report-attachment');
    const attachmentClear = document.getElementById('rsm-file-clear');

    bindOnce(attachmentInput, 'change', () => {
        const file  = attachmentInput.files[0];
        const error = file ? validateAttachmentFile(file) : null;
        if (error) {
            attachmentInput.value = '';
            const formError = document.getElementById('report-form-error');
            if (formError) {
                formError.textContent   = error;
                formError.style.display = 'block';
            }
        }
        syncAttachmentLabel();
    });
    bindOnce(attachmentClear, 'click', () => {
        if (attachmentInput) attachmentInput.value = '';
        syncAttachmentLabel();
    });

    // --- View/PDF modal wiring ---
    const viewOverlay = document.getElementById('rsm-view-modal-overlay');
    const viewClose   = document.getElementById('rsm-view-modal-close');
    const pdfOverlay  = document.getElementById('rsm-pdf-modal-overlay');
    const pdfClose    = document.getElementById('rsm-pdf-modal-close');
    const pdfFrame    = document.getElementById('rsm-pdf-frame');

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

    if (!window.rsmEscBound) {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeCreateModal();
            closeViewModal();
            closePdfModal();
        });
        window.rsmEscBound = true;
    }

    // --- Save as Draft ---
    const draftBtn = document.getElementById('draft-btn');
    bindOnce(draftBtn, 'click', () => submitReportForm('save_draft'));

    // --- Submit Report ---
    bindOnce(reportForm, 'submit', (e) => {
        e.preventDefault();
        submitReportForm('submit');
    });

    // --- Table row "View" clicks (delegated, survives re-renders) ---
    if (!window.rsmViewDelegationBound) {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.rsm-view-report');
            if (btn) viewReport(btn.dataset.reportId);
        });
        window.rsmViewDelegationBound = true;
    }

    // --- Department filter ---
    const deptFilter = document.getElementById('rsm-filter');
    bindOnce(deptFilter, 'change', () => loadReports());

    // --- Status filter ---
    const statusFilter = document.getElementById('rsm-status-filter');
    bindOnce(statusFilter, 'change', () => loadReports());

    // --- Search ---
    const search = document.getElementById('rsm-search');
    bindOnce(search, 'input', applySearchFilter);
}

// Handles both hard refresh and sidebar navigation
window.addEventListener('page:loaded', initReportModule);
document.addEventListener('DOMContentLoaded', initReportModule);

// ── ATTACHMENT PICKER HELPERS ─────────────────────────────────────────────────
function validateAttachmentFile(file) {
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!RSM_ALLOWED_EXTENSIONS.includes(ext)) {
        return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
    }
    if (file.size > RSM_MAX_ATTACHMENT_BYTES) {
        return `That file is ${formatFileSize(file.size)}. The maximum is 10 MB.`;
    }
    return null;
}

// Updates the "No file chosen" text and the clear (×) button from the current input state.
function syncAttachmentLabel() {
    const input   = document.getElementById('report-attachment');
    const label   = document.getElementById('rsm-file-name');
    const clear   = document.getElementById('rsm-file-clear');
    const file    = input?.files?.[0];

    if (label) {
        label.textContent = file ? `${file.name} (${formatFileSize(file.size)})` : 'No file chosen';
        label.classList.toggle('rsm-file-name-set', !!file);
    }
    if (clear) clear.hidden = !file;
}

function formatFileSize(bytes) {
    const n = Number(bytes) || 0;
    if (n >= 1024 * 1024) return (n / (1024 * 1024)).toFixed(1) + ' MB';
    if (n >= 1024)        return Math.round(n / 1024) + ' KB';
    return n + ' B';
}

// ── FILE URLS ─────────────────────────────────────────────────────────────────
// Files are never linked by their disk path. They are always requested through the
// controller, which checks the session/role and streams the bytes.
function fileUrl(reportId, kind, download = false) {
    const params = new URLSearchParams({ action: 'file', report_id: reportId, kind });
    if (download) params.set('download', '1');
    return `${ReportController}?${params.toString()}`;
}

// ── SUBMIT / DRAFT ────────────────────────────────────────────────────────────
async function submitReportForm(action) {
    const reportForm = document.getElementById('report-form');
    const formError   = document.getElementById('report-form-error');
    const submitBtn   = document.getElementById('submit-btn');
    const draftBtn    = document.getElementById('draft-btn');

    formError.style.display = 'none';
    submitBtn.disabled = true;
    draftBtn.disabled  = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = action === 'submit' ? 'Submitting…' : submitBtn.textContent;

    // FormData includes the optional "attachment" file input automatically (multipart/form-data).
    // Do NOT set a Content-Type header manually — the browser adds the multipart boundary.
    const formData = new FormData(reportForm);
    formData.set('action', action);

    try {
        const res  = await fetch(ReportController, { method: 'POST', body: formData });
        const json = await res.json();

        if (!json.success && json.message?.toLowerCase().includes('session expired')) {
            setTimeout(() => window.location.href = '/sms/index.php', 1500);
            return;
        }

        if (json.success) {
            document.getElementById('rsm-modal-overlay')?.classList.remove('active');
            if (json.debug) console.error('Report debug:', json.debug);
            showToast(action === 'submit' ? 'Report submitted successfully!' : 'Draft saved.', 'success');
            if (json.warning) showToast(json.warning, 'error');
            loadReports();
        } else {
            formError.textContent   = json.message;
            formError.style.display = 'block';
        }
    } catch (err) {
        console.error(err);
        formError.textContent   = 'A network error occurred. Please try again.';
        formError.style.display = 'block';
    } finally {
        submitBtn.disabled     = false;
        draftBtn.disabled      = false;
        submitBtn.textContent  = originalLabel;
    }
}

// ── LOAD REPORTS ──────────────────────────────────────────────────────────────
async function loadReports() {
    const deptFilter    = document.getElementById('rsm-filter');
    const statusFilter  = document.getElementById('rsm-status-filter');
    const department_id = deptFilter && deptFilter.value !== '' ? deptFilter.value : '';
    const status         = statusFilter && statusFilter.value !== '' ? statusFilter.value : '';
    const tbody          = document.querySelector('.rsm-table tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">Loading…</td></tr>`;

    const params = new URLSearchParams({ action: 'list' });
    if (department_id !== '') params.set('department_id', department_id);
    if (status !== '') params.set('status', status);

    try {
        const res  = await fetch(`${ReportController}?${params.toString()}`);
        const json = await res.json();

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">Failed to load reports.</td></tr>`;
            return;
        }

        if (!json.data?.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">No reports found.</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(row => `
            <tr>
                <td>${esc(row.report_id)}</td>
                <td>${esc(row.title)}</td>
                <td>${esc(row.report_type ?? 'N/A')}</td>
                <td>${esc(row.department_name)}</td>
                <td>${esc(row.submitted_by)}</td>
                <td><span class="rsm-status rsm-status-${esc(row.status)}">${esc(STATUS_LABELS[row.status] ?? row.status)}</span></td>
                <td>${esc(row.submitted_at ?? 'N/A')}</td>
                <td class="rsm-actions-cell">
                    <button type="button" class="rsm-btn-view rsm-view-report" data-report-id="${esc(row.report_id)}">View</button>
                </td>
            </tr>
        `).join('');

        applySearchFilter();
    } catch (err) {
        console.error('loadReports error:', err);
        tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">Failed to load reports.</td></tr>`;
    }
}

// ── VIEW / REVIEW / DECISION ─────────────────────────────────────────────────
async function viewReport(reportId) {
    const overlay = document.getElementById('rsm-view-modal-overlay');
    const body    = document.getElementById('rsm-view-modal-body');
    if (!overlay || !body) return;

    body.innerHTML = '<p class="rsm-muted">Loading…</p>';
    overlay.classList.add('active');

    try {
        const res  = await fetch(`${ReportController}?action=get&report_id=${encodeURIComponent(reportId)}`);
        const json = await res.json();

        if (!json.success) {
            body.innerHTML = `<p class="rsm-muted">${esc(json.message)}</p>`;
            return;
        }

        renderReportDetail(json.data);
    } catch (err) {
        console.error('viewReport error:', err);
        body.innerHTML = '<p class="rsm-muted">Failed to load report.</p>';
    }
}

function renderReportDetail(report) {
    const page = document.querySelector('.rsm-page');
    const isDirectress = page?.dataset.isDirectress === '1';
    const body = document.getElementById('rsm-view-modal-body');

    const statusLabel = STATUS_LABELS[report.status] ?? report.status;

    let reviewSection = '';
    if (isDirectress && report.status === 'submitted') {
        reviewSection = `
            <div class="rsm-review-box">
                <label for="rsm-review-notes">Review Notes (optional)</label>
                <textarea id="rsm-review-notes" rows="2" placeholder="Any notes for this review"></textarea>
                <div class="rsm-actions">
                    <button type="button" class="rsm-btn-submit" id="rsm-mark-reviewed" data-report-id="${esc(report.report_id)}">Mark as Reviewed</button>
                </div>
            </div>`;
    } else if (isDirectress && report.status === 'reviewed') {
        reviewSection = `
            <div class="rsm-review-box">
                <label for="rsm-decision-notes">Decision Notes (optional)</label>
                <textarea id="rsm-decision-notes" rows="2" placeholder="Any notes for this decision"></textarea>
                <div class="rsm-actions">
                    <button type="button" class="rsm-btn-submit rsm-decide-btn" data-decision="approved" data-report-id="${esc(report.report_id)}">Approve</button>
                    <button type="button" class="rsm-btn-cancel rsm-decide-btn" data-decision="rejected" data-report-id="${esc(report.report_id)}">Reject</button>
                </div>
            </div>`;
    }

    // The server no longer sends file paths — only has_pdf / has_attachment flags.
    const pdfButton = report.has_pdf
        ? `<button type="button" class="rsm-btn-view" id="rsm-open-pdf" data-report-id="${esc(report.report_id)}">View PDF</button>`
        : `<span class="rsm-muted">PDF not yet generated</span>`;

    const attachmentRow = report.has_attachment
        ? `<div class="rsm-detail-row rsm-attachment-row">
                <span class="rsm-detail-label">Attachment</span>
                <span class="rsm-attachment-info">
                    <span class="rsm-attachment-name">${esc(report.attachment_name ?? 'Attachment')}${report.attachment_size ? ` (${esc(formatFileSize(report.attachment_size))})` : ''}</span>
                    <button type="button" class="rsm-btn-view" id="rsm-open-attachment"
                            data-report-id="${esc(report.report_id)}" data-mime="${esc(report.attachment_mime ?? '')}">View</button>
                    <a class="rsm-btn-view rsm-link-btn" href="${esc(fileUrl(report.report_id, 'attachment', true))}">Download</a>
                </span>
           </div>`
        : '';

    const aiSummaryBox = `
        <div class="rsm-ai-box">
            <div class="rsm-ai-box-header">
                <strong>AI Summary</strong>
                <button type="button" class="rsm-btn-view" id="rsm-summarize-btn" data-report-id="${esc(report.report_id)}">
                    ${report.ai_summary ? 'Regenerate' : 'Generate Summary'}
                </button>
            </div>
            <p id="rsm-ai-summary-text" class="rsm-muted">${report.ai_summary ? esc(report.ai_summary) : 'No AI summary yet.'}</p>
        </div>`;

    body.innerHTML = `
        <div class="rsm-detail-row"><span class="rsm-detail-label">Title</span><span>${esc(report.title)}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Type</span><span>${esc(report.report_type ?? 'N/A')}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Department</span><span>${esc(report.department_name ?? 'N/A')}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Submitted By</span><span>${esc(report.submitted_by_name ?? 'N/A')}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Status</span><span class="rsm-status rsm-status-${esc(report.status)}">${esc(statusLabel)}</span></div>
        ${attachmentRow}

        <h4>Summary</h4>
        <p>${esc(report.summary)}</p>

        <h4>Findings</h4>
        <p>${esc(report.findings) || '<span class="rsm-muted">None provided.</span>'}</p>

        <h4>Recommendations</h4>
        <p>${esc(report.recommendations) || '<span class="rsm-muted">None provided.</span>'}</p>

        ${aiSummaryBox}

        <div class="rsm-actions">${pdfButton}</div>

        ${reviewSection}
    `;

    document.getElementById('rsm-open-pdf')?.addEventListener('click', (e) => {
        openFileModal(fileUrl(e.currentTarget.dataset.reportId, 'pdf'), 'Report PDF');
    });
    document.getElementById('rsm-open-attachment')?.addEventListener('click', (e) => {
        openAttachment(e.currentTarget.dataset.reportId, e.currentTarget.dataset.mime);
    });
    document.getElementById('rsm-summarize-btn')?.addEventListener('click', (e) => {
        summarizeReport(e.currentTarget.dataset.reportId);
    });
    document.getElementById('rsm-mark-reviewed')?.addEventListener('click', (e) => {
        const notes = document.getElementById('rsm-review-notes')?.value ?? '';
        reviewReport(e.currentTarget.dataset.reportId, notes);
    });
    body.querySelectorAll('.rsm-decide-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const notes = document.getElementById('rsm-decision-notes')?.value ?? '';
            decideReport(e.currentTarget.dataset.reportId, e.currentTarget.dataset.decision, notes);
        });
    });
}

// Opens any controller file URL in the in-modal viewer (PDFs and images render in the iframe).
function openFileModal(url, title = 'Report PDF') {
    const overlay = document.getElementById('rsm-pdf-modal-overlay');
    const frame   = document.getElementById('rsm-pdf-frame');
    const heading = document.getElementById('rsm-pdf-modal-title');
    if (!overlay || !frame || !url) return;
    if (heading) heading.textContent = title;
    frame.src = url;
    overlay.classList.add('active');
}

// PDFs and images preview in the modal; Word/Excel files can't, so they download instead.
function openAttachment(reportId, mime) {
    const previewable = mime === 'application/pdf' || String(mime).startsWith('image/');
    if (previewable) {
        openFileModal(fileUrl(reportId, 'attachment'), 'Attachment');
    } else {
        window.location.href = fileUrl(reportId, 'attachment', true);
    }
}

async function reviewReport(reportId, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'review');
        formData.append('report_id', reportId);
        formData.append('notes', notes);

        const res  = await fetch(ReportController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast('Report marked as reviewed.', 'success');
            viewReport(reportId);
            loadReports();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('reviewReport error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function decideReport(reportId, decision, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'decide');
        formData.append('report_id', reportId);
        formData.append('decision', decision);
        formData.append('notes', notes);

        const res  = await fetch(ReportController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast(`Report ${decision}.`, 'success');
            viewReport(reportId);
            loadReports();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('decideReport error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function summarizeReport(reportId) {
    const btn  = document.getElementById('rsm-summarize-btn');
    const text = document.getElementById('rsm-ai-summary-text');
    if (btn) { btn.disabled = true; btn.textContent = 'Summarizing…'; }

    try {
        const res  = await fetch(`${ReportController}?action=summarize&report_id=${encodeURIComponent(reportId)}`, { method: 'POST' });
        const json = await res.json();

        if (json.success) {
            if (text) text.textContent = json.summary;
            if (btn) btn.textContent = 'Regenerate';
        } else {
            if (json.debug) console.error('AI summarization debug:', json.debug);
            showToast(json.message, 'error');
            if (btn) btn.textContent = 'Generate Summary';
        }
    } catch (err) {
        console.error('summarizeReport error:', err);
        showToast('A network error occurred.', 'error');
        if (btn) btn.textContent = 'Generate Summary';
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── SEARCH ────────────────────────────────────────────────────────────────────
function applySearchFilter() {
    const search = document.getElementById('rsm-search');
    const term   = search?.value.toLowerCase().trim() ?? '';
    const rows   = document.querySelectorAll('.rsm-table tbody tr');

    rows.forEach(row => {
        if (row.classList.contains('rsm-no-data')) return;
        const title     = row.cells[1]?.textContent.toLowerCase() ?? '';
        const submitter = row.cells[4]?.textContent.toLowerCase() ?? '';
        row.style.display = (title.includes(term) || submitter.includes(term)) ? '' : 'none';
    });
}

// ── TOAST ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    const bg = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';

    // Stack above any toast that is still visible (e.g. "submitted" + an attachment warning).
    const bottom = 24 + document.querySelectorAll('.rsm-toast').length * 64;

    const toast = document.createElement('div');
    toast.className = 'rsm-toast';
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