const ApprovalController = 'controllers/ApprovalController.php';

const APPROVAL_STATUS_LABELS = {
    draft: 'Draft',
    submitted: 'Submitted',
    reviewed: 'Reviewed',
    approved: 'Approved',
    rejected: 'Rejected',
};

const APPROVAL_ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
const APPROVAL_MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;

// ── INIT ──────────────────────────────────────────────────────────────────────
function initApprovalModule() {
    const moduleRoot = document.querySelector('.approval-module');
    if (!moduleRoot) return;

    const queueContainer = document.getElementById('approval-queue-list');
    const recentContainer = document.getElementById('approval-recent-list');
    const statusSelect = document.getElementById('approval-status-filter');
    const deptSelect = document.getElementById('approval-department-filter');
    const searchInput = document.getElementById('approval-search');
    const fromInput = document.getElementById('approval-date-from');
    const toInput = document.getElementById('approval-date-to');
    const form = document.getElementById('approval-form');
    const modalOverlay = document.getElementById('approval-modal-overlay');
    const viewOverlay = document.getElementById('approval-view-modal-overlay');
    const pdfOverlay = document.getElementById('approval-pdf-modal-overlay');

    if (moduleRoot.dataset.isApprover !== '1') {
        if (deptSelect) deptSelect.style.display = 'none';
    }

    if (!window.approvalInitBound) {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeApprovalModal();
                closeApprovalViewModal();
                closeApprovalPdfModal();
            }
        });

        document.addEventListener('click', (event) => {
            const rowButton = event.target.closest('.approval-request-action');
            if (rowButton) {
                const approvalId = rowButton.dataset.approvalId;
                if (approvalId) {
                    viewApproval(approvalId);
                }
            }

            const recentItem = event.target.closest('[data-recent-id]');
            if (recentItem) {
                const approvalId = recentItem.dataset.recentId;
                if (approvalId) {
                    viewApproval(approvalId);
                }
            }

            const pdfTrigger = event.target.closest('[data-open-pdf]');
            if (pdfTrigger) {
                const approvalId = pdfTrigger.dataset.openPdf;
                if (approvalId) {
                    openApprovalDocument(approvalId, 'pdf');
                }
            }

            const attachmentTrigger = event.target.closest('[data-open-attachment]');
            if (attachmentTrigger) {
                const approvalId = attachmentTrigger.dataset.openAttachment;
                if (approvalId) {
                    openApprovalDocument(approvalId, 'attachment');
                }
            }

            const summarizeButton = event.target.closest('#approval-summarize-btn');
            if (summarizeButton) {
                summarizeApproval(summarizeButton.dataset.approvalId);
            }

            const markedReviewed = event.target.closest('#approval-mark-reviewed');
            if (markedReviewed) {
                const approvalId = markedReviewed.dataset.approvalId;
                const notes = document.getElementById('approval-review-notes')?.value ?? '';
                reviewApproval(approvalId, notes);
            }

            const decisionButtons = event.target.closest('.approval-decide-btn');
            if (decisionButtons) {
                const decision = decisionButtons.dataset.decision;
                const approvalId = decisionButtons.dataset.approvalId;
                const notes = document.getElementById('approval-decision-notes')?.value ?? '';
                decideApproval(approvalId, decision, notes);
            }
        });

        window.approvalInitBound = true;
    }

    function bindOnce(element, eventName, handler) {
        if (!element || element.dataset.bound === '1') return;
        element.addEventListener(eventName, handler);
        element.dataset.bound = '1';
    }

    const closeApprovalModal = () => modalOverlay?.classList.remove('active');
    const closeApprovalViewModal = () => viewOverlay?.classList.remove('active');
    const closeApprovalPdfModal = () => {
        pdfOverlay?.classList.remove('active');
        const frame = document.getElementById('approval-pdf-frame');
        if (frame) frame.src = '';
    };

    bindOnce(document.getElementById('approval-open-modal'), 'click', () => {
        if (!form) return;
        form.reset();
        document.getElementById('approval-id').value = '';
        const errorBox = document.getElementById('approval-form-error');
        if (errorBox) {
            errorBox.hidden = true;
            errorBox.textContent = '';
        }
        modalOverlay?.classList.add('active');
    });

    bindOnce(document.getElementById('approval-modal-close'), 'click', closeApprovalModal);
    bindOnce(modalOverlay, 'click', (event) => {
        if (event.target === modalOverlay) closeApprovalModal();
    });
    bindOnce(document.getElementById('approval-view-modal-close'), 'click', closeApprovalViewModal);
    bindOnce(viewOverlay, 'click', (event) => {
        if (event.target === viewOverlay) closeApprovalViewModal();
    });
    bindOnce(document.getElementById('approval-pdf-modal-close'), 'click', closeApprovalPdfModal);
    bindOnce(pdfOverlay, 'click', (event) => {
        if (event.target === pdfOverlay) closeApprovalPdfModal();
    });

    const attachmentInput = document.getElementById('approval-attachment');
    bindOnce(attachmentInput, 'change', () => {
        const file = attachmentInput.files && attachmentInput.files[0] ? attachmentInput.files[0] : null;
        const error = file ? validateApprovalAttachment(file) : null;
        const formError = document.getElementById('approval-form-error');
        if (error) {
            attachmentInput.value = '';
            if (formError) {
                formError.textContent = error;
                formError.hidden = false;
            }
            return;
        }
        if (formError) {
            formError.textContent = '';
            formError.hidden = true;
        }
    });

    bindOnce(form, 'submit', (event) => {
        event.preventDefault();
        submitApprovalForm('submit');
    });

    bindOnce(document.getElementById('approval-draft-btn'), 'click', () => {
        submitApprovalForm('save_draft');
    });

    bindOnce(statusSelect, 'change', () => {
        loadQueue();
    });
    bindOnce(deptSelect, 'change', () => {
        loadQueue();
    });
    bindOnce(fromInput, 'change', () => {
        loadQueue();
    });
    bindOnce(toInput, 'change', () => {
        loadQueue();
    });
    bindOnce(document.getElementById('approval-clear-filters'), 'click', () => {
        if (searchInput) searchInput.value = '';
        if (deptSelect) deptSelect.value = '';
        if (statusSelect) statusSelect.value = 'pending';
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';
        loadQueue();
    });

    if (searchInput) {
        let searchTimer = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadQueue(), 300);
        });
    }

    loadOverview();
    loadQueue();
}

