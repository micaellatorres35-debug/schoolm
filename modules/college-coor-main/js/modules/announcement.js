function initAnnouncementsModule() {
    // ── Image preview ──────────────────────────────────────────────────────
    const imageInput = document.getElementById('ann-image');
    let previewEl    = document.getElementById('ann-image-preview');

    if (imageInput) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];

            if (!previewEl) {
                previewEl    = document.createElement('img');
                previewEl.id = 'ann-image-preview';
                previewEl.classList.add('ann-image-preview');
                imageInput.parentElement.appendChild(previewEl);
            }

            if (file) {
                previewEl.src           = URL.createObjectURL(file);
                previewEl.style.display = 'block';
            } else {
                previewEl.src           = '';
                previewEl.style.display = 'none';
            }
        });
    }

    // ── Modal wiring ─────────────────────────────────────────────────────
    const modalOverlay  = document.getElementById('comm-modal-overlay');
    const openModalBtn  = document.getElementById('comm-open-modal');
    const closeModalBtn = document.getElementById('comm-modal-close');
    const form          = document.getElementById('announcement-form');

    const openCommModal = () => modalOverlay?.classList.add('active');
    const closeCommModal = () => {
        modalOverlay?.classList.remove('active');
        form?.reset();
        if (previewEl) {
            previewEl.src           = '';
            previewEl.style.display = 'none';
        }
    };

    openModalBtn?.addEventListener('click', openCommModal);
    closeModalBtn?.addEventListener('click', closeCommModal);
    modalOverlay?.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeCommModal();
    });

    if (!window.commEscBound) {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeCommModal();
        });
        window.commEscBound = true;
    }

    // ── Clear preview on manual reset ──────────────────────────────────────
    if (form) {
        form.addEventListener('reset', () => {
            if (previewEl) {
                previewEl.src           = '';
                previewEl.style.display = 'none';
            }
        });
    }

    // ── Refresh list + close modal after router-driven submit ─────────────
    if (!window.commSuccessBound) {
        window.addEventListener('form:success', async () => {
            const overlay = document.getElementById('comm-modal-overlay');
            overlay?.classList.remove('active');

            const listEl = document.querySelector('.announcements-list');
            if (!listEl) return;

            try {
                const response = await fetch(window.location.href, { cache: 'no-store' });
                if (!response.ok) return;

                const html    = await response.text();
                const parser  = new DOMParser();
                const doc     = parser.parseFromString(html, 'text/html');
                const newList = doc.querySelector('.announcements-list');

                if (newList) {
                    listEl.innerHTML = newList.innerHTML;
                }
            } catch {
                // Silent fail
            }
        });
        window.commSuccessBound = true;
    }
}

window.addEventListener('page:loaded', initAnnouncementsModule);
document.addEventListener('DOMContentLoaded', initAnnouncementsModule);


/* ============================================================
   Archive / restore (prefix: ann / ANN_)
   `var` is used so the file stays safe if the router evaluates it twice.
   ============================================================ */

var ANN_ARCHIVE_ENDPOINT = 'controllers/AnnouncementController.php';
var ANN_SEARCH_DELAY = 300;
var ANN_REQUEST_TIMEOUT = 15000;

var annPage = 1;
var annPages = 1;
var annListReq = 0;
var annSearchTimer = null;
var annToastTimer = null;
var annConfirm = null;          // { kind: 'archive'|'restore', id, label }
var annBusy = false;
var annActiveStale = false;     // active list needs a refresh after a restore
var annReturnFocus = null;
var annMouseDownTarget = null;

function annEl(id) {
    return document.getElementById(id);
}

function annEsc(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function initAnnouncementArchive() {
    var root = annEl('annRoot');
    if (!root || root.dataset.annBound) return;
    root.dataset.annBound = '1';

    // Fresh page render: reset state
    annPage = 1;
    annPages = 1;
    annConfirm = null;
    annBusy = false;
    annActiveStale = false;

    if (root.dataset.canArchive !== '1') return; // no archive UI rendered for this role

    root.addEventListener('click', annOnClick);
    root.addEventListener('mousedown', function (e) { annMouseDownTarget = e.target; });
    root.addEventListener('keydown', annOnKeydown);
    root.addEventListener('input', function (e) {
        if (e.target.id !== 'annSearch') return;
        clearTimeout(annSearchTimer);
        annSearchTimer = setTimeout(function () { annPage = 1; annLoadArchived(); }, ANN_SEARCH_DELAY);
    });

    if (!window.annEscBound) {
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var overlay = annEl('annConfirmOverlay');
            if (overlay && overlay.classList.contains('active')) annCloseConfirm();
        });
        window.annEscBound = true;
    }
}

