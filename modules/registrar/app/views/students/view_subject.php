<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

  

<div class="container-fluid py-4">
    
    

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" >
        <div id="dashboard-title">
            <h3 class="fw-bold text-dark mb-1" ><?= $course['name'] ?> (<?= $course['code'] ?>)</h3>
            <p class="text-muted mb-0">
                Curriculum: <span class="fw-semibold text-secondary"><?= $curriculum['curriculum_name'] ?></span> &middot; Effective Year: <?= $curriculum['effective_year'] ?>
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <button class="btn btn-primary btn-sm px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="addCurrBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Subject
            </button>
        </div>
    </div>

    <!-- CURRICULUM WORKSPACE CARD -->
    <div class="card border-0 shadow-sm rounded-3">
        
        <!-- UTILITY ACTIONS BAR (Matches your Theme's Filter/Export Row) -->
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="fs-5 fw-bold text-primary">Curriculum Structure</span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 px-3" id="pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/><path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.047-.183-.154-.909.097-1.578.14-.374.396-.893.754-1.456a.58.58 0 0 0-.013-.59c-.437-.718-.832-1.645-1.017-2.6-.077-.4-.112-.767-.115-1.03-.002-.196.012-.398.073-.531l.212-.33a.787.787 0 0 1 .743-.324c.152.015.362.08.536.244.174.164.28.387.326.602.127.58.01 1.139-.17 1.715-.18.574-.5 1.412-.787 2.006l.183.22c.288.347.628.67.933.916a1.2 1.2 0 0 1 .158-.162c.46-.382.903-.58 1.24-.58.12 0 .218.016.292.05.152.072.305.214.358.412.054.21.03.541-.18.883-.195.316-.549.533-.883.615a3.13 3.13 0 0 1-.771.044.79.79 0 0 1-.616-.393 8.12 8.12 0 0 0-.755-.317 18.45 18.45 0 0 1-1.39 1.583c-.3.298-.6.541-.856.692-.256.15-.496.227-.798.227z"/></svg>
                    PDF
                </button>
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
            </div>
        </div>

        <div class="card-body p-4">

         <div id="curriculum-structure"></div>

        </div>

</div>

</div>

</div>
</main>


<div class="modal fade" id="curriculumSubjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Add Curriculum Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

         
            <!-- Body -->
            <div class="modal-body">

                <form class="row g-4" id="curriculumSubjectForm" action="<?php echo BASE_URL ?>/curriculum-subject/store" method="POST">

                  <div class="col-12 d-flex justify-content-between align-items-center border rounded p-2 px-3 bg-light">


                        <div>
                            <span class="fw-semibold">AI Auto-Correct</span>
                            <i class="bi bi-info-circle text-muted"
                               data-bs-toggle="tooltip"
                               data-bs-placement="right"
                               title="When enabled, AI automatically corrects grammar, spelling, and capitalization.">
                            </i>
                            <div class="small text-muted">
                                Improve text automatically before saving.
                            </div>
                        </div>

                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="ai_auto_correct" value="0">

                            <input class="form-check-input"
                                   type="checkbox"
                                   id="aiAutoCorrect"
                                   name="ai_auto_correct"
                                   value="1">
                        </div>
                  </div>

                    <input type="hidden" name="curriculum_id" value="<?= $id ?>" >

                  <div class="mb-2">

                     <div class="row">


                       <div class="mb-2">

                    <div class="row">

                  <!-- Year Level -->
                   <div class="col-md-6">
                        <label class="form-label fw-semibold">Year Level</label>
                     <select name="year_level" class="form-select">
                        <option value="">Select Year Level</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                    </div>

                    <!-- semester -->
                     <div class="col-md-6">
                        <label class="form-label fw-semibold">Semester</label>
                     <select name="semester" class="form-select">
                        <option value="">Select Semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                    </select>
                    </div>

                    </div>

                  </div>
                    
                      <!-- Subject -->
                   <div class="col-md-12">
                  <label class="form-label fw-semibold">Subject</label>
                  <select name="subject_id" id="subjectSelect" class="form-select shadow-sm">
                  <option value="">Select Subject</option>

                   <?php foreach($subjects as $subject) { ?>
                     <option value="<?= $subject['id'] ?>">
                   <?= $subject['code'] ?> - <?= $subject['name'] ?>
                   </option>
                   <?php } ?>

                   </select>
                  </div>

                     </div>

                  </div>

               

                
                    

                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-success px-4" id="curriculumSubjectSubmitBtn">
                    Save Curriculum Subject
                </button>
            </div>

        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script>
     const CurriculumID = <?= (int)$id ?>;
</script>
<script src="<?= BASE_URL ?>/js/curriculum_subject.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>