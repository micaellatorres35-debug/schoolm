<?php include  __DIR__ .'/../partials/header.php'; ?>
<?php include __DIR__ .'/../partials/sidebar.php'; ?>


<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Document Status</h1>
        </div>
        
    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Requested Documents</h3>
        </div>


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
                        placeholder="Search subjects..."
                        id="search">
                </div>

        </div>


        <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Request No.</th>
                            <th>Document Type</th>
                            <th>Requested At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">
                    </tbody>

                </table>

            </div>


            
            <div class="d-flex align-items-center justify-content-between mt-3">

                <div class="fw-semibold small" id="pageInfo"></div>

                <div id="pagination" class="d-flex gap-1"></div>

            </div>

        </div>

    </div>

</div>
</main>


<!-- Document Request History Modal -->

<div class="modal fade"
     id="documentRequestHistoryModal"
     tabindex="-1"
     aria-labelledby="documentRequestHistoryModalLabel"
     aria-hidden="true">

<div class="modal-dialog modal-dialog-centered modal-lg">

    <div class="modal-content border-0 shadow rounded-4">

      
        <div class="modal-header px-4 py-3">

            <div>
                <h5 class="modal-title fw-semibold"
                    id="documentRequestHistoryModalLabel">
                    Document Request History
                </h5>

                <small class="text-muted">
                    Track the progress of this document request
                </small>
            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
            </button>

        </div>


        <div class="modal-body px-4 py-4">


            <div class="bg-light rounded-3 p-3 mb-4">

                <div class="row g-3">

                 
                    <div class="col-md-6">

                        <small class="text-muted d-block">
                            Request Number
                        </small>

                        <span class="fw-semibold"
                              id="history-request-number">
                            -
                        </span>

                    </div>


                
                    <div class="col-md-6">

                        <small class="text-muted d-block">
                            Document
                        </small>

                        <span class="fw-semibold"
                              id="history-document">
                            -
                        </span>

                    </div>


                    <div class="col-md-6">

                        <small class="text-muted d-block">
                            Purpose
                        </small>

                        <span id="history-purpose">
                            -
                        </span>

                    </div>


                 
                    <div class="col-md-6">

                        <small class="text-muted d-block">
                            Current Status
                        </small>

                        <span id="history-status"
                              class="badge text-bg-secondary">
                            -
                        </span>

                    </div>

                </div>

            </div>




            <div class="mb-4">

                <h6 class="fw-semibold mb-1">
                    Activity History
                </h6>

                <small class="text-muted">
                    A record of updates made to this document request
                </small>

            </div>


            <div id="history-timeline">

            

            </div>


        </div>

        <div class="modal-footer px-4">

            <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                Close

            </button>

        </div>

    </div>

</div>


</div>



<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/documentStatus.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>