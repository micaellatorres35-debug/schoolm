/**
 * analytics-reports.js
 * Module: Analytics & Reports (Guidance)
 *
 * Flow: pick type + period -> Generate -> fetch chart data -> draw Chart.js
 * charts off-screen -> post them as PNG with the request -> server builds and
 * saves the dompdf PDF -> report opens in the in-modal viewer.
 * Every JSON response is { success, message?, data? }.
 */

const API_URL = 'controllers/AnalyticsReportController.php';
const PAGE_KEY = 'analytics-reports';
// Pinned Chart.js build. If the school network is offline, host this file
// locally and point this constant at it.
const CHART_JS_SRC = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js';

let els = {};
let globalListenersBound = false;
let chartJsPromise = null;

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('page:loaded', init);

function queryElements() {
    return {
        root: document.querySelector(`.rprt-page[data-page-key="${PAGE_KEY}"]`),
        type: document.getElementById('rprtReportType'),
        preset: document.getElementById('rprtPreset'),
        customGroup: document.getElementById('rprtCustomGroup'),
        dateFrom: document.getElementById('rprtDateFrom'),
        dateTo: document.getElementById('rprtDateTo'),
        generateBtn: document.getElementById('rprtGenerateBtn'),
        status: document.getElementById('rprtStatus'),
        historyWrapper: document.getElementById('rprtHistoryWrapper'),
        modal: document.getElementById('rprtViewerModal'),
        viewerTitle: document.getElementById('rprtViewerTitle'),
        viewerFrame: document.getElementById('rprtViewerFrame'),
        viewerClose: document.getElementById('rprtViewerClose'),
    };
}

function init() {
    els = queryElements();
    if (!els.root) return; // fragment is not currently in the page

    bindOnce(els.preset, 'rprtBound', 'change', onPresetChange);
    bindOnce(els.generateBtn, 'rprtBound', 'click', onGenerate);
    bindOnce(els.historyWrapper, 'rprtBound', 'click', onHistoryClick);
    bindOnce(els.viewerClose, 'rprtBound', 'click', closeViewer);
    bindOnce(els.modal, 'rprtBound', 'click', (e) => {
        if (e.target === els.modal) closeViewer();
    });
    bindGlobalListeners();

    onPresetChange();
    loadHistory();
}

function bindOnce(el, flag, event, handler) {
    if (!el || el.dataset[flag]) return;
    el.dataset[flag] = '1';
    el.addEventListener(event, handler);
}

function bindGlobalListeners() {
    if (globalListenersBound) return;
    globalListenersBound = true;

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeViewer();
    });
}

/* ---------------------------------------------------------
   Period handling
--------------------------------------------------------- */
function onPresetChange() {
    if (!els.preset || !els.customGroup) return;
    els.customGroup.hidden = els.preset.value !== 'custom';
}

function pad(n) {
    return String(n).padStart(2, '0');
}

function toYmd(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function presetRange(key) {
    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth();

    switch (key) {
        case 'this_month':
            return [toYmd(new Date(y, m, 1)), toYmd(new Date(y, m + 1, 0))];
        case 'last_month':
            return [toYmd(new Date(y, m - 1, 1)), toYmd(new Date(y, m, 0))];
        case 'last_30': {
            const start = new Date(now);
            start.setDate(start.getDate() - 29);
            return [toYmd(start), toYmd(now)];
        }
        case 'this_year':
            return [toYmd(new Date(y, 0, 1)), toYmd(new Date(y, 11, 31))];
        default:
            return null;
    }
}

function getRange() {
    if (els.preset.value === 'custom') {
        const from = els.dateFrom.value;
        const to = els.dateTo.value;
        if (!from || !to || from > to) return null;
        return { from, to };
    }

    const range = presetRange(els.preset.value);
    return range ? { from: range[0], to: range[1] } : null;
}

/* ---------------------------------------------------------
   Generate
--------------------------------------------------------- */
async function onGenerate() {
    const range = getRange();
    if (!range) {
        setStatus('Choose a valid date range (start date on or before end date).', true);
        return;
    }

    const type = els.type.value;
    setBusy(true);
    setStatus('Preparing report...', false);

    try {
        await ensureChartJs();

        const params = new URLSearchParams({
            action: 'chart_data',
            type,
            date_from: range.from,
            date_to: range.to,
        });
        const preview = await fetchJson(`${API_URL}?${params.toString()}`);

        if (!preview.data.total) {
            setStatus('No records found for the selected period.', true);
            return;
        }

        setStatus('Building PDF...', false);

        const form = new FormData();
        form.append('action', 'generate');
        form.append('type', type);
        form.append('date_from', range.from);
        form.append('date_to', range.to);
        preview.data.charts.forEach((chart) => form.append('charts[]', renderChartToPng(chart)));

        const result = await fetchJson(API_URL, { method: 'POST', body: form });

        setStatus('Report generated and saved.', false);
        await loadHistory();
        openViewer(result.data.report_id, result.data.title);
    } catch (err) {
        console.error('Failed to generate report:', err);
        setStatus(err.message || 'Something went wrong generating this report.', true);
    } finally {
        setBusy(false);
    }
}

/* ---------------------------------------------------------
   Chart.js -> PNG
--------------------------------------------------------- */
function ensureChartJs() {
    if (window.Chart) return Promise.resolve();

    if (!chartJsPromise) {
        chartJsPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = CHART_JS_SRC;
            script.onload = resolve;
            script.onerror = () => {
                chartJsPromise = null;
                reject(new Error('Could not load Chart.js. Check CHART_JS_SRC or your connection.'));
            };
            document.head.appendChild(script);
        });
    }

    return chartJsPromise;
}

