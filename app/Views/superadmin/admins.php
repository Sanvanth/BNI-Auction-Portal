<!DOCTYPE html>
<html lang="en">

<?php require('components/head.php'); ?>

<style>
    #adminsTable {
        width: 100% !important;
    }

    .btn-toggle {
        background: #d8ab3b;
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        color: black !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        margin-right: 10px;
    }

    .btn-create:hover {
        background: Whitesmoke;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        color: black;
    }

    .btn-toggle:hover {
        background: black;
        color: white;
    }

    .btn-toggle.active {
        background: #d8ab3b;
    }

    .btn-toggle.active:hover {
        background: whitesmoke;
        color: black;
    }

    .btn-action {
        border-radius: 50px;
        padding: 8px 15px;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        margin: 0.125rem;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #d8ab3b;
        color: black;
    }

    .btn-reset {
        background: #d8ab3b;
        color: black;
    }

    .btn-delete {
        background: #d8ab3b;
        color: black;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        color: black;
    }

    .admin-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f6d776ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .form-floating {
        margin-bottom: 1.5rem;
    }
</style>

<body>

    <?php require('components/navbar.php'); ?>

    <div class="container main-container admins-container">
        <!-- Statistics Cards -->
        <div class="row stats-cards">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= isset($stats['total_admins']) ? $stats['total_admins'] : '0' ?></div>
                    <div class="stat-label">Total Admins</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= isset($stats['active_admins']) ? $stats['active_admins'] : '0' ?></div>
                    <div class="stat-label">Active Admins</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= isset($stats['inactive_admins']) ? $stats['inactive_admins'] : '0' ?>
                    </div>
                    <div class="stat-label">Inactive Admins</div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-users-cog me-2"></i>Admin Management
                </h2>
                <div>
                    <button class="btn btn-toggle active" id="toggleViewBtn" onclick="toggleView()">
                        <i class="fas fa-user-slash me-2"></i>Inactive Admins
                    </button>
                    <a href="<?= base_url('/superadmin/add-admin') ?>" class="btn btn-create">
                        <i class="fas fa-user-plus me-2"></i>Add Admin
                    </a>
                </div>
            </div>
            <div class="table-container">
                <div class="table-responsive">
                    <table id="adminsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Username</th>
                                <th>Event</th>
                                <th>Event Status</th>
                                <th>Account Status</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Admin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editAdminForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <?= csrf_field() ?>

                        <div class="form-floating">
                            <input type="text" class="form-control" id="edit_username" name="username"
                                placeholder="Username" required>
                            <label for="edit_username">
                                <i class="fas fa-user me-2"></i>Username
                            </label>
                            <div class="error-feedback" id="edit_username_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Admin
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="editSubmitSpinner"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let adminsTable;
            let currentView = 'active'; // Start with active admins (showing by default)
            let showingInactive = false; // Track if we're showing inactive or active

            // Initialize DataTable
            function initializeDataTable(showInactive = false) {
                if (adminsTable) {
                    adminsTable.destroy();
                }

                adminsTable = $('#adminsTable').DataTable({
                    ajax: {
                        url: '<?= base_url('/superadmin/api/admins-list') ?>',
                        data: function(d) {
                            d.show_inactive = showInactive;
                        },
                        dataSrc: function(json) {
                            return json;
                        }
                    },
                    columns: [{
                            data: 'username',
                            render: function(data, type, row) {
                                return `<div class="admin-avatar">${data.charAt(0).toUpperCase()}</div>`;
                            }
                        },
                        {
                            data: 'username',
                            render: function(data, type, row) {
                                return `<strong>${data}</strong><br><small class="text-muted">ID: ${row.user_id}</small>`;
                            }
                        },
                        {
                            data: 'event_name',
                            render: function(data, type, row) {
                                if (data && data !== 'No Event Assigned') {
                                    let badgeClass = 'event-badge';
                                    if (row.event_end_date && new Date(row.event_end_date) < new Date()) {
                                        badgeClass += ' event-ended';
                                    }
                                    return `<span class="${badgeClass}">${data}</span>`;
                                }
                                return '<span class="text-muted">No Event Assigned</span>';
                            }
                        },
                        {
                            data: 'event_is_active',
                            render: function(data, type, row) {
                                if (row.event_name && row.event_name !== 'No Event Assigned') {
                                    if (row.event_end_date && new Date(row.event_end_date) < new Date()) {
                                        return '<span class="status-badge status-inactive">Ended</span>';
                                    }
                                    return data == 1 ?
                                        '<span class="status-badge status-active">Active</span>' :
                                        '<span class="status-badge status-inactive">Inactive</span>';
                                }
                                return '<span class="text-muted">-</span>';
                            }
                        },
                        {
                            data: 'is_active',
                            render: function(data, type, row) {
                                return data == 1 ?
                                    '<span class="status-badge status-active">Active</span>' :
                                    '<span class="status-badge status-inactive">Inactive</span>';
                            }
                        },
                        {
                            data: 'created_at',
                            render: function(data, type, row) {
                                const date = new Date(data);
                                return `${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}<br>
                                        <small class="text-muted">${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>`;
                            }
                        },
                        {
                            data: 'updated_at',
                            render: function(data, type, row) {
                                const date = new Date(data);
                                return `${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}<br>
                                        <small class="text-muted">${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>`;
                            }
                        },
                        {
                            data: 'user_id',
                            render: function(data, type, row) {
                                return `
                                    <button class="btn btn-action btn-edit" onclick="editAdmin(${data})" title="Edit Admin">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-action btn-reset" onclick="resetPassword(${data})" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn btn-action btn-delete" onclick="deleteAdmin(${data})" title="Delete Admin">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                `;
                            }
                        }
                    ],
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            title: function() {
                                return (showInactive ? 'Inactive' : 'Active') + ' Admins Report - ' + new Date().toLocaleDateString();
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                            title: function() {
                                return (showInactive ? 'Inactive' : 'Active') + ' Admins Report';
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-1"></i> Print',
                            title: function() {
                                return (showInactive ? 'Inactive' : 'Active') + ' Admins Report';
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        }
                    ],
                    responsive: true,
                    pageLength: 10,
                    order: [
                        [5, 'desc']
                    ],
                    language: {
                        emptyTable: showInactive ? "No inactive admin accounts found" : "No active admin accounts found",
                        zeroRecords: showInactive ? "No matching inactive admin accounts found" : "No matching active admin accounts found"
                    }
                });
            }

            // Initialize with active admins (showing by default, button shows "Inactive Admins")
            initializeDataTable(false);
            currentView = 'active';
            showingInactive = false;

            // Toggle View Function
            window.toggleView = function() {
                const toggleBtn = $('#toggleViewBtn');

                if (!showingInactive) {
                    // Currently showing active, switch to inactive
                    showingInactive = true;
                    currentView = 'inactive';
                    toggleBtn.html('<i class="fas fa-users me-2"></i>Active Admins');
                    initializeDataTable(true); // Show inactive admins
                } else {
                    // Currently showing inactive, switch to active
                    showingInactive = false;
                    currentView = 'active';
                    toggleBtn.html('<i class="fas fa-user-slash me-2"></i>Inactive Admins');
                    initializeDataTable(false); // Show active admins
                }
            };

            // Edit Admin Function
            window.editAdmin = function(userId) {
                $.ajax({
                    url: `<?= base_url('/superadmin/get-admin/') ?>${userId}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#edit_user_id').val(response.admin.user_id);
                            $('#edit_username').val(response.admin.username);
                            $('#editAdminModal').modal('show');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to load admin data.', 'error');
                    }
                });
            };

            // Edit Admin Form Submission
            $('#editAdminForm').submit(function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.error-feedback').text('');
                $('.form-control, .form-select').removeClass('is-invalid');

                const submitBtn = $(this).find('button[type="submit"]');
                const submitSpinner = $('#editSubmitSpinner');

                // Show loading state
                submitBtn.prop('disabled', true);
                submitSpinner.removeClass('d-none');

                const formData = new FormData(this);
                const userId = $('#edit_user_id').val();

                $.ajax({
                    url: `<?= base_url('/superadmin/update-admin/') ?>${userId}`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#editAdminModal').modal('hide');
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                adminsTable.ajax.reload();
                            });
                        } else {
                            // Display validation errors
                            if (response.errors) {
                                $.each(response.errors, function(field, message) {
                                    $(`#edit_${field}`).addClass('is-invalid');
                                    $(`#edit_${field}_error`).text(message);
                                });
                            }
                            Swal.fire('Error!', response.message || 'Failed to update admin.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'An error occurred while updating the admin.', 'error');
                    },
                    complete: function() {
                        // Hide loading state
                        submitBtn.prop('disabled', false);
                        submitSpinner.addClass('d-none');
                    }
                });
            });

            // Reset Password Function
            window.resetPassword = function(userId) {
                Swal.fire({
                    title: 'Reset Password',
                    text: 'This will generate a new random password for the admin.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?= base_url('/superadmin/reset-admin-password/') ?>${userId}`,
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Password Reset!',
                                        html: `<p>${response.message}</p><p><strong>New Password:</strong> <code>${response.new_password}</code></p><p class="text-warning"><small>Please share this password securely with the admin.</small></p>`,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'An error occurred while resetting the password.', 'error');
                            }
                        });
                    }
                });
            };

            // Delete Admin Function (Soft Delete)
            window.deleteAdmin = function(userId) {
                Swal.fire({
                    title: 'Delete Admin',
                    text: 'This will deactivate the admin account. This action can be reversed if needed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?= base_url('/superadmin/delete-admin/') ?>${userId}`,
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                                        adminsTable.ajax.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'An error occurred while deleting the admin.', 'error');
                            }
                        });
                    }
                });
            };
        });
    </script>
</body>

</html>