<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>


<main class="main-content">
<div class="container">

<div class="container-fluid py-4">

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start">

                <div>
                    <h4 class="mb-1"><?= $applicant_first_name ?? ''?> <?= $applicant_surname ?? '' ?> <?= $applicant_suffix ?? ''?></h4>

                    <p class="text-muted mb-1">
                        <?= $applicant_number ?? '' ?>
                    </p>

                    <span class="badge bg-primary">
                        <?= $applicant_course_code ?? 'None' ?>
                    </span>

                    <span class="badge bg-info">
                        <?= $admission_type ?>
                    </span>

                   
                </div>

                <div>
                    <button class="btn btn-primary btn-sm" id="editBtn">
                        Update Student Information
                    </button>

                     <button class="btn btn-success btn-sm" id="insertDocuments">
                        Manage Requirements
                    </button>

                    <button class="btn btn-secondary btn-sm" id="printPdf">
                        Print
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm">

        <div class="card-header p-0">

            <ul class="nav nav-tabs card-header-tabs" role="tablist">

                <li class="nav-item">
                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#personal">
                        Personal
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#contact">
                        Contact
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#academic">
                        Academic
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#parent">
                        Parent/Guardian
                    </button>
                </li>

                 <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#enroll">
                        Enrollment
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#documents">
                        Documents
                    </button>
                </li>

            </ul>

        </div>

        <div class="card-body">

            <div class="tab-content">

                <!-- Personal -->
                 
                 <?php include VIEW_PATH . '/overviews/personal.php'; ?>
              
                <!-- Contact -->
                
                 <?php include VIEW_PATH . '/overviews/contact.php'; ?>

                <!-- Academic -->

                 <?php include VIEW_PATH . '/overviews/academic.php'; ?>

                <!-- Enrollment -->

                  <?php include VIEW_PATH . '/overviews/enrollment.php'; ?>
                 
                <!-- parent or guardian information -->

                 <?php include VIEW_PATH . '/overviews/parent.php'; ?>

                <!-- Documents -->
              
                 <?php include VIEW_PATH . '/overviews/documents.php'; ?>

            </div>

        </div>

    </div>


</div>

</div>
</main>



