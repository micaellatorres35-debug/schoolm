/* ============================================================
   School Directress — User & Access Management (AJAX)
   Prefix: um / UM_
   ============================================================ */

const PAGE_KEY = 'user-management';

const UM_ENDPOINT = 'controllers/UserController.php';
const UM_SEARCH_DELAY = 300;
const UM_LOOKUP_DELAY = 300;
const UM_REQUEST_TIMEOUT = 15000;
const UM_ACTIVITY_LIMIT = 15;
const UM_MIN_LOOKUP = 2;

let umPage = 1;
let umTotalPages = 1;
let umRequestId = 0;
let umSearchTimer = null;
let umLookupTimer = null;
let umLookupId = 0;
let umView = 'cards';
let umRows = [];
let umBusy = false;
let umToastTimer = null;
let umMouseDownTarget = null;

let umDetailUserId = null;
let umDetailData = null;
let umActivityOffset = 0;
let umActivityTotal = 0;
let umActivityLoaded = false;
let umActivityReq = 0;

let umStatusTarget = null;
let umModalStack = [];
let umReturnFocus = {};

/* ── Setup ──────────────────────────────────────── */

function umEl(id) {
    return document.getElementById(id);
}

function umBindOnce(el, flag, evt, handler) {
    if (!el || el.dataset[flag]) return;
    el.dataset[flag] = '1';
    el.addEventListener(evt, handler);
}

function umInit() {
    const root = umEl('umRoot');
    if (!root) return;

    umBindOnce(root, 'umBoundClick', 'click', umOnClick);
    umBindOnce(root, 'umBoundInput', 'input', umOnInput);
    umBindOnce(root, 'umBoundChange', 'change', umOnChange);
    umBindOnce(root, 'umBoundKey', 'keydown', umOnRootKeydown);
    umBindOnce(root, 'umBoundSubmit', 'submit', umOnSubmit);
    umBindOnce(root, 'umBoundDown', 'mousedown', function (e) { umMouseDownTarget = e.target; });

    umBindGlobalsOnce();

    // Initial load once per rendered page (init fires on both DOMContentLoaded and page:loaded)
    if (!root.dataset.umLoaded) {
        root.dataset.umLoaded = '1';
        umPage = 1;
        umView = 'cards';
        umRows = [];
        umBusy = false;
        umDetailUserId = null;
        umDetailData = null;
        umStatusTarget = null;
        umModalStack = [];
        umReturnFocus = {};
        umSyncBodyLock();
        umLoad();
    }
}

function umBindGlobalsOnce() {
    if (window.__umGlobalsBound) return;
    window.__umGlobalsBound = true;

    // Click outside an open row menu closes it
    document.addEventListener('click', function (e) {
        if (!e.target.closest || e.target.closest('.um-menu-wrap')) return;
        umCloseMenus(false);
    });

    // Escape closes the open menu first, then the top-most modal
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !umEl('umRoot')) return;
        if (document.querySelector('.um-menu:not([hidden])')) {
            umCloseMenus(true);
            return;
        }
        if (umModalStack.length) {
            e.preventDefault();
            umCloseModal(umModalStack[umModalStack.length - 1]);
        }
    });
}

/* ── Helpers ────────────────────────────────────── */

function umEsc(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function umNA(v, fallback) {
    return (v === null || v === undefined || String(v).trim() === '') ? (fallback || 'Not available') : String(v);
}

function umSet(id, text) {
    const el = umEl(id);
    if (el) el.textContent = text;
}

function umFullName(r) {
    const parts = [r.first_name, r.middle_name, r.last_name, r.suffix]
        .filter(function (p) { return p && String(p).trim() !== ''; });
    return parts.length ? parts.join(' ') : 'Unnamed employee';
}

function umInitials(r) {
    const f = String(r.first_name || '').trim().charAt(0);
    const l = String(r.last_name || '').trim().charAt(0);
    return (f + l).toUpperCase() || '?';
}

function umAvatarClass(r) {
    const n = (parseInt(r.employee_id, 10) || 0) % 3;
    return n === 1 ? ' um-avatar-blue' : (n === 2 ? ' um-avatar-violet' : '');
}

function umParseDate(s) {
    if (!s) return null;
    const d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d.getTime()) ? null : d;
}