// Handles both hard refresh and sidebar navigation
window.addEventListener('page:loaded', initApprovalModule);
document.addEventListener('DOMContentLoaded', initApprovalModule);

// ── ATTACHMENT / FILE HELPERS ─────────────────────────────────────────────────
function validateApprovalAttachment(file) {
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!APPROVAL_ALLOWED_EXTENSIONS.includes(ext)) {
        return 'This file type is not allowed. Use PDF, JPG, JPEG or PNG.';
    }
    if (file.size > APPROVAL_MAX_ATTACHMENT_BYTES) {
        return 'That file is too large. The maximum is 10 MB.';
    }
    return null;
}

function approvalFileUrl(approvalId, kind, download = false) {
    const params = new URLSearchParams({ action: 'file', approval_id: approvalId, kind: kind || 'attachment' });
    if (download) params.set('download', '1');
    return `${ApprovalController}?${params.toString()}`;
}

function previewDocumentMarkup(url, mime) {
    const normalizedMime = String(mime || '').toLowerCase();
    if (normalizedMime === 'application/pdf') {
        return `<iframe src="${esc(url)}" title="Approval document preview" style="width:100%;min-height:400px;border:0;border-radius:10px;"></iframe>`;
    }
    if (normalizedMime.startsWith('image/')) {
        return `<img src="${esc(url)}" alt="Approval document preview" style="max-width:100%;border-radius:10px;display:block;margin:0 auto;">`;
    }
    return '<div class="approval-empty-text">Preview not available.</div>';
}

