<?php
$pageTitle = 'Partnership and Stakeholder Management';
$psmCssPath = 'css/pages/partnership-management.css?v=' . filemtime(__DIR__ . '/../css/pages/partnership-management.css');
?>
<div id="psmRoot" class="psm-root" data-psm-page="class-scheduling">
    <div class="psm-header-card">
        <div class="psm-header-badge">Stakeholder Network</div>
        <h1>Partnership and Stakeholder Management</h1>
        <p>Monitor partnerships, track collaboration outcomes, and maintain a clear picture of community and institutional engagement.</p>
    </div>

    <div class="psm-stats" aria-label="Partnership summary statistics">
        <div class="psm-stat-card">
            <span class="psm-stat-label">Active Partners</span>
            <strong class="psm-stat-value" data-psm-stat="activePartners">0</strong>
        </div>
        <div class="psm-stat-card">
            <span class="psm-stat-label">MOA in Force</span>
            <strong class="psm-stat-value" data-psm-stat="activeMoas">0</strong>
        </div>
        <div class="psm-stat-card">
            <span class="psm-stat-label">Programs Supported</span>
            <strong class="psm-stat-value" data-psm-stat="programsSupported">0</strong>
        </div>
        <div class="psm-stat-card">
            <span class="psm-stat-label">This Quarter</span>
            <strong class="psm-stat-value" data-psm-stat="quarterImpact">0</strong>
        </div>
    </div>

    <div class="psm-toolbar">
        <div class="psm-search-wrap">
            <input id="psmSearch" type="text" placeholder="Search partner organizations..." aria-label="Search partner organizations">
        </div>
        <div class="psm-filter-group">
            <select id="psmCategoryFilter" aria-label="Filter by partnership category">
                <option value="All">All Categories</option>
                <option value="Government">Government</option>
                <option value="Community">Community</option>
                <option value="Academic">Academic</option>
                <option value="Private">Private</option>
            </select>
            <select id="psmStatusFilter" aria-label="Filter by partnership status">
                <option value="All">All Status</option>
                <option value="Active">Active</option>
                <option value="Monitoring">Monitoring</option>
                <option value="Renewal">Renewal</option>
            </select>
        </div>
    </div>

    <div id="psmListView" class="psm-list-view">
        <div class="psm-table-wrap">
            <table class="psm-table">
                <thead>
                    <tr>
                        <th>Partner Organization</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Lead Contact</th>
                        <th>Latest Activity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="psmTableBody"></tbody>
            </table>
        </div>
    </div>

    <div id="psmDetailView" class="psm-detail-view" hidden>
        <div class="psm-detail-header">
            <button id="psmBackButton" type="button" class="psm-back-button">Back</button>
            <div>
                <div class="psm-detail-kicker">Partner Overview</div>
                <h2 id="psmDetailName">Partner Name</h2>
            </div>
        </div>

        <div class="psm-detail-grid">
            <section class="psm-card">
                <h3>Partner Information</h3>
                <dl id="psmPartnerInfo" class="psm-info-list"></dl>
            </section>

            <section class="psm-card">
                <h3>Contact Information</h3>
                <dl id="psmContactInfo" class="psm-info-list"></dl>
            </section>

            <section class="psm-card">
                <h3>MOA</h3>
                <dl id="psmMoaInfo" class="psm-info-list"></dl>
            </section>

            <section class="psm-card">
                <h3>Activities</h3>
                <ul id="psmActivityList" class="psm-list"></ul>
            </section>

            <section class="psm-card psm-accomplishment-card">
                <h3>Accomplishments</h3>
                <ul id="psmAccomplishmentList" class="psm-list"></ul>
            </section>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo htmlspecialchars($psmCssPath); ?>">
