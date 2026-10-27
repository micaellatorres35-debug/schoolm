<div class="modal fade" id="psyViewInventoryModal" tabindex="-1" aria-labelledby="psyViewInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="psyViewInventoryModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    View Psychology Inventory
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
                            id="psy_view_item_name"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Category -->
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input
                            type="text"
                            id="psy_view_category"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Laboratory -->
                    <div class="col-md-6">
                        <label class="form-label">Laboratory</label>
                        <input
                            type="text"
                            id="psy_view_laboratory"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Total Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Total Quantity</label>
                        <input
                            type="number"
                            id="psy_view_total_item"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Available Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Available Quantity</label>
                        <input
                            type="number"
                            id="psy_view_available_item"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select
                            id="psy_view_status"
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
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>