<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Auction Display</title>
    <link rel="icon" href="<?= base_url('public/assets/AppTeqLogo.png') ?>" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #000000 100%);
            color: #ffffff;
            overflow: auto;
            height: 100vh;
            position: relative;
        }

        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
            }

            100% {
                transform: translateY(-100px) rotate(360deg);
            }
        }

        .main-container {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .header {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(90deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 215, 0, 0.05) 50%, rgba(255, 215, 0, 0.1) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(255, 215, 0, 0.3);
            padding: 45px;
            z-index: 20;
            gap: 15px;
            width: 100%;
        }

        .header-logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            background: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0);
            margin-bottom: 0 !important;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(45deg, #ffd700, #ffed4e, #ffd700);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: goldShimmer 3s ease-in-out infinite;
            text-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
        }

        /* Rounds Display */
        .rounds-display {
            position: absolute;
            top: 17px;
            left: 20px;
            background: linear-gradient(145deg, rgba(255, 215, 0, 0.15), rgba(255, 215, 0, 0.08));
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 215, 0, 0.4);
            border-radius: 20px;
            padding: 20px;
            min-width: 280px;
            box-shadow: 0 8px 32px rgba(255, 215, 0, 0.2);
            z-index: 25;
        }

        .round-info {
            text-align: center;
            margin-bottom: 15px;
        }

        .round-title {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        /* .round-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 215, 0, 0.8);
            font-weight: 500;
        } */

        .progress-container {
            margin-top: 15px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .progress-text {
            font-weight: 600;
        }

        .progress-count {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .progress-bar-container {
            width: 100%;
            height: 12px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
            background-size: 200% 100%;
            border-radius: 6px;
            transition: width 0.5s ease-in-out;
            animation: progressShimmer 2s ease-in-out infinite;
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: progressGlow 2s ease-in-out infinite;
        }

        /* Picture Display (Right Side) */
        .picture-display {
            position: absolute;
            top: 45px;
            right: 0px;
            /* background: linear-gradient(145deg, rgba(255, 215, 0, 0.15), rgba(255, 215, 0, 0.08)); */
            /* backdrop-filter: blur(15px); */
            /* border: 2px solid rgba(255, 215, 0, 0.4); */
            border-radius: 20px;
            /* padding: 20px; */
            min-width: 280px;
            /* box-shadow: 0 8px 32px rgba(255, 215, 0, 0.2); */
            z-index: 25;
            text-align: center;
        }

        .picture-container {
            width: 180px;
            height: 90px;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            background: transparent;
        }

        .picture-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 13px;
        }

        @keyframes progressShimmer {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes progressGlow {

            0%,
            100% {
                transform: translateX(-100%);
            }

            50% {
                transform: translateX(100%);
            }
        }

        @keyframes goldShimmer {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .waiting-state {
            text-align: center;
            opacity: 0;
            animation: fadeIn 1s ease-in-out forwards;
        }

        .waiting-state h2 {
            font-size: 2.5rem;
            color: rgba(255, 215, 0, 0.8);
            margin-bottom: 20px;
            font-weight: 300;
        }

        .waiting-state .spinner {
            width: 80px;
            height: 80px;
            border: 4px solid rgba(255, 215, 0, 0.2);
            border-top: 4px solid #ffd700;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .current_player_details {
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .player-display {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: start;
            gap: 150px;
            max-width: 1300px;
            width: 100%;
            opacity: 0;
            transform: translateY(50px);
            animation: slideUp 0.8s ease-out forwards;
        }

        .player-left,
        .player-right {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .player-left {
            align-items: center;
        }

        .player-right {
            justify-content: flex-start;
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .player-image-container {
            position: relative;
            display: inline-block;
        }

        .player-image {
            width: 350px;
            height: 450px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            background: linear-gradient(45deg, #ffd700, #ffed4e, #ffd700);
            background-clip: padding-box;
            box-shadow:
                0 0 50px rgba(255, 215, 0, 0.4),
                0 0 100px rgba(255, 215, 0, 0.2),
                inset 0 0 50px rgba(255, 215, 0, 0.1);
            animation: goldGlow 4s ease-in-out infinite;
        }

        @keyframes goldGlow {

            0%,
            100% {
                box-shadow:
                    0 0 50px rgba(255, 215, 0, 0.4),
                    0 0 100px rgba(255, 215, 0, 0.2),
                    inset 0 0 50px rgba(255, 215, 0, 0.1);
            }

            50% {
                box-shadow:
                    0 0 80px rgba(255, 215, 0, 0.6),
                    0 0 150px rgba(255, 215, 0, 0.3),
                    inset 0 0 80px rgba(255, 215, 0, 0.2);
            }
        }

        .player-name {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
            text-align:center;
        }

        .player-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .detail-card {
            background: linear-gradient(145deg, rgba(255, 215, 0, 0.1), rgba(255, 215, 0, 0.05));
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 20px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.5);
        }

        .detail-label {
            font-size: 1rem;
            color: rgba(255, 215, 0, 0.7);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .detail-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
            text-transform: capitalize;
        }

        .base-price {
            font-size: 1.5rem !important;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        .status-display {
            margin-top: 40px;
            padding: 30px;
            border-radius: 25px;
            text-align: center;
            display: none;
        }

        .status-sold {
            background: linear-gradient(145deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1));
            border: 2px solid rgba(34, 197, 94, 0.5);
            box-shadow: 0 0 50px rgba(34, 197, 94, 0.2);
        }

        .status-unsold {
            background: linear-gradient(145deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            border: 2px solid rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 50px rgba(239, 68, 68, 0.2);
        }

        .status-label {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .status-sold .status-label {
            color: #22c55e;
            text-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
        }

        .status-unsold .status-label {
            color: #ef4444;
            text-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
        }

        .team-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }

        .team-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 215, 0, 0.5);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }

        .team-name {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
        }

        .final-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #22c55e;
            margin-top: 15px;
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .rounds-display {
                position: relative;
                top: auto;
                left: auto;
                margin: 10px auto;
                min-width: auto;
                width: 90%;
            }

            .picture-display {
                position: relative;
                top: auto;
                right: auto;
                margin: 10px auto;
                min-width: auto;
                width: 90%;
            }

            .header {
                flex-direction: column;
                gap: 10px;
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .player-name {
                font-size: 2.5rem;
            }

            .player-image {
                width: 200px;
                height: 200px;
            }

            .player-details {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .detail-value {
                font-size: 1.2rem;
            }

            .base-price {
                font-size: 2rem !important;
            }

            .status-label {
                font-size: 2rem;
            }

            .player-display {
                grid-template-columns: 1fr;
            }

            .player-left,
            .player-right {
                align-items: center;
            }
        }

        /* Loading animation for image */
        .image-loading {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255, 215, 0, 0.1), rgba(255, 215, 0, 0.3));
            display: flex;
            align-items: center;
            justify-content: center;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.5;
            }
        }

        /* SOLD Badge Overlay Styles */
        .sold-overlay {
            position: absolute;
            top: 20px;
            left: 100px;
            opacity: 0;
            transform: translateX(100px) rotate(20deg);
            transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 10;
        }

        .sold-overlay.show {
            opacity: 1;
            transform: translateX(0) rotate(-15deg);
        }

        .sold-ribbon {
            position: relative;
            width: 98px;
            height: 50px;
            background: #0ea545;
            border-radius: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 6px solid #22c55e;
            box-shadow: 0 0 0 3px #22c55e, 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 2px 0 rgba(255, 255, 255, 0.1);
        }

        .sold-ribbon:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 30px;
            background: #34495e;
            border-radius: 15px;
            z-index: -1;
        }

        .sold-text {
            font-size: 1.2rem;
            font-weight: 900;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            position: relative;
            z-index: 2;
            animation: soldPulse 2s ease-in-out infinite alternate;
        }

        @keyframes soldPulse {
            from {
                transform: scale(1);
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            }

            to {
                transform: scale(1.05);
                text-shadow:
                    2px 2px 4px rgba(0, 0, 0, 0.8),
                    0 0 10px rgba(255, 255, 255, 0.3);
            }
        }

        /* UNSOLD Badge Overlay Styles */
        .unsold-overlay {
            position: absolute;
            top: 20px;
            left: 90px;
            opacity: 0;
            transform: translateX(100px) rotate(20deg);
            transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 10;
        }

        .unsold-overlay.show {
            opacity: 1;
            transform: translateX(0) rotate(-15deg);
        }

        .unsold-ribbon {
            position: relative;
            width: 120px;
            height: 50px;
            background: #c0392b;
            border-radius: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 6px solid #e74c3c;
            box-shadow: 0 0 0 3px #c0392b, 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 2px 0 rgba(255, 255, 255, 0.1);
        }

        .unsold-ribbon:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 30px;
            background: #e74c3c;
            border-radius: 15px;
            z-index: -1;
        }

        .unsold-text {
            font-size: 1.2rem;
            font-weight: 900;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            position: relative;
            z-index: 2;
            animation: unsoldPulse 2s ease-in-out infinite alternate;
        }

        @keyframes unsoldPulse {
            from {
                transform: scale(1);
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            }

            to {
                transform: scale(1.05);
                text-shadow:
                    2px 2px 4px rgba(0, 0, 0, 0.8),
                    0 0 10px rgba(255, 255, 255, 0.3);
            }
        }
    </style>
</head>

<body>

    <div class="particles" id="particles"></div>

    <!-- Header -->
    <div class="header">
        <!-- Rounds Display -->
        <div class="rounds-display" id="rounds-display">
            <div class="round-info">
                <div class="round-title" id="round-title">Round 1</div>
                <!-- <div class="round-subtitle">Auction Progress</div> -->
            </div>
            <div class="progress-container">
                <div class="progress-label">
                    <span class="progress-text">Players Auctioned</span>
                    <span class="progress-count" id="progress-count">0 / 0</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progress-bar" style="width: 0%;"></div>
                </div>
            </div>
        </div>

        <!-- Picture Display -->
        <div class="picture-display" id="picture-display">
            <div class="picture-container">
                <img src="<?= base_url('public/assets/appteq_new_logo_w.png') ?>" alt="Auction Picture"
                    id="auction-picture">
            </div>
        </div>

        <?php
        $event_id = session()->get('event_id');
        $eventModel = new \App\Models\EventModel();
        $event = $eventModel->find($event_id);
        $event_logo = $event && $event['event_logo'] ? base_url('uploads/events/' . $event['event_logo']) : base_url('public/assets/bnilogo1.png');
        ?>
        <img src="<?= $event_logo ?>" alt="Logo" class="header-logo mb-2">
        <h1><i></i> LIVE AUCTION</h1>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Waiting State -->
        <div class="waiting-state" id="waiting-state">
            <h2>Waiting for Player Selection...</h2>
            <div class="spinner"></div>
        </div>

        <div class="player-display" id="player-display">

            <!-- Left Side -->
            <div class="player-left">
                <div class="player-image-container">
                    <img id="player-image" class="player-image" src="" alt="Player Image">
                </div>
                <h2 class="player-name" id="player-name">Player Name</h2>
                <div class="sold-overlay" id="sold-overlay">
                    <div class="sold-ribbon">
                        <div class="sold-text">SOLD</div>
                    </div>
                </div>
                <div class="unsold-overlay" id="unsold-overlay">
                    <div class="unsold-ribbon">
                        <div class="unsold-text">UNSOLD</div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="player-right">
                <div class="current_player_details">
                    <div id="current-team">₹ 0</div>
                    <div>
                        <div class="detail-label">Current Price</div>
                        <div class="detail-value base-price" id="current-price"></div>
                    </div>
                </div>
                <div class="player-details">
                    <div class="detail-card">
                        <div class="detail-label">Role</div>
                        <div class="detail-value" id="player-role">-</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Batting Style</div>
                        <div class="detail-value" id="batting-type">-</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Bowling Style</div>
                        <div class="detail-value" id="bowling-type">-</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Base Price</div>
                        <div class="detail-value base-price" id="base-price">₹ 0</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Previous CRPL Team</div>
                        <div class="detail-value" id="company-name">-</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Club</div> <!--  Chapter -->
                        <div class="detail-value" id="chapter-name">-</div>
                    </div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Description</div> <!-- category - company-->
                    <div class="detail-value" id="business-category">-</div>
                </div>
            </div>
        </div>

    </div>
    <div>
        <h3 style="color:cyan;">
            <center>Developed By - AppTeq Technology Solutions</center>
        </h3>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        let currentPlayerId = null;
        let pollingInterval = null;
        let auctionSettings = null;
        let currentRound = 1;
        let currentRoundProgress = 0;
        let totalPlayersPerRound = 0;
        let currentRoundTotalPlayers = 0;
        let totalRounds = 0;
        let roundsRules = [];

        $(document).ready(function () {
            initializeParticles();
            loadAuctionSettings();
            startPolling();
        });

        function loadAuctionSettings() {
            $.get('<?= base_url('admin/get-auction-settings') ?>')
                .done(function (response) {
                    auctionSettings = response;
                    // totalPlayersPerRound = parseInt(response.players_per_round) || 20;
                    totalRounds = parseInt(response.number_of_rounds) || 5;
                     // Parse rounds rules from the new JSON structure
                    if (response.rounds_rules) {
                        try {
                            roundsRules = typeof response.rounds_rules === 'string' ?
                                JSON.parse(response.rounds_rules) :
                                response.rounds_rules;
                        } catch (e) {
                            console.error('Error parsing rounds rules:', e);
                            // Set default rounds rules
                            roundsRules = generateDefaultRoundsRules();
                        }
                    } else {
                        // Set default rounds rules
                        roundsRules = generateDefaultRoundsRules();
                    }
                    // Sort rounds rules by round number
                    roundsRules.sort((a, b) => a.round - b.round);
                    updateRoundDisplay();
                })
                .fail(function (xhr, status, error) {
                    console.error('Error loading auction settings:', error);
                    // Set default values
                    totalPlayersPerRound = 20;
                    totalRounds = 5;
                    roundsRules = generateDefaultRoundsRules();
                    updateRoundDisplay();
                });
        }
         function generateDefaultRoundsRules() {
            const defaultRules = [];
            for (let i = 1; i <= totalRounds; i++) {
                defaultRules.push({
                    round: i,
                    players_count: Math.max(20 - (i - 1) * 3, 5) 
                });
            }
            return defaultRules;
        }



        function getCurrentRoundPlayersCount(roundNumber) {

            const roundRule = roundsRules.find(rule => rule.round === roundNumber);

            return roundRule ? parseInt(roundRule.players_count) : 10; // Default fallback

        }

        function updateRoundDisplay() {
            // Get players count for current round

            currentRoundTotalPlayers = getCurrentRoundPlayersCount(currentRound);
            $('#round-title').text('Round ' + currentRound);
            $('#progress-count').text(currentRoundProgress + ' / ' + currentRoundTotalPlayers);

            const progressPercentage = (currentRoundProgress / currentRoundTotalPlayers) * 100;
            $('#progress-bar').css('width', Math.min(progressPercentage, 100) + '%');
        }

        function fetchRoundProgress() {
            $.get('<?= base_url('auction/getCompletedAuctionsCount') ?>')
                .done(function (response) {
                    if (response.success) {
                        const totalCompleted = parseInt(response.completed_count);
                        calculateCurrentRoundAndProgress(totalCompleted);

                        // Calculate current round and progress
                        // currentRound = Math.floor(totalCompleted / totalPlayersPerRound) + 1;
                        // currentRoundProgress = totalCompleted % totalPlayersPerRound;

                        // // Ensure we don't exceed total rounds
                        // if (currentRound > totalRounds) {
                        //     currentRound = totalRounds;
                        //     currentRoundProgress = totalPlayersPerRound;
                        // }

                        updateRoundDisplay();
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Error fetching round progress:', error);
                });
        }
        
        function calculateCurrentRoundAndProgress(totalCompleted) {
            let cumulativeCount = 0;
            let foundRound = false;

            // Iterate through rounds to find current round
            for (let i = 0; i < roundsRules.length; i++) {
                const roundRule = roundsRules[i];
                const roundPlayersCount = parseInt(roundRule.players_count);
                if (totalCompleted <= cumulativeCount + roundPlayersCount) {
                    currentRound = parseInt(roundRule.round);
                    currentRoundProgress = totalCompleted - cumulativeCount;
                    foundRound = true;
                    break;
                }
                cumulativeCount += roundPlayersCount;
            }
            // If we've completed all rounds
            if (!foundRound && roundsRules.length > 0) {
                const lastRound = roundsRules[roundsRules.length - 1];
                currentRound = parseInt(lastRound.round);
                currentRoundProgress = parseInt(lastRound.players_count);
            }

            // Ensure we don't exceed total rounds
            if (currentRound > totalRounds) {
                currentRound = totalRounds;
                const lastRoundRule = roundsRules.find(rule => rule.round === totalRounds);
                currentRoundProgress = lastRoundRule ? parseInt(lastRoundRule.players_count) : 0;
            }
        }

        function initializeParticles() {
            const particlesContainer = document.getElementById('particles');

            for (let i = 0; i < 35; i++) {
                createParticle(particlesContainer);
            }

            // Create new particles periodically
            setInterval(() => {
                if (document.querySelectorAll('.particle').length < 35) {
                    createParticle(particlesContainer);
                }
            }, 1000);
        }

        function createParticle(container) {
            const particle = document.createElement('div');
            particle.className = 'particle';

            const size = Math.random() * 4 + 2;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 15 + 10) + 's';
            particle.style.animationDelay = Math.random() * 5 + 's';

            container.appendChild(particle);

            // Remove particle after animation
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 25000);
        }

        function startPolling() {
            // Initial fetch
            fetchSelectedPlayer();
            fetchRoundProgress();

            // Poll every 3 seconds
            pollingInterval = setInterval(() => {
                fetchSelectedPlayer();
                fetchRoundProgress();
            }, 3000);
        }

        function fetchSelectedPlayer() {
            // Get the selected player
            $.get('<?= base_url('auction/getSelectedPlayer') ?>')
                .done(function (response) {
                    if (response.success && response.player) {
                        displayPlayer(response.player);
                    } else {
                        showWaitingState();
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Error fetching selected player:', error);
                    showWaitingState();
                });
        }

        function displayPlayer(player) {
            // DEBUG: Log the entire player object to see what's coming from backend
            // console.log('Player data received:', player);

            // Hide waiting state and show player display
            $('#waiting-state').hide();
            $('#player-display').show();

            // Update player information
            $('#player-name').text(player.player_name || 'Unknown Player');
            $('#player-role').text(player.player_role || '-');
            $('#batting-type').text(player.batting_type || '-');
            $('#bowling-type').text(player.bowling_type || '-');
            $('#company-name').text(player.company_name || '-');
            $('#business-category').text(player.business_category || '-');
            // COMPANY AND BUSINESS CATEGORY LOGIC WITH DEBUG
            var companyDisplay = '';
            // console.log('Business category value:', player);
            // console.log('Company name value:', player.company_name);

            // if (player.business_category && player.company_name) {
            //     companyDisplay = player.business_category + ' - ' + player.company_name;
            //     // console.log('Case 1: Both found - ', companyDisplay);
            // } else if (player.company_name) {
            //     companyDisplay = player.company_name;
            //     // console.log('Case 2: Only company - ', companyDisplay);
            // } else if (player.business_category) {
            //     companyDisplay = player.business_category;
            //     // console.log('Case 3: Only business category - ', companyDisplay);
            // } else {
            //     companyDisplay = '-';
            //     // console.log('Case 4: Neither found');
            // }

            // $('#company-name').text(companyDisplay);
            // // console.log('Final company display set to:', companyDisplay);

            $('#chapter-name').text(player.chapter_name || '-');
            $('#current-price').text('₹ ' + formatPrice(player.current_price || 0));
            const logoUrl = player.team_logo ?
                '<?= base_url('uploads/teams/') ?>' + player.team_logo :
                '<?= base_url() ?>public/assets/default-team1.png';
            $('#current-team').html(`<img src="${logoUrl}" alt="Team Logo" style="height: 100px; border-radius: 8px; margin-right:20px">`);
            $('#base-price').text('₹ ' + formatPrice(player.base_price || 0));
            const imageSrc = player.profile_pic ?
                '<?= base_url('uploads/players/') ?>' + player.profile_pic :
                '<?= base_url('uploads/teams/bni_logo.png') ?>';
            $('#player-image').attr('src', imageSrc);
            updatePlayerStatus(player);
            $('#player-image').addClass('pulse-animation');
            currentPlayerId = player.player_id;
        }

        function updatePlayerStatus(player) {
            const statusDisplay = $('#status-display');
            const statusLabel = $('#status-label');
            const teamInfo = $('#team-info');
            const finalPriceDisplay = $('#final-price-display');
            const soldOverlay = $('#sold-overlay');
            const unsoldOverlay = $('#unsold-overlay');

            if (player.auction_status === 'sold' && player.sold_to) {
                // Show sold status
                statusDisplay.removeClass('status-unsold').addClass('status-sold').show();
                statusLabel.text('SOLD');
                soldOverlay.addClass('show');
                unsoldOverlay.removeClass('show');
                // Show team info
                if (player.team_name) {
                    $('#team-name').text(player.team_name);
                    teamInfo.show();

                    // Show team logo if available
                    if (player.team_logo) {
                        $('#team-logo').attr('src', '<?= base_url('uploads/teams/') ?>' + player.team_logo).show();
                    } else {
                        $('#team-logo').attr('src', '<?= base_url('uploads/teams/bni_logo.png') ?>').show();
                    }
                } else {
                    teamInfo.hide();
                }

                // Show final price
                if (player.final_price) {
                    finalPriceDisplay.text('₹ ' + formatPrice(player.final_price)).show();
                } else {
                    finalPriceDisplay.hide();
                }

            } else if (player.auction_status === 'unsold') {
                // Show unsold status
                statusDisplay.removeClass('status-sold').addClass('status-unsold').show();
                statusLabel.text('UNSOLD');
                teamInfo.hide();
                finalPriceDisplay.hide();
                soldOverlay.removeClass('show');
                unsoldOverlay.addClass('show');

            } else {
                // Hide status for available players
                statusDisplay.hide();
                soldOverlay.removeClass('show');
                unsoldOverlay.removeClass('show');
            }
        }

        function showWaitingState() {
            $('#player-display').hide();
            $('#waiting-state').show();
            currentPlayerId = null;
        }

        function formatPrice(price) {
            if (!price) return '0';
            return parseInt(price).toLocaleString('en-IN');
        }

        // Handle visibility change to pause/resume polling
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            } else {
                if (!pollingInterval) {
                    startPolling();
                }
            }
        });

        // Handle window focus/blur for better performance
        $(window).on('focus', function () {
            if (!pollingInterval) {
                startPolling();
            }
        });

        $(window).on('blur', function () {
            // Don't stop polling completely, but you could reduce frequency here if needed
        });

        // Error handling for images
        $('#player-image').on('error', function () {
            $(this).attr('src', '<?= base_url('uploads/teams/bni_logo.png') ?>');
        });

        $('#team-logo').on('error', function () {
            $(this).attr('src', '<?= base_url('uploads/teams/bni_logo.png') ?>');
        });

        // Smooth transitions for status changes
        function animateStatusChange() {
            $('#status-display').hide().fadeIn(800);
        }

        // Add loading animation while fetching
        function showLoadingImage() {
            const container = $('.player-image-container');
            container.html('<div class="image-loading"><i class="fas fa-spinner fa-spin fa-2x" style="color: #ffd700;"></i></div>');
        }

        function hideLoadingImage() {
            const container = $('.player-image-container');
            container.html('<img id="player-image" class="player-image" src="" alt="Player Image">');
        }
    </script>
</body>

</html>