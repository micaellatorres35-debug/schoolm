<div class="module-header">
    <h1>Community Needs Assessment</h1>
    <p>Manage community profiles, identify local needs, and monitor community engagement activities.</p>
</div>

<div id="cnaRoot" class="cna-page">
    <div class="cna-shell">
        <section class="cna-stats" aria-label="Community statistics">
            <div class="cna-stat-card">
                <div class="cna-stat-label">Total Communities</div>
                <div class="cna-stat-value">12</div>
                <div class="cna-stat-meta">Registered communities</div>
            </div>
            <div class="cna-stat-card">
                <div class="cna-stat-label">Total Assessments</div>
                <div class="cna-stat-value">24</div>
                <div class="cna-stat-meta">Assessment records</div>
            </div>
            <div class="cna-stat-card">
                <div class="cna-stat-label">Identified Issues</div>
                <div class="cna-stat-value">18</div>
                <div class="cna-stat-meta">Community concerns</div>
            </div>
            <div class="cna-stat-card">
                <div class="cna-stat-label">Total Beneficiaries</div>
                <div class="cna-stat-value">350</div>
                <div class="cna-stat-meta">Assessed individuals</div>
            </div>
        </section>

        <nav class="cna-tabs" aria-label="Community needs assessment tabs">
            <button class="cna-tab active" type="button" data-cna-tab="records">Community Records</button>
            <button class="cna-tab" type="button" data-cna-tab="assessment">Needs Assessment</button>
            <button class="cna-tab" type="button" data-cna-tab="engagement">Community Engagement</button>
        </nav>

        <section class="cna-panel active" data-cna-panel="records">
            <div class="cna-section-header">
                <div>
                    <h2 class="cna-panel-title">Community Records</h2>
                    <p class="cna-panel-subtitle">Community Profiling and Target Community Identification</p>
                </div>
                <button class="cna-primary-btn" type="button">+ Add Community</button>
            </div>

            <div class="cna-search-wrap">
                <input id="cnaCommunitySearch" class="cna-search-input" type="text" placeholder="Search community name or location..." aria-label="Search community">
            </div>

            <div class="cna-table-wrap">
                <table class="cna-table" aria-label="Community records table">
                    <thead>
                        <tr>
                            <th>Community</th>
                            <th>Focus Area</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="cnaCommunityTableBody"></tbody>
                </table>
            </div>

            <div class="cna-footer">
                <div id="cnaSummaryText" class="cna-summary">Showing 3 sample records · Page 1 of 1</div>
                <button id="cnaViewDetailsBtn" class="cna-secondary-btn" type="button">View sample details</button>
            </div>
            <p class="cna-note">Illustrative UI only. Sample records and counts are not actual database data.</p>
        </section>

        <section class="cna-panel" data-cna-panel="assessment">
            <div class="cna-empty-panel">
                <h3>Needs Assessment</h3>
                <p>Sample assessment insights will appear here.</p>
            </div>
        </section>

        <section class="cna-panel" data-cna-panel="engagement">
            <div class="cna-empty-panel">
                <h3>Community Engagement</h3>
                <p>Sample community engagement activity data will appear here.</p>
            </div>
        </section>

        <div id="cnaModal" class="cna-modal" aria-hidden="true">
            <div class="cna-modal-backdrop" data-cna-close="modal"></div>
            <div class="cna-modal-card" role="dialog" aria-modal="true" aria-labelledby="cnaModalTitle">
                <button class="cna-modal-close" type="button" data-cna-close="modal" aria-label="Close">×</button>
                <div class="cna-modal-body">
                    <h3 id="cnaModalTitle">Community Details</h3>
                    <p class="cna-modal-copy">Sample data view for community planning and engagement activities.</p>
                    <div id="cnaModalContent" class="cna-modal-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
