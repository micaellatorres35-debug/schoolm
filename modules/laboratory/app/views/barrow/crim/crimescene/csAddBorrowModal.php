<div class="modal fade" id="csAddBorrowModal" tabindex="-1" aria-labelledby="csAddBorrowModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form
                id="csAddBorrowForm"
                action="<?= BASE_URL ?>/crimescene-borrow/create"
                method="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="csAddBorrowModalLabel">
                        <i class="fas fa-hand-holding me-2"></i>
                        Add Borrow Record
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
                            <label class="form-label">Laboratory</label>

                            <input
                                type="text"
                                name="laboratory"
                                class="form-control"
                                value="Crime Scene Lab"
                                readonly>
                        </div>

                        <!-- Borrower Name -->
                        <div class="col-md-6">
                            <label class="form-label">Borrower Name</label>

                            <input
                                type="text"
                                name="borrower_name"
                                class="form-control"
                                placeholder="Enter borrower name"
                                required>
                        </div>

                        <!-- Student ID -->
                        <div class="col-md-6">
                            <label class="form-label">Student ID</label>

                            <input
                                type="text"
                                name="student_id"
                                class="form-control"
                                placeholder="Enter student ID"
                                required>
                        </div>

                        <!-- Section -->
                        <div class="col-md-6">
                            <label class="form-label">Section</label>

                            <input
                                type="text"
                                name="section"
                                class="form-control"
                                placeholder="Enter section"
                                required>
                        </div>

                        <!-- Item Name -->
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>

                            <input
                                type="text"
                                name="item_name"
                                class="form-control"
                                placeholder="Enter item name"
                                required>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>

                            <input
                                type="number"
                                name="quantity"
                                class="form-control"
                                min="1"
                                placeholder="Enter quantity"
                                required>
                        </div>

                        <!-- Borrowed Date -->
                        <div class="col-md-6">
                            <label class="form-label">Borrowed Date</label>

                            <input
                                type="date"
                                name="borrowed_date"
                                class="form-control"
                                required>
                        </div>

                        <!-- Expected Return -->
                        <div class="col-md-6">
                            <label class="form-label">Expected Return</label>

                            <input
                                type="date"
                                name="expected_return"
                                class="form-control"
                                required>
                        </div>

                        <!-- Returned Date -->
                        <div class="col-md-6">
                            <label class="form-label">Returned Date</label>

                            <input
                                type="date"
                                name="returned_date"
                                class="form-control">
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>

                            <select
                                name="status"
                                class="form-select"
                                required>

                                <option value="" disabled selected>
                                    -- Select Status --
                                </option>

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
                        type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>
                        Save

                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>