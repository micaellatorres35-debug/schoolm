<div class="modal fade" id="lab3AddMonitoringModal" tabindex="-1" aria-labelledby="lab3AddMonitoringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form
                id="lab3AddMonitoringForm"
                action="<?= BASE_URL ?>/lab3-monitoring/create"
                method="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="lab3AddMonitoringModalLabel">
                        <i class="fas fa-plus me-2"></i>
                        Add Lab 3 Monitoring Record
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
                                name="item_name"
                                class="form-control"
                                required>
                        </div>

                        <!-- Laboratory -->
                        <div class="col-md-6">
                            <label class="form-label">Laboratory</label>
                            <input
                                type="text"
                                name="laboratory"
                                class="form-control"
                                value="IT Lab 3"
                                readonly>
                        </div>

                        <!-- Condition -->
                        <div class="col-md-6">
                            <label class="form-label">Condition</label>
                            <select
                                name="equipment_condition"
                                class="form-select"
                                required>
                                <option value="" disabled selected>-- Select Condition --</option>
                                <option value="Working">Working</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>

                        <!-- Last Checked -->
                        <div class="col-md-6">
                            <label class="form-label">Last Checked</label>
                            <input
                                type="date"
                                name="last_checked"
                                class="form-control"
                                required>
                        </div>

                        <!-- Checked By -->
                        <div class="col-md-6">
                            <label class="form-label">Checked By</label>
                            <input
                                type="text"
                                name="checked_by"
                                class="form-control"
                                required>
                        </div>

                        <!-- Remarks -->
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea
                                name="remarks"
                                class="form-control"
                                rows="3"
                                placeholder="Enter remarks..."
                                required></textarea>
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