<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Superadmin</title>
    <link rel="icon" href="<?= base_url('public/assets/AppTeqLogo.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #e5e4a8ff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: black;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .main-container {
            padding: 2rem 0;
        }

        .form-card {
            background: black;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-title {
            background: whitesmoke;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
        }

        .section-divider {
            background: #f6d776ff;
            height: 2px;
            border-radius: 1px;
            margin: 2rem 0 1.5rem 0;
        }

        .section-title {
            color: #f6d776ff;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #d8ab3b;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: #d8ab3b;
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            color: black;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: black;
            background: whitesmoke;
        }

        .btn-back {
            background: transparent;
            border: 2px solid #d8ab3b;
            border-radius: 50px;
            padding: 10px 30px;
            color: #d8ab3b;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: white;
            color: black;
        }

        .navbar-brand {
            font-weight: 700;
            background: #f6d776ff;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .error-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e9ecef;
            margin-top: 0.25rem;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background: #dc3545;
        }

        .strength-medium {
            background: #ffc107;
        }

        .strength-strong {
            background: #198754;
        }

        /* .info-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .info-card h6 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .info-card p {
            margin-bottom: 0;
            opacity: 0.9;
        } */
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('/superadmin/dashboard') ?>">
                <i class="fas fa-crown me-2"></i>Superadmin Panel
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/superadmin/dashboard') ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/superadmin/events') ?>">
                            <i class="fas fa-calendar-alt me-1"></i>Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/superadmin/admins') ?>">
                            <i class="fas fa-users-cog me-1"></i>Admins
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('/superadmin/superadmins') ?>">
                            <i class="fas fa-user-shield me-1"></i>Superadmins
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= session()->get('username') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('/logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-user-shield me-2"></i>Create New Superadmin
                </h2>
                <a href="<?= base_url('/superadmin/superadmins') ?>" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Superadmins
                </a>
            </div>
<!-- 
            Info Card
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Superadmin Privileges</h6>
                <p>Superadmins have full system access including managing events, admins, and other superadmins. They
                    are not assigned to specific events.</p>
            </div> -->

            <form id="createSuperadminForm">
                <?= csrf_field() ?>

                <!-- Superadmin Information Section -->
                <h5 class="section-title">
                    <i class="fas fa-user-shield me-2"></i>Superadmin Account Information
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username"
                                required>
                            <label for="username">
                                <i class="fas fa-user me-2"></i>Username
                            </label>
                            <div class="error-feedback" id="username_error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="error-feedback" id="password_error"></div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <small class="text-muted" id="strengthText">Enter password to check strength</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="fas fa-user-shield me-2"></i>Create Superadmin Account
                        <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Password strength checker
            $('#password').on('input', function () {
                const password = $(this).val();
                const strength = calculatePasswordStrength(password);
                updatePasswordStrength(strength);
            });

            // Form submission
            $('#createSuperadminForm').submit(function (e) {
                e.preventDefault();

                // Clear previous errors
                $('.error-feedback').text('');
                $('.form-control').removeClass('is-invalid');

                const submitBtn = $('#submitBtn');
                const submitSpinner = $('#submitSpinner');

                // Show loading state
                submitBtn.prop('disabled', true);
                submitSpinner.removeClass('d-none');

                const formData = new FormData(this);

                $.ajax({
                    url: '<?= base_url('/superadmin/store-superadmin') ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Superadmin account created successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '<?= base_url('/superadmin/superadmins') ?>';
                            });
                        } else {
                            // Display validation errors
                            if (response.errors) {
                                $.each(response.errors, function (field, message) {
                                    $(`#${field}`).addClass('is-invalid');
                                    $(`#${field}_error`).text(message);
                                });
                            }

                            Swal.fire('Error!', response.message || 'Failed to create superadmin account.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An unexpected error occurred. Please try again.', 'error');
                    },
                    complete: function () {
                        // Hide loading state
                        submitBtn.prop('disabled', false);
                        submitSpinner.addClass('d-none');
                    }
                });
            });

            function calculatePasswordStrength(password) {
                let strength = 0;

                if (password.length >= 8) strength += 1;
                if (password.match(/[a-z]/)) strength += 1;
                if (password.match(/[A-Z]/)) strength += 1;
                if (password.match(/[0-9]/)) strength += 1;
                if (password.match(/[^a-zA-Z0-9]/)) strength += 1;

                return strength;
            }

            function updatePasswordStrength(strength) {
                const strengthFill = $('#strengthFill');
                const strengthText = $('#strengthText');

                strengthFill.removeClass('strength-weak strength-medium strength-strong');

                if (strength <= 2) {
                    strengthFill.addClass('strength-weak').css('width', '33%');
                    strengthText.text('Weak password').css('color', '#dc3545');
                } else if (strength <= 4) {
                    strengthFill.addClass('strength-medium').css('width', '66%');
                    strengthText.text('Medium password').css('color', '#ffc107');
                } else {
                    strengthFill.addClass('strength-strong').css('width', '100%');
                    strengthText.text('Strong password').css('color', '#198754');
                }
            }
        });
    </script>
</body>

</html>