function umFormatTime(d) {
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function umFormatWhen(s, fallback) {
    const d = umParseDate(s);
    if (!d) return fallback || 'Not available';
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    const same = function (a, b) { return a.toDateString() === b.toDateString(); };
    if (same(d, today)) return 'Today, ' + umFormatTime(d);
    if (same(d, yesterday)) return 'Yesterday, ' + umFormatTime(d);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + ', ' + umFormatTime(d);
}

function umFormatDate(s) {
    const d = umParseDate(s);
    return d ? d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Not available';
}

function umIsDeactivated(r) {
    return r.account_status === 'Deactivated';
}

function umIsLocked(r) {
    return Number(r.is_locked) === 1;
}

function umStatusBadge(r) {
    const off = umIsDeactivated(r);
    return '<span class="um-status ' + (off ? 'is-inactive' : 'is-active') + '">' + (off ? 'Deactivated' : 'Active') + '</span>' +
        (umIsLocked(r) ? ' <span class="um-status is-locked">Locked</span>' : '');
}

function umFilterParams() {
    const val = function (id) { const el = umEl(id); return el ? el.value : ''; };
    return {
        search: val('umSearch').trim(),
        status: val('umStatus'),
        role: val('umRole'),
        department: val('umDepartment'),
        unit: val('umUnit')
    };
}

function umFindUser(id) {
    id = parseInt(id, 10);
    const fromRows = umRows.filter(function (r) { return parseInt(r.user_id, 10) === id; })[0];
    if (fromRows) return fromRows;
    if (umDetailData && parseInt(umDetailData.user_id, 10) === id) return umDetailData;
    return null;
}

/* ── AJAX ───────────────────────────────────────── */

async function umRequest(action, params, method) {
    method = method || 'GET';
    const root = umEl('umRoot');
    const controller = new AbortController();
    const timer = setTimeout(function () { controller.abort(); }, UM_REQUEST_TIMEOUT);

    // X-Requested-With makes auth/session.php answer with JSON instead of a redirect
    const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    const opts = { method: method, credentials: 'same-origin', headers: headers, signal: controller.signal };
    let url = UM_ENDPOINT + '?action=' + encodeURIComponent(action);

    if (method === 'GET') {
        const qs = new URLSearchParams(params || {}).toString();
        if (qs) url += '&' + qs;
    } else {
        headers['X-CSRF-Token'] = root ? (root.dataset.csrf || '') : '';
        opts.body = new URLSearchParams(params || {});
    }

    let res;
    try {
        res = await fetch(url, opts);
    } catch (err) {
        throw new Error(err.name === 'AbortError' ? 'The request timed out. Please try again.' : 'Network error. Check your connection and try again.');
    } finally {
        clearTimeout(timer);
    }

    let json;
    try {
        json = await res.json();
    } catch (err) {
        throw new Error('Unexpected server response.');
    }

    if (json && json.redirect) {
        window.location.href = json.redirect;
        throw new Error('Your session has ended. Redirecting to sign in…');
    }
    if (!res.ok || !json.success) {
        const e = new Error((json && json.message) || 'Request failed.');
        e.status = res.status;
        e.errors = (json && json.errors) || {};
        throw e;
    }
    return json.data;
}

/* ── Directory: load + render ───────────────────── */

async function umLoad() {
    if (!umEl('umRoot')) return;

    const requestId = ++umRequestId; // ignore out-of-order responses
    umShowMessage('Loading users…');

    try {
        const params = umFilterParams();
        params.page = umPage;
        const data = await umRequest('list', params);
        if (requestId !== umRequestId) return;

        umPage = data.page;
        umTotalPages = data.pages;
        umRows = data.rows;
        umRenderStats(data.stats);
        umRenderDirectory();
        umRenderShowing(data);
        umRenderPager();
    } catch (err) {
        if (requestId !== umRequestId) return;
        umShowMessage(err.message || 'Unable to load users.', true);
        umSet('umShowing', '');
        const nav = umEl('umPageNav');
        if (nav) nav.innerHTML = '';
    }
}

function umShowMessage(text, isError) {
    const msg = umEl('umEmpty');
    if (!msg) return;
    msg.textContent = text;
    msg.classList.toggle('um-empty-state--error', !!isError);
    msg.hidden = false;
    umEl('umCardGrid').hidden = true;
    umEl('umTableWrap').hidden = true;
}

function umRenderStats(stats) {
    if (!stats) return;
    umSet('umStatTotal', String(stats.total));
    umSet('umStatActive', String(stats.active));
    umSet('umStatInactive', String(stats.deactivated));
}

function umRenderDirectory() {
    if (!umRows.length) {
        umShowMessage('No users match your search or filters.');
        return;
    }
    umEl('umEmpty').hidden = true;
    umEl('umCardGrid').innerHTML = umRows.map(umCardHtml).join('');
    umEl('umTableBody').innerHTML = umRows.map(umRowHtml).join('');
    umApplyView();
}

function umApplyView() {
    const has = umRows.length > 0;
    umEl('umCardGrid').hidden = !(has && umView === 'cards');
    umEl('umTableWrap').hidden = !(has && umView === 'table');
    document.querySelectorAll('#umRoot .um-view-btn').forEach(function (b) {
        const on = b.dataset.view === umView;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
}

function umMenuHtml(r) {
    const name = umFullName(r);
    const off = umIsDeactivated(r);
    return '' +
    '<div class="um-menu-wrap">' +
        '<button class="um-icon-btn" type="button" data-um-action="menu" aria-haspopup="true" aria-expanded="false" aria-label="More actions for ' + umEsc(name) + '">⋯</button>' +
        '<div class="um-menu" role="menu" hidden>' +
            '<button type="button" role="menuitem" data-um-action="edit-user" data-user="' + umEsc(r.user_id) + '">Edit user</button>' +
            '<button type="button" role="menuitem" data-um-action="reset-password" data-user="' + umEsc(r.user_id) + '">Reset password</button>' +
            (umIsLocked(r) ? '<button type="button" role="menuitem" data-um-action="unlock" data-user="' + umEsc(r.user_id) + '">Unlock account</button>' : '') +
            '<button type="button" role="menuitem" class="um-menu-danger" data-um-action="toggle-status" data-user="' + umEsc(r.user_id) + '" data-mode="' + (off ? 'reactivate' : 'deactivate') + '">' +
                (off ? 'Reactivate account' : 'Deactivate account') + '</button>' +
        '</div>' +
    '</div>';
}

function umCardHtml(r) {
    const name = umFullName(r);
    const subline = umNA(r.employee_code, 'No employee ID') + ' · ' + umNA(r.role_name, 'No role');
    const place = [r.department_name, r.unit_name].filter(function (x) { return x && String(x).trim() !== ''; }).join(' / ');

    return '' +
    '<article class="um-user-card" data-user-id="' + umEsc(r.user_id) + '">' +
        '<div class="um-card-top">' +
            '<div class="um-avatar' + umAvatarClass(r) + '" aria-hidden="true">' + umEsc(umInitials(r)) + '</div>' +
            '<div class="um-user-identity">' +
                '<h3>' + umEsc(name) + '</h3>' +
                '<p>' + umEsc(subline) + '</p>' +
                '<p class="um-user-department">' + umEsc(umNA(place, 'No department assigned')) + '</p>' +
                (r.position_name ? '<p>' + umEsc(r.position_name) + '</p>' : '') +
            '</div>' +
            umMenuHtml(r) +
        '</div>' +
        '<div class="um-card-meta">' +
            '<div><span>Account status</span><strong>' + umStatusBadge(r) + '</strong></div>' +
            '<div><span>Last login</span><strong>' + umEsc(umFormatWhen(r.last_login, 'No login recorded')) + '</strong></div>' +
        '</div>' +
        '<div class="um-card-actions">' +
            '<button class="um-btn um-btn-secondary" type="button" data-um-action="view-details" data-user="' + umEsc(r.user_id) + '">View details</button>' +
            '<button class="um-btn um-btn-quiet" type="button" data-um-action="edit-user" data-user="' + umEsc(r.user_id) + '">Edit</button>' +
        '</div>' +
    '</article>';
}

function umRowHtml(r) {
    const name = umFullName(r);
    const place = [r.department_name, r.unit_name].filter(function (x) { return x && String(x).trim() !== ''; }).join(' / ');
    return '' +
    '<tr data-user-id="' + umEsc(r.user_id) + '">' +
        '<td><div class="um-table-person"><span class="um-avatar um-avatar-small' + umAvatarClass(r) + '" aria-hidden="true">' + umEsc(umInitials(r)) + '</span>' +
            '<span><strong>' + umEsc(name) + '</strong><small>' + umEsc(umNA(r.employee_code, 'No employee ID')) + '</small></span></div></td>' +
        '<td>' + umEsc(umNA(r.role_name, 'No role')) + '</td>' +
        '<td>' + umEsc(umNA(place, 'Not assigned')) + '</td>' +
        '<td>' + umEsc(umFormatWhen(r.last_login, 'No login recorded')) + '</td>' +
        '<td>' + umStatusBadge(r) + '</td>' +
        '<td><div class="um-row-actions">' +
            '<button class="um-btn um-btn-quiet" type="button" data-um-action="view-details" data-user="' + umEsc(r.user_id) + '">View</button>' +
            '<button class="um-btn um-btn-quiet" type="button" data-um-action="edit-user" data-user="' + umEsc(r.user_id) + '">Edit</button>' +
        '</div></td>' +
    '</tr>';
}

function umRenderShowing(data) {
    if (!data.total) { umSet('umShowing', ''); return; }
    const from = (data.page - 1) * data.per_page + 1;
    const to = from + data.rows.length - 1;
    umSet('umShowing', 'Showing ' + from + '-' + to + ' of ' + data.total);
}

function umRenderPager() {
    const nav = umEl('umPageNav');
    if (!nav) return;
    if (umTotalPages <= 1) { nav.innerHTML = ''; return; }

    const nums = [];
    for (let p = 1; p <= umTotalPages; p++) {
        if (p === 1 || p === umTotalPages || Math.abs(p - umPage) <= 1) nums.push(p);
    }

    let html = '<button type="button" class="um-page-btn" data-um-action="page" data-page="' + (umPage - 1) + '"' +
               (umPage === 1 ? ' disabled' : '') + '>&laquo; Prev</button>';
    let prev = 0;
    nums.forEach(function (p) {
        if (p - prev > 1) html += '<span class="um-page-gap">…</span>';
        html += '<button type="button" class="um-page-btn' + (p === umPage ? ' um-page-btn--active' : '') +
                '" data-um-action="page" data-page="' + p + '"' + (p === umPage ? ' aria-current="page"' : '') + '>' + p + '</button>';
        prev = p;
    });
    html += '<button type="button" class="um-page-btn" data-um-action="page" data-page="' + (umPage + 1) + '"' +
            (umPage === umTotalPages ? ' disabled' : '') + '>Next &raquo;</button>';
    nav.innerHTML = html;
}

/* ── Event delegation ───────────────────────────── */

function umOnClick(e) {
    const t = e.target;

    // Backdrop click closes the top modal, but only if the press also started on the backdrop
    if (t.classList && t.classList.contains('um-modal-backdrop')) {
        if (umMouseDownTarget === t) umCloseModal(t.id);
        return;
    }

    const btn = t.closest('[data-um-action]');
    if (!btn) {
        // Clicking the card body opens details; controls inside it never do
        const card = t.closest('.um-user-card');
        if (card && !t.closest('button, a, input, select, textarea, .um-menu')) {
            umOpenDetails(card.dataset.userId);
        }
        return;
    }
    if (btn.disabled) return;

    const action = btn.dataset.umAction;
    const userId = btn.dataset.user || '';
    if (action !== 'menu') umCloseMenus(false);

    switch (action) {
        case 'menu':           umToggleMenu(btn); break;
        case 'view-switch':    umView = btn.dataset.view === 'table' ? 'table' : 'cards'; umApplyView(); break;
        case 'page':           umPage = parseInt(btn.dataset.page, 10) || 1; umLoad(); break;
        case 'clear-filters':  umClearFilters(); break;
        case 'export':         umExportCsv(); break;
        case 'open-create':    umOpenCreate(); break;
        case 'view-details':   umOpenDetails(userId); break;
        case 'edit-user':      umOpenEdit(userId || umDetailUserId, ''); break;
        case 'edit-access':    umOpenEdit(umDetailUserId, 'umEditRole'); break;
        case 'reset-password': umOpenEdit(userId || umDetailUserId, 'umEditPassword'); break;
        case 'toggle-status':  umOpenStatusFromButton(btn); break;
        case 'status-confirm': umConfirmStatus(); break;
        case 'unlock':         umUnlock(userId || umDetailUserId); break;
        case 'tab':            umSelectTab(btn.dataset.tab, false); break;
        case 'activity-more':  umLoadActivity(false); break;
        case 'close-modal':    umCloseModal(btn.closest('.um-modal-backdrop').id); break;
        case 'toggle-password': umTogglePassword(btn.dataset.target, btn); break;
        case 'generate-password': umGeneratePassword(btn.dataset.target); break;
        case 'copy-password':  umCopyPassword(btn.dataset.target); break;
    }
}

function umOnInput(e) {
    const id = e.target.id;
    if (id === 'umSearch') {
        clearTimeout(umSearchTimer);
        umSearchTimer = setTimeout(function () { umPage = 1; umLoad(); }, UM_SEARCH_DELAY);
    } else if (id === 'umLookup') {
        umScheduleLookup();
    }
}

function umOnChange(e) {
    const id = e.target.id;
    if (id === 'umDepartment') {
        umFilterUnits(umEl('umDepartment'), umEl('umUnit'));
        umPage = 1;
        umLoad();
    } else if (id === 'umStatus' || id === 'umRole' || id === 'umUnit') {
        umPage = 1;
        umLoad();
    } else if (id === 'umCreateDept') {
        umFilterUnits(umEl('umCreateDept'), umEl('umCreateUnit'));
    } else if (id === 'umEditDept') {
        umFilterUnits(umEl('umEditDept'), umEl('umEditUnit'));
    } else if (id === 'umEmployee') {
        umOnEmployeePick(e.target);
    }
}

function umOnSubmit(e) {
    const form = e.target;
    if (form.id !== 'umCreateForm' && form.id !== 'umEditForm') return;
    e.preventDefault();
    if (form.id === 'umCreateForm') umSubmitCreate(form);
    else umSubmitEdit(form);
}

function umOnRootKeydown(e) {
    umTrapTab(e);

    // Arrow keys move through an open row menu
    if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && e.target.closest && e.target.closest('.um-menu')) {
        e.preventDefault();
        const items = Array.from(e.target.closest('.um-menu').querySelectorAll('button'));
        const i = items.indexOf(e.target);
        const next = e.key === 'ArrowDown' ? (i + 1) % items.length : (i - 1 + items.length) % items.length;
        items[next].focus();
        return;
    }

    // Arrow keys move between tabs
    if ((e.key === 'ArrowRight' || e.key === 'ArrowLeft') && e.target.classList && e.target.classList.contains('um-tab')) {
        const tabs = Array.from(document.querySelectorAll('#umRoot .um-tab'));
        const i = tabs.indexOf(e.target);
        const next = e.key === 'ArrowRight' ? (i + 1) % tabs.length : (i - 1 + tabs.length) % tabs.length;
        e.preventDefault();
        umSelectTab(tabs[next].dataset.tab, true);
    }
}

/* ── Filters ────────────────────────────────────── */

function umClearFilters() {
    ['umSearch', 'umStatus', 'umRole', 'umDepartment', 'umUnit'].forEach(function (id) {
        const el = umEl(id);
        if (el) el.value = '';
    });
    umFilterUnits(umEl('umDepartment'), umEl('umUnit'));
    umPage = 1;
    umLoad();
}

// Show only the units that belong to the chosen department
function umFilterUnits(deptSel, unitSel) {
    if (!deptSel || !unitSel) return;
    const dept = deptSel.value;
    Array.from(unitSel.options).forEach(function (o) {
        const d = o.dataset.department;
        const hide = !!(dept && d && d !== dept);
        o.hidden = hide;
        o.disabled = hide;
    });
    const sel = unitSel.selectedOptions[0];
    if (sel && sel.disabled) unitSel.value = '';
}

// Make sure the record's current value is selectable even if it is no longer in the active list
function umEnsureOption(sel, value, label, dept) {
    if (!sel || !value) return;
    const exists = Array.from(sel.options).some(function (o) { return o.value === String(value); });
    if (exists) return;
    const o = document.createElement('option');
    o.value = String(value);
    o.textContent = (label || 'Current value') + ' (inactive)';
    if (dept) o.dataset.department = String(dept);
    sel.appendChild(o);
}

/* ── Row menu ───────────────────────────────────── */

function umToggleMenu(btn) {
    const wrap = btn.closest('.um-menu-wrap');
    const menu = wrap.querySelector('.um-menu');
    const willOpen = menu.hidden;
    umCloseMenus(false);
    if (willOpen) {
        menu.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
        const first = menu.querySelector('button');
        if (first) first.focus();
    }
}

function umCloseMenus(returnFocus) {
    document.querySelectorAll('.um-menu:not([hidden])').forEach(function (m) {
        m.hidden = true;
        const b = m.parentElement.querySelector('[data-um-action="menu"]');
        if (b) {
            b.setAttribute('aria-expanded', 'false');
            if (returnFocus) b.focus();
        }
    });
}

/* ── Modal plumbing ─────────────────────────────── */

function umSyncBodyLock() {
    document.body.classList.toggle('um-modal-open', umModalStack.length > 0);
}

function umOpenModal(id) {
    const m = umEl(id);
    if (!m) return;
    if (umModalStack.indexOf(id) === -1) {
        umModalStack.push(id);
        umReturnFocus[id] = document.activeElement;
    }
    m.hidden = false;
    umSyncBodyLock();
    const dlg = m.querySelector('.um-modal');
    if (dlg) dlg.focus();
}

function umCloseModal(id) {
    const m = umEl(id);
    if (!m) return;
    m.hidden = true;
    umModalStack = umModalStack.filter(function (x) { return x !== id; });
    umSyncBodyLock();

    if (id === 'umDetailsModal') {
        umDetailUserId = null;
        umActivityReq++;
    }
    if (id === 'umStatusModal') umStatusTarget = null;

    const back = umReturnFocus[id];
    delete umReturnFocus[id];
    if (back && document.body.contains(back) && typeof back.focus === 'function') back.focus();
}

function umTrapTab(e) {
    if (e.key !== 'Tab' || !umModalStack.length) return;
    const top = umEl(umModalStack[umModalStack.length - 1]);
    if (!top) return;
    const items = Array.from(top.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )).filter(function (el) { return el.offsetParent !== null; });
    if (!items.length) return;

    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && (document.activeElement === first || document.activeElement === top.querySelector('.um-modal'))) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

function umToast(message) {
    const el = umEl('umToast');
    if (!el) return;
    el.textContent = message;
    el.hidden = false;
    clearTimeout(umToastTimer);
    umToastTimer = setTimeout(function () { el.hidden = true; }, 4500);
}

function umSetNotice(id, text, isError) {
    const el = umEl(id);
    if (!el) return;
    el.textContent = text;
    el.classList.toggle('um-notice-text--error', !!isError);
    el.hidden = !text;
}

function umSetBusy(form, busy, busyLabel) {
    umBusy = busy;
    const submit = form.querySelector('button[type="submit"]');
    if (!submit) return;
    submit.disabled = busy;
    submit.textContent = busy ? busyLabel : (submit.dataset.label || submit.textContent);
}

function umClearErrors(form, msgId) {
    form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
    umSet(msgId, '');
}

function umShowFormErrors(form, msgId, err) {
    const errors = err.errors || {};
    const lines = [err.message];
    Object.keys(errors).forEach(function (key) {
        const field = form.querySelector('[name="' + key + '"]');
        if (field) field.setAttribute('aria-invalid', 'true');
        if (errors[key] && lines.indexOf(errors[key]) === -1 && errors[key] !== err.message) lines.push(errors[key]);
    });
    umSet(msgId, lines.join(' '));
}

/* ── Details modal ──────────────────────────────── */

async function umOpenDetails(userId) {
    const id = parseInt(userId, 10);
    if (!id) return;

    umDetailUserId = id;
    umDetailData = null;
    umActivityLoaded = false;
    umActivityOffset = 0;
    umActivityTotal = 0;
    umActivityReq++;

    umEl('umDetailsContent').hidden = true;
    umSetNotice('umDetailsNotice', 'Loading details…');
    umEl('umActivityList').innerHTML = '';
    umEl('umActivityMore').hidden = true;
    umSetNotice('umActivityNotice', '');
    umSet('umDetailsTitle', 'Employee details');
    umSet('umDetailSubline', '');
    umEl('umDetailStatus').className = 'um-status';
    umSet('umDetailStatus', '');
    umSelectTab('overview', false);
    umOpenModal('umDetailsModal');

    await umReloadDetails();
}

async function umReloadDetails() {
    const id = umDetailUserId;
    if (!id) return;
    try {
        const data = await umRequest('details', { user_id: id });
        if (id !== umDetailUserId) return;
        umDetailData = data;
        umRenderDetails(data);
        umSetNotice('umDetailsNotice', '');
        umEl('umDetailsContent').hidden = false;

        umActivityLoaded = false;
        if (umEl('umTabBtnActivity').getAttribute('aria-selected') === 'true') umLoadActivity(true);
    } catch (err) {
        if (id !== umDetailUserId) return;
        umSetNotice('umDetailsNotice', err.message || 'Unable to load details.', true);
    }
}

function umRenderDetails(d) {
    const name = umFullName(d);
    const off = umIsDeactivated(d);

    const av = umEl('umDetailAvatar');
    av.textContent = umInitials(d);
    av.className = 'um-avatar um-avatar-large' + umAvatarClass(d);

    umSet('umDetailsTitle', name);
    umSet('umDetailSubline', umNA(d.employee_code, 'No employee ID') + ' · ' + umNA(d.role_name, 'No role'));
    const st = umEl('umDetailStatus');
    st.className = 'um-status ' + (off ? 'is-inactive' : 'is-active');
    st.textContent = off ? 'Deactivated' : 'Active';

    umSet('umDetailLastLogin', umFormatWhen(d.last_login, 'No login recorded'));
    umSet('umDetailCreated', umFormatDate(d.account_created_at));
    umSet('umDetailActivityCount', d.activity_total + (Number(d.activity_total) === 1 ? ' event' : ' events'));

    umSet('umInfoName', name);
    umSet('umInfoId', umNA(d.employee_code));
    umSet('umInfoEmail', umNA(d.email));
    umSet('umInfoContact', umNA(d.mobile_no || d.phone_no));
    umSet('umInfoDepartment', umNA(d.department_name));
    umSet('umInfoUnit', umNA(d.unit_name));
    umSet('umInfoPosition', umNA(d.position_name));
    umSet('umInfoLastActivity', umFormatWhen(d.last_activity_at, 'No activity recorded'));

    umSet('umAccessRole', umNA(d.role_name, 'No role assigned'));
    umSet('umAccessStatus', off ? 'Deactivated' : 'Active');
    umSet('umAccessDepartment', umNA(d.department_name));
    umSet('umAccessUnit', umNA(d.unit_name));
    umSet('umAccessPwChanged', umFormatWhen(d.password_changed_at, 'No change recorded'));
    umSet('umAccessFailed', String(d.failed_login_attempts || 0));
    umSet('umAccessLock', umIsLocked(d) ? 'Locked until ' + umFormatWhen(d.locked_until) : 'Not locked');

    umEl('umAccessDeactWrap').hidden = !off;
    umSet('umAccessDeactAt', umFormatWhen(d.deactivated_at));
    umSet('umAccessDeactBy', umNA(d.deactivated_by_name));
    umSet('umAccessDeactReason', umNA(d.deactivation_reason, 'No reason given'));

    umEl('umDetailUnlockBtn').hidden = !umIsLocked(d);

    const sb = umEl('umDetailStatusBtn');
    sb.textContent = off ? 'Reactivate' : 'Deactivate';
    sb.dataset.mode = off ? 'reactivate' : 'deactivate';
    sb.dataset.user = String(d.user_id);
}

function umSelectTab(name, focus) {
    document.querySelectorAll('#umDetailsModal .um-tab').forEach(function (t) {
        const on = t.dataset.tab === name;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        if (on && focus) t.focus();
    });
    ['overview', 'activity', 'access'].forEach(function (n) {
        const panel = umEl('umTab' + n.charAt(0).toUpperCase() + n.slice(1));
        if (panel) panel.hidden = n !== name;
    });
    if (name === 'activity' && !umActivityLoaded && umDetailUserId) umLoadActivity(true);
}

/* ── Activity tab ───────────────────────────────── */

function umActivityIcon(action) {
    const a = String(action || '').toLowerCase();
    if (a.indexOf('login') !== -1 || a.indexOf('sign') !== -1) return '↪';
    if (a.indexOf('deactivat') !== -1) return '⊘';
    if (a.indexOf('reactivat') !== -1) return '↺';
    if (a.indexOf('creat') !== -1 || a.indexOf('submit') !== -1) return '＋';
    if (a.indexOf('approv') !== -1 || a.indexOf('review') !== -1) return '✓';
    if (a.indexOf('updat') !== -1 || a.indexOf('chang') !== -1 || a.indexOf('reset') !== -1) return '✎';
    return '•';
}

function umActivityHtml(a, targetUserId) {
    const detail = [a.module, a.description].filter(function (x) { return x && String(x).trim() !== ''; }).join(' · ');
    const byOther = a.actor_name && parseInt(a.actor_user_id, 10) !== targetUserId;
    const when = umParseDate(a.created_at);
    return '' +
    '<li class="um-activity-item">' +
        '<span class="um-activity-icon" aria-hidden="true">' + umEsc(umActivityIcon(a.action)) + '</span>' +
        '<div>' +
            '<strong>' + umEsc(umNA(a.action, 'Activity')) + '</strong>' +
            '<p>' + umEsc(umNA(detail, 'No details recorded')) + (a.record_id ? ' · Record #' + umEsc(a.record_id) : '') + '</p>' +
            (byOther ? '<p>By ' + umEsc(a.actor_name) + '</p>' : '') +
            '<time' + (when ? ' datetime="' + umEsc(when.toISOString()) + '"' : '') + '>' + umEsc(umFormatWhen(a.created_at)) + '</time>' +
        '</div>' +
    '</li>';
}

async function umLoadActivity(reset) {
    const id = umDetailUserId;
    if (!id) return;

    if (reset) {
        umActivityOffset = 0;
        umEl('umActivityList').innerHTML = '';
    }
    const reqId = ++umActivityReq;
    umSetNotice('umActivityNotice', reset ? 'Loading activity…' : 'Loading more…');
    umEl('umActivityMore').hidden = true;

    try {
        const data = await umRequest('activity', { user_id: id, offset: umActivityOffset, limit: UM_ACTIVITY_LIMIT });
        if (reqId !== umActivityReq || id !== umDetailUserId) return;

        umEl('umActivityList').insertAdjacentHTML('beforeend', data.rows.map(function (a) { return umActivityHtml(a, id); }).join(''));
        umActivityOffset += data.rows.length;
        umActivityTotal = data.total;
        umActivityLoaded = true;

        umSetNotice('umActivityNotice', data.total === 0 ? 'No activity has been recorded for this account yet.' : '');
        umEl('umActivityMore').hidden = umActivityOffset >= umActivityTotal;
    } catch (err) {
        if (reqId !== umActivityReq || id !== umDetailUserId) return;
        umSetNotice('umActivityNotice', err.message || 'Unable to load activity.', true);
        umEl('umActivityMore').hidden = umActivityOffset >= umActivityTotal && umActivityLoaded;
        if (!umActivityLoaded) umEl('umActivityMore').hidden = true;
    }
}

/* ── Create modal ───────────────────────────────── */

function umOpenCreate() {
    const form = umEl('umCreateForm');
    form.reset();
    umClearErrors(form, 'umCreateMsg');
    umEl('umEmployee').innerHTML = '';
    umFilterUnits(umEl('umCreateDept'), umEl('umCreateUnit'));
    umSet('umLookupHint', 'Type at least ' + UM_MIN_LOOKUP + ' characters to search. Employees who already have an account can\u2019t be selected.');
    umResetPasswordToggles();
    umOpenModal('umCreateModal');
    umEl('umLookup').focus();
}

function umScheduleLookup() {
    clearTimeout(umLookupTimer);
    umLookupTimer = setTimeout(umLookup, UM_LOOKUP_DELAY);
}

async function umLookup() {
    const input = umEl('umLookup');
    const list = umEl('umEmployee');
    if (!input || !list) return;

    const q = input.value.trim();
    const reqId = ++umLookupId;

    if (q.length < UM_MIN_LOOKUP) {
        list.innerHTML = '';
        umSet('umLookupHint', 'Type at least ' + UM_MIN_LOOKUP + ' characters to search.');
        return;
    }

    umSet('umLookupHint', 'Searching…');
    try {
        const data = await umRequest('lookup_employees', { q: q });
        if (reqId !== umLookupId) return;

        list.innerHTML = data.rows.map(function (r) {
            let note = '';
            let disabled = false;
            if (Number(r.has_account) === 1) { note = ' — already has an account'; disabled = true; }
            else if (Number(r.eligible) !== 1) { note = ' — not eligible (' + r.employment_status + ')'; disabled = true; }

            return '<option value="' + umEsc(r.employee_id) + '"' +
                ' data-role="' + umEsc(r.role_id || '') + '"' +
                ' data-dept="' + umEsc(r.department_id || '') + '"' +
                ' data-unit="' + umEsc(r.unit_id || '') + '"' +
                ' data-email="' + umEsc(r.email || '') + '"' +
                (disabled ? ' disabled' : '') + '>' +
                umEsc(umNA(r.full_name, 'Unnamed') + ' (' + umNA(r.employee_code, 'no ID') + ')' +
                    (r.department_name ? ' · ' + r.department_name : '') + note) +
                '</option>';
        }).join('');

        umSet('umLookupHint', data.rows.length
            ? data.rows.length + ' match' + (data.rows.length === 1 ? '' : 'es') + '. Select an employee to continue.'
            : 'No employees found.');
    } catch (err) {
        if (reqId !== umLookupId) return;
        umSet('umLookupHint', err.message || 'Search failed.');
    }
}

function umOnEmployeePick(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt) return;
    const d = opt.dataset;

    umEl('umCreateRole').value = d.role || '';
    umEl('umCreateDept').value = d.dept || '';
    umFilterUnits(umEl('umCreateDept'), umEl('umCreateUnit'));
    umEl('umCreateUnit').value = d.unit || '';
    umEl('umCreateEmail').value = d.email || '';
    umSet('umLookupHint', 'Selected: ' + opt.textContent);
}

