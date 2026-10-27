<div class="modal fade" id="dtAddMonitoringModal" tabindex="-1" aria-labelledby="dtAddMonitoringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form
                id="dtAddMonitoringForm"
                action="<?= BASE_URL ?>/defense-tactics-monitoring/create"
                method="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="dtAddMonitoringModalLabel">
                        <i class="fas fa-plus me-2"></i>
                        Add Defense Tactics Monitoring Record
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
                                value="Defense Tactics Laboratory"
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