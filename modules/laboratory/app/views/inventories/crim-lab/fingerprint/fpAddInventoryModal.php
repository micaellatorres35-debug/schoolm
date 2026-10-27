<div class="modal fade" id="fpAddInventoryModal" tabindex="-1" aria-labelledby="fpAddInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form
                id="fpAddInventoryForm"
                action="<?= BASE_URL ?>/fingerprint-inventory/create"
                method="POST">  

                <div class="modal-header">
                    <h5 class="modal-title" id="fpAddInventoryModalLabel">
                        Add Fingerprint Inventory
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">
 
                        <!-- Item Name -->
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" required>
                        </div>

                        <!-- Laboratory -->
                        <div class="col-md-6">
                            <label class="form-label">Laboratory</label>
                            <input type="text"
                                name="laboratory"
                                class="form-control"
                                value="Fingerprint Laboratory"
                                readonly>
                        </div>

                        <!-- Total Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity</label>
                            <input type="number"
                                name="total_item"
                                class="form-control"
                                min="1"
                                required>
                        </div>

                        <!-- Available Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">Available Quantity</label>
                            <input type="number"
                                name="available_item"
                                class="form-control"
                                min="0"
                                required>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="" selected disabled>-- Select Status --</option>
                                <option value="Working">Working</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save
                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>