// ── SUBMIT / DRAFT ────────────────────────────────────────────────────────────
async function submitApprovalForm(action) {
    const form = document.getElementById('approval-form');
    const submitBtn = document.getElementById('approval-submit-btn');
    const draftBtn = document.getElementById('approval-draft-btn');
    const errorBox = document.getElementById('approval-form-error');
    if (!form) return;

    if (errorBox) {
        errorBox.hidden = true;
        errorBox.textContent = '';
    }
    if (submitBtn) submitBtn.disabled = true;
    if (draftBtn) draftBtn.disabled = true;

    const formData = new FormData(form);
    formData.set('action', action);

    try {
        const res = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (!json.success && json.message && json.message.toLowerCase().includes('session expired')) {
            setTimeout(() => {
                window.location.href = '/schoolm/index.php';
            }, 250);
            return;
        }

        if (json.success) {
            closeApprovalModal();
            if (json.warning) showToast(json.warning, 'error');
            showToast(action === 'submit' ? 'Approval request submitted successfully.' : 'Draft saved.', 'success');
            loadOverview();
            loadQueue();
            form.reset();
        } else {
            if (errorBox) {
                errorBox.textContent = json.message || 'Something went wrong.';
                errorBox.hidden = false;
            }
        }
    } catch (error) {
        console.error('submitApprovalForm error:', error);
        if (errorBox) {
            errorBox.textContent = 'A network error occurred. Please try again.';
            errorBox.hidden = false;
        }
    } finally {
        if (submitBtn) submitBtn.disabled = false;
        if (draftBtn) draftBtn.disabled = false;
    }
}

// ── LOAD OVERVIEW ────────────────────────────────────────────────────────────────
async function loadOverview() {
    const params = new URLSearchParams({ action: 'overview' });
    const isApprover = document.querySelector('.approval-module')?.dataset.isApprover === '1';
    const deptFilter = document.getElementById('approval-department-filter');
    if (isApprover && deptFilter && deptFilter.value) {
        params.set('department_id', deptFilter.value);
    }

    try {
        const res = await fetch(`${ApprovalController}?${params.toString()}`);
        const json = await res.json();
        if (!json.success) return;
        renderOverview(json.data || { counts: { submitted: 0, reviewed: 0, approved: 0, rejected: 0 }, recent: [] });
    } catch (error) {
        console.error('loadOverview error:', error);
    }
}

function renderOverview(data) {
    const counts = data.counts || {};
    const statKeys = ['submitted', 'reviewed', 'approved', 'rejected'];
    statKeys.forEach((key) => {
        const node = document.querySelector(`[data-stat="${key}"]`);
        if (node) node.textContent = Number(counts[key] ?? 0);
    });

    const recentList = document.getElementById('approval-recent-list');
    if (!recentList) return;

    const recent = Array.isArray(data.recent) ? data.recent : [];
    if (!recent.length) {
        recentList.innerHTML = `
            <div class="approval-empty-recent">
                <div class="approval-empty-title">No decisions yet</div>
                <div class="approval-empty-text">Approved and rejected requests will appear here.</div>
            </div>
        `;
        return;
    }

    recentList.innerHTML = recent.map((item) => {
        const status = (item.status || 'approved').toLowerCase();
        const label = APPROVAL_STATUS_LABELS[status] || status.charAt(0).toUpperCase() + status.slice(1);
        const decisionText = item.decided_by_name ? `${item.decided_by_name}` : 'Unknown';
        const dateText = item.decided_at ? formatDateValue(item.decided_at) : '—';
        return `
            <div class="approval-recent-item" data-recent-id="${esc(item.approval_id)}" tabindex="0">
                <div class="approval-recent-item-header">
                    <div class="approval-recent-title">${esc(item.title || 'Untitled request')}</div>
                    <span class="approval-status-pill approval-status-${status}">${label}</span>
                </div>
                <div class="approval-recent-meta">${esc(item.department_name || 'Department')}</div>
                <div class="approval-recent-meta">${esc(decisionText)} • ${esc(dateText)}</div>
            </div>
        `;
    }).join('');
}

