const ReportController = 'controllers/ReportController.php';

// Local copy of Chart.js (works offline on XAMPP and on the live server, no CDN needed).
// The path is relative to the page, like ReportController above.
const RSM_CHART_SRC = 'js/assets/vendor/chart.umd.js';

const STATUS_LABELS = {
    draft: 'Draft',
    submitted: 'Submitted',
    reviewed: 'Reviewed',
    approved: 'Approved',
    rejected: 'Rejected',
};

// Same colors as the status badges in the stylesheet.
const RSM_STATUS_ORDER  = ['draft', 'submitted', 'reviewed', 'approved', 'rejected'];
const RSM_STATUS_COLORS = {
    draft: '#6c757d',
    submitted: 'rgba(32, 0, 130, 1)',
    reviewed: '#b8860b',
    approved: '#28a745',
    rejected: '#dc3545',
};

const RSM_SEARCH_DELAY = 300;

// Attachment rules — keep in sync with FileStorage.php (the server re-checks everything).
const RSM_MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;
const RSM_ALLOWED_EXTENSIONS   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

let rsmPage = 1;
let rsmListReq = 0;
let rsmAnalyticsReq = 0;
let rsmSearchTimer = null;
let rsmChartPromise = null;
let rsmCharts = {};

// ── INIT ──────────────────────────────────────────────────────────────────────
function initReportModule() {
    const page = document.querySelector('.rsm-page');
    if (!page) return;

    // Prevents the same element from getting a listener attached twice when
    // init() runs more than once on unchanged DOM (e.g. DOMContentLoaded +
    // page:loaded both firing on a hard load with no container swap between them)
    function bindOnce(el, event, handler) {
        if (!el || el.dataset.rsmBound === '1') return;
        el.addEventListener(event, handler);
        el.dataset.rsmBound = '1';
    }

    // Initial load once per rendered page
    if (page.dataset.rsmLoaded !== '1') {
        page.dataset.rsmLoaded = '1';
        rsmDestroyCharts();
        rsmPage = 1;
        refreshAll();
    }

    const reportForm = document.getElementById('report-form');

    // --- Create modal wiring ---
    const modalOverlay  = document.getElementById('rsm-modal-overlay');
    const openModalBtn  = document.getElementById('rsm-open-modal');
    const closeModalBtn = document.getElementById('rsm-modal-close');

    const openCreateModal = () => {
        reportForm?.reset();
        document.getElementById('report-id').value = '';
        hideFormError();
        syncAttachmentLabel();
        modalOverlay?.classList.add('active');
    };
    const closeCreateModal = () => {
        modalOverlay?.classList.remove('active');
        hideFormError();
    };

    bindOnce(openModalBtn, 'click', openCreateModal);
    bindOnce(closeModalBtn, 'click', closeCreateModal);
    bindOnce(modalOverlay, 'click', (e) => {
        if (e.target === modalOverlay) closeCreateModal();
    });

    // --- Report file picker ---
    const attachmentInput = document.getElementById('report-attachment');
    const attachmentClear = document.getElementById('rsm-file-clear');

    bindOnce(attachmentInput, 'change', () => {
        const file  = attachmentInput.files[0];
        const error = file ? validateAttachmentFile(file) : null;
        if (error) {
            attachmentInput.value = '';
            showFormError(error);
        } else {
            hideFormError();
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
            document.getElementById('rsm-modal-overlay')?.classList.remove('active');
            document.getElementById('rsm-view-modal-overlay')?.classList.remove('active');
            const overlay = document.getElementById('rsm-pdf-modal-overlay');
            overlay?.classList.remove('active');
            const frame = document.getElementById('rsm-pdf-frame');
            if (frame) frame.src = '';
        });
        window.rsmEscBound = true;
    }

    // --- Save as Draft ---
    bindOnce(document.getElementById('draft-btn'), 'click', () => submitReportForm('save_draft'));

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

    // --- Filters: department, type, status, dates ---
    bindOnce(document.getElementById('rsm-filter'), 'change', async () => {
        await refreshTypeOptions(); // the type list depends on the department
        rsmPage = 1;
        refreshAll();
    });
    ['rsm-type-filter', 'rsm-status-filter', 'rsm-date-from', 'rsm-date-to'].forEach((id) => {
        bindOnce(document.getElementById(id), 'change', () => {
            rsmPage = 1;
            refreshAll();
        });
    });

    // --- Search (server-side, debounced) ---
    bindOnce(document.getElementById('rsm-search'), 'input', () => {
        clearTimeout(rsmSearchTimer);
        rsmSearchTimer = setTimeout(() => { rsmPage = 1; refreshAll(); }, RSM_SEARCH_DELAY);
    });

    // --- Clear filters ---
    bindOnce(document.getElementById('rsm-clear-filters'), 'click', async () => {
        ['rsm-search', 'rsm-filter', 'rsm-type-filter', 'rsm-status-filter', 'rsm-date-from', 'rsm-date-to'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        await refreshTypeOptions();
        rsmPage = 1;
        refreshAll();
    });

    // --- Pagination ---
    bindOnce(document.getElementById('rsm-page-nav'), 'click', (e) => {
        const btn = e.target.closest('.rsm-page-btn');
        if (!btn || btn.disabled) return;
        rsmPage = parseInt(btn.dataset.page, 10) || 1;
        loadReports();
    });

    // --- Retry buttons rendered inside messages ---
    if (!window.rsmRetryBound) {
        document.addEventListener('click', (e) => {
            const retry = e.target.closest('[data-rsm-retry]');
            if (!retry) return;
            if (retry.dataset.rsmRetry === 'list') loadReports();
            else loadAnalytics();
        });
        window.rsmRetryBound = true;
    }
}

// Handles both hard refresh and sidebar navigation
window.addEventListener('page:loaded', initReportModule);
document.addEventListener('DOMContentLoaded', initReportModule);

// ── FILTERS ───────────────────────────────────────────────────────────────────
function rsmFilterParams() {
    const val = (id) => document.getElementById(id)?.value ?? '';
    const raw = {
        department_id: val('rsm-filter'),
        type:          val('rsm-type-filter'),
        status:        val('rsm-status-filter'),
        date_from:     val('rsm-date-from'),
        date_to:       val('rsm-date-to'),
        search:        val('rsm-search').trim(),
    };
    const params = new URLSearchParams();
    Object.keys(raw).forEach((k) => { if (raw[k] !== '') params.set(k, raw[k]); });
    return params;
}

function rsmFiltersActive() {
    return Array.from(rsmFilterParams().keys()).length > 0;
}

function refreshAll() {
    loadReports();
    loadAnalytics();
}

// Report types depend on the chosen department; keep the selected type if it still exists.
async function refreshTypeOptions() {
    const typeSel = document.getElementById('rsm-type-filter');
    if (!typeSel) return;

    const params = new URLSearchParams({ action: 'report_types' });
    const dept = document.getElementById('rsm-filter')?.value ?? '';
    if (dept !== '') params.set('department_id', dept);

    try {
        const res  = await fetch(`${ReportController}?${params.toString()}`);
        const json = await res.json();
        if (!json.success) return;

        const current = typeSel.value;
        typeSel.innerHTML = '<option value="">All report types</option>' +
            json.data.map((name) => `<option value="${esc(name)}">${esc(name)}</option>`).join('');
        typeSel.value = json.data.includes(current) ? current : '';
    } catch (err) {
        console.error('refreshTypeOptions error:', err);
    }
}

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

function showFormError(message) {
    const el = document.getElementById('report-form-error');
    if (!el) return;
    el.textContent = message;
    el.hidden = false;
}

function hideFormError() {
    const el = document.getElementById('report-form-error');
    if (el) el.hidden = true;
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
    const submitBtn  = document.getElementById('submit-btn');
    const draftBtn   = document.getElementById('draft-btn');

    hideFormError();

    // A report is a file: it is required to submit (drafts can be saved without one).
    if (action === 'submit' && !document.getElementById('report-attachment')?.files?.length) {
        showFormError('Please attach the report file before submitting.');
        return;
    }

    submitBtn.disabled = true;
    draftBtn.disabled  = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = action === 'submit' ? 'Submitting…' : submitBtn.textContent;

    // FormData includes the "attachment" file input automatically (multipart/form-data).
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
            rsmPage = 1;
            refreshAll();
        } else {
            if (json.debug) console.error('Report debug:', json.debug);
            showFormError(json.message);
        }
    } catch (err) {
        console.error(err);
        showFormError('A network error occurred. Please try again.');
    } finally {
        submitBtn.disabled     = false;
        draftBtn.disabled      = false;
        submitBtn.textContent  = originalLabel;
    }
}

