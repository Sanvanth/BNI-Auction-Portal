<?php
namespace App\Controllers;

use App\Models\PlayerModel;
use App\Models\TeamModel;
use CodeIgniter\Controller;

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Admin extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        // Check if event is selected
        if (!$eventId) {
            return redirect()->back()->with('error', 'No event selected. Please select an event to continue.');
        }

        // Get total players count for the selected event
        $totalPlayersQuery = $db->query("SELECT COUNT(*) as count FROM players WHERE is_active = 1 AND event_id = ?", [$eventId]);
        $totalPlayers = $totalPlayersQuery->getRow()->count;

        // Get total teams count for the selected event
        $totalTeamsQuery = $db->query("SELECT COUNT(*) as count FROM teams WHERE is_active = 1 AND event_id = ?", [$eventId]);
        $totalTeams = $totalTeamsQuery->getRow()->count;

        // Get players available in auction (newly added, not yet auctioned) for the selected event
        $availablePlayersQuery = $db->query("SELECT COUNT(*) as count FROM players WHERE is_active = 1 AND auction_status = 'available' AND event_id = ?", [$eventId]);
        $availablePlayers = $availablePlayersQuery->getRow()->count;

        // Get sold players count (only those with a valid team) for the selected event
        $soldPlayersQuery = $db->query("SELECT COUNT(*) as count FROM players WHERE is_active = 1 AND auction_status = 'sold' AND sold_to IS NOT NULL AND event_id = ?", [$eventId]);
        $soldPlayers = $soldPlayersQuery->getRow()->count;

        // Get unsold players count (auctioned but no team bid for them) for the selected event
        $unsoldPlayersQuery = $db->query("SELECT COUNT(*) as count FROM players WHERE is_active = 1 AND auction_status = 'unsold' AND sold_to IS NULL AND event_id = ?", [$eventId]);
        $unsoldPlayers = $unsoldPlayersQuery->getRow()->count;

        // Get player stats using raw query with strict condition on sold players for the selected event
        $playerStatsQuery = $db->query("
        SELECT 
            player_role,
            COUNT(*) as total,
            COUNT(CASE WHEN auction_status = 'available' THEN 1 END) as available,
            COUNT(CASE WHEN auction_status = 'sold' AND sold_to IS NOT NULL THEN 1 END) as sold,
            COUNT(CASE WHEN auction_status = 'unsold' THEN 1 END) as unsold,
            COUNT(CASE WHEN auction_status = 'sold' AND sold_to IS NOT NULL THEN 1 END) as sold_active
        FROM players 
        WHERE is_active = 1 AND event_id = ?
        GROUP BY player_role
    ", [$eventId]);
        $playerStatsResult = $playerStatsQuery->getResult();

        // Format player stats
        $playerStats = [];
        $roles = ['batsman', 'bowler', 'allrounder', 'wicketkeeper'];
        foreach ($roles as $role) {
            $playerStats[$role] = [
                'total' => 0,
                'sold' => 0,
                'sold_active' => 0,
            ];
        }
        foreach ($playerStatsResult as $stat) {
            $playerStats[$stat->player_role] = [
                'total' => $stat->total,
                'sold' => $stat->sold,
                'sold_active' => $stat->sold_active,
            ];
        }

        // Get team composition and role breakdown for the selected event
        $teamCompositionQuery = $db->query("
        SELECT 
            t.team_id,
            t.team_name,
            t.purse,
            t.max_purse,
            COUNT(p.player_id) as total_players,
            SUM(CASE WHEN p.player_role = 'batsman' THEN 1 ELSE 0 END) as batsmen,
            SUM(CASE WHEN p.player_role = 'bowler' THEN 1 ELSE 0 END) as bowlers,
            SUM(CASE WHEN p.player_role = 'allrounder' THEN 1 ELSE 0 END) as allrounders,
            SUM(CASE WHEN p.player_role = 'wicketkeeper' THEN 1 ELSE 0 END) as wicketkeepers,
            SUM(CASE WHEN p.final_price IS NOT NULL THEN p.final_price ELSE 0 END) as total_spent
        FROM teams t
        LEFT JOIN players p 
            ON t.team_id = p.sold_to AND p.is_active = 1 AND p.auction_status = 'sold' AND p.event_id = ?
        WHERE t.is_active = 1 AND t.event_id = ?
        GROUP BY t.team_id, t.team_name, t.purse, t.max_purse
        ORDER BY t.team_name
    ", [$eventId, $eventId]);
        $teamComposition = $teamCompositionQuery->getResult();

        $data = [
            'total_players' => $totalPlayers,
            'total_teams' => $totalTeams,
            'available_players' => $availablePlayers,
            'sold_players' => $soldPlayers,
            'unsold_players' => $unsoldPlayers,
            'player_stats' => $playerStats,
            'team_composition' => $teamComposition,
            'activeMenu' => 'dashboard'
        ];

        return view('admin/dashboard', $data);
    }

    public function settings()
    {
        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        if (!$eventId) {
            return redirect()->back()->with('error', 'No event selected. Please select an event to continue.');
        }

        // Get settings from auction_settings table for current event
        $settingsQuery = $db->table('auction_settings')->getWhere(['event_id' => $eventId]);
        $settings = $settingsQuery->getRowArray();

        // If no settings exist for this event, create default settings
        if (!$settings) {
            $defaultSettings = [
                'event_id' => $eventId,
                'max_purse' => 100000000.00,
                'base_price' => 2000000.00,
                'total_players_per_team' => 11,
                'hold_amount' => 22000000.00,
                'number_of_rounds' => 5,
                'rounds_rules' => '[{"round":1,"players_count":20},{"round":2,"players_count":15},{"round":3,"players_count":10},{"round":4,"players_count":8},{"round":5,"players_count":5}]',
                'bid_increment_rules' => '[{"min_amount":0,"max_amount":10000000,"increment_amount":500000},{"min_amount":10000001,"max_amount":null,"increment_amount":1000000}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('auction_settings')->insert($defaultSettings);
            $settings = $defaultSettings;
            $settings['id'] = $db->insertID();
        }

        return view('admin/settings', [
            'settings' => $settings,
            'activeMenu' => 'settings',
            'pageTitle' => 'Auction & Team Settings'
        ]);
    }

    public function saveAuctionSettings()
    {
        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        if (!$eventId) {
            return redirect()->back()->with('error', 'No event selected. Please select an event to continue.');
        }

        $db->transBegin();

        try {
            // Validate inputs
            $max_purse = (float) $this->request->getPost('max_purse');
            $base_price = (float) $this->request->getPost('base_price');
            $total_players_per_team = (int) $this->request->getPost('total_players_per_team');
            $hold_amount = (float) $this->request->getPost('hold_amount');
            $number_of_rounds = (int) $this->request->getPost('number_of_rounds');
            $rounds_rules = $this->request->getPost('rounds_rules');
            $bid_increment_rules = $this->request->getPost('bid_increment_rules');

            // Validate basic inputs
            if ($max_purse <= 0 || $base_price < 0 || $total_players_per_team <= 0 || $number_of_rounds < 0) {
                throw new \Exception('Invalid input values.');
            }

            // Validate rounds rules JSON
            if (!empty($rounds_rules)) {
                $decodedRoundsRules = json_decode($rounds_rules, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedRoundsRules)) {
                    throw new \Exception('Invalid rounds rules format.');
                }

                // Validate each round rule
                foreach ($decodedRoundsRules as $roundRule) {
                    if (
                        !isset($roundRule['round']) || !isset($roundRule['players_count']) ||
                        !is_numeric($roundRule['round']) || !is_numeric($roundRule['players_count']) ||
                        $roundRule['round'] <= 0 || $roundRule['players_count'] <= 0
                    ) {
                        throw new \Exception('Invalid round rule values. Each round must have valid round number and players count.');
                    }
                }

                // Validate that rounds are sequential and match number_of_rounds
                $roundNumbers = array_column($decodedRoundsRules, 'round');
                sort($roundNumbers);
                $expectedRounds = range(1, $number_of_rounds);
                
                if (count($roundNumbers) !== $number_of_rounds || $roundNumbers !== $expectedRounds) {
                    throw new \Exception('Rounds rules must match the number of rounds and be sequential (1, 2, 3, ...).');
                }
            } else {
                // Set default rounds rules if empty
                $defaultRounds = [];
                for ($i = 1; $i <= $number_of_rounds; $i++) {
                    $defaultRounds[] = [
                        'round' => $i,
                        'players_count' => max(20 - ($i - 1) * 3, 5) // Decreasing count: 20, 17, 14, 11, 8, 5...
                    ];
                }
                $rounds_rules = json_encode($defaultRounds);
            }

            // Validate bid increment rules JSON
            if (!empty($bid_increment_rules)) {
                $decodedRules = json_decode($bid_increment_rules, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedRules)) {
                    throw new \Exception('Invalid bid increment rules format.');
                }

                // Validate each rule
                foreach ($decodedRules as $rule) {
                    if (
                        !isset($rule['min_amount']) || !isset($rule['increment_amount']) ||
                        !is_numeric($rule['min_amount']) || !is_numeric($rule['increment_amount']) ||
                        $rule['min_amount'] < 0 || $rule['increment_amount'] <= 0
                    ) {
                        throw new \Exception('Invalid bid increment rule values.');
                    }
                }
            } else {
                // Set default rules if empty
                $bid_increment_rules = '[{"min_amount":0,"max_amount":10000000,"increment_amount":500000},{"min_amount":10000001,"max_amount":null,"increment_amount":1000000}]';
            }

            $data = [
                'max_purse' => $max_purse,
                'base_price' => $base_price,
                'total_players_per_team' => $total_players_per_team,
                'hold_amount' => $hold_amount,
                'number_of_rounds' => $number_of_rounds,
                'rounds_rules' => $rounds_rules,
                'bid_increment_rules' => $bid_increment_rules,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Check if settings exist for this event
            $existingSettings = $db->table('auction_settings')->getWhere(['event_id' => $eventId])->getRowArray();

            if ($existingSettings) {
                // Update existing settings for this event
                $db->table('auction_settings')->where('event_id', $eventId)->update($data);
            } else {
                // Insert new settings for this event
                $data['event_id'] = $eventId;
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->table('auction_settings')->insert($data);
            }

            // NOW UPDATE ALL PLAYERS BASE PRICE - THIS IS THE KEY PART
            $playerUpdateResult = \App\Controllers\Player::updatePlayersBasePriceFromSettings($base_price,$eventId);

            $db->transCommit();

            // Create success message that includes player update info
            $successMessage = 'Settings saved successfully for current event';
            if ($playerUpdateResult['success']) {
                $successMessage .= '. ' . $playerUpdateResult['message'];
            } else {
                // If player update failed, add warning but don't fail the entire operation
                $successMessage .= '. Warning: ' . $playerUpdateResult['message'];
            }

            return redirect()->to('/admin/settings')->with('success', $successMessage);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to save auction settings: ' . $e->getMessage());
        }
    }

    public function getAuctionSettings()
    {
        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        if (!$eventId) {
            return $this->response->setJSON(['error' => 'No event selected'])->setStatusCode(400);
        }

        $settings = $db->table('auction_settings')->getWhere(['event_id' => $eventId])->getRowArray();

        if (!$settings) {
            // Return default settings if none exist for this event
            $settings = [
                'event_id' => $eventId,
                'max_purse' => 100000000.00,
                'base_price' => 2000000.00,
                'total_players_per_team' => 11,
                'hold_amount' => 22000000.00,
                'number_of_rounds' => 5,
                'rounds_rules' => '[{"round":1,"players_count":20},{"round":2,"players_count":15},{"round":3,"players_count":10},{"round":4,"players_count":8},{"round":5,"players_count":5}]',
                'bid_increment_rules' => '[{"min_amount":0,"max_amount":10000000,"increment_amount":500000},{"min_amount":10000001,"max_amount":null,"increment_amount":1000000}]'
            ];
        }

        return $this->response->setJSON($settings);
    }

    public function getBidIncrement()
    {
        $currentBid = (float) $this->request->getGet('current_bid');

        if ($currentBid < 0) {
            return $this->response->setJSON(['error' => 'Invalid bid amount'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        if (!$eventId) {
            return $this->response->setJSON(['error' => 'No event selected'])->setStatusCode(400);
        }

        $settings = $db->table('auction_settings')->getWhere(['event_id' => $eventId])->getRowArray();

        if (!$settings || empty($settings['bid_increment_rules'])) {
            return $this->response->setJSON(['increment' => 500000]); // Default increment
        }

        $bidRules = json_decode($settings['bid_increment_rules'], true);
        $increment = 500000; // Default fallback

        foreach ($bidRules as $rule) {
            $minAmount = (float) $rule['min_amount'];
            $maxAmount = isset($rule['max_amount']) && !is_null($rule['max_amount']) ? (float) $rule['max_amount'] : null;

            if ($currentBid >= $minAmount && ($maxAmount === null || $currentBid <= $maxAmount)) {
                $increment = (float) $rule['increment_amount'];
                break;
            }
        }

        return $this->response->setJSON(['increment' => $increment]);
    }

    /**
     * Get rounds configuration for the current event
     * @return mixed
     */
    public function getRoundsRules()
    {
        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        if (!$eventId) {
            return $this->response->setJSON(['error' => 'No event selected'])->setStatusCode(400);
        }

        $settings = $db->table('auction_settings')->getWhere(['event_id' => $eventId])->getRowArray();

        if (!$settings || empty($settings['rounds_rules'])) {
            // Return default rounds rules
            $defaultRounds = [];
            for ($i = 1; $i <= 5; $i++) {
                $defaultRounds[] = [
                    'round' => $i,
                    'players_count' => max(20 - ($i - 1) * 3, 5)
                ];
            }
            return $this->response->setJSON(['rounds_rules' => $defaultRounds]);
        }

        $roundsRules = json_decode($settings['rounds_rules'], true);
        return $this->response->setJSON(['rounds_rules' => $roundsRules]);
    }

    /**
     * Get players count for a specific round
     * @param int $roundNumber
     * @return mixed
     */
    public function getPlayersCountForRound($roundNumber = null)
    {
        if (!$roundNumber) {
            $roundNumber = (int) $this->request->getGet('round');
        }

        if ($roundNumber <= 0) {
            return $this->response->setJSON(['error' => 'Invalid round number'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $session = session();
        $eventId = $session->get('event_id');

        if (!$eventId) {
            return $this->response->setJSON(['error' => 'No event selected'])->setStatusCode(400);
        }

        $settings = $db->table('auction_settings')->getWhere(['event_id' => $eventId])->getRowArray();

        if (!$settings || empty($settings['rounds_rules'])) {
            // Return default count
            $defaultCount = max(20 - ($roundNumber - 1) * 3, 5);
            return $this->response->setJSON(['players_count' => $defaultCount]);
        }

        $roundsRules = json_decode($settings['rounds_rules'], true);
        
        foreach ($roundsRules as $rule) {
            if ((int)$rule['round'] === $roundNumber) {
                return $this->response->setJSON(['players_count' => (int)$rule['players_count']]);
            }
        }

        // If round not found, return default
        return $this->response->setJSON(['players_count' => 10]);
    }

    public function exportTeamComposition($type = 'pdf')
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
            t.team_name,
            COUNT(p.player_id) as total_players,
            SUM(CASE WHEN p.player_role = 'batsman' THEN 1 ELSE 0 END) as batsmen,
            SUM(CASE WHEN p.player_role = 'bowler' THEN 1 ELSE 0 END) as bowlers,
            SUM(CASE WHEN p.player_role = 'allrounder' THEN 1 ELSE 0 END) as allrounders,
            SUM(CASE WHEN p.player_role = 'wicketkeeper' THEN 1 ELSE 0 END) as wicketkeepers,
            t.purse
        FROM teams t
        LEFT JOIN players p ON t.team_id = p.sold_to AND p.is_active = 1 AND p.auction_status = 'sold'
        WHERE t.is_active = 1
        GROUP BY t.team_id, t.team_name, t.purse
        ORDER BY t.team_name
    ");

        $teams = $query->getResultArray();

        if ($type === 'pdf') {
            $html = '<h3>Team Composition</h3><table border="1" cellspacing="0" cellpadding="5">
            <tr>
                <th>#</th>
                <th>Team Name</th>
                <th>Total Players</th>
                <th>Batsmen</th>
                <th>Bowlers</th>
                <th>Allrounders</th>
                <th>Wicketkeepers</th>
                <th>Remaining Purse</th>
            </tr>';

            $i = 1;
            foreach ($teams as $team) {
                $html .= '<tr>
                <td>' . $i++ . '</td>
                <td>' . esc($team['team_name']) . '</td>
                <td>' . $team['total_players'] . '</td>
                <td>' . $team['batsmen'] . '</td>
                <td>' . $team['bowlers'] . '</td>
                <td>' . $team['allrounders'] . '</td>
                <td>' . $team['wicketkeepers'] . '</td>
                <td>' . number_format($team['purse'], 2) . '</td>
            </tr>';
            }

            $html .= '</table>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('team_composition_' . date('Ymd_His') . '.pdf', ["Attachment" => true]);
            exit;
        } elseif ($type === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#')
                ->setCellValue('B1', 'Team Name')
                ->setCellValue('C1', 'Total Players')
                ->setCellValue('D1', 'Batsmen')
                ->setCellValue('E1', 'Bowlers')
                ->setCellValue('F1', 'Allrounders')
                ->setCellValue('G1', 'Wicketkeepers')
                ->setCellValue('H1', 'Remaining Purse');

            $row = 2;
            $count = 1;
            foreach ($teams as $team) {
                $sheet->setCellValue('A' . $row, $count++)
                    ->setCellValue('B' . $row, $team['team_name'])
                    ->setCellValue('C' . $row, $team['total_players'])
                    ->setCellValue('D' . $row, $team['batsmen'])
                    ->setCellValue('E' . $row, $team['bowlers'])
                    ->setCellValue('F' . $row, $team['allrounders'])
                    ->setCellValue('G' . $row, $team['wicketkeepers'])
                    ->setCellValue('H' . $row, $team['purse']);
                $row++;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="team_composition.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Invalid export type: " . esc($type));
        }
    }
}