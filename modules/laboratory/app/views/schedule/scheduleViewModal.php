<div class="modal fade" id="scheduleViewModal" tabindex="-1" aria-labelledby="scheduleViewModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">

                <h5 class="modal-title" id="scheduleViewModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Laboratory Schedule Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>


            <!-- Body -->
            <div class="modal-body">

                <div class="row g-3">

                    <!-- Laboratory -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Laboratory
                        </label>

                        <input
                            type="text"
                            id="view_laboratory_name"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Subject Code -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Subject Code
                        </label>

                        <input
                            type="text"
                            id="view_subject_code"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Subject Name -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Subject Name
                        </label>

                        <input
                            type="text"
                            id="view_subject_name"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Instructor -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Instructor
                        </label>

                        <input
                            type="text"
                            id="view_instructor"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Section -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Section
                        </label>

                        <input
                            type="text"
                            id="view_section"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Day -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Day
                        </label>

                        <input
                            type="text"
                            id="view_day"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Start Time -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Start Time
                        </label>

                        <input
                            type="text"
                            id="view_start_time"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- End Time -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            End Time
                        </label>

                        <input
                            type="text"
                            id="view_end_time"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Semester -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Semester
                        </label>

                        <input
                            type="text"
                            id="view_semester"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- School Year -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            School Year
                        </label>

                        <input
                            type="text"
                            id="view_school_year"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Status -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Status
                        </label>

                        <input
                            type="text"
                            id="view_status"
                            class="form-control"
                            readonly>

                    </div>


                    <!-- Remarks -->
                    <div class="col-12">

                        <label class="form-label fw-bold">
                            Remarks
                        </label>

                        <textarea
                            id="view_remarks"
                            class="form-control"
                            rows="3"
                            readonly></textarea>

                    </div>

                </div>

            </div>


            <!-- Footer -->
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

