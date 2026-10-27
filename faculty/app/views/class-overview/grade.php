
<div
    class="tab-pane fade"
    id="grades"
    role="tabpanel">

    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row
                justify-content-between
                align-items-lg-center gap-3 mb-4">

        <div>
            <h5 class="fw-bold mb-1">
                Grades
            </h5>

            <div class="text-muted small">
                Upload, review, and submit student grades
            </div>
        </div>

        <!-- Grade Period -->
        <div class="d-flex align-items-center gap-2">

            <label for="gradePeriod"
                   class="fw-semibold text-nowrap">
                Grade Period:
            </label>

            <select
                class="form-select"
                id="gradePeriod"
                style="width: 160px;">

                <option value="prelim">
                    Prelim
                </option>

                <option value="midterm">
                    Midterm
                </option>

                <option value="final">
                    Final
                </option>

            </select>

        </div>

    </div>


    <!-- CSV Actions -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3">

                <!-- Download Template -->
                <div class="col-md-6">

                    <div class="border rounded p-3 h-100">

                        <div class="d-flex align-items-start">

                            <div class="me-3">

                                <span class="badge text-bg-primary p-3">
                                    <i class="fa-solid fa-file-csv fa-lg"></i>
                                </span>

                            </div>

                            <div>

                                <h6 class="fw-bold mb-1">
                                    Grade Template
                                </h6>

                                <p class="text-muted small mb-3">
                                    Download the CSV template for
                                    this class.
                                </p>

                                <button
                                    type="button"
                                    id="excelBtn"
                                    class="btn btn-outline-primary btn-sm">

                                    <i class="fa-solid fa-download me-1"></i>
                                    Download Template

                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Upload CSV -->
                <div class="col-md-6">

                    <div class="border rounded p-3 h-100">

                        <div class="d-flex align-items-start">

                            <div class="me-3">

                                <span class="badge text-bg-success p-3">
                                    <i class="fa-solid fa-upload fa-lg"></i>
                                </span>

                            </div>

                            <div class="flex-grow-1 ">

                                <h6 class="fw-bold mb-1">
                                    Upload Grades
                                </h6>

                                <p class="text-muted small mb-3">
                                    Upload the completed CSV file
                                    for this class.
                                </p>

                                <input
                                    type="file"
                                    class="form-control form-control-sm mb-3"
                                    id="excelFile"
                                    >


                                  <button
                                    type="button"
                                    id="importExcelBtn"
                                    class="btn btn-outline-primary btn-sm">

                                    <i class="fa-solid fa-download me-1"></i>
                                   Import CSV

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    

  


        <!-- Grade Preview -->
        <div class="card border-0 shadow-sm">

            <!-- Preview Header -->
            <div class="card-header bg-white">

                <div class="d-flex flex-column
                            flex-md-row
                            justify-content-between
                            align-items-md-center gap-2">

                    <div>

                        <h6 class="fw-bold mb-1">
                            Grade Preview
                        </h6>

                        <div class="text-muted small">
                            Review uploaded grades before submission.
                        </div>

                    </div>

                    <span class="badge text-bg-warning">
                        Draft
                    </span>

                </div>

            </div>

            


            <!-- Grade Table -->
            <div class="card-body p-0">


                <div class="table-responsive">

                   <form id="gradeForm">

                    <table class="table table-hover
                                align-middle mb-0">

                     

                        <thead class="table-light">

                            <tr>

                                <th class="ps-4">
                                    Student
                                </th>

                                <th class="text-center">
                                    Written Works
                                </th>

                                <th class="text-center">
                                    Performance
                                </th>

                                <th class="text-center">
                                    Exam
                                </th>

                                <th class="text-center">
                                    Grade
                                </th>

                                <th class="text-center">
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody id="show_grade_list">

                            <tr>

                                <td class="ps-4">

                                    <div class="fw-semibold">
                                        2026-001
                                    </div>

                                    <small class="text-muted">
                                        Juan Dela Cruz
                                    </small>

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="85">

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="90">

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="88">

                                </td>


                                <td class="text-center fw-bold">
                                    88.33
                                </td>


                                <td class="text-center">

                                    <span class="badge text-bg-success">
                                        Valid
                                    </span>

                                </td>

                            </tr>


                            <tr>

                                <td class="ps-4">

                                    <div class="fw-semibold">
                                        2026-002
                                    </div>

                                    <small class="text-muted">
                                        Maria Santos
                                    </small>

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="90">

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="88">

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="92">

                                </td>


                                <td class="text-center fw-bold">
                                    90.00
                                </td>


                                <td class="text-center">

                                    <span class="badge text-bg-success">
                                        Valid
                                    </span>

                                </td>

                            </tr>


                            <tr>

                                <td class="ps-4">

                                    <div class="fw-semibold">
                                        2026-003
                                    </div>

                                    <small class="text-muted">
                                        Pedro Santos
                                    </small>

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="87">

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="92">

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        class="form-control
                                            form-control-sm
                                            text-center"
                                        min="0"
                                        max="100"
                                        value="89">

                                </td>


                                <td class="text-center fw-bold">
                                    89.33
                                </td>


                                <td class="text-center">

                                    <span class="badge text-bg-success">
                                        Valid
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="card-footer bg-white">

                <div class="d-flex flex-column
                            flex-md-row
                            justify-content-end
                            align-items-md-center gap-3">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Save Grades

                    </button>

                </div>

                

          

            </div>

              </form>

        </div>

</div>  