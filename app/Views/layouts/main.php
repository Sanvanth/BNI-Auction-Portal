<?php
// At the top of the file, after any existing PHP code, add this to fetch event data
$eventName = 'BNI Cricket Auction'; // Default fallback
$eventLogo = 'public/assets/bnilogo1.png'; // Default fallback

// Get event data from session
$eventId = session()->get('event_id');
if ($eventId) {
    $db = \Config\Database::connect();
    $query = $db->query("SELECT event_name, event_logo FROM events WHERE event_id = ? AND status = 'Active'", [$eventId]);
    $eventData = $query->getRow();
    
    if ($eventData) {
        $eventName = $eventData->event_name;
        // Use event logo if it exists, otherwise use default
        if (!empty($eventData->event_logo)) {
            $eventLogo = 'uploads/events/' . $eventData->event_logo;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Cricket Auction System' ?></title>
    <link rel="icon" href="<?= base_url('public/assets/AppTeqLogo.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar overlay for mobile/when closed */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Menu toggle button */
        .menu-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1002;
            background: black;
            color: #f6d776ff;
            border: none;
            padding: 10px 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .menu-toggle:hover {
            background: #333;
            color: #f6d776ff;
            transform: scale(1.05);
        }

        /* Admin Dashboard's Sidebar CSS Styles */
        .sidebar {
            background: black;
            padding: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: -250px; /* Hidden by default */
            width: 250px;
            z-index: 1001;
            overflow-y: auto;
            transition: left 0.3s ease;

            /* Hide scrollbar for Webkit browsers (Chrome, Safari, Edge) */
            &::-webkit-scrollbar {
                width: 0;
                background: transparent;
                /* make scrollbar transparent */
            }

            /* Hide scrollbar for Firefox */
            scrollbar-width: none;
            /* Firefox */

            /* Hide scrollbar for Internet Explorer and Edge (older versions) */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .sidebar.show {
            left: 0; /* Show sidebar */
        }

        /* Close button inside sidebar */
        .sidebar-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: transparent;
            border: none;
            color: #f6d776ff;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 1002;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .sidebar-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Sidebar Navigation Text Link Styles */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #f6d776ff;
        }

        /* Sidebar Logo Styles */
        .sidebar-logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            background: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0);
            margin-bottom: 16px;
            /*space below logo */
        }

        /* Sidebar Text below logo Styles */
        .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px 20px 20px;
            /* Increases top padding for sidebar logo vertical centering */
        }

        .sidebar-brand h4 {
            margin: 0;
            font-weight: 600;
            width: 100%;
            text-align: center;
            /* Center the text under the logo */
            color: white;
        }

        /* Main content body CSS Styles - adjusted for collapsible sidebar */
        .main-content {
            margin-left: 0; /* No margin when sidebar is hidden */
            padding: 80px 20px 20px 20px; /* Extra top padding for menu button */
            background: #e5e4a8ff;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Navbar(static) CSS Styles */
        .navbar {
            background: black;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #f6d776ff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
            z-index: 1050 !important;
            /* to make navbar fixed in the top of the content page */
        }

        /* Navbar Text and User toggle styles */
        .navbar-brand {
            color: #f6d776ff !important;
        }

        .navbar .nav-link.dropdown-toggle {
            color: #fff !important;
        }

        .navbar .dropdown-menu {
            z-index: 1051 !important;
        }

        /* players & team sold unsold ststus card styles */
        .stats-card {
            background: black;
            color: #d5dfe3ff;
            border-radius: 8px;
            padding: 20px;
        }

        .stats-card h3 {
            margin: 0;
            font-size: 2rem;
        }

        .stats-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }

        /* player card(batsman, bowler, allrounder) by roles styles */
        .card {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 30px;
        }

        /* Role-specific player statistics card header styles */
        .role-stats-card>.card-header {
            background: black;
            color: #f6d776;
            border-radius: 8px 8px 0 0 !important;
            font-weight: bold;
            border-bottom: none;
        }

        /* Player Statistics by Role card panel and header theme */
        .role-stats-card {
            background: black;
            border: none !important;
            border-radius: 12px !important;
        }

        /* Role-specific player statistics card individual background style */
        .role-stats-card-in-card {
            background: #d8ab3b !important;
        }

        .role-stats-card-in-card h4 {
            font-size: clamp(0.9rem, 2vw, 1.25rem);
        }

        .role-stats-card-in-card p {
            font-size: clamp(0.75rem, 1.5vw, 1rem);
        }

        .role-stats-card-in-card h4,
        .role-stats-card-in-card p {
            white-space: normal;
            /* allow wrapping */
            word-break: break-word;
            /* break long words */
        }

        /* progress bar at the bottom of player statistics card */
        .pgbar {
            background: black !important;
        }

        .role-stats-card h4.text-primary {
            color: black !important;
            /* gold heading for roles */
        }

        /* All existing tables header styles */
        .card-header {
            background: black;
            color: #f6d776ff;
            border-radius: 8px 8px 0 0 !important;
        }

        .btn-outline-primary {
            background: #fff !important;
            color: #111 !important;
            border: 2px solid #111 !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background: #111 !important;
            color: #fff !important;
            border: 2px solid #111 !important;
        }

        /* Team logo(Auction page) styles */
        .team-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .team-logo:hover {
            transform: scale(1.1);
        }

        .team-logo.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Add/update player/team modal styles  */
        .modal-header {
            background: black;
            color: #f6d776;
        }

        /* All tables styles players, teams, leaderboard */
        .table,
        .table th,
        .table td {
            background-color: #fff !important;
            color: #222 !important;
        }

        /* dashboard gold table styles */
        .dashboard-gold-table,
        .dashboard-gold-table th,
        .dashboard-gold-table td {
            background: black !important;
            color: #fff !important;
        }

        /* DataTables: Style the pagination buttons (Next/Prev/page numbers) */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #222 !important;
            /* Black text */
            border: 1px solid #222 !important;
            /* Black border */
            background: #fff !important;
            /* White background for default */
            border-radius: 4px !important;
            margin: 0 2px;
            padding: 4px 12px;
        }

        /* DataTables: On hover, invert for focus */
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #fff !important;
            /* White text */
            background: #222 !important;
            /* Black background */
            border: 1px solid #222 !important;
        }

        /* DataTables: Active page button */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            color: #fff !important;
            /* White text */
            background: #222 !important;
            /* Black background */
            border: 1px solid #222 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .menu-toggle {
                top: 15px;
                left: 15px;
            }
            
            .main-content {
                padding: 70px 15px 15px 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Menu Toggle Button -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Close button inside sidebar -->
        <button class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>

        <div class="sidebar-brand d-flex flex-column align-items-center justify-content-center">
            <img src="<?= base_url($eventLogo) ?>" alt="Event Logo" class="sidebar-logo mb-2">
            <h4 class="mb-0 text-center">
                <i class="fas fa-cricket-ball"></i>
                <?= $eventName ?>
            </h4>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a class="nav-link <?= $activeMenu === 'players' ? 'active' : '' ?>"
                href="<?= base_url('admin/players') ?>">
                <i class="fas fa-users me-2"></i> Players
            </a>
            <a class="nav-link <?= $activeMenu === 'teams' ? 'active' : '' ?>" href="<?= base_url('admin/teams') ?>">
                <i class="fas fa-shield-alt me-2"></i> Teams
            </a>
            <a class="nav-link <?= $activeMenu === 'auction' ? 'active' : '' ?>"
                href="<?= base_url('admin/auction') ?>">
                <i class="fas fa-gavel me-2"></i> Auction Page
            </a>
            <a class="nav-link <?= $activeMenu === 'leaderboard' ? 'active' : '' ?>"
                href="<?= base_url('admin/leaderboard') ?>">
                <i class="fas fa-trophy me-2"></i> Leaderboard
            </a>
            <a class="nav-link <?= $activeMenu === 'bid_logs' ? 'active' : '' ?>"
                href="<?= base_url('admin/bid_logs') ?>">
                <i class="fas fa-history me-2"></i> Bid Logs
            </a>
            <a class="nav-link <?= $activeMenu === 'teams_details' ? 'active' : '' ?>"
                href="<?= base_url('auction/teams-details') ?>" target="_blank">
                <i class="fas fa-chart-pie me-2"></i> Teams Details
            </a>

            <a class="nav-link" href="<?= base_url('auction') ?>" target="_blank">
                <i class="fas fa-chart-pie me-2"></i> Auction
            </a>
            <!-- New Settings Menu -->
            <a class="nav-link <?= $activeMenu === 'settings' ? 'active' : '' ?>"
                href="<?= base_url('admin/settings') ?>">
                <i class="fas fa-cogs me-2"></i> Settings
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <h5 class="navbar-brand mb-0"><?= $pageTitle ?? 'Dashboard' ?></h5>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i><?= session()->get('username') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('logout') ?>"><i
                                        class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTables
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [0, 'desc']
                ]
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Sidebar toggle functionality
            const menuToggle = $('#menuToggle');
            const sidebar = $('#sidebar');
            const sidebarOverlay = $('#sidebarOverlay');
            const sidebarClose = $('#sidebarClose');
            const mainContent = $('.main-content');

            // Function to show sidebar
            function showSidebar() {
                sidebar.addClass('show');
                sidebarOverlay.addClass('show');
                // Optional: Push main content to the right on larger screens
                if ($(window).width() > 768) {
                    mainContent.css('margin-left', '250px');
                }
            }

            // Function to hide sidebar
            function hideSidebar() {
                sidebar.removeClass('show');
                sidebarOverlay.removeClass('show');
                mainContent.css('margin-left', '0');
            }

            // Toggle sidebar when menu button is clicked
            menuToggle.on('click', function() {
                if (sidebar.hasClass('show')) {
                    hideSidebar();
                } else {
                    showSidebar();
                }
            });

            // Close sidebar when close button is clicked
            sidebarClose.on('click', function() {
                hideSidebar();
            });

            // Close sidebar when overlay is clicked
            sidebarOverlay.on('click', function() {
                hideSidebar();
            });

            // Close sidebar when a navigation link is clicked (on mobile)
            $('.sidebar .nav-link').on('click', function() {
                if ($(window).width() <= 768) {
                    hideSidebar();
                }
            });

            // Handle window resize
            $(window).on('resize', function() {
                if ($(window).width() <= 768) {
                    mainContent.css('margin-left', '0');
                } else if (sidebar.hasClass('show')) {
                    mainContent.css('margin-left', '250px');
                }
            });

            // Close sidebar when pressing Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.hasClass('show')) {
                    hideSidebar();
                }
            });
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>