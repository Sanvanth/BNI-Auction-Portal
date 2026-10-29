<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Summary Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stats-card">
            <h3><?= esc($total_players) ?></h3>
            <p><i class="fas fa-users me-2"></i>Total Players</p>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stats-card">
            <h3><?= esc($total_teams) ?></h3>
            <p><i class="fas fa-shield-alt me-2"></i>Total Teams</p>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stats-card">
            <h3>
                <?php
                $total_sold = 0;
                if (is_array($player_stats) || is_object($player_stats)) {
                    foreach ($player_stats as $stats) {
                        $stats_data = is_array($stats) ? $stats : (array) $stats;
                        if (isset($stats_data['sold_active'])) {
                            $total_sold += $stats_data['sold_active'];
                        } elseif (isset($stats_data['sold'])) {
                            $total_sold += $stats_data['sold'];
                        }
                    }
                }
                echo esc($total_sold);
                ?>
            </h3>
            <p><i class="fas fa-handshake me-2"></i>Sold Players</p>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stats-card">
            <h3><?= esc($unsold_players) ?></h3>
            <p><i class="fas fa-user-times me-2"></i>Unsold Players</p>
        </div>
    </div>
</div>

<!-- Player Statistics -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card role-stats-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Player Statistics by Role</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $stats_array = is_array($player_stats) ? $player_stats : (array)$player_stats;
                    foreach ($stats_array as $role => $stats):
                        $stats_data = is_array($stats) ? $stats : (array)$stats;
                    ?>

                        <div class="col-md-3 mb-3">
                            <div class="card role-stats-card-in-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between" style="height: 120px;">
                                        <?php if ($role == 'batsman'): ?>
                                            <img src="<?= base_url('public/assets/bm.png') ?>" alt="Batsman" style="width:80px;height:80px;" />
                                        <?php elseif ($role == 'bowler'): ?>
                                            <img src="<?= base_url('public/assets/Bwl.png') ?>" alt="Bowler" style="width:80px;height:80px;" />
                                        <?php elseif ($role == 'allrounder'): ?>
                                            <img src="<?= base_url('public/assets/Allll.png') ?>" alt="Allrounder" style="width:80px;height:80px;" />
                                        <?php elseif ($role == 'wicketkeeper'): ?>
                                            <img src="<?= base_url('public/assets/WK.png') ?>" alt="Wicketkeeper" style="width:80px;height:80px;" />
                                        <?php endif; ?>
                                        <div class="flex-grow-1 ms-3 text-end">
                                            <h4 class="text-primary mb-2"><?= ucfirst($role) ?></h4>
                                            <p class="mb-1 fs-5">Total: <strong><?= $stats_data['total'] ?></strong></p>
                                            <p class="mb-0 fs-6">Sold: <strong><?= $stats_data['sold'] ?></strong></p>
                                        </div>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar pgbar"
                                            role="progressbar"
                                            style="width: <?= $stats_data['total'] > 0 ? ($stats_data['sold'] / $stats_data['total']) * 100 : 0 ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team Composition -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Team Composition</h5>
                <div class="btn-group">
                    <a href="<?= base_url('/admin/export-team-composition/pdf') ?>" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('/admin/export-team-composition/excel') ?>" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped data-table dashboard-gold-table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Team Name</th>
                                <th>Total Players</th>
                                <th>Batsmen</th>
                                <th>Bowlers</th>
                                <th>Allrounders</th>
                                <th>Wicketkeepers</th>
                                <th>Remaining Purse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; ?>
                            <?php foreach ((array) $team_composition as $team): ?>
                                <?php $team_data = is_array($team) ? $team : (array) $team; ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($team_data['team_name']) ?></td>
                                    <td><?= esc($team_data['total_players']) ?></td>
                                    <td><?= esc($team_data['batsmen']) ?></td>
                                    <td><?= esc($team_data['bowlers']) ?></td>
                                    <td><?= esc($team_data['allrounders']) ?></td>
                                    <td><?= esc($team_data['wicketkeepers']) ?></td>
                                    <td>₹<span class="indian-price"><?= $team_data['purse'] ?></span></td> 
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const activeMenu = 'Dashboard';
    function formatPrice(price) {
        if (!price) return '0';
        return parseInt(price).toLocaleString('en-IN');
    }
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.indian-price').forEach(el => {
            const price = el.textContent;
            el.textContent = formatPrice(price);
        });
    });
</script>
<?= $this->endSection() ?>
