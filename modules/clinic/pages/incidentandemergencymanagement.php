<?php
include_once __DIR__ . '/../../../database/db.php';
include_once __DIR__ . '/../classes/student.php';

$studentClass = new Student();
try {
    $allStudents = $studentClass->getAllStudents();
} catch (Throwable $e) {
    $allStudents = [];
}
if (!$allStudents) $allStudents = [];
?>

<div class="module-header">
    <h1>Incident &amp; Emergency &mdash; Medical Gate Pass</h1>
</div>

<div class="module-content">

    <div id="gpMessage" class="message"></div>

    <!-- Student lookup -->
    <div class="form-group">
        <label for="gpStudentNumber">Student Number:</label>
        <div class="search-container">
            <input type="text" id="gpStudentNumber" class="search-input" placeholder="Enter student number" autocomplete="off">
            <button type="button" id="gpSearchBtn" class="btn-primary">Search</button>
            <button type="button" id="gpClearBtn" class="btn-secondary">Clear</button>
        </div>
    </div>

    <div class="form-group">
        <label for="gpStudentSelect">Or select from list:</label>
        <select id="gpStudentSelect" class="search-select" style="width:100%;">
            <option value="">-- Select Student --</option>
            <?php foreach ($allStudents as $s): ?>
                <option value="<?= htmlspecialchars($s['student_number']) ?>">
                    <?= htmlspecialchars($s['student_number']) ?> -
                    <?= htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Gate pass form -->
    <div class="student-info">
        <h2 id="gpFormTitle">New gate pass</h2>
        <form id="gpForm" novalidate>
            <input type="hidden" id="incident_id">

            <div class="form-row">
                <div class="form-col">
                    <label for="student_id">Student number</label>
                    <input type="text" id="student_id" readonly placeholder="Search a student first">
                </div>
                <div class="form-col">
                    <label for="student_name">Student name</label>
                    <input type="text" id="student_name" readonly>
                </div>
                <div class="form-col">
                    <label for="course">Course / Year</label>
                    <input type="text" id="course" readonly>
                </div>
                <div class="form-col">
                    <label for="visit_date">Date of visit</label>
                    <input type="date" id="visit_date">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="incident_type">Reason<span class="required">*</span></label>
                    <input type="text" id="incident_type" placeholder="e.g. Fever, Sprain">
                </div>
                <div class="form-col">
                    <label for="incident_ref">Incident reference no.</label>
                    <input type="text" id="incident_ref" placeholder="e.g. A-1024">
                </div>
                <div class="form-col">
                    <label for="time_allowed">Time allowed to leave</label>
                    <input type="text" id="time_allowed" placeholder="e.g. 2:30 PM">
                </div>
                <div class="form-col">
                    <label for="reported_by">Reported by</label>
                    <input type="text" id="reported_by" placeholder="Staff / faculty name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="Open">Open - Under assessment</option>
                        <option value="In Progress">In Progress - Attending</option>
                        <option value="Resolved">Resolved - Gate pass issued</option>
                    </select>
                </div>
                <div class="form-col">
                    <label for="guardian_notified">Guardian notified</label>
                    <select id="guardian_notified">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-col">
                    <label for="resolved_date">Resolved date</label>
                    <input type="datetime-local" id="resolved_date">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-full">
                    <label for="description">Description / notes</label>
                    <textarea id="description" rows="3" placeholder="Medical concern, observations, instructions given"></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="gpPdfFormBtn" class="btn-secondary" style="display:none;">Export as PDF</button>
                <button type="button" id="gpCancelBtn" class="btn-secondary" style="display:none;">Cancel edit</button>
                <button type="submit" id="gpSaveBtn" class="btn-primary">Save gate pass</button>
            </div>
        </form>
    </div>

    <!-- Records -->
    <div class="table-info">
        <span id="gpCount">0 gate passes</span>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Reason</th>
                    <th>Time leave</th>
                    <th>Reported by</th>
                    <th>Date</th>
                    <th>Guardian</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="gpTbody">
                <tr><td colspan="10" class="no-data">No gate passes yet.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- PDF preview modal -->
