(function () {
    'use strict';

    var escHandler = null;

    function init() {
    var root = document.getElementById('brmRoot');
    if (!root || root.dataset.brmInit === '1') return;   // not on this page, or already initialised
    root.dataset.brmInit = '1';

    // ---------- Sample data (replace with fetch() to your PHP/API endpoint later) ----------
    var activities = [
        {
            id: 1, name: 'Community Feeding Program', status: 'Approved',
            proposed: 35000, approved: 30000,
            expenses: [
                { item: 'Food Supplies', amount: 10000 },
                { item: 'Transportation', amount: 3000 },
                { item: 'Other Supplies', amount: 2000 }
            ],
            materials: [
                { name: 'Rice', required: '20 sacks', used: '15' },
                { name: 'Chairs', required: '30', used: '30' },
                { name: 'Tables', required: '5', used: '5' }
            ],
            manpower: [
                { role: 'Faculty', required: 3 },
                { role: 'Student Volunteers', required: 15 },
                { role: 'Other Personnel', required: 2 }
            ]
        },
        {
            id: 2, name: 'Community Clean-Up', status: 'Ongoing',
            proposed: 22000, approved: 20000,
            expenses: [
                { item: 'Cleaning Supplies', amount: 5000 },
                { item: 'Waste Bags', amount: 3000 }
            ],
            materials: [
                { name: 'Trash Bags', required: '200 pcs', used: '120' },
                { name: 'Gloves', required: '50 pairs', used: '50' }
            ],
            manpower: [
                { role: 'Faculty', required: 2 },
                { role: 'Student Volunteers', required: 25 },
                { role: 'Other Personnel', required: 3 }
            ]
        },
        {
            id: 3, name: 'Literacy Program', status: 'Proposed',
            proposed: 25000, approved: 0,
            expenses: [],
            materials: [
                { name: 'Books', required: '100 pcs', used: '0' },
                { name: 'Writing Materials', required: '60 sets', used: '0' }
            ],
            manpower: [
                { role: 'Faculty', required: 2 },
                { role: 'Student Volunteers', required: 10 },
                { role: 'Other Personnel', required: 1 }
            ]
        }
    ];

    // Overall summary figures (as shown in the UI concept)
    var stats = { proposed: 150000, approved: 120000, expenses: 65000 };
    var nextId = 4;

    var $ = function (sel) { return root.querySelector(sel); };
    var listView = $('#brmListView');
    var detailView = $('#brmDetailView');
    var tbody = $('#brmTableBody');
    var searchEl = $('#brmSearch');
    var statusEl = $('#brmStatusFilter');
    var filtersEl = $('#brmFilters');
    var filterBtn = $('#brmFilterToggle');

    // ---------- Helpers ----------
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function peso(n) {
        return '\u20B1' + (Number(n) || 0).toLocaleString('en-PH');
    }

    function pill(label) {
        var key = String(label || '').toLowerCase();
        var cls = ['approved', 'ongoing', 'proposed', 'completed'].indexOf(key) > -1 ? key : 'default';
        return '<span class="brm-status brm-status-' + cls + '">' + esc(label || '\u2014') + '</span>';
    }

    function totalExpenses(a) {
        return a.expenses.reduce(function (sum, e) { return sum + e.amount; }, 0);
    }

    function budgetOf(a) {
        return a.approved > 0 ? a.approved : a.proposed;
    }

    function find(id) {
        for (var i = 0; i < activities.length; i++) if (activities[i].id === id) return activities[i];
        return null;
    }

    // ---------- Stats ----------
    function renderStats() {
        var map = {
            proposed: stats.proposed,
            approved: stats.approved,
            expenses: stats.expenses,
            remaining: stats.approved - stats.expenses
        };
        Object.keys(map).forEach(function (k) {
            var el = $('[data-brm-stat="' + k + '"]');
            if (el) el.textContent = peso(map[k]);
        });
    }

    // ---------- List ----------
    function getFiltered() {
        var q = searchEl.value.trim().toLowerCase();
        return activities.filter(function (a) {
            return (!q || a.name.toLowerCase().indexOf(q) > -1) &&
                (statusEl.value === 'All' || a.status === statusEl.value);
        });
    }

    function renderTable() {
        var rows = getFiltered();
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="brm-empty">No extension activities found.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (a) {
            return '<tr data-id="' + a.id + '" tabindex="0">' +
                '<td>' + esc(a.name) + '</td>' +
                '<td>' + peso(budgetOf(a)) + '</td>' +
                '<td>' + peso(totalExpenses(a)) + '</td>' +
                '<td>' + pill(a.status) + '</td>' +
                '</tr>';
        }).join('');
    }

    // ---------- Detail ----------
    function table(headers, rows, emptyMsg) {
        var head = '<thead><tr>' + headers.map(function (h, i) {
            return '<th' + (i > 0 ? ' class="brm-num"' : '') + '>' + esc(h) + '</th>';
        }).join('') + '</tr></thead>';
        var body = rows.length
            ? rows.map(function (r) {
                return '<tr>' + r.map(function (c, i) {
                    return '<td' + (i > 0 ? ' class="brm-num"' : '') + '>' + esc(c) + '</td>';
                }).join('') + '</tr>';
            }).join('')
            : '<tr><td colspan="' + headers.length + '" class="brm-empty">' + esc(emptyMsg) + '</td></tr>';
        return '<div class="brm-table-wrap"><table class="brm-table">' + head + '<tbody>' + body + '</tbody></table></div>';
    }

    function section(title, inner) {
        return '<div class="brm-detail-section"><h3>' + esc(title) + '</h3>' + inner + '</div>';
    }

    function showDetail(id) {
        var a = find(id);
        if (!a) return;
        var actual = totalExpenses(a);

        $('#brmDetailName').textContent = a.name;
        $('#brmDetailStatus').innerHTML = pill(a.status);

        $('#brmDetailBody').innerHTML =
            section('Budget Details', table(['Description', 'Amount'], [
                ['Proposed Budget', peso(a.proposed)],
                ['Approved Budget', peso(a.approved)],
                ['Actual Expenses', peso(actual)],
                ['Remaining', peso(a.approved - actual)]
            ], '')) +
            section('Expense and Procurement Records', table(['Item', 'Amount'],
                a.expenses.map(function (e) { return [e.item, peso(e.amount)]; }),
                'No expense records yet.')) +
            section('Materials and Resource Requirements', table(['Resource', 'Required', 'Used'],
                a.materials.map(function (m) { return [m.name, m.required, m.used]; }),
                'No resource requirements yet.')) +
            section('Manpower Requirements', table(['Personnel', 'Required'],
                a.manpower.map(function (m) { return [m.role, m.required]; }),
                'No manpower requirements yet.'));

        listView.setAttribute('hidden', '');
        detailView.removeAttribute('hidden');
        root.scrollIntoView({ block: 'start' });
    }

    function showList() {
        detailView.setAttribute('hidden', '');
        listView.removeAttribute('hidden');
    }

    // ---------- Modal ----------
    var addModal = $('#brmAddModal');
    var form = $('#brmAddForm');
    var errorEl = $('#brmFormError');

    function openModal(el) { el.classList.add('is-open'); el.setAttribute('aria-hidden', 'false'); }
    function closeModal(el) { el.classList.remove('is-open'); el.setAttribute('aria-hidden', 'true'); }

    // ---------- Events ----------
    root.addEventListener('click', function (e) {
        var t = e.target;
        if (t.hasAttribute('data-brm-close')) { closeModal(addModal); return; }
        var row = t.closest('#brmTableBody tr[data-id]');
        if (row) showDetail(parseInt(row.getAttribute('data-id'), 10));
    });

    tbody.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var row = e.target.closest('tr[data-id]');
        if (row) { e.preventDefault(); showDetail(parseInt(row.getAttribute('data-id'), 10)); }
    });

    if (escHandler) document.removeEventListener('keydown', escHandler);
    escHandler = function (e) {
        if (e.key === 'Escape') closeModal(addModal);
    };
    document.addEventListener('keydown', escHandler);

    $('#brmBack').addEventListener('click', showList);

    filterBtn.addEventListener('click', function () {
        var open = filtersEl.hasAttribute('hidden');
        if (open) filtersEl.removeAttribute('hidden'); else filtersEl.setAttribute('hidden', '');
        filterBtn.setAttribute('aria-expanded', String(open));
    });

    [searchEl, statusEl].forEach(function (el) {
        el.addEventListener('input', renderTable);
        el.addEventListener('change', renderTable);
    });

    $('#brmOpenAdd').addEventListener('click', function () {
        form.reset();
        errorEl.setAttribute('hidden', '');
        openModal(addModal);
        $('#brmFName').focus();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = $('#brmFName').value.trim();
        var proposed = Math.max(0, parseFloat($('#brmFProposed').value) || 0);
        var approved = Math.max(0, parseFloat($('#brmFApproved').value) || 0);

        if (!name) { errorEl.removeAttribute('hidden'); return; }

        activities.push({
            id: nextId++, name: name, status: $('#brmFStatus').value,
            proposed: proposed, approved: approved,
            expenses: [], materials: [], manpower: []
        });

        stats.proposed += proposed;
        stats.approved += approved;

        closeModal(addModal);
        renderStats();
        renderTable();
    });

    renderStats();
    renderTable();
    }

    // Run on first load AND every time the page loader injects this page
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    window.addEventListener('page:loaded', init);
})();