<!-- Add Schedule Modal -->
<div
    class="modal fade"
    id="addScheduleModal"
    tabindex="-1"
    aria-labelledby="addScheduleModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="addScheduleModalLabel">

                    Add Laboratory Schedule

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <!-- Form -->
            <form id="addScheduleForm">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Laboratory -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Laboratory
                            </label>

                            <select
                                name="lab_id"
                                class="form-select"
                                required>

                                <option value="10">
                                    Physics Laboratory
                                </option>

                                <option value="2">
                                    Psychology Laboratory
                                </option>

                                <option value="3">
                                    HE Laboratory
                                </option>

                                <option value="4">
                                    Chemistry Laboratory
                                </option>

                                <option value="5">
                                    Fingerprint Laboratory
                                </option>

                                <option value="6">
                                    Crime Scene Laboratory
                                </option>

                                <option value="7">
                                    Ballistics Laboratory
                                </option>

                                <option value="8">
                                    Questioned Documents Laboratory
                                </option>

                                <option value="9">
                                    Defense and Tactics Laboratory
                                </option>

                                <option value="1">
                                    IT Laboratory 1
                                </option>

                                <option value="11">
                                    IT Laboratory 2
                                </option>

                                <option value="12">
                                    IT Laboratory 3
                                </option>

                            </select>
                        </div>

                        <!-- Subject Code -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Subject Code
                            </label>

                            <input
                                type="text"
                                name="subject_code"
                                class="form-control"
                                placeholder="e.g. IT101"
                                required>

                        </div>

                        <!-- Subject Name -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Subject Name
                            </label>

                            <input
                                type="text"
                                name="subject_name"
                                class="form-control"
                                placeholder="e.g. Web Development"
                                required>

                        </div>


                        <!-- Instructor -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Instructor
                            </label>
                            <input
                                type="text"
                                name="instructor"
                                class="form-control"
                                placeholder="Instructor name"
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
                                class="form-control"
                                placeholder="BSIT-4A"
                                required>

                        </div>


                        <!-- Day -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Day
                            </label>

                            <select
                                name="day"
                                class="form-select"
                                required>

                                <option value="">
                                    Select Day
                                </option>

                                <option value="Monday">
                                    Monday
                                </option>

                                <option value="Tuesday">
                                    Tuesday
                                </option>

                                <option value="Wednesday">
                                    Wednesday
                                </option>

                                <option value="Thursday">
                                    Thursday
                                </option>

                                <option value="Friday">
                                    Friday
                                </option>

                                <option value="Saturday">
                                    Saturday
                                </option>

                            </select>

                        </div>


                        <!-- Start Time -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Start Time
                            </label>

                            <input
                                type="time"
                                name="start_time"
                                class="form-control"
                                required>

                        </div>


                        <!-- End Time -->
                        <div class="col-md-6">

                            <label class="form-label">
                                End Time
                            </label>

                            <input
                                type="time"
                                name="end_time"
                                class="form-control"
                                required>

                        </div>


                        <!-- Semester -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Semester
                            </label>

                            <select
                                name="semester"
                                class="form-select"
                                required>

                                <option value="1st">
                                    1st Semester
                                </option>

                                <option value="2nd">
                                    2nd Semester
                                </option>

                                <option value="Summer">
                                    Summer
                                </option>

                            </select>

                        </div>


                        <!-- School Year -->
                        <div class="col-md-6">

                            <label class="form-label">
                                School Year
                            </label>

                            <input
                                type="text"
                                name="school_year"
                                class="form-control"
                                placeholder="2026-2027"
                                required>

                        </div>


                        <!-- Status -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                                required>

                                <option value="Scheduled">
                                    Scheduled
                                </option>

                                <option value="Completed">
                                    Completed
                                </option>

                                <option value="Cancelled">
                                    Cancelled
                                </option>

                            </select>

                        </div>


                        <!-- Remarks -->
                        <div class="col-12">

                            <label class="form-label">
                                Remarks
                            </label>

                            <textarea
                                name="remarks"
                                class="form-control"
                                rows="3"
                                placeholder="Optional remarks"></textarea>

                        </div>

                    </div>

                </div>


                <!-- Modal Footer -->
                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-save me-1"></i>
                        Save Schedule

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>