<div class="modal fade" id="addDamageModal" tabindex="-1" aria-labelledby="addDamageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="addDamageForm" action="<?= BASE_URL ?>/damages/create" method="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="addDamageModalLabel">
                        <i class="fas fa-plus-square me-2"></i>
                        Add New Damage
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

                        <!-- Laboratory -->
                        <div class="col-md-6">
                            <label class="form-label">Laboratory</label>
                            <input type="text"
                                name="laboratory"
                                class="form-control"
                                value="Physics Laboratory"
                                readonly>
                        </div>

                        <!-- Issue -->
                        <div class="col-md-6">
                            <label class="form-label">Issue</label>
                            <input type="text"
                                name="issue"
                                class="form-control"
                                required>
                        </div>

                        <!-- Reported By -->
                        <div class="col-md-6">
                            <label class="form-label">Reported By</label>
                            <input type="text"
                                name="reported_by"
                                class="form-control"
                                required>
                        </div>

                        <!-- Date Reported -->
                        <div class="col-md-6">
                            <label class="form-label">Date Reported</label>
                            <input type="date"
                                name="date_reported"
                                class="form-control"
                                required>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="">-- Select Status --</option>
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