async function umSubmitCreate(form) {
    if (umBusy) return;
    umClearErrors(form, 'umCreateMsg');

    const data = Object.fromEntries(new FormData(form).entries());
    if (!data.employee_id) {
        umSet('umCreateMsg', 'Select an employee from the search results.');
        umEl('umLookup').focus();
        return;
    }

    umSetBusy(form, true, 'Creating…');
    try {
        const res = await umRequest('create', data, 'POST');
        umCloseModal('umCreateModal');
        umToast('Account created for ' + res.name + '.');
        await umLoad();
    } catch (err) {
        umShowFormErrors(form, 'umCreateMsg', err);
        if (err.status === 409) umLookup(); // refresh so the employee shows as already having an account
    } finally {
        umSetBusy(form, false);
    }
}

/* ── Edit modal ─────────────────────────────────── */

async function umOpenEdit(userId, focusId) {
    const id = parseInt(userId, 10);
    if (!id) return;

    const form = umEl('umEditForm');
    form.reset();
    umClearErrors(form, 'umEditMsg');
    umEl('umEditFields').disabled = true;
    umEl('umEditSubmit').disabled = true;
    umSet('umEditSubline', 'Loading…');
    umResetPasswordToggles();
    umOpenModal('umEditModal');

    try {
        const d = await umRequest('details', { user_id: id });
        if (umModalStack.indexOf('umEditModal') === -1) return; // closed while loading

        umEl('umEditUserId').value = String(d.user_id);
        umSet('umEditSubline', umFullName(d) + ' · ' + umNA(d.employee_code, 'No employee ID'));

        umEnsureOption(umEl('umEditRole'), d.role_id, d.role_name);
        umEnsureOption(umEl('umEditDept'), d.department_id, d.department_name);
        umEnsureOption(umEl('umEditUnit'), d.unit_id, d.unit_name, d.department_id);

        umEl('umEditRole').value = d.role_id || '';
        umEl('umEditDept').value = d.department_id || '';
        umFilterUnits(umEl('umEditDept'), umEl('umEditUnit'));
        umEl('umEditUnit').value = d.unit_id || '';
        umEl('umEditEmail').value = d.email || '';

        umEl('umEditFields').disabled = false;
        umEl('umEditSubmit').disabled = false;

        const target = umEl(focusId || 'umEditRole');
        if (target) target.focus();
    } catch (err) {
        umSet('umEditSubline', '');
        umSet('umEditMsg', err.message || 'Unable to load this account.');
    }
}

