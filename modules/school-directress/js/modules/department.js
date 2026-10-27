/* ============================================================
   School Directress — Department & Unit Management
   Prefix: dm / DM_
   Rows are server-rendered; search + pagination run client-side.
   ============================================================ */

const PAGE_KEY = 'department-management';

const DM_PER_PAGE = 10;
const DM_SEARCH_DELAY = 200;

let dmEls = {};
let dmPage = 1;
let dmSearchTimer = null;

/* ── Setup ──────────────────────────────────────── */

function dmQueryElements() {
    dmEls = {
        search:    document.getElementById('dmSearch'),
        exportBtn: document.getElementById('dmExportBtn'),
        body:      document.getElementById('dmBody'),
        noMatch:   document.getElementById('dmNoMatch'),
        showing:   document.getElementById('dmShowing'),
        pageNav:   document.getElementById('dmPageNav')
    };
    return !!dmEls.body;
}

function dmBindOnce(el, flag, evt, handler) {
    if (!el || el.dataset[flag]) return;
    el.dataset[flag] = '1';
    el.addEventListener(evt, handler);
}

function dmInit() {
    if (!dmQueryElements()) return;

    dmBindOnce(dmEls.search, 'dmBound', 'input', function () {
        clearTimeout(dmSearchTimer);
        dmSearchTimer = setTimeout(function () {
            dmPage = 1;
            dmApply();
        }, DM_SEARCH_DELAY);
    });

    dmBindOnce(dmEls.exportBtn, 'dmBound', 'click', dmExportCsv);

    dmBindOnce(dmEls.body, 'dmBound', 'click', function (e) {
        const row = e.target.closest('tr.dm-row');
        if (row) dmToggle(row, row.dataset.detail);
    });

    dmBindOnce(dmEls.pageNav, 'dmBound', 'click', function (e) {
        const btn = e.target.closest('.dm-page-btn');
        if (!btn || btn.disabled) return;
        dmPage = parseInt(btn.dataset.page, 10) || 1;
        dmApply();
    });

    // Initial render once per rendered page (init fires on both DOMContentLoaded and page:loaded)
    if (!dmEls.body.dataset.dmLoaded) {
        dmEls.body.dataset.dmLoaded = '1';
        dmPage = 1;
        dmApply();
    }
}

/* ── Filter + paginate ──────────────────────────── */

function dmAllRows() {
    return Array.prototype.slice.call(dmEls.body.querySelectorAll('tr.dm-row'));
}

function dmMatchingRows() {
    const q = dmEls.search ? dmEls.search.value.trim().toLowerCase() : '';
    return dmAllRows().filter(function (row) {
        return !q || (row.dataset.search || '').indexOf(q) !== -1;
    });
}

function dmApply() {
    if (!dmEls.body) return;

    const all = dmAllRows();
    if (!all.length) {
        if (dmEls.showing) dmEls.showing.textContent = '';
        if (dmEls.pageNav) dmEls.pageNav.innerHTML = '';
        return;
    }

    const matches = dmMatchingRows();
    const total = matches.length;
    const pages = Math.max(1, Math.ceil(total / DM_PER_PAGE));
    dmPage = Math.min(Math.max(dmPage, 1), pages);

    const start = (dmPage - 1) * DM_PER_PAGE;
    const visible = matches.slice(start, start + DM_PER_PAGE);

    // Hide everything (and collapse details), then show the current page
    all.forEach(function (row) {
        row.style.display = 'none';
        row.classList.remove('dm-open');
        const detail = document.getElementById(row.dataset.detail);
        if (detail) {
            detail.classList.remove('dm-open');
            detail.style.display = 'none';
        }
    });

    visible.forEach(function (row) {
        row.style.display = '';
        const detail = document.getElementById(row.dataset.detail);
        if (detail) detail.style.display = ''; // CSS keeps it hidden until .dm-open
    });

    if (dmEls.noMatch) dmEls.noMatch.style.display = total ? 'none' : '';

    dmRenderShowing(start, visible.length, total);
    dmRenderPager(pages);
}

/* ── Row expand / collapse ──────────────────────── */

function dmToggle(row, detailId) {
    const detail = document.getElementById(detailId);
    if (!detail) return;
    const open = !row.classList.contains('dm-open');
    row.classList.toggle('dm-open', open);
    detail.classList.toggle('dm-open', open);
}

/* ── Render footer ──────────────────────────────── */

function dmRenderShowing(start, count, total) {
    if (!dmEls.showing) return;
    if (!total) {
        dmEls.showing.textContent = '';
        return;
    }
    dmEls.showing.textContent = 'Showing ' + (start + 1) + '-' + (start + count) + ' of ' + total;
}

function dmRenderPager(pages) {
    if (!dmEls.pageNav) return;
    if (pages <= 1) {
        dmEls.pageNav.innerHTML = '';
        return;
    }

    const nums = [];
    for (let p = 1; p <= pages; p++) {
        if (p === 1 || p === pages || Math.abs(p - dmPage) <= 1) nums.push(p);
    }

    let html = '<button type="button" class="dm-page-btn" data-page="' + (dmPage - 1) + '"' +
               (dmPage === 1 ? ' disabled' : '') + '>&laquo; Prev</button>';

    let prev = 0;
    nums.forEach(function (p) {
        if (p - prev > 1) html += '<span class="dm-page-gap">…</span>';
        html += '<button type="button" class="dm-page-btn' + (p === dmPage ? ' dm-page-btn--active' : '') +
                '" data-page="' + p + '">' + p + '</button>';
        prev = p;
    });

    html += '<button type="button" class="dm-page-btn" data-page="' + (dmPage + 1) + '"' +
            (dmPage === pages ? ' disabled' : '') + '>Next &raquo;</button>';

    dmEls.pageNav.innerHTML = html;
}

/* ── Export (all rows matching the current search) ─ */

function dmCsvCell(v) {
    return '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
}

function dmExportCsv() {
    const rows = dmMatchingRows();
    if (!rows.length) return;

    const lines = [['Department', 'Department Head', 'Members'].map(dmCsvCell).join(',')];
    rows.forEach(function (row) {
        lines.push([row.dataset.name, row.dataset.head, row.dataset.count].map(dmCsvCell).join(','));
    });

    const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'departments_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

/* ── Lifecycle ──────────────────────────────────── */

document.addEventListener('DOMContentLoaded', dmInit);
document.addEventListener('page:loaded', dmInit);

// Safety net: the router may insert the page after (or without) firing the events
// above, so also watch for #dmBody appearing. dmInit is safe to call repeatedly —
// the initial render is guarded by a flag on the table body.
function dmWatchDom() {
    if (window.__dmObserver || !document.body) return;
    window.__dmObserver = new MutationObserver(function () {
        const body = document.getElementById('dmBody');
        if (body && !body.dataset.dmLoaded) dmInit();
    });
    window.__dmObserver.observe(document.body, { childList: true, subtree: true });
}

dmWatchDom();
document.addEventListener('DOMContentLoaded', dmWatchDom);

if (document.readyState !== 'loading') {
    dmInit();
}