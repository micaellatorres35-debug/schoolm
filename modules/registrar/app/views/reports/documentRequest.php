<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>


<main class="main-content">
    <div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Requested Documents</h1>
             <p class="text-muted small">Simple approval queue for submitted requests and reports.</p>
        </div>


    </div>
  
    <div class="card shadow-sm">


    <div class="card-header d-flex justify-content-between align-items-center">

            <h3 class="fs-6 mb-0 text-primary">Requested Documents</h3>

    </div>

        <!-- test -->

      <div class="card-body">
  <div class="row g-2 mb-3">

                <div class="col-md-2">
                    <label class="form-label small">Per Page</label>
                    <select class="form-select form-select-sm" id="limit">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="40">40</option>
                    </select>
                </div>

                 <div class="col-md-3">
                    <label class="form-label small">Order</label>
                    <select class="form-select form-select-sm" id="order">
                        <option value="desc">Descending (Z-A)</option>
                        <option value="asc">Ascending (A-Z)</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label small">Search</label>
                    <input type="text"
                        class="form-control form-control-sm"
                        placeholder="Search activity..."
                        id="search">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-danger btn-sm d-none w-100" id="delete-btn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>

     </div>

          <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Request Number</th>
                            <th>Document Type</th>
                            <th>Purpose</th>
                            <th>Copies</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">
                    </tbody>

                </table>

            </div>

         

       <div class="d-flex align-items-center justify-content-between">
            <div class="fw-bold" id="pageInfo"></div>
            <div id="pagination" class="d-flex gap-2"></div>
        </div>


         </div>
        
       
    </div>


      </div>
    </div>        
    </div>
</main>


<div class="modal fade"
     id="viewDetailsModal"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-labelledby="viewDetailsModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title fw-semibold mb-1"
                        id="viewDetailsModalLabel">
                        Document Request Details
                    </h5>

                    <small class="text-white-50" id="requestNumber">
                        REQ-20260828-6520
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Status -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">
                            Request Status
                        </div>

                        <div class="mt-1">
                            <span id="requestStatus"
                                  class="badge bg-warning-subtle text-warning-emphasis px-3 py-2">
                                Pending
                            </span>
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="text-muted small text-uppercase fw-semibold">
                            Document
                        </div>

                        <div class="fw-semibold mt-1" id="documentType">
                            COR
                        </div>
                    </div>

                </div>


                <!-- Request Information -->
                <div class="mb-4">

                    <h6 class="fw-semibold mb-3">
                        Request Information
                    </h6>

                    <div class="border rounded-3 p-3 bg-light">

                        <div class="row g-3">

                            <div class="col-6">
                                <div class="text-muted small">
                                    Purpose
                                </div>

                                <div class="fw-medium" id="purpose">
                                    Employment
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="text-muted small">
                                    Course
                                </div>

                                <div class="fw-medium" id="courseName">
                                    1
                                </div>
                            </div>  


                            <div class="col-6">
                                <div class="text-muted small">
                                    Student Name
                                </div>

                                <div class="fw-medium" id="studentName">
                                    13
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="text-muted small">
                                    Copies
                                </div>

                                <div class="fw-medium" id="copies">
                                    1
                                </div>
                            </div>


                             <div class="col-6">
                                <div class="text-muted small">
                                    Semester
                                </div>

                                <div class="fw-medium" id="semesterName">
                                    31
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="text-muted small">
                                    Requested At
                                </div>

                                <div class="fw-medium" id="requestedAt">
                                    1
                                </div>
                            </div>                                                                                                                             

                        </div>

                    </div>

                </div>


                <!-- Attachment -->
                <div>

                    <h6 class="fw-semibold mb-3">
                        Submitted Document
                    </h6>

                    <div class="border rounded-3 p-3">

                        <div class="d-flex align-items-center">

                            <!-- File Icon -->
                            <div class="bg-light rounded-3 p-3 me-3">
                                <i class="bi bi-file-earmark-image fs-4 text-primary"></i>
                            </div>

                            <!-- File Information -->
                            <div class="flex-grow-1 overflow-hidden">

                                <div class="fw-medium text-truncate"
                                     id="fileName">
                                    doc_6a908f16349eb9.88423253.png
                                </div>

                                <small class="text-muted">
                                    Submitted document
                                </small>

                            </div>

                            <!-- View -->
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm ms-2"
                                    id="viewDocumentBtn">
                                <i class="bi bi-eye me-1"></i>
                                View
                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer bg-light">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>


<div class="modal fade"
     id="rejectDocumentModal"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-labelledby="rejectDocumentModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <!-- Header -->
            <div class="modal-header bg-danger text-white">

                <div>
                    <h5 class="modal-title fw-semibold mb-1"
                        id="rejectDocumentModalLabel">
                        Reject Document Request
                    </h5>

                    <small class="text-white-50" id="rejectRequestNumber">
                        REQ-20260828-6520
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>


            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Warning -->
                <div class="alert alert-danger d-flex align-items-start mb-4"
                     role="alert">

                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>

                    <div>
                        <div class="fw-semibold">
                            Reject this document request?
                        </div>

                        <small>
                            The student will be notified that the request was rejected.
                            Please provide a reason below.
                        </small>
                    </div>

                </div>


            
               <div> 
                <label for="rejectionReasonSelect" class="form-label fw-semibold"> Reason for Rejection 
                    <span class="text-danger">*</span> 
                </label>
                
              
                  <select class="form-select" id="rejectionReasonSelect" name="rejection_reason" required>
                     <option value="" selected disabled> Select a reason </option> 
                     <option value="Incomplete requirements"> Incomplete requirements </option> 
                     <option value="Invalid or incorrect information"> Invalid or incorrect information </option> 
                     <option value="Document is not available"> Document is not available </option> 
                     <option value="Request does not meet the requirements"> Request does not meet the requirements </option> 
                     <option value="Student record requires verification"> Student record requires verification </option> 
                     <option value="Duplicate request"> Duplicate request </option>
                      <option value="Request is not authorized"> Request is not authorized </option> 
                      <option value="Others"> Others </option> </select> 
                  
                       
                      <div class="mt-3 d-none" id="otherReasonContainer"> 
                        <label for="otherReason" class="form-label fw-semibold"> Please specify <span class="text-danger">*</span> </label>
                         <textarea class="form-control" id="otherReason" name="other_reason" rows="4" maxlength="500" placeholder="Enter the reason for rejection...">

                         </textarea> <div class="d-flex justify-content-between mt-2"> 
                            <small class="text-muted">
                             Please provide a clear and specific reason.
                            </small> <small class="text-muted"> 
                            <span id="reasonCharacterCount">0</span>/500 
                        </small> 
                    </div> 
                </div> 
            </div>

            </div>


            <!-- Footer -->
            <div class="modal-footer bg-light">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-danger"
                        id="confirmRejectBtn">

                    <i class="bi bi-x-circle me-1"></i>
                    Reject Request

                </button>

            </div>

        </div>
    </div>
</div>






<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>   const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?php echo BASE_URL ?>/js/documentRequest.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>
