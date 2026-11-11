<?php
$pageTitle = 'Volunteer and Manpower Management';

?>
<div id="vmmRoot" class="vmm-page">
    <div class="vmm-shell">

        <!-- Header -->
        <div class="vmm-header">
            <div>
                <h1>Volunteer and Manpower Management</h1>
                <p>Manage volunteers and manpower for extension activities.</p>
            </div>
            <span class="vmm-status vmm-status-assigned">Active</span>
        </div>

        <!-- Stats -->
        <div class="vmm-stats" aria-label="Volunteer statistics">
            <div class="vmm-stat-card">
                <div class="vmm-stat-label">Total Volunteers</div>
                <div class="vmm-stat-value" data-vmm-stat="total">120</div>
                <div class="vmm-stat-meta">Registered</div>
            </div>
            <div class="vmm-stat-card">
                <div class="vmm-stat-label">Assigned Volunteers</div>
                <div class="vmm-stat-value" data-vmm-stat="assigned">85</div>
                <div class="vmm-stat-meta">Across activities</div>
            </div>
            <div class="vmm-stat-card">
                <div class="vmm-stat-label">Present Volunteers</div>
                <div class="vmm-stat-value" data-vmm-stat="present">72</div>
                <div class="vmm-stat-meta">Attendance recorded</div>
            </div>
            <div class="vmm-stat-card">
                <div class="vmm-stat-label">Total Hours Rendered</div>
                <div class="vmm-stat-value" data-vmm-stat="hours">456</div>
                <div class="vmm-stat-meta">Accumulated hours</div>
            </div>
        </div>

        <div class="vmm-layout">

            <!-- Volunteer Management -->
            <section class="vmm-card">
                <div class="vmm-section-header">
                    <h2 class="vmm-panel-title">Volunteer Management</h2>
                    <button type="button" class="vmm-btn vmm-btn-primary" id="vmmOpenRegister">+ Register Volunteer</button>
                </div>

                <div class="vmm-toolbar">
                    <input id="vmmSearch" class="vmm-input" type="text" placeholder="Search volunteer name..." aria-label="Search volunteer name">
                    <button type="button" class="vmm-btn vmm-btn-secondary" id="vmmFilterToggle" aria-expanded="false">Filter</button>
                </div>

                <div class="vmm-filters" id="vmmFilters" hidden>
                    <div class="vmm-field">
                        <label class="vmm-label" for="vmmTypeFilter">Type</label>
                        <select id="vmmTypeFilter" class="vmm-select">
                            <option value="All">All Types</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Community">Community</option>
                        </select>
                    </div>
                    <div class="vmm-field">
                        <label class="vmm-label" for="vmmStatusFilter">Status</label>
                        <select id="vmmStatusFilter" class="vmm-select">
                            <option value="All">All Status</option>
                            <option value="Assigned">Assigned</option>
                            <option value="Available">Available</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="vmm-records-label">Volunteer Records</div>
                <div class="vmm-table-wrap">
                    <table class="vmm-table" aria-label="Volunteer records table">
                        <thead>
                            <tr>
                                <th>Volunteer</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="vmmTableBody"></tbody>
                    </table>
                </div>
            </section>

            <!-- Details / Assignment Overview -->
            <section class="vmm-card" id="vmmDetails">
                <h2 class="vmm-panel-title">Volunteer Details / Assignment Overview</h2>
                <div id="vmmDetailsBody"></div>
            </section>
        </div>

        <p class="vmm-footnote">Illustrative UI concept only. Sample records and figures.</p>
    </div>

    <!-- Register Volunteer modal -->
    <div id="vmmRegisterModal" class="vmm-modal" aria-hidden="true">
        <div class="vmm-modal-backdrop" data-vmm-close></div>
        <div class="vmm-modal-card" role="dialog" aria-modal="true" aria-labelledby="vmmRegisterTitle">
            <button type="button" class="vmm-modal-close" data-vmm-close aria-label="Close">&times;</button>
            <h3 id="vmmRegisterTitle">Register Volunteer</h3>
            <form id="vmmRegisterForm" novalidate>
                <div class="vmm-form-grid">
                    <div class="vmm-field vmm-field-full">
                        <label class="vmm-label" for="vmmFName">Full Name</label>
                        <input id="vmmFName" class="vmm-input" type="text" required>
                    </div>
                    <div class="vmm-field">
                        <label class="vmm-label" for="vmmFType">Type</label>
                        <select id="vmmFType" class="vmm-select">
                            <option>Student</option>
                            <option>Faculty</option>
                            <option>Staff</option>
                            <option>Community</option>
                        </select>
                    </div>
                    <div class="vmm-field">
                        <label class="vmm-label" for="vmmFInfo">Program / Year or Office</label>
                        <input id="vmmFInfo" class="vmm-input" type="text" placeholder="e.g. BSIS &bull; 2nd Year">
                    </div>
                    <div class="vmm-field">
                        <label class="vmm-label" for="vmmFEmail">Email</label>
                        <input id="vmmFEmail" class="vmm-input" type="email">
                    </div>
                    <div class="vmm-field">
                        <label class="vmm-label" for="vmmFContact">Contact No.</label>
                        <input id="vmmFContact" class="vmm-input" type="text">
                    </div>
                </div>
                <p class="vmm-form-error" id="vmmFormError" hidden>Please enter the volunteer's full name.</p>
                <div class="vmm-modal-actions">
                    <button type="button" class="vmm-btn vmm-btn-secondary" data-vmm-close>Cancel</button>
                    <button type="submit" class="vmm-btn vmm-btn-primary">Save Volunteer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Full details modal -->
    <div id="vmmFullModal" class="vmm-modal" aria-hidden="true">
        <div class="vmm-modal-backdrop" data-vmm-close></div>
        <div class="vmm-modal-card" role="dialog" aria-modal="true" aria-labelledby="vmmFullTitle">
            <button type="button" class="vmm-modal-close" data-vmm-close aria-label="Close">&times;</button>
            <h3 id="vmmFullTitle">Volunteer Full Details</h3>
            <div id="vmmFullBody"></div>
            <div class="vmm-modal-actions">
                <button type="button" class="vmm-btn vmm-btn-secondary" data-vmm-close>Close</button>
            </div>
        </div>
    </div>
</div>

