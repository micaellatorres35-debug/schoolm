<div class="tab-content" id="tab-reports">

    <!-- Library Stats Report -->
    <div class="card mb-2">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Library Reports</h3>
            <button class="btn btn-sm" onclick="app.reports.load()"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
        <div id="reportsContent"><div class="spinner"></div></div>
    </div>

    <!-- Submit Report to Dean -->
    <div class="card mb-2">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-paper-plane"></i> Submit Report for Dean Approval
                <span class="badge badge-info" style="font-size:.7rem;margin-left:.5rem">sd_reports</span>
            </h3>
        </div>
        <p class="text-muted mb-2"><i class="fas fa-info-circle"></i> Submit library reports to the School Directress for approval. Reports will appear in the dean's dashboard.</p>
        <div class="grid-2">
            <div>
                <div class="form-group">
                    <label>Report Title <span style="color:red">*</span></label>
                    <input type="text" id="reportTitle" placeholder="e.g. Monthly Library Inventory Report">
                </div>
                <div class="form-group">
                    <label>Report Type <span style="color:red">*</span></label>
                    <select id="reportType">
                        <option value="16">Borrowed Books Report</option>
                        <option value="17">Overdue Books Report</option>
                        <option value="18">Library Inventory Summary Report</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="reportDescription" placeholder="Brief description of this report..." style="min-height:80px"></textarea>
                </div>
                <button class="btn btn-success" onclick="LibraryReports.submit()">
                    <i class="fas fa-paper-plane"></i> Submit to Dean
                </button>
            </div>
            <div>
                <h4 class="mb-1"><i class="fas fa-clock"></i> Submission History</h4>
                <div id="reportSubmissions"><div class="spinner"></div></div>
            </div>
        </div>
    </div>

    <!-- Dean Decisions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-gavel"></i> Dean Approval & Decisions
            </h3>
            <button class="btn btn-sm btn-outline" onclick="LibraryReports.loadDecisions()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <p class="text-muted mb-2"><i class="fas fa-info-circle"></i> Shows approval decisions made by the School Directress on all department reports.</p>
        <div class="d-flex gap-1 mb-2 flex-wrap">
            <button class="btn btn-sm btn-outline" onclick="LibraryReports.filterDecisions('')">All</button>
            <button class="btn btn-sm btn-warning"  onclick="LibraryReports.filterDecisions('Pending')"><i class="fas fa-clock"></i> Pending</button>
            <button class="btn btn-sm btn-success"  onclick="LibraryReports.filterDecisions('Approved')"><i class="fas fa-check"></i> Approved</button>
            <button class="btn btn-sm btn-danger"   onclick="LibraryReports.filterDecisions('Returned')"><i class="fas fa-undo"></i> Returned</button>
        </div>
        <div id="deanDecisions"><div class="spinner"></div></div>
    </div>
</div>

<script>
const LibraryReports = (() => {
    let allDecisions = [];

    async function submit() {
        const title       = document.getElementById('reportTitle').value.trim();
        const reportType  = document.getElementById('reportType').value;
        const description = document.getElementById('reportDescription').value.trim();

        if (!title) { app.ui.showNotif('Report title is required.', 'error'); return; }

        const res = await app.api.post('submit_library_report', { title, report_type: reportType, description });
        if (res.error) { app.ui.showNotif(res.error, 'error'); return; }
        app.ui.showNotif(res.message || 'Report submitted successfully!', 'success');
        document.getElementById('reportTitle').value       = '';
        document.getElementById('reportDescription').value = '';
        loadSubmissions();
    }

    async function loadSubmissions() {
        const wrap = document.getElementById('reportSubmissions');
        if (!wrap) return;
        wrap.innerHTML = '<div class="spinner"></div>';
        try {
            const res = await app.api.get({ action: 'get_library_reports' });
            const reports = res.reports || [];
            if (!reports.length) {
                wrap.innerHTML = '<div class="empty-state"><i class="fas fa-file-alt"></i><p>No reports submitted yet.</p></div>';
                return;
            }
            wrap.innerHTML = reports.map(r => {
                const statusClass = r.status === 'Approved' ? 'badge-success'
                                  : r.status === 'Returned' ? 'badge-danger'
                                  : 'badge-warning';
                return `
                <div style="padding:.75rem 0;border-bottom:1px solid var(--border-color)">
                    <div class="d-flex gap-1 align-center">
                        <div style="flex:1">
                            <div class="fw-600 small">${escH(r.title)}</div>
                            <div class="text-muted small">${escH(r.report_type_name||'')} &nbsp;·&nbsp; ${escH(r.submitted_at)}</div>
                        </div>
                        <span class="badge ${statusClass}">${r.status}</span>
                    </div>
                </div>`;
            }).join('');
        } catch(e) {
            wrap.innerHTML = '<p class="text-danger small">Error loading submissions.</p>';
        }
    }

    async function loadDecisions() {
        const wrap = document.getElementById('deanDecisions');
        if (!wrap) return;
        wrap.innerHTML = '<div class="spinner"></div>';
        try {
            const res = await app.api.get({ action: 'get_dean_decisions' });
            allDecisions = res.reports || [];
            renderDecisions(allDecisions);
        } catch(e) {
            wrap.innerHTML = '<p class="text-danger small">Error loading decisions.</p>';
        }
    }

    function filterDecisions(status) {
        const filtered = status ? allDecisions.filter(r => r.status === status) : allDecisions;
        renderDecisions(filtered);
    }

    function renderDecisions(reports) {
        const wrap = document.getElementById('deanDecisions');
        if (!reports.length) {
            wrap.innerHTML = '<div class="empty-state"><i class="fas fa-gavel"></i><p>No decisions found.</p></div>';
            return;
        }
        wrap.innerHTML = `
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Department</th>
                    <th>Submitted By</th>
                    <th>Date</th>
                    <th>Decision</th>
                </tr>
            </thead>
            <tbody>
            ${reports.map(r => {
                const statusClass = r.status === 'Approved' ? 'badge-success'
                                  : r.status === 'Returned' ? 'badge-danger'
                                  : 'badge-warning';
                const icon = r.status === 'Approved' ? 'fa-check-circle'
                           : r.status === 'Returned' ? 'fa-times-circle'
                           : 'fa-clock';
                return `
                <tr>
                    <td><div class="fw-600 small">${escH(r.title)}</div>
                        ${r.description ? `<div class="text-muted small">${escH(r.description.substring(0,60))}${r.description.length>60?'...':''}</div>` : ''}
                    </td>
                    <td class="small">${escH(r.report_type_name||'—')}</td>
                    <td class="small">${escH(r.department_name||'—')}</td>
                    <td class="small">${escH(r.submitted_by_name||'—')}</td>
                    <td class="small">${escH(r.submitted_at||'—')}</td>
                    <td><span class="badge ${statusClass}"><i class="fas ${icon}"></i> ${r.status}</span></td>
                </tr>`;
            }).join('')}
            </tbody>
        </table>
        </div>`;
    }

    function escH(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    return { submit, loadSubmissions, loadDecisions, filterDecisions };
})();
</script>