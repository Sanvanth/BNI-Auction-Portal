    // SUPERADMIN tab's js code starts here.
    let currentPasswordResetUserId = null;
    const showingInactive = `<?= isset($showing_inactive) && $showing_inactive ? 'true' : 'false' ?>`;

    $(document).ready(function() {
        let superadminsTable = $('#superadminsTable').DataTable({
        dom: 'Bfrtip',
    buttons: [{
        extend: 'excel',
    text: '<i class="fas fa-file-excel me-1"></i> Excel',
    title: (showingInactive ? 'Inactive' : 'Active') + ' Superadmins Report - ' + new Date().toLocaleDateString(),
    exportOptions: {
        columns: [1, 2, 3, 4, 5] // Exclude avatar and actions columns
                        }
                    },
    {
        extend: 'pdf',
    text: '<i class="fas fa-file-pdf me-1"></i> PDF',
    title: (showingInactive ? 'Inactive' : 'Active') + ' Superadmins Report',
    orientation: 'landscape',
    pageSize: 'A4',
    exportOptions: {
        columns: [1, 2, 3, 4, 5] // Exclude avatar and actions columns
                        },
    customize: function(doc) {
        doc.content[1].table.widths = ['20%', '20%', '20%', '20%', '20%'];
    doc.styles.tableHeader.fontSize = 10;
    doc.defaultStyle.fontSize = 9;
    doc.content[0].text = (showingInactive ? 'Inactive' : 'Active') + ' Superadmin Management Report';
    doc.content[0].style = 'header';

    // Add header
    doc.content.splice(0, 0, {
        text: [{
        text: 'Superadmin Panel - ' + (showingInactive ? 'Inactive' : 'Active') + ' Superadmins Report\n',
    fontSize: 16,
    bold: true
                                    },
    {
        text: 'Generated on: ' + new Date().toLocaleDateString() + '\n\n',
    fontSize: 10
                                    }
    ],
    alignment: 'center'
                            });
                        }
                    },
    {
        extend: 'print',
    text: '<i class="fas fa-print me-1"></i> Print',
    title: (showingInactive ? 'Inactive' : 'Active') + ' Superadmins Report',
    exportOptions: {
        columns: [1, 2, 3, 4, 5] // Exclude avatar and actions columns
                        }
                    }
    ],
    responsive: true,
    pageLength: 10,
    order: showingInactive ? [
    [5, 'desc']
    ] : [
    [4, 'desc']
    ], // Sort by updated_at for inactive, created_at for active
    language: {
        emptyTable: showingInactive ? "No inactive superadmin accounts found" : "No active superadmin accounts found",
    zeroRecords: showingInactive ? "No matching inactive superadmin accounts found" : "No matching active superadmin accounts found"
                }
            });

    // Edit Superadmin Function
    window.editSuperadmin = function(userId) {
        $.ajax({
            url: `<?= base_url('/superadmin/get-superadmin/') ?>${userId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    $('#edit_user_id').val(response.superadmin.user_id);
                    $('#edit_username').val(response.superadmin.username);
                    $('#editSuperadminModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error!', 'Failed to load superadmin data.', 'error');
            }
        });
            };

    // Edit Superadmin Form Submission
    $('#editSuperadminForm').submit(function(e) {
        e.preventDefault();

    // Clear previous errors
    $('.error-feedback').text('');
    $('.form-control').removeClass('is-invalid');

    const formData = new FormData(this);
    const userId = $('#edit_user_id').val();

    $.ajax({
        url: `<?= base_url('/superadmin/update-superadmin/') ?>${userId}`,
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
                        if (response.success) {
        $('#editSuperadminModal').modal('hide');
                            Swal.fire('Success!', response.message, 'success').then(() => {
        location.reload();
                            });
                        } else {
                            // Display validation errors
                            if (response.errors) {
        $.each(response.errors, function (field, message) {
            $(`#edit_${field}`).addClass('is-invalid');
            $(`#edit_${field}_error`).text(message);
        });
                            }
    Swal.fire('Error!', response.message || 'Failed to update superadmin.', 'error');
                        }
                    },
    error: function() {
        Swal.fire('Error!', 'An error occurred while updating the superadmin.', 'error');
                    }
                });
            });

    // Reset Password Function
    window.resetPassword = function(userId) {
        currentPasswordResetUserId = userId;
    $('#newPasswordDisplay').hide();
    $('#passwordResetModal').modal('show');
            };

    // Confirm Password Reset
    $('#confirmPasswordReset').click(function() {
                if (!currentPasswordResetUserId) return;

    $.ajax({
        url: `<?= base_url('/superadmin/reset-password/') ?>${currentPasswordResetUserId}`,
    method: 'POST',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
    success: function(response) {
                        if (response.success) {
        $('#newPasswordText').text(response.new_password);
    $('#newPasswordDisplay').show();
    $('#confirmPasswordReset').hide();
    Swal.fire('Success!', response.message, 'success');
                        } else {
        Swal.fire('Error!', response.message, 'error');
                        }
                    },
    error: function() {
        Swal.fire('Error!', 'An error occurred while resetting the password.', 'error');
                    }
                });
            });

    // Copy Password Function
    window.copyPassword = function() {
                const passwordText = $('#newPasswordText').text();
    navigator.clipboard.writeText(passwordText).then(function() {
        Swal.fire('Copied!', 'Password copied to clipboard.', 'success');
                });
            };

    // Delete (Deactivate) Superadmin Function
    window.deleteSuperadmin = function(userId) {
        Swal.fire({
            title: 'Deactivate Superadmin',
            text: 'This will deactivate the superadmin account. The account can be reactivated later.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deactivate it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `<?= base_url('/superadmin/delete-superadmin/') ?>${userId}`,
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deactivated!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An error occurred while deactivating the superadmin.', 'error');
                    }
                });
            }
        });
            };

    // Activate Superadmin Function
    window.activateSuperadmin = function(userId) {
        Swal.fire({
            title: 'Activate Superadmin',
            text: 'Are you sure you want to activate this superadmin account?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, activate it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `<?= base_url('/superadmin/activate-superadmin/') ?>${userId}`,
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Activated!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An error occurred while activating the superadmin.', 'error');
                    }
                });
            }
        });
            };

    // Reset password modal cleanup
    $('#passwordResetModal').on('hidden.bs.modal', function() {
        currentPasswordResetUserId = null;
    $('#newPasswordDisplay').hide();
    $('#confirmPasswordReset').show();
            });
        });
// SUPERADMIN tab's js code ends here.



// DASHBOARD container's script for animations
// Add smooth scrolling and animations
document.addEventListener('DOMContentLoaded', function () {
    // Animate stats on scroll
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-fade-in');
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;

            if (elementTop < window.innerHeight - elementVisible) {
                element.classList.add('animate__animated', 'animate__fadeInUp');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Initial check
});

// DASHBOARD container's script for animations ends here.


