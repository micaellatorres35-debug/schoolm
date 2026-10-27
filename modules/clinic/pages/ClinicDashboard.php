<?php
require_once __DIR__ . '/../classes/ClinicDashboard.php';

$dashboard    = new ClinicDashboard();
$patientCount = $dashboard->getPatientCount();
$medSum       = $dashboard->getMedicationSummary();
$alerts       = $dashboard->getMedicationAlerts(6);
?>

<div class="module-header">
    <h1>Clinic Dashboard Overview</h1>
</div>

<div class="module-content">

    <!-- Module cards -->
    <div class="student-info">
        <h2>Modules</h2>
        <div class="stat-row" style="margin-bottom:0;">
            <a href="?page=patientinformationmanagement" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card">
                    <strong><?= $patientCount === null ? '-' : $patientCount ?></strong>
                    <span>Patients on record</span>
                </div>
            </a>
            <a href="?page=clinicvisitandconsultation" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card">
                    <strong id="visitsToday">-</strong>
                    <span>Consultations today</span><br>
                    <span id="visitsTotal">Loading…</span>
                </div>
            </a>
            <a href="?page=incidentandemergencymanagement" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card danger">
                    <strong id="incOpen">-</strong>
                    <span>Open gate passes</span><br>
                    <span id="incTotal">Loading…</span>
                </div>
            </a>
            <a href="?page=medicationinventory" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card warn">
                    <strong><?= $medSum['low'] + $medSum['out_of_stock'] ?></strong>
                    <span>Medicines to restock</span><br>
                    <span><?= $medSum['total'] ?> in inventory</span>
                </div>
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/announcements.php'; ?>

    <!-- Visits by type + Medication stock -->
    <div class="form-row">
        <div class="form-col student-info" style="margin-bottom:0;">
            <h2>Recent visits by type</h2>
            <div id="visitTypes"><span class="pagination-info">Loading…</span></div>
        </div>
        <div class="form-col student-info" style="margin-bottom:0;">
            <h2>Medication stock</h2>
            <div class="stat-row" style="margin-bottom:0;">
                <div class="stat-card warn"><strong><?= $medSum['low'] ?></strong><span>Low</span></div>
                <div class="stat-card danger"><strong><?= $medSum['out_of_stock'] ?></strong><span>Out</span></div>
                <div class="stat-card warn"><strong><?= $medSum['expiring'] ?></strong><span>Expiring</span></div>
                <div class="stat-card danger"><strong><?= $medSum['expired'] ?></strong><span>Expired</span></div>
            </div>
        </div>
    </div>

    <!-- Recent consultations -->
    <div class="table-info" style="margin-top:25px;">
        <span>Latest consultations</span>
        <a class="page-link" href="?page=clinicvisitandconsultation">Open module</a>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Patient</th><th>Visit type</th><th>Nurse</th><th>Date &amp; time</th><th>Diagnosis</th></tr>
            </thead>
            <tbody id="recentVisits">
                <tr><td colspan="5" class="no-data">Loading…</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Latest gate passes -->
    <div class="table-info" style="margin-top:25px;">
        <span>Latest gate passes</span>
        <a class="page-link" href="?page=incidentandemergencymanagement">Open module</a>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Student</th><th>Reason</th><th>Date</th><th>Guardian</th><th>Status</th></tr>
            </thead>
            <tbody id="recentIncidents">
                <tr><td colspan="5" class="no-data">Loading…</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Medicines needing attention -->
    <div class="table-info" style="margin-top:25px;">
        <span>Medicines that need attention</span>
        <a class="page-link" href="?page=medicationinventory">Open module</a>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Medicine</th><th>Stock</th><th>Expiry</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$alerts): ?>
                <tr><td colspan="4" class="no-data">All medicines are stocked and within expiry.</td></tr>
            <?php else: foreach ($alerts as $row):
                [$label, $class] = Medication::getStatus($row);
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['medicine_name']) ?></strong>
                        <?php if ($row['strength']): ?> <?= htmlspecialchars($row['strength']) ?><?php endif; ?></td>
                    <td><?= (int) $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td><?= $row['expiry_date'] ? date('M d, Y', strtotime($row['expiry_date'])) : '-' ?></td>
                    <td><span class="badge <?= $class ?>"><?= $label ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    'use strict';

    // Same storage key used by the Incident & Emergency page
    const GATEPASS_KEY = 'bcp_gatepass_v3';

    function esc(v) {
        if (v === null || v === undefined) return '';
        const d = document.createElement('div');
        d.textContent = String(v);
        return d.innerHTML;
    }
    function localToday() {
        const n = new Date();
        return n.getFullYear() + '-' + String(n.getMonth() + 1).padStart(2, '0') + '-' + String(n.getDate()).padStart(2, '0');
    }
    function statusBadge(status) {
        const cls = status === 'Resolved' ? 'badge-success' : (status === 'In Progress' ? 'badge-warning' : 'badge-danger');
        return '<span class="badge ' + cls + '">' + esc(status) + '</span>';
    }

    /* ---------- Clinic visits (same handler the Consultation page uses) ---------- */
    async function loadVisits() {
        const body = document.getElementById('recentVisits');
        try {
            const params = new URLSearchParams({ page: 1, search: '', limit: 50 });
            const res = await fetch('../handlers/get_consultations.php?' + params);
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Failed');

            const rows  = json.data || [];
            const today = localToday();

            document.getElementById('visitsToday').textContent =
                rows.filter(function (r) { return r.appointment_date === today; }).length;
            document.getElementById('visitsTotal').textContent = (json.total || 0) + ' total records';

            // Visit-type bars (from the latest 50)
            const counts = {};
            rows.forEach(function (r) { counts[r.visit_type] = (counts[r.visit_type] || 0) + 1; });
            const types = Object.keys(counts).sort(function (a, b) { return counts[b] - counts[a]; });
            const max = types.length ? counts[types[0]] : 1;
            document.getElementById('visitTypes').innerHTML = types.length
                ? types.map(function (t) {
                    return '<div style="margin-bottom:10px;">' +
                        '<div style="display:flex;justify-content:space-between;font-size:13px;color:#495057;">' +
                        '<span>' + esc(t) + '</span><strong>' + counts[t] + '</strong></div>' +
                        '<div style="background:#e9ecef;border-radius:4px;height:8px;">' +
                        '<div style="background:#667eea;border-radius:4px;height:8px;width:' + (counts[t] / max * 100) + '%;"></div>' +
                        '</div></div>';
                  }).join('')
                : '<span class="pagination-info">No visits recorded yet.</span>';

            body.innerHTML = rows.length
                ? rows.slice(0, 5).map(function (r) {
                    return '<tr><td><strong>' + esc(r.patient_name) + '</strong></td>' +
                        '<td>' + esc(r.visit_type) + '</td>' +
                        '<td>' + esc(r.nurse) + '</td>' +
                        '<td>' + esc(r.appointment_date) + ' ' + esc(r.appointment_time || '') + '</td>' +
                        '<td>' + (r.diagnosis ? esc(String(r.diagnosis).substring(0, 50)) : '-') + '</td></tr>';
                  }).join('')
                : '<tr><td colspan="5" class="no-data">No consultations yet.</td></tr>';
        } catch (err) {
            document.getElementById('visitsTotal').textContent = 'Could not load';
            document.getElementById('visitTypes').innerHTML = '<span class="pagination-info">Could not load visit data.</span>';
            body.innerHTML = '<tr><td colspan="5" class="no-data">Could not load consultations (' + esc(err.message) + ').</td></tr>';
        }
    }

    /* ---------- Gate passes (stored in this browser's localStorage) ---------- */
    function loadIncidents() {
        const body = document.getElementById('recentIncidents');
        let list = [];
        try { list = JSON.parse(localStorage.getItem(GATEPASS_KEY)) || []; } catch (e) { list = []; }

        const open = list.filter(function (i) { return i.status !== 'Resolved'; }).length;
        document.getElementById('incOpen').textContent = open;
        document.getElementById('incTotal').textContent = list.length + ' total on this browser';

        list.sort(function (a, b) { return b.id - a.id; });
        body.innerHTML = list.length
            ? list.slice(0, 5).map(function (i) {
                return '<tr><td><strong>' + esc(i.student_name) + '</strong><br><small>' + esc(i.student_id) + '</small></td>' +
                    '<td>' + esc(i.incident_type) + '</td>' +
                    '<td>' + esc(i.visit_date || '-') + '</td>' +
                    '<td>' + esc(i.guardian || '-') + '</td>' +
                    '<td>' + statusBadge(i.status) + '</td></tr>';
              }).join('')
            : '<tr><td colspan="5" class="no-data">No gate passes saved on this browser.</td></tr>';
    }

    loadVisits();
    loadIncidents();
})();
</script>