async function umSubmitEdit(form) {
    if (umBusy) return;
    umClearErrors(form, 'umEditMsg');

    const data = Object.fromEntries(new FormData(form).entries());
    umSetBusy(form, true, 'Saving…');
    try {
        const res = await umRequest('update', data, 'POST');
        umCloseModal('umEditModal');
        umToast(res.changed ? 'Changes saved for ' + res.name + '.' : 'No changes to save.');
        await umAfterChange();
    } catch (err) {
        umShowFormErrors(form, 'umEditMsg', err);
    } finally {
        umSetBusy(form, false);
    }
}

/* ── Deactivate / reactivate / unlock ───────────── */

function umOpenStatusFromButton(btn) {
    const userId = btn.dataset.user || umDetailUserId;
    let mode = btn.dataset.mode;
    if (!mode && umDetailData) mode = umIsDeactivated(umDetailData) ? 'reactivate' : 'deactivate';
    umOpenStatus(userId, mode === 'reactivate' ? 'reactivate' : 'deactivate');
}

function umOpenStatus(userId, mode) {
    const id = parseInt(userId, 10);
    const row = umFindUser(id);
    if (!id || !row) return;

    const name = umFullName(row);
    umStatusTarget = { userId: id, mode: mode };
    const deactivate = mode === 'deactivate';

    umSet('umStatusTitle', deactivate ? 'Deactivate account' : 'Reactivate account');
    umSet('umStatusText', deactivate
        ? name + ' will no longer be able to sign in. Their employee record and activity history are kept, and you can reactivate the account at any time.'
        : name + ' will be able to sign in again with their current password.');
    umEl('umStatusReasonWrap').hidden = !deactivate;
    umEl('umStatusReason').value = '';
    umSet('umStatusMsg', '');

    const confirm = umEl('umStatusConfirm');
    confirm.textContent = deactivate ? 'Deactivate account' : 'Reactivate account';
    confirm.dataset.label = confirm.textContent;
    confirm.disabled = false;
    confirm.className = 'um-btn ' + (deactivate ? 'um-btn-danger' : 'um-btn-primary');

    umOpenModal('umStatusModal');
    confirm.focus();
}

