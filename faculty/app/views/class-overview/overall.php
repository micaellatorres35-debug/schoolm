<!-- Overall Grades -->
<div class="tab-pane fade"
     id="overall"
     role="tabpanel">

    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row
                justify-content-between
                align-items-lg-center gap-3 mb-4">

        <div>
            <h5 class="fw-bold mb-1">
                Overall Grades
            </h5>

            <div class="text-muted small">
                Review student grades and submit final grades to the registrar.
            </div>
        </div>

        <!-- Status -->
        <div>
            <span class="badge text-bg-warning">
                Draft
            </span>
        </div>

    </div>


    <!-- Overall Grade Table -->
    <div class="card border-0 shadow-sm">

       


        <!-- Table -->
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover
                             align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="ps-4">
                                Student
                            </th>

                            <th class="text-center">
                                Prelim
                            </th>

                            <th class="text-center">
                                Midterm
                            </th>

                            <th class="text-center">
                                Finals
                            </th>

                            <th class="text-center">
                                Overall
                            </th>

                            <th class="text-center">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody id="show_overall_grade_list">

                        <!-- Student 1 -->
                        <tr>

                            <td class="ps-4">

                                <div class="fw-semibold">
                                    2026-001
                                </div>

                                <small class="text-muted">
                                    Juan Dela Cruz
                                </small>

                            </td>

                            <td class="text-center">
                                85.00
                            </td>

                            <td class="text-center">
                                88.00
                            </td>

                            <td class="text-center">
                                90.00
                            </td>

                            <td class="text-center fw-bold">
                                87.67
                            </td>

                            <td class="text-center">

                                <span class="badge text-bg-success">
                                    Complete
                                </span>

                            </td>

                        </tr>


                        <!-- Student 2 -->
                        <tr>

                            <td class="ps-4">

                                <div class="fw-semibold">
                                    2026-002
                                </div>

                                <small class="text-muted">
                                    Maria Santos
                                </small>

                            </td>

                            <td class="text-center">
                                90.00
                            </td>

                            <td class="text-center">
                                91.00
                            </td>

                            <td class="text-center">
                                89.00
                            </td>

                            <td class="text-center fw-bold">
                                90.00
                            </td>

                            <td class="text-center">

                                <span class="badge text-bg-success">
                                    Complete
                                </span>

                            </td>

                        </tr>


                        <!-- Student 3 -->
                        <tr>

                            <td class="ps-4">

                                <div class="fw-semibold">
                                    2026-003
                                </div>

                                <small class="text-muted">
                                    Pedro Santos
                                </small>

                            </td>

                            <td class="text-center">
                                87.00
                            </td>

                            <td class="text-center">
                                92.00
                            </td>

                            <td class="text-center">
                                —
                            </td>

                            <td class="text-center fw-bold">
                                —
                            </td>

                            <td class="text-center">

                                <span class="badge text-bg-danger">
                                    Missing
                                </span>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Footer -->
        <div class="card-footer bg-white">

            <div class="d-flex flex-column
                        flex-md-row
                        justify-content-end
                        align-items-md-center gap-3">

                <button
                    type="button"
                    id="submitGradesBtn"
                    class="btn btn-primary">

                    <i class="fa-solid fa-paper-plane me-1"></i>
                    Submit Grades to Registrar

                </button>

            </div>

        </div>

    </div>

</div>