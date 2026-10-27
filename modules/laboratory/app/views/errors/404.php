<?php include  __DIR__ .'/../includes/header.php'; ?>
<?php include  __DIR__ .'/../includes/sidebar.php'; ?>


<style>
.error-404{
    font-size:8rem;
    font-weight:700;
    color:#6c757d;
    animation: float 3s ease-in-out infinite;
    text-align: center;
    padding: 1rem;
    color: var(--color2);
    margin-left: var(--sidebar-width);
}

section{
    margin-left: var(--sidebar-width);
}

@keyframes float{
    0%{ transform: translateY(0px); }
    50%{ transform: translateY(-15px); }
    100%{ transform: translateY(0px); }
}
</style>

<div class="d-flex flex-column min-vh-100 mt-5">

    <div class="container-fluid flex-grow-1">
        <div class="d-flex flex-column justify-content-center align-items-center text-center  h-100">

            <div class="error-404">404</div>
            <section class="mb-4 lead">
                <p class="fs-4 text-secondary mb-3">Page Not Found</p>

                <p class="text-muted mb-3">
                    It looks like you found a glitch in the matrix...
                </p>

                <a href="<?= BASE_URL ?>" class="text-decoration-none">
                    ← Back to Dashboard
                </a>
            </section>

        </div>
    </div>

</div>

<?php include  __DIR__ .'/../includes/footer.php'; ?>