// ── LOAD REPORTS (paginated list) ────────────────────────────────────────────
function rsmFormatDate(s) {
    if (!s) return '—';
    const d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d.getTime())
        ? '—'
        : d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function loadReports() {
    const tbody = document.querySelector('.rsm-table tbody');
    if (!tbody) return;

    const req = ++rsmListReq; // ignore out-of-order responses
    tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">Loading…</td></tr>`;

    const params = rsmFilterParams();
    params.set('action', 'list');
    params.set('page', rsmPage);

    try {
        const res  = await fetch(`${ReportController}?${params.toString()}`);
        const json = await res.json();
        if (req !== rsmListReq) return;

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">${esc(json.message || 'Failed to load reports.')}
                <button type="button" class="rsm-link-btn-text" data-rsm-retry="list">Try again</button></td></tr>`;
            rsmRenderFooter(null);
            return;
        }

        const meta = json.meta || {};
        const page = Number(meta.page) || rsmPage;
        rsmPage = page;

        if (!json.data?.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">${rsmFiltersActive()
                ? 'No reports match your search or filters.'
                : 'No reports found.'}</td></tr>`;
            rsmRenderFooter(meta, 0);
            return;
        }

        tbody.innerHTML = json.data.map(row => `
            <tr>
                <td>${esc(row.report_id)}</td>
                <td class="rsm-cell-title">${esc(row.title)}</td>
                <td>${esc(row.report_type ?? 'N/A')}</td>
                <td>${esc(row.department_name ?? 'Unassigned')}</td>
                <td>${esc(row.submitted_by ?? 'N/A')}</td>
                <td><span class="rsm-status rsm-status-${esc(row.status)}">${esc(STATUS_LABELS[row.status] ?? row.status)}</span></td>
                <td>${esc(rsmFormatDate(row.submitted_at))}</td>
                <td class="rsm-actions-cell">
                    <button type="button" class="rsm-btn-view rsm-view-report" data-report-id="${esc(row.report_id)}">View</button>
                </td>
            </tr>
        `).join('');

        rsmRenderFooter(meta, json.data.length);
    } catch (err) {
        if (req !== rsmListReq) return;
        console.error('loadReports error:', err);
        tbody.innerHTML = `<tr><td colspan="8" class="rsm-muted">Failed to load reports.
            <button type="button" class="rsm-link-btn-text" data-rsm-retry="list">Try again</button></td></tr>`;
        rsmRenderFooter(null);
    }
}

