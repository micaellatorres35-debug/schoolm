<?php include __DIR__ . '/partials/header.php'; ?>
<?php include __DIR__ . '/partials/sidebar.php'; ?>



<div class="main-content bg-light pb-5">
    <div class="schoff-module-header">
    <h2>Scholarship Offered</h2>
</div>

<div class="module-content">
    <div class="schoff-grid" id="schoffGrid">
        <div class="schoff-empty">Loading...</div>
    </div>
</div>

<div class="schoff-modal-overlay" id="schoffModalOverlay">
    <div class="schoff-modal">
        <div class="schoff-modal-header">
            <h3>Apply for Scholarship</h3>
            <button type="button" class="schoff-modal-close" id="schoffModalClose">&times;</button>
        </div>

        <form id="schoffApplyForm" enctype="multipart/form-data" data-skip>
            <input type="hidden" id="schoffTypeId" name="scholarship_type_id">

            <div class="schoff-modal-body">
                <div class="schoff-field-row">
                    <div class="schoff-field">
                        <label>Student Number</label>
                        <input type="text" value="<?= htmlspecialchars($studentNumber) ?>" readonly>
                    </div>
                    <div class="schoff-field">
                        <label>Student Name</label>
                        <input type="text" value="<?= htmlspecialchars($studentName) ?>" readonly>
                    </div>
                </div>

                <div class="schoff-field-row">
                    <div class="schoff-field">
                        <label>Program</label>
                        <input type="text" value="<?= htmlspecialchars($program) ?>" readonly>
                    </div>
                    <div class="schoff-field">
                        <label>Year Level</label>
                        <input type="text" value="<?= htmlspecialchars($yearLevel) ?>" readonly>
                    </div>
                </div>

                <div class="schoff-field">
                    <label>Scholarship Type</label>
                    <input type="text" id="schoffModalTypeName" readonly>
                </div>

                <div class="schoff-field">
                    <label>Attach Requirement (TOR / COG)</label>
                    <input type="file" id="schoffAttachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" required>
                    <span class="schoff-file-hint">PDF, JPG, or PNG — max 5MB</span>
                </div>

                <p class="schoff-modal-error" id="schoffModalError"></p>
            </div>

            <div class="schoff-modal-footer">
                <button type="button" class="schoff-modal-cancel" id="schoffModalCancel">Cancel</button>
                <button type="submit" class="schoff-modal-submit" id="schoffModalSubmit">Submit Application</button>
            </div>
        </form>
    </div>
</div>
</div>

<script> const BASE_URL = "<?php echo BASE_URL ?>"; </script>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/scholarship-offered.css">
<script src="<?= BASE_URL ?>/js/scholarship-offered.js"></script>
<?php include __DIR__ . '/partials/footer.php'; ?>