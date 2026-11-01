const ApprovalController = 'controllers/ApprovalController.php';

const APPROVAL_STATUS_LABELS = {
    draft: 'Draft',
    submitted: 'Submitted',
    reviewed: 'Reviewed',
    approved: 'Approved',
    rejected: 'Rejected',
};

// Attachment rules — keep in sync with FileStorage.php (the server re-checks everything).
const APPROVAL_MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;
const APPROVAL_ALLOWED_EXTENSIONS   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
/* ============================================================
   School Directress — Approval & Decision Support
   Decision Command Center (all selectors/classes prefixed sd-approval-)
   ============================================================ */
(function () {
    'use strict';

    // Scripts may be injected again on page navigation. One copy is enough: it re-initialises
    // itself on every 'page:loaded' for whatever DOM is currently on screen.
    if (window.sdApprovalLoaded) return;
    window.sdApprovalLoaded = true;

    const CONTROLLER = 'controllers/ApprovalController.php';

    const STATUS_LABELS = {
        draft: 'Draft',
        submitted: 'Submitted',
        reviewed: 'Reviewed',
        approved: 'Approved',
        rejected: 'Rejected',
    };

    // Keep in sync with APPROVAL_UPLOAD_EXTENSIONS in the controller (the server re-checks everything).
    const MAX_BYTES = 10 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    const state = {
        root: null,
        isDirectress: false,
        currentId: null,
        lastTrigger: null,
        searchTimer: null,
        queueSeq: 0,
        requestSeq: 0,
        confirmCancel: null,
    };

    // ── Small helpers ────────────────────────────────────────────────────────
    const $ = (id) => document.getElementById(id);

    function esc(value) {
        const d = document.createElement('div');
        d.textContent = String(value ?? '');
        return d.innerHTML;
    }

    function fmtDate(value, withTime) {
        if (!value) return '';
        const d = new Date(String(value).replace(' ', 'T'));
        if (isNaN(d.getTime())) return String(value);
        const opts = { year: 'numeric', month: 'short', day: 'numeric' };
        if (withTime) { opts.hour = 'numeric'; opts.minute = '2-digit'; }
        return d.toLocaleString(undefined, opts);
    }

    function fmtSize(bytes) {
        const n = Number(bytes) || 0;
        if (n >= 1024 * 1024) return (n / (1024 * 1024)).toFixed(1) + ' MB';
        if (n >= 1024) return Math.round(n / 1024) + ' KB';
        return n + ' B';
    }

    function badge(status) {
        const label = STATUS_LABELS[status] ?? status ?? '';
        return `<span class="sd-approval-badge sd-approval-badge--${esc(status)}">${esc(label)}</span>`;
    }

    // Files are never linked by disk path. Every request goes through the controller,
    // which checks the session/role and streams the bytes.
    function fileUrl(approvalId, kind, download) {
        const params = new URLSearchParams({ action: 'file', approval_id: approvalId, kind });
        if (download) params.set('download', '1');
        return `${CONTROLLER}?${params.toString()}`;
    }

    function bindOnce(el, event, handler) {
        if (!el || el.dataset['sdBound' + event] === '1') return;
        el.addEventListener(event, handler);
        el.dataset['sdBound' + event] = '1';
    }

    // ── Network ──────────────────────────────────────────────────────────────
    async function parseResponse(res) {
        let json;
        try {
            json = await res.json();
        } catch (e) {
            throw new Error('The server sent an unexpected response.');
        }
        if (json.debug) console.error('Approval debug:', json.debug);
        if (!json.success && /session expired/i.test(json.message || '')) {
            setTimeout(() => { window.location.href = '/sms/index.php'; }, 1500);
        }
        return json;
    }

    async function getJson(params) {
        const res = await fetch(`${CONTROLLER}?${new URLSearchParams(params).toString()}`, { credentials: 'same-origin' });
        return parseResponse(res);
    }

    async function postForm(formData) {
        const res = await fetch(CONTROLLER, { method: 'POST', body: formData, credentials: 'same-origin' });
        return parseResponse(res);
    }

    // ── Toasts & confirmation ────────────────────────────────────────────────
    function toast(message, type) {
        const host = $('sd-approval-toasts');
        if (!host) return;
        const el = document.createElement('div');
        el.className = `sd-approval-toast sd-approval-toast--${type || 'info'}`;
        el.textContent = message;
        host.appendChild(el);
        setTimeout(() => el.remove(), 4200);
    }

    function confirmAction({ title, message, confirmLabel, danger }) {
        return new Promise((resolve) => {
            const overlay = $('sd-approval-confirm-overlay');
            const ok = $('sd-approval-confirm-ok');
            const cancel = $('sd-approval-confirm-cancel');

            $('sd-approval-confirm-title').textContent = title;
            $('sd-approval-confirm-message').textContent = message;
            ok.textContent = confirmLabel || 'Confirm';
            ok.classList.toggle('sd-approval-btn--danger', !!danger);
            ok.classList.toggle('sd-approval-btn--primary', !danger);

            const finish = (result) => {
                overlay.classList.remove('is-open');
                ok.removeEventListener('click', onOk);
                cancel.removeEventListener('click', onCancel);
                overlay.removeEventListener('click', onBackground);
                state.confirmCancel = null;
                resolve(result);
            };
            const onOk = () => finish(true);
            const onCancel = () => finish(false);
            const onBackground = (e) => { if (e.target === overlay) finish(false); };

            state.confirmCancel = onCancel;
            ok.addEventListener('click', onOk);
            cancel.addEventListener('click', onCancel);
            overlay.addEventListener('click', onBackground);
            overlay.classList.add('is-open');
            (danger ? cancel : ok).focus();
        });
    }

    async function withBusy(buttons, task) {
        const list = buttons.filter(Boolean);
        list.forEach((b) => { b.disabled = true; });
        try {
            return await task();
        } finally {
            list.forEach((b) => { b.disabled = false; });
        }
    }

    // ── Reusable state blocks (empty / error) ────────────────────────────────
    function stateHtml(title, text, buttonLabel, buttonAction) {
        return `<div class="sd-approval-state">
            <strong>${esc(title)}</strong>
            ${text ? `<span>${esc(text)}</span>` : ''}
            ${buttonLabel ? `<button type="button" class="sd-approval-btn sd-approval-btn--small" data-action="${esc(buttonAction)}">${esc(buttonLabel)}</button>` : ''}
        </div>`;
    }

    // ── Filters ──────────────────────────────────────────────────────────────
    function defaultStatus() {
        return state.isDirectress ? 'pending' : 'all';
    }

    function readFilters() {
        return {
            search: ($('sd-approval-search')?.value || '').trim(),
            department_id: $('sd-approval-department')?.value || '',
            status: $('sd-approval-status')?.value || defaultStatus(),
            date_from: $('sd-approval-date-from')?.value || '',
            date_to: $('sd-approval-date-to')?.value || '',
        };
    }

    function hasActiveFilters(f) {
        return !!(f.search || f.department_id || f.date_from || f.date_to || f.status !== defaultStatus());
    }

    function clearFilters() {
        if ($('sd-approval-search')) $('sd-approval-search').value = '';
        if ($('sd-approval-department')) $('sd-approval-department').value = '';
        if ($('sd-approval-status')) $('sd-approval-status').value = defaultStatus();
        if ($('sd-approval-date-from')) $('sd-approval-date-from').value = '';
        if ($('sd-approval-date-to')) $('sd-approval-date-to').value = '';
        loadQueue();
        loadOverview();
    }

    function syncChrome(filters) {
        const title = $('sd-approval-queue-title');
        if (title) {
            title.textContent = !state.isDirectress ? 'Department requests'
                : (filters.status === 'pending' ? 'Needs your decision' : 'Requests');
        }
        state.root.querySelectorAll('.sd-approval-stage').forEach((stage) => {
            stage.setAttribute('aria-pressed', stage.dataset.status === filters.status ? 'true' : 'false');
            stage.classList.toggle('is-active', stage.dataset.status === filters.status);
        });
    }

    // ── Overview: summary counts + recent decisions ──────────────────────────
    function renderCounts(counts) {
        ['submitted', 'reviewed', 'approved', 'rejected'].forEach((key) => {
            const el = state.root.querySelector(`[data-count="${key}"]`);
            if (el) el.textContent = counts ? String(counts[key] ?? 0) : '–';
        });
    }

    function renderRecent(rows) {
        const host = $('sd-approval-recent-list');
        if (!rows || !rows.length) {
            host.innerHTML = stateHtml('No decisions yet', 'Approved and rejected requests will appear here.');
            return;
        }
        host.innerHTML = rows.map((r) => {
            const approved = r.status === 'approved';
            const by = r.decided_by_name && r.decided_by_name.trim() ? `by ${r.decided_by_name}` : '';
            return `<button type="button" class="sd-approval-recent-item" data-action="review" data-id="${esc(r.approval_id)}">
                <span class="sd-approval-recent-title">${esc(r.title || 'Untitled request')}</span>
                <span class="sd-approval-recent-line">
                    <span class="sd-approval-result sd-approval-result--${approved ? 'approved' : 'rejected'}">${approved ? 'Approved' : 'Rejected'}</span>
                    <span>${esc(fmtDate(r.decided_at))}</span>
                </span>
                <span class="sd-approval-recent-line sd-approval-muted">
                    <span>${esc(r.department_name || 'No department')}</span>
                    ${by ? `<span>${esc(by)}</span>` : ''}
                </span>
            </button>`;
        }).join('');
    }

    async function loadOverview() {
        const host = $('sd-approval-recent-list');
        if (!host) return;
        host.setAttribute('aria-busy', 'true');
        try {
            const json = await getJson({ action: 'overview', department_id: $('sd-approval-department')?.value || '' });
            if (!json.success) throw new Error(json.message || 'Request failed');
            renderCounts(json.data.counts);
            renderRecent(json.data.recent);
        } catch (err) {
            console.error('loadOverview error:', err);
            renderCounts(null);
            host.innerHTML = stateHtml('Could not load recent decisions', 'Check your connection, then try again.', 'Try again', 'retry-overview');
        } finally {
            host.setAttribute('aria-busy', 'false');
        }
    }

    // ── Queue ────────────────────────────────────────────────────────────────
    function waitingText(row) {
        if (row.waiting_days === null || row.waiting_days === undefined || row.waiting_days === '') return '';
        const n = Number(row.waiting_days);
        if (n <= 0) return 'Submitted today';
        return n === 1 ? 'Waiting 1 day' : `Waiting ${n} days`;
    }

    function queueItemHtml(row) {
        const actionable = state.isDirectress && (row.status === 'submitted' || row.status === 'reviewed');
        const waiting = waitingText(row);
        const submitted = row.submitted_on ? fmtDate(row.submitted_on) : 'Not submitted yet';
        return `<article class="sd-approval-item sd-approval-item--${esc(row.status)}">
            <div class="sd-approval-item-main">
                <div class="sd-approval-item-top">
                    <span class="sd-approval-id">#${esc(row.approval_id)}</span>
                    <h3 class="sd-approval-item-title">${esc(row.title || 'Untitled request')}</h3>
                </div>
                <dl class="sd-approval-meta">
                    <div><dt>Department</dt><dd>${esc(row.department_name || 'N/A')}</dd></div>
                    <div><dt>Submitted by</dt><dd>${esc(row.submit_by || 'N/A')}</dd></div>
                    <div><dt>Submitted</dt><dd>${esc(submitted)}</dd></div>
                </dl>
            </div>
            <div class="sd-approval-item-side">
                ${badge(row.status)}
                ${waiting ? `<span class="sd-approval-waiting">${esc(waiting)}</span>` : ''}
                <button type="button" class="sd-approval-btn ${actionable ? 'sd-approval-btn--primary' : ''} sd-approval-btn--small"
                        data-action="review" data-id="${esc(row.approval_id)}">${actionable ? 'Review request' : 'View request'}</button>
            </div>
        </article>`;
    }

    async function loadQueue() {
        const host = $('sd-approval-queue-list');
        if (!host) return;

        const filters = readFilters();
        syncChrome(filters);

        const seq = ++state.queueSeq;
        host.setAttribute('aria-busy', 'true');
        host.innerHTML = '<div class="sd-approval-state">Loading requests…</div>';
        $('sd-approval-queue-count').textContent = '';

        const params = { action: 'queue' };
        Object.entries(filters).forEach(([k, v]) => { if (v !== '') params[k] = v; });

        try {
            const json = await getJson(params);
            if (seq !== state.queueSeq) return; // a newer request replaced this one
            if (!json.success) throw new Error(json.message || 'Request failed');

            if (!json.data.length) {
                host.innerHTML = hasActiveFilters(filters)
                    ? stateHtml('No requests match these filters', 'Clear a filter or change your search.', 'Clear filters', 'clear-filters')
                    : stateHtml(
                        state.isDirectress ? 'Nothing is waiting for your decision' : 'No requests yet',
                        state.isDirectress ? 'New requests appear here as soon as a department submits them.' : 'Requests from your department appear here.'
                    );
                return;
            }

            host.innerHTML = json.data.map(queueItemHtml).join('');
            const n = json.data.length;
            $('sd-approval-queue-count').textContent = json.has_more
                ? 'Showing the first 100 requests'
                : `${n} ${n === 1 ? 'request' : 'requests'}`;
        } catch (err) {
            if (seq !== state.queueSeq) return;
            console.error('loadQueue error:', err);
            host.innerHTML = stateHtml('Could not load requests', 'Check your connection, then try again.', 'Try again', 'retry-queue');
        } finally {
            if (seq === state.queueSeq) host.setAttribute('aria-busy', 'false');
        }
    }

    function refreshAll() {
        loadQueue();
        loadOverview();
    }

    // ── Request details modal (same layout as the report details modal) ──────
    function historyHtml(history) {
        if (!history || !history.length) {
            return '<p class="sd-approval-muted">No activity has been recorded for this request.</p>';
        }
        return `<ol class="sd-approval-timeline">${history.map((h) => `
            <li class="sd-approval-tl sd-approval-tl--${esc(h.event)}">
                <div class="sd-approval-tl-head"><strong>${esc(h.label)}</strong><time>${esc(fmtDate(h.at, true))}</time></div>
                ${h.by && String(h.by).trim() ? `<div class="sd-approval-muted">${esc(h.by)}</div>` : ''}
                ${h.note ? `<blockquote class="sd-approval-tl-note">${esc(h.note)}</blockquote>` : ''}
            </li>`).join('')}</ol>`;
    }

    function decisionHtml(a) {
        const id = esc(a.approval_id);

        if (state.isDirectress && a.status === 'submitted') {
            return `<div class="sd-approval-review-box">
                <label for="sd-approval-note">Review notes <span class="sd-approval-muted">(optional)</span></label>
                <textarea id="sd-approval-note" class="sd-approval-input" rows="3" maxlength="2000"
                          placeholder="Anything the decision should take into account"></textarea>
                <div class="sd-approval-actions">
                    <button type="button" class="sd-approval-btn sd-approval-btn--primary" data-action="mark-reviewed" data-id="${id}">Mark as reviewed</button>
                </div>
                <p class="sd-approval-muted">Reviewing moves this request to the decision step.</p>
            </div>`;
        }

        if (state.isDirectress && a.status === 'reviewed') {
            return `<div class="sd-approval-review-box">
                <label for="sd-approval-note">Decision note <span class="sd-approval-muted">(required to reject)</span></label>
                <textarea id="sd-approval-note" class="sd-approval-input" rows="3" maxlength="2000"
                          placeholder="Explain the reason for your decision"></textarea>
                <div id="sd-approval-note-error" class="sd-approval-error" role="alert" hidden></div>
                <div class="sd-approval-actions">
                    <button type="button" class="sd-approval-btn sd-approval-btn--primary" data-action="decide" data-decision="approved" data-id="${id}">Approve</button>
                    <button type="button" class="sd-approval-btn sd-approval-btn--danger-outline" data-action="decide" data-decision="rejected" data-id="${id}">Reject</button>
                </div>
            </div>`;
        }

        let message = 'Only the School Directress can review and decide on requests.';
        if (a.status === 'draft') message = 'This is a draft. It has not been submitted for approval.';
        else if (a.status === 'approved' || a.status === 'rejected') message = `This request was ${a.status}. The decision is recorded in the history.`;
        else if (state.isDirectress) message = 'No action is needed on this request.';
        return `<div class="sd-approval-review-box"><p class="sd-approval-muted">${esc(message)}</p></div>`;
    }

    function renderRequest(a) {
        const row = (label, value) => `<div class="sd-approval-detail-row"><span class="sd-approval-detail-label">${label}</span><span>${value}</span></div>`;
        const none = '<span class="sd-approval-muted">None provided.</span>';

        // The document: a row with Full screen / Download, and the file shown inline below.
        const mime = String(a.attachment_mime || '');
        const previewable = mime === 'application/pdf' || mime.startsWith('image/');
        let fileRow = '';
        let preview = '';

        if (a.has_attachment) {
            const size = a.attachment_size ? ` (${esc(fmtSize(a.attachment_size))})` : '';
            fileRow = `<div class="sd-approval-detail-row">
                <span class="sd-approval-detail-label">Request document</span>
                <span class="sd-approval-attachment-info">
                    <span class="sd-approval-attachment-name">${esc(a.attachment_name || 'Request document')}${size}</span>
                    <button type="button" class="sd-approval-btn sd-approval-btn--small" data-action="open-file"
                            data-id="${esc(a.approval_id)}" data-kind="attachment" data-mime="${esc(mime)}">${previewable ? 'Full screen' : 'Open'}</button>
                    <a class="sd-approval-btn sd-approval-btn--small" href="${esc(fileUrl(a.approval_id, 'attachment', true))}">Download</a>
                </span>
            </div>`;
            preview = previewable
                ? `<div class="sd-approval-preview"><iframe class="sd-approval-preview-frame" src="${esc(fileUrl(a.approval_id, 'attachment', false))}" title="Request document preview"></iframe></div>`
                : '<p class="sd-approval-muted sd-approval-preview-note">This file type can\'t be previewed here. Use Open or Download above.</p>';
        } else if (a.has_pdf) {
            fileRow = `<div class="sd-approval-detail-row">
                <span class="sd-approval-detail-label">Generated PDF</span>
                <span class="sd-approval-attachment-info">
                    <button type="button" class="sd-approval-btn sd-approval-btn--small" data-action="open-file"
                            data-id="${esc(a.approval_id)}" data-kind="pdf" data-mime="application/pdf">Full screen</button>
                    <a class="sd-approval-btn sd-approval-btn--small" href="${esc(fileUrl(a.approval_id, 'pdf', true))}">Download</a>
                </span>
            </div>`;
            preview = `<div class="sd-approval-preview"><iframe class="sd-approval-preview-frame" src="${esc(fileUrl(a.approval_id, 'pdf', false))}" title="Generated PDF preview"></iframe></div>`;
        } else {
            fileRow = row('Request document', '<span class="sd-approval-muted">No document attached</span>');
        }

        $('sd-approval-view-body').innerHTML = `
            ${row('Request ID', `#${esc(a.approval_id)}`)}
            ${row('Title', esc(a.title || 'Untitled request'))}
            ${row('Department', esc(a.department_name || 'N/A'))}
            ${row('Submitted By', esc(a.submit_by_name || 'N/A'))}
            ${row('Submitted', esc(a.submitted_on ? fmtDate(a.submitted_on, true) : 'Not submitted yet'))}
            ${row('Status', badge(a.status))}
            ${fileRow}
            ${preview}

            <h4>Description</h4>
            <p class="sd-approval-prose">${esc(a.description) || none}</p>

            <h4>Justification</h4>
            <p class="sd-approval-prose">${esc(a.justification) || none}</p>

            <h4>Decision history and comments</h4>
            ${historyHtml(a.history)}

            <div class="sd-approval-ai">
                <div class="sd-approval-ai-head">
                    <strong>AI summary</strong>
                    <button type="button" class="sd-approval-btn sd-approval-btn--small" id="sd-approval-summarize" data-action="summarize" data-id="${esc(a.approval_id)}">
                        ${a.ai_summary ? 'Regenerate' : 'Generate summary'}
                    </button>
                </div>
                <p id="sd-approval-ai-text" class="sd-approval-prose ${a.ai_summary ? '' : 'sd-approval-muted'}">${a.ai_summary ? esc(a.ai_summary) : 'No summary yet.'}</p>
            </div>

            ${decisionHtml(a)}`;
    }

    async function loadRequest(id) {
        const body = $('sd-approval-view-body');
        const seq = ++state.requestSeq;
        try {
            const json = await getJson({ action: 'get', approval_id: id });
            if (seq !== state.requestSeq) return;
            if (!json.success) {
                body.innerHTML = stateHtml('This request could not be opened', json.message || '', 'Try again', 'retry-request');
                return;
            }
            renderRequest(json.data);
        } catch (err) {
            if (seq !== state.requestSeq) return;
            console.error('loadRequest error:', err);
            body.innerHTML = stateHtml('This request could not be opened', 'Check your connection, then try again.', 'Try again', 'retry-request');
        }
    }

    function openRequest(id, trigger) {
        state.currentId = id;
        state.lastTrigger = trigger || document.activeElement;

        $('sd-approval-view-body').innerHTML = '<div class="sd-approval-state">Loading request…</div>';
        $('sd-approval-view-overlay').classList.add('is-open');
        document.body.classList.add('sd-approval-lock');

        return loadRequest(id);
    }

    function closeRequest() {
        const overlay = $('sd-approval-view-overlay');
        if (!overlay || !overlay.classList.contains('is-open')) return;
        overlay.classList.remove('is-open');
        document.body.classList.remove('sd-approval-lock');
        $('sd-approval-view-body').innerHTML = ''; // also unloads the PDF viewer
        state.currentId = null;
        state.requestSeq++;
        if (state.lastTrigger && document.contains(state.lastTrigger)) state.lastTrigger.focus();
    }

    // Full screen viewer: PDFs and images open here; Word/Excel files download instead.
    function openFile(approvalId, kind, mime) {
        const previewable = mime === 'application/pdf' || String(mime).startsWith('image/');
        if (!previewable) {
            window.location.href = fileUrl(approvalId, kind, true);
            return;
        }
        const overlay = $('sd-approval-file-overlay');
        const frame = $('sd-approval-file-frame');
        if (!overlay || !frame) return;
        frame.src = fileUrl(approvalId, kind, false);
        overlay.classList.add('is-open');
    }

    function closeFile() {
        const overlay = $('sd-approval-file-overlay');
        if (!overlay || !overlay.classList.contains('is-open')) return;
        overlay.classList.remove('is-open');
        const frame = $('sd-approval-file-frame');
        if (frame) frame.src = '';
    }

    // ── Decisions (all consequential actions are confirmed, then saved by the server) ──
    async function markReviewed(id, button) {
        const notes = $('sd-approval-note')?.value ?? '';
        const ok = await confirmAction({
            title: 'Mark as reviewed?',
            message: `Request #${id} moves to the decision step. Your notes are saved with it.`,
            confirmLabel: 'Mark as reviewed',
        });
        if (!ok) return;

        await withBusy([button], async () => {
            try {
                const fd = new FormData();
                fd.append('action', 'review');
                fd.append('approval_id', id);
                fd.append('notes', notes);
                const json = await postForm(fd);
                toast(json.message, json.success ? 'success' : 'error');
                if (json.success) {
                    await loadRequest(id);
                    refreshAll();
                }
            } catch (err) {
                console.error('markReviewed error:', err);
                toast('A network error occurred. Nothing was saved.', 'error');
            }
        });
    }

    async function decide(id, decision, button) {
        const noteEl = $('sd-approval-note');
        const errEl = $('sd-approval-note-error');
        const note = (noteEl?.value || '').trim();
        if (errEl) errEl.hidden = true;

        if (decision === 'rejected' && !note) {
            if (errEl) {
                errEl.textContent = 'Enter a reason before rejecting this request.';
                errEl.hidden = false;
            }
            noteEl?.focus();
            return;
        }

        const approving = decision === 'approved';
        const ok = await confirmAction({
            title: approving ? 'Approve this request?' : 'Reject this request?',
            message: `Request #${id} will be ${approving ? 'approved' : 'rejected'}. The decision cannot be changed from this page.`,
            confirmLabel: approving ? 'Approve request' : 'Reject request',
            danger: !approving,
        });
        if (!ok) return;

        await withBusy([button, ...(button ? button.parentElement.querySelectorAll('button') : [])], async () => {
            try {
                const fd = new FormData();
                fd.append('action', 'decide');
                fd.append('approval_id', id);
                fd.append('decision', decision);
                fd.append('notes', note);
                const json = await postForm(fd);
                toast(json.message, json.success ? 'success' : 'error');
                if (json.success) {
                    await loadRequest(id);
                    refreshAll();
                }
            } catch (err) {
                console.error('decide error:', err);
                toast('A network error occurred. Nothing was saved.', 'error');
            }
        });
    }

    async function summarize(id, button) {
        const text = $('sd-approval-ai-text');
        const original = button ? button.textContent.trim() : '';
        if (button) button.textContent = 'Summarizing…';
        await withBusy([button], async () => {
            try {
                const json = await getJson({ action: 'summarize', approval_id: id });
                if (json.success) {
                    if (text) { text.textContent = json.summary; text.classList.remove('sd-approval-muted'); }
                    if (button) button.textContent = 'Regenerate';
                } else {
                    toast(json.message, 'error');
                    if (button) button.textContent = original;
                }
            } catch (err) {
                console.error('summarize error:', err);
                toast('A network error occurred.', 'error');
                if (button) button.textContent = original;
            }
        });
    }

    // ── Submit for Approval modal ────────────────────────────────────────────
    function showFormError(message) {
        const el = $('sd-approval-form-error');
        if (!el) return;
        el.textContent = message;
        el.hidden = !message;
    }

    function openCreate() {
        $('sd-approval-form')?.reset();
        $('sd-approval-id').value = '';
        showFormError('');
        $('sd-approval-create-overlay').classList.add('is-open');
        $('sd-approval-title')?.focus();
    }

    function closeCreate() {
        $('sd-approval-create-overlay')?.classList.remove('is-open');
        showFormError('');
    }

    function validateFile(file) {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        if (!ALLOWED_EXTENSIONS.includes(ext)) return 'This file type is not allowed. Use PDF, JPG or PNG.';
        if (file.size > MAX_BYTES) return `That file is ${fmtSize(file.size)}. The maximum is 10 MB.`;
        return null;
    }

    async function submitForm(action) {
        const form = $('sd-approval-form');
        const submitBtn = $('sd-approval-submit-btn');
        const draftBtn = $('sd-approval-draft-btn');
        if (!form) return;

        showFormError('');
        const fd = new FormData(form);
        const file = $('sd-approval-attachment')?.files[0];

        if (!String(fd.get('title') || '').trim()) return showFormError('Enter a title for the request.');
        if (!String(fd.get('description') || '').trim()) return showFormError('Describe what is being requested.');
        if (action === 'submit' && !file) return showFormError('Upload the request document (PDF, JPG or PNG) before submitting.');
        if (file) {
            const fileError = validateFile(file);
            if (fileError) return showFormError(fileError);
        }

        fd.set('action', action);
        const original = submitBtn.textContent;
        if (action === 'submit') submitBtn.textContent = 'Submitting…';

        await withBusy([submitBtn, draftBtn], async () => {
            try {
                const json = await postForm(fd);
                if (json.success) {
                    closeCreate();
                    toast(action === 'submit' ? 'Approval request submitted.' : 'Draft saved.', 'success');
                    refreshAll();
                } else {
                    showFormError(json.message || 'The request could not be saved.');
                }
            } catch (err) {
                console.error('submitForm error:', err);
                showFormError('A network error occurred. Please try again.');
            } finally {
                submitBtn.textContent = original;
            }
        });
    }

    // ── Event wiring ─────────────────────────────────────────────────────────
    function onRootClick(e) {
        const target = e.target;

        // Click on a dimmed backdrop closes that layer (the confirm dialog handles its own).
        if (target.classList && target.classList.contains('sd-approval-overlay') && !target.classList.contains('sd-approval-overlay--confirm')) {
            if (target.id === 'sd-approval-view-overlay') closeRequest();
            else if (target.id === 'sd-approval-file-overlay') closeFile();
            else closeCreate();
            return;
        }

        const el = target.closest('[data-action]');
        if (!el || !state.root.contains(el)) return;

        switch (el.dataset.action) {
            case 'open-create': openCreate(); break;
            case 'close-create': closeCreate(); break;
            case 'save-draft': submitForm('save_draft'); break;
            case 'close-view': closeRequest(); break;
            case 'close-file': closeFile(); break;
            case 'open-file': openFile(el.dataset.id, el.dataset.kind, el.dataset.mime); break;
            case 'review': openRequest(el.dataset.id, el); break;
            case 'retry-request': openRequest(state.currentId, state.lastTrigger); break;
            case 'retry-queue': loadQueue(); break;
            case 'retry-overview': loadOverview(); break;
            case 'clear-filters': clearFilters(); break;
            case 'filter-status': {
                const status = el.dataset.status;
                const select = $('sd-approval-status');
                if (!select) break;
                select.value = select.value === status ? defaultStatus() : status;
                loadQueue();
                break;
            }
            case 'mark-reviewed': markReviewed(el.dataset.id, el); break;
            case 'decide': decide(el.dataset.id, el.dataset.decision, el); break;
            case 'summarize': summarize(el.dataset.id, el); break;
        }
    }

    function onRootChange(e) {
        const id = e.target.id;
        if (id === 'sd-approval-department') { loadQueue(); loadOverview(); }
        else if (id === 'sd-approval-status' || id === 'sd-approval-date-from' || id === 'sd-approval-date-to') loadQueue();
        else if (id === 'sd-approval-attachment') {
            const file = e.target.files[0];
            const error = file ? validateFile(file) : null;
            if (error) {
                e.target.value = '';
                showFormError(error);
            } else {
                showFormError('');
            }
        }
    }

    function onRootInput(e) {
        if (e.target.id !== 'sd-approval-search') return;
        clearTimeout(state.searchTimer);
        state.searchTimer = setTimeout(loadQueue, 300);
    }

    function onRootSubmit(e) {
        if (e.target.id !== 'sd-approval-form') return;
        e.preventDefault();
        submitForm('submit');
    }

    function init() {
        const root = document.querySelector('.sd-approval-module');
        if (!root || root.dataset.sdApprovalInit === '1') return;
        root.dataset.sdApprovalInit = '1';

        state.root = root;
        state.isDirectress = root.dataset.isDirectress === '1';
        document.body.classList.remove('sd-approval-lock'); // in case the modal was open when the user navigated away

        bindOnce(root, 'click', onRootClick);
        bindOnce(root, 'change', onRootChange);
        bindOnce(root, 'input', onRootInput);
        bindOnce(root, 'submit', onRootSubmit);

        refreshAll();
    }

    if (!window.sdApprovalEscBound) {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape' || !state.root || !document.contains(state.root)) return;
            if (state.confirmCancel) { state.confirmCancel(); return; }
            if ($('sd-approval-file-overlay')?.classList.contains('is-open')) { closeFile(); return; }
            if ($('sd-approval-view-overlay')?.classList.contains('is-open')) { closeRequest(); return; }
            closeCreate();
        });
        window.sdApprovalEscBound = true;
    }

    // Handles both hard refresh and sidebar navigation.
    window.addEventListener('page:loaded', init);
    document.addEventListener('DOMContentLoaded', init);
    if (document.readyState !== 'loading') init();
})();
// ── INIT ──────────────────────────────────────────────────────────────────────
function initApprovalModule() {
    const moduleRoot = document.querySelector('.approval-module');
    if (!moduleRoot) return;

    // Prevents the same element from getting a listener attached twice when
    // init() runs more than once on unchanged DOM.
    function bindOnce(el, event, handler) {
        if (!el || el.dataset.approvalBound === '1') return;
        el.addEventListener(event, handler);
        el.dataset.approvalBound = '1';
    }

    loadApprovalQueue();

    const approvalForm = document.getElementById('approval-form');

    // --- Create modal wiring ---
    const modalOverlay  = document.getElementById('approval-modal-overlay');
    const openModalBtn  = document.getElementById('approval-open-modal');
    const closeModalBtn = document.getElementById('approval-modal-close');

    const openCreateModal = () => {
        approvalForm?.reset();
        document.getElementById('approval-id').value = '';
        modalOverlay?.classList.add('active');
    };
    const closeCreateModal = () => {
        modalOverlay?.classList.remove('active');
        const formError = document.getElementById('approval-form-error');
        if (formError) formError.style.display = 'none';
    };

    bindOnce(openModalBtn, 'click', openCreateModal);
    bindOnce(closeModalBtn, 'click', closeCreateModal);
    bindOnce(modalOverlay, 'click', (e) => {
        if (e.target === modalOverlay) closeCreateModal();
    });

    // --- Optional attachment: check type/size before the user even submits ---
    const attachmentInput = document.getElementById('approval-attachment');
    bindOnce(attachmentInput, 'change', () => {
        const file  = attachmentInput.files[0];
        const error = file ? validateApprovalAttachment(file) : null;
        if (error) {
            attachmentInput.value = '';
            const formError = document.getElementById('approval-form-error');
            if (formError) {
                formError.textContent   = error;
                formError.style.display = 'block';
            }
        }
    });

    // --- View/PDF modal wiring ---
    const viewOverlay = document.getElementById('approval-view-modal-overlay');
    const viewClose   = document.getElementById('approval-view-modal-close');
    const pdfOverlay  = document.getElementById('approval-pdf-modal-overlay');
    const pdfClose    = document.getElementById('approval-pdf-modal-close');
    const pdfFrame    = document.getElementById('approval-pdf-frame');

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

    if (!window.approvalEscBound) {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeCreateModal();
            closeViewModal();
            closePdfModal();
        });
        window.approvalEscBound = true;
    }

    // --- Save as Draft ---
    const draftBtn = document.getElementById('approval-draft-btn');
    bindOnce(draftBtn, 'click', () => submitApprovalForm('save_draft'));

    // --- Submit ---
    bindOnce(approvalForm, 'submit', (e) => {
        e.preventDefault();
        submitApprovalForm('submit');
    });

    // --- Table row "View" clicks (delegated, survives re-renders) ---
    if (!window.approvalViewDelegationBound) {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.approval-view-btn');
            if (btn) viewApproval(btn.dataset.approvalId);
        });
        window.approvalViewDelegationBound = true;
    }

    // --- Department filter ---
    const deptFilter = document.getElementById('department-filter');
    bindOnce(deptFilter, 'change', () => loadApprovalQueue());

    // --- Status filter ---
    const statusFilter = document.getElementById('status-filter');
    bindOnce(statusFilter, 'change', () => loadApprovalQueue());
}