async function umConfirmStatus() {
    if (umBusy || !umStatusTarget) return;
    const target = umStatusTarget;
    const deactivate = target.mode === 'deactivate';
    const confirm = umEl('umStatusConfirm');

    umBusy = true;
    confirm.disabled = true;
    confirm.textContent = deactivate ? 'Deactivating…' : 'Reactivating…';
    umSet('umStatusMsg', '');

    try {
        const params = { user_id: target.userId };
        if (deactivate) params.reason = umEl('umStatusReason').value.trim();
        const res = await umRequest(deactivate ? 'deactivate' : 'reactivate', params, 'POST');
        umCloseModal('umStatusModal');
        umToast(res.name + (deactivate ? '\u2019s account was deactivated.' : '\u2019s account was reactivated.'));
        await umAfterChange();
    } catch (err) {
        umSet('umStatusMsg', err.message);
        confirm.disabled = false;
        confirm.textContent = confirm.dataset.label || 'Confirm';
    } finally {
        umBusy = false;
    }
}

async function umUnlock(userId) {
    if (umBusy) return;
    const id = parseInt(userId, 10);
    if (!id) return;
    umBusy = true;
    try {
        const res = await umRequest('unlock', { user_id: id }, 'POST');
        umToast(res.name + '\u2019s account was unlocked.');
        await umAfterChange();
    } catch (err) {
        umToast(err.message || 'Unable to unlock this account.');
    } finally {
        umBusy = false;
    }
}