function rsmRenderFooter(meta, count) {
    const showing = document.getElementById('rsm-showing');
    const nav     = document.getElementById('rsm-page-nav');
    if (!showing || !nav) return;

    if (!meta || !meta.total) {
        showing.textContent = '';
        nav.innerHTML = '';
        return;
    }

    const from = (meta.page - 1) * meta.per_page + 1;
    showing.textContent = `Showing ${from}-${from + count - 1} of ${meta.total}`;

    if (meta.pages <= 1) {
        nav.innerHTML = '';
        return;
    }

    const nums = [];
    for (let p = 1; p <= meta.pages; p++) {
        if (p === 1 || p === meta.pages || Math.abs(p - meta.page) <= 1) nums.push(p);
    }

    let html = `<button type="button" class="rsm-page-btn" data-page="${meta.page - 1}"${meta.page === 1 ? ' disabled' : ''}>&laquo; Prev</button>`;
    let prev = 0;
    nums.forEach((p) => {
        if (p - prev > 1) html += '<span class="rsm-page-gap">…</span>';
        html += `<button type="button" class="rsm-page-btn${p === meta.page ? ' rsm-page-btn--active' : ''}" data-page="${p}"${p === meta.page ? ' aria-current="page"' : ''}>${p}</button>`;
        prev = p;
    });
    html += `<button type="button" class="rsm-page-btn" data-page="${meta.page + 1}"${meta.page === meta.pages ? ' disabled' : ''}>Next &raquo;</button>`;
    nav.innerHTML = html;
}