<div id="gpModal" class="gp-modal" style="display:none;">
    <div class="gp-modal-box">
        <h3>Gate pass PDF preview</h3>
        <pre id="gpPreview" class="gp-preview"></pre>
        <div class="form-actions">
            <button type="button" id="gpModalCancel" class="btn-secondary">Cancel</button>
            <button type="button" id="gpModalDownload" class="btn-primary">Download PDF</button>
        </div>
    </div>
</div>

<style>
.gp-modal { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; display: flex; align-items: center; justify-content: center; }
.gp-modal-box { background: #fff; border-radius: 10px; padding: 20px; width: 640px; max-width: 95vw; max-height: 90vh; overflow-y: auto; }
.gp-modal-box h3 { margin-top: 0; color: #495057; }
.gp-preview { background: #f8f9fa; border-radius: 8px; padding: 14px; font-size: 12px; white-space: pre-wrap; border-left: 4px solid #667eea; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // Same storage key as before, so existing records and the dashboard keep working
    const STORAGE_KEY = 'bcp_gatepass_v3';
    const $ = function (id) { return document.getElementById(id); };
    let pdfRecord = null;

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
    function showMsg(type, text) {
        const el = $('gpMessage');
        el.className = 'message ' + type;
        el.textContent = text;
        el.style.display = 'block';
        clearTimeout(showMsg.t);
        showMsg.t = setTimeout(function () { el.style.display = 'none'; }, 4000);
    }

    /* ---------- Storage ---------- */
    function getData() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch (e) { return []; }
    }
    function saveData(arr) { localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); }
    function nextId(arr) { return arr.length ? Math.max.apply(null, arr.map(function (i) { return i.id; })) + 1 : 1001; }

    /* ---------- Student lookup (same handler as Patient Information) ---------- */
    function clearStudent() {
        ['student_id', 'student_name', 'course'].forEach(function (id) { $(id).value = ''; });
    }

    function lookupStudent(num) {
        num = (num || '').trim();
        if (!num) { showMsg('error', 'Enter or select a student number.'); return; }

        fetch('../handlers/get_student.php?student_number=' + encodeURIComponent(num))
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (s) {
                if (!s || s.error) {
                    clearStudent();
                    showMsg('error', 'Student not found.');
                    return;
                }
                const yr = s.year_level ? ' - Year ' + s.year_level : '';
                $('student_id').value = s.student_number || num;
                $('student_name').value = ((s.first_name || '') + ' ' + (s.last_name || '')).trim();
                $('course').value = (s.course || '') + yr;
                $('gpStudentNumber').value = s.student_number || num;
                showMsg('success', 'Student loaded.');
            })
            .catch(function () {
                clearStudent();
                showMsg('error', 'Could not load student. Please try again.');
            });
    }

    $('gpSearchBtn').addEventListener('click', function () { lookupStudent($('gpStudentNumber').value); });
    $('gpStudentNumber').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); lookupStudent(this.value); }
    });
    $('gpStudentSelect').addEventListener('change', function () {
        if (this.value) lookupStudent(this.value); else clearStudent();
    });
    $('gpClearBtn').addEventListener('click', function () {
        $('gpStudentNumber').value = '';
        $('gpStudentSelect').value = '';
        clearStudent();
    });

    /* ---------- Form ---------- */
    const formFields = ['incident_type', 'incident_ref', 'time_allowed', 'reported_by', 'description', 'resolved_date'];

    function resetForm() {
        $('incident_id').value = '';
        clearStudent();
        $('gpStudentNumber').value = '';
        $('gpStudentSelect').value = '';
        formFields.forEach(function (id) { $(id).value = ''; });
        $('visit_date').value = localToday();
        $('status').value = 'Open';
        $('guardian_notified').value = 'Yes';
        $('gpFormTitle').textContent = 'New gate pass';
        $('gpSaveBtn').textContent = 'Save gate pass';
        $('gpCancelBtn').style.display = 'none';
        $('gpPdfFormBtn').style.display = 'none';
    }

    $('gpCancelBtn').addEventListener('click', resetForm);

    $('gpForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const sid = $('student_id').value.trim();
        const name = $('student_name').value.trim();
        const reason = $('incident_type').value.trim();

        if (!sid || !name) { showMsg('error', 'Search and load a student first.'); return; }
        if (!reason) { showMsg('error', 'Reason is required.'); return; }

        const incId = $('incident_id').value;
        const record = {
            id: incId ? parseInt(incId, 10) : null,
            student_id: sid,
            student_name: name,
            course: $('course').value.trim(),
            visit_date: $('visit_date').value,
            incident_type: reason,
            incident_ref: $('incident_ref').value.trim(),
            time_allowed: $('time_allowed').value.trim() || '-',
            reported_by: $('reported_by').value.trim(),
            status: $('status').value,
            guardian: $('guardian_notified').value,
            resolved_date: $('resolved_date').value || null,
            description: $('description').value.trim(),
            date_reported: new Date().toISOString()
        };

        const arr = getData();
        if (record.id) {
            const idx = arr.findIndex(function (i) { return i.id === record.id; });
            if (idx !== -1) arr[idx] = record;
        } else {
            record.id = nextId(arr);
            arr.push(record);
        }
        saveData(arr);
        renderTable();
        showMsg('success', incId ? 'Gate pass updated.' : 'Gate pass saved.');
        resetForm();
    });

    /* ---------- Table ---------- */
    function renderTable() {
        const arr = getData().slice().sort(function (a, b) { return b.id - a.id; });
        const tbody = $('gpTbody');
        $('gpCount').textContent = arr.length + ' gate pass' + (arr.length === 1 ? '' : 'es');

        if (!arr.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="no-data">No gate passes yet.</td></tr>';
            return;
        }

        tbody.innerHTML = arr.map(function (r) {
            const date = r.visit_date ? new Date(r.visit_date + 'T12:00:00')
                .toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
            const cls = r.status === 'Resolved' ? 'badge-success' : (r.status === 'In Progress' ? 'badge-warning' : 'badge-danger');
            return '<tr>' +
                '<td>' + r.id + '</td>' +
                '<td><strong>' + esc(r.student_name) + '</strong><br><small>' + esc(r.student_id) + '</small></td>' +
                '<td>' + esc(r.course || '-') + '</td>' +
                '<td>' + esc(r.incident_type) + (r.incident_ref ? '<br><small>' + esc(r.incident_ref) + '</small>' : '') + '</td>' +
                '<td>' + esc(r.time_allowed) + '</td>' +
                '<td>' + esc(r.reported_by || '-') + '</td>' +
                '<td>' + esc(date) + '</td>' +
                '<td>' + esc(r.guardian) + '</td>' +
                '<td><span class="badge ' + cls + '">' + esc(r.status) + '</span></td>' +
                '<td class="action-cell">' +
                    '<button type="button" class="btn-edit" data-action="pdf" data-id="' + r.id + '">PDF</button>' +
                    '<button type="button" class="btn-edit" data-action="edit" data-id="' + r.id + '">Edit</button>' +
                    '<button type="button" class="btn-delete" data-action="del" data-id="' + r.id + '">Delete</button>' +
                '</td></tr>';
        }).join('');
    }

    $('gpTbody').addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const action = btn.getAttribute('data-action');
        if (action === 'edit') editRecord(id);
        else if (action === 'pdf') openPdf(getData().find(function (i) { return i.id === id; }));
        else if (action === 'del') {
            if (!confirm('Delete this gate pass? This cannot be undone.')) return;
            saveData(getData().filter(function (i) { return i.id !== id; }));
            renderTable();
            showMsg('success', 'Gate pass deleted.');
        }
    });

    function editRecord(id) {
        const r = getData().find(function (i) { return i.id === id; });
        if (!r) return;
        $('incident_id').value = r.id;
        $('student_id').value = r.student_id || '';
        $('student_name').value = r.student_name || '';
        $('course').value = r.course || '';
        $('gpStudentNumber').value = r.student_id || '';
        $('visit_date').value = r.visit_date || '';
        $('incident_type').value = r.incident_type || '';
        $('incident_ref').value = r.incident_ref || '';
        $('time_allowed').value = r.time_allowed === '-' ? '' : (r.time_allowed || '');
        $('reported_by').value = r.reported_by || '';
        $('status').value = r.status || 'Open';
        $('guardian_notified').value = r.guardian || 'Yes';
        $('resolved_date').value = r.resolved_date ? r.resolved_date.slice(0, 16) : '';
        $('description').value = r.description || '';
        $('gpFormTitle').textContent = 'Edit gate pass #' + r.id;
        $('gpSaveBtn').textContent = 'Save changes';
        $('gpCancelBtn').style.display = '';
        $('gpPdfFormBtn').style.display = '';
        $('gpForm').scrollIntoView({ behavior: 'smooth' });
    }

    /* ---------- PDF ---------- */
    function longDate(d) {
        const date = d ? new Date(d + 'T12:00:00') : new Date();
        return date.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function recordFromForm() {
        return {
            student_id: $('student_id').value || '_____',
            student_name: $('student_name').value || '[Student Name]',
            course: $('course').value || '[Course]',
            visit_date: $('visit_date').value || localToday(),
            incident_type: $('incident_type').value || '[Medical Concern]',
            incident_ref: $('incident_ref').value || '-',
            time_allowed: $('time_allowed').value || '-',
            guardian: $('guardian_notified').value || 'Yes',
            description: $('description').value || ''
        };
    }

    function openPdf(rec) {
        if (!rec) return;
        pdfRecord = rec;
        $('gpPreview').textContent =
            'BESTLINK COLLEGE OF THE PHILIPPINES\n' +
            'IPO Road, Brgy. Minuyan Proper, City of San Jose Del Monte, Bulacan\n\n' +
            'SCHOOL CLINIC MEDICAL GATE PASS\n\n' +
            'DATE: ' + longDate(rec.visit_date) + '\n\n' +
            'This is to certify that ' + rec.student_name + ', from ' + (rec.course || '[Course]') +
            ' with Student ID ' + rec.student_id + ', visited the school clinic due to a medical concern. ' +
            'Based on the clinic assessment, the student is allowed to leave the campus for medical reasons.\n\n' +
            'Reason: ' + rec.incident_type + '\n' +
            'Incident Reference: ' + (rec.incident_ref || '-') + '\n' +
            'Time Allowed to Leave: ' + (rec.time_allowed || '-') + '\n' +
            'Guardian Notified: ' + rec.guardian + '\n' +
            (rec.description ? '\nNotes: ' + rec.description + '\n' : '') +
            '\nApproved by:\nMARIA CARLA P. SERENAS, LPT, RN (Clinic Nurse)\n' +
            'ROSEMARIE SALVADOR (College Coordinator)\n\nSecurity Verification: ______________ (Security Officer)';
        $('gpModal').style.display = 'flex';
    }

    function closePdf() { $('gpModal').style.display = 'none'; pdfRecord = null; }

    $('gpPdfFormBtn').addEventListener('click', function () { openPdf(recordFromForm()); });
    $('gpModalCancel').addEventListener('click', closePdf);
    $('gpModal').addEventListener('click', function (e) { if (e.target === this) closePdf(); });

    $('gpModalDownload').addEventListener('click', function () {
        if (!pdfRecord) return;
        if (!window.jspdf) { showMsg('error', 'PDF library did not load. Check your internet connection.'); return; }

        const rec = pdfRecord;
        const doc = new window.jspdf.jsPDF({ unit: 'mm', format: 'letter' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 25;
        const contentW = pageW - margin * 2;
        let y;

        doc.setFont('helvetica', 'bold'); doc.setFontSize(13); doc.setTextColor(13, 35, 64);
        doc.text('BESTLINK COLLEGE OF THE PHILIPPINES', pageW / 2, 30, { align: 'center' });
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9); doc.setTextColor(80, 80, 80);
        doc.text('IPO Road, Barangay Minuyan Proper, City of San Jose Del Monte, Bulacan', pageW / 2, 36, { align: 'center' });
        doc.setDrawColor(13, 35, 64); doc.setLineWidth(0.6); doc.line(margin, 40, pageW - margin, 40);
        doc.setLineWidth(0.2); doc.line(margin, 42, pageW - margin, 42);

        doc.setFont('helvetica', 'bold'); doc.setFontSize(14); doc.setTextColor(13, 35, 64);
        doc.text('SCHOOL CLINIC MEDICAL GATE PASS', pageW / 2, 54, { align: 'center' });

        doc.setFontSize(10); doc.setTextColor(30, 30, 30);
        doc.text('DATE:', margin, 68);
        doc.setFont('helvetica', 'normal');
        doc.text(longDate(rec.visit_date), margin + 14, 68);

        doc.setFontSize(10.5);
        const body = 'This is to certify that ' + rec.student_name + ', from ' + (rec.course || '[Course]') +
            ' with Student ID ' + rec.student_id + ', visited the school clinic due to a medical concern. ' +
            'Based on the clinic assessment, the student is allowed to leave the campus for medical reasons.';
        const lines = doc.splitTextToSize(body, contentW);
        doc.text(lines, margin, 78);
        y = 78 + lines.length * 6 + 8;

        function field(label, value) {
            doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); doc.setTextColor(30, 30, 30);
            doc.text(label, margin, y);
            doc.setDrawColor(80, 80, 80); doc.setLineWidth(0.3);
            doc.line(margin + 48, y + 1, margin + 118, y + 1);
            doc.setTextColor(50, 50, 50);
            doc.text(String(value || '-'), margin + 50, y);
            y += 9;
        }
        field('Reason:', rec.incident_type);
        field('Incident Reference:', rec.incident_ref);
        field('Time Allowed to Leave:', rec.time_allowed);
        field('Guardian Notified:', rec.guardian);

        if (rec.description) {
            y += 4;
            doc.setFont('helvetica', 'italic'); doc.setFontSize(9.5); doc.setTextColor(80, 80, 80);
            const notes = doc.splitTextToSize('Notes: ' + rec.description, contentW);
            doc.text(notes, margin, y);
            y += notes.length * 5.5 + 4;
        }

        y += 6;
        doc.setDrawColor(180, 180, 180); doc.line(margin, y, pageW - margin, y);
        y += 10;
        doc.setFont('helvetica', 'normal'); doc.setFontSize(10); doc.setTextColor(30, 30, 30);
        doc.text('Approved by:', margin, y);
        y += 16;

        const col2 = pageW / 2 + 5;
        doc.setFont('helvetica', 'bold'); doc.setTextColor(13, 35, 64);
        doc.text('MARIA CARLA P. SERENAS, LPT, RN', margin, y);
        doc.text('ROSEMARIE SALVADOR', col2, y);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9); doc.setTextColor(80, 80, 80);
        doc.text('Clinic Nurse', margin + 18, y + 6);
        doc.text('College Coordinator', col2 + 8, y + 6);
        y += 26;

        doc.setFontSize(10); doc.setTextColor(30, 30, 30);
        doc.text('Security Verification:', margin, y);
        y += 16;
        doc.setDrawColor(80, 80, 80); doc.line(margin, y, margin + 70, y);
        doc.setFontSize(9); doc.setTextColor(80, 80, 80);
        doc.text('Security Officer', margin + 12, y + 6);

        doc.save('GatePass_' + (rec.student_id || 'student') + '_' + String(rec.visit_date || localToday()).replace(/-/g, '') + '.pdf');
        closePdf();
        showMsg('success', 'PDF downloaded.');
    });

    /* ---------- Init ---------- */
    resetForm();
    renderTable();
})();
</script>