<!-- EDIT STUDENT INFO MODAL -->
<div class="modal fade"
     id="editStudentInfo"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-labelledby="editModalTitle"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <!-- FORM IS NOW THE MODAL CONTENT -->
        <form id="updateStudentForm"
              action=""
              method="POST"
              class="modal-content shadow-lg border-0 rounded-3 needs-validation"
              novalidate>

            <input type="hidden" name="action" value="update_student">
            <input type="hidden"
                   name="applicant_id"
                   id="edit_applicant_id"
                   value="<?= htmlspecialchars(string: $applicant_id ?? '') ?>">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white">

                <div>
                    <h5 class="modal-title mb-0" id="editModalTitle">
                        Edit Student Information
                    </h5>

                    <small class="opacity-75">
                        Update personal, academic, and contact details
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>


            <!-- SCROLLABLE BODY -->
            <div class="modal-body p-4">

                <!-- ACADEMIC DETAILS -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2 fw-bold">
                        Academic Details
                    </h6>

                    <div class="p-3 bg-light rounded border">

                        <div class="row g-3 mb-2">

                            <div class="col-md-4">
                                <label for="edit_admission_type"
                                       class="form-label small text-muted mb-1">
                                    Admission Type
                                </label>

                                <select class="form-select form-select-sm"
                                        id="edit_admission_type"
                                        name="admission_type"
                                        required>

                                    <option value="freshmen"
                                        <?= ($admission_type ?? '') === 'freshmen' ? 'selected' : '' ?>>
                                        Freshmen
                                    </option>

                                    <option value="transferee"
                                        <?= ($admission_type ?? '') === 'transferee' ? 'selected' : '' ?>>
                                        Transferee
                                    </option>

                                    <option value="returnee"
                                        <?= ($admission_type ?? '') === 'returnee' ? 'selected' : '' ?>>
                                        Returnee
                                    </option>

                                    <option value="senior"
                                        <?= ($admission_type ?? '') === 'senior' ? 'selected' : '' ?>>
                                        Senior High
                                    </option>

                                </select>
                            </div>


                            <div class="col-md-4">

                                <label for="edit_working_student"
                                       class="form-label small text-muted mb-1">
                                    Working Student?
                                </label>

                                <select class="form-select form-select-sm"
                                        id="edit_working_student"
                                        name="working_student"
                                        required>

                                    <option value="No"
                                        <?= ($applicant_working_student ?? '') === 'No' ? 'selected' : '' ?>>
                                        No
                                    </option>

                                    <option value="Yes"
                                        <?= ($applicant_working_student ?? '') === 'Yes' ? 'selected' : '' ?>>
                                        Yes
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-4">

                                <label for="edit_school_last_attended"
                                       class="form-label small text-muted mb-1">
                                    School Last Attended
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_school_last_attended"
                                       name="school_last_attended"
                                       value="<?= htmlspecialchars($applicant_last_school ?? '') ?>"
                                       required>

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-md-4">

                                <label for="edit_year_graduated"
                                       class="form-label small text-muted mb-1">
                                    Year Graduated
                                </label>

                                <input type="number"
                                       class="form-control form-control-sm"
                                       id="edit_year_graduated"
                                       name="year_graduated"
                                       value="<?= htmlspecialchars($applicant_year_graduated ?? '') ?>"
                                       required>

                            </div>


                            <div class="col-md-4">
                                <label for="edit_course_id" class="form-label small text-muted mb-1">
                                    Course
                                </label>
                                <select class="form-select form-select-sm" 
                                        id="edit_course_id" 
                                        name="course_id">
                                    <option value="">Select Course</option>
                                    <?php foreach ($all_courses as $course): ?>
                                        <option value="<?= $course['id'] ?>" 
                                            <?= (isset($course['id']) && $applicant_course_id == $course['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($course['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            <div class="col-md-4">

                                <label for="edit_how_hear"
                                       class="form-label small text-muted mb-1">
                                    How Did You Hear About Us?
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_how_hear"
                                       name="how_hear"
                                       value="<?= htmlspecialchars( $applicant_how_hear ?? '') ?>">

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PERSONAL INFORMATION -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2 fw-bold">
                        Personal Information
                    </h6>

                    <div class="p-3 border rounded">

                        <div class="row g-3 mb-2">

                            <div class="col-md-3">
                                <label for="edit_surname"
                                       class="form-label small text-muted mb-1">
                                    Surname
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_surname"
                                       name="surname"
                                       value="<?= htmlspecialchars($applicant_surname ?? '') ?>"
                                       required>
                            </div>


                            <div class="col-md-3">
                                <label for="edit_first_name"
                                       class="form-label small text-muted mb-1">
                                    First Name
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_first_name"
                                       name="first_name"
                                       value="<?= htmlspecialchars($applicant_first_name ?? '') ?>"
                                       required>
                            </div>


                            <div class="col-md-3">
                                <label for="edit_middle_name"
                                       class="form-label small text-muted mb-1">
                                    Middle Name
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_middle_name"
                                       name="middle_name"
                                       value="<?= htmlspecialchars($applicant_middle_name ?? '') ?>">
                            </div>


                            <div class="col-md-3">
                                <label for="edit_suffix"
                                       class="form-label small text-muted mb-1">
                                    Suffix
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_suffix"
                                       name="suffix"
                                       value="<?= htmlspecialchars($applicant_suffix ?? '') ?>">
                            </div>

                        </div>


                        <div class="row g-3 mb-2">

                            <div class="col-md-3">

                                <label for="edit_sex"
                                       class="form-label small text-muted mb-1">
                                    Sex
                                </label>

                                <select class="form-select form-select-sm"
                                        id="edit_sex"
                                        name="sex"
                                        required>

                                    <option value="Male"
                                        <?= ($applicant_sex ?? '') === 'Male' ? 'selected' : '' ?>>
                                        Male
                                    </option>

                                    <option value="Female"
                                        <?= ($applicant_sex ?? '') === 'Female' ? 'selected' : '' ?>>
                                        Female
                                    </option>

                                    <option value="Other"
                                        <?= ($applicant_sex ?? '') === 'Other' ? 'selected' : '' ?>>
                                        Other
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-3">

                                <label for="edit_date_of_birth"
                                       class="form-label small text-muted mb-1">
                                    Date of Birth
                                </label>

                                <input type="date"
                                       class="form-control form-control-sm"
                                       id="edit_date_of_birth"
                                       name="date_of_birth"
                                       value="<?= !empty($applicant_dob) ? date('Y-m-d', strtotime($applicant_dob)) : '' ?>"
                                       required>

                            </div>


                            <div class="col-md-2">

                                <label for="edit_age"
                                       class="form-label small text-muted mb-1">
                                    Age
                                </label>

                                <input type="number"
                                       class="form-control form-control-sm bg-light"
                                       id="edit_age"
                                       name="age"
                                       value="<?= !empty($applicant_dob) ? date_diff(date_create($applicant_dob), date_create('today'))->y : '' ?>"
                                       readonly>

                            </div>


                            <div class="col-md-4">

                                <label for="edit_place_of_birth"
                                       class="form-label small text-muted mb-1">
                                    Place of Birth
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_place_of_birth"
                                       name="place_of_birth"
                                       value="<?= htmlspecialchars($applicant_place_of_birth ?? '') ?>"
                                       required>

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label for="edit_civil_status"
                                       class="form-label small text-muted mb-1">
                                    Civil Status
                                </label>

                                <select class="form-select form-select-sm"
                                        id="edit_civil_status"
                                        name="civil_status"
                                        required>

                                    <option value="Single"
                                        <?= ($applicant_civil_status ?? '') === 'Single' ? 'selected' : '' ?>>
                                        Single
                                    </option>

                                    <option value="Married"
                                        <?= ($applicant_civil_status ?? '') === 'Married' ? 'selected' : '' ?>>
                                        Married
                                    </option>

                                    <option value="Divorced"
                                        <?= ($applicant_civil_status ?? '') === 'Divorced' ? 'selected' : '' ?>>
                                        Divorced
                                    </option>

                                    <option value="Widowed"
                                        <?= ($applicant_civil_status ?? '') === 'Widowed' ? 'selected' : '' ?>>
                                        Widowed
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-6">

                                <label for="edit_religion"
                                       class="form-label small text-muted mb-1">
                                    Religion
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_religion"
                                       name="religion"
                                       value="<?= htmlspecialchars($applicant_religion ?? '') ?>">

                            </div>

                        </div>

                    </div>

                </div>


                <!-- CONTACT & ADDRESS INFORMATION -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2 fw-bold">
                        Contact & Address Information
                    </h6>

                    <div class="p-3 border rounded">

                        <div class="row g-3 mb-2">

                            <div class="col-md-4">

                                <label for="edit_email"
                                       class="form-label small text-muted mb-1">
                                    Email Address
                                </label>

                                <input type="email"
                                       class="form-control form-control-sm"
                                       id="edit_email"
                                       name="email"
                                       value="<?= htmlspecialchars($applicant_email ?? '') ?>"
                                       required>

                            </div>


                            <div class="col-md-4">

                                <label for="edit_contact_number"
                                       class="form-label small text-muted mb-1">
                                    Contact Number
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_contact_number"
                                       name="contact_number"
                                       value="<?= htmlspecialchars($applicant_contact_number ?? '') ?>"
                                       required>

                            </div>


                            <div class="col-md-2">

                                <label for="edit_facebook"
                                       class="form-label small text-muted mb-1">
                                    Facebook
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_facebook"
                                       name="facebook"
                                       value="<?= htmlspecialchars($applicant_facebook ?? '') ?>">

                            </div>


                            <div class="col-md-2">

                                <label for="edit_messenger"
                                       class="form-label small text-muted mb-1">
                                    Messenger
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_messenger"
                                       name="messenger"
                                       value="<?= htmlspecialchars($applicant_messenger ?? '') ?>">

                            </div>

                        </div>


                        <div class="row g-3 mb-2">

                            <div class="col-md-4">

                                <label for="edit_address_barangay"
                                       class="form-label small text-muted mb-1">
                                    Barangay
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_address_barangay"
                                       name="address_barangay"
                                       value="<?= htmlspecialchars($applicant_barangay ?? '') ?>"
                                       required>

                            </div>


                            <div class="col-md-4">

                                <label for="edit_address_city"
                                       class="form-label small text-muted mb-1">
                                    City/Municipality
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_address_city"
                                       name="address_city"
                                       value="<?= $applicant_city ?? '' ?>"
                                       required>

                            </div>


                            <div class="col-md-4">

                                <label for="edit_address_province"
                                       class="form-label small text-muted mb-1">
                                    Province
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_address_province"
                                       name="address_province"
                                       value="<?= htmlspecialchars($applicant_province ?? '') ?>"
                                       required>

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-md-12">

                                <label for="edit_address_complete"
                                       class="form-label small text-muted mb-1">
                                    Complete Street Address
                                </label>

                                <textarea class="form-control form-control-sm"
                                          id="edit_address_complete"
                                          name="address_complete"
                                          rows="2"><?= htmlspecialchars($applicant_address_complete ?? '') ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PARENT / GUARDIAN DETAILS -->
                <div class="mb-4">

                    <h6 class="text-muted text-uppercase small mb-2 fw-bold">
                        Parent / Guardian Details
                    </h6>

                    <div class="p-3 border rounded">

                        <div class="row g-3 mb-2">

                            <div class="col-md-6">

                                <label for="edit_parent_full_name"
                                       class="form-label small text-muted mb-1">
                                    Full Name
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_parent_full_name"
                                       name="parent_full_name"
                                       value="<?= htmlspecialchars($applicant_parent_name ?? '') ?>"
                                       required>

                            </div>


                            <div class="col-md-6">

                                <label for="edit_parent_contact"
                                       class="form-label small text-muted mb-1">
                                    Contact Number
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_parent_contact"
                                       name="parent_contact"
                                       value="<?= htmlspecialchars($applicant_parent_contact ?? '') ?>">

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-md-12">

                                <label for="edit_parent_address"
                                       class="form-label small text-muted mb-1">
                                    Parent Address
                                </label>

                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="edit_parent_address"
                                       name="parent_address"
                                       value="<?= htmlspecialchars($applicant_parent_address ?? '') ?>">

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FOOTER -->
            <div class="modal-footer d-flex justify-content-between bg-white border-top">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit"
                        class="btn btn-primary">
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>

<!-- REQUIRED DOCUMENT INSERT -->
<div class="modal fade" id="insertDocumentModal" tabindex="-1" aria-labelledby="exampleModalLabel" data-bs-backdrop="static"
     data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

<div class="modal-header bg-success text-white">

    <h5 class="modal-title" id="exampleModalLabel">
        Insert Student Requirements
    </h5>

    <button type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close">
    </button>

</div>

<form id="insertStudentDocumentForm"
      method="POST"
      enctype="multipart/form-data">

    <div class="modal-body">

        <input type="hidden"
               name="student_id"
               id="student_id"
               value="<?= htmlspecialchars($student_id ?? '') ?>">

        <!-- Requirement -->
        <div class="mb-3">

            <label for="requirement_id" class="form-label">
                Requirement
            </label>

            <select
                class="form-select"
                id="requirement_id"
                name="requirement_id"
                required>

                <option value="" selected disabled>
                    Select requirement
                </option>

            </select>

        </div>

        <!-- File -->
        <div class="mb-3">

            <label for="requirement_file" class="form-label">
                Upload Document
            </label>

            <input
                type="file"
                class="form-control"
                id="requirement_file"
                name="requirement_file"
                accept=".pdf,.jpg,.jpeg,.png"
                required>

            <div class="form-text">
                Accepted formats: PDF, JPG, JPEG, PNG.
            </div>

        </div>

        <!-- Notes -->
        <div class="mb-3">

            <label for="requirement_notes" class="form-label">
                Notes
            </label>

            <textarea
                class="form-control"
                id="requirement_notes"
                name="requirement_notes"
                rows="2"
                placeholder="Optional notes..."></textarea>

        </div>

        <hr>

        <!-- Requirements List -->
        <div class="mb-3">

            <h6 class="fw-semibold mb-1">
                Requirements List
            </h6>

            <small class="text-muted">
                List of required documents
            </small>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Requirement</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Submitted Date</th>
                    </tr>
                </thead>

                <tbody id="studentRequirementsTable">
                </tbody>

            </table>

        </div>

    </div>

    <!-- Footer -->
    <div class="modal-footer">

        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
            Cancel
        </button>

        <button type="submit"
                class="btn btn-primary">
            Save
        </button>

    </div>

</form>

        </div>
    </div>
</div>

</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.5/js/lightbox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script>
     const student_id = <?= $student_id ?>;
</script>
<script src="<?= BASE_URL ?>/js/student_view.js"></script>


<?php include  __DIR__ .'/../partials/footer.php'; ?>