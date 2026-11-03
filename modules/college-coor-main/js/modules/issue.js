/* School Directress — Concerns & Issue Tracking (case workspace)
   Everything lives inside this IIFE so nothing leaks into other modules' globals. */
(function () {
    'use strict';

    const ENDPOINT = 'controllers/IssueController.php';

    const STATUS_LABELS = {
        draft: 'Draft',
        submitted: 'Submitted',
        reviewed: 'Reviewed',
        resolved: 'Resolved',
        dismissed: 'Dismissed',
    };

    // Keep in sync with FileStorage.php (the server re-checks everything).
    const MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
    const MIN_CLOSURE_CHARS = 10; // matches the server rule in IssueController 'decide'

    const state = { listSeq: 0, overviewSeq: 0, currentId: null, lastFocus: null, searchTimer: null, closing: null };

    // ── Helpers ───────────────────────────────────────────────────────────────
    const byId = (id) => document.getElementById(id);

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, (ch) => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]
        ));
    }

    function parseDate(s) {
        if (!s) return null;
        const d = new Date(String(s).replace(' ', 'T'));
        return isNaN(d) ? null : d;
    }

    function fmtDate(s, withTime) {
        const d = parseDate(s);
        if (!d) return '—';
        const day = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        return withTime
            ? day + ', ' + d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
            : day;
    }

    function waitingText(days) {
        if (days === null || days === undefined) return '';
        if (days <= 0) return 'Submitted today';
        return 'Waiting ' + days + (days === 1 ? ' day' : ' days');
    }

    function fileSize(bytes) {
        const n = Number(bytes) || 0;
        if (n >= 1048576) return (n / 1048576).toFixed(1) + ' MB';
        if (n >= 1024) return Math.round(n / 1024) + ' KB';
        return n + ' B';
    }

    function statusChip(status) {
        return `<span class="sd-issue-status sd-issue-status-${esc(status)}">${esc(STATUS_LABELS[status] ?? status)}</span>`;
    }

    // Files are never linked by disk path; the controller checks access and streams the bytes.
    function fileUrl(issueId, download) {
        const p = new URLSearchParams({ action: 'file', issue_id: issueId });
        if (download) p.set('download', '1');
        return `${ENDPOINT}?${p.toString()}`;
    }

    function toast(message, type) {
        let host = byId('sd-issue-toast-host');
        if (!host) {
            host = document.createElement('div');
            host.id = 'sd-issue-toast-host';
            host.className = 'sd-issue-toast-host';
            host.setAttribute('role', 'status');
            host.setAttribute('aria-live', 'polite');
            document.body.appendChild(host);
        }
        const el = document.createElement('div');
        el.className = 'sd-issue-toast sd-issue-toast-' + (type || 'info');
        el.textContent = message;
        host.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    async function api(url, options) {
        let json;
        try {
            const res = await fetch(url, options);
            json = await res.json();
        } catch (err) {
            console.error('Concerns request failed:', err);
            return { success: false, message: 'A network error occurred. Please try again.' };
        }
        if (!json.success && /session expired/i.test(json.message || '')) {
            toast('Your session has expired. Redirecting to login…', 'error');
            setTimeout(() => { window.location.href = '/sms/index.php'; }, 1500);
        }
        return json;
    }

    const getJson = (params) => api(`${ENDPOINT}?${new URLSearchParams(params).toString()}`);

    function postJson(action, fields) {
        const fd = new FormData();
        fd.set('action', action);
        Object.entries(fields).forEach(([k, v]) => fd.set(k, v));
        return api(ENDPOINT, { method: 'POST', body: fd });
    }

    function filters() {
        return {
            department_id: byId('sd-issue-dept')?.value || '',
            status: byId('sd-issue-status')?.value || '',
            search: byId('sd-issue-search')?.value.trim() || '',
        };
    }

    // ── Overview: summary cards, workflow counts, needs-attention ─────────────
    async function loadOverview() {
        const seq = ++state.overviewSeq;
        const dept = byId('sd-issue-dept')?.value || '';
        const params = { action: 'overview' };
        if (dept) params.department_id = dept;

        const json = await getJson(params);
        if (seq !== state.overviewSeq) return; // a newer request replaced this one

        if (!json.success) {
            const list = byId('sd-issue-attn-list');
            if (list) {
                list.innerHTML = `<li class="sd-issue-state sd-issue-state-error">${esc(json.message || 'Could not load the overview.')}
                    <button type="button" class="sd-issue-btn sd-issue-btn-sm sd-issue-retry">Retry</button></li>`;
            }
            return;
        }
        renderSummary(json.data.summary);
        renderAttention(json.data.attention, json.data.summary);
    }

    function renderSummary(s) {
        const set = (id, v) => { const el = byId(id); if (el) el.textContent = String(v); };
        set('sd-issue-count-total', s.total);
        set('sd-issue-count-submitted', s.submitted);
        set('sd-issue-count-reviewed', s.reviewed);
        set('sd-issue-count-resolved', s.resolved);
        set('sd-issue-note-total', s.dismissed > 0 ? `Includes ${s.dismissed} dismissed` : 'Submitted concerns');
        document.querySelectorAll('.sd-issue-stage-count').forEach((el) => {
            el.textContent = String(s[el.dataset.count] ?? 0);
        });
    }

    function renderAttention(items, summary) {
        const list = byId('sd-issue-attn-list');
        const foot = byId('sd-issue-attn-foot');
        if (!list) return;
        const unresolved = (summary.submitted || 0) + (summary.reviewed || 0);

        if (!items.length) {
            list.innerHTML = '<li class="sd-issue-state">Nothing is waiting. Every submitted concern has been resolved or dismissed.</li>';
            if (foot) foot.textContent = '';
            return;
        }

        list.innerHTML = items.map((r) => `
            <li>
                <button type="button" class="sd-issue-attn-item sd-issue-open sd-issue-edge-${esc(r.status)}" data-issue-id="${esc(r.issue_id)}">
                    <span class="sd-issue-attn-main">
                        <span class="sd-issue-attn-title">${esc(r.title)}</span>
                        <span class="sd-issue-attn-meta">#${esc(r.issue_id)} &middot; ${esc(r.department_name ?? 'N/A')} &middot; ${esc(r.submitted_by ?? 'N/A')}</span>
                    </span>
                    <span class="sd-issue-attn-side">
                        ${statusChip(r.status)}
                        <span class="sd-issue-waiting">${esc(waitingText(r.days_waiting))}</span>
                    </span>
                </button>
            </li>`).join('');

        if (foot) {
            foot.textContent = unresolved > items.length
                ? `Showing ${items.length} of ${unresolved} unresolved. Concerns have no stored priority, so they are ordered by waiting time.`
                : 'Concerns have no stored priority, so they are ordered by waiting time.';
        }
    }

    // ── Issue list ────────────────────────────────────────────────────────────
    function hasFilters() {
        const f = filters();
        return !!(f.department_id || f.status || f.search);
    }

    function syncStageHighlight() {
        const active = byId('sd-issue-status')?.value || '';
        document.querySelectorAll('.sd-issue-stage').forEach((btn) => {
            const on = btn.dataset.status === active;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    async function loadList() {
        const tbody = byId('sd-issue-tbody');
        if (!tbody) return;
        const seq = ++state.listSeq;
        const f = filters();
        syncStageHighlight();

        tbody.innerHTML = '<tr><td colspan="8" class="sd-issue-state">Loading…</td></tr>';

        const params = { action: 'list' };
        if (f.department_id) params.department_id = f.department_id;
        if (f.status) params.status = f.status;
        if (f.search) params.search = f.search;

        const json = await getJson(params);
        if (seq !== state.listSeq) return;

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="8" class="sd-issue-state sd-issue-state-error">${esc(json.message || 'Failed to load concerns.')}
                <button type="button" class="sd-issue-btn sd-issue-btn-sm sd-issue-retry">Retry</button></td></tr>`;
            return;
        }

        if (!json.data.length) {
            tbody.innerHTML = hasFilters()
                ? `<tr><td colspan="8" class="sd-issue-state">No concerns match these filters.
                     <button type="button" class="sd-issue-btn sd-issue-btn-sm sd-issue-clear">Clear filters</button></td></tr>`
                : '<tr><td colspan="8" class="sd-issue-state">No concerns have been logged yet.</td></tr>';
            return;
        }

        tbody.innerHTML = json.data.map(rowHtml).join('');
    }

    function rowHtml(r) {
        const id = esc(r.issue_id);
        const waiting = r.days_waiting !== null && r.days_waiting !== undefined
            ? `<span class="sd-issue-sub">${esc(waitingText(r.days_waiting))}</span>` : '';
        return `
            <tr class="sd-issue-row sd-issue-edge-${esc(r.status)}">
                <td data-label="ID"><span class="sd-issue-id">#${id}</span></td>
                <td data-label="Concern">
                    <button type="button" class="sd-issue-link sd-issue-open" data-issue-id="${id}">${esc(r.title)}</button>
                    ${r.has_attachment ? '<span class="sd-issue-tag">Attachment</span>' : ''}
                </td>
                <td data-label="Department">${esc(r.department_name ?? 'N/A')}</td>
                <td data-label="Submitted By">${esc(r.submitted_by ?? 'N/A')}</td>
                <td data-label="Reported">${esc(fmtDate(r.submitted_on || r.created_at))}</td>
                <td data-label="Status">${statusChip(r.status)}${waiting}</td>
                <td data-label="Last Updated">${esc(fmtDate(r.updated_at || r.submitted_on || r.created_at))}</td>
                <td data-label="Actions"><button type="button" class="sd-issue-btn sd-issue-btn-sm sd-issue-open" data-issue-id="${id}">View</button></td>
            </tr>`;
    }

    function loadAll() {
        loadOverview();
        loadList();
    }

    // ── Concern details modal (same layout as the report details modal) ───────
    function closeCase() {
        const overlay = byId('sd-issue-view-overlay');
        if (!overlay || !overlay.classList.contains('active')) return;
        overlay.classList.remove('active');
        const body = byId('sd-issue-view-body');
        if (body) body.innerHTML = ''; // also unloads the inline file preview
        state.currentId = null;
        state.closing = null;
        if (state.lastFocus && document.contains(state.lastFocus)) state.lastFocus.focus();
    }

    async function openCase(id) {
        const body = byId('sd-issue-view-body');
        const overlay = byId('sd-issue-view-overlay');
        if (!body || !overlay) return;

        state.currentId = id;
        if (!overlay.classList.contains('active')) state.lastFocus = document.activeElement;

        body.innerHTML = '<p class="sd-issue-state">Loading…</p>';
        overlay.classList.add('active');

        const json = await getJson({ action: 'get', issue_id: id });
        if (state.currentId !== id) return;

        if (!json.success) {
            body.innerHTML = `<p class="sd-issue-state sd-issue-state-error">${esc(json.message || 'Failed to load this concern.')}</p>`;
            return;
        }
        renderCase(json.data);
    }

    function stepsHtml(c) {
        if (c.status === 'draft') {
            return '<div class="sd-issue-notice">This concern is a draft and has not been submitted yet.</div>';
        }
        const closed = c.status === 'resolved' || c.status === 'dismissed';
        const reviewedDone = !!c.reviewed_at || c.status === 'reviewed' || closed;
        const steps = [
            { label: 'Submitted', at: c.submitted_on, done: true },
            { label: 'Reviewed', at: c.reviewed_at, done: reviewedDone },
            { label: c.status === 'dismissed' ? 'Dismissed' : 'Resolved', at: c.resolved_at, done: closed },
        ];
        let current = 0;
        steps.forEach((s, i) => { if (s.done) current = i; });

        return '<ol class="sd-issue-steps">' + steps.map((s, i) => `
            <li class="sd-issue-step${s.done ? ' is-done' : ''}${i === current ? ' is-current' : ''}${c.status === 'dismissed' && i === 2 ? ' is-dismissed' : ''}">
                <span class="sd-issue-step-dot"></span>
                <span class="sd-issue-step-label">${esc(s.label)}</span>
                <span class="sd-issue-step-date">${s.done ? esc(fmtDate(s.at)) : 'Pending'}</span>
            </li>`).join('') + '</ol>';
    }

    // Built only from fields that exist on sd_issues (submitted/reviewed/resolved milestones).
    // It is a record of status milestones, not a full follow-up history.
    function recordHtml(c) {
        const items = [];
        if (c.status !== 'draft') {
            items.push({ title: 'Concern reported', at: c.submitted_on, who: c.submitted_by_name, note: '' });
        }
        if (c.reviewed_at) {
            items.push({ title: 'Marked as reviewed', at: c.reviewed_at, who: c.reviewed_by_name, note: c.review_notes || '' });
        }
        if (c.resolved_at) {
            items.push({ title: c.status === 'dismissed' ? 'Concern dismissed' : 'Concern resolved', at: c.resolved_at, who: c.resolved_by_name, note: '' });
        }
        if (!items.length) return '';

        return `
            <section class="sd-issue-section">
                <h4>Case Record</h4>
                <ul class="sd-issue-record">
                    ${items.map((it) => `
                        <li>
                            <div class="sd-issue-record-top">
                                <strong>${esc(it.title)}</strong>
                                <span class="sd-issue-sub">${esc(fmtDate(it.at, true))}${it.who ? ' &middot; ' + esc(it.who) : ''}</span>
                            </div>
                            ${it.note ? `<p class="sd-issue-text">${esc(it.note)}</p>` : ''}
                        </li>`).join('')}
                </ul>
            </section>`;
    }

    function actionsHtml(c) {
        if (c.status === 'draft' && c.is_mine) {
            return `<section class="sd-issue-action-box">
                <h4>Draft</h4>
                <p class="sd-issue-sub">Only you can see this draft until you submit it.</p>
                <div class="sd-issue-btn-row"><button type="button" class="sd-issue-btn sd-issue-btn-primary" id="sd-issue-edit-draft">Continue Editing</button></div>
            </section>`;
        }
        if (c.can_review) {
            return `<section class="sd-issue-action-box">
                <h4>Review this concern</h4>
                <label for="sd-issue-review-notes">Review notes <span class="sd-issue-optional">(optional)</span></label>
                <textarea class="sd-issue-input" id="sd-issue-review-notes" rows="3" maxlength="5000" placeholder="Anything worth recording about this review"></textarea>
                <div class="sd-issue-btn-row"><button type="button" class="sd-issue-btn sd-issue-btn-primary" id="sd-issue-mark-reviewed">Mark as Reviewed</button></div>
            </section>`;
        }
        if (c.can_resolve) {
            return `<section class="sd-issue-action-box" id="sd-issue-closure">
                <h4>Close this case</h4>
                <p class="sd-issue-sub">Record the outcome. A written summary is required.</p>
                <div class="sd-issue-btn-row" id="sd-issue-closure-choices">
                    <button type="button" class="sd-issue-btn sd-issue-btn-success" id="sd-issue-start-resolve">Record Resolution</button>
                    <button type="button" class="sd-issue-btn" id="sd-issue-start-dismiss">Dismiss</button>
                </div>
                <div id="sd-issue-closure-form" hidden>
                    <label for="sd-issue-closure-text" id="sd-issue-closure-label">Resolution summary</label>
                    <textarea class="sd-issue-input" id="sd-issue-closure-text" rows="4" maxlength="5000"></textarea>
                    <small class="sd-issue-hint" id="sd-issue-closure-hint"></small>
                    <div class="sd-issue-warning">Closing a case saves this summary and ends the workflow. There is no reopen step, so check it before you confirm.</div>
                    <div class="sd-issue-form-error" id="sd-issue-closure-error" role="alert" hidden></div>
                    <div class="sd-issue-btn-row">
                        <button type="button" class="sd-issue-btn sd-issue-btn-primary" id="sd-issue-confirm-close" disabled></button>
                        <button type="button" class="sd-issue-btn" id="sd-issue-cancel-close">Cancel</button>
                    </div>
                </div>
            </section>`;
        }
        return '';
    }

    function renderCase(c) {
        const body = byId('sd-issue-view-body');
        const row = (label, value) => `<div class="sd-issue-detail-row"><span class="sd-issue-detail-label">${label}</span><span>${value}</span></div>`;

        // Attachment: a row with Full screen / Download, and the file shown inline below it.
        const mime = String(c.attachment_mime || '');
        const previewable = mime === 'application/pdf' || mime.startsWith('image/');
        let fileRow;
        let preview = '';

        if (c.has_attachment) {
            const size = c.attachment_size ? ` (${esc(fileSize(c.attachment_size))})` : '';
            fileRow = `<div class="sd-issue-detail-row">
                <span class="sd-issue-detail-label">Attachment</span>
                <span class="sd-issue-attachment">
                    <span class="sd-issue-attachment-name">${esc(c.attachment_name || 'Attachment')}${size}</span>
                    <button type="button" class="sd-issue-btn sd-issue-btn-sm" id="sd-issue-view-file"
                            data-issue-id="${esc(c.issue_id)}" data-mime="${esc(mime)}">${previewable ? 'Full screen' : 'Open'}</button>
                    <a class="sd-issue-btn sd-issue-btn-sm" href="${esc(fileUrl(c.issue_id, true))}">Download</a>
                </span>
            </div>`;
            preview = previewable
                ? `<div class="sd-issue-preview"><iframe class="sd-issue-preview-frame" src="${esc(fileUrl(c.issue_id, false))}" title="Attachment preview"></iframe></div>`
                : '<p class="sd-issue-sub sd-issue-preview-note">This file type can\'t be previewed here. Use Open or Download above.</p>';
        } else {
            fileRow = row('Attachment', '<span class="sd-issue-sub">No attachment</span>');
        }

        const closed = c.status === 'resolved' || c.status === 'dismissed';
        const resolution = closed && c.resolution_notes
            ? `<section class="sd-issue-section sd-issue-resolution sd-issue-resolution-${esc(c.status)}">
                   <h4>${c.status === 'dismissed' ? 'Dismissal Summary' : 'Resolution Summary'}</h4>
                   <p class="sd-issue-text">${esc(c.resolution_notes)}</p>
               </section>` : '';

        const aiBox = c.status === 'draft' ? '' : `
            <section class="sd-issue-ai">
                <div class="sd-issue-ai-head">
                    <strong>AI Summary</strong>
                    <button type="button" class="sd-issue-btn sd-issue-btn-sm" id="sd-issue-summarize" data-issue-id="${esc(c.issue_id)}">${c.ai_summary ? 'Regenerate' : 'Generate Summary'}</button>
                </div>
                <p class="sd-issue-text sd-issue-sub" id="sd-issue-ai-text">${c.ai_summary ? esc(c.ai_summary) : 'No AI summary yet.'}</p>
            </section>`;

        body.innerHTML = `
            ${row('Concern ID', '#' + esc(c.issue_id))}
            ${row('Title', esc(c.title || 'Concern'))}
            ${row('Department', esc(c.department_name ?? 'N/A'))}
            ${row('Submitted By', esc(c.submitted_by_name ?? 'N/A'))}
            ${row('Date Reported', esc(fmtDate(c.submitted_on || c.created_at, true)))}
            ${row('Last Updated', esc(fmtDate(c.updated_at || c.submitted_on || c.created_at, true)))}
            ${row('Status', statusChip(c.status))}
            ${fileRow}
            ${preview}

            <section class="sd-issue-section">
                <h4>Progress</h4>
                ${stepsHtml(c)}
            </section>

            <section class="sd-issue-section">
                <h4>Concern</h4>
                <p class="sd-issue-text">${esc(c.details)}</p>
            </section>

            <section class="sd-issue-section">
                <h4>Desired Resolution</h4>
                ${c.desired_resolution ? `<p class="sd-issue-text">${esc(c.desired_resolution)}</p>` : '<p class="sd-issue-sub">None provided.</p>'}
            </section>

            ${resolution}
            ${recordHtml(c)}
            ${aiBox}
            ${actionsHtml(c)}
        `;

        bindCaseActions(c);
        const modal = byId('sd-issue-view-modal');
        if (modal) modal.scrollTop = 0;
    }

    function bindCaseActions(c) {
        byId('sd-issue-view-file')?.addEventListener('click', (e) => {
            openAttachment(e.currentTarget.dataset.issueId, e.currentTarget.dataset.mime);
        });
        byId('sd-issue-summarize')?.addEventListener('click', (e) => summarize(e.currentTarget.dataset.issueId));
        byId('sd-issue-edit-draft')?.addEventListener('click', () => { closeCase(); openCreateModal(c); });
        byId('sd-issue-mark-reviewed')?.addEventListener('click', (e) => markReviewed(c.issue_id, e.currentTarget));

        byId('sd-issue-start-resolve')?.addEventListener('click', () => startClosure('resolved'));
        byId('sd-issue-start-dismiss')?.addEventListener('click', () => startClosure('dismissed'));
        byId('sd-issue-cancel-close')?.addEventListener('click', cancelClosure);
        byId('sd-issue-closure-text')?.addEventListener('input', updateClosureState);
        byId('sd-issue-confirm-close')?.addEventListener('click', (e) => confirmClosure(c.issue_id, e.currentTarget));
    }

    // ── Review / resolution ───────────────────────────────────────────────────
    async function markReviewed(issueId, btn) {
        const notes = byId('sd-issue-review-notes')?.value ?? '';
        btn.disabled = true;
        btn.textContent = 'Saving…';
        const json = await postJson('review', { issue_id: issueId, notes });
        if (json.success) {
            toast('Marked as reviewed.', 'success');
            loadAll();
            openCase(issueId);
        } else {
            toast(json.message || 'Unable to review this concern.', 'error');
            btn.disabled = false;
            btn.textContent = 'Mark as Reviewed';
        }
    }

    function startClosure(decision) {
        state.closing = decision;
        const isResolve = decision === 'resolved';
        byId('sd-issue-closure-choices').hidden = true;
        byId('sd-issue-closure-form').hidden = false;
        byId('sd-issue-closure-label').textContent = isResolve ? 'Resolution summary (required)' : 'Reason for dismissing (required)';
        byId('sd-issue-confirm-close').textContent = isResolve ? 'Confirm Resolution' : 'Confirm Dismissal';
        byId('sd-issue-closure-text').placeholder = isResolve
            ? 'What was done to resolve this concern?'
            : 'Why is this concern being dismissed?';
        updateClosureState();
        byId('sd-issue-closure-text').focus();
    }

    function cancelClosure() {
        state.closing = null;
        byId('sd-issue-closure-form').hidden = true;
        byId('sd-issue-closure-choices').hidden = false;
        byId('sd-issue-closure-text').value = '';
        byId('sd-issue-closure-error').hidden = true;
    }

    function updateClosureState() {
        const len = byId('sd-issue-closure-text').value.trim().length;
        byId('sd-issue-confirm-close').disabled = len < MIN_CLOSURE_CHARS;
        byId('sd-issue-closure-hint').textContent = len < MIN_CLOSURE_CHARS
            ? `At least ${MIN_CLOSURE_CHARS} characters (${len} so far).`
            : 'Ready to confirm.';
    }

    async function confirmClosure(issueId, btn) {
        const decision = state.closing;
        const notes = byId('sd-issue-closure-text').value.trim();
        const err = byId('sd-issue-closure-error');
        if (!decision || notes.length < MIN_CLOSURE_CHARS) return;

        err.hidden = true;
        btn.disabled = true;
        const label = btn.textContent;
        btn.textContent = 'Saving…';

        const json = await postJson('decide', { issue_id: issueId, decision, notes });
        if (json.success) {
            toast(decision === 'resolved' ? 'Concern resolved.' : 'Concern dismissed.', 'success');
            loadAll();
            openCase(issueId);
        } else {
            err.textContent = json.message || 'Unable to close this concern.';
            err.hidden = false;
            btn.disabled = false;
            btn.textContent = label;
        }
    }

    async function summarize(issueId) {
        const btn = byId('sd-issue-summarize');
        const text = byId('sd-issue-ai-text');
        const original = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = 'Summarizing…'; }

        const json = await postJson('summarize', { issue_id: issueId });
        if (json.success) {
            if (text) text.textContent = json.summary;
            if (btn) btn.textContent = 'Regenerate';
        } else {
            toast(json.message || 'AI summarization failed.', 'error');
            if (btn) btn.textContent = original;
        }
        if (btn) btn.disabled = false;
    }

    // ── Attachment viewer (Full screen) ───────────────────────────────────────
    function openAttachment(issueId, mime) {
        const previewable = mime === 'application/pdf' || String(mime).startsWith('image/');
        if (!previewable) {
            window.location.href = fileUrl(issueId, true); // Word/Excel can't render in the browser
            return;
        }
        byId('sd-issue-viewer-frame').src = fileUrl(issueId, false);
        byId('sd-issue-viewer-overlay').classList.add('active');
    }

    function closeViewer() {
        byId('sd-issue-viewer-overlay')?.classList.remove('active');
        const frame = byId('sd-issue-viewer-frame');
        if (frame) frame.src = '';
    }

    // ── Create / edit draft ───────────────────────────────────────────────────
    function showFormError(message) {
        const el = byId('sd-issue-form-error');
        if (!el) return;
        el.textContent = message || '';
        el.hidden = !message;
    }

    function openCreateModal(draft) {
        const form = byId('sd-issue-form');
        if (!form) return;
        form.reset();
        showFormError('');
        byId('sd-issue-id-field').value = draft ? draft.issue_id : '';
        byId('sd-issue-modal-title').textContent = draft ? 'Edit Draft' : 'Create Concern';

        const hint = byId('sd-issue-file-hint');
        hint.textContent = 'PDF, Word, Excel, JPG or PNG \u00b7 up to 10 MB';

        if (draft) {
            byId('sd-issue-title').value = draft.title || '';
            byId('sd-issue-details').value = draft.details || '';
            byId('sd-issue-desired').value = draft.desired_resolution || '';
            if (draft.has_attachment) {
                hint.textContent = `Current attachment: ${draft.attachment_name || 'file'}. Choose a file only if you want to replace it.`;
            }
        }
        byId('sd-issue-modal-overlay').classList.add('active');
        byId('sd-issue-title').focus();
    }

    function closeCreateModal() {
        byId('sd-issue-modal-overlay')?.classList.remove('active');
        showFormError('');
    }

    function validateAttachment(file) {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        if (!ALLOWED_EXTENSIONS.includes(ext)) return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
        if (file.size > MAX_ATTACHMENT_BYTES) return `That file is ${fileSize(file.size)}. The maximum is 10 MB.`;
        return null;
    }

    async function submitForm(action) {
        const form = byId('sd-issue-form');
        const submitBtn = byId('sd-issue-submit');
        const draftBtn = byId('sd-issue-draft');
        if (!form || !form.reportValidity()) return;

        showFormError('');
        submitBtn.disabled = true;
        draftBtn.disabled = true;
        const submitLabel = submitBtn.textContent;
        const draftLabel = draftBtn.textContent;
        if (action === 'submit') submitBtn.textContent = 'Submitting…';
        else draftBtn.textContent = 'Saving…';

        // FormData carries the optional file; never set Content-Type by hand (boundary).
        const fd = new FormData(form);
        fd.set('action', action);
        const json = await api(ENDPOINT, { method: 'POST', body: fd });

        submitBtn.disabled = false;
        draftBtn.disabled = false;
        submitBtn.textContent = submitLabel;
        draftBtn.textContent = draftLabel;

        if (json.success) {
            closeCreateModal();
            toast(action === 'submit' ? 'Concern submitted.' : 'Draft saved.', 'success');
            loadAll();
        } else {
            showFormError(json.message || 'Something went wrong. Please try again.');
        }
    }

    // ── Wiring ────────────────────────────────────────────────────────────────
    function bindOnce(el, event, handler) {
        if (!el || el.dataset.sdIssueBound === event) return;
        el.addEventListener(event, handler);
        el.dataset.sdIssueBound = event;
    }

    function init() {
        const root = document.querySelector('.sd-issue-page');
        if (!root || root.dataset.sdIssueInit === '1') return; // also stops double init (DOMContentLoaded + page:loaded)
        root.dataset.sdIssueInit = '1';

        // Filters
        bindOnce(byId('sd-issue-dept'), 'change', loadAll);
        bindOnce(byId('sd-issue-status'), 'change', loadList);
        bindOnce(byId('sd-issue-search'), 'input', () => {
            clearTimeout(state.searchTimer);
            state.searchTimer = setTimeout(loadList, 300);
        });

        // Workflow stages act as status filters (click again to clear)
        document.querySelectorAll('.sd-issue-stage').forEach((btn) => {
            bindOnce(btn, 'click', () => {
                const select = byId('sd-issue-status');
                if (!select) return;
                select.value = select.value === btn.dataset.status ? '' : btn.dataset.status;
                loadList();
            });
        });

        // Create / edit modal
        bindOnce(byId('sd-issue-create'), 'click', () => openCreateModal(null));
        bindOnce(byId('sd-issue-modal-close'), 'click', closeCreateModal);
        bindOnce(byId('sd-issue-modal-overlay'), 'click', (e) => {
            if (e.target.id === 'sd-issue-modal-overlay') closeCreateModal();
        });
        bindOnce(byId('sd-issue-form'), 'submit', (e) => { e.preventDefault(); submitForm('submit'); });
        bindOnce(byId('sd-issue-draft'), 'click', () => submitForm('save_draft'));
        bindOnce(byId('sd-issue-file'), 'change', (e) => {
            const file = e.target.files[0];
            const error = file ? validateAttachment(file) : null;
            if (error) {
                e.target.value = '';
                showFormError(error);
            } else {
                showFormError('');
            }
        });

        // Concern details modal + attachment viewer
        bindOnce(byId('sd-issue-view-close'), 'click', closeCase);
        bindOnce(byId('sd-issue-view-overlay'), 'click', (e) => {
            if (e.target.id === 'sd-issue-view-overlay') closeCase();
        });
        bindOnce(byId('sd-issue-viewer-close'), 'click', closeViewer);
        bindOnce(byId('sd-issue-viewer-overlay'), 'click', (e) => {
            if (e.target.id === 'sd-issue-viewer-overlay') closeViewer();
        });

        loadAll();
    }

    // Document-level listeners are registered once; they survive page-loader navigation.
    if (!window.sdIssueGlobalsBound) {
        window.sdIssueGlobalsBound = true;

        document.addEventListener('click', (e) => {
            const open = e.target.closest('.sd-issue-open');
            if (open && open.dataset.issueId) { openCase(open.dataset.issueId); return; }

            if (e.target.closest('.sd-issue-retry')) { loadAll(); return; }

            if (e.target.closest('.sd-issue-clear')) {
                ['sd-issue-search', 'sd-issue-dept', 'sd-issue-status'].forEach((id) => {
                    const el = byId(id);
                    if (el) el.value = '';
                });
                loadAll();
            }
        });

        // Escape closes the top-most layer only
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (byId('sd-issue-viewer-overlay')?.classList.contains('active')) closeViewer();
            else if (byId('sd-issue-modal-overlay')?.classList.contains('active')) closeCreateModal();
            else if (byId('sd-issue-view-overlay')?.classList.contains('active')) closeCase();
        });
    }

    // Handles both hard refresh and sidebar navigation
    window.addEventListener('page:loaded', init);
    document.addEventListener('DOMContentLoaded', init);
    if (document.readyState !== 'loading') init();
})();