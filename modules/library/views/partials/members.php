<div class="tab-content" id="tab-borrowers">

    <!-- ── Import Student from Registrar ──────────────────────── -->
    <div class="card" style="margin-bottom:1.5rem;border-left:4px solid var(--primary)">
        <div class="card-header" style="margin-bottom:.875rem;padding-bottom:.875rem">
            <h3 class="card-title">
                <i class="fas fa-user-graduate"></i> Import Student from Registrar
                <span class="badge badge-info" style="font-size:.7rem;margin-left:.5rem">rgr_students</span>
            </h3>
            <button class="btn btn-sm btn-success" onclick="app.members.openAddModal()">
                <i class="fas fa-user-plus"></i> Add Teacher / Staff
            </button>
        </div>
        <div class="d-flex gap-1 align-center" style="margin-bottom:.875rem;flex-wrap:wrap">
            <div class="search-box" style="flex:1;min-width:220px;margin:0">
                <i class="fas fa-search"></i>
                <input type="text" id="studentSearch"
                    placeholder="Search by name or student number..."
                    oninput="app.members.searchStudents(this.value)">
            </div>
            <span class="text-muted small">Type at least 2 characters</span>
        </div>
        <div id="studentSearchResults" style="display:none">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student No.</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentSearchBody"></tbody>
                </table>
            </div>
        </div>
        <div id="studentSearchEmpty" style="display:none" class="text-muted small">
            <i class="fas fa-info-circle"></i> No students found or all already imported.
        </div>
        <div id="studentSearchSpinner" style="display:none">
            <div class="spinner" style="width:24px;height:24px;border-width:3px;margin:.5rem 0"></div>
        </div>
    </div>

    <!-- ── Library Members Table ──────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users"></i> Library Members</h3>
        </div>
        <div class="d-flex gap-1 mb-1 align-center flex-wrap">
            <div class="search-box" style="flex:1;min-width:220px;margin:0">
                <i class="fas fa-search"></i>
                <input type="text" id="memberSearch" placeholder="Search members..."
                    oninput="app.members.search(this.value)">
            </div>
            <select id="memberTypeFilter" onchange="app.members.filter()"
                style="width:auto;padding:.875rem">
                <option value="">All Types</option>
                <option value="Student">Students</option>
                <option value="Teacher">Teachers</option>
                <option value="Staff">Staff</option>
            </select>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name / ID</th>
                        <th>Type</th>
                        <th>Borrowed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    <tr><td colspan="4" class="empty-state"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span class="pagination-info" id="membersPaginationInfo">Loading...</span>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline" onclick="app.members.changePage(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-sm btn-outline" onclick="app.members.changePage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>