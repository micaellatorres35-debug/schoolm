/**
 * scholarships.js
 * Module: Scholarship (Guidance & Counseling)
 *
 * Talks to controller/ScholarshipController.php (flat procedural router)
 * via fetch(). Every response is shaped as { success, message?, data? }.
 *
 * Endpoints used:
 *   GET  controller/ScholarshipController.php?action=list&search=&status=&eligibility_basis=&coverage_type=&counselor_id=&page=
 *   GET  controller/ScholarshipController.php?action=details&scholarship_id=
 *   GET  controller/ScholarshipController.php?action=counselors
 *   POST controller/ScholarshipController.php?action=create           (multipart/form-data — file attachment)
 *   POST controller/ScholarshipController.php?action=mark_under_review
 *   POST controller/ScholarshipController.php?action=review
 *   POST controller/ScholarshipController.php?action=activate
 *   POST controller/ScholarshipController.php?action=finalize
 */

(function () {
    'use strict';

    const API_URL = 'controllers/ScholarshipController.php';
    const PAGE_KEY = 'scholarship'; // must match the Page class's $_GET['page'] key

    /* ---------------------------------------------------------
       Element references — rebuilt fresh on every init() call.
       See cases.js for why this can't be a one-time top-level
       const capture (stale after AJAX page swaps).
    --------------------------------------------------------- */
    let els = {};

    function queryElements() {
        return {
            searchInput: document.getElementById('scholSearchInput'),
            filterStatus: document.getElementById('scholFilterStatus'),
            filterEligibility: document.getElementById('scholFilterEligibility'),
            filterCounselor: document.getElementById('scholFilterCounselor'),
            tableBody: document.querySelector('.schol-table tbody'),
            pagination: document.querySelector('.schol-pagination'),

            createBtn: document.getElementById('scholCreateBtn'),
            createModal: document.getElementById('scholCreateModal'),
            createForm: document.getElementById('scholCreateForm'),
            createCloseBtn: document.getElementById('scholCreateCloseBtn'),
            createCancelBtn: document.getElementById('scholCreateCancelBtn'),

            overlay: document.getElementById('scholOverlay'),
            drawer: document.getElementById('scholDrawer'),
            drawerCloseBtn: document.getElementById('scholDrawerCloseBtn'),
            drawerTitle: document.getElementById('scholDrawerTitle'),
            drawerSubtitle: document.getElementById('scholDrawerSubtitle'),
            drawerBadges: document.getElementById('scholDrawerBadges'),
            quickActions: document.getElementById('scholQuickActions'),

            reviewNotesLabel: document.getElementById('scholReviewNotesLabel'),
            reviewNotesBox: document.getElementById('scholReviewNotesBox'),
            attachmentRow: document.getElementById('scholAttachmentRow'),
            attachmentLink: document.getElementById('scholAttachmentLink'),
            attachmentName: document.getElementById('scholAttachmentName'),

            actionModal: document.getElementById('scholActionModal'),
            actionTitle: document.getElementById('scholActionTitle'),
            actionBody: document.getElementById('scholActionBody'),
            actionForm: document.getElementById('scholActionForm'),
            actionCloseBtn: document.getElementById('scholActionCloseBtn'),
            actionCancelBtn: document.getElementById('scholActionCancelBtn'),
        };
    }

    let currentPage = 1;
    let activeScholarshipId = null;
    let activeStatus = null;
    let actionMode = null; // 'review' | 'finalize'
    let globalListenersBound = false; // document/window listeners must only ever bind once

    document.addEventListener('DOMContentLoaded', init);
    window.addEventListener('page:loaded', (e) => {
        if (e.detail && e.detail.page === PAGE_KEY) init();
    });

    function init() {
        els = queryElements();
        if (!els.tableBody) return;

        currentPage = 1;
        activeScholarshipId = null;
        activeStatus = null;
        actionMode = null;

        bindFilterEvents();
        bindTableEvents();
        bindPaginationEvents();
        bindCreateModal();
        bindDrawerEvents();
        bindActionModal();
    }

    /* ---------------------------------------------------------
       Filters / search
    --------------------------------------------------------- */
    function bindFilterEvents() {
        let debounceTimer;
        const triggerSearch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { currentPage = 1; loadScholarships(); }, 350);
        };

        els.searchInput?.addEventListener('input', triggerSearch);
        [els.filterStatus, els.filterEligibility, els.filterCounselor]
            .forEach(sel => sel?.addEventListener('change', () => { currentPage = 1; loadScholarships(); }));
    }

    function getFilterParams(extra = {}) {
        return new URLSearchParams({
            search: els.searchInput?.value.trim() || '',
            status: els.filterStatus?.value || '',
            eligibility_basis: els.filterEligibility?.value || '',
            counselor_id: els.filterCounselor?.value || '',
            page: currentPage,
            ...extra,
        });
    }

    async function loadScholarships() {
        setTableLoading(true);
        try {
            const params = getFilterParams();
            const res = await fetch(`${API_URL}?action=list&${params.toString()}`);
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Request failed');

            renderRows(payload.data.scholarships || []);
            renderPagination(payload.data.pagination || null);
        } catch (err) {
            console.error('Failed to load scholarship applications:', err);
            if (els.tableBody) {
                els.tableBody.innerHTML = `<tr><td colspan="7" class="schol-table__empty">Something went wrong loading applications. Please try again.</td></tr>`;
            }
        } finally {
            setTableLoading(false);
        }
    }

    function setTableLoading(isLoading) {
        if (isLoading && els.tableBody) {
            els.tableBody.innerHTML = `<tr><td colspan="7" class="schol-table__empty">Loading applications...</td></tr>`;
        }
    }

    function renderRows(rows) {
        if (!els.tableBody) return;
        if (!rows.length) {
            els.tableBody.innerHTML = `<tr><td colspan="7" class="schol-table__empty">No scholarship applications found for the selected filters.</td></tr>`;
            return;
        }
        els.tableBody.innerHTML = rows.map(rowTemplate).join('');
    }

    function rowTemplate(sc) {
        return `
            <tr class="schol-row" data-scholarship-id="${escapeHtml(sc.scholarship_id)}">
                <td>
                    <div class="schol-student-name">${escapeHtml(sc.student_name)}</div>
                    <div class="schol-student-sub">#${escapeHtml(sc.student_number)}</div>
                </td>
                <td>
                    <div class="schol-name">${escapeHtml(sc.scholarship_name)}</div>
                    ${sc.sponsor ? `<div class="schol-sponsor">${escapeHtml(sc.sponsor)}</div>` : ''}
                </td>
                <td>${escapeHtml(sc.coverage_type)}</td>
                <td>${escapeHtml(sc.eligibility_basis)}</td>
                <td><span class="schol-badge ${statusBadgeClass(sc.status)}">${escapeHtml(sc.status)}</span></td>
                <td>${escapeHtml(sc.counselor_name)}</td>
                <td>${escapeHtml(sc.applied_at_display || sc.applied_at)}</td>
            </tr>
        `;
    }

    function statusBadgeClass(status) {
        switch (status) {
            case 'Applied': return 'schol-badge--status-applied';
            case 'Under Review': return 'schol-badge--status-review';
            case 'Approved': return 'schol-badge--status-approved';
            case 'Rejected': return 'schol-badge--status-rejected';
            case 'Active': return 'schol-badge--status-active';
            case 'Completed': return 'schol-badge--status-completed';
            default: return 'schol-badge--status-terminated'; // Terminated
        }
    }

    /* ---------------------------------------------------------
       Pagination
    --------------------------------------------------------- */
    function bindPaginationEvents() {
        els.pagination?.addEventListener('click', (e) => {
            const btn = e.target.closest('.schol-pagination__page');
            if (!btn || btn.classList.contains('schol-pagination__page--active')) return;
            const page = btn.dataset.page;
            if (page) { currentPage = parseInt(page, 10); loadScholarships(); }
        });
    }

    function renderPagination(pagination) {
        if (!pagination || !els.pagination) return;
        const { total, page, totalPages, pageSize, count } = pagination;

        const summary = els.pagination.querySelector('span');
        if (summary) {
            if (total > 0) {
                const start = (page - 1) * pageSize + 1;
                summary.textContent = `Showing ${start}-${start + count - 1} of ${total} applications`;
            } else {
                summary.textContent = 'No applications found';
            }
        }

        const pagesWrap = els.pagination.querySelector('.schol-pagination__pages');
        if (!pagesWrap) return;
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="schol-pagination__page ${i === page ? 'schol-pagination__page--active' : ''}" data-page="${i}">${i}</button>`;
        }
        if (page < totalPages) {
            html += `<button class="schol-pagination__page" data-page="${page + 1}">&rsaquo;</button>`;
        }
        pagesWrap.innerHTML = html;
    }

    /* ---------------------------------------------------------
       Table row -> open drawer
    --------------------------------------------------------- */
    function bindTableEvents() {
        els.tableBody?.addEventListener('click', (e) => {
            const row = e.target.closest('.schol-row');
            if (!row) return;
            openDrawer(row.dataset.scholarshipId);
        });
    }

    /* ---------------------------------------------------------
       New Application modal (multipart submit — has a file input)
    --------------------------------------------------------- */
    function bindCreateModal() {
        els.createBtn?.addEventListener('click', () => {
            els.createForm?.reset();
            els.createModal?.classList.add('schol-modal-overlay--open');
        });
        els.createCloseBtn?.addEventListener('click', closeCreateModal);
        els.createCancelBtn?.addEventListener('click', closeCreateModal);

        els.createForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(els.createForm); // multipart — carries the file automatically

            const submitBtn = els.createForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Submitting...');

            try {
                const res = await fetch(`${API_URL}?action=create`, {
                    method: 'POST',
                    body: formData, // no Content-Type header — browser sets the multipart boundary
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                closeCreateModal();
                loadScholarships();
            } catch (err) {
                console.error('Failed to submit application:', err);
                alert(err.message || 'Could not submit the application. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Submit Application');
            }
        });
    }

    function closeCreateModal() {
        els.createModal?.classList.remove('schol-modal-overlay--open');
    }

    /* ---------------------------------------------------------
       Drawer open/close
    --------------------------------------------------------- */
    function bindDrawerEvents() {
        els.drawerCloseBtn?.addEventListener('click', closeDrawer);
        els.overlay?.addEventListener('click', closeDrawer);

        if (!globalListenersBound) {
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });
            globalListenersBound = true;
        }
    }

    async function openDrawer(scholarshipId) {
        if (!scholarshipId) return;
        activeScholarshipId = scholarshipId;

        els.overlay?.classList.add('schol-overlay--open');
        els.drawer?.classList.add('schol-drawer--open');

        try {
            const res = await fetch(`${API_URL}?action=details&scholarship_id=${encodeURIComponent(scholarshipId)}`);
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Request failed');
            populateDrawer(payload.data);
        } catch (err) {
            console.error('Failed to load scholarship details:', err);
        }
    }

    function closeDrawer() {
        els.overlay?.classList.remove('schol-overlay--open');
        els.drawer?.classList.remove('schol-drawer--open');
        activeScholarshipId = null;
        activeStatus = null;
    }

    function populateDrawer(data) {
        activeStatus = data.status;

        if (els.drawerTitle) els.drawerTitle.textContent = data.scholarship_name;
        if (els.drawerSubtitle) els.drawerSubtitle.textContent = `${data.student_name} — #${data.student_number}`;
        if (els.drawerBadges) {
            els.drawerBadges.innerHTML = `<span class="schol-badge ${statusBadgeClass(data.status)}">${escapeHtml(data.status)}</span>`;
        }

        document.querySelectorAll('#scholDrawer [data-field]').forEach(el => {
            const field = el.dataset.field;
            if (field === 'review_notes') return; // handled separately below
            el.textContent = data[field] ?? '—';
        });

        // Review notes only shown once a review has actually happened
        const hasReviewNotes = !!data.review_notes;
        if (els.reviewNotesLabel) els.reviewNotesLabel.style.display = hasReviewNotes ? '' : 'none';
        if (els.reviewNotesBox) {
            els.reviewNotesBox.style.display = hasReviewNotes ? '' : 'none';
            els.reviewNotesBox.textContent = data.review_notes || '';
        }

        // Attachment
        if (data.attachment_path) {
            els.attachmentRow.style.display = '';
            els.attachmentLink.href = data.attachment_path;
            els.attachmentName.textContent = data.attachment_name || 'Attachment';
        } else {
            els.attachmentRow.style.display = 'none';
        }

        renderQuickActions(data.status);
    }

    function renderQuickActions(status) {
        if (!els.quickActions) return;

        let html = '';
        if (status === 'Applied') {
            html = `<button type="button" class="schol-btn schol-btn--sm" id="scholMarkUnderReviewBtn">Start Review</button>`;
        } else if (status === 'Under Review') {
            html = `
                <button type="button" class="schol-btn schol-btn--sm" id="scholApproveBtn">Approve</button>
                <button type="button" class="schol-btn schol-btn--ghost schol-btn--sm" id="scholRejectBtn">Reject</button>
            `;
        } else if (status === 'Approved') {
            html = `<button type="button" class="schol-btn schol-btn--sm" id="scholActivateBtn">Activate Scholarship</button>`;
        } else if (status === 'Active') {
            html = `
                <button type="button" class="schol-btn schol-btn--sm" id="scholCompleteBtn">Mark Completed</button>
                <button type="button" class="schol-btn schol-btn--ghost schol-btn--sm" id="scholTerminateBtn">Terminate</button>
            `;
        }
        // Rejected / Completed / Terminated are terminal — no actions shown

        els.quickActions.innerHTML = html;

        document.getElementById('scholMarkUnderReviewBtn')?.addEventListener('click', markUnderReview);
        document.getElementById('scholApproveBtn')?.addEventListener('click', () => openReviewAction('approve'));
        document.getElementById('scholRejectBtn')?.addEventListener('click', () => openReviewAction('reject'));
        document.getElementById('scholActivateBtn')?.addEventListener('click', activate);
        document.getElementById('scholCompleteBtn')?.addEventListener('click', () => openFinalizeAction('completed'));
        document.getElementById('scholTerminateBtn')?.addEventListener('click', () => openFinalizeAction('terminated'));
    }

    /* ---------------------------------------------------------
       Simple one-click transitions (no form needed)
    --------------------------------------------------------- */
    async function markUnderReview() {
        await postAction('mark_under_review', { scholarship_id: activeScholarshipId });
    }

    async function activate() {
        await postAction('activate', { scholarship_id: activeScholarshipId });
    }

    async function postAction(action, body) {
        try {
            const res = await fetch(`${API_URL}?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Request failed');

            openDrawer(activeScholarshipId); // refresh
            loadScholarships(); // status may have changed in the list too
        } catch (err) {
            console.error(`Failed to ${action}:`, err);
            alert(err.message || 'Could not save. Please try again.');
        }
    }

    /* ---------------------------------------------------------
       Review (Approve/Reject) and Finalize (Complete/Terminate)
       modal — both need a notes field, so they share one shell
    --------------------------------------------------------- */
    function bindActionModal() {
        els.actionCloseBtn?.addEventListener('click', closeActionModal);
        els.actionCancelBtn?.addEventListener('click', closeActionModal);

        els.actionForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeScholarshipId || !actionMode) return;

            const formData = new FormData(els.actionForm);
            const body = Object.fromEntries(formData.entries());
            body.scholarship_id = activeScholarshipId;

            const submitBtn = els.actionForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Saving...');

            try {
                const res = await fetch(`${API_URL}?action=${actionMode.endpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                closeActionModal();
                openDrawer(activeScholarshipId);
                loadScholarships();
            } catch (err) {
                console.error('Failed to save:', err);
                alert(err.message || 'Could not save. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Save');
            }
        });
    }

    function openReviewAction(decision) {
        actionMode = { endpoint: 'review' };
        if (els.actionTitle) els.actionTitle.textContent = decision === 'approve' ? 'Approve Application' : 'Reject Application';
        els.actionBody.innerHTML = `
            <input type="hidden" name="decision" value="${decision}">
            <div class="schol-form-group">
                <label>Review Notes</label>
                <textarea name="notes" placeholder="Reason for this decision..."></textarea>
            </div>
        `;
        els.actionModal?.classList.add('schol-modal-overlay--open');
    }

    function openFinalizeAction(outcome) {
        actionMode = { endpoint: 'finalize' };
        if (els.actionTitle) els.actionTitle.textContent = outcome === 'completed' ? 'Mark as Completed' : 'Terminate Scholarship';
        els.actionBody.innerHTML = `
            <input type="hidden" name="outcome" value="${outcome}">
            <div class="schol-form-group">
                <label>Notes</label>
                <textarea name="notes" placeholder="${outcome === 'completed' ? 'Any closing notes...' : 'Reason for termination...'}"></textarea>
            </div>
        `;
        els.actionModal?.classList.add('schol-modal-overlay--open');
    }

    function closeActionModal() {
        els.actionModal?.classList.remove('schol-modal-overlay--open');
        actionMode = null;
    }

    /* ---------------------------------------------------------
       Helpers
    --------------------------------------------------------- */
    function toggleButtonLoading(btn, isLoading, label) {
        if (!btn) return;
        btn.disabled = isLoading;
        btn.textContent = label;
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();