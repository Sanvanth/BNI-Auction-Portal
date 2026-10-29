<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
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

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
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

        .admin-credentials {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            display: none;
        }

        .credential-item {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        .error-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }
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
                        <a class="nav-link active" href="<?= base_url('/superadmin/events') ?>">
                            <i class="fas fa-calendar-alt me-1"></i>Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/superadmin/admins') ?>">
                            <i class="fas fa-users-cog me-1"></i>Admins
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/superadmin/superadmins') ?>">
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
                    <i class="fas fa-plus-circle me-2"></i>Create New Event
                </h2>
                <a href="<?= base_url('/superadmin/events') ?>" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Events
                </a>
            </div>

            <form id="createEventForm" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Event Information Section -->
                <h5 class="section-title">
                    <i class="fas fa-calendar-alt me-2"></i>Event Information
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="event_name" name="event_name"
                                placeholder="Event Name" required>
                            <label for="event_name">
                                <i class="fas fa-calendar-alt me-2"></i>Event Name
                            </label>
                            <div class="error-feedback" id="event_name_error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="file" class="form-control" id="event_logo" name="event_logo" accept="image/*">
                            <label for="event_logo">
                                <i class="fas fa-image me-2"></i>Event Logo
                            </label>
                            <div class="error-feedback" id="event_logo_error"></div>
                        </div>
                    </div>
                </div>

                <div class="form-floating">
                    <textarea class="form-control" id="event_description" name="event_description"
                        placeholder="Event Description" style="height: 120px;" required></textarea>
                    <label for="event_description">
                        <i class="fas fa-align-left me-2"></i>Event Description
                    </label>
                    <div class="error-feedback" id="event_description_error"></div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                            <label for="start_date">
                                <i class="fas fa-calendar-plus me-2"></i>Start Date
                            </label>
                            <div class="error-feedback" id="start_date_error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                            <label for="end_date">
                                <i class="fas fa-calendar-minus me-2"></i>End Date
                            </label>
                            <div class="error-feedback" id="end_date_error"></div>
                        </div>
                    </div>
                </div>

                <!-- Admin Account Section -->
                <div class="section-divider"></div>
                <h5 class="section-title">
                    <i class="fas fa-user-shield me-2"></i>Admin Account Details
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="admin_username" name="admin_username"
                                placeholder="Admin Username" required>
                            <label for="admin_username">
                                <i class="fas fa-user me-2"></i>Admin Username
                            </label>
                            <div class="error-feedback" id="admin_username_error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="admin_password" name="admin_password"
                                placeholder="Admin Password" required>
                            <label for="admin_password">
                                <i class="fas fa-lock me-2"></i>Admin Password
                            </label>
                            <div class="error-feedback" id="admin_password_error"></div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="fas fa-plus-circle me-2"></i>Create Event & Admin Account
                        <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></span>
                    </button>
                </div>
            </form>

            <!-- Admin Credentials Display -->
            <div class="admin-credentials" id="adminCredentials">
                <h5><i class="fas fa-user-shield me-2"></i>Event & Admin Account Created Successfully!</h5>
                <p>An admin account has been automatically created for this event:</p>
                <div class="credential-item">
                    <strong>Username:</strong> <span id="adminUsername"></span>
                </div>
                <div class="credential-item">
                    <strong>Password:</strong> <span id="adminPassword"></span>
                </div>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Please save these credentials. The password will not be shown again.
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            $('#start_date').attr('min', today);

            // Update end date minimum when start date changes
            $('#start_date').change(function () {
                const startDate = $(this).val();
                $('#end_date').attr('min', startDate);

                // Clear end date if it's before start date
                const endDate = $('#end_date').val();
                if (endDate && endDate <= startDate) {
                    $('#end_date').val('');
                }
            });

            $('#createEventForm').submit(function (e) {
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
                    url: '<?= base_url('/superadmin/store-event') ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            // Show admin credentials
                            $('#adminUsername').text(response.admin_credentials.username);
                            $('#adminPassword').text(response.admin_credentials.password);
                            $('#adminCredentials').show();

                            Swal.fire({
                                title: 'Success!',
                                text: 'Event created successfully with admin account.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reset form
                                $('#createEventForm')[0].reset();
                                $('#adminCredentials').hide();
                            });
                        } else {
                            // Display validation errors
                            if (response.errors) {
                                $.each(response.errors, function (field, message) {
                                    $(`#${field}`).addClass('is-invalid');
                                    $(`#${field}_error`).text(message);
                                });
                            }

                            Swal.fire('Error!', response.message || 'Failed to create event.', 'error');
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
        });
    </script>
</body>

</html>