/* ── AJAX ───────────────────────────────────────── */

async function annRequest(action, params, method) {
    method = method || 'GET';
    var root = annEl('annRoot');
    var controller = new AbortController();
    var timer = setTimeout(function () { controller.abort(); }, ANN_REQUEST_TIMEOUT);

    // X-Requested-With makes auth/session.php answer with JSON instead of a redirect
    var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    var opts = { method: method, credentials: 'same-origin', headers: headers, signal: controller.signal };
    var url = ANN_ARCHIVE_ENDPOINT + '?action=' + encodeURIComponent(action);

    if (method === 'GET') {
        var qs = new URLSearchParams(params || {}).toString();
        if (qs) url += '&' + qs;
    } else {
        headers['X-CSRF-Token'] = root ? (root.dataset.csrf || '') : '';
        opts.body = new URLSearchParams(params || {});
    }

    var res;
    try {
        res = await fetch(url, opts);
    } catch (err) {
        throw new Error(err.name === 'AbortError' ? 'The request timed out. Please try again.' : 'Network error. Check your connection and try again.');
    } finally {
        clearTimeout(timer);
    }

    var json;
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
        throw new Error((json && json.message) || 'Request failed.');
    }
    return json.data || {};
}

/* ── Events ─────────────────────────────────────── */

function annOnClick(e) {
    var t = e.target;

    // Clicking the dimmed backdrop closes the confirm dialog
    if (t.id === 'annConfirmOverlay') {
        if (annMouseDownTarget === t) annCloseConfirm();
        return;
    }

    var btn = t.closest('[data-ann-action]');
    if (!btn || btn.disabled) return;

    switch (btn.dataset.annAction) {
        case 'tab':           annSelectTab(btn.dataset.tab, false); break;
        case 'archive':       annOpenConfirm('archive', btn.dataset.id, btn.dataset.label, btn); break;
        case 'restore':       annOpenConfirm('restore', btn.dataset.id, btn.dataset.label, btn); break;
        case 'confirm-cancel': annCloseConfirm(); break;
        case 'confirm-run':   annRunConfirm(); break;
        case 'page':          annPage = parseInt(btn.dataset.page, 10) || 1; annLoadArchived(); break;
        case 'retry':         annLoadArchived(); break;
    }
}

function annOnKeydown(e) {
    // Arrow keys move between the two tabs
    if ((e.key === 'ArrowRight' || e.key === 'ArrowLeft') && e.target.classList && e.target.classList.contains('ann-tab')) {
        e.preventDefault();
        annSelectTab(e.target.dataset.tab === 'active' ? 'archived' : 'active', true);
        return;
    }

    // Keep Tab inside the confirm dialog while it is open
    var overlay = annEl('annConfirmOverlay');
    if (e.key === 'Tab' && overlay && overlay.classList.contains('active')) {
        var items = Array.prototype.slice.call(overlay.querySelectorAll('button:not([disabled])'))
            .filter(function (el) { return el.offsetParent !== null; });
        if (!items.length) return;
        var first = items[0];
        var last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }
}

/* ── Tabs ───────────────────────────────────────── */

function annSelectTab(name, focus) {
    var archived = name === 'archived';

    ['active', 'archived'].forEach(function (n) {
        var tab = annEl(n === 'active' ? 'annTabActive' : 'annTabArchived');
        var on = n === name;
        if (!tab) return;
        tab.classList.toggle('is-active', on);
        tab.setAttribute('aria-selected', on ? 'true' : 'false');
        tab.tabIndex = on ? 0 : -1;
        if (on && focus) tab.focus();
    });

    annEl('annActivePanel').hidden = archived;
    annEl('annArchivedPanel').hidden = !archived;

    if (archived) {
        annLoadArchived();
    } else if (annActiveStale) {
        annActiveStale = false;
        annRefreshActiveList();
    }
}

// Same technique the module already uses after posting: re-fetch the page and swap the list
async function annRefreshActiveList() {
    var listEl = annEl('annActivePanel');
    if (!listEl) return;
    try {
        var response = await fetch(window.location.href, { cache: 'no-store' });
        if (!response.ok) return;
        var doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        var fresh = doc.querySelector('#annActivePanel');
        if (fresh) listEl.innerHTML = fresh.innerHTML;
    } catch (err) {
        // Silent: the list refreshes on the next page load
    }
}

