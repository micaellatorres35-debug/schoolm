<div class="modal fade" id="viewDamageModal" tabindex="-1" aria-labelledby="viewDamageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="viewDamageModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    View Damage
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <!-- Item Name -->
                    <div class="col-md-6">
                        <label class="form-label">Item Name</label>
                        <input
                            type="text"
                            id="view_item_name"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Laboratory -->
                    <div class="col-md-6">
                        <label class="form-label">Laboratory</label>
                        <input
                            type="text"
                            id="view_laboratory"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Issue -->
                    <div class="col-md-6">
                        <label class="form-label">Issue</label>
                        <input
                            type="text"
                            id="view_issue"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Reported By -->
                    <div class="col-md-6">
                        <label class="form-label">Reported By</label>
                        <input
                            type="text"
                            id="view_reported_by"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Date Reported -->
                    <div class="col-md-6">
                        <label class="form-label">Date Reported</label>
                        <input
                            type="date"
                            id="view_date_reported"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select
                            id="view_status"
                            class="form-select"
                            disabled>
                            <option value="Working">Working</option>
                            <option value="Under Maintenance">Under Maintenance</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>