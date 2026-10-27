// Directress dashboard overview — tab switching, enrollment range toggle, quick navigation.
// The page itself is rendered by PHP; this file only handles the small interactions.

const DD_PAGE_KEY = 'dashboard-overview';

function ddQueryElements() {
    return {
        root: document.querySelector('[data-dd-root]'),
    };
}

function ddInit() {
    const { root } = ddQueryElements();
    if (!root || root.dataset.ddRoot !== DD_PAGE_KEY) return;

    ddBindOnce(root, 'ddClickBound', 'click', ddOnClick);
    ddBindOnce(root, 'ddKeyBound', 'keydown', ddOnKeydown);
}

// Flag lives on the root element: a freshly loaded page gets a fresh root,
// so listeners are attached once per render and never stack up.
function ddBindOnce(el, flag, type, handler) {
    if (el.dataset[flag] === '1') return;
    el.dataset[flag] = '1';
    el.addEventListener(type, handler);
}

function ddOnClick(e) {
    const tab = e.target.closest('[data-dd-tab]');
    if (tab) {
        ddSwitchTab(tab);
        return;
    }

    const range = e.target.closest('[data-dd-range]');
    if (range) {
        ddSwitchRange(range);
        return;
    }

    const go = e.target.closest('[data-dd-go]');
    if (go) {
        ddGoTo(go.dataset.ddGo);
    }
}

// Buttons already fire click on Enter/Space; this covers the role="button" links and rows.
function ddOnKeydown(e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;

    const go = e.target.closest('[data-dd-go]');
    if (!go || go.tagName === 'BUTTON') return;

    e.preventDefault();
    ddGoTo(go.dataset.ddGo);
}

function ddSwitchTab(tab) {
    const scope = tab.closest('[data-dd-tabs]');
    if (!scope) return;

    const name = tab.dataset.ddTab;

    scope.querySelectorAll('[data-dd-tab]').forEach((btn) => {
        const active = btn === tab;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    scope.querySelectorAll('[data-dd-pane]').forEach((pane) => {
        pane.hidden = pane.dataset.ddPane !== name;
    });
}

function ddSwitchRange(btn) {
    const scope = btn.closest('[data-dd-range-scope]');
    if (!scope) return;

    const name = btn.dataset.ddRange;

    scope.querySelectorAll('[data-dd-range]').forEach((b) => {
        const active = b === btn;
        b.classList.toggle('is-active', active);
        b.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    scope.querySelectorAll('[data-dd-bars]').forEach((bars) => {
        bars.hidden = bars.dataset.ddBars !== name;
    });
}

// Opens another page by clicking its sidebar link, so the normal page-loading
// logic runs. If no matching sidebar link is found, nothing happens.
function ddGoTo(key) {
    if (!key) return;

    const link = document.querySelector(
        '.sidebar [href*="' + key + '"], ' +
        '.sidebar [data-page="' + key + '"], ' +
        '.sidebar [data-module="' + key + '"]'
    );

    if (link) link.click();
}

document.addEventListener('DOMContentLoaded', ddInit);
document.addEventListener('page:loaded', ddInit);