function renderChartToPng(def) {
    const canvas = document.createElement('canvas');
    canvas.width = 640;
    canvas.height = 340;

    const accent = getComputedStyle(document.documentElement).getPropertyValue('--color1').trim() || '#2f6fed';

    const chart = new window.Chart(canvas.getContext('2d'), {
        type: def.kind,
        data: {
            labels: def.labels,
            datasets: [{
                data: def.values,
                backgroundColor: accent,
                borderColor: accent,
                borderWidth: def.kind === 'line' ? 2 : 0,
                tension: 0.25,
                fill: false,
            }],
        },
        options: {
            responsive: false,
            animation: false,
            devicePixelRatio: 2,
            plugins: {
                legend: { display: false },
                title: { display: true, text: def.title },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
        },
        plugins: [{
            id: 'rprtWhiteBackground',
            beforeDraw(c) {
                const ctx = c.ctx;
                ctx.save();
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, c.width, c.height);
                ctx.restore();
            },
        }],
    });

    const png = canvas.toDataURL('image/png');
    chart.destroy();

    return png;
}

/* ---------------------------------------------------------
   History + viewer
--------------------------------------------------------- */
async function loadHistory() {
    if (!els.historyWrapper) return;

    try {
        const payload = await fetchJson(`${API_URL}?action=list`);
        renderHistory(payload.data.reports);
    } catch (err) {
        console.error('Failed to load saved reports:', err);
        els.historyWrapper.innerHTML = `<div class="rprt-empty">Could not load saved reports.</div>`;
    }
}

function renderHistory(reports) {
    if (!reports.length) {
        els.historyWrapper.innerHTML = `<div class="rprt-empty">No saved reports yet. Generate one above.</div>`;
        return;
    }

    const rows = reports.map((r) => `
        <tr>
            <td>${escapeHtml(r.type_label)}</td>
            <td>${escapeHtml(formatDay(r.period_from))} &ndash; ${escapeHtml(formatDay(r.period_to))}</td>
            <td>${escapeHtml(r.record_count)}</td>
            <td>${escapeHtml(r.generated_by_name || '—')}</td>
            <td>${escapeHtml(formatStamp(r.generated_at))}</td>
            <td class="rprt-actions-cell">
                <button type="button" class="rprt-btn rprt-btn--sm" data-action="view" data-id="${escapeHtml(r.report_id)}" data-title="${escapeHtml(r.type_label)}">View</button>
                ${r.can_delete ? `<button type="button" class="rprt-btn rprt-btn--sm" data-action="delete" data-id="${escapeHtml(r.report_id)}">Delete</button>` : ''}
            </td>
        </tr>
    `).join('');

    els.historyWrapper.innerHTML = `
        <div class="rprt-table-wrapper">
            <table class="rprt-table">
                <thead>
                    <tr>
                        <th>Report</th><th>Period</th><th>Records</th>
                        <th>Generated By</th><th>Generated</th><th></th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
        <div class="rprt-results-count">${reports.length} saved report${reports.length === 1 ? '' : 's'}</div>
    `;
}

async function onHistoryClick(e) {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const id = btn.dataset.id;

    if (btn.dataset.action === 'view') {
        openViewer(id, btn.dataset.title);
        return;
    }

    if (btn.dataset.action === 'delete') {
        if (!window.confirm('Delete this saved report? This cannot be undone.')) return;

        btn.disabled = true;
        try {
            const form = new FormData();
            form.append('action', 'delete');
            form.append('id', id);
            await fetchJson(API_URL, { method: 'POST', body: form });
            setStatus('Report deleted.', false);
            await loadHistory();
        } catch (err) {
            console.error('Failed to delete report:', err);
            setStatus(err.message || 'Could not delete the report.', true);
            btn.disabled = false;
        }
    }
}

function openViewer(id, title) {
    if (!els.modal) return;
    els.viewerTitle.textContent = title || 'Report';
    els.viewerFrame.src = `${API_URL}?action=view&id=${encodeURIComponent(id)}`;
    els.modal.hidden = false;
}

function closeViewer() {
    if (!els.modal || els.modal.hidden) return;
    els.modal.hidden = true;
    els.viewerFrame.src = 'about:blank';
}

/* ---------------------------------------------------------
   Helpers
--------------------------------------------------------- */
async function fetchJson(url, options) {
    const res = await fetch(url, options);

    let payload;
    try {
        payload = await res.json();
    } catch (err) {
        throw new Error('Unexpected server response.');
    }

    if (!payload.success) throw new Error(payload.message || 'Request failed.');
    return payload;
}

function setBusy(isBusy) {
    if (!els.generateBtn) return;
    els.generateBtn.disabled = isBusy;
    els.generateBtn.textContent = isBusy ? 'Generating...' : 'Generate';
}

function setStatus(message, isError) {
    if (!els.status) return;
    els.status.textContent = message || '';
    els.status.hidden = !message;
    els.status.classList.toggle('rprt-status--error', Boolean(isError));
}

function formatDay(value) {
    const d = new Date(`${value}T00:00:00`);
    return isNaN(d.getTime())
        ? value
        : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatStamp(value) {
    const d = new Date(String(value).replace(' ', 'T'));
    return isNaN(d.getTime())
        ? value
        : d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
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