// ── LOAD QUEUE ────────────────────────────────────────────────────────────────
async function loadQueue() {
    const loadingState = document.getElementById('approval-queue-loading');
    const queueList = document.getElementById('approval-queue-list');
    const emptyState = document.getElementById('approval-queue-empty');
    const queueCount = document.getElementById('approval-queue-count');
    const moreState = document.getElementById('approval-queue-more');
    if (!queueList) return;

    if (loadingState) loadingState.hidden = false;
    if (queueList) queueList.innerHTML = '';
    if (emptyState) emptyState.hidden = true;
    if (moreState) moreState.hidden = true;

    const params = new URLSearchParams({ action: 'queue' });
    const status = document.getElementById('approval-status-filter')?.value || 'pending';
    const departmentId = document.getElementById('approval-department-filter')?.value || '';
    const search = document.getElementById('approval-search')?.value || '';
    const dateFrom = document.getElementById('approval-date-from')?.value || '';
    const dateTo = document.getElementById('approval-date-to')?.value || '';

    params.set('status', status);
    if (departmentId) params.set('department_id', departmentId);
    if (search) params.set('search', search);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);

    try {
        const response = await fetch(`${ApprovalController}?${params.toString()}`);
        const json = await response.json();
        if (!json.success) {
            queueList.innerHTML = '<div class="approval-empty-state"><p>Failed to load requests.</p></div>';
            return;
        }

        renderQueue(json.data || [], !!json.has_more, status, queueCount, queueList, emptyState, moreState);
    } catch (error) {
        console.error('loadQueue error:', error);
        queueList.innerHTML = '<div class="approval-empty-state"><p>Failed to load requests.</p></div>';
    } finally {
        if (loadingState) loadingState.hidden = true;
    }
}

function renderQueue(rows, hasMore, status, queueCount, queueList, emptyState, moreState) {
    const isApprover = document.querySelector('.approval-module')?.dataset.isApprover === '1';
    if (!rows.length) {
        queueList.innerHTML = '';
        if (emptyState) {
            emptyState.hidden = false;
            emptyState.innerHTML = '<div class="approval-empty-icon">No requests</div><p>No requests match your filters.</p>';
        }
        if (queueCount) queueCount.textContent = '0 requests';
        if (moreState) moreState.hidden = true;
        return;
    }

    if (queueCount) queueCount.textContent = `${rows.length} request${rows.length === 1 ? '' : 's'}`;

    queueList.innerHTML = rows.map((item) => {
        const rowStatus = (item.status || 'submitted').toLowerCase();
        const actionLabel = isApprover && (rowStatus === 'submitted' || rowStatus === 'reviewed') ? 'Review request' : 'View';
        const waitingText = item.waiting_days !== null && item.waiting_days !== undefined ? `Waiting ${item.waiting_days} day${Number(item.waiting_days) === 1 ? '' : 's'}` : '';
        const rowDate = item.submitted_on ? formatDateValue(item.submitted_on, true) : '—';
        const title = item.title || 'Untitled request';
        const submittedBy = item.submit_by || 'Unknown';
        const department = item.department_name || 'Department';

        return `
            <div class="approval-request-item" data-approval-id="${esc(item.approval_id)}">
                <div class="approval-request-main">
                    <div class="approval-request-id">#${esc(item.approval_id)}</div>
                    <div class="approval-request-title">${esc(title)}</div>
                    <div class="approval-request-meta">
                        <span>Department <strong>${esc(department)}</strong></span>
                        <span>Submitted by <strong>${esc(submittedBy)}</strong></span>
                        <span>Submitted <strong>${esc(rowDate)}</strong></span>
                    </div>
                </div>
                <div class="approval-request-side">
                    <span class="approval-status-pill approval-status-${rowStatus}">${esc(APPROVAL_STATUS_LABELS[rowStatus] || rowStatus)}</span>
                    ${waitingText ? `<span class="approval-waiting-text">${esc(waitingText)}</span>` : ''}
                    <button type="button" class="approval-primary-btn approval-request-action" data-approval-id="${esc(item.approval_id)}">${actionLabel}</button>
                </div>
            </div>
        `;
    }).join('');

    if (emptyState) emptyState.hidden = true;
    if (moreState) {
        moreState.hidden = !hasMore;
        if (hasMore) moreState.textContent = 'Showing the first 100 results. Narrow your filters.';
    }
}

// ── VIEW / REVIEW / DECISION ─────────────────────────────────────────────────
async function viewApproval(approvalId) {
    const overlay = document.getElementById('approval-view-modal-overlay');
    const body = document.getElementById('approval-view-modal-body');
    if (!overlay || !body) return;

    body.innerHTML = '<div class="approval-loading-state">Loading…</div>';
    overlay.classList.add('active');

    try {
        const response = await fetch(`${ApprovalController}?action=get&approval_id=${encodeURIComponent(approvalId)}`);
        const json = await response.json();
        if (!json.success) {
            body.innerHTML = `<div class="approval-empty-state"><p>${esc(json.message || 'Unable to load approval request.')}</p></div>`;
            return;
        }
        renderApprovalDetail(json.data);
    } catch (error) {
        console.error('viewApproval error:', error);
        body.innerHTML = '<div class="approval-empty-state"><p>Failed to load approval request.</p></div>';
    }
}

