<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2">
    <link rel="icon" href="<?= base_url('public/assets/AppTeqLogo.png') ?>" type="image/png">
    <title><?= esc($event['event_name']) ?> - Team Purse Values</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2D1810 0%, #1A0F0A 100%);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .logo {
            width: 80px;
            height: 80px;
            background: #D4AF37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }

        .logo-text {
            font-weight: bold;
            font-size: 24px;
            color: #2D1810;
        }

        .header-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #D4AF37;
            margin-bottom: 5px;
        }

        .header-text p {
            font-size: 1.1rem;
            color: #B8860B;
        }

        .event-info {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .event-name {
            font-size: 1.8rem;
            color: #D4AF37;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .event-date {
            color: #B8860B;
            font-size: 1rem;
        }

        .table-container {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .table-header {
            padding: 25px 30px;
            background: rgba(212, 175, 55, 0.1);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .table-title {
            font-size: 1.5rem;
            color: #D4AF37;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .table-subtitle {
            color: #B8860B;
            font-size: 0.9rem;
        }

        .team-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .team-table th {
            background: rgba(212, 175, 55, 0.15);
            color: #D4AF37;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
        }

        .team-table td {
            padding: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            color: #fff;
            vertical-align: middle;
        }

        .team-table tr:hover {
            background: rgba(212, 175, 55, 0.05);
        }

        .team-table tr:last-child td {
            border-bottom: none;
        }

        .team-logo-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .team-logo-table {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #D4AF37;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2D1810;
            font-weight: bold;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
            flex-shrink: 0;
        }

        .team-name-cell {
            font-weight: 600;
            color: #D4AF37;
            font-size: 1rem;
        }

        .purse-value {
            font-weight: 700;
            color: #FFD700;
            font-size: 1.1rem;
        }

        .player-count {
            font-weight: 600;
            color: #fff;
            text-align: start;
        }

        .rank-cell {
            font-weight: bold;
            color: #D4AF37;
            text-align: start;
            font-size: 1.1rem;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .no-data h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #B8860B;
        }

        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #D4AF37, #FFD700);
            border: none;
            border-radius: 50%;
            color: #2D1810;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .refresh-btn:hover {
            transform: scale(1.1) rotate(180deg);
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.5);
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #B8860B;
        }

        .spinner {
            border: 3px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            border-top: 3px solid #D4AF37;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        .stats-bar {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #D4AF37;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #B8860B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .header-text h1 {
                font-size: 2rem;
            }

            .event-name {
                font-size: 1.4rem;
            }

            .stats-bar {
                flex-direction: column;
                gap: 15px;
            }

            .team-table {
                font-size: 0.85rem;
            }

            .team-table th,
            .team-table td {
                padding: 12px 8px;
            }

            .team-logo-cell {
                gap: 10px;
            }

            .team-logo-table {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }

            .header {
                padding: 15px;
                flex-direction: column;
                text-align: center;
            }

            .logo {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .team-table th,
            .team-table td {
                padding: 8px 4px;
                font-size: 0.8rem;
            }
        }

        .error-message {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #dc3545;
            margin: 20px 0;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #28a745;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Teams Table -->
        <div class="table-container">
            <div>
                <table class="team-table">
                    <thead>
                        <tr>
                            <th>S No</th>
                            <th>Team Name</th>
                            <th>Hold Amount</th>
                            <th>Available Purse</th>
                            <th>Players</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($teams)): ?>
                            <?php 
                            // Sort teams by available purse (descending) for ranking
                            usort($teams, function($a, $b) {
                                return floatval($b['purse']) <=> floatval($a['purse']);
                            });
                            ?>
                            <?php foreach ($teams as $index => $team): ?>
                                <tr>
                                    <td class="rank-cell"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="team-logo-cell">
                                            <?php if (!empty($team['team_logo'])): ?>
                                                <img src="<?= base_url('uploads/teams/' . esc($team['team_logo'])) ?>"
                                                    alt="<?= esc($team['team_name']) ?>"
                                                    style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="team-logo-table">
                                                    <?= strtoupper(substr(esc($team['team_name']), 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="team-name-cell"><?= esc($team['team_name']) ?></div>
                                        </div>
                                    </td>
                                    <td class="purse-value">₹<span class="indian-price"><?= floatval($team['hold_amount']) ?></span></td>
                                    <td class="purse-value">₹<span class="indian-price"><?= floatval($team['purse']) ?></span></td>
                                    <td class="player-count"><?= $team['player_count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-data">
                                    <h3>No Teams Found</h3>
                                    <p>Teams will appear here once they are added to this event.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button class="refresh-btn" onclick="location.reload()" title="Refresh Data">↻</button>

    <script>
        function formatPrice(price) {
            if (!price) return '0';
            return parseFloat(price).toLocaleString('en-IN', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function calculateStatistics() {
            const teams = <?= json_encode($teams) ?>;
            let totalPlayers = 0;
            let totalPurse = 0;
            
            teams.forEach(team => {
                totalPlayers += parseInt(team.player_count) || 0;
                totalPurse += parseFloat(team.purse) || 0;
            });

            const avgPurse = teams.length > 0 ? totalPurse / teams.length : 0;

            // Update statistics
            document.getElementById('total-players').textContent = totalPlayers;
            document.getElementById('total-purse').textContent = '₹' + formatPrice(totalPurse);
            document.getElementById('avg-purse').textContent = '₹' + formatPrice(avgPurse);
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Format all price elements
            const priceElements = document.querySelectorAll('.indian-price');
            priceElements.forEach(el => {
                const price = el.textContent;
                el.textContent = formatPrice(price);
            });

            // Calculate and display statistics
            calculateStatistics();

            // Add loading indicator during refresh
            let refreshing = false;
            const refreshBtn = document.querySelector('.refresh-btn');
            
            refreshBtn.addEventListener('click', function() {
                if (!refreshing) {
                    refreshing = true;
                    this.innerHTML = '⟳';
                    this.style.animation = 'spin 1s linear infinite';
                    
                    setTimeout(() => {
                        refreshing = false;
                        this.innerHTML = '↻';
                        this.style.animation = '';
                    }, 1000);
                }
            });

            // Add visual feedback for auto-refresh
            let lastRefresh = Date.now();
            const refreshIndicator = document.createElement('div');
            refreshIndicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(212, 175, 55, 0.9);
                color: #2D1810;
                padding: 8px 12px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: bold;
                opacity: 0;
                transition: opacity 0.3s ease;
                z-index: 1000;
            `;
            refreshIndicator.textContent = 'Updated';
            document.body.appendChild(refreshIndicator);

            // Show update indicator briefly every 2 seconds (matching meta refresh)
            setInterval(() => {
                refreshIndicator.style.opacity = '1';
                setTimeout(() => {
                    refreshIndicator.style.opacity = '0';
                }, 500);
            }, 2000);
        });

        // Handle visibility change to pause/resume auto-refresh when tab is not visible
        document.addEventListener('visibilitychange', function() {
            const metaRefresh = document.querySelector('meta[http-equiv="refresh"]');
            if (document.hidden) {
                // Pause auto-refresh when tab is hidden
                if (metaRefresh) metaRefresh.remove();
            } else {
                // Resume auto-refresh when tab becomes visible
                if (!document.querySelector('meta[http-equiv="refresh"]')) {
                    const newMeta = document.createElement('meta');
                    newMeta.setAttribute('http-equiv', 'refresh');
                    newMeta.setAttribute('content', '2');
                    document.head.appendChild(newMeta);
                }
            }
        });
    </script>
</body>

</html>