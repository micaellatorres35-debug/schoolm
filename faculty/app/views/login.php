<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestlink College of the Philippines - Sign In</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        :root{
            --primary-blue:#4d44d6;
            --primary-hover:#3b33b3;
            --dark-bg-start:#163683;
            --dark-bg-end:#0d2358;
        }

        body{
            background:#f8f9fa;
        }

        .login-panel{
            min-height:100vh;
        }

        .login-wrapper{
            width:100%;
            max-width:400px;
        }

        .logo-img{
            width:140px;
        }

        .form-control{
            height:50px;
        }

        .form-control:focus{
            border-color:var(--primary-blue);
            box-shadow:0 0 0 .2rem rgba(77,68,214,.2);
        }

        .btn-primary-custom{
            background:var(--primary-blue);
            border:none;
            border-radius:50px;
            height:50px;
            font-weight:600;
        }

        .btn-primary-custom:hover{
            background:var(--primary-hover);
        }

        .brand-panel{
            background:linear-gradient(135deg,var(--dark-bg-start),var(--dark-bg-end));
            color:#fff;
            min-height:100vh;
            position:relative;
            overflow:hidden;
        }

        .brand-panel::before{
            content:'';
            position:absolute;
            width:700px;
            height:700px;
            border-radius:50%;
            background:rgba(255,255,255,.03);
            right:-250px;
            bottom:-250px;
        }

        .brand-content{
            position:relative;
            z-index:2;
            padding:0 5rem;
        }

        .brand-heading{
            font-size:4rem;
            font-weight:700;
            line-height:1.1;
        }

        .brand-link{
            color:#fff;
            text-decoration:none;
            font-weight:600;
        }

        .brand-link:hover{
            text-decoration:underline;
        }

        .dot-grid{
            position:absolute;
            top:40px;
            right:40px;
            display:grid;
            grid-template-columns:repeat(8,6px);
            gap:12px;
            opacity:.2;
        }

        .dot-grid span{
            width:6px;
            height:6px;
            background:#fff;
            border-radius:50%;
        }

        .password-toggle{
            cursor:pointer;
        }

        @media(max-width:991px){
            .brand-panel{
                display:none !important;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Login -->
        <div class="col-lg-5 d-flex justify-content-center align-items-center login-panel bg-white">

            <div class="login-wrapper">

                <div class="text-center mb-4">
                    <img src="<?= BASE_URL ?>/assets/images/bcp-logo.png"
                         class="logo-img img-fluid"
                         alt="Logo">
                </div>

                <h1 class="fw-bold mb-4">Sign in</h1>

                <div class="p-2 mb-2 border border-danger rounded-1 text-danger d-none" id="alert-box">
                    
                     <span>test</span>

                </div>

                <form id="loginForm">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Username *
                        </label>

                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="form-control"
                           placeholder="Username">
                    </div>

                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            Password *
                        </label>

                      <div class="input-group">

    <input
        type="password"
        id="password"
        name="password"
        class="form-control"
        placeholder="Enter password">

    <button
        class="btn btn-outline-secondary password-toggle"
        type="button">

        <i class="bi bi-eye" id="passwordIcon"></i>

    </button>

</div>

                    </div>

                    <button  id="submit-btn" class="btn btn-primary-custom w-100">
                        Sign in
                    </button>

                </form>

            </div>

        </div>

        <!-- Right Banner -->
        <div class="col-lg-7 d-none d-lg-flex align-items-center brand-panel">

            <div class="dot-grid">
                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>

                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>

                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="brand-content">

                <h2 class="brand-heading mb-4">
                    Faculty <br>
                   System <br>
                   Portal
                </h2>

                <a href="#" class="brand-link">
                    Student admission click here
                </a>

            </div>

        </div>

    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script> const BASE_URL = <?= json_encode(BASE_URL) ?> </script> 
<script src="<?= BASE_URL ?>/js/login.js"></script>

</body>
</html>