function renderApprovalDetail(approval) {
    const body = document.getElementById('approval-view-modal-body');
    if (!body) return;

    const isApprover = document.querySelector('.approval-module')?.dataset.isApprover === '1';
    const status = (approval.status || '').toLowerCase();
    const statusText = APPROVAL_STATUS_LABELS[status] || status;
    const attachmentUrl = approval.has_attachment ? approvalFileUrl(approval.approval_id, 'attachment') : '';
    const documentName = approval.has_attachment ? (approval.attachment_name || 'Document') : '';
    const documentSize = approval.has_attachment && approval.attachment_size != null ? formatFileSize(Number(approval.attachment_size) || 0) : '';

    const documentValue = approval.has_attachment ? `
        <span class="approval-document-file-name">${esc(documentName)}${documentSize ? ` (${esc(documentSize)})` : ''}</span>
        <div class="approval-document-actions">
            <button type="button" class="approval-secondary-btn" data-open-attachment="${esc(approval.approval_id)}">Full screen</button>
            <a href="${approvalFileUrl(approval.approval_id, 'attachment', true)}" class="approval-secondary-link" target="_blank" rel="noopener noreferrer">Download</a>
        </div>
    ` : 'No document uploaded';

    let reviewSection = '';
    if (isApprover && status === 'submitted') {
        reviewSection = `
            <div class="approval-review-box">
                <label for="approval-review-notes">Review notes (optional)</label>
                <textarea id="approval-review-notes" rows="3" placeholder="Anything the decision should take into account"></textarea>
                <div class="approval-inline-error" id="approval-review-error" hidden></div>
                <div class="approval-modal-actions">
                    <button type="button" id="approval-mark-reviewed" data-approval-id="${esc(approval.approval_id)}" class="approval-primary-btn">Mark as reviewed</button>
                </div>
                <div class="approval-review-helper">Reviewing moves this request to the decision step.</div>
            </div>
        `;
    }

    if (isApprover && status === 'reviewed') {
        reviewSection = `
            <div class="approval-review-box">
                <label for="approval-decision-notes">Decision notes</label>
                <textarea id="approval-decision-notes" rows="3" placeholder="Anything the decision should take into account"></textarea>
                <div class="approval-inline-error" id="approval-decision-error" hidden></div>
                <div class="approval-modal-actions approval-decision-actions">
                    <button type="button" class="approval-primary-btn approval-decide-btn" data-decision="approved" data-approval-id="${esc(approval.approval_id)}">Approve</button>
                    <button type="button" class="approval-danger-btn approval-decide-btn" data-decision="rejected" data-approval-id="${esc(approval.approval_id)}">Reject</button>
                </div>
            </div>
        `;
    }

    const historyEntries = Array.isArray(approval.history) ? approval.history : [];
    const historyMarkup = historyEntries.length ? historyEntries.map((entry) => {
        const when = entry.date ? formatDateValue(entry.date, true) : '—';
        const eventLabel = {
            Created: 'Request created',
            Submitted: 'Submitted for approval',
            Reviewed: 'Reviewed',
            Approved: 'Approved',
            Rejected: 'Rejected',
        }[(entry.event || '').trim()] || (entry.event || 'Update');
        const notes = entry.notes ? `<div class="approval-history-note">${esc(entry.notes)}</div>` : '';
        const personLine = entry.by ? `<div class="approval-history-user">${esc(entry.by)}</div>` : '';
        return `
            <div class="approval-history-item">
                <div class="approval-history-main">
                    <div class="approval-history-heading">${esc(eventLabel)}</div>
                    ${personLine}
                    ${notes}
                </div>
                <div class="approval-history-date">${esc(when)}</div>
            </div>
        `;
    }).join('') : '<div class="approval-empty-text">No history available.</div>';

    const aiSummary = approval.ai_summary ? esc(approval.ai_summary) : 'No summary yet.';
    const documentPreview = approval.has_attachment ? `
        <div class="approval-document-preview">
            ${previewDocumentMarkup(attachmentUrl, approval.attachment_mime || '')}
        </div>
    ` : '';

    body.innerHTML = `
        <div class="approval-detail-list">
            <div class="approval-detail-row">
                <div class="approval-detail-label">Request ID</div>
                <div class="approval-detail-value">#${esc(approval.approval_id)}</div>
            </div>
            <div class="approval-detail-row">
                <div class="approval-detail-label">Title</div>
                <div class="approval-detail-value">${esc(approval.title || 'Untitled request')}</div>
            </div>
            <div class="approval-detail-row">
                <div class="approval-detail-label">Department</div>
                <div class="approval-detail-value">${esc(approval.department_name || 'N/A')}</div>
            </div>
            <div class="approval-detail-row">
                <div class="approval-detail-label">Submitted By</div>
                <div class="approval-detail-value">${esc(approval.submit_by_name || 'N/A')}</div>
            </div>
            <div class="approval-detail-row">
                <div class="approval-detail-label">Submitted</div>
                <div class="approval-detail-value">${esc(approval.submitted_on ? formatDateValue(approval.submitted_on, true) : 'N/A')}</div>
            </div>
            <div class="approval-detail-row">
                <div class="approval-detail-label">Status</div>
                <div class="approval-detail-value"><span class="approval-status-pill approval-status-${status}">${esc(statusText.toUpperCase())}</span></div>
            </div>
            <div class="approval-detail-row approval-detail-row-document">
                <div class="approval-detail-label">Request document</div>
                <div class="approval-detail-value approval-detail-document-value">${documentValue}</div>
            </div>
        </div>

        ${documentPreview}

        <div class="approval-section-block">
            <h4>Description</h4>
            <div class="approval-text-block">${esc(approval.description || 'No description provided.')}</div>
        </div>

        <div class="approval-section-block">
            <h4>Justification</h4>
            <div class="approval-text-block approval-pre-wrap">${esc(approval.justification || 'No justification provided.')}</div>
        </div>

        <div class="approval-section-block">
            <h4>Decision history and comments</h4>
            <div class="approval-history-list">${historyMarkup}</div>
        </div>

        <div class="approval-section-block">
            <div class="approval-ai-header">
                <h4>AI summary</h4>
                <button type="button" class="approval-secondary-btn" id="approval-summarize-btn" data-approval-id="${esc(approval.approval_id)}">${approval.ai_summary ? 'Regenerate' : 'Generate summary'}</button>
            </div>
            <div class="approval-ai-summary">${aiSummary}</div>
        </div>

        ${reviewSection}
    `;

    document.getElementById('approval-open-pdf')?.addEventListener('click', (e) => {
        openApprovalFileModal(approvalFileUrl(e.currentTarget.dataset.approvalId, 'pdf'), 'Approval PDF');
    });
    document.getElementById('approval-open-attachment')?.addEventListener('click', (e) => {
        openApprovalAttachment(e.currentTarget.dataset.approvalId, e.currentTarget.dataset.mime);
    });
    document.getElementById('approval-summarize-btn')?.addEventListener('click', (e) => {
        summarizeApproval(e.currentTarget.dataset.approvalId);
    });
    document.getElementById('approval-mark-reviewed')?.addEventListener('click', (e) => {
        const notes = document.getElementById('approval-review-notes')?.value ?? '';
        reviewApproval(e.currentTarget.dataset.approvalId, notes);
    });
    body.querySelectorAll('.approval-decide-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const notes = document.getElementById('approval-decision-notes')?.value ?? '';
            decideApproval(e.currentTarget.dataset.approvalId, e.currentTarget.dataset.decision, notes);
        });
    });
}

