<div class="modal fade" id="balViewInventoryModal" tabindex="-1" aria-labelledby="balViewInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="balViewInventoryModalLabel">
                    View Ballistic Inventory
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <!-- ID -->
                    <div class="col-md-6">
                        <label class="form-label">ID</label>
                        <input
                            type="text"
                            id="view_id"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Item Name -->
                    <div class="col-md-6">
                        <label class="form-label">Item Name</label>
                        <input
                            type="text"
                            id="view_item_name"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Category -->
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input
                            type="text"
                            id="view_category"
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

                    <!-- Total Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Total Quantity</label>
                        <input
                            type="number"
                            id="view_total_quantity"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Available Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Available Quantity</label>
                        <input
                            type="number"
                            id="view_available_quantity"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input
                            type="text"
                            id="view_status"
                            class="form-control"
                            readonly>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>