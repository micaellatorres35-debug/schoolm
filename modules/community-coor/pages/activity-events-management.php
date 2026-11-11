<?php
$pageTitle = 'Activity and Event Management';
?>
<div id="aemRoot" class="aem-page">
    <div class="aem-shell">

        <!-- Header -->
        <div class="aem-header">
            <div>
                <h1>Activity and Event Management</h1>
                <p>Manage the preparation, coordination, and execution of community extension activities.</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="aem-stats" aria-label="Activity statistics">
            <div class="aem-stat-card">
                <div class="aem-stat-label">Total Activities</div>
                <div class="aem-stat-value" data-aem-stat="total">12</div>
            </div>
            <div class="aem-stat-card">
                <div class="aem-stat-label">Upcoming Activities</div>
                <div class="aem-stat-value" data-aem-stat="upcoming">4</div>
            </div>
            <div class="aem-stat-card">
                <div class="aem-stat-label">Ongoing Activities</div>
                <div class="aem-stat-value" data-aem-stat="ongoing">2</div>
            </div>
            <div class="aem-stat-card">
                <div class="aem-stat-label">Completed Activities</div>
                <div class="aem-stat-value" data-aem-stat="completed">6</div>
            </div>
        </div>

        <!-- Activity Records -->
        <section class="aem-card">
            <div class="aem-section-header">
                <h2 class="aem-panel-title">Activity Records</h2>
            </div>

            <div class="aem-toolbar">
                <input id="aemSearch" class="aem-input" type="text" placeholder="Search activity..." aria-label="Search activity">
                <button type="button" class="aem-btn aem-btn-secondary" id="aemFilterToggle" aria-expanded="false">Filter</button>
            </div>

            <div class="aem-filters" id="aemFilters" hidden>
                <div class="aem-field">
                    <label class="aem-label" for="aemStatusFilter">Preparation Status</label>
                    <select id="aemStatusFilter" class="aem-select">
                        <option value="All">All Status</option>
                        <option value="Planning">Planning</option>
                        <option value="In Preparation">In Preparation</option>
                        <option value="Ready">Ready</option>
                    </select>
                </div>
            </div>

            <div class="aem-subheader">
                <h3>Extension Activities</h3>
                <button type="button" class="aem-btn aem-btn-primary" id="aemOpenAdd">+ Add Activity</button>
            </div>

            <div class="aem-table-wrap">
                <table class="aem-table" aria-label="Extension activities table">
                    <thead>
                        <tr>
                            <th>Activity Name</th>
                            <th>Schedule</th>
                            <th>Preparation Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="aemTableBody"></tbody>
                </table>
            </div>
            <p class="aem-footnote">Illustrative UI concept only.</p>
        </section>
    </div>

    <!-- Activity Details drawer -->
    <div id="aemDrawer" class="aem-drawer" aria-hidden="true">
        <div class="aem-drawer-backdrop" data-aem-close></div>
        <aside class="aem-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="aemDrawerTitle">
            <div class="aem-drawer-head">
                <div>
                    <h2 id="aemDrawerTitle">Activity Details</h2>
                    <div class="aem-drawer-meta">
                        <span id="aemDrawerStatus"></span>
                        <span id="aemDrawerSub"></span>
                    </div>
                </div>
                <button type="button" class="aem-drawer-close" id="aemDrawerClose" data-aem-close aria-label="Close">&times;</button>
            </div>
            <div class="aem-drawer-body" id="aemDrawerBody"></div>
        </aside>
    </div>

    <!-- Add Activity modal -->
    <div id="aemAddModal" class="aem-modal" aria-hidden="true">
        <div class="aem-modal-backdrop" data-aem-close></div>
        <div class="aem-modal-card" role="dialog" aria-modal="true" aria-labelledby="aemAddTitle">
            <button type="button" class="aem-modal-close" data-aem-close aria-label="Close">&times;</button>
            <h3 id="aemAddTitle">Add Activity</h3>
            <form id="aemAddForm" novalidate>
                <div class="aem-form-grid">
                    <div class="aem-field aem-field-full">
                        <label class="aem-label" for="aemFName">Activity Name</label>
                        <input id="aemFName" class="aem-input" type="text" required>
                    </div>
                    <div class="aem-field aem-field-full">
                        <label class="aem-label" for="aemFProgram">Extension Program</label>
                        <input id="aemFProgram" class="aem-input" type="text">
                    </div>
                    <div class="aem-field">
                        <label class="aem-label" for="aemFDate">Date</label>
                        <input id="aemFDate" class="aem-input" type="date" required>
                    </div>
                    <div class="aem-field">
                        <label class="aem-label" for="aemFStatus">Preparation Status</label>
                        <select id="aemFStatus" class="aem-select">
                            <option>Planning</option>
                            <option>In Preparation</option>
                            <option>Ready</option>
                        </select>
                    </div>
                    <div class="aem-field">
                        <label class="aem-label" for="aemFVenue">Venue</label>
                        <input id="aemFVenue" class="aem-input" type="text">
                    </div>
                    <div class="aem-field">
                        <label class="aem-label" for="aemFPic">Person-in-Charge</label>
                        <input id="aemFPic" class="aem-input" type="text">
                    </div>
                </div>
                <p class="aem-form-error" id="aemFormError" hidden>Please enter the activity name and date.</p>
                <div class="aem-modal-actions">
                    <button type="button" class="aem-btn aem-btn-secondary" data-aem-close>Cancel</button>
                    <button type="submit" class="aem-btn aem-btn-primary">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo htmlspecialchars($aemCssPath); ?>">
<script src="<?php echo htmlspecialchars($aemJsPath); ?>" defer></script>