/* ── Archived list ──────────────────────────────── */

function annFormatDate(s, withTime) {
    if (!s) return 'Not available';
    var d = new Date(String(s).replace(' ', 'T'));
    if (isNaN(d.getTime())) return 'Not available';
    var out = d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    if (withTime) out += ', ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    return out;
}

function annLabelFor(r) {
    var title = (r.title || '').trim();
    return title !== '' ? title : 'The announcement for ' + annFormatDate(r.publish_date, false);
}

function annSetState(text, isError, withRetry) {
    var el = annEl('annArchiveState');
    el.innerHTML = annEsc(text) + (withRetry ? ' <button type="button" class="ann-link-btn" data-ann-action="retry">Try again</button>' : '');
    el.classList.toggle('ann-state--error', !!isError);
    el.hidden = !text;
}

async function annLoadArchived() {
    var list = annEl('annArchiveList');
    var searchEl = annEl('annSearch');
    if (!list) return;

    var req = ++annListReq; // ignore out-of-order responses
    annSetState('Loading archived announcements…');
    list.setAttribute('aria-busy', 'true');

    try {
        var data = await annRequest('list_archived', { search: searchEl ? searchEl.value.trim() : '', page: annPage });
        if (req !== annListReq) return;

        annPage = data.page;
        annPages = data.pages;
        list.removeAttribute('aria-busy');

        if (!data.rows.length) {
            list.innerHTML = '';
            annSetState(searchEl && searchEl.value.trim()
                ? 'No archived announcements match your search.'
                : 'No announcements have been archived.');
            annEl('annShowing').textContent = '';
            annEl('annPageNav').innerHTML = '';
            return;
        }

        annSetState('');
        list.innerHTML = data.rows.map(annArchivedCardHtml).join('');

        var from = (data.page - 1) * data.per_page + 1;
        annEl('annShowing').textContent = 'Showing ' + from + '-' + (from + data.rows.length - 1) + ' of ' + data.total;
        annRenderPager();
    } catch (err) {
        if (req !== annListReq) return;
        list.removeAttribute('aria-busy');
        list.innerHTML = '';
        annEl('annShowing').textContent = '';
        annEl('annPageNav').innerHTML = '';
        annSetState(err.message || 'Unable to load archived announcements.', true, true);
    }
}

function annArchivedCardHtml(r) {
    var base = annEl('annRoot').dataset.imageBase || '';
    var label = annLabelFor(r);
    var hasTitle = (r.title || '').trim() !== '';

    return '' +
    '<article class="announcement-card ann-archived-card" data-id="' + annEsc(r.announcement_id) + '">' +
        (r.image_file
            ? '<div class="announcement-image"><img src="' + annEsc(base + encodeURIComponent(r.image_file)) + '" alt="' + annEsc(hasTitle ? r.title : '') + '" loading="lazy"></div>'
            : '') +
        '<div class="announcement-content">' +
            '<div class="ann-archived-top">' +
                '<div class="ann-archived-text">' +
                    (hasTitle ? '<h3 class="ann-archived-title">' + annEsc(r.title) + '</h3>' : '') +
                    (r.preview ? '<p class="ann-archived-preview">' + annEsc(r.preview) + '</p>' : '') +
                '</div>' +
                '<button type="button" class="ann-card-btn" data-ann-action="restore" data-id="' + annEsc(r.announcement_id) + '" data-label="' + annEsc(label) + '">Restore</button>' +
            '</div>' +
            '<dl class="ann-meta-grid">' +
                '<div><dt>Published</dt><dd>' + annEsc(annFormatDate(r.publish_date, false)) + '</dd></div>' +
                '<div><dt>Archived</dt><dd>' + annEsc(annFormatDate(r.archived_at, true)) + '</dd></div>' +
                '<div><dt>Posted by</dt><dd>' + annEsc(r.created_by_name || 'Not available') + '</dd></div>' +
                '<div><dt>Archived by</dt><dd>' + annEsc(r.archived_by_name || 'Not available') + '</dd></div>' +
            '</dl>' +
        '</div>' +
    '</article>';
}