// Handles both hard refresh and sidebar navigation
window.addEventListener('page:loaded', initApprovalModule);
document.addEventListener('DOMContentLoaded', initApprovalModule);

// ── ATTACHMENT / FILE HELPERS ─────────────────────────────────────────────────
function validateApprovalAttachment(file) {
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!APPROVAL_ALLOWED_EXTENSIONS.includes(ext)) {
        return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
    }
    if (file.size > APPROVAL_MAX_ATTACHMENT_BYTES) {
        return `That file is ${approvalFormatFileSize(file.size)}. The maximum is 10 MB.`;
    }
    return null;
}

function approvalFormatFileSize(bytes) {
    const n = Number(bytes) || 0;
    if (n >= 1024 * 1024) return (n / (1024 * 1024)).toFixed(1) + ' MB';
    if (n >= 1024)        return Math.round(n / 1024) + ' KB';
    return n + ' B';
}

// Files are never linked by their disk path. They are always requested through the
// controller, which checks the session/role and streams the bytes.
function approvalFileUrl(approvalId, kind, download = false) {
    const params = new URLSearchParams({ action: 'file', approval_id: approvalId, kind });
    if (download) params.set('download', '1');
    return `${ApprovalController}?${params.toString()}`;
}

// ── SUBMIT / DRAFT ────────────────────────────────────────────────────────────
async function submitApprovalForm(action) {
    const approvalForm = document.getElementById('approval-form');
    const formError     = document.getElementById('approval-form-error');
    const submitBtn     = document.getElementById('approval-submit-btn');
    const draftBtn      = document.getElementById('approval-draft-btn');

    formError.style.display = 'none';
    submitBtn.disabled = true;
    draftBtn.disabled  = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = action === 'submit' ? 'Submitting…' : submitBtn.textContent;

    // FormData includes the optional "attachment" file input automatically (multipart/form-data).
    // Do NOT set a Content-Type header manually — the browser adds the multipart boundary.
    const formData = new FormData(approvalForm);
    formData.set('action', action);

    try {
        const res  = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (!json.success && json.message?.toLowerCase().includes('session expired')) {
            setTimeout(() => window.location.href = '/sms/index.php', 1500);
            return;
        }

        if (json.success) {
            document.getElementById('approval-modal-overlay')?.classList.remove('active');
            if (json.debug) console.error('Approval debug:', json.debug);
            showToast(action === 'submit' ? 'Approval request submitted successfully!' : 'Draft saved.', 'success');
            if (json.warning) showToast(json.warning, 'error');
            loadApprovalQueue();
        } else {
            if (json.debug) console.error('Approval debug:', json.debug);
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

// ── LOAD QUEUE ────────────────────────────────────────────────────────────────
async function loadApprovalQueue() {
    const deptFilter    = document.getElementById('department-filter');
    const statusFilter  = document.getElementById('status-filter');
    const department_id = deptFilter && deptFilter.value !== '' ? deptFilter.value : '';
    const status         = statusFilter && statusFilter.value !== '' ? statusFilter.value : '';
    const tbody          = document.querySelector('.ads-table tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="muted">Loading…</td></tr>`;

    const params = new URLSearchParams({ action: 'list' });
    if (department_id !== '') params.set('department_id', department_id);
    if (status !== '') params.set('status', status);

    try {
        const res  = await fetch(`${ApprovalController}?${params.toString()}`);
        const json = await res.json();

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load approvals.</td></tr>`;
            return;
        }

        if (!json.data?.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="muted">No approvals found.</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(row => `
            <tr data-approval-id="${esc(row.approval_id)}">
                <td>${esc(row.approval_id)}</td>
                <td>${esc(row.title)}</td>
                <td>${esc(row.submit_by)}</td>
                <td>${esc(row.department_name)}</td>
                <td><span class="badge badge-${esc(row.status)}">${esc(APPROVAL_STATUS_LABELS[row.status] ?? row.status)}</span></td>
                <td>${esc(row.submitted_on ?? 'N/A')}</td>
                <td class="actions-cell">
                    <button type="button" class="attachment-link approval-view-btn" data-approval-id="${esc(row.approval_id)}">View</button>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        console.error('loadApprovalQueue error:', err);
        tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load approvals.</td></tr>`;
    }
}

// ── VIEW / REVIEW / DECISION ─────────────────────────────────────────────────
async function viewApproval(approvalId) {
    const overlay = document.getElementById('approval-view-modal-overlay');
    const body    = document.getElementById('approval-view-modal-body');
    if (!overlay || !body) return;

    body.innerHTML = '<p class="muted">Loading…</p>';
    overlay.classList.add('active');

    try {
        const res  = await fetch(`${ApprovalController}?action=get&approval_id=${encodeURIComponent(approvalId)}`);
        const json = await res.json();

        if (!json.success) {
            body.innerHTML = `<p class="muted">${esc(json.message)}</p>`;
            return;
        }

        renderApprovalDetail(json.data);
    } catch (err) {
        console.error('viewApproval error:', err);
        body.innerHTML = '<p class="muted">Failed to load approval request.</p>';
    }
}

function renderApprovalDetail(approval) {
    const moduleRoot    = document.querySelector('.approval-module');
    const isDirectress  = moduleRoot?.dataset.isDirectress === '1';
    const body          = document.getElementById('approval-view-modal-body');
    const statusLabel   = APPROVAL_STATUS_LABELS[approval.status] ?? approval.status;

    let reviewSection = '';
    if (isDirectress && approval.status === 'submitted') {
        reviewSection = `
            <div class="approval-review-box">
                <label for="approval-review-notes">Review Notes (optional)</label>
                <textarea id="approval-review-notes" rows="2" placeholder="Any notes for this review"></textarea>
                <div class="approval-actions">
                    <button type="button" class="approval-submit-btn" id="approval-mark-reviewed" data-approval-id="${esc(approval.approval_id)}">Mark as Reviewed</button>
                </div>
            </div>`;
    } else if (isDirectress && approval.status === 'reviewed') {
        reviewSection = `
            <div class="approval-review-box">
                <label for="approval-decision-notes">Decision Notes (optional)</label>
                <textarea id="approval-decision-notes" rows="2" placeholder="Any notes for this decision"></textarea>
                <div class="approval-actions">
                    <button type="button" class="btn-approve approval-decide-btn" data-decision="approved" data-approval-id="${esc(approval.approval_id)}">Approve</button>
                    <button type="button" class="btn-reject approval-decide-btn" data-decision="rejected" data-approval-id="${esc(approval.approval_id)}">Reject</button>
                </div>
            </div>`;
    }

    // The server no longer sends file paths — only has_pdf / has_attachment flags.
    const pdfButton = approval.has_pdf
        ? `<button type="button" class="attachment-link" id="approval-open-pdf" data-approval-id="${esc(approval.approval_id)}">View PDF</button>`
        : `<span class="muted">PDF not yet generated</span>`;

    const attachmentLine = approval.has_attachment
        ? `<span class="approval-attachment-info">
                <span class="approval-attachment-name">${esc(approval.attachment_name ?? 'Attachment')}${approval.attachment_size ? ` (${esc(approvalFormatFileSize(approval.attachment_size))})` : ''}</span>
                <button type="button" class="attachment-link" id="approval-open-attachment"
                        data-approval-id="${esc(approval.approval_id)}" data-mime="${esc(approval.attachment_mime ?? '')}">View</button>
                <a class="attachment-link approval-link-btn" href="${esc(approvalFileUrl(approval.approval_id, 'attachment', true))}">Download</a>
           </span>`
        : `<span class="muted">No attachment</span>`;

    const aiSummaryBox = `
        <div class="approval-ai-box">
            <div class="approval-ai-box-header">
                <strong>AI Summary</strong>
                <button type="button" class="attachment-link" id="approval-summarize-btn" data-approval-id="${esc(approval.approval_id)}">
                    ${approval.ai_summary ? 'Regenerate' : 'Generate Summary'}
                </button>
            </div>
            <p id="approval-ai-summary-text" class="muted">${approval.ai_summary ? esc(approval.ai_summary) : 'No AI summary yet.'}</p>
        </div>`;

    body.innerHTML = `
        <div class="approval-detail-row"><span class="approval-detail-label">Title</span><span>${esc(approval.title)}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Department</span><span>${esc(approval.department_name ?? 'N/A')}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Submitted By</span><span>${esc(approval.submit_by_name ?? 'N/A')}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Status</span><span class="badge badge-${esc(approval.status)}">${esc(statusLabel)}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Attachment</span>${attachmentLine}</div>

        <h4>Description</h4>
        <p>${esc(approval.description)}</p>

        <h4>Justification</h4>
        <p>${esc(approval.justification) || '<span class="muted">None provided.</span>'}</p>

        ${aiSummaryBox}

        <div class="approval-actions">${pdfButton}</div>

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
    const frame   = document.getElementById('approval-pdf-frame');
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
        formData.append('notes', notes);

        const res  = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast('Marked as reviewed.', 'success');
            viewApproval(approvalId);
            loadApprovalQueue();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('reviewApproval error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function decideApproval(approvalId, decision, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'decide');
        formData.append('approval_id', approvalId);
        formData.append('decision', decision);
        formData.append('notes', notes);

        const res  = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast(`Request ${decision}.`, 'success');
            viewApproval(approvalId);
            loadApprovalQueue();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('decideApproval error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function summarizeApproval(approvalId) {
    const btn  = document.getElementById('approval-summarize-btn');
    const text = document.getElementById('approval-ai-summary-text');
    if (btn) { btn.disabled = true; btn.textContent = 'Summarizing…'; }

    try {
        const res  = await fetch(`${ApprovalController}?action=summarize&approval_id=${encodeURIComponent(approvalId)}`, { method: 'POST' });
        const json = await res.json();

        if (json.success) {
            if (text) text.textContent = json.summary;
            if (btn) btn.textContent = 'Regenerate';
        } else {
            showToast(json.message, 'error');
            if (btn) btn.textContent = 'Generate Summary';
        }
    } catch (err) {
        console.error('summarizeApproval error:', err);
        showToast('A network error occurred.', 'error');
        if (btn) btn.textContent = 'Generate Summary';
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── TOAST ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    const bg = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';

    // Stack above any toast that is still visible (e.g. "submitted" + a PDF warning).
    const bottom = 24 + document.querySelectorAll('.approval-toast').length * 64;

    const toast = document.createElement('div');
    toast.className = 'approval-toast';
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