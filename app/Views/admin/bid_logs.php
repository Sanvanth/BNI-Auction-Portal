<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Bid Logs<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Event Info Header
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <div>
                    <strong>Current Event:</strong> Event ID - <?= session()->get('event_id') ?>
                    <small class="ms-3 text-muted">Showing data for your assigned event only</small>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Statistics Cards -->
    <?php if (isset($statistics)): ?>
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Total Bids</h6>
                                <h4 class="mb-0"><?= number_format($statistics['total_bids']) ?></h4>
                            </div>
                            <div>
                                <i class="fas fa-gavel fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Total Value</h6>
                                <h4 class="mb-0">₹<span class="indian-price"><?= $statistics['total_bid_value'] ?></span>
                                </h4>
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Average Bid</h6>
                                <h4 class="mb-0">₹<span class="indian-price"><?= $statistics['average_bid'] ?></span></h4>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Highest Bid</h6>
                                <h4 class="mb-0">₹<span class="indian-price"><?= $statistics['highest_bid'] ?></span></h4>
                            </div>
                            <div>
                                <i class="fas fa-crown fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list-alt me-2"></i>Bid Logs
                            <small class="text-light ms-2"></small>
                        </h3>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="exportData('pdf')"
                                title="Export to PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="exportData('excel')"
                                title="Export to Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading bid data...</p>
                    </div>

                    <!-- Table Container -->
                    <div class="table-responsive" id="table-container">
                        <table class="table table-striped table-hover" id="bid_logs_table">
                            <thead class="table-dark" style="color: black;">
                                <tr>
                                    <th style="width: 60px;">S.No</th>
                                    <th style="min-width: 180px;">Player Details</th>
                                    <th style="min-width: 120px;">Team</th>
                                    <th style="width: 60px;">Logo</th>
                                    <th style="min-width: 100px;">Bid Amount</th>
                                    <th style="min-width: 130px;">Bid Time</th>
                                    <th style="width: 80px;">Role</th>
                                    <th style="width: 80px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="bid_logs_tbody">
                                <?php if (isset($bid_logs) && count($bid_logs) > 0): ?>
                                    <?php foreach ($bid_logs as $index => $bid): ?>
                                        <tr data-bid-id="<?= $bid['bid_id'] ?>" data-event-id="<?= session()->get('event_id') ?>">
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <div>
                                                    <strong class="text-dark"><?= esc($bid['player_name']) ?></strong>
                                                    <?php if (!empty($bid['company_name'])): ?>
                                                        <br><small class="text-muted"><?= esc($bid['company_name']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($bid['chapter_name'])): ?>
                                                        <br><small class="text-info"><?= esc($bid['chapter_name']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?= esc($bid['team_name'] ?? 'No Team') ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($bid['team_logo'])): ?>
                                                    <img src="<?= base_url('uploads/teams/' . $bid['team_logo']) ?>"
                                                        alt="<?= esc($bid['team_name']) ?>" class="rounded-circle team-logo"
                                                        style="width: 35px; height: 35px; object-fit: cover;"
                                                        onerror="this.src='<?= base_url('public/assets/default-team1.png') ?>'">
                                                <?php else: ?>
                                                    <img src="<?= base_url('public/assets/default-team1.png') ?>" alt="Default Team"
                                                        class="rounded-circle team-logo"
                                                        style="width: 35px; height: 35px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold fs-6">
                                                    ₹<span class="indian-price"><?= $bid['bid_amount'] ?></span>
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <span
                                                        class="text-dark"><?= date('M j, Y', strtotime($bid['created_at'])) ?></span>
                                                    <br><small
                                                        class="text-muted"><?= date('g:i A', strtotime($bid['created_at'])) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-capitalize rounded-pill">
                                                    <?= esc($bid['player_role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($bid['is_sold']) && $bid['is_sold']): ?>
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="fas fa-check-circle me-1"></i>Sold
                                                    </span>
                                                <?php elseif (isset($bid['is_unsold']) && $bid['is_unsold']): ?>
                                                    <span class="badge bg-warning text-dark rounded-pill">
                                                        <i class="fas fa-exclamation-circle me-1"></i>Unsold
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">
                                                        <i class="fas fa-clock me-1"></i>Bidding
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="no-data-row">
                                        <td colspan="8" class="text-center py-5">
                                            <div class="no-data-message">
                                                <i class="fas fa-gavel fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">No bid logs found for this event</h5>
                                                <p class="text-muted mb-0">Bid logs for Event ID <?= session()->get('event_id') ?> will appear here as the auction progresses.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables
    const currentEventId = <?= session()->get('event_id') ?>;
    
    $(document).ready(function () {
        // Initialize DataTable with enhanced options
        const table = $('#bid_logs_table').DataTable({
            "order": [[5, "desc"]], // Sort by Bid Time descending
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "responsive": true,
            "processing": true,
            "language": {
                "search": "Quick Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ bid logs (Event: " + currentEventId + ")",
                "infoEmpty": "No bid logs available for this event",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "zeroRecords": "No matching bid logs found for this event",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "columnDefs": [
                {
                    "targets": [3], // Team Logo column
                    "orderable": false,
                    "searchable": false
                },
                {
                    "targets": [0], // S.No column
                    "orderable": false,
                    "searchable": false
                }
            ],
            "dom": 'lBfrtip',
            "buttons": [
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-outline-secondary',
                    title: 'Bid Logs - Event ' + currentEventId
                },
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-outline-success',
                    filename: 'bid_logs_event_' + currentEventId + '_' + new Date().toISOString().slice(0,10)
                }
            ]
        });

        // Auto-refresh functionality
        let autoRefresh = true;
        const refreshInterval = setInterval(function () {
            if (autoRefresh) {
                refreshData(false); // Silent refresh
            }
        }, 3000); // Refresh every 3 seconds
    });

    function updateTable(bidLogs) {
        const tbody = $('#bid_logs_tbody');
        tbody.empty();

        if (bidLogs && bidLogs.length > 0) {
            bidLogs.forEach((bid, index) => {
                const statusBadge = getStatusBadge(bid);
                const teamLogo = getTeamLogo(bid);

                const row = `
                <tr data-bid-id="${bid.bid_id}" data-event-id="${currentEventId}">
                    <td class="text-center">${index + 1}</td>
                    <td>
                        <div>
                            <strong class="text-dark">${escapeHtml(bid.player_name)}</strong>
                            ${bid.company_name ? `<br><small class="text-muted">${escapeHtml(bid.company_name)}</small>` : ''}
                            ${bid.chapter_name ? `<br><small class="text-info">${escapeHtml(bid.chapter_name)}</small>` : ''}
                        </div>
                    </td>
                    <td><strong class="text-primary">${escapeHtml(bid.team_name || 'No Team')}</strong></td>
                    <td class="text-center">${teamLogo}</td>
                    <td><span class="text-success fw-bold fs-6">₹${numberFormat(bid.bid_amount)}</span></td>
                    <td>
                        <div>
                            <span class="text-dark">${formatDate(bid.created_at)}</span>
                            <br><small class="text-muted">${formatTime(bid.created_at)}</small>
                        </div>
                    </td>
                    <td><span class="badge bg-info text-capitalize rounded-pill">${escapeHtml(bid.player_role)}</span></td>
                    <td>${statusBadge}</td>
                </tr>
            `;
                tbody.append(row);
            });
        } else {
            tbody.append(`
            <tr id="no-data-row">
                <td colspan="8" class="text-center py-5">
                    <div class="no-data-message">
                        <i class="fas fa-gavel fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No bid logs found for this event</h5>
                        <p class="text-muted mb-0">Bid logs for Event ID ${currentEventId} will appear here as the auction progresses.</p>
                    </div>
                </td>
            </tr>
        `);
        }

        // Reinitialize DataTable
        if ($.fn.DataTable.isDataTable('#bid_logs_table')) {
            $('#bid_logs_table').DataTable().destroy();
        }

        $('#bid_logs_table').DataTable({
            "order": [[5, "desc"]],
            "pageLength": 25,
            "responsive": true,
            "language": {
                "info": "Showing _START_ to _END_ of _TOTAL_ bid logs (Event: " + currentEventId + ")",
                "infoEmpty": "No bid logs available for this event",
                "zeroRecords": "No matching bid logs found for this event"
            },
            "columnDefs": [
                { "targets": [3], "orderable": false, "searchable": false },
                { "targets": [0], "orderable": false, "searchable": false }
            ]
        });
    }

    function updateStatistics(stats) {
        if (stats) {
            // Update statistic cards dynamically
            if (stats.total_bids !== undefined) {
                $('.card.bg-primary .card-body h4').text(numberFormat(stats.total_bids, 0));
            }
            if (stats.total_bid_value !== undefined) {
                $('.card.bg-success .card-body h4').html('₹<span class="indian-price">' + stats.total_bid_value + '</span>');
                // Re-format the new price
                formatIndianPrices();
            }
            if (stats.average_bid !== undefined) {
                $('.card.bg-warning .card-body h4').html('₹<span class="indian-price">' + stats.average_bid + '</span>');
                // Re-format the new price
                formatIndianPrices();
            }
            if (stats.highest_bid !== undefined) {
                $('.card.bg-info .card-body h4').html('₹<span class="indian-price">' + stats.highest_bid + '</span>');
                // Re-format the new price
                formatIndianPrices();
            }
        }
    }

    function getStatusBadge(bid) {
        if (bid.is_sold) {
            return '<span class="badge bg-success rounded-pill"><i class="fas fa-check-circle me-1"></i>Sold</span>';
        } else if (bid.is_unsold) {
            return '<span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-exclamation-circle me-1"></i>Unsold</span>';
        } else {
            return '<span class="badge bg-secondary rounded-pill"><i class="fas fa-clock me-1"></i>Bidding</span>';
        }
    }

    function getTeamLogo(bid) {
        const logoSrc = bid.team_logo ?
            `<?= base_url('uploads/teams/') ?>${bid.team_logo}` :
            '<?= base_url('uploads/teams/bni_logo.png') ?>';

        return `<img src="${logoSrc}" alt="${escapeHtml(bid.team_name || 'Default Team')}" 
                 class="rounded-circle team-logo" 
                 style="width: 35px; height: 35px; object-fit: cover;"
                 onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'">`;
    }

    function exportData(format) {
        const url = '<?= base_url('admin/bid_logs/export') ?>/' + format;
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showAlert(`Exporting ${format.toUpperCase()} for Event ${currentEventId}...`, 'info', 3000);
    }

    function showLoading() {
        $('#loading-spinner').removeClass('d-none');
        $('#table-container').addClass('opacity-50');
    }

    function hideLoading() {
        $('#loading-spinner').addClass('d-none');
        $('#table-container').removeClass('opacity-50');
    }

    function showAlert(message, type = 'info', duration = 5000) {
        const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

        $('body').append(alertHtml);

        if (duration > 0) {
            setTimeout(() => {
                $('.alert').alert('close');
            }, duration);
        }
    }

    // Utility functions
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

    function numberFormat(number, decimals = 2) {
        return new Intl.NumberFormat('en-IN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatTime(dateString) {
        return new Date(dateString).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatIndianPrices() {
        const priceElements = document.querySelectorAll('.indian-price');
        priceElements.forEach(el => {
            const price = el.textContent;
            el.textContent = formatPrice(price);
        });
    }

    // Custom CSS
    const style = document.createElement('style');
    style.textContent = `
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transition: background-color 0.2s ease;
    }
    
    .team-logo {
        transition: transform 0.2s ease;
    }
    
    .team-logo:hover {
        transform: scale(1.1);
    }
    
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .no-data-message {
        padding: 2rem;
    }
    
    .opacity-50 {
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }

    .alert-info {
        background-color: #e3f2fd;
        border-color: #bbdefb;
        color: #1565c0;
    }
    
    @media (max-width: 768px) {
        .card-header .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .alert-info {
            font-size: 0.875rem;
        }
    }
`;
    document.head.appendChild(style);
</script>

<script>
    function formatPrice(price) {
        if (!price) return '0';
        return parseFloat(price).toLocaleString('en-IN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        formatIndianPrices();
    });
</script>
<?= $this->endSection() ?>