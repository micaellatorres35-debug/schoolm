
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
                        Freshman
                    </span>

                    <span class="badge bg-warning text-dark">
                        Pending Review
                    </span>
                </div>

                <div>
                    <button class="btn btn-success btn-sm" id="approveBtn">
                        Approve
                    </button>

                    <button class="btn btn-danger btn-sm" id="declineBtn">
                        Reject
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
                        data-bs-target="#documents">
                        Documents
                    </button>
                </li>

            </ul>

        </div>

        <div class="card-body">

            <div class="tab-content">

                <!-- Personal -->
                <div class="tab-pane fade show active" id="personal">

                    <h5 class="mt-3 fw-bold">PERSONAL INFORMATION</h5>

                    <div class="row mt-4">

                        <div class="col-md-3 mb-3">
                            <strong>First Name</strong>
                            <p><?= $applicant_first_name ?? '' ?></p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>Last Name</strong>
                            <p><?= $applicant_surname ?? '' ?></p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>Middle Name</strong>
                            <p><?= $applicant_middle_name ?? 'N/A' ?></p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>Suffix</strong>
                            <p><?= !empty($applicant_suffix) ? $applicant_suffix : '-' ?></p>
                        </div>


                        <div class="col-md-3 mb-3">
                            <strong>Birth Date</strong>
                            <p><?= $applicant_dob ?? '' ?></p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>Sex</strong>
                            <p><?= $applicant_sex ?? '' ?></p>
                        </div>

                         <div class="col-md-3 mb-3">
                            <strong>Place of Birth</strong>
                            <p><?= $applicant_place_of_birth ?? '' ?></p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>Civil Status</strong>
                            <p><?= $applicant_civil_status ?? '' ?></p>
                        </div>

                        

                    </div>

                </div>

                <!-- Contact -->
                <div class="tab-pane fade" id="contact">

                <h5 class="mt-3 fw-bold">CONTACT INFORMATION</h5>

                <div class="row mt-4">

                    <div class="col-md-3 mb-3">
                            <strong>Email</strong>
                            <p><?= $applicant_email ?? '' ?></p>
                     </div>

                      <div class="col-md-3 mb-3">
                            <strong>Mobile No.</strong>
                            <p><?= $applicant_contact_number ?? '' ?></p>
                     </div>

                </div>

                <h5 class="mt-3 fw-bold">ADDRESS</h5>

                 <div class="row mt-4">

                    <div class="col-md-3 mb-3">
                            <strong>Barangay</strong>
                            <p><?= $applicant_barangay ?? '' ?></p>
                     </div>

                      <div class="col-md-3 mb-3">
                            <strong>City</strong>
                            <p><?= $applicant_city ?? '' ?></p>
                     </div>

                       <div class="col-md-3 mb-3">
                            <strong>Province</strong>
                            <p><?= $applicant_province ?? '' ?></p>
                     </div>

                       <div class="col-md-3 mb-3">
                            <strong>Complete Address</strong>
                            <p><?= $applicant_address_complete ?? '' ?></p>
                     </div>

                </div>
                  
                </div>

                <!-- Academic -->

                

                <div class="tab-pane fade" id="academic">

                <h5 class="mt-3 fw-bold">ACADEMIC INFORMATION</h5>

                <div class="row mt-4">

                <div class="col-md-3 mb-3">
                            <strong>Course Applied</strong>
                            <p><?=  $applicant_course_code ? " $applicant_course_name ($applicant_course_code) "  : 'None' ?></p>
                </div>

                <div class="col-md-3 mb-3">
                            <strong>School Last Attended</strong>
                            <p><?= $applicant_last_school ?? '' ?></p>
                </div>

                 <div class="col-md-3 mb-3">
                            <strong>Year Graduated</strong>
                            <p><?= $applicant_year_graduated ?? '' ?></p>
                </div>

                 <div class="col-md-3 mb-3">
                            <strong>Submitted At</strong>
                            <p><?= $applicant_submission_date ?? '' ?></p>
                </div>

                </div>
                
                </div>

                <!-- parent or guardian information -->

                 <div class="tab-pane fade" id="parent">

                 <h5 class="mt-3 fw-bold">PARENT/GUARDIAN INFORMATION</h5>

                  <div class="row mt-4">

                    <div class="col-md-3 mb-3">
                            <strong>Parent Name</strong>
                            <p><?= $applicant_parent_name ?? '' ?></p>
                   </div>

                      <div class="col-md-3 mb-3">
                            <strong>Parent Contact</strong>
                            <p><?= $applicant_parent_contact ?? '' ?></p>
                   </div>

                      <div class="col-md-3 mb-3">
                            <strong>Parent Address</strong>
                            <p><?= $appplicant_parent_address ?? '' ?></p>
                   </div>

                  </div>

               

                 

                 </div>

                <!-- Documents -->
                <div class="tab-pane fade" id="documents">

                <h5 class="mt-3 fw-bold">DOCUMENTS INFORMATION</h5>

                    <table class="table mt-4">

                        <thead>
                            <tr>
                                <th>Required Documents</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody id="enrollee-documents-table-body">

                        </tbody>

                    </table>

                </div>

            </div>

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
     const applicantId = <?= $applicant_id ?>;
</script>
<script src="<?= BASE_URL ?>/js/enrollee_view.js"></script>


<?php include  __DIR__ .'/../partials/footer.php'; ?>