function annRenderPager() {
    var nav = annEl('annPageNav');
    if (annPages <= 1) { nav.innerHTML = ''; return; }

    var nums = [];
    for (var p = 1; p <= annPages; p++) {
        if (p === 1 || p === annPages || Math.abs(p - annPage) <= 1) nums.push(p);
    }

    var html = '<button type="button" class="ann-page-btn" data-ann-action="page" data-page="' + (annPage - 1) + '"' +
               (annPage === 1 ? ' disabled' : '') + '>&laquo; Prev</button>';
    var prev = 0;
    nums.forEach(function (n) {
        if (n - prev > 1) html += '<span class="ann-page-gap">…</span>';
        html += '<button type="button" class="ann-page-btn' + (n === annPage ? ' ann-page-btn--active' : '') +
                '" data-ann-action="page" data-page="' + n + '"' + (n === annPage ? ' aria-current="page"' : '') + '>' + n + '</button>';
        prev = n;
    });
    html += '<button type="button" class="ann-page-btn" data-ann-action="page" data-page="' + (annPage + 1) + '"' +
            (annPage === annPages ? ' disabled' : '') + '>Next &raquo;</button>';
    nav.innerHTML = html;
}

/* ── Confirm dialog ─────────────────────────────── */

function annOpenConfirm(kind, id, label, trigger) {
    id = parseInt(id, 10);
    if (!id) return;

    annConfirm = { kind: kind, id: id, label: label || 'This announcement' };
    annReturnFocus = trigger || null;

    var archive = kind === 'archive';
    annEl('annConfirmTitle').textContent = archive ? 'Archive announcement' : 'Restore announcement';
    annEl('annConfirmText').textContent = archive
        ? '“' + annConfirm.label + '” will be moved to the archive and will no longer appear in the active announcements list. Nothing is deleted, and you can restore it at any time.'
        : '“' + annConfirm.label + '” will be removed from the archive and will appear in the active announcements list again, with its original date and image.';
    annEl('annConfirmMsg').textContent = '';

    var btn = annEl('annConfirmBtn');
    btn.textContent = archive ? 'Confirm archive' : 'Confirm restore';
    btn.dataset.label = btn.textContent;
    btn.disabled = false;

    annEl('annConfirmOverlay').classList.add('active');
    var cancel = annEl('annConfirmOverlay').querySelector('.ann-cancel');
    if (cancel) cancel.focus();
}

function annCloseConfirm() {
    var overlay = annEl('annConfirmOverlay');
    if (overlay) overlay.classList.remove('active');
    annConfirm = null;
    if (annReturnFocus && document.body.contains(annReturnFocus)) annReturnFocus.focus();
    annReturnFocus = null;
}

async function annRunConfirm() {
    if (annBusy || !annConfirm) return;
    var c = annConfirm;
    var btn = annEl('annConfirmBtn');

    annBusy = true; // blocks double-clicks and repeated submissions
    btn.disabled = true;
    btn.textContent = c.kind === 'archive' ? 'Archiving…' : 'Restoring…';
    annEl('annConfirmMsg').textContent = '';

    try {
        var res = await annRequest(c.kind, { announcement_id: c.id }, 'POST');
        annReturnFocus = null; // the triggering card is about to disappear
        annCloseConfirm();
        annToast(c.kind === 'archive' ? 'Announcement archived.' : 'Announcement restored.');

        if (c.kind === 'archive') {
            annRemoveActiveCard(c.id);
            annEl('annTabArchived').focus();
        } else {
            annActiveStale = true;
            await annLoadArchived();
            var s = annEl('annSearch');
            if (s) s.focus();
        }
    } catch (err) {
        // Keep the dialog open and the page state as it was
        annEl('annConfirmMsg').textContent = err.message || 'Something went wrong. Please try again.';
        btn.disabled = false;
        btn.textContent = btn.dataset.label || 'Confirm';
    } finally {
        annBusy = false;
    }
}

function annRemoveActiveCard(id) {
    var panel = annEl('annActivePanel');
    var trigger = panel && panel.querySelector('[data-ann-action="archive"][data-id="' + id + '"]');
    var card = trigger && trigger.closest('.announcement-card');
    if (card) card.remove();

    if (panel && !panel.querySelector('.announcement-card') && !panel.querySelector('.muted')) {
        panel.insertAdjacentHTML('beforeend', '<p class="muted">No announcements have been posted yet.</p>');
    }
}

function annToast(message) {
    var el = annEl('annToast');
    if (!el) return;
    el.textContent = message;
    el.hidden = false;
    clearTimeout(annToastTimer);
    annToastTimer = setTimeout(function () { el.hidden = true; }, 4000);
}

window.addEventListener('page:loaded', initAnnouncementArchive);
document.addEventListener('DOMContentLoaded', initAnnouncementArchive);
if (document.readyState !== 'loading') {
    initAnnouncementArchive();
}