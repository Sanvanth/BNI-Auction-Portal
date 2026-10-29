<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Account - Cricket Auction</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="<?= base_url('public/assets/AppTeqLogo.png') ?>" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.6/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">

    <style id="darkThemeStyles">
        :root {
            /* Dark Theme Colors */
            --primary-bg: #0A090A;
            --secondary-bg: #2a2a2a;
            --primary-color: #ffffff;
            --secondary-color: #d8ab3b;
            --accent-color-dark: #d8ab3b;
            --accent-color-light: #FFF8DC;
            --link-color: #d8ab3b;
            --error-color: #ff6b6b;
        }

        body.dark-theme {
            background: var(--primary-bg);
            color: var(--primary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        .dashboard-text-dark {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }

        .team-header-dark {
            background: var(--primary-bg);
            border: 2px solid var(--secondary-color);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .team-logo-dark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--secondary-color);
            object-fit: cover;
        }

        .team-name-dark {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-color-light);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }

        /* Added for consistency, although not strictly needed for dark mode due to inheritence */
        .team-purse-heading-dark {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .purse-amount-dark {
            font-size: 2rem;
            color: var(--secondary-color);
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        .action-btn-dark {
            background: var(--secondary-color);
            color: black;
            border: none;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .action-btn-dark:hover {
            background: whitesmoke;
            color: black;
        }

        .action-btn-dark.active {
            background: linear-gradient(45deg, var(--accent-color-dark), var(--secondary-color));
            transform: translateY(-5px);
            border: 2px solid var(--accent-color-light);
            border-radius: 10px;
        }

        /* START: Custom Dropdown/Profile Icon Styles for Dark Theme */
        .profile-dropdown-btn-dark {
            background: var(--secondary-color);
            color: var(--primary-bg);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .profile-dropdown-btn-dark:hover {
            background: var(--accent-color-light);
            color: var(--primary-bg);
        }

        .dropdown-menu-dark {
            background-color: var(--secondary-bg);
            border: 1px solid var(--secondary-color);
        }

        .dropdown-item-dark {
            color: var(--primary-color) !important;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 10px 15px;
            display: flex;
            align-items: center;
        }

        .dropdown-item-dark:hover {
            background-color: rgba(216, 171, 59, 0.2);
            color: var(--secondary-color) !important;
        }

        .theme-btn-dark,
        .logout-btn-dark {
            /* Reusing existing styles */
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 15px;
            margin: 0;
            border-radius: 0;
            background: none;
            color: var(--primary-color);
            border: none;
        }

        .theme-btn-dark:hover,
        .logout-btn-dark:hover {
            background-color: rgba(216, 171, 59, 0.2);
            color: var(--secondary-color);
        }

        /* END: Custom Dropdown/Profile Icon Styles for Dark Theme */

        /* Original Styles for reference and cleanup */
        .logout-btn-dark {
            background: var(--secondary-color);
            color: black;
            border: whitesmoke;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-btn-dark:hover {
            background: whitesmoke;
            color: black;
        }

        .theme-btn-dark {
            background: var(--primary-bg);
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .theme-btn-dark:hover {
            background: var(--secondary-color);
            color: var(--primary-bg);
        }

        .role-card-dark {
            background: var(--primary-bg);
            border: 2px solid var(--secondary-color);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .role-card-dark:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 215, 0, 0.4);
            border-color: var(--accent-color-light);
        }

        .role-count-dark {
            font-size: 3.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            margin-right: 15px;
        }

        .role-name-dark {
            font-size: 1.3rem;
            color: var(--secondary-color);
            margin-top: 10px;
            text-align: center;
        }

        .role-image-dark {
            width: 40%;
            height: auto;
            object-fit: contain;
        }

        .role-header-dark {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .player-card-dark {
            background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
            border: 2px solid #555;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .player-card-dark:hover {
            transform: translateY(-3px);
            border-color: var(--secondary-color);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }

        .player-card-dark.captain-owner {
            border: 3px solid var(--secondary-color);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
            background: linear-gradient(135deg, #3a2a1a, #2a1a0a);
        }

        .player-card-dark.captain-owner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 40%, rgba(255, 215, 0, 0.1) 50%, transparent 60%);
            z-index: 1;
        }

        .player-pic-dark {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid var(--secondary-color);
            object-fit: cover;
            box-shadow: 0 3px 10px rgba(255, 215, 0, 0.4);
        }

        .player-name-dark {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 15px 0 5px 0;
        }

        .player-role-dark {
            color: #bbb;
            font-size: 1rem;
            text-transform: capitalize;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .player-role-image-dark {
            width: 16px;
            height: 16px;
            object-fit: contain;
            filter: brightness(0) saturate(100%) invert(73%) sepia(3%) saturate(396%) hue-rotate(314deg) brightness(97%) contrast(93%);
        }

        .player-price-dark {
            color: var(--secondary-color);
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 10px;
        }

        .section-title-dark {
            color: var(--secondary-color);
            font-size: 2.2rem;
            font-weight: bold;
            margin: 40px 0 25px 0;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }

        .captain-badge-dark {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary-color);
            color: var(--primary-bg);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 2;
        }

        .cricheroes-badge-dark {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.4);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 2px solid #4CAF50;
        }

        .cricheroes-badge-dark:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.6);
        }

        .cricheroes-badge-dark img {
            width: 20px;
            height: 20px;
            object-fit: contain;
            border-radius: 50%;
        }

        .loading-dark {
            text-align: center;
            color: var(--secondary-color);
            font-size: 1.5rem;
            padding: 50px;
        }

        .no-data-dark {
            text-align: center;
            color: #888;
            font-size: 1.3rem;
            padding: 40px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 10px;
            margin: 20px 0;
        }

        .error-message-dark {
            text-align: center;
            color: var(--error-color);
            font-size: 1.3rem;
            padding: 40px;
            background: rgba(255, 107, 107, 0.1);
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid var(--error-color);
        }

        /* Control Panel Styles */
        .control-panel-dark {
            background: var(--primary-bg);
            border: 2px solid var(--secondary-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .search-container-dark {
            position: relative;
            margin-bottom: 20px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-input-dark {
            background: var(--primary-bg);
            border: 2px solid #555;
            border-radius: 10px;
            color: white;
            padding: 12px 45px 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input-dark:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .search-input-dark::placeholder {
            color: #aaa;
        }

        .search-icon-dark {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        .toggle-container-dark {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .toggle-switch-dark {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch-dark input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-dark {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--primary-bg);
            transition: 0.4s;
            border-radius: 34px;
            border: 2px solid var(--secondary-color);
        }

        .slider-dark:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: var(--accent-color-light);
            transition: 0.4s;
            border-radius: 50%;
        }

        .toggle-switch-dark input:checked+.slider-dark {
            background-color: var(--accent-color-dark);
        }

        .toggle-switch-dark input:checked+.slider-dark:before {
            transform: translateX(26px);
        }

        .toggle-label-dark {
            color: var(--secondary-color);
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* DataTable Styles */
        .dataTables_wrapper-dark {
            color: white !important;
        }

        .table-dark-theme {
            --bs-table-bg: linear-gradient(135deg, #2a2a2a, #1a1a1a);
            border: 2px solid var(--secondary-color);
            border-radius: 10px;
            overflow: hidden;
        }

        .table-dark-theme th {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color-dark)) !important;
            color: var(--primary-bg) !important;
            border-color: var(--secondary-color) !important;
            font-weight: bold;
        }

        .table-dark-theme td {
            border-color: #555 !important;
            color: white !important;
            vertical-align: middle;
        }

        .table-dark-theme tbody tr:hover {
            background-color: rgba(255, 215, 0, 0.1) !important;
        }

        .dataTables_length-dark select {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 2px solid #555 !important;
            border-radius: 8px !important;
            color: white !important;
            padding: 5px !important;
        }

        .dataTables_info-dark,
        .dataTables_length-dark label,
        .dataTables_filter-dark label {
            color: white !important;
        }

        .page-link-dark {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: #555 !important;
            color: var(--secondary-color) !important;
        }

        .page-link-dark:hover {
            background-color: var(--secondary-color) !important;
            color: var(--primary-bg) !important;
        }

        .page-item.active .page-link-dark {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
            color: var(--primary-bg) !important;
        }

        .dt-player-pic-dark {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--secondary-color);
            object-fit: cover;
        }

        .dt-role-badge-dark {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color-dark));
            color: var(--primary-bg);
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.85rem;
            display: inline-block;
        }

        .dt-captain-badge-dark {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 0.75rem;
            margin-left: 8px;
        }

        .dt-cricheroes-link-dark {
            color: var(--link-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dt-cricheroes-link-dark:hover {
            color: var(--accent-color-light);
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
        }

        @media (max-width: 768px) {
            .team-name-dark {
                font-size: 1.8rem;
            }

            .purse-amount-dark {
                font-size: 1.5rem;
            }

            .action-btn-dark {
                width: 100%;
                margin: 5px 0;
            }

            .role-count-dark {
                font-size: 2rem;
            }

            .toggle-container-dark {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Generic DataTables class fixes (since it doesn't take theme suffix) */
        .dataTables_filter {
            display: none;
        }
    </style>

    <style id="lightThemeStyles" disabled>
        :root {
            /* Light Theme Colors */
            --primary-bg: #98B1BF;
            --secondary-bg: #ffffff;
            --primary-color: #ffffff;
            --secondary-color: #2d6dc3;
            /* Deep Blue */
            --accent-color-dark: #2d6dc3;
            --accent-color-light: #FFF8DC;
            --link-color: #d8ab3b;
            --error-color: #ff6b6b;
            --inactive-btn-bg: #EEF5FF;
            /* Light Blue for inactive buttons */
        }

        a {
            text-decoration: none;
        }

        body.light-theme {
            background: var(--primary-bg);
            color: var(--primary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-text-light {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            /* text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8); */
        }

        .team-header-light {
            background: var(--secondary-bg);
            border: 3px solid var(--secondary-color);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .team-logo-light {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            object-fit: cover;
        }

        .team-name-light {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            /* text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8); */
        }

        /* FIX: Added explicit color for "Team Purse" heading */
        .team-purse-heading-light {
            font-size: 1.5rem;
            color: var(--secondary-color);
            /* Deep Blue */
            margin-bottom: 5px;
        }

        .purse-amount-light {
            font-size: 2rem;
            color: black;
            font-weight: bold;
            /* text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8); */
        }

        /* FIX: Inactive light theme buttons are now visible */
        .action-btn-light {
            background: var(--inactive-btn-bg);
            /* Use light blue for visibility */
            color: var(--secondary-color);
            border: none;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .action-btn-light:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
        }

        .action-btn-light.active {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-5px);
            border: 2px solid var(--accent-color-light);
            border-radius: 10px;
        }

        /* START: Custom Dropdown/Profile Icon Styles for Light Theme */
        .profile-dropdown-btn-light {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .profile-dropdown-btn-light:hover {
            background: var(--primary-color);
            color: var(--secondary-color);
        }

        .dropdown-menu-light {
            background-color: var(--secondary-bg);
            border: 1px solid var(--secondary-color);
        }

        .dropdown-item-light {
            color: var(--secondary-color) !important;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 10px 15px;
            display: flex;
            align-items: center;
        }

        .dropdown-item-light:hover {
            background-color: rgba(45, 109, 195, 0.2);
            color: var(--primary-bg) !important;
        }

        .theme-btn-light,
        .logout-btn-light {
            /* Reusing existing styles */
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 15px;
            margin: 0;
            border-radius: 0;
            background: none;
            color: var(--secondary-color);
            border: none;
        }

        .theme-btn-light:hover,
        .logout-btn-light:hover {
            background-color: rgba(45, 109, 195, 0.2);
            color: var(--primary-bg);
        }

        /* END: Custom Dropdown/Profile Icon Styles for Light Theme */

        /* Original Styles for reference and cleanup */
        .logout-btn-light {
            background: var(--secondary-bg);
            color: var(--secondary-color);
            border: whitesmoke;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-btn-light:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
        }

        .theme-btn-light {
            background: var(--secondary-bg);
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .theme-btn-light:hover {
            background: var(--secondary-color);
            color: var(--primary-bg);
        }


        .role-card-light {
            background: var(--secondary-color);
            border: 2px solid var(--primary-color);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .role-card-light:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px var(--secondary-bg);
            border-color: var(--accent-color-light);
        }

        .role-count-light {
            font-size: 3.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            margin-right: 15px;
        }

        .role-name-light {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-top: 10px;
            text-align: center;
        }

        .role-image-light {
            width: 40%;
            height: auto;
            object-fit: contain;
        }

        .role-header-light {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .player-card-light {
            background: rgba(255, 255, 255, 0.7);
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .player-card-light:hover {
            transform: translateY(-3px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 25px rgba(26, 104, 213, 0.5);
        }

        .player-card-light.captain-owner {
            border: 3px solid var(--primary-color);
            box-shadow: 0 0 20px rgba(5, 45, 226, 1);
            background: rgba(255, 255, 255, 0.7);
            color: white !important;
        }

        .player-card-light.captain-owner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            /* background: linear-gradient(45deg, transparent 40%, rgba(107, 129, 192, 0.14) 50%, transparent 60%); */
            z-index: 1;
        }

        .player-pic-light {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
            object-fit: cover;
            box-shadow: 0 3px 10px rgba(141, 195, 212, 0.4);
        }

        .player-name-light {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 15px 0 5px 0;
        }

        .player-role-light {
            color: var(--secondary-color);
            font-size: 1rem;
            text-transform: capitalize;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .player-role-image-light {
            width: 16px;
            height: 16px;
            object-fit: contain;
            filter: grayscale(100%) brightness(0);
        }

        .player-price-light {
            color: black;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 10px;
        }

        .section-title-light {
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: bold;
            margin: 40px 0 25px 0;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }

        .captain-badge-light {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 2;
        }

        .cricheroes-badge-light {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.4);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #4CAF50;
        }

        .cricheroes-badge-light:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.6);
        }

        .cricheroes-badge-light img {
            width: 20px;
            height: 20px;
            object-fit: contain;
            border-radius: 50%;
        }

        .loading-light {
            text-align: center;
            color: var(--primary-color);
            font-size: 1.5rem;
            padding: 50px;
        }

        .no-data-light {
            text-align: center;
            color: #888;
            font-size: 1.3rem;
            padding: 40px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 10px;
            margin: 20px 0;
        }

        .error-message-light {
            text-align: center;
            color: var(--error-color);
            font-size: 1.3rem;
            padding: 40px;
            background: rgba(255, 107, 107, 0.1);
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid var(--error-color);
        }

        /* Control Panel Styles */
        .control-panel-light {
            background: var(--secondary-bg);
            border: 3px solid var(--secondary-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .search-container-light {
            position: relative;
            margin-bottom: 20px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-input-light {
            background: #eeefe61f;
            border: 2px solid #555;
            border-radius: 10px;
            color: black;
            padding: 12px 45px 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input-light:focus {
            border-color: #94baecff;
            box-shadow: 0 0 15px rgba(149, 166, 187, 1);
            background: rgba(255, 255, 255, 0);
            color: black;
        }

        .search-input-light::placeholder {
            color: #aaa;
        }

        .search-icon-light {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        .toggle-container-light {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .toggle-switch-light {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch-light input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-light {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--primary-color);
            transition: 0.4s;
            border-radius: 34px;A
            border: 2px solid var(--secondary-color);
        }

        .slider-light:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: var(--secondary-color);
            transition: 0.4s;
            border-radius: 50%;
        }

        .toggle-switch-light input:checked+.slider-light {
            background-color: var(--secondary-bg);
        }

        .toggle-switch-light input:checked+.slider-light:before {
            transform: translateX(26px);
        }

        .toggle-label-light {
            color: var(--secondary-color);
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* DataTable Styles */
        .dataTables_wrapper-light {
            color: black !important;
        }

        .table-light-theme {
            --bs-table-bg: var(--secondary-bg);
            --bs-table-striped-bg: #b9dbebff;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            overflow: hidden;
        }

        .table-light-theme th {
            background: var(--secondary-color) !important;
            color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            font-weight: bold;
        }

        .table-light-theme td {
            border-color: #555 !important;
            color: black !important;
            vertical-align: middle;
        }

        .table-light-theme tbody tr:hover {
            background-color: #f2f1e9d3 !important;
        }

        .dataTables_length-light select {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 2px solid #555 !important;
            border-radius: 8px !important;
            color: white !important;
            padding: 5px !important;
        }

        .dataTables_info-light,
        .dataTables_length-light label,
        .dataTables_filter-light label {
            color: black !important;
        }

        .page-link-light {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: #555 !important;
            color: var(--primary-color) !important;
        }

        .page-link-light:hover {
            background-color: var(--primary-color) !important;
            color: black !important;
        }

        .page-item.active .page-link-light {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: black !important;
        }

        .dt-player-pic-light {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
            object-fit: cover;
        }

        .dt-role-badge-light {
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.85rem;
            display: inline-block;
        }

        .dt-captain-badge-light {
            background: #c82333;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 0.75rem;
            margin-left: 8px;
        }

        .dt-cricheroes-link-light {
            color: var(--link-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dt-cricheroes-link-light:hover {
            color: #aaa;
            text-shadow: 0 0 5px rgba(147, 181, 180, 0.5);
        }

        @media (max-width: 768px) {
            .team-name-light {
                font-size: 1.8rem;
            }

            .purse-amount-light {
                font-size: 1.5rem;
            }

            .action-btn-light {
                width: 100%;
                margin: 5px 0;
            }

            .role-count-light {
                font-size: 2rem;
            }

            .toggle-container-light {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body class="light-theme">
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-end align-items-center mb-3">
            <div class="dropdown">
                <button class="profile-dropdown-btn-dark js-theme-class" data-class-base="profile-dropdown-btn"
                    type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark js-theme-class"
                    data-class-base="dropdown-menu" aria-labelledby="profileDropdown">
                    <li>
                        <a id="themeButtonDropdown" class="dropdown-item-dark js-theme-class"
                            data-class-base="dropdown-item" onclick="toggleTheme()">
                            <i class="fas fa-sun me-2" id="themeIcon"></i> <span id="themeText">Toggle Theme</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('/logout') ?>" class="dropdown-item-dark js-theme-class"
                            data-class-base="dropdown-item">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="team-header-dark js-theme-class" data-class-base="team-header">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6">
                    <div class="d-flex align-items-center">
                        <img id="teamLogo" src="<?= base_url('public/assets/AppTeqLogo.png') ?>" alt="Team Logo"
                            class="team-logo-dark me-4 js-theme-class" data-class-base="team-logo">
                        <div>
                            <h1 class="team-name-dark mb-0 js-theme-class" data-class-base="team-name" id="teamName">
                                Loading...</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 text-end">
                    <div class="mt-3 mt-md-0">
                        <h3 class="team-purse-heading-dark mb-2 js-theme-class" data-class-base="team-purse-heading">
                            Team Purse</h3>
                        <div class="purse-amount-dark js-theme-class" data-class-base="purse-amount">₹<span
                                id="teamPurse">0</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <button class="action-btn-dark active js-theme-class" data-class-base="action-btn" id="teamViewBtn"
                onclick="showTeamView()">
                <i class="fas fa-home me-2"></i>Team View
            </button>
            <button class="action-btn-dark js-theme-class" data-class-base="action-btn" id="availablePlayersBtn"
                onclick="showAvailablePlayers()">
                <i class="fas fa-users me-2"></i>Available Players
            </button>
            <button class="action-btn-dark js-theme-class" data-class-base="action-btn" id="unsoldPlayersBtn"
                onclick="showUnsoldPlayers()">
                <i class="fas fa-user-times me-2"></i>Unsold Players
            </button>
        </div>

        <div class="control-panel-dark js-theme-class" data-class-base="control-panel" id="controlPanel">
            <div class="search-container-dark js-theme-class" data-class-base="search-container">
                <input type="text" class="form-control search-input-dark js-theme-class" data-class-base="search-input"
                    id="searchInput" placeholder="Search players...">
                <i class="fas fa-search search-icon-dark js-theme-class" data-class-base="search-icon"></i>
            </div>

            <div class="toggle-container-dark js-theme-class" data-class-base="toggle-container">
                <span class="toggle-label-dark js-theme-class" data-class-base="toggle-label">Card View</span>
                <label class="toggle-switch-dark js-theme-class" data-class-base="toggle-switch">
                    <input type="checkbox" id="viewToggle">
                    <span class="slider-dark js-theme-class" data-class-base="slider"></span>
                </label>
                <span class="toggle-label-dark js-theme-class" data-class-base="toggle-label">Table View</span>
            </div>
        </div>

        <div class="section-title-dark js-theme-class" data-class-base="section-title">Team Composition</div>
        <div class="row" id="roleStats">
            <div class="col-lg-3 col-md-6">
                <div class="role-card-dark js-theme-class" data-class-base="role-card"
                    onclick="filterPlayersByRole('batsman')">
                    <div class="role-header-dark js-theme-class" data-class-base="role-header">
                        <img src="<?= base_url('public/assets/bm_white.png') ?>" alt="Batsman"
                            class="role-image-dark js-theme-class" data-class-base="role-image" id="batsmanImage">
                        <div class="role-count-dark js-theme-class" data-class-base="role-count" id="batsmenCount">0
                        </div>
                    </div>
                    <div class="role-name-dark js-theme-class" data-class-base="role-name">Batsmen</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="role-card-dark js-theme-class" data-class-base="role-card"
                    onclick="filterPlayersByRole('bowler')">
                    <div class="role-header-dark js-theme-class" data-class-base="role-header">
                        <img src="<?= base_url('public/assets/bwl_white.png') ?>" alt="Bowler"
                            class="role-image-dark js-theme-class" data-class-base="role-image" id="bowlerImage">
                        <div class="role-count-dark js-theme-class" data-class-base="role-count" id="bowlersCount">0
                        </div>
                    </div>
                    <div class="role-name-dark js-theme-class" data-class-base="role-name">Bowlers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="role-card-dark js-theme-class" data-class-base="role-card"
                    onclick="filterPlayersByRole('wicketkeeper')">
                    <div class="role-header-dark js-theme-class" data-class-base="role-header">
                        <img src="<?= base_url('public/assets/WK_white.png') ?>" alt="Wicketkeeper"
                            class="role-image-dark js-theme-class" data-class-base="role-image" id="wicketkeeperImage">
                        <div class="role-count-dark js-theme-class" data-class-base="role-count"
                            id="wicketkeepersCount">0</div>
                    </div>
                    <div class="role-name-dark js-theme-class" data-class-base="role-name">WicketKeepers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="role-card-dark js-theme-class" data-class-base="role-card"
                    onclick="filterPlayersByRole('allrounder')">
                    <div class="role-header-dark js-theme-class" data-class-base="role-header">
                        <img src="<?= base_url('public/assets/AllRounders.png') ?>" alt="Allrounder"
                            class="role-image-dark js-theme-class" data-class-base="role-image" id="allrounderImage">
                        <div class="role-count-dark js-theme-class" data-class-base="role-count" id="allroundersCount">0
                        </div>
                    </div>
                    <div class="role-name-dark js-theme-class" data-class-base="role-name">AllRounders</div>
                </div>
            </div>
        </div>

        <div id="teamPlayersContent">
            <div class="section-title-dark js-theme-class" data-class-base="section-title">Team Players</div>
            <div class="row" id="teamPlayers">
                <div class="loading-dark js-theme-class" data-class-base="loading">
                    <i class="fas fa-spinner fa-spin me-2"></i>Loading team players...
                </div>
            </div>

            <div id="teamPlayersTable" class="d-none">
                <table class="table table-dark table-striped table-dark-theme js-theme-class" data-class-base="table"
                    id="teamPlayersDataTable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Price</th>
                            <th>CricHeroes Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbod>
                </table>
            </div>
        </div>

        <div id="playerListContent" class="d-none">
            <div class="section-title-dark js-theme-class" data-class-base="section-title" id="playerListTitle">
                Available Players</div>

            <div class="row" id="playerList">
            </div>

            <div id="playerTable" class="d-none">
                <table class="table table-dark table-striped table-dark-theme js-theme-class" data-class-base="table"
                    id="playersDataTable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Price</th>
                            <th>CricHeroes Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Configuration
        const baseUrl = '<?= base_url() ?>';
        let teamData = {};
        let teamPlayers = [];
        let allAvailablePlayers = [];
        let allUnsoldPlayers = [];
        let currentView = 'team';
        let currentPlayers = [];
        let dataTable = null;
        let teamPlayersDataTable = null;
        let currentPriceField = 'final_price';
        let searchTerm = '';

        // Theme Management
        let currentTheme = 'light'; // MODIFIED: Default theme is now 'light'
        const themeClassMap = {
            'dark': {
                suffix: '-dark',
                bodyClass: 'dark-theme',
                dataTableClass: 'table-dark-theme',
                icon: 'fa-sun', // The icon to display when in DARK mode (to suggest switching to LIGHT)
                themeText: 'Theme'
            },
            'light': {
                suffix: '-light',
                bodyClass: 'light-theme',
                dataTableClass: 'table-light-theme',
                icon: 'fa-moon', // The icon to display when in LIGHT mode (to suggest switching to DARK)
                themeText: 'Theme'
            }
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, starting data load...');
            // Start with Card View (toggle unchecked)
            document.getElementById('viewToggle').checked = false;

            // Initialize theme to light and apply classes
            applyThemeClasses('light'); // MODIFIED: Apply 'light' theme classes on load

            loadTeamData(true);
            initializeEventListeners();

            // Set up interval for refreshing data every 1.25 seconds
            setInterval(refreshPageContents, 1250);
        });

        // Toggle Theme Function
        function toggleTheme() {
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            console.log('Theme toggled to:', newTheme);
            applyThemeClasses(newTheme);
            updateCurrentViewDisplay(true); // Re-render to apply table/card theme classes

            // Re-apply active button style after theme switch
            const activeBtn = document.querySelector('.action-btn' + themeClassMap[newTheme].suffix + '.active');
            if (activeBtn) {
                const activeId = activeBtn.id;
                updateActiveButton(activeId);
            }
        }

        // Apply theme-specific classes to all elements tagged with js-theme-class
        function applyThemeClasses(theme) {
            const body = document.body;
            const newTheme = themeClassMap[theme];
            const oldTheme = theme === 'dark' ? themeClassMap['light'] : themeClassMap['dark'];
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');

            // 1. Update body class
            body.classList.remove(oldTheme.bodyClass);
            body.classList.add(newTheme.bodyClass);

            // 2. Enable/Disable Style Tags
            document.getElementById('darkThemeStyles').disabled = (theme !== 'dark');
            document.getElementById('lightThemeStyles').disabled = (theme !== 'light');

            // 3. Update all elements with the generic class switch
            document.querySelectorAll('.js-theme-class').forEach(el => {
                const base = el.getAttribute('data-class-base');
                if (base) {
                    el.classList.remove(base + oldTheme.suffix);
                    el.classList.add(base + newTheme.suffix);

                    // Special case for action buttons: retain 'active' state
                    if (base === 'action-btn') {
                        const isActive = el.classList.contains('active');
                        // Must remove old class first, then add new one
                        el.classList.remove(base + oldTheme.suffix);
                        el.classList.add(base + newTheme.suffix);
                        if (isActive) {
                            el.classList.add('active'); // Re-apply active class
                        }
                    }
                }
            });

            // 4. Update Theme Icon and Text
            if (themeIcon) {
                themeIcon.classList.remove(themeClassMap['dark'].icon);
                themeIcon.classList.remove(themeClassMap['light'].icon);
                themeIcon.classList.add(newTheme.icon);
            }
            if (themeText) {
                // The text should suggest the theme you are switching *to* // but the icon shows the one you *can* switch to.
                // Let's keep the icon logic simple: if dark, show sun (switch to light).
                themeText.textContent = theme === 'dark' ? 'Light Theme' : 'Dark Theme';
            }

            currentTheme = theme;
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Toggle switch - ensure immediate response
            const viewToggle = document.getElementById('viewToggle');
            viewToggle.addEventListener('change', function() {
                console.log('View Toggle changed to:', this.checked ? 'Table' : 'Card');
                updateCurrentViewDisplay();
            });

            // Search input - store search term and apply immediately
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                searchTerm = this.value.trim();
                console.log('Search input changed:', searchTerm);
                handleSearch(searchTerm);
            });
        }

        // Load team data from backend
        async function loadTeamData(initialLoad = false) {
            try {
                const teamDataResponse = await fetch(`${baseUrl}/team/getTeamData`);
                if (!teamDataResponse.ok) throw new Error(`HTTP error! status: ${teamDataResponse.status}`);
                const teamDataResult = await teamDataResponse.json();

                if (teamDataResult.success) {
                    teamData = teamDataResult.team;
                    displayTeamData();

                    if (initialLoad) {
                        await loadTeamPlayers();
                    }
                } else {
                    console.error('Team data error:', teamDataResult.error);
                    if (initialLoad) showError(teamDataResult.error || 'Failed to load team data');
                }
            } catch (error) {
                console.error('Error loading team data:', error);
                if (initialLoad) showError('Failed to connect to server. Please check your connection.');
            }
        }

        // Load team players from backend
        async function loadTeamPlayers(silent = false) {
            try {
                const response = await fetch(`${baseUrl}/team/getTeamPlayers`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();

                if (result.success) {
                    const previousCount = teamPlayers.length;
                    teamPlayers = result.players || [];

                    if (!silent) {
                        if (currentView === 'team') {
                            currentPlayers = teamPlayers;
                            updateCurrentViewDisplay();
                        }
                        updateRoleStats();

                        // Log changes for debugging
                        if (teamPlayers.length !== previousCount) {
                            console.log(`Team players updated: ${previousCount} -> ${teamPlayers.length}`);
                        }
                    }
                } else {
                    console.error('Team players error:', result.error);
                    if (!silent) showError(result.error || 'Failed to load team players');
                }
            } catch (error) {
                console.error('Error loading team players:', error);
                if (!silent) showError('Failed to load team players');
            }
        }

        // Function to load Available Players
        async function loadAvailablePlayers() {
            try {
                const response = await fetch(`${baseUrl}/team/getAvailablePlayersForTeam`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();

                if (result.success) {
                    allAvailablePlayers = result.players || [];
                } else {
                    console.error('Available players error:', result.error);
                }
            } catch (error) {
                console.error('Error loading available players:', error);
            }
        }

        // Function to load Unsold Players
        async function loadUnsoldPlayers() {
            try {
                const response = await fetch(`${baseUrl}/team/getUnsoldPlayersForTeam`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();

                if (result.success) {
                    allUnsoldPlayers = result.players || [];
                } else {
                    console.error('Unsold players error:', result.error);
                }
            } catch (error) {
                console.error('Error loading unsold players:', error);
            }
        }

        // Polling function: Refreshes Team Data and Page Contents
        async function refreshPageContents() {
            try {
                // Always refresh team header and purse
                await loadTeamData(false);

                // Refresh team players and stats
                await loadTeamPlayers(true); // silent refresh of teamPlayers array
                updateRoleStats(); // update stats based on new teamPlayers array

                // Silent refresh of other lists
                if (currentView === 'available' || currentView === 'unsold' || currentView === 'filtered') {
                    // Only refresh the list that is currently viewed to save bandwidth
                    if (currentView === 'available') {
                        await loadAvailablePlayers();
                    } else if (currentView === 'unsold') {
                        await loadUnsoldPlayers();
                    }
                }

                // Update display only if we're on the view that was refreshed/updated
                if (currentView === 'team') {
                    currentPlayers = teamPlayers;
                } else if (currentView === 'available') {
                    currentPlayers = allAvailablePlayers;
                } else if (currentView === 'unsold') {
                    currentPlayers = allUnsoldPlayers;
                }

                // For 'filtered' view, we don't need to re-filter here,
                // as the underlying teamPlayers array will update its display in the next call.

                updateCurrentViewDisplay(true); // silent update display

            } catch (error) {
                console.error('Error during refresh:', error);
            }
        }

        // Display team data (Header)
        function displayTeamData() {
            const teamNameElement = document.getElementById('teamName');
            const teamLogoElement = document.getElementById('teamLogo');
            const teamPurseElement = document.getElementById('teamPurse');

            if (teamNameElement && teamData.team_name) {
                teamNameElement.textContent = teamData.team_name;
            }

            if (teamLogoElement && teamData.team_logo_url) {
                teamLogoElement.src = teamData.team_logo_url;
                teamLogoElement.alt = teamData.team_name + ' Logo';
            }

            if (teamPurseElement && teamData.purse !== undefined) {
                teamPurseElement.textContent = formatPrice(teamData.purse);
            }
        }

        // Update role statistics
        function updateRoleStats() {
            const stats = {
                batsman: 0,
                bowler: 0,
                wicketkeeper: 0,
                allrounder: 0
            };

            teamPlayers.forEach(player => {
                if (stats.hasOwnProperty(player.player_role)) {
                    stats[player.player_role]++;
                }
            });

            document.getElementById('batsmenCount').textContent = stats.batsman;
            document.getElementById('bowlersCount').textContent = stats.bowler;
            document.getElementById('wicketkeepersCount').textContent = stats.wicketkeeper;
            document.getElementById('allroundersCount').textContent = stats.allrounder;
        }

        // Show Team View
        function showTeamView() {
            currentView = 'team';
            currentPlayers = teamPlayers;
            currentPriceField = 'final_price';
            updateActiveButton('teamViewBtn');

            document.getElementById('teamPlayersContent').classList.remove('d-none');
            document.getElementById('playerListContent').classList.add('d-none');

            updateCurrentViewDisplay();
        }

        // Show Available Players
        async function showAvailablePlayers() {
            document.getElementById('playerListTitle').textContent = 'Loading Available Players...';
            // Ensure players are loaded on button click if not loaded already
            if (allAvailablePlayers.length === 0) {
                await loadAvailablePlayers();
                if (allAvailablePlayers.length === 0) {
                    document.getElementById('playerListTitle').textContent = 'Available Players';
                }
            }

            currentView = 'available';
            currentPlayers = allAvailablePlayers;
            currentPriceField = 'base_price';
            updateActiveButton('availablePlayersBtn');

            document.getElementById('teamPlayersContent').classList.add('d-none');
            document.getElementById('playerListContent').classList.remove('d-none');
            document.getElementById('playerListTitle').textContent = 'Available Players';

            updateCurrentViewDisplay();
        }

        // Show Unsold Players
        async function showUnsoldPlayers() {
            document.getElementById('playerListTitle').textContent = 'Loading Unsold Players...';
            // Ensure players are loaded on button click if not loaded already
            if (allUnsoldPlayers.length === 0) {
                await loadUnsoldPlayers();
                if (allUnsoldPlayers.length === 0) {
                    document.getElementById('playerListTitle').textContent = 'Unsold Players';
                }
            }

            currentView = 'unsold';
            currentPlayers = allUnsoldPlayers;
            currentPriceField = 'base_price';
            updateActiveButton('unsoldPlayersBtn');

            document.getElementById('teamPlayersContent').classList.add('d-none');
            document.getElementById('playerListContent').classList.remove('d-none');
            document.getElementById('playerListTitle').textContent = 'Unsold Players';

            updateCurrentViewDisplay();
        }

        // Filter players by role
        function filterPlayersByRole(role) {
            const filteredPlayers = teamPlayers.filter(player => player.player_role === role);

            currentView = 'filtered';
            currentPlayers = filteredPlayers;
            currentPriceField = 'final_price';

            document.getElementById('teamPlayersContent').classList.add('d-none');
            document.getElementById('playerListContent').classList.remove('d-none');
            document.getElementById('playerListTitle').textContent = `Team ${role.charAt(0).toUpperCase() + role.slice(1)}s`;

            updateCurrentViewDisplay();
        }

        // Updates the display for the current view based on toggle state
        function updateCurrentViewDisplay(silent = false) {
            const isTableView = document.getElementById('viewToggle').checked;

            // Re-initialize tables only if we switch view OR if it's a silent refresh
            // but the table is currently visible.

            if (currentView === 'team') {
                if (!silent) {
                    // Destroy existing DataTables on view switch
                    if (teamPlayersDataTable) {
                        teamPlayersDataTable.destroy();
                        teamPlayersDataTable = null;
                    }
                }

                // Show/hide appropriate containers
                document.getElementById('teamPlayersTable').classList.toggle('d-none', !isTableView);
                document.getElementById('teamPlayers').classList.toggle('d-none', isTableView);

                if (isTableView) {
                    initializeTeamPlayersDataTable();
                } else {
                    displayTeamPlayersCards();
                }
            } else { // available, unsold, or filtered
                if (!silent) {
                    // Destroy existing DataTables on view switch
                    if (dataTable) {
                        dataTable.destroy();
                        dataTable = null;
                    }
                }

                // Show/hide appropriate containers
                document.getElementById('playerTable').classList.toggle('d-none', !isTableView);
                document.getElementById('playerList').classList.toggle('d-none', isTableView);

                if (isTableView) {
                    initializePlayersDataTable();
                } else {
                    displayPlayerCards();
                }
            }

            // Apply search if there's a search term
            if (searchTerm) {
                handleSearch(searchTerm);
            }
        }

        // Display team players in card format
        function displayTeamPlayersCards() {
            const container = document.getElementById('teamPlayers');
            const playersToDisplay = currentPlayers || [];
            const theme = currentTheme;
            const cardClass = `player-card-${theme}`;
            const badgeClass = `cricheroes-badge-${theme}`;
            const picClass = `player-pic-${theme}`;
            const nameClass = `player-name-${theme}`;
            const roleClass = `player-role-${theme}`;
            const roleImageClass = `player-role-image-${theme}`;
            const priceClass = `player-price-${theme}`;
            const captainBadgeClass = `captain-badge-${theme}`;
            const noDataClass = `no-data-${theme}`;

            if (playersToDisplay.length === 0) {
                container.innerHTML = `<div class="${noDataClass}">No players in this category yet.</div>`;
                return;
            }

            let html = '';
            // Apply card view search filter here
            const filteredCards = playersToDisplay.filter(player =>
                !searchTerm ||
                player.player_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                player.player_role.toLowerCase().includes(searchTerm.toLowerCase())
            );

            if (filteredCards.length === 0 && searchTerm) {
                container.innerHTML = `<div class="${noDataClass}">No matching players found.</div>`;
                return;
            } else if (filteredCards.length === 0) {
                container.innerHTML = `<div class="${noDataClass}">No players in this category yet.</div>`;
                return;
            }

            filteredCards.forEach(player => {
                const hasCricHeroesUrl = player.cricheroes_url && player.cricheroes_url.trim() !== '';
                let captainOwnerClass = '';
                let captainOwnerBadge = '';

                // Captain/Owner logic for team players
                if (currentView !== 'available' && currentView !== 'unsold' && parseInt(player.is_cap) === 1) {
                    captainOwnerClass = 'captain-owner';
                    if (teamData.captain_player_id && parseInt(player.player_id) === parseInt(teamData.captain_player_id) &&
                        teamData.owner_player_id && parseInt(player.player_id) === parseInt(teamData.owner_player_id)) {
                        captainOwnerBadge = `<div class="${captainBadgeClass}">Captain & Owner</div>`;
                    } else if (teamData.captain_player_id && parseInt(player.player_id) === parseInt(teamData.captain_player_id)) {
                        captainOwnerBadge = `<div class="${captainBadgeClass}">Captain</div>`;
                    } else if (teamData.owner_player_id && parseInt(player.player_id) === parseInt(teamData.owner_player_id)) {
                        captainOwnerBadge = `<div class="${captainBadgeClass}">Owner</div>`;
                    } else {
                        captainOwnerBadge = `<div class="${captainBadgeClass}">Captain/Owner</div>`;
                    }
                }

                const priceToShow = player[currentPriceField] || player.final_price || player.base_price || 0;

                html += `
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="${cardClass} ${captainOwnerClass}">
                            ${captainOwnerBadge}
                            ${hasCricHeroesUrl ? `
                                <div class="${badgeClass}" onclick="window.open('${player.cricheroes_url}', '_blank')" title="Click to view CricHeroes profile">
                                    <img src="${baseUrl}/public/assets/cricheroes-logo.webp" alt="CricHeroes">
                                </div>
                            ` : ''}
                            <div class="text-center">
                                <img src="${player.profile_pic_url || baseUrl + '/public/assets/default-player.png'}" alt="${player.player_name}" class="${picClass}">
                                <div class="${nameClass}">${player.player_name}</div>
                                <div class="${roleClass}">
                                    <img src="${getRoleImageSrc(player.player_role)}" alt="${player.player_role}" class="${roleImageClass}">
                                    ${player.player_role}
                                </div>
                                <div class="${priceClass}">₹${formatPrice(priceToShow)}</div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Display other players in card format
        function displayPlayerCards() {
            const container = document.getElementById('playerList');
            const playersToDisplay = currentPlayers || [];
            const theme = currentTheme;
            const cardClass = `player-card-${theme}`;
            const badgeClass = `cricheroes-badge-${theme}`;
            const picClass = `player-pic-${theme}`;
            const nameClass = `player-name-${theme}`;
            const roleClass = `player-role-${theme}`;
            const roleImageClass = `player-role-image-${theme}`;
            const priceClass = `player-price-${theme}`;
            const noDataClass = `no-data-${theme}`;

            if (playersToDisplay.length === 0) {
                container.innerHTML = `<div class="${noDataClass}">No players found.</div>`;
                return;
            }

            let html = '';
            // Apply card view search filter here
            const filteredCards = playersToDisplay.filter(player =>
                !searchTerm ||
                player.player_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                player.player_role.toLowerCase().includes(searchTerm.toLowerCase())
            );

            if (filteredCards.length === 0 && searchTerm) {
                container.innerHTML = `<div class="${noDataClass}">No matching players found.</div>`;
                return;
            } else if (filteredCards.length === 0) {
                container.innerHTML = `<div class="${noDataClass}">No players found.</div>`;
                return;
            }

            filteredCards.forEach(player => {
                const hasCricHeroesUrl = player.cricheroes_url && player.cricheroes_url.trim() !== '';
                const priceToShow = player[currentPriceField] || player.base_price || 0;

                html += `
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="${cardClass}">
                            ${hasCricHeroesUrl ? `
                                <div class="${badgeClass}" onclick="window.open('${player.cricheroes_url}', '_blank')" title="Click to view CricHeroes profile">
                                    <img src="${baseUrl}/public/assets/cricheroes-logo.webp" alt="CricHeroes">
                                </div>
                            ` : ''}
                            <div class="text-center">
                                <img src="${player.profile_pic_url || baseUrl + '/public/assets/default-player.png'}" alt="${player.player_name}" class="${picClass}">
                                <div class="${nameClass}">${player.player_name}</div>
                                <div class="${roleClass}">
                                    <img src="${getRoleImageSrc(player.player_role)}" alt="${player.player_role}" class="${roleImageClass}">
                                    ${player.player_role}
                                </div>
                                <div class="${priceClass}">₹${formatPrice(priceToShow)}</div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Initialize DataTable for Team Players
        function initializeTeamPlayersDataTable() {
            const tableBody = document.querySelector('#teamPlayersDataTable tbody');
            const theme = currentTheme;
            const picClass = `dt-player-pic-${theme}`;
            const roleBadgeClass = `dt-role-badge-${theme}`;
            const captainBadgeClass = `dt-captain-badge-${theme}`;
            const linkClass = `dt-cricheroes-link-${theme}`;
            const tableClass = themeClassMap[theme].dataTableClass;

            // Apply theme class to the table element itself
            $('#teamPlayersDataTable').removeClass(themeClassMap['dark'].dataTableClass).removeClass(themeClassMap['light'].dataTableClass).addClass(tableClass);

            let html = '';

            (currentPlayers || []).forEach(player => {
                const hasCricHeroesUrl = player.cricheroes_url && player.cricheroes_url.trim() !== '';
                let roleBadge = '';

                if (parseInt(player.is_cap) === 1) {
                    if (teamData.captain_player_id && parseInt(player.player_id) === parseInt(teamData.captain_player_id) &&
                        teamData.owner_player_id && parseInt(player.player_id) === parseInt(teamData.owner_player_id)) {
                        roleBadge = `<span class="${captainBadgeClass}">Captain & Owner</span>`;
                    } else if (teamData.captain_player_id && parseInt(player.player_id) === parseInt(teamData.captain_player_id)) {
                        roleBadge = `<span class="${captainBadgeClass}">Captain</span>`;
                    } else if (teamData.owner_player_id && parseInt(player.player_id) === parseInt(teamData.owner_player_id)) {
                        roleBadge = `<span class="${captainBadgeClass}">Owner</span>`;
                    } else {
                        roleBadge = `<span class="${captainBadgeClass}">C/O</span>`;
                    }
                }

                const priceToShow = player[currentPriceField] || player.final_price || player.base_price || 0;

                html += `
                    <tr>
                        <td>
                            <img src="${player.profile_pic_url || baseUrl + '/public/assets/default-player.png'}" alt="${player.player_name}" class="${picClass}">
                        </td>
                        <td>
                            ${player.player_name}
                            ${roleBadge}
                        </td>
                        <td>
                            <span class="${roleBadgeClass}">${player.player_role}</span>
                        </td>
                        <td>₹${formatPrice(priceToShow)}</td>
                        <td>
                            ${hasCricHeroesUrl ?
                        `<a href="${player.cricheroes_url}" target="_blank" class="${linkClass}">
                                    <i class="fas fa-external-link-alt"></i> CricHeroes
                                </a>` :
                        '<span class="text-muted">-</span>'}
                        </td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;

            if (teamPlayersDataTable) {
                // If it exists, destroy and re-initialize to ensure theme is applied
                teamPlayersDataTable.destroy();
                teamPlayersDataTable = null;
            }

            teamPlayersDataTable = $('#teamPlayersDataTable').DataTable({
                pageLength: 10,
                responsive: true,
                searching: true,
                dom: 'lrtip',
                language: {
                    emptyTable: "No players found",
                    zeroRecords: "No matching players found"
                }
            });

            // Apply search filter if there is one
            teamPlayersDataTable.search(searchTerm).draw();
        }

        // Initialize DataTable for other players
        function initializePlayersDataTable() {
            const tableBody = document.querySelector('#playersDataTable tbody');
            const theme = currentTheme;
            const picClass = `dt-player-pic-${theme}`;
            const roleBadgeClass = `dt-role-badge-${theme}`;
            const linkClass = `dt-cricheroes-link-${theme}`;
            const tableClass = themeClassMap[theme].dataTableClass;

            // Apply theme class to the table element itself
            $('#playersDataTable').removeClass(themeClassMap['dark'].dataTableClass).removeClass(themeClassMap['light'].dataTableClass).addClass(tableClass);

            let html = '';

            (currentPlayers || []).forEach(player => {
                const hasCricHeroesUrl = player.cricheroes_url && player.cricheroes_url.trim() !== '';
                const priceToShow = player[currentPriceField] || player.base_price || 0;

                html += `
                    <tr>
                        <td>
                            <img src="${player.profile_pic_url || baseUrl + '/public/assets/default-player.png'}" alt="${player.player_name}" class="${picClass}">
                        </td>
                        <td>${player.player_name}</td>
                        <td>
                            <span class="${roleBadgeClass}">${player.player_role}</span>
                        </td>
                        <td>₹${formatPrice(priceToShow)}</td>
                        <td>
                            ${hasCricHeroesUrl ?
                        `<a href="${player.cricheroes_url}" target="_blank" class="${linkClass}">
                                    <i class="fas fa-external-link-alt"></i> CricHeroes
                                </a>` :
                        '<span class="text-muted">-</span>'}
                        </td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;

            if (dataTable) {
                // If it exists, destroy and re-initialize to ensure theme is applied
                dataTable.destroy();
                dataTable = null;
            }

            dataTable = $('#playersDataTable').DataTable({
                pageLength: 10,
                responsive: true,
                searching: true,
                dom: 'lrtip',
                language: {
                    emptyTable: "No players found",
                    zeroRecords: "No matching players found"
                }
            });

            // Apply search filter if there is one
            dataTable.search(searchTerm).draw();
        }

        // Handle search functionality
        function handleSearch(searchTerm) {
            const isTableView = document.getElementById('viewToggle').checked;

            if (isTableView) {
                // Apply search to active DataTable
                const activeTable = currentView === 'team' ? teamPlayersDataTable : dataTable;
                if (activeTable) {
                    // Manually trigger the search and redraw
                    activeTable.search(searchTerm).draw();
                }
            } else {
                // Filter and redraw cards based on search term
                if (currentView === 'team' || currentView === 'filtered') {
                    displayTeamPlayersCards();
                } else {
                    displayPlayerCards();
                }
            }
        }

        // Update active button
        function updateActiveButton(activeId) {
            const themeSuffix = themeClassMap[currentTheme].suffix;
            document.querySelectorAll('.action-btn' + themeSuffix).forEach(btn => btn.classList.remove('active'));
            document.getElementById(activeId).classList.add('active');
        }

        // Get role image source
        function getRoleImageSrc(role) {
            const roleImages = {
                batsman: '<?= base_url('public/assets/bm_white.png') ?>',
                bowler: '<?= base_url('public/assets/bwl_white.png') ?>',
                wicketkeeper: '<?= base_url('public/assets/WK_white.png') ?>',
                allrounder: '<?= base_url('public/assets/AllRounders.png') ?>'
            };
            return roleImages[role] || '';
        }

        // Format price
        function formatPrice(price) {
            if (price === null || price === undefined || isNaN(parseInt(price))) return '0';

            price = parseInt(price);
            if (price >= 10000000) {
                return (price / 10000000).toFixed(1) + ' Cr';
            } else if (price >= 100000) {
                return (price / 100000).toFixed(1) + ' L';
            } else if (price >= 1000) {
                return (price / 1000).toFixed(1) + ' K';
            }
            return price.toString();
        }

        // Show error message
        function showError(message) {
            const theme = currentTheme;
            const errorClass = `error-message-${theme}`;
            const errorHtml = `<div class="${errorClass}"><i class="fas fa-exclamation-triangle me-2"></i>${message}</div>`;

            if (!document.getElementById('teamPlayersContent').classList.contains('d-none')) {
                document.getElementById('teamPlayers').classList.remove('d-none');
                document.getElementById('teamPlayersTable').classList.add('d-none');
                document.getElementById('teamPlayers').innerHTML = errorHtml;
            } else {
                document.getElementById('playerListContent').classList.remove('d-none');
                document.getElementById('playerListTitle').textContent = 'Error';
                document.getElementById('playerList').classList.remove('d-none');
                document.getElementById('playerTable').classList.add('d-none');
                document.getElementById('playerList').innerHTML = errorHtml;
            }
        }
    </script>
</body>

</html>