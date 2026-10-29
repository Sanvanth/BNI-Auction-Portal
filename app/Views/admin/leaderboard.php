<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Auction Leaderboard <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Event Info Alert -->
    <!-- <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle"></i>
        <strong>Event ID: <?= esc($event_id) ?></strong> - Showing leaderboard data for your assigned event only.
    </div> -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        Auction Leaderboard - Top 10 Expensive Buys 
                        <small class="text-muted"></small>
                    </h3>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="exportData('pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="exportData('excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="leaderboard_table">
                            <thead class="table-dark" style="color: black;">
                                <tr>
                                    <th>Rank</th>
                                    <th>Player Name</th>
                                    <th>Role</th>
                                    <th>Final Price</th>
                                    <th>Sold To</th>
                                    <th>Team Logo</th>
                                    <th>Player Image</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (isset($leaderboard) && count($leaderboard) > 0): ?>
                                    <?php foreach ($leaderboard as $index => $player): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary fs-6"><?= $index + 1 ?></span>
                                            </td>
                                            <td>
                                                <strong><?= esc($player['player_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= esc($player['company_name'] ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-capitalize">
                                                    <?= esc($player['player_role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold fs-5">
                                                    ₹<span class="indian-price"><?= floatval($player['final_price']) ?></span>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= esc($player['team_name'] ?? 'Unknown Team') ?></strong>
                                            </td>
                                            <td>
                                                <?php if (!empty($player['team_logo'])): ?>
                                                    <img src="<?= base_url('uploads/teams/' . $player['team_logo']) ?>"
                                                        alt="<?= esc($player['team_name']) ?>" class="rounded-circle"
                                                        style="width: 50px; height: 50px; object-fit: cover;"
                                                        onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'">
                                                <?php else: ?>
                                                    <img src="<?= base_url('uploads/teams/bni_logo.png') ?>" alt="Default Team"
                                                        class="rounded-circle"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($player['profile_pic'])): ?>
                                                    <img src="<?= base_url('uploads/players/' . $player['profile_pic']) ?>"
                                                        alt="<?= esc($player['player_name']) ?>" class="rounded-circle"
                                                        style="width: 50px; height: 50px; object-fit: cover;"
                                                        onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'">
                                                <?php else: ?>
                                                    <img src="<?= base_url('uploads/teams/bni_logo.png') ?>" alt="Default Player"
                                                        class="rounded-circle"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No sold players yet for Event </h5>
                                                <p class="text-muted">The leaderboard will be populated as players are sold
                                                    in the auction for this event.</p>
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

    <!-- Statistics Cards -->
    <?php if (isset($statistics)): ?>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Sold</h5>
                                <h3><?= $statistics['total_sold'] ?></h3>
                                <!-- <small>Event: <?= esc($event_id) ?></small> -->
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Value</h5>
                                <h3>₹<span class="indian-price"><?= $statistics['total_value'] ?></span></h3>
                                <!-- <small>Event: <?= esc($event_id) ?></small> -->
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Average Price</h5>
                                <h3>₹<span class="indian-price"><?= $statistics['average_price'] ?></span></h3>
                                <!-- <small>Event: <?= esc($event_id) ?></small> -->
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Highest Sale</h5>
                                <h3>₹<span class="indian-price"><?= $statistics['highest_sale'] ?></span></h3>
                                <!-- <small>Event: <?= esc($event_id) ?></small> -->
                            </div>
                            <div>
                                <i class="fas fa-crown fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#leaderboard_table').DataTable({
            "order": [[3, "desc"]], // Sort by Final Price descending
            "pageLength": 10,
            "responsive": true,
            "language": {
                "search": "Search players:",
                "lengthMenu": "Show _MENU_ players per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ players",
                "infoEmpty": "No players found for Event <?= esc($event_id) ?>",
                "zeroRecords": "No players found matching your search in Event <?= esc($event_id) ?>"
            },
            "columnDefs": [
                {
                    "targets": [5, 6], // Team Logo and Player Image columns
                    "orderable": false
                }
            ]
        });

        // Auto-refresh every 30 seconds
        setInterval(function () {
            location.reload();
        }, 30000);

        // Show flash messages if any
        <?php if (session()->getFlashdata('error')): ?>
            alert('Error: <?= session()->getFlashdata('error') ?>');
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('success')): ?>
            alert('Success: <?= session()->getFlashdata('success') ?>');
        <?php endif; ?>
    });

    function exportData(format) {
        window.location.href = '<?= base_url('admin/leaderboard/export') ?>/' + format;
    }

    // Add some custom styling
    const style = document.createElement('style');
    style.textContent = `
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: black;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: black;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.1);
    }
    
    .badge {
        font-size: 0.875rem;
        padding: 0.5em 0.75em;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .fw-bold {
        font-weight: bold !important;
    }
    
    .fs-5 {
        font-size: 1.25rem !important;
    }
    
    .fs-6 {
        font-size: 1rem !important;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        color: white;
    }
    
    .alert-info .fas {
        margin-right: 10px;
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

    document.addEventListener("DOMContentLoaded", function() {
        const priceElements = document.querySelectorAll('.indian-price');
        priceElements.forEach(el => {
            const price = el.textContent;
            el.textContent = formatPrice(price);
        });
    });
</script>
<?= $this->endSection() ?>