// Refresh the directory, stats and (if open) the details modal
async function umAfterChange() {
    const reload = umDetailUserId ? umReloadDetails() : Promise.resolve();
    await Promise.all([umLoad(), reload]);
}

/* ── Password helpers ───────────────────────────── */

function umGeneratePassword(targetId) {
    const input = umEl(targetId);
    if (!input) return;

    const sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%*?'];
    const all = sets.join('');
    const len = 14;
    const rand = function (max) {
        const buf = new Uint32Array(1);
        const limit = Math.floor(0xFFFFFFFF / max) * max;
        do { crypto.getRandomValues(buf); } while (buf[0] >= limit); // avoid modulo bias
        return buf[0] % max;
    };

    // One character from each set first, so the result always mixes cases, digits and symbols
    const chars = sets.map(function (s) { return s.charAt(rand(s.length)); });
    while (chars.length < len) chars.push(all.charAt(rand(all.length)));
    for (let i = chars.length - 1; i > 0; i--) {
        const j = rand(i + 1);
        const tmp = chars[i]; chars[i] = chars[j]; chars[j] = tmp;
    }

    input.value = chars.join('');
    input.type = 'text';
    const toggle = document.querySelector('[data-um-action="toggle-password"][data-target="' + targetId + '"]');
    if (toggle) toggle.textContent = 'Hide';
    input.removeAttribute('aria-invalid');
}