// Opens any controller file URL in the in-modal viewer (PDFs and images render in the iframe).
function openApprovalFileModal(url, title = 'Approval PDF') {
    const overlay = document.getElementById('approval-pdf-modal-overlay');
    const frame = document.getElementById('approval-pdf-frame');
    const heading = document.getElementById('approval-pdf-modal-title');
    if (!overlay || !frame || !url) return;
    if (heading) heading.textContent = title;
    frame.src = url;
    overlay.classList.add('active');
}

// PDFs and images preview in the modal; Word/Excel files can't, so they download instead.
function openApprovalAttachment(approvalId, mime) {
    const previewable = mime === 'application/pdf' || String(mime).startsWith('image/');
    if (previewable) {
        openApprovalFileModal(approvalFileUrl(approvalId, 'attachment'), 'Attachment');
    } else {
        window.location.href = approvalFileUrl(approvalId, 'attachment', true);
    }
}

async function reviewApproval(approvalId, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'review');
        formData.append('approval_id', approvalId);
        formData.append('notes', notes || '');

        const response = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await response.json();
        if (json.success) {
            showToast('Marked as reviewed.', 'success');
            loadOverview();
            loadQueue();
            viewApproval(approvalId);
        } else {
            showToast(json.message || 'Unable to review this request.', 'error');
        }
    } catch (error) {
        console.error('reviewApproval error:', error);
        showToast('A network error occurred.', 'error');
    }
}

