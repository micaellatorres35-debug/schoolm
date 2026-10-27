<?php include __DIR__ . '/partials/header.php'; ?>
<?php include __DIR__ . '/partials/sidebar.php'; ?>


<div class="main-content bg-light pb-5">
    <div class="myapp-module-header">
    <h2>My Application</h2>
</div>

<div class="module-content">
    <div class="myapp-card">
        <table class="myapp-table" id="myappTable">
            <thead>
                <tr>
                    <th>Application Number</th>
                    <th>Scholarship Type</th>
                    <th>Date Applied</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="myapp-no-data">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/my-applications.css">
<script src="<?= BASE_URL ?>/js/my-application.js"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<?php include __DIR__ . '/partials/footer.php'; ?>