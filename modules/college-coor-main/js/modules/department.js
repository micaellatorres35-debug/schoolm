/* ============================================================
   School Directress — Department & Unit Management (AJAX)
   Prefix: dm / DM_
   ============================================================ */

const PAGE_KEY = 'department-management';

const DM_ENDPOINT = 'controllers/DepartmentController.php';
const DM_SEARCH_DELAY = 300;
const DM_REQUEST_TIMEOUT = 15000;
const DM_SKELETON_CARDS = 6;

let dmDepts = [];
let dmMatched = {};
let dmView = 'overview';          // 'overview' | 'detail'
let dmDeptId = null;
let dmDeptData = null;
let dmUnits = [];
let dmRequestId = 0;
let dmDetailReq = 0;
let dmBusy = false;
let dmSearchTimer = null;
let dmToastTimer = null;
let dmMouseDownTarget = null;
let dmConfirm = null;             // { kind: 'dept'|'unit', id, status }
let dmUnitDetailsId = null;
let dmModalStack = [];
let dmReturnFocus = {};
let dmLastOpened = null;          // department id to refocus when coming back

/* ── Setup ──────────────────────────────────────── */

function dmEl(id) {
    return document.getElementById(id);
}

function dmBindOnce(el, flag, evt, handler) {
    if (!el || el.dataset[flag]) return;
    el.dataset[flag] = '1';
    el.addEventListener(evt, handler);
}

function dmCanWrite() {
    const root = dmEl('dmRoot');
    return !!root && root.dataset.canWrite === '1';
}

function dmInit() {
    const root = dmEl('dmRoot');
    if (!root) return;

    dmBindOnce(root, 'dmBoundClick', 'click', dmOnClick);
    dmBindOnce(root, 'dmBoundInput', 'input', dmOnInput);
    dmBindOnce(root, 'dmBoundChange', 'change', dmOnChange);
    dmBindOnce(root, 'dmBoundKey', 'keydown', dmTrapTab);
    dmBindOnce(root, 'dmBoundSubmit', 'submit', dmOnSubmit);
    dmBindOnce(root, 'dmBoundDown', 'mousedown', function (e) { dmMouseDownTarget = e.target; });

    dmBindGlobalsOnce();

    // Initial load once per rendered page (init fires on both DOMContentLoaded and page:loaded)
    if (!root.dataset.dmLoaded) {
        root.dataset.dmLoaded = '1';
        dmDepts = [];
        dmMatched = {};
        dmView = 'overview';
        dmDeptId = null;
        dmDeptData = null;
        dmUnits = [];
        dmBusy = false;
        dmConfirm = null;
        dmModalStack = [];
        dmReturnFocus = {};
        dmSyncBodyLock();
        dmLoadList();
    }
}

function dmBindGlobalsOnce() {
    if (window.__dmGlobalsBound) return;
    window.__dmGlobalsBound = true;

    document.addEventListener('click', function (e) {
        if (!e.target.closest || e.target.closest('.dm-menu-wrap')) return;
        dmCloseMenus(false);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !dmEl('dmRoot')) return;
        if (document.querySelector('.dm-menu:not([hidden])')) {
            dmCloseMenus(true);
            return;
        }
        if (dmModalStack.length) {
            e.preventDefault();
            dmCloseModal(dmModalStack[dmModalStack.length - 1]);
        }
    });
}

/* ── Helpers ────────────────────────────────────── */