// Password fields always start masked, with their Show/Hide buttons in sync
function umResetPasswordToggles() {
    ['umCreatePassword', 'umEditPassword'].forEach(function (id) {
        const input = umEl(id);
        if (input) input.type = 'password';
        const toggle = document.querySelector('[data-um-action="toggle-password"][data-target="' + id + '"]');
        if (toggle) toggle.textContent = 'Show';
    });
}

function umTogglePassword(targetId, btn) {
    const input = umEl(targetId);
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Hide' : 'Show';
}

async function umCopyPassword(targetId) {
    const input = umEl(targetId);
    if (!input || !input.value) {
        umToast('There is no password to copy yet.');
        return;
    }
    try {
        await navigator.clipboard.writeText(input.value);
        umToast('Password copied.');
    } catch (err) {
        umToast('Copy failed. Select the password and copy it manually.');
    }
}

/* ── Export (all rows matching current filters) ─── */

function umCsvCell(v) {
    let s = String(v === null || v === undefined ? '' : v);
    if (/^[=+\-@\t\r]/.test(s)) s = "'" + s; // stop spreadsheet formula injection
    return '"' + s.replace(/"/g, '""') + '"';
}

async function umExportCsv() {
    const btn = umEl('umExportBtn');
    if (!btn || btn.disabled) return;

    const label = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Exporting…';

    try {
        const data = await umRequest('export', umFilterParams());
        if (!data.rows.length) {
            umToast('There are no users to export.');
            return;
        }

        const lines = [['Employee ID', 'Name', 'Email', 'Role', 'Department', 'Unit', 'Position', 'Account status', 'Last login']
            .map(umCsvCell).join(',')];

        data.rows.forEach(function (r) {
            lines.push([
                r.employee_code, umFullName(r), r.email, r.role_name, r.department_name,
                r.unit_name, r.position_name, r.account_status, r.last_login
            ].map(umCsvCell).join(','));
        });

        const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'user_accounts_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    } catch (err) {
        umToast(err.message || 'Export failed.');
    } finally {
        btn.disabled = false;
        btn.textContent = label;
    }
}

/* ── Lifecycle ──────────────────────────────────── */

document.addEventListener('DOMContentLoaded', umInit);
document.addEventListener('page:loaded', umInit);

// Safety net: the router may insert the page after (or without) firing the events
// above, so also watch for #umRoot appearing. umInit is safe to call repeatedly —
// the initial load is guarded by a flag on the root element.
function umWatchDom() {
    if (window.__umObserver || !document.body) return;
    window.__umObserver = new MutationObserver(function () {
        const root = document.getElementById('umRoot');
        if (root && !root.dataset.umLoaded) umInit();
    });
    window.__umObserver.observe(document.body, { childList: true, subtree: true });
}

umWatchDom();
document.addEventListener('DOMContentLoaded', umWatchDom);

if (document.readyState !== 'loading') {
    umInit();
}