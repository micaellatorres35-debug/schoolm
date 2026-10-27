 <main class="main-content">
     <div class="container-fluid">
         <div class="card shadow-sm border-0">
             <div class="card-body">
                 <div class="mb-4">
                     <h3 class="mb-1">Report Submissions & Management</h3>
                     <p class="text-muted mb-0">
                         View and manage submitted reports from teachers and staff.
                     </p>
                 </div>

                 <div class="card shadow-sm border-0 border-top border-4 border-secondary">
                     <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                             <div class="d-flex align-items-center gap-2 flex-wrap">
                                 <form method="GET" class="d-inline-block">
                                     <select name="status" id="concern-filter" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                                         <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                                         <option value="open" <?= ($status ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                                         <option value="resolved" <?= ($status ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                     </select>
                                 </form>
                                 <input class="form-control form-control-sm"
                                     id="concern-search"
                                     type="search"
                                     placeholder="Search..."
                                     style="width: 200px;">
                             </div>
                             <div class="d-flex align-items-center gap-2">
                                 <button
                                     type="button"
                                     class="btn btn-primary btn-sm"
                                     data-bs-toggle="modal"
                                     data-bs-target="#createconcernsITModal">
                                     <i class="fas fa-plus me-1"></i>
                                     Create
                                 </button>
                             </div>
                         </div>
                         <div class="table-responsive">
                             <?php require __DIR__ . '/table.php'; ?>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </main>

 <?php require __DIR__ . '/create-concernsIT-modal.php'; ?>