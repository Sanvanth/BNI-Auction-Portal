<!DOCTYPE html>
<html lang="en">

<?php require('components/head.php'); ?>

<style>
    .btn-create,
    .btn-toggle-view {
        background: #d8ab3b;
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        color: black !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        margin: 0 0.25rem;
    }

    .btn-toggle-view:hover {
        background: whitesmoke;
        color: white;
    }

    .btn-toggle-view.active {
        background: #d8ab3b;
    }

    .btn-toggle-view.active:hover {
        background: whitesmoke !important;
        color: black;
    }

    #eventsTable {
        width: 100% !important;
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

    .btn-delete {
        background: #d8ab3b;
        color: black;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        color: black;
    }

    .event-logo {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
    }

    .modal-header {
        background: black;
        color: #e5e4a8ff;
        border-radius: 20px 20px 0 0;
    }
</style>

<body>

    <?php include 'components/navbar.php'; ?>

    <div class="container main-container events-container">
        <!-- Statistics Cards -->
        <div class="row stats-cards">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= isset($stats['total']) ? $stats['total'] : '0' ?></div>
                    <div class="stat-label">Total Events</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= isset($stats['active']) ? $stats['active'] : '0' ?></div>
                    <div class="stat-label">Active Events</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= isset($stats['inactive']) ? $stats['inactive'] : '0' ?></div>
                    <div class="stat-label">Inactive Events</div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-calendar-alt me-2"></i>Event Management
                </h2>
                <div>
                    <button class="btn btn-toggle-view" id="toggleViewBtn" onclick="toggleEventView()">
                        <i class="fas fa-calendar-times me-2"></i>Show Inactive Events
                    </button>
                    <a href="<?= base_url('/superadmin/create-event') ?>" class="btn btn-create">
                        <i class="fas fa-plus me-2"></i>Create Event
                    </a>
                </div>
            </div>
            <div class="table-container">
                <div class="table-responsive">
                    <table id="eventsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Event Name</th>
                                <th>Description</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Admin Count</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Event
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit_event_id" name="event_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_event_name" class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Event Name
                                    </label>
                                    <input type="text" class="form-control" id="edit_event_name" name="event_name"
                                        required>
                                    <div class="error-feedback" id="edit_event_name_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_event_logo" class="form-label">
                                        <i class="fas fa-image me-2"></i>Event Logo
                                    </label>
                                    <input type="file" class="form-control" id="edit_event_logo" name="event_logo"
                                        accept="image/*">
                                    <div class="error-feedback" id="edit_event_logo_error"></div>
                                    <div id="current_logo_display" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_event_description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Event Description
                            </label>
                            <textarea class="form-control" id="edit_event_description" name="event_description"
                                rows="4"></textarea>
                            <!-- <div class="error-feedback" id="edit_event_description_error"></div> -->
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">
                                        <i class="fas fa-calendar-plus me-2"></i>Start Date
                                    </label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date"
                                        required>
                                    <div class="error-feedback" id="edit_start_date_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">
                                        <i class="fas fa-calendar-minus me-2"></i>End Date
                                    </label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                                    <div class="error-feedback" id="edit_end_date_error"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateEventBtn">
                        <i class="fas fa-save me-2"></i>Update Event
                        <span class="spinner-border spinner-border-sm ms-2 d-none" id="updateSpinner"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this event? This will soft delete the event (mark as inactive).
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This is a soft delete operation. The event will be marked as inactive but data will be
                        preserved.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete Event</button>
                </div>
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
        let showingInactive = false;
        let eventsTable;
        let eventToDelete = null;

        $(document).ready(function() {
            initializeDataTable();

            // Edit form date validation
            $('#edit_start_date').change(function() {
                const startDate = $(this).val();
                $('#edit_end_date').attr('min', startDate);

                const endDate = $('#edit_end_date').val();
                if (endDate && endDate <= startDate) {
                    $('#edit_end_date').val('');
                }
            });

            // Update event form submission
            $('#updateEventBtn').click(function() {
                updateEvent();
            });

            // Delete confirmation
            $('#confirmDelete').click(function() {
                if (eventToDelete) {
                    performDelete(eventToDelete);
                }
            });
        });

        function initializeDataTable() {
            if (eventsTable) {
                eventsTable.destroy();
            }

            eventsTable = $('#eventsTable').DataTable({
                ajax: {
                    url: '<?= base_url('/superadmin/events/list') ?>',
                    type: 'GET',
                    data: function(d) {
                        d.filter = showingInactive ? 'inactive' : 'active';
                    },
                    dataSrc: function(json) {
                        if (json.error) {
                            Swal.fire('Error!', json.error, 'error');
                            return [];
                        }
                        return json;
                    }
                },
                columns: [{
                        data: 'event_logo',
                        render: function(data, type, row) {
                            if (data) {
                                return `<img src="<?= base_url('uploads/events/') ?>${data}" class="event-logo" alt="Logo">`;
                            } else {
                                return `<div class="event-logo d-flex align-items-center justify-content-center" 
                                           style="background: var(--primary-gradient); color: white; font-weight: bold;">
                                           ${row.event_name.charAt(0)}
                                       </div>`;
                            }
                        }
                    },
                    {
                        data: 'event_name'
                    },
                    {
                        data: 'event_description',
                        render: function(data) {
                            return data.length > 50 ? data.substring(0, 50) + '...' : data;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const startDate = new Date(row.start_date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                            const endDate = new Date(row.end_date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                            return `${startDate} to ${endDate}`;
                        }
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'Active') {
                                return '<span class="status-badge status-active">Active</span>';
                            } else {
                                return '<span class="status-badge status-inactive">Inactive</span>';
                            }
                        }
                    },
                    {
                        data: 'admin_count'
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-action btn-edit" onclick="editEvent(${row.event_id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-action btn-delete" onclick="deleteEvent(${row.event_id})" title="Delete">
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
                        title: 'Events Report - ' + new Date().toLocaleDateString(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        title: 'Events Report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i> Print',
                        title: 'Events Report',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6]
                        }
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [
                    [6, 'desc']
                ],
                language: {
                    emptyTable: "No events found",
                    zeroRecords: "No matching events found"
                }
            });
        }

        function toggleEventView() {
            showingInactive = !showingInactive;
            const toggleBtn = $('#toggleViewBtn');

            if (showingInactive) {
                toggleBtn.html('<i class="fas fa-calendar-check me-2"></i>Show Active Events');
            } else {
                toggleBtn.html('<i class="fas fa-calendar-times me-2"></i>Show Inactive Events');
            }

            eventsTable.ajax.reload();
        }

        function editEvent(eventId) {
            // Clear previous errors
            $('.error-feedback').text('');
            $('.form-control').removeClass('is-invalid');

            // Fetch event data - use the route that matches your routes.php
            $.ajax({
                url: `<?= base_url('/superadmin/get-event-data/') ?>${eventId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const event = response.data;

                        // Debug: Log the event data to console
                        console.log('Event data received:', event);

                        // Populate form fields
                        $('#edit_event_id').val(event.event_id);
                        $('#edit_event_name').val(event.event_name);
                        $('#edit_event_description').val(event.event_description);

                        // Handle dates specifically - ensure they're in YYYY-MM-DD format
                        if (event.start_date) {
                            const startDate = new Date(event.start_date).toISOString().split('T')[0];
                            $('#edit_start_date').val(startDate);
                            console.log('Start date set to:', startDate);
                        }

                        if (event.end_date) {
                            const endDate = new Date(event.end_date).toISOString().split('T')[0];
                            $('#edit_end_date').val(endDate);
                            console.log('End date set to:', endDate);
                        }

                        // Show current logo if exists
                        if (event.event_logo) {
                            $('#current_logo_display').html(`
                        <small class="text-muted">Current logo:</small><br>
                        <img src="<?= base_url('uploads/events/') ?>${event.event_logo}" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
                    `);
                        } else {
                            $('#current_logo_display').html('<small class="text-muted">No logo uploaded</small>');
                        }

                        // Set date constraints after setting the values
                        if (event.start_date) {
                            let startDate = event.start_date;
                            if (startDate.includes(' ')) {
                                startDate = startDate.split(' ')[0];
                            }
                            $('#edit_end_date').attr('min', startDate);
                        }

                        // Show modal
                        $('#editEventModal').modal('show');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire('Error!', 'Failed to load event data.', 'error');
                }
            });
        }

        function updateEvent() {
            // Clear previous errors
            $('.error-feedback').text('');
            $('.form-control').removeClass('is-invalid');

            const updateBtn = $('#updateEventBtn');
            const updateSpinner = $('#updateSpinner');
            const eventId = $('#edit_event_id').val();

            // Show loading state
            updateBtn.prop('disabled', true);
            updateSpinner.removeClass('d-none');

            const formData = new FormData($('#editEventForm')[0]);

            $.ajax({
                url: `<?= base_url('/superadmin/update-event/') ?>${eventId}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            $('#editEventModal').modal('hide');
                            eventsTable.ajax.reload();
                        });
                    } else {
                        // Display validation errors
                        if (response.errors) {
                            $.each(response.errors, function(field, message) {
                                $(`#edit_${field}`).addClass('is-invalid');
                                $(`#edit_${field}_error`).text(message);
                            });
                        }

                        Swal.fire('Error!', response.message || 'Failed to update event.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'An unexpected error occurred. Please try again.', 'error');
                },
                complete: function() {
                    // Hide loading state
                    updateBtn.prop('disabled', false);
                    updateSpinner.addClass('d-none');
                }
            });
        }

        function deleteEvent(eventId) {
            eventToDelete = eventId;
            $('#deleteModal').modal('show');
        }

        function performDelete(eventId) {
            $.ajax({
                url: `<?= base_url('/superadmin/delete-event/') ?>${eventId}`,
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            eventsTable.ajax.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    $('#deleteModal').modal('hide');
                    Swal.fire('Error!', 'An error occurred while deleting the event.', 'error');
                }
            });
        }
    </script>
</body>

</html>