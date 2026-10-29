<!DOCTYPE html>
<html lang="en">

<?php require('components/head.php'); ?>

<style>
    .dropdown-menu {
        z-index: 1000;
        /* Use a value higher than other page elements */
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .animate-fade-in {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<body>
    <?php require('components/navbar.php'); ?>

    <div class="container main-container dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section animate-fade-in">
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome back, <?= session()->get('username') ?>!</h1>
                <p class="welcome-subtitle">Here's what's happening in your auction system today.</p>

                <div class="action-buttons">
                    <a href="<?= base_url('/superadmin/create-event') ?>" class="btn btn-action btn-events">
                        <i class="fas fa-plus me-2"></i>Create Event
                    </a>
                    <a href="<?= base_url('/superadmin/create-admin') ?>" class="btn btn-action btn-admins">
                        <i class="fas fa-user-plus me-2"></i>Add Admin
                    </a>
                    <a href="<?= base_url('/superadmin/create-superadmin') ?>" class="btn btn-action btn-superadmins">
                        <i class="fas fa-crown me-2"></i>Add Superadmin
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon icon-events me-3">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <div class="stats-number"><?= $totalEvents ?></div>
                                <div class="stats-label">Total Events</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-card animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon icon-active me-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <div class="stats-number"><?= $activeEvents ?></div>
                                <div class="stats-label">Active Events</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-card animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon icon-admins me-3">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div>
                                <div class="stats-number"><?= $totalAdmins ?></div>
                                <div class="stats-label">Admin Users</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-card animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon icon-superadmins me-3">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>
                                <div class="stats-number"><?= $totalSuperadmins ?></div>
                                <div class="stats-label">Superadmins</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions animate-fade-in" style="animation-delay: 0.5s;">
            <h3 class="section-title">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </h3>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="d-grid">
                        <a href="<?= base_url('/superadmin/events') ?>" class="btn btn-action btn-events">
                            <i class="fas fa-calendar-alt me-2"></i>Manage Events
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid">
                        <a href="<?= base_url('/superadmin/admins') ?>" class="btn btn-action btn-admins">
                            <i class="fas fa-users-cog me-2"></i>Manage Admins
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid">
                        <a href="<?= base_url('/superadmin/superadmins') ?>" class="btn btn-action btn-superadmins">
                            <i class="fas fa-user-shield me-2"></i>Manage Superadmins
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require('components/footer.php'); ?>
</body>

</html>