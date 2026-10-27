<div class="modal fade" id="psyEditBorrowModal" tabindex="-1" aria-labelledby="psyEditBorrowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form
                id="psyEditBorrowForm"
                action="<?= BASE_URL ?>/psy_borrow/update"
                method="POST">

                <!-- Hidden ID -->
                <input
                    type="hidden"
                    name="id"
                    id="edit_id">

                <div class="modal-header">

                    <h5 class="modal-title" id="psyEditBorrowModalLabel">
                        <i class="fas fa-edit me-2"></i>
                        Edit Borrow Record
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
                                name="laboratory"
                                id="edit_laboratory"
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
                                name="borrower_name"
                                id="edit_borrower_name"
                                class="form-control"
                                required>
                        </div>


                        <!-- Student ID -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Student ID
                            </label>

                            <input
                                type="text"
                                name="student_id"
                                id="edit_student_id"
                                class="form-control"
                                required>
                        </div>


                        <!-- Section -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Section
                            </label>

                            <input
                                type="text"
                                name="section"
                                id="edit_section"
                                class="form-control"
                                required>
                        </div>


                        <!-- Item Name -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Item Name
                            </label>

                            <input
                                type="text"
                                name="item_name"
                                id="edit_item_name"
                                class="form-control"
                                required>
                        </div>


                        <!-- Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity"
                                id="edit_quantity"
                                class="form-control"
                                min="1"
                                required>
                        </div>


                        <!-- Borrowed Date -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Borrowed Date
                            </label>

                            <input
                                type="date"
                                name="borrowed_date"
                                id="edit_borrowed_date"
                                class="form-control"
                                required>
                        </div>


                        <!-- Expected Return -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Expected Return
                            </label>

                            <input
                                type="date"
                                name="expected_return"
                                id="edit_expected_return"
                                class="form-control"
                                required>
                        </div>


                        <!-- Returned Date -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Returned Date
                            </label>

                            <input
                                type="date"
                                name="returned_date"
                                id="edit_returned_date"
                                class="form-control">
                        </div>


                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                id="edit_status"
                                class="form-select"
                                required>

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
                        class="btn btn-warning">

                        <i class="fas fa-save me-1"></i>
                        Update

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