async function decideApproval(approvalId, decision, notes) {
    const trimmedNotes = (notes || '').trim();
    if (decision === 'rejected' && !trimmedNotes) {
        const errorBox = document.getElementById('approval-decision-error');
        if (errorBox) {
            errorBox.textContent = 'A rejection reason is required.';
            errorBox.hidden = false;
        }
        return;
    }

    if (!window.confirm(decision === 'approved' ? 'Approve this request?' : 'Reject this request?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'decide');
        formData.append('approval_id', approvalId);
        formData.append('decision', decision);
        formData.append('notes', trimmedNotes);

        const response = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await response.json();
        if (json.success) {
            showToast(decision === 'approved' ? 'Request approved.' : 'Request rejected.', 'success');
            loadOverview();
            loadQueue();
            viewApproval(approvalId);
        } else {
            const errorBox = document.getElementById('approval-decision-error');
            if (errorBox) {
                errorBox.textContent = json.message || 'Unable to record this decision.';
                errorBox.hidden = false;
            }
            showToast(json.message || 'Unable to record this decision.', 'error');
        }
    } catch (error) {
        console.error('decideApproval error:', error);
        showToast('A network error occurred.', 'error');
    }
}

async function summarizeApproval(approvalId) {
    const button = document.getElementById('approval-summarize-btn');
    if (button) {
        button.disabled = true;
        button.textContent = 'Summarizing…';
    }
    try {
        const response = await fetch(`${ApprovalController}?action=summarize&approval_id=${encodeURIComponent(approvalId)}`);
        const json = await response.json();
        if (json.success) {
            const summaryNode = document.querySelector('.approval-ai-summary');
            if (summaryNode) summaryNode.textContent = json.summary || 'No summary available.';
            if (button) button.textContent = 'Regenerate';
        } else {
            showToast(json.message || 'AI summary unavailable.', 'error');
            if (button) button.textContent = 'Generate summary';
        }
    } catch (error) {
        console.error('summarizeApproval error:', error);
        showToast('A network error occurred while generating the summary.', 'error');
        if (button) button.textContent = 'Generate summary';
    } finally {
        if (button) button.disabled = false;
    }
}

function openApprovalDocument(approvalId, kind) {
    const url = approvalFileUrl(approvalId, kind);
    const frame = document.getElementById('approval-pdf-frame');
    const title = document.getElementById('approval-pdf-modal-title');
    if (frame) {
        frame.src = url;
    }
    if (title) {
        title.textContent = kind === 'attachment' ? 'Request document' : 'Approval document';
    }
    document.getElementById('approval-pdf-modal-overlay')?.classList.add('active');
}

function formatDateValue(value, includeTime = false) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    const dateOptions = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: false };
    const formattedDate = date.toLocaleDateString(undefined, dateOptions);
    if (!includeTime) return formattedDate;
    return `${formattedDate} ${date.toLocaleTimeString(undefined, timeOptions)}`;
}

function formatFileSize(sizeInBytes) {
    if (typeof sizeInBytes !== 'number' || Number.isNaN(sizeInBytes) || sizeInBytes < 0) return '';

    const units = ['bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(sizeInBytes) / Math.log(1024));
    const size = (sizeInBytes / Math.pow(1024, i)).toFixed(2);

    return `${size} ${units[i]}`;
}

function esc(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'approval-toast approval-toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3200);
}