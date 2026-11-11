(function () {
    'use strict';

    var escHandler = null;

    function init() {
    var root = document.getElementById('vmmRoot');
    if (!root || root.dataset.vmmInit === '1') return;   // not on this page, or already initialised
    root.dataset.vmmInit = '1';

    // Sample data (replace with a fetch() to your PHP/API endpoint later)
    var volunteers = [
        {
            id: 1, name: 'Juan Dela Cruz', type: 'Student', info: 'BSIS \u2022 2nd Year',
            status: 'Assigned', activity: 'Community Feeding Program', task: 'Distribution Team',
            attendance: 'Present', time: '8:00 AM \u2013 12:00 PM', hours: 4,
            email: 'juan.delacruz@example.com', contact: '0917 000 0001'
        },
        {
            id: 2, name: 'Maria Santos', type: 'Faculty', info: 'College of Education',
            status: 'Available', activity: '', task: '',
            attendance: '', time: '', hours: 0,
            email: 'maria.santos@example.com', contact: '0917 000 0002'
        },
        {
            id: 3, name: 'Pedro Reyes', type: 'Student', info: 'BSIT \u2022 3rd Year',
            status: 'Assigned', activity: 'Literacy Outreach', task: 'Teaching Assistant',
            attendance: 'Present', time: '1:00 PM \u2013 4:00 PM', hours: 3,
            email: 'pedro.reyes@example.com', contact: '0917 000 0003'
        }
    ];

    var selectedId = volunteers[0].id;
    var nextId = 4;

    var $ = function (sel) { return root.querySelector(sel); };
    var tbody = $('#vmmTableBody');
    var searchEl = $('#vmmSearch');
    var typeEl = $('#vmmTypeFilter');
    var statusEl = $('#vmmStatusFilter');
    var filtersEl = $('#vmmFilters');
    var filterBtn = $('#vmmFilterToggle');
    var detailsBody = $('#vmmDetailsBody');

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function pill(label) {
        if (!label) return '<span class="vmm-status vmm-status-default">\u2014</span>';
        var key = String(label).toLowerCase();
        var cls = ['assigned', 'available', 'present'].indexOf(key) > -1 ? key : 'default';
        return '<span class="vmm-status vmm-status-' + cls + '">' + esc(label) + '</span>';
    }

    function getFiltered() {
        var q = searchEl.value.trim().toLowerCase();
        return volunteers.filter(function (v) {
            return (!q || v.name.toLowerCase().indexOf(q) > -1) &&
                (typeEl.value === 'All' || v.type === typeEl.value) &&
                (statusEl.value === 'All' || v.status === statusEl.value);
        });
    }

    function renderTable() {
        var rows = getFiltered();
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="vmm-empty">No volunteers found.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (v) {
            return '<tr data-id="' + v.id + '" class="' + (v.id === selectedId ? 'is-selected' : '') + '">' +
                '<td>' + esc(v.name) + '</td>' +
                '<td>' + esc(v.type) + '</td>' +
                '<td>' + pill(v.status) + '</td>' +
                '<td><button type="button" class="vmm-btn-link" data-view="' + v.id + '">View</button></td>' +
                '</tr>';
        }).join('');
    }

    function find(id) {
        for (var i = 0; i < volunteers.length; i++) if (volunteers[i].id === id) return volunteers[i];
        return null;
    }

    function dash(v) { return v ? esc(v) : '\u2014'; }

    function renderDetails() {
        var v = find(selectedId);
        if (!v) {
            detailsBody.innerHTML = '<p class="vmm-empty">Select a volunteer to view details.</p>';
            return;
        }
        detailsBody.innerHTML =
            '<div class="vmm-person"><strong>' + esc(v.name) + '</strong>' +
            '<span>' + esc(v.type) + ' \u2022 ' + esc(v.info) + '</span></div>' +
            '<dl class="vmm-details-list">' +
            '<div class="vmm-detail-row"><dt>Extension Activity</dt><dd>' + dash(v.activity) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Task Assignment</dt><dd>' + dash(v.task) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Attendance</dt><dd>' + (v.attendance ? pill(v.attendance) : '\u2014') + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Time In / Out</dt><dd>' + dash(v.time) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Hours Rendered</dt><dd>' + (v.hours ? v.hours + ' Hours' : '\u2014') + '</dd></div>' +
            '</dl>' +
            '<button type="button" class="vmm-btn vmm-btn-secondary vmm-btn-block" data-full="' + v.id + '">View Full Details</button>';
    }

    function select(id) {
        selectedId = id;
        renderTable();
        renderDetails();
    }

    // ----- Modals -----
    function openModal(el) { el.classList.add('is-open'); el.setAttribute('aria-hidden', 'false'); }
    function closeModal(el) { el.classList.remove('is-open'); el.setAttribute('aria-hidden', 'true'); }
    function closeAll() {
        root.querySelectorAll('.vmm-modal.is-open').forEach(closeModal);
    }

    root.addEventListener('click', function (e) {
        var t = e.target;

        if (t.hasAttribute('data-vmm-close')) { closeAll(); return; }

        var viewBtn = t.closest('[data-view]');
        if (viewBtn) { select(parseInt(viewBtn.getAttribute('data-view'), 10)); return; }

        var fullBtn = t.closest('[data-full]');
        if (fullBtn) { showFull(parseInt(fullBtn.getAttribute('data-full'), 10)); return; }

        var row = t.closest('#vmmTableBody tr[data-id]');
        if (row) select(parseInt(row.getAttribute('data-id'), 10));
    });

    if (escHandler) document.removeEventListener('keydown', escHandler);
    escHandler = function (e) {
        if (e.key === 'Escape') closeAll();
    };
    document.addEventListener('keydown', escHandler);

    function showFull(id) {
        var v = find(id);
        if (!v) return;
        $('#vmmFullBody').innerHTML =
            '<dl class="vmm-details-list">' +
            '<div class="vmm-detail-row"><dt>Name</dt><dd>' + esc(v.name) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Type</dt><dd>' + esc(v.type) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Program / Office</dt><dd>' + dash(v.info) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Email</dt><dd>' + dash(v.email) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Contact No.</dt><dd>' + dash(v.contact) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Status</dt><dd>' + pill(v.status) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Extension Activity</dt><dd>' + dash(v.activity) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Task Assignment</dt><dd>' + dash(v.task) + '</dd></div>' +
            '<div class="vmm-detail-row"><dt>Hours Rendered</dt><dd>' + (v.hours ? v.hours + ' Hours' : '\u2014') + '</dd></div>' +
            '</dl>';
        openModal($('#vmmFullModal'));
    }

    // ----- Filters -----
    filterBtn.addEventListener('click', function () {
        var open = filtersEl.hasAttribute('hidden');
        if (open) filtersEl.removeAttribute('hidden'); else filtersEl.setAttribute('hidden', '');
        filterBtn.setAttribute('aria-expanded', String(open));
    });
    [searchEl, typeEl, statusEl].forEach(function (el) {
        el.addEventListener('input', renderTable);
        el.addEventListener('change', renderTable);
    });

    // ----- Register volunteer -----
    var registerModal = $('#vmmRegisterModal');
    var form = $('#vmmRegisterForm');
    var errorEl = $('#vmmFormError');

    $('#vmmOpenRegister').addEventListener('click', function () {
        form.reset();
        errorEl.setAttribute('hidden', '');
        openModal(registerModal);
        $('#vmmFName').focus();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = $('#vmmFName').value.trim();
        if (!name) { errorEl.removeAttribute('hidden'); return; }

        volunteers.push({
            id: nextId++, name: name, type: $('#vmmFType').value, info: $('#vmmFInfo').value.trim(),
            status: 'Available', activity: '', task: '', attendance: '', time: '', hours: 0,
            email: $('#vmmFEmail').value.trim(), contact: $('#vmmFContact').value.trim()
        });

        var totalEl = $('[data-vmm-stat="total"]');
        totalEl.textContent = (parseInt(totalEl.textContent, 10) || 0) + 1;

        closeModal(registerModal);
        selectedId = volunteers[volunteers.length - 1].id;
        renderTable();
        renderDetails();
    });

    renderTable();
    renderDetails();
    }

    // Run on first load AND every time the page loader injects this page
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    window.addEventListener('page:loaded', init);
})();