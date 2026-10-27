<div class="modal fade" id="fpViewMonitoringModal" tabindex="-1" aria-labelledby="fpViewMonitoringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="fpViewMonitoringModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    View Fingerprint Monitoring Record
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

                    <!-- Condition -->
                    <div class="col-md-6">
                        <label class="form-label">Condition</label>
                        <select
                            id="view_condition"
                            class="form-select"
                            disabled>
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
                            id="view_last_checked"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Checked By -->
                    <div class="col-md-6">
                        <label class="form-label">Checked By</label>
                        <input
                            type="text"
                            id="view_checked_by"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Remarks -->
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea
                            id="view_remarks"
                            class="form-control"
                            rows="3"
                            readonly></textarea>
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