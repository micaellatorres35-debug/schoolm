<div class="modal fade" id="balViewBorrowModal" tabindex="-1" aria-labelledby="balViewBorrowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="balViewBorrowModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    View Borrow Record
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <!-- Laboratory -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Laboratory
                        </label>

                        <input
                            type="text"
                            id="view_laboratory"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Borrower Name -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Borrower Name
                        </label>

                        <input
                            type="text"
                            id="view_borrower_name"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Student ID -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Student ID
                        </label>

                        <input
                            type="text"
                            id="view_student_id"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Section -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Section
                        </label>

                        <input
                            type="text"
                            id="view_section"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Item Name -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Item Name
                        </label>

                        <input
                            type="text"
                            id="view_item_name"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Quantity
                        </label>

                        <input
                            type="number"
                            id="view_quantity"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Borrowed Date -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Borrowed Date
                        </label>

                        <input
                            type="date"
                            id="view_borrowed_date"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Expected Return -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Expected Return
                        </label>

                        <input
                            type="date"
                            id="view_expected_return"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Returned Date -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Returned Date
                        </label>

                        <input
                            type="date"
                            id="view_returned_date"
                            class="form-control"
                            readonly>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">
                            Status
                        </label>

                        <select
                            id="view_status"
                            class="form-select"
                            disabled>

                            <option value="Borrowed">
                                Borrowed
                            </option>

                            <option value="Returned">
                                Returned
                            </option>

                            <option value="Overdue">
                                Overdue
                            </option>

                        </select>
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    <i class="fas fa-times me-1"></i>
                    Close

                </button>

            </div>

        </div>
    </div>
</div>