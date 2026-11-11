<?php
$pageTitle = 'Budget and Resource Management';
?>
<div id="brmRoot" class="brm-page">
    <div class="brm-shell">

        <!-- Header -->
        <div class="brm-header">
            <div>
                <h1>Budget and Resource Management</h1>
                <p>Manage proposed budgets, activity expenses, and resource requirements for community extension programs.</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="brm-stats" aria-label="Budget summary">
            <div class="brm-stat-card">
                <div class="brm-stat-label">Proposed Budget</div>
                <div class="brm-stat-value" data-brm-stat="proposed">&#8369;0</div>
            </div>
            <div class="brm-stat-card">
                <div class="brm-stat-label">Approved Budget</div>
                <div class="brm-stat-value" data-brm-stat="approved">&#8369;0</div>
            </div>
            <div class="brm-stat-card">
                <div class="brm-stat-label">Total Expenses</div>
                <div class="brm-stat-value" data-brm-stat="expenses">&#8369;0</div>
            </div>
            <div class="brm-stat-card">
                <div class="brm-stat-label">Remaining Budget</div>
                <div class="brm-stat-value" data-brm-stat="remaining">&#8369;0</div>
            </div>
        </div>

        <!-- LIST VIEW -->
        <section class="brm-card" id="brmListView">
            <div class="brm-section-header">
                <h2 class="brm-panel-title">Extension Activity Records</h2>
            </div>

            <div class="brm-toolbar">
                <input id="brmSearch" class="brm-input" type="text" placeholder="Search extension activity..." aria-label="Search extension activity">
                <button type="button" class="brm-btn brm-btn-secondary" id="brmFilterToggle" aria-expanded="false">Filter</button>
            </div>

            <div class="brm-filters" id="brmFilters" hidden>
                <div class="brm-field">
                    <label class="brm-label" for="brmStatusFilter">Status</label>
                    <select id="brmStatusFilter" class="brm-select">
                        <option value="All">All Status</option>
                        <option value="Proposed">Proposed</option>
                        <option value="Approved">Approved</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>

            <div class="brm-subheader">
                <h3>Activity Budget and Resources</h3>
                <button type="button" class="brm-btn brm-btn-primary" id="brmOpenAdd">+ Add Record</button>
            </div>

            <div class="brm-table-wrap">
                <table class="brm-table" aria-label="Activity budget table">
                    <thead>
                        <tr>
                            <th>Extension Activity</th>
                            <th>Budget</th>
                            <th>Expenses</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="brmTableBody"></tbody>
                </table>
            </div>
            <p class="brm-footnote">Illustrative UI only. Select an activity to view its details.</p>
        </section>

        <!-- DETAIL VIEW -->
        <section class="brm-card" id="brmDetailView" hidden>
            <div class="brm-detail-header">
                <button type="button" class="brm-btn brm-btn-secondary" id="brmBack">&larr; Back</button>
                <div>
                    <h2 class="brm-panel-title" id="brmDetailName">Activity</h2>
                    <span id="brmDetailStatus"></span>
                </div>
            </div>
            <div id="brmDetailBody"></div>
            <p class="brm-footnote">Sample activity data.</p>
        </section>
    </div>

    <!-- Add Record modal -->
    <div id="brmAddModal" class="brm-modal" aria-hidden="true">
        <div class="brm-modal-backdrop" data-brm-close></div>
        <div class="brm-modal-card" role="dialog" aria-modal="true" aria-labelledby="brmAddTitle">
            <button type="button" class="brm-modal-close" data-brm-close aria-label="Close">&times;</button>
            <h3 id="brmAddTitle">Add Activity Record</h3>
            <form id="brmAddForm" novalidate>
                <div class="brm-form-grid">
                    <div class="brm-field brm-field-full">
                        <label class="brm-label" for="brmFName">Extension Activity</label>
                        <input id="brmFName" class="brm-input" type="text" required>
                    </div>
                    <div class="brm-field">
                        <label class="brm-label" for="brmFStatus">Status</label>
                        <select id="brmFStatus" class="brm-select">
                            <option>Proposed</option>
                            <option>Approved</option>
                            <option>Ongoing</option>
                            <option>Completed</option>
                        </select>
                    </div>
                    <div class="brm-field">
                        <label class="brm-label" for="brmFProposed">Proposed Budget (&#8369;)</label>
                        <input id="brmFProposed" class="brm-input" type="number" min="0" step="any" value="0">
                    </div>
                    <div class="brm-field">
                        <label class="brm-label" for="brmFApproved">Approved Budget (&#8369;)</label>
                        <input id="brmFApproved" class="brm-input" type="number" min="0" step="any" value="0">
                    </div>
                </div>
                <p class="brm-form-error" id="brmFormError" hidden>Please enter an activity name.</p>
                <div class="brm-modal-actions">
                    <button type="button" class="brm-btn brm-btn-secondary" data-brm-close>Cancel</button>
                    <button type="submit" class="brm-btn brm-btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>