// ── ANALYTICS (stat cards + charts) ──────────────────────────────────────────
function rsmLoadChartLib() {
    if (window.Chart) return Promise.resolve(window.Chart);
    if (!rsmChartPromise) {
        rsmChartPromise = new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = RSM_CHART_SRC;
            s.onload  = () => resolve(window.Chart);
            s.onerror = () => { rsmChartPromise = null; reject(new Error('The chart library could not be loaded.')); };
            document.head.appendChild(s);
        });
    }
    return rsmChartPromise;
}

function rsmDestroyCharts() {
    Object.keys(rsmCharts).forEach((k) => { try { rsmCharts[k]?.destroy(); } catch (e) { /* already gone */ } });
    rsmCharts = {};
}

function rsmCssColor(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return v || fallback;
}

function rsmSetStat(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

// Shows a message instead of a chart (loading, empty, or error)
function rsmChartMessage(key, text, isError) {
    const box   = document.getElementById(`rsm-${key}-box`);
    const state = document.getElementById(`rsm-${key}-state`);
    if (box) box.hidden = true;
    if (state) {
        state.textContent = text;
        state.classList.toggle('rsm-chart-state--error', !!isError);
        state.hidden = false;
    }
}

function rsmShowChart(key) {
    const box   = document.getElementById(`rsm-${key}-box`);
    const state = document.getElementById(`rsm-${key}-state`);
    if (box) box.hidden = false;
    if (state) state.hidden = true;
}

// Creates the chart, or updates it in place when it already exists on the current canvas.
function rsmUpsertChart(key, canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const existing = rsmCharts[key];
    if (existing && existing.canvas === canvas) {
        existing.data = config.data;
        existing.update();
        return;
    }
    if (existing) existing.destroy();
    rsmCharts[key] = new window.Chart(canvas.getContext('2d'), config);
}

async function loadAnalytics() {
    if (!document.getElementById('rsm-trend-chart')) return;

    const req = ++rsmAnalyticsReq;
    ['trend', 'status', 'dept'].forEach((k) => rsmChartMessage(k, 'Loading chart…'));

    const params = rsmFilterParams();
    params.set('action', 'analytics');

    let data;
    try {
        const res  = await fetch(`${ReportController}?${params.toString()}`);
        const json = await res.json();
        if (req !== rsmAnalyticsReq) return;
        if (!json.success) throw new Error(json.message || 'Unable to load analytics.');
        data = json.data;
    } catch (err) {
        if (req !== rsmAnalyticsReq) return;
        ['total', 'pending', 'approved', 'rejected'].forEach((k) => rsmSetStat(`rsm-stat-${k}`, '—'));
        ['trend', 'status', 'dept'].forEach((k) => rsmChartMessage(k, err.message || 'Unable to load analytics.', true));
        return;
    }

    // Stat cards do not need the chart library
    rsmSetStat('rsm-stat-total', String(data.stats.total));
    rsmSetStat('rsm-stat-pending', String(data.stats.pending));
    rsmSetStat('rsm-stat-approved', String(data.stats.approved));
    rsmSetStat('rsm-stat-rejected', String(data.stats.rejected));

    rsmRenderLegend(data.statuses);

    try {
        await rsmLoadChartLib();
    } catch (err) {
        ['trend', 'status', 'dept'].forEach((k) => rsmChartMessage(k, err.message, true));
        return;
    }
    if (req !== rsmAnalyticsReq) return;

    rsmRenderTrend(data.trend);
    rsmRenderStatus(data.statuses);
    rsmRenderDepartments(data.departments);
}

function rsmMonthLabel(ym) {
    const [y, m] = ym.split('-').map(Number);
    return new Date(y, m - 1, 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

function rsmChartBase() {
    return {
        muted: rsmCssColor('--color5', '#5f5f5f'),
        grid:  'rgba(0, 0, 0, 0.06)',
        brand: rsmCssColor('--color1', 'rgba(32, 0, 130, 1)'),
    };
}

function rsmRenderTrend(trend) {
    const sub = document.getElementById('rsm-trend-sub');
    const hasRange = !!(document.getElementById('rsm-date-from')?.value || document.getElementById('rsm-date-to')?.value);
    if (sub && trend.labels.length) {
        sub.textContent = hasRange
            ? `${rsmMonthLabel(trend.labels[0])} – ${rsmMonthLabel(trend.labels[trend.labels.length - 1])}`
            : 'Last six months';
    }

    const total = trend.values.reduce((a, b) => a + b, 0);
    if (!total) {
        rsmChartMessage('trend', 'No reports were submitted in this period.');
        return;
    }

    rsmShowChart('trend');
    const c = rsmChartBase();

    rsmUpsertChart('trend', 'rsm-trend-chart', {
        type: 'line',
        data: {
            labels: trend.labels.map(rsmMonthLabel),
            datasets: [{
                label: 'Reports submitted',
                data: trend.values,
                borderColor: c.brand,
                backgroundColor: 'rgba(32, 0, 130, 0.08)',
                pointBackgroundColor: c.brand,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2,
                tension: 0.3,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.parsed.y} report${ctx.parsed.y === 1 ? '' : 's'}` } },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, color: c.muted }, grid: { color: c.grid } },
                x: { ticks: { color: c.muted }, grid: { display: false } },
            },
        },
    });
}

function rsmRenderLegend(statuses) {
    const ul = document.getElementById('rsm-status-legend');
    if (!ul) return;
    ul.innerHTML = RSM_STATUS_ORDER.map((s) => `
        <li>
            <span class="rsm-legend-dot rsm-legend-${s}" aria-hidden="true"></span>
            <span class="rsm-legend-name">${esc(STATUS_LABELS[s])}</span>
            <strong>${esc(statuses[s] ?? 0)}</strong>
        </li>`).join('');
}

function rsmRenderStatus(statuses) {
    const total = RSM_STATUS_ORDER.reduce((sum, s) => sum + (statuses[s] || 0), 0);
    if (!total) {
        rsmChartMessage('status', 'No reports to chart for this selection.');
        return;
    }

    rsmShowChart('status');

    rsmUpsertChart('status', 'rsm-status-chart', {
        type: 'doughnut',
        data: {
            labels: RSM_STATUS_ORDER.map((s) => STATUS_LABELS[s]),
            datasets: [{
                data: RSM_STATUS_ORDER.map((s) => statuses[s] || 0),
                backgroundColor: RSM_STATUS_ORDER.map((s) => RSM_STATUS_COLORS[s]),
                borderColor: '#ffffff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false }, // the counted legend below the chart is used instead
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` } },
            },
        },
    });
}

function rsmRenderDepartments(rows) {
    if (!rows.length) {
        rsmChartMessage('dept', 'No reports found for this selection.');
        return;
    }

    const box = document.getElementById('rsm-dept-box');
    if (box) box.style.setProperty('--rsm-bar-rows', String(rows.length));
    rsmShowChart('dept');
    const c = rsmChartBase();

    rsmUpsertChart('dept', 'rsm-dept-chart', {
        type: 'bar',
        data: {
            labels: rows.map((r) => r.name),
            datasets: [{
                label: 'Reports',
                data: rows.map((r) => r.count),
                backgroundColor: c.brand,
                borderRadius: 4,
                maxBarThickness: 22,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.parsed.x} report${ctx.parsed.x === 1 ? '' : 's'}` } },
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0, color: c.muted }, grid: { color: c.grid } },
                y: { ticks: { color: c.muted, autoSkip: false }, grid: { display: false } },
            },
        },
    });
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
    const has = (key) => String(report[key] ?? '').trim() !== '';
    const hasText = ['description', 'summary', 'findings', 'recommendations'].some(has);

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

    // The server only sends has_attachment / has_pdf flags, never disk paths.
    const mime = String(report.attachment_mime ?? '');
    const previewable = mime === 'application/pdf' || mime.startsWith('image/');

    const fileRow = report.has_attachment
        ? `<div class="rsm-detail-row rsm-attachment-row">
                <span class="rsm-detail-label">Report file</span>
                <span class="rsm-attachment-info">
                    <span class="rsm-attachment-name">${esc(report.attachment_name ?? 'Report file')}${report.attachment_size ? ` (${esc(formatFileSize(report.attachment_size))})` : ''}</span>
                    <button type="button" class="rsm-btn-view" id="rsm-open-attachment"
                            data-report-id="${esc(report.report_id)}" data-mime="${esc(mime)}">${previewable ? 'Full screen' : 'Open'}</button>
                    <a class="rsm-btn-view rsm-link-btn" href="${esc(fileUrl(report.report_id, 'attachment', true))}">Download</a>
                </span>
           </div>`
        : `<div class="rsm-detail-row"><span class="rsm-detail-label">Report file</span><span class="rsm-muted">No file attached</span></div>`;

    // Reports created before file submission may have a system-generated PDF
    const legacyPdfRow = report.has_pdf
        ? `<div class="rsm-detail-row">
                <span class="rsm-detail-label">Generated PDF</span>
                <span class="rsm-attachment-info">
                    <button type="button" class="rsm-btn-view" id="rsm-open-pdf" data-report-id="${esc(report.report_id)}">View PDF</button>
                </span>
           </div>`
        : '';

    const section = (title, key) => has(key) ? `<h4>${title}</h4><p>${esc(report[key])}</p>` : '';

    // The report itself is shown right inside the details window (no extra click needed).
    // PDFs and images preview inline; other file types can still be opened or downloaded above.
    let previewBlock = '';
    if (report.has_attachment && previewable) {
        previewBlock = `<div class="rsm-preview">
                <iframe class="rsm-preview-frame" src="${esc(fileUrl(report.report_id, 'attachment'))}" title="Report file preview"></iframe>
            </div>`;
    } else if (!report.has_attachment && report.has_pdf) {
        previewBlock = `<div class="rsm-preview">
                <iframe class="rsm-preview-frame" src="${esc(fileUrl(report.report_id, 'pdf'))}" title="Report PDF preview"></iframe>
            </div>`;
    } else if (report.has_attachment) {
        previewBlock = `<p class="rsm-muted rsm-preview-note">This file type can't be previewed here. Use Open or Download above.</p>`;
    }

    const aiSummaryBox = hasText ? `
        <div class="rsm-ai-box">
            <div class="rsm-ai-box-header">
                <strong>AI Summary</strong>
                <button type="button" class="rsm-btn-view" id="rsm-summarize-btn" data-report-id="${esc(report.report_id)}">
                    ${report.ai_summary ? 'Regenerate' : 'Generate Summary'}
                </button>
            </div>
            <p id="rsm-ai-summary-text" class="rsm-muted">${report.ai_summary ? esc(report.ai_summary) : 'No AI summary yet.'}</p>
        </div>` : '';

    body.innerHTML = `
        <div class="rsm-detail-row"><span class="rsm-detail-label">Title</span><span>${esc(report.title)}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Type</span><span>${esc(report.report_type ?? 'N/A')}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Department</span><span>${esc(report.department_name ?? 'N/A')}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Submitted By</span><span>${esc(report.submitted_by_name ?? 'N/A')}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Submitted</span><span>${esc(rsmFormatDate(report.submitted_at))}</span></div>
        <div class="rsm-detail-row"><span class="rsm-detail-label">Status</span><span class="rsm-status rsm-status-${esc(report.status)}">${esc(statusLabel)}</span></div>
        ${fileRow}
        ${legacyPdfRow}
        ${previewBlock}

        ${section('Description', 'description')}
        ${section('Summary', 'summary')}
        ${section('Findings', 'findings')}
        ${section('Recommendations', 'recommendations')}

        ${aiSummaryBox}

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

// Opens any controller file URL in the in-system viewer (PDFs and images render in the iframe).
function openFileModal(url, title = 'Report file') {
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
        openFileModal(fileUrl(reportId, 'attachment'), 'Report file');
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
            refreshAll();
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
            refreshAll(); // the stat cards and charts change with the decision
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

// ── TOAST ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    // One fixed container stacks the toasts, so no inline positioning is needed.
    let stack = document.getElementById('rsm-toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'rsm-toast-stack';
        stack.className = 'rsm-toast-stack';
        document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.className = `rsm-toast rsm-toast-${type}`;
    toast.setAttribute('role', 'status');
    toast.textContent = message;
    stack.appendChild(toast);

    setTimeout(() => toast.remove(), 3500);
}

// ── HELPERS ───────────────────────────────────────────────────────────────────
function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}