function dmEsc(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function dmSet(id, text) {
    const el = dmEl(id);
    if (el) el.textContent = text;
}

function dmInitials(name) {
    const words = String(name || '').trim().split(/\s+/).filter(Boolean);
    return words.slice(0, 2).map(function (w) { return w.charAt(0).toUpperCase(); }).join('') || '?';
}

function dmPlural(n, word) {
    return n + ' ' + word + (Number(n) === 1 ? '' : 's');
}

function dmIsInactive(r) {
    return r.status === 'Inactive';
}

function dmStatusBadge(r) {
    return '<span class="dm-status ' + (dmIsInactive(r) ? 'is-inactive' : 'is-active') + '">' +
        (dmIsInactive(r) ? 'Inactive' : 'Active') + '</span>';
}

function dmFindDept(id) {
    id = parseInt(id, 10);
    if (dmDeptData && parseInt(dmDeptData.department_id, 10) === id) return dmDeptData;
    return dmDepts.filter(function (d) { return parseInt(d.department_id, 10) === id; })[0] || null;
}

function dmFindUnit(id) {
    id = parseInt(id, 10);
    return dmUnits.filter(function (u) { return parseInt(u.unit_id, 10) === id; })[0] || null;
}

function dmFilterParams() {
    const s = dmEl('dmSearch');
    const st = dmEl('dmStatus');
    return { search: s ? s.value.trim() : '', status: st ? st.value : '' };
}

function dmFiltersActive() {
    const f = dmFilterParams();
    return !!(f.search || f.status);
}

/* ── AJAX ───────────────────────────────────────── */

async function dmRequest(action, params, method) {
    method = method || 'GET';
    const root = dmEl('dmRoot');
    const controller = new AbortController();
    const timer = setTimeout(function () { controller.abort(); }, DM_REQUEST_TIMEOUT);

    // X-Requested-With makes auth/session.php answer with JSON instead of a redirect
    const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    const opts = { method: method, credentials: 'same-origin', headers: headers, signal: controller.signal };
    let url = DM_ENDPOINT + '?action=' + encodeURIComponent(action);

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

/* ── Overview ───────────────────────────────────── */

function dmShowListState(text, isError, withRetry) {
    const el = dmEl('dmListState');
    el.innerHTML = dmEsc(text) + (withRetry ? ' <button type="button" class="dm-link-btn" data-dm-action="retry">Try again</button>' : '');
    el.classList.toggle('dm-state--error', !!isError);
    el.hidden = false;
}

function dmShowSkeleton() {
    const grid = dmEl('dmGrid');
    dmEl('dmListState').hidden = true;
    grid.setAttribute('aria-busy', 'true');
    let html = '';
    for (let i = 0; i < DM_SKELETON_CARDS; i++) {
        html += '<div class="dm-card dm-skeleton" aria-hidden="true"><div class="dm-sk-line dm-sk-w60"></div>' +
                '<div class="dm-sk-line"></div><div class="dm-sk-line dm-sk-w80"></div><div class="dm-sk-line dm-sk-w40"></div></div>';
    }
    grid.innerHTML = html;
}

async function dmLoadList() {
    if (!dmEl('dmRoot')) return;
    const requestId = ++dmRequestId; // ignore out-of-order responses
    dmShowSkeleton();

    try {
        const data = await dmRequest('list', dmFilterParams());
        if (requestId !== dmRequestId) return;
        dmDepts = data.departments;
        dmMatched = data.matched || {};
        dmRenderStats(data.stats);
        dmRenderGrid();
    } catch (err) {
        if (requestId !== dmRequestId) return;
        dmEl('dmGrid').innerHTML = '';
        dmEl('dmGrid').removeAttribute('aria-busy');
        dmShowListState(err.message || 'Unable to load departments.', true, true);
    }
}

function dmRenderStats(s) {
    if (!s) return;
    dmSet('dmStatDepts', String(s.departments));
    dmSet('dmStatDeptsNote', s.active_departments + ' active');
    dmSet('dmStatUnits', String(s.units));
    dmSet('dmStatUnitsNote', s.active_units + ' active');
    dmSet('dmStatEmployees', String(s.employees));
}

function dmMenuHtml(kind, r, name) {
    if (!dmCanWrite()) {
        return kind === 'dept'
            ? '<div class="dm-menu-wrap"><button type="button" class="dm-icon-btn" data-dm-action="menu" aria-haspopup="true" aria-expanded="false" aria-label="More actions for ' + dmEsc(name) + '">⋯</button>' +
              '<div class="dm-menu" role="menu" hidden><button type="button" role="menuitem" data-dm-action="open-dept" data-id="' + dmEsc(r.department_id) + '">View details</button></div></div>'
            : '<div class="dm-menu-wrap"><button type="button" class="dm-icon-btn" data-dm-action="menu" aria-haspopup="true" aria-expanded="false" aria-label="More actions for ' + dmEsc(name) + '">⋯</button>' +
              '<div class="dm-menu" role="menu" hidden><button type="button" role="menuitem" data-dm-action="view-unit" data-id="' + dmEsc(r.unit_id) + '">View details</button></div></div>';
    }
    const off = dmIsInactive(r);
    const id = kind === 'dept' ? r.department_id : r.unit_id;
    return '' +
    '<div class="dm-menu-wrap">' +
        '<button type="button" class="dm-icon-btn" data-dm-action="menu" aria-haspopup="true" aria-expanded="false" aria-label="More actions for ' + dmEsc(name) + '">⋯</button>' +
        '<div class="dm-menu" role="menu" hidden>' +
            '<button type="button" role="menuitem" data-dm-action="' + (kind === 'dept' ? 'open-dept' : 'view-unit') + '" data-id="' + dmEsc(id) + '">View details</button>' +
            '<button type="button" role="menuitem" data-dm-action="' + (kind === 'dept' ? 'edit-dept' : 'edit-unit') + '" data-id="' + dmEsc(id) + '">Edit ' + (kind === 'dept' ? 'department' : 'unit') + '</button>' +
            '<button type="button" role="menuitem" class="dm-menu-danger" data-dm-action="' + (kind === 'dept' ? 'toggle-dept' : 'toggle-unit') + '" data-id="' + dmEsc(id) + '" data-status="' + (off ? 'Active' : 'Inactive') + '">' +
                (off ? 'Reactivate' : 'Deactivate') + '</button>' +
        '</div>' +
    '</div>';
}

function dmCardHtml(d) {
    const matched = dmMatched[d.department_id] || dmMatched[String(d.department_id)] || [];
    return '' +
    '<article class="dm-card' + (dmIsInactive(d) ? ' dm-card--inactive' : '') + '" data-dept-id="' + dmEsc(d.department_id) + '">' +
        '<div class="dm-card-head">' +
            '<div class="dm-avatar" aria-hidden="true">' + dmEsc(dmInitials(d.department_name)) + '</div>' +
            '<div class="dm-card-titlewrap">' +
                '<button type="button" class="dm-card-title" data-dm-action="open-dept" data-id="' + dmEsc(d.department_id) + '">' + dmEsc(d.department_name) + '</button>' +
                dmStatusBadge(d) +
            '</div>' +
            dmMenuHtml('dept', d, d.department_name) +
        '</div>' +
        '<p class="dm-card-desc">' + (d.description ? dmEsc(d.description) : '<span class="dm-muted">No description</span>') + '</p>' +
        (matched.length ? '<p class="dm-card-match">Matching units: ' + matched.map(dmEsc).join(', ') + '</p>' : '') +
        '<dl class="dm-card-stats">' +
            '<div><dt>Units</dt><dd>' + dmEsc(d.unit_count) + '</dd></div>' +
            '<div><dt>Employees</dt><dd>' + dmEsc(d.employee_count) + '</dd></div>' +
        '</dl>' +
        '<div class="dm-card-foot">' +
            '<span class="dm-muted">' + (d.head_name ? 'Head: ' + dmEsc(d.head_name) : 'No head assigned') + '</span>' +
            '<span class="dm-open-hint" aria-hidden="true">View units ›</span>' +
        '</div>' +
    '</article>';
}

function dmRenderGrid() {
    const grid = dmEl('dmGrid');
    grid.removeAttribute('aria-busy');

    if (!dmDepts.length) {
        grid.innerHTML = '';
        dmShowListState(dmFiltersActive()
            ? 'No departments or units match your search or filters.'
            : (dmCanWrite() ? 'No departments yet. Use “Add department” to create the first one.' : 'No departments found.'));
        return;
    }
    dmEl('dmListState').hidden = true;
    grid.innerHTML = dmDepts.map(dmCardHtml).join('');
}

/* ── Department detail ──────────────────────────── */

function dmSetView(view) {
    dmView = view;
    dmEl('dmOverview').hidden = view !== 'overview';
    dmEl('dmDetail').hidden = view !== 'detail';
}

async function dmOpenDept(id) {
    id = parseInt(id, 10);
    if (!id) return;
    dmDeptId = id;
    dmLastOpened = id;
    dmDeptData = null;
    dmUnits = [];

    dmSetView('detail');
    dmEl('dmDetailContent').hidden = true;
    const st = dmEl('dmDetailState');
    st.textContent = 'Loading department…';
    st.classList.remove('dm-state--error');
    st.hidden = false;
    dmEl('dmBackBtn').focus();
    dmEl('dmRoot').scrollIntoView({ block: 'start' });

    await dmLoadDetail();
}

async function dmLoadDetail() {
    const id = dmDeptId;
    if (!id) return;
    const req = ++dmDetailReq;
    try {
        const data = await dmRequest('department', { id: id });
        if (req !== dmDetailReq || id !== dmDeptId) return;
        dmDeptData = data.department;
        dmUnits = data.units;
        dmRenderDetail();
    } catch (err) {
        if (req !== dmDetailReq || id !== dmDeptId) return;
        const st = dmEl('dmDetailState');
        st.innerHTML = dmEsc(err.message || 'Unable to load this department.') +
            ' <button type="button" class="dm-link-btn" data-dm-action="retry-detail">Try again</button>';
        st.classList.add('dm-state--error');
        st.hidden = false;
        dmEl('dmDetailContent').hidden = true;
    }
}

function dmRenderDetail() {
    const d = dmDeptData;
    dmEl('dmDetailState').hidden = true;
    dmEl('dmDetailContent').hidden = false;

    dmSet('dmDetailAvatar', dmInitials(d.department_name));
    dmSet('dmDetailName', d.department_name);
    const badge = dmEl('dmDetailStatus');
    badge.className = 'dm-status ' + (dmIsInactive(d) ? 'is-inactive' : 'is-active');
    badge.textContent = dmIsInactive(d) ? 'Inactive' : 'Active';
    dmSet('dmDetailDesc', d.description || 'No description provided.');
    dmSet('dmDetailHead', d.head_name ? 'Department head: ' + d.head_name : 'No department head assigned.');
    dmSet('dmDetailUnits', String(d.unit_count));
    dmSet('dmDetailEmployees', String(d.employee_count));

    const addBtn = dmEl('dmDetailAddUnitBtn');
    if (addBtn) addBtn.disabled = dmIsInactive(d);
    if (addBtn) addBtn.title = dmIsInactive(d) ? 'Reactivate this department before adding units.' : '';

    const grid = dmEl('dmUnitGrid');
    const st = dmEl('dmUnitsState');
    if (!dmUnits.length) {
        grid.innerHTML = '';
        st.textContent = dmCanWrite() ? 'This department has no units yet. Use “Add unit” to create one.' : 'This department has no units yet.';
        st.hidden = false;
    } else {
        st.hidden = true;
        grid.innerHTML = dmUnits.map(dmUnitCardHtml).join('');
    }
}

function dmUnitCardHtml(u) {
    return '' +
    '<article class="dm-card dm-unit-card' + (dmIsInactive(u) ? ' dm-card--inactive' : '') + '" data-unit-id="' + dmEsc(u.unit_id) + '">' +
        '<div class="dm-card-head">' +
            '<div class="dm-card-titlewrap">' +
                '<button type="button" class="dm-card-title" data-dm-action="view-unit" data-id="' + dmEsc(u.unit_id) + '">' + dmEsc(u.unit_name) + '</button>' +
                dmStatusBadge(u) +
            '</div>' +
            dmMenuHtml('unit', u, u.unit_name) +
        '</div>' +
        '<dl class="dm-card-stats">' +
            '<div><dt>Employees</dt><dd>' + dmEsc(u.employee_count) + '</dd></div>' +
            '<div><dt>Unit head</dt><dd class="dm-dd-text">' + (u.head_name ? dmEsc(u.head_name) : '<span class="dm-muted">None</span>') + '</dd></div>' +
        '</dl>' +
    '</article>';
}

function dmBack() {
    dmDeptId = null;
    dmDeptData = null;
    dmUnits = [];
    dmDetailReq++;
    dmSetView('overview');
    // Search and status filter stay as they were; just bring the list up to date
    dmLoadList().then(function () {
        const btn = dmLastOpened && document.querySelector('.dm-card[data-dept-id="' + dmLastOpened + '"] .dm-card-title');
        if (btn) btn.focus();
    });
}

/* ── Events ─────────────────────────────────────── */

function dmOnClick(e) {
    const t = e.target;

    if (t.classList && t.classList.contains('dm-modal-backdrop')) {
        if (dmMouseDownTarget === t) dmCloseModal(t.id);
        return;
    }

    const btn = t.closest('[data-dm-action]');
    if (!btn || btn.disabled) return;

    const action = btn.dataset.dmAction;
    const id = btn.dataset.id || '';
    if (action !== 'menu') dmCloseMenus(false);

    switch (action) {
        case 'menu':          dmToggleMenu(btn); break;
        case 'open-dept':     dmOpenDept(id); break;
        case 'back':          dmBack(); break;
        case 'retry':         dmLoadList(); break;
        case 'retry-detail':  dmLoadDetail(); break;
        case 'clear-filters': dmClearFilters(); break;
        case 'export':        dmExportCsv(); break;
        case 'add-dept':      dmOpenDeptForm(null); break;
        case 'edit-dept':     dmOpenDeptForm(id || dmDeptId); break;
        case 'add-unit':      dmOpenUnitForm(null, null); break;
        case 'add-unit-here': dmOpenUnitForm(null, dmDeptId); break;
        case 'edit-unit':     dmOpenUnitForm(id, null); break;
        case 'view-unit':     dmOpenUnitDetails(id); break;
        case 'edit-unit-from-details': dmOpenUnitForm(dmUnitDetailsId, null); break;
        case 'toggle-dept':   dmOpenConfirm('dept', id, btn.dataset.status); break;
        case 'toggle-unit':   dmOpenConfirm('unit', id, btn.dataset.status); break;
        case 'confirm-status': dmRunConfirm(); break;
        case 'close-modal':   dmCloseModal(btn.closest('.dm-modal-backdrop').id); break;
    }
}

function dmOnInput(e) {
    if (e.target.id === 'dmSearch') {
        clearTimeout(dmSearchTimer);
        dmSearchTimer = setTimeout(dmLoadList, DM_SEARCH_DELAY);
    }
}

function dmOnChange(e) {
    if (e.target.id === 'dmStatus') dmLoadList();
}

function dmOnSubmit(e) {
    const form = e.target;
    if (form.id !== 'dmDeptForm' && form.id !== 'dmUnitForm') return;
    e.preventDefault();
    if (form.id === 'dmDeptForm') dmSubmitDept(form);
    else dmSubmitUnit(form);
}

function dmClearFilters() {
    dmEl('dmSearch').value = '';
    dmEl('dmStatus').value = '';
    dmLoadList();
}

/* ── Row menus ──────────────────────────────────── */

function dmToggleMenu(btn) {
    const menu = btn.closest('.dm-menu-wrap').querySelector('.dm-menu');
    const willOpen = menu.hidden;
    dmCloseMenus(false);
    if (!willOpen) return;

    menu.hidden = false;
    menu.classList.remove('dm-menu--up');
    btn.setAttribute('aria-expanded', 'true');

    // Keep the menu inside the viewport
    const rect = menu.getBoundingClientRect();
    if (rect.bottom > window.innerHeight - 8) menu.classList.add('dm-menu--up');

    const first = menu.querySelector('button');
    if (first) first.focus();
}

function dmCloseMenus(returnFocus) {
    document.querySelectorAll('.dm-menu:not([hidden])').forEach(function (m) {
        m.hidden = true;
        const b = m.parentElement.querySelector('[data-dm-action="menu"]');
        if (b) {
            b.setAttribute('aria-expanded', 'false');
            if (returnFocus) b.focus();
        }
    });
}

/* ── Modal plumbing ─────────────────────────────── */

function dmSyncBodyLock() {
    document.body.classList.toggle('dm-modal-open', dmModalStack.length > 0);
}

function dmOpenModal(id) {
    const m = dmEl(id);
    if (!m) return;
    if (dmModalStack.indexOf(id) === -1) {
        dmModalStack.push(id);
        dmReturnFocus[id] = document.activeElement;
    }
    m.hidden = false;
    dmSyncBodyLock();
    const dlg = m.querySelector('.dm-modal');
    if (dlg) dlg.focus();
}

function dmCloseModal(id) {
    const m = dmEl(id);
    if (!m) return;
    m.hidden = true;
    dmModalStack = dmModalStack.filter(function (x) { return x !== id; });
    dmSyncBodyLock();

    if (id === 'dmConfirmModal') dmConfirm = null;
    if (id === 'dmUnitDetailsModal') dmUnitDetailsId = null;

    const back = dmReturnFocus[id];
    delete dmReturnFocus[id];
    if (back && document.body.contains(back) && typeof back.focus === 'function') back.focus();
}

function dmTrapTab(e) {
    if (e.key !== 'Tab' || !dmModalStack.length) return;
    const top = dmEl(dmModalStack[dmModalStack.length - 1]);
    if (!top) return;
    const items = Array.from(top.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )).filter(function (el) { return el.offsetParent !== null; });
    if (!items.length) return;

    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && (document.activeElement === first || document.activeElement === top.querySelector('.dm-modal'))) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

function dmToast(message) {
    const el = dmEl('dmToast');
    if (!el) return;
    el.textContent = message;
    el.hidden = false;
    clearTimeout(dmToastTimer);
    dmToastTimer = setTimeout(function () { el.hidden = true; }, 4500);
}

function dmSetBusy(form, busy, label) {
    dmBusy = busy;
    const submit = form.querySelector('button[type="submit"]');
    if (!submit) return;
    submit.disabled = busy;
    submit.textContent = busy ? label : (submit.dataset.label || submit.textContent);
}

function dmClearErrors(form, msgId) {
    form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
    form.querySelectorAll('.dm-field-error').forEach(function (p) { p.textContent = ''; p.hidden = true; });
    dmSet(msgId, '');
}

function dmShowFieldErrors(form, msgId, errors, message) {
    let shown = 0;
    Object.keys(errors || {}).forEach(function (key) {
        const field = form.querySelector('[name="' + key + '"]');
        const note = form.querySelector('[data-error-for="' + key + '"]');
        if (field) field.setAttribute('aria-invalid', 'true');
        if (note) { note.textContent = errors[key]; note.hidden = false; shown++; }
    });
    // Anything not tied to a field goes in the general message
    dmSet(msgId, shown ? '' : message);
    const firstBad = form.querySelector('[aria-invalid="true"]');
    if (firstBad) firstBad.focus();
}

async function dmAfterChange() {
    const detail = (dmView === 'detail' && dmDeptId) ? dmLoadDetail() : Promise.resolve();
    await Promise.all([dmLoadList(), detail]);
}

/* ── Department form ────────────────────────────── */

function dmOpenDeptForm(id) {
    const form = dmEl('dmDeptForm');
    form.reset();
    dmClearErrors(form, 'dmDeptMsg');

    const d = id ? dmFindDept(id) : null;
    dmSet('dmDeptTitle', d ? 'Edit department' : 'Add department');
    dmEl('dmDeptId').value = d ? String(d.department_id) : '';
    dmEl('dmDeptName').value = d ? d.department_name : '';
    dmEl('dmDeptDesc').value = d && d.description ? d.description : '';
    dmEl('dmDeptStatus').value = d && dmIsInactive(d) ? 'Inactive' : 'Active';

    dmOpenModal('dmDeptModal');
    dmEl('dmDeptName').focus();
}

async function dmSubmitDept(form) {
    if (dmBusy) return;
    dmClearErrors(form, 'dmDeptMsg');

    const data = Object.fromEntries(new FormData(form).entries());
    data.name = (data.name || '').trim();
    if (!data.name) {
        dmShowFieldErrors(form, 'dmDeptMsg', { name: 'Department name is required.' }, '');
        return;
    }

    dmSetBusy(form, true, 'Saving…');
    try {
        const res = await dmRequest('save_department', data, 'POST');
        dmCloseModal('dmDeptModal');
        dmToast('Department “' + res.name + '” saved.');
        await dmAfterChange();
    } catch (err) {
        dmShowFieldErrors(form, 'dmDeptMsg', err.errors, err.message);
    } finally {
        dmSetBusy(form, false);
    }
}

/* ── Unit form ──────────────────────────────────── */

async function dmFillDeptOptions(selectedId, currentId) {
    const sel = dmEl('dmUnitDept');
    sel.innerHTML = '<option value="">Loading…</option>';
    try {
        const data = await dmRequest('options');
        let html = '<option value="">Select department</option>';
        data.departments.forEach(function (d) {
            const inactive = d.status === 'Inactive';
            const keep = String(d.department_id) === String(currentId); // a unit may stay under its current parent
            html += '<option value="' + dmEsc(d.department_id) + '"' + (inactive && !keep ? ' disabled' : '') + '>' +
                dmEsc(d.department_name) + (inactive ? ' (inactive)' : '') + '</option>';
        });
        sel.innerHTML = html;
        sel.value = selectedId ? String(selectedId) : '';
    } catch (err) {
        sel.innerHTML = '<option value="">Unable to load departments</option>';
        dmSet('dmUnitMsg', err.message || 'Unable to load departments.');
    }
}

async function dmOpenUnitForm(unitId, presetDeptId) {
    const form = dmEl('dmUnitForm');
    form.reset();
    dmClearErrors(form, 'dmUnitMsg');

    const u = unitId ? dmFindUnit(unitId) : null;
    if (unitId && !u) {
        dmToast('Open the department first, then edit its unit.');
        return;
    }

    dmSet('dmUnitTitle', u ? 'Edit unit' : 'Add unit');
    dmEl('dmUnitId').value = u ? String(u.unit_id) : '';
    dmEl('dmUnitName').value = u ? u.unit_name : '';
    dmEl('dmUnitDesc').value = u && u.description ? u.description : '';
    dmEl('dmUnitStatus').value = u && dmIsInactive(u) ? 'Inactive' : 'Active';

    dmOpenModal('dmUnitModal');
    dmEl('dmUnitName').focus();
    await dmFillDeptOptions(u ? u.department_id : presetDeptId, u ? u.department_id : null);
}

async function dmSubmitUnit(form) {
    if (dmBusy) return;
    dmClearErrors(form, 'dmUnitMsg');

    const data = Object.fromEntries(new FormData(form).entries());
    data.name = (data.name || '').trim();
    const errors = {};
    if (!data.department_id) errors.department_id = 'Select a parent department.';
    if (!data.name) errors.name = 'Unit name is required.';
    if (Object.keys(errors).length) {
        dmShowFieldErrors(form, 'dmUnitMsg', errors, '');
        return;
    }

    dmSetBusy(form, true, 'Saving…');
    try {
        const res = await dmRequest('save_unit', data, 'POST');
        dmCloseModal('dmUnitModal');
        if (dmUnitDetailsId) dmCloseModal('dmUnitDetailsModal');
        dmToast('Unit “' + res.name + '” saved.');
        await dmAfterChange();
    } catch (err) {
        dmShowFieldErrors(form, 'dmUnitMsg', err.errors, err.message);
    } finally {
        dmSetBusy(form, false);
    }
}

/* ── Unit details ───────────────────────────────── */

async function dmOpenUnitDetails(id) {
    id = parseInt(id, 10);
    if (!id) return;
    dmUnitDetailsId = id;

    dmEl('dmUnitDetailsContent').hidden = true;
    const st = dmEl('dmUnitDetailsState');
    st.textContent = 'Loading…';
    st.classList.remove('dm-state--error');
    st.hidden = false;
    dmSet('dmUnitDetailsTitle', 'Unit');
    dmEl('dmUnitDetailsStatus').className = 'dm-status';
    dmSet('dmUnitDetailsStatus', '');
    dmOpenModal('dmUnitDetailsModal');

    try {
        const data = await dmRequest('unit', { id: id });
        if (id !== dmUnitDetailsId) return;
        const u = data.unit;

        dmSet('dmUnitDetailsTitle', u.unit_name);
        const badge = dmEl('dmUnitDetailsStatus');
        badge.className = 'dm-status ' + (dmIsInactive(u) ? 'is-inactive' : 'is-active');
        badge.textContent = dmIsInactive(u) ? 'Inactive' : 'Active';
        dmSet('dmUdDept', u.department_name);
        dmSet('dmUdHead', u.head_name || 'No unit head assigned');
        dmSet('dmUdCount', String(u.employee_count));
        dmSet('dmUdDesc', u.description || 'No description provided.');

        const list = dmEl('dmUdEmployees');
        const note = dmEl('dmUdEmpState');
        if (!data.employees.length) {
            list.innerHTML = '';
            note.textContent = 'No employees are assigned to this unit.';
            note.hidden = false;
        } else {
            note.hidden = true;
            list.innerHTML = data.employees.map(function (e) {
                return '<li><span class="dm-avatar dm-avatar-small" aria-hidden="true">' + dmEsc(dmInitials(e.full_name)) + '</span>' +
                    '<span><strong>' + dmEsc(e.full_name) + '</strong><small>' +
                    dmEsc([e.employee_code, e.position_name].filter(Boolean).join(' · ') || 'No ID or position') + '</small></span></li>';
            }).join('');
        }

        st.hidden = true;
        dmEl('dmUnitDetailsContent').hidden = false;
    } catch (err) {
        if (id !== dmUnitDetailsId) return;
        st.textContent = err.message || 'Unable to load this unit.';
        st.classList.add('dm-state--error');
    }
}

/* ── Activate / deactivate ──────────────────────── */

function dmOpenConfirm(kind, id, status) {
    id = parseInt(id, 10);
    const rec = kind === 'dept' ? dmFindDept(id) : dmFindUnit(id);
    if (!rec) return;

    const name = kind === 'dept' ? rec.department_name : rec.unit_name;
    const deactivate = status === 'Inactive';
    dmConfirm = { kind: kind, id: id, status: status };

    dmSet('dmConfirmTitle', (deactivate ? 'Deactivate ' : 'Reactivate ') + (kind === 'dept' ? 'department' : 'unit'));
    dmSet('dmConfirmText', deactivate
        ? '“' + name + '” will be marked inactive. Nothing is deleted, and you can reactivate it later.'
        : '“' + name + '” will be marked active again.');
    dmSet('dmConfirmMsg', '');

    const btn = dmEl('dmConfirmBtn');
    btn.textContent = deactivate ? 'Deactivate' : 'Reactivate';
    btn.dataset.label = btn.textContent;
    btn.disabled = false;
    btn.className = 'dm-btn ' + (deactivate ? 'dm-btn-danger' : 'dm-btn-primary');

    dmOpenModal('dmConfirmModal');
    btn.focus();
}

async function dmRunConfirm() {
    if (dmBusy || !dmConfirm) return;
    const c = dmConfirm;
    const btn = dmEl('dmConfirmBtn');

    dmBusy = true;
    btn.disabled = true;
    btn.textContent = 'Working…';
    dmSet('dmConfirmMsg', '');

    try {
        const res = await dmRequest(c.kind === 'dept' ? 'set_department_status' : 'set_unit_status',
            { id: c.id, status: c.status }, 'POST');
        dmCloseModal('dmConfirmModal');
        dmToast('“' + res.name + '” is now ' + c.status.toLowerCase() + '.');
        await dmAfterChange();
    } catch (err) {
        // e.g. blocked because staff are still assigned; the reason is shown here
        dmSet('dmConfirmMsg', err.message);
        btn.disabled = false;
        btn.textContent = btn.dataset.label || 'Confirm';
    } finally {
        dmBusy = false;
    }
}

/* ── Export (departments matching the current filters) ─ */

function dmCsvCell(v) {
    let s = String(v === null || v === undefined ? '' : v);
    if (/^[=+\-@\t\r]/.test(s)) s = "'" + s; // stop spreadsheet formula injection
    return '"' + s.replace(/"/g, '""') + '"';
}

function dmExportCsv() {
    if (!dmDepts.length) {
        dmToast('There are no departments to export.');
        return;
    }
    const lines = [['Department', 'Status', 'Units', 'Employees', 'Department Head'].map(dmCsvCell).join(',')];
    dmDepts.forEach(function (d) {
        lines.push([d.department_name, d.status, d.unit_count, d.employee_count, d.head_name]
            .map(dmCsvCell).join(','));
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
// above, so also watch for #dmRoot appearing. dmInit is safe to call repeatedly —
// the initial load is guarded by a flag on the root element.
function dmWatchDom() {
    if (window.__dmObserver || !document.body) return;
    window.__dmObserver = new MutationObserver(function () {
        const root = document.getElementById('dmRoot');
        if (root && !root.dataset.dmLoaded) dmInit();
    });
    window.__dmObserver.observe(document.body, { childList: true, subtree: true });
}

dmWatchDom();
document.addEventListener('DOMContentLoaded', dmWatchDom);

if (document.readyState !== 'loading') {
    dmInit();
}