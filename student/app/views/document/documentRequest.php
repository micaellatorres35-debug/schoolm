<?php include  __DIR__ .'/../partials/header.php'; ?>
<?php include __DIR__ .'/../partials/sidebar.php'; ?>

<main class="main-content bg-light pb-5">
    <div class="container-fluid px-4">


    <div class="mb-4">

        <small class="text-primary">
            Document Request
        </small>

        <h2 class="fw-bold mt-1">
            Document Request
        </h2>

    </div>

    <div class="row mb-4">

    <div class="col-md-4">

        <div class="card shadow-sm">

            <div class="card-body text-center">

                <i class="bi bi-file-earmark-text fs-1"></i>

                <h5 class="mt-3">
                    Transcript of Records
                </h5>

                <p class="text-muted">
                    Processing time: 3-5 days
                </p>

                <button class="btn btn-primary" id="torProcessing">
                    Request TOR
                </button>

            </div>

        </div>

    </div>

     <div class="col-md-4">

        <div class="card shadow-sm">

            <div class="card-body text-center">

                <i class="bi bi-file-earmark-text fs-1"></i>

                <h5 class="mt-3">
                    Certificate of Registration
                </h5>

                <p class="text-muted">
                    Processing time: 3-5 days
                </p>

                <button class="btn btn-primary" id="corProcessing">
                    Request COR
                </button>

            </div>

        </div>
        
    </div>
    
    </div>

   </div>
     
    </div> 
</main>


<!-- cor -->

<div class="modal fade" id="corProcessingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">COR Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

         
            <!-- Body -->
            <div class="modal-body">

                <form class="row g-4" id="corRequestForm" action="<?php echo BASE_URL ?>/store" method="POST">

                       <input 
                            type="hidden" 
                            name="student_id" 
                            id="student_id"
                            value="<?= $studentSchoolInfo['student_id'] ?>"
                        >


                    <!-- Curriculum Name -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Type of Document</label>
                        <input 
                            type="text" 
                            class="form-control shadow-sm ai-clean" 
                            name="document_type" 
                            id="document_type"
                            value="COR"
                        readonly 
                        >
                    </div>

                    <!-- school year -->

                       <div class="col-md-4">
                        <label class="form-label fw-semibold">School Year</label>
                         <select name="school_year_id" class="form-control" id="school_year" required>
                       <option value="">Select School Year</option>

                        </select>

                       </div>

                       <!-- semester -->
                       
                       <div class="col-md-4">
                        <label class="form-label fw-semibold">Semester</label>
                         <select name="semester_id" class="form-control" id="semester" required>
                       <option value="">Select Semester</option>
                        </select>

                       </div>

                       <!-- purpose -->

                        <div class="col-md-8">
                        <label class="form-label">Purpose</label>
                         <select name="purpose_type" class="form-control" id="purpose" required>
                         <option value="">Select Purpose</option>

                            <option value="Employment">
                                Employment
                            </option>

                            <option value="Scholarship">
                                Scholarship Application
                            </option>

                            <option value="Transfer">
                                Transfer to Another School
                            </option>

                            <option value="Board Exam">
                                Board Examination
                            </option>

                            <option value="Visa">
                                Visa Application
                            </option>

                            <option value="Further Studies">
                                Further Studies
                            </option>

                            <option value="Others">
                                Others
                            </option>

                        </select>

          

                       </div>

                      <div class="mb-3 d-none" id="otherPurposeBox">

                        <label class="form-label">
                            Specify Purpose
                        </label>

                        <textarea
                         type="text"
                            class="form-control"
                            name="otherPurpose_type"
                            id="otherPurpose"
                            rows="3"
                            placeholder="Enter purpose">
                        </textarea> 
                           

                    </div>


                    <!-- copies -->

                     <div class="col-md-4">
                        <label class="form-label fw-semibold">Number of Copies</label>
                        <input 
                            type="number" 
                            class="form-control shadow-sm ai-clean" 
                            name="copies" 
                            id="copies"
                            placeholder="copies"
                             min="1"
                             max="100"
                        >
                    </div>

                     <div class="col-md-12">
                        <label class="form-label fw-semibold">Proof of Payment</label>
                        <input 
                            type="file" 
                            class="form-control shadow-sm ai-clean" 
                            name="proof_of_payment" 
                            id="proof_of_payment"
                        required
                        >
                    </div>

                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer">

                <button type="button" class="btn btn-success px-4" id="CorRequestSubmitBtn">
                    Submit Request
                </button>
            </div>

        </div>
    </div>
</div>


<!-- tor -->

<div class="modal fade" id="torProcessingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">TOR Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="toRcloseBtn"></button>
            </div>


         
            <!-- Body -->
            <div class="modal-body">

                <form class="row g-4" id="torRequestForm" action="<?php echo BASE_URL ?>/store" method="POST" enctype="multipart/form-data">
             
                  
                     
                        <input 
                            type="hidden" 
                            name="student_id" 
                            id="student_id"
                            value="<?= $studentSchoolInfo['student_id'] ?>"
                        >
                 
 

                    <!-- Curriculum Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type of Document</label>
                        <input 
                            type="text" 
                            class="form-control shadow-sm ai-clean" 
                            name="document_type" 
                            id="document_type"
                            value="TOR"
                        readonly 
                        >
                    </div>

                     <!-- copies -->

                     <div class="col-md-6">
                        <label class="form-label fw-semibold">Number of Copies</label>
                        <input 
                            type="number" 
                            class="form-control shadow-sm ai-clean" 
                            name="copies" 
                            id="copies"
                            placeholder="copies"
                             min="1"
                             max="100"
                        >
                    </div>

                       <!-- purpose -->

                        <div class="col-md-12">
                         <label class="form-label fw-semibold">Purpose</label>
                         <select name="purpose_type" class="form-control" id="toRpurpose" required>
                         <option value="">Select Purpose</option>

                            <option value="Employment">
                                Employment
                            </option>

                            <option value="Scholarship">
                                Scholarship Application
                            </option>

                            <option value="Transfer">
                                Transfer to Another School
                            </option>

                            <option value="Board Exam">
                                Board Examination
                            </option>

                            <option value="Visa">
                                Visa Application
                            </option>

                            <option value="Further Studies">
                                Further Studies
                            </option>

                            <option value="Others">
                                Others
                            </option>

                        </select>

          

                       </div>

                      <div class="mb-3 d-none" id="otherTorPurposeBox">
                        <label class="form-label">
                            Specify Purpose
                        </label>

                        <textarea
                         type="text"
                            class="form-control"
                            name="otherPurpose_type"
                            id="purpose"
                            rows="3"
                            placeholder="Enter purpose">
                        </textarea> 
                           
                    </div>

                     <div class="col-md-12">
                        <label class="form-label fw-semibold">Proof of Payment</label>
                        <input 
                            type="file" 
                            class="form-control shadow-sm ai-clean" 
                            name="proof_of_payment" 
                            id="proof_of_payment"
                        required
                        >
                    </div>

                   

                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer">

                <button type="button" class="btn btn-success px-4" id="TorRequestSubmitBtn">
                    Submit Request
                </button>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<script src="<?php echo BASE_URL ?>/js/driver.js"></script>

<script> const BASE_URL = "<?php echo BASE_URL ?>"</script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js'></script>
<script src="<?php echo BASE_URL ?>/js/documentRequest.js"></script>



<?php include  __DIR__ .'/../partials/footer.php'; ?>

