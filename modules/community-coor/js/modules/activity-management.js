(function () {
    'use strict';

    var escHandler = null;

    function init() {
    var root = document.getElementById('aemRoot');
    if (!root || root.dataset.aemInit === '1') return;   // not on this page, or already initialised
    root.dataset.aemInit = '1';

    // ---------- Section field definitions (order = display order) ----------
    var INFO_FIELDS = ['Activity Title', 'Extension Program', 'Activity Description', 'Objectives',
        'Target Community', 'Date and Time', 'Expected Participants', 'Person-in-Charge'];
    var VENUE_FIELDS = ['Venue Name / Location', 'Venue Address', 'Reservation Date', 'Venue Capacity', 'Reservation Status'];
    var LOGISTICS_FIELDS = ['Transportation Arrangements', 'Equipment Preparation', 'Delivery and Distribution',
        'Setup and Cleanup', 'Communication and Coordination'];
    var SAFETY_FIELDS = ['Identified Risks', 'Safety Measures', 'First Aid Arrangements', 'Emergency Contact',
        'Safety Inspection Status'];
    var DOC_FIELDS = ['Attendance Records', 'Activity Photos', 'Supporting Documents',
        'Accomplishment Documentation', 'Post-Activity Evaluation'];

    // ---------- Sample data (replace with fetch() to your PHP/API endpoint later) ----------
    var activities = [
        {
            id: 1, name: 'Community Feeding Program', date: '2026-11-20', status: 'In Preparation', progress: 70,
            info: {
                'Activity Title': 'Community Feeding Program',
                'Extension Program': 'Barangay Nutrition and Wellness Extension',
                'Activity Description': 'Distribution of nutritious meals to families and children in the partner barangay.',
                'Objectives': 'Address short-term hunger and promote community health awareness.',
                'Target Community': 'Barangay San Isidro',
                'Date and Time': 'November 20, 2026 \u2022 8:00 AM \u2013 12:00 PM',
                'Expected Participants': '100 beneficiaries, 25 volunteers',
                'Person-in-Charge': 'Maria Santos'
            },
            venue: {
                'Venue Name / Location': 'Barangay Covered Court',
                'Venue Address': 'San Isidro, Mabalacat City, Pampanga',
                'Reservation Date': 'November 10, 2026',
                'Venue Capacity': '150 persons',
                'Reservation Status': 'Confirmed'
            },
            materials: [
                { item: 'Food Supplies', qty: 100, status: 'Requested' },
                { item: 'Chairs', qty: 30, status: 'Available' },
                { item: 'Tables', qty: 5, status: 'Available' },
                { item: 'Sound System', qty: 1, status: 'Reserved' }
            ],
            logistics: {
                'Transportation Arrangements': 'School van reserved for volunteers and supplies',
                'Equipment Preparation': 'Serving equipment checked and packed',
                'Delivery and Distribution': 'Food packs delivered one day before the activity',
                'Setup and Cleanup': 'Setup at 6:30 AM; cleanup team of 8 volunteers',
                'Communication and Coordination': 'Coordination with the barangay captain and volunteer group chat'
            },
            safety: {
                'Identified Risks': 'Food safety, crowding, heat exposure',
                'Safety Measures': 'Hygiene protocols, crowd marshals, shaded waiting area',
                'First Aid Arrangements': 'First aid kit and 2 trained volunteers on site',
                'Emergency Contact': 'Barangay Health Center \u2022 0917 000 0000',
                'Safety Inspection Status': 'Scheduled'
            },
            docs: {
                'Attendance Records': 'To be collected on the day of the activity',
                'Activity Photos': 'Pending',
                'Supporting Documents': 'Request letter and venue reservation attached',
                'Accomplishment Documentation': 'Pending',
                'Post-Activity Evaluation': 'Pending'
            }
        },
        {
            id: 2, name: 'Community Clean-Up', date: '2026-11-25', status: 'In Preparation', progress: 45,
            info: {
                'Activity Title': 'Community Clean-Up',
                'Extension Program': 'Environmental Awareness Program',
                'Activity Description': 'Clean-up drive along the barangay main road and drainage canals.',
                'Objectives': 'Promote cleanliness and environmental responsibility.',
                'Target Community': 'Barangay Dau',
                'Date and Time': 'November 25, 2026 \u2022 6:00 AM \u2013 10:00 AM',
                'Expected Participants': '60 volunteers',
                'Person-in-Charge': 'Pedro Reyes'
            },
            venue: {
                'Venue Name / Location': 'Barangay Dau Main Road',
                'Reservation Status': 'Not required'
            },
            materials: [
                { item: 'Trash Bags', qty: 200, status: 'Requested' },
                { item: 'Gloves', qty: 60, status: 'Available' }
            ],
            logistics: {}, safety: {}, docs: {}
        },
        {
            id: 3, name: 'Literacy Outreach', date: '2026-12-02', status: 'Planning', progress: 15,
            info: {
                'Activity Title': 'Literacy Outreach',
                'Extension Program': 'Basic Literacy and Digital Skills',
                'Activity Description': 'Reading sessions and learning activities for elementary learners.',
                'Objectives': 'Improve reading skills and learner engagement.',
                'Target Community': 'Barangay Poblacion',
                'Date and Time': 'December 2, 2026 \u2022 1:00 PM \u2013 4:00 PM',
                'Expected Participants': '40 learners',
                'Person-in-Charge': 'Maria Santos'
            },
            venue: { 'Venue Name / Location': 'Barangay Library', 'Reservation Status': 'Pending' },
            materials: [
                { item: 'Story Books', qty: 80, status: 'Requested' },
                { item: 'Writing Materials', qty: 40, status: 'Requested' }
            ],
            logistics: {}, safety: {}, docs: {}
        },
        {
            id: 4, name: 'Health and Wellness Caravan', date: '2026-12-10', status: 'Ready', progress: 100,
            info: {
                'Activity Title': 'Health and Wellness Caravan',
                'Extension Program': 'Community Health Program',
                'Activity Description': 'Free health screening, consultation, and wellness talks.',
                'Objectives': 'Provide accessible basic health services.',
                'Target Community': 'Barangay Camachiles',
                'Date and Time': 'December 10, 2026 \u2022 8:00 AM \u2013 3:00 PM',
                'Expected Participants': '150 residents',
                'Person-in-Charge': 'Pedro Reyes'
            },
            venue: {
                'Venue Name / Location': 'Barangay Hall',
                'Reservation Date': 'November 15, 2026',
                'Venue Capacity': '200 persons',
                'Reservation Status': 'Confirmed'
            },
            materials: [
                { item: 'Blood Pressure Monitors', qty: 6, status: 'Available' },
                { item: 'Chairs', qty: 40, status: 'Available' }
            ],
            logistics: {}, safety: {}, docs: {}
        }
    ];

    // Overall summary figures (as shown in the UI concept)
    var stats = { total: 12, upcoming: 4, ongoing: 2, completed: 6 };
    var nextId = 5;

    var $ = function (sel) { return root.querySelector(sel); };
    var tbody = $('#aemTableBody');
    var searchEl = $('#aemSearch');
    var statusEl = $('#aemStatusFilter');
    var filtersEl = $('#aemFilters');
    var filterBtn = $('#aemFilterToggle');
    var drawer = $('#aemDrawer');
    var addModal = $('#aemAddModal');
    var form = $('#aemAddForm');
    var errorEl = $('#aemFormError');
    var lastFocus = null;

    // ---------- Helpers ----------
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function slug(s) { return String(s || '').toLowerCase().replace(/\s+/g, '-'); }

    function pill(label) {
        var known = ['ready', 'in-preparation', 'planning', 'available', 'requested', 'reserved'];
        var key = slug(label);
        var cls = known.indexOf(key) > -1 ? key : 'default';
        return '<span class="aem-status aem-status-' + cls + '">' + esc(label || '\u2014') + '</span>';
    }

    function parseDate(iso) {
        var p = String(iso || '').split('-');
        return p.length === 3 ? new Date(+p[0], +p[1] - 1, +p[2]) : null;
    }

    function fmtShort(iso) {
        var d = parseDate(iso);
        return d ? d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '\u2014';
    }

    function fmtLong(iso) {
        var d = parseDate(iso);
        return d ? d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '\u2014';
    }

    function find(id) {
        for (var i = 0; i < activities.length; i++) if (activities[i].id === id) return activities[i];
        return null;
    }

    // ---------- Stats ----------
    function renderStats() {
        Object.keys(stats).forEach(function (k) {
            var el = $('[data-aem-stat="' + k + '"]');
            if (el) el.textContent = stats[k];
        });
    }

    // ---------- Table ----------
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
            tbody.innerHTML = '<tr><td colspan="4" class="aem-empty">No activities found.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (a) {
            return '<tr>' +
                '<td>' + esc(a.name) + '</td>' +
                '<td>' + fmtShort(a.date) + '</td>' +
                '<td>' + pill(a.status) + '</td>' +
                '<td><button type="button" class="aem-btn-link" data-view="' + a.id + '">View Details</button></td>' +
                '</tr>';
        }).join('');
    }

    // ---------- Drawer rendering ----------
    function kvSection(letter, title, fields, data) {
        data = data || {};
        var rows = fields.map(function (f) {
            var val = data[f];
            return '<div class="aem-kv-row"><dt>' + esc(f) + '</dt>' +
                (val ? '<dd>' + esc(val) + '</dd>' : '<dd class="is-empty">Not yet provided</dd>') + '</div>';
        }).join('');
        return '<div class="aem-section"><h3>' + letter + '. ' + esc(title) + '</h3><dl class="aem-kv">' + rows + '</dl></div>';
    }

    function materialsSection(list) {
        var body = list && list.length
            ? list.map(function (m) {
                return '<tr><td>' + esc(m.item) + '</td><td>' + esc(m.qty) + '</td><td>' + pill(m.status) + '</td></tr>';
            }).join('')
            : '<tr><td colspan="3" class="aem-empty">No materials listed yet.</td></tr>';
        return '<div class="aem-section"><h3>C. Materials and Procurement</h3>' +
            '<div class="aem-table-wrap"><table class="aem-table"><thead><tr><th>Item</th><th>Quantity</th><th>Status</th></tr></thead>' +
            '<tbody>' + body + '</tbody></table></div></div>';
    }

    function openDrawer(id) {
        var a = find(id);
        if (!a) return;

        $('#aemDrawerTitle').textContent = a.name;
        $('#aemDrawerStatus').innerHTML = pill(a.status);
        $('#aemDrawerSub').textContent = 'Extension Activity \u2022 ' + fmtLong(a.date);

        $('#aemDrawerBody').innerHTML =
            kvSection('A', 'Activity Information and Planning', INFO_FIELDS, a.info) +
            kvSection('B', 'Venue and Facilities', VENUE_FIELDS, a.venue) +
            materialsSection(a.materials) +
            kvSection('D', 'Logistics and Coordination', LOGISTICS_FIELDS, a.logistics) +
            kvSection('E', 'Safety and Risk Management', SAFETY_FIELDS, a.safety) +
            kvSection('F', 'Activity Documentation', DOC_FIELDS, a.docs) +
            '<div class="aem-section"><h3>Activity Execution Status</h3>' +
            '<div class="aem-progress-head"><span>Preparation Progress</span><span>' + a.progress + '%</span></div>' +
            '<div class="aem-progress-wrap"><div class="aem-progress-bar" style="width:' + a.progress + '%"></div></div>' +
            '<p class="aem-drawer-note">Illustrative activity details and sample data.</p></div>';

        $('#aemDrawerBody').scrollTop = 0;
        lastFocus = document.activeElement;
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        $('#aemDrawerClose').focus();
    }

    function closeDrawer() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    // ---------- Modal ----------
    function openModal(el) { el.classList.add('is-open'); el.setAttribute('aria-hidden', 'false'); }
    function closeModal(el) { el.classList.remove('is-open'); el.setAttribute('aria-hidden', 'true'); }

    // ---------- Events ----------
    root.addEventListener('click', function (e) {
        var t = e.target;

        if (t.hasAttribute('data-aem-close')) {
            if (addModal.classList.contains('is-open')) closeModal(addModal);
            else closeDrawer();
            return;
        }

        var viewBtn = t.closest('[data-view]');
        if (viewBtn) openDrawer(parseInt(viewBtn.getAttribute('data-view'), 10));
    });

    if (escHandler) document.removeEventListener('keydown', escHandler);
    escHandler = function (e) {
        if (e.key !== 'Escape') return;
        if (addModal.classList.contains('is-open')) closeModal(addModal);
        else if (drawer.classList.contains('is-open')) closeDrawer();
    };
    document.addEventListener('keydown', escHandler);

    filterBtn.addEventListener('click', function () {
        var open = filtersEl.hasAttribute('hidden');
        if (open) filtersEl.removeAttribute('hidden'); else filtersEl.setAttribute('hidden', '');
        filterBtn.setAttribute('aria-expanded', String(open));
    });

    [searchEl, statusEl].forEach(function (el) {
        el.addEventListener('input', renderTable);
        el.addEventListener('change', renderTable);
    });

    $('#aemOpenAdd').addEventListener('click', function () {
        form.reset();
        errorEl.setAttribute('hidden', '');
        openModal(addModal);
        $('#aemFName').focus();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = $('#aemFName').value.trim();
        var date = $('#aemFDate').value;
        if (!name || !date) { errorEl.removeAttribute('hidden'); return; }

        var status = $('#aemFStatus').value;
        var progressByStatus = { 'Planning': 10, 'In Preparation': 50, 'Ready': 100 };

        activities.push({
            id: nextId++, name: name, date: date, status: status, progress: progressByStatus[status] || 0,
            info: {
                'Activity Title': name,
                'Extension Program': $('#aemFProgram').value.trim(),
                'Date and Time': fmtLong(date),
                'Person-in-Charge': $('#aemFPic').value.trim()
            },
            venue: { 'Venue Name / Location': $('#aemFVenue').value.trim() },
            materials: [], logistics: {}, safety: {}, docs: {}
        });

        stats.total += 1;
        stats.upcoming += 1;

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