<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Player extends Controller
{
    protected $db;
    protected $session;
    protected $currentEventId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        
        // Get current user's event_id from session
        $this->currentEventId = $this->session->get('event_id');
        
        // Redirect if no event_id in session
        if (!$this->currentEventId) {
            redirect()->to('/login')->send();
            exit;
        }
    }

    // Helper to get global auction settings for current event
    protected function getAuctionSettings()
    {
        $settings = $this->db->table('auction_settings')
            ->getWhere(['event_id' => $this->currentEventId])
            ->getRowArray();

        // Return default settings if none exist
        if (!$settings) {
            return [
                'base_price' => 2000000, // Default ₹20 Lakhs
                'max_purse' => 100000000, // Default ₹10 Crores
                'total_players_per_team' => 11,
                'hold_amount' => 22000000,
                'number_of_rounds' => 5,
                'players_per_round' => 20,
                'bid_increment_rules' => '[]'
            ];
        }

        return $settings;
    }

    public function index()
    {
        $showDeleted = $this->request->getGet('deleted') === '1';

        $sql = "
            SELECT p.*, t.team_name AS sold_to_name
            FROM players p
            LEFT JOIN teams t ON p.sold_to = t.team_id AND t.event_id = ?
            WHERE p.is_active = ? AND p.event_id = ?
            ORDER BY p.player_id DESC
        ";

        $players = $this->db->query($sql, [
            $this->currentEventId,
            $showDeleted ? 0 : 1,
            $this->currentEventId
        ])->getResultArray();

        // Get auction settings for display
        $settings = $this->getAuctionSettings();

        return view('admin/players', [
            'players' => $players,
            'showDeleted' => $showDeleted,
            'activeMenu' => 'players',
            'settings' => $settings // Pass settings to view if needed
        ]);
    }

    public function store()
    {
        helper(['form']);

        // Fetch base_price from auction_settings (dynamic)
        $settings = $this->getAuctionSettings();
        $base_price = isset($settings['base_price']) ? (float) $settings['base_price'] : 2000000;

        // Get form data
        $player_name = $this->request->getPost('player_name');
        $batting_type = $this->request->getPost('batting_type');
        $bowling_type = $this->request->getPost('bowling_type');
        $player_role = $this->request->getPost('player_role');
        $category = $this->request->getPost('category'); // NEW FIELD
        $chapter_name = $this->request->getPost('chapter_name');
        $business_category = $this->request->getPost('business_category');
        $company_name = $this->request->getPost('company_name');
        $phone_number = $this->request->getPost('phone_number');
        $cricheroes_url = $this->request->getPost('cricheroes_url');
        $is_unsold = 0;
        $is_active = 1;
        $auction_status = 'available'; // Default status

        // Validate required fields
        if (empty($player_name) || empty($player_role)) {
            return redirect()->back()->with('error', 'Player name and role are required fields.');
        }

        // Handle profile picture upload
        $profile_pic = null;
        $file = $this->request->getFile('profile_pic');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $uploadPath = FCPATH . 'uploads/players';

            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $newName);
            $profile_pic = $newName;
        }

        $this->db->transBegin();

        try {
            $sql = "INSERT INTO players 
                (player_name, batting_type, bowling_type, player_role, category, chapter_name, 
                 business_category, company_name, phone_number, cricheroes_url, base_price, is_unsold, 
                 is_active, profile_pic, auction_status, event_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $result = $this->db->query($sql, [
                $player_name,
                $batting_type,
                $bowling_type,
                $player_role,
                $category, // Add category parameter
                $chapter_name,
                $business_category,
                $company_name,
                $phone_number,
                $cricheroes_url,
                $base_price,
                $is_unsold,
                $is_active,
                $profile_pic,
                $auction_status,
                $this->currentEventId // Add event_id
            ]);

            if (!$result) {
                throw new \Exception('Failed to add player');
            }

            $this->db->transCommit();
            return redirect()->to('/admin/players')->with('success', 'Player added successfully with base price of ₹' . number_format($base_price));

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to add player: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sql = "SELECT * FROM players WHERE player_id = ? AND event_id = ?";
        $player = $this->db->query($sql, [$id, $this->currentEventId])->getRowArray();

        if (!$player) {
            return $this->response->setJSON(['error' => 'Player not found']);
        }

        return $this->response->setJSON($player);
    }

    public function update($id)
    {
        // Get current settings to update base_price for this player
        $settings = $this->getAuctionSettings();
        $base_price = isset($settings['base_price']) ? (float) $settings['base_price'] : 2000000;

        $player_name = $this->request->getPost('player_name');
        $batting_type = $this->request->getPost('batting_type');
        $bowling_type = $this->request->getPost('bowling_type');
        $player_role = $this->request->getPost('player_role');
        $category = $this->request->getPost('category'); // NEW FIELD
        $chapter_name = $this->request->getPost('chapter_name');
        $business_category = $this->request->getPost('business_category');
        $company_name = $this->request->getPost('company_name');
        $phone_number = $this->request->getPost('phone_number');
        $cricheroes_url = $this->request->getPost('cricheroes_url');

        // Validate required fields
        if (empty($player_name) || empty($player_role)) {
            return redirect()->back()->with('error', 'Player name and role are required fields.');
        }

        // Handle profile picture upload
        $profile_pic = null;
        $file = $this->request->getFile('profile_pic');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $uploadPath = FCPATH . 'uploads/players';

            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $newName);
            $profile_pic = $newName;
        }

        $this->db->transBegin();

        try {
            $sql = "
                UPDATE players SET
                    player_name = ?,
                    batting_type = ?,
                    bowling_type = ?,
                    player_role = ?,
                    category = ?,        
                    chapter_name = ?,
                    business_category = ?,
                    company_name = ?,
                    phone_number = ?,
                    cricheroes_url = ?,
                    base_price = ?,
                    updated_at = NOW()" .
                ($profile_pic ? ", profile_pic = ?" : "") .
                " WHERE player_id = ? AND event_id = ?";

            $params = [
                $player_name,
                $batting_type,
                $bowling_type,
                $player_role,
                $category, // Add category parameter
                $chapter_name,
                $business_category,
                $company_name,
                $phone_number,
                $cricheroes_url,
                $base_price
            ];

            if ($profile_pic) {
                $params[] = $profile_pic;
            }

            $params[] = $id;
            $params[] = $this->currentEventId; // Add event_id condition

            $result = $this->db->query($sql, $params);

            if (!$result) {
                throw new \Exception('Failed to update player');
            }

            $this->db->transCommit();
            return redirect()->to('/admin/players')->with('success', 'Player updated successfully with current base price ₹' . number_format($base_price));

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to update player: ' . $e->getMessage());
        }
    }

    public function updateCurrentBidTeam()
    {
        $json = $this->request->getBody();
        $request = json_decode($json, true);

        if (!$request || !isset($request['player_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing player ID']);
        }

        if (!is_numeric($request['player_id']) || !is_numeric($request['team_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid IDs']);
        }

        $this->db->transBegin();

        try {
            $playerModel = new \App\Models\PlayerModel();

            $data = [
                'current_team' => $request['team_id'],
                'current_price' => $request['final_price'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update only players belonging to current event
            $updated = $playerModel->where('event_id', $this->currentEventId)
                ->update($request['player_id'], $data);

            if ($updated) {
                // Deactivate previous auction logs for this player and event
                $this->db->table('auction_logs')
                    ->where('player_id', $request['player_id'])
                    ->where('event_id', $this->currentEventId)
                    ->update(['is_active' => 0]);

                // Insert new auction log
                $logData = [
                    'player_id' => $request['player_id'],
                    'team_id' => $request['team_id'],
                    'event_id' => $this->currentEventId,
                    'is_active' => 1,
                    'current_bid_amount' => $request['final_price'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->table('auction_logs')->insert($logData);
            }

            $this->db->transCommit();
            return $this->response->setJSON(['success' => $updated]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Update ALL players base_price to current auction setting base_price for current event
    public function resetAllPlayersBasePrice()
    {
        $settings = $this->getAuctionSettings();
        $base_price = isset($settings['base_price']) ? (float) $settings['base_price'] : 2000000;

        $this->db->transBegin();

        try {
            $sql = "UPDATE players SET base_price = ?, updated_at = NOW() WHERE is_active = 1 AND event_id = ?";
            $result = $this->db->query($sql, [$base_price, $this->currentEventId]);

            if (!$result) {
                throw new \Exception('Failed to update all players base price');
            }

            $affectedRows = $this->db->affectedRows();
            $this->db->transCommit();

            return redirect()->back()->with('success', "All {$affectedRows} active players base_price updated to ₹" . number_format($base_price));

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to update players base price: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $this->db->transBegin();

        try {
            // Get player details before deletion (ensure it belongs to current event)
            $player = $this->db->query("SELECT * FROM players WHERE player_id = ? AND event_id = ?", [$id, $this->currentEventId])->getRowArray();

            if (!$player) {
                throw new \Exception('Player not found');
            }

            // If player was sold to a team, refund the amount using dynamic settings
            if ($player['sold_to'] && $player['final_price']) {
                $team = $this->db->query("SELECT * FROM teams WHERE team_id = ? AND event_id = ?", [$player['sold_to'], $this->currentEventId])->getRowArray();

                if ($team) {
                    $settings = $this->getAuctionSettings();
                    $basePrice = (float) $settings['base_price'];

                    // Calculate refund breakdown (same logic as markAsSold)
                    $fromHold = min($basePrice, $player['final_price']);
                    $fromPurse = $player['final_price'] - $fromHold;

                    $newPurse = $team['purse'] + $fromPurse;
                    $newHold = $team['hold_amount'] + $fromHold;

                    $this->db->query("
                        UPDATE teams 
                        SET purse = ?, hold_amount = ?, updated_at = NOW() 
                        WHERE team_id = ? AND event_id = ?
                    ", [$newPurse, $newHold, $team['team_id'], $this->currentEventId]);
                }
            }

            // Soft delete the player
            $this->db->query("
                UPDATE players 
                SET is_active = 0, sold_to = NULL, final_price = NULL, 
                    auction_status = 'deleted', updated_at = NOW() 
                WHERE player_id = ? AND event_id = ?
            ", [$id, $this->currentEventId]);

            // Deactivate auction logs for this player
            $this->db->table('auction_logs')
                ->where('player_id', $id)
                ->where('event_id', $this->currentEventId)
                ->update(['is_active' => 0]);

            $this->db->transCommit();
            return redirect()->to('/admin/players')->with('success', 'Player deleted successfully');

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to delete player: ' . $e->getMessage());
        }
    }

    public function export($type = 'pdf')
    {
        $sql = "
            SELECT p.*, t.team_name AS sold_to_name,
                   CASE 
                       WHEN p.auction_status = 'sold' THEN 'Sold'
                       WHEN p.auction_status = 'unsold' THEN 'Unsold'
                       WHEN p.auction_status = 'available' THEN 'Available'
                       ELSE 'Unknown'
                   END as status_text
            FROM players p
            LEFT JOIN teams t ON p.sold_to = t.team_id AND t.event_id = ?
            WHERE p.is_active = 1 AND p.event_id = ?
            ORDER BY p.player_name ASC
        ";

        $players = $this->db->query($sql, [$this->currentEventId, $this->currentEventId])->getResultArray();

        if ($type === 'pdf') {
            $html = '<h3>Player List</h3><table border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <th>ID</th><th>Name</th><th>Role</th><th>Category</th><th>Chapter</th><th>Company</th>
                    <th>Base Price</th><th>Status</th><th>Team</th><th>Final Price</th><th>CricHeroes URL</th>
                </tr>';

            foreach ($players as $player) {
                $html .= '<tr>
                    <td>' . esc($player['player_id']) . '</td>
                    <td>' . esc($player['player_name']) . '</td>
                    <td>' . esc($player['player_role']) . '</td>
                    <td>' . esc($player['category'] ?? '-') . '</td>
                    <td>' . esc($player['chapter_name']) . '</td>
                    <td>' . esc($player['company_name']) . '</td>
                    <td>₹' . number_format($player['base_price']) . '</td>
                    <td>' . esc($player['status_text']) . '</td>
                    <td>' . esc($player['sold_to_name'] ?? '-') . '</td>
                    <td>' . ($player['final_price'] ? '₹' . number_format($player['final_price']) : '-') . '</td>
                    <td>' . esc($player['cricheroes_url'] ?? '-') . '</td>
                </tr>';
            }
            $html .= '</table>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'players_' . date('Ymd_His') . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit;

        } elseif ($type === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $sheet->setCellValue('A1', 'Player ID')
                ->setCellValue('B1', 'Player Name')
                ->setCellValue('C1', 'Role')
                ->setCellValue('D1', 'Category')
                ->setCellValue('E1', 'Chapter')
                ->setCellValue('F1', 'Company')
                ->setCellValue('G1', 'Base Price')
                ->setCellValue('H1', 'Status')
                ->setCellValue('I1', 'Team')
                ->setCellValue('J1', 'Final Price')
                ->setCellValue('K1', 'CricHeroes URL');

            $row = 2;
            foreach ($players as $player) {
                $sheet->setCellValue('A' . $row, $player['player_id'])
                    ->setCellValue('B' . $row, $player['player_name'])
                    ->setCellValue('C' . $row, $player['player_role'])
                    ->setCellValue('D' . $row, $player['category'] ?? '-')
                    ->setCellValue('E' . $row, $player['chapter_name'])
                    ->setCellValue('F' . $row, $player['company_name'])
                    ->setCellValue('G' . $row, $player['base_price'])
                    ->setCellValue('H' . $row, $player['status_text'])
                    ->setCellValue('I' . $row, $player['sold_to_name'] ?? '-')
                    ->setCellValue('J' . $row, $player['final_price'] ?? 0)
                    ->setCellValue('K' . $row, $player['cricheroes_url'] ?? '');
                $row++;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="players.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Invalid export type: " . esc($type));
        }
    }

    public function restore($id)
    {
        $this->db->transBegin();

        try {
            // Get current base price from settings
            $settings = $this->getAuctionSettings();
            $base_price = isset($settings['base_price']) ? (float) $settings['base_price'] : 2000000;

            $result = $this->db->query("
                UPDATE players 
                SET is_active = 1, auction_status = 'available', base_price = ?, updated_at = NOW() 
                WHERE player_id = ? AND event_id = ?
            ", [$base_price, $id, $this->currentEventId]);

            if (!$result) {
                throw new \Exception('Failed to restore player');
            }

            $this->db->transCommit();
            return redirect()->to('/admin/players')->with('success', 'Player restored successfully with current base price ₹' . number_format($base_price));

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to restore player: ' . $e->getMessage());
        }
    }

    // Additional helper method to get current auction settings for AJAX calls
    public function getSettings()
    {
        $settings = $this->getAuctionSettings();
        return $this->response->setJSON($settings);
    }

    // Method to bulk update ALL active players base prices when settings change - THIS IS THE KEY METHOD
    public function updateAllPlayersBasePrices($newBasePrice = null)
    {
        if ($newBasePrice === null) {
            $settings = $this->getAuctionSettings();
            $newBasePrice = (float) $settings['base_price'];
        } else {
            $newBasePrice = (float) $newBasePrice;
        }

        $this->db->transBegin();

        try {
            // Update ALL active players with new base price for current event
            $result = $this->db->query("
                UPDATE players 
                SET base_price = ?, updated_at = NOW() 
                WHERE is_active = 1 AND event_id = ?
            ", [$newBasePrice, $this->currentEventId]);

            if (!$result) {
                throw new \Exception('Failed to update base prices for all players');
            }

            $affectedRows = $this->db->affectedRows();

            $this->db->transCommit();
            
            return [
                'success' => true,
                'message' => "Updated base price for {$affectedRows} active players to ₹" . number_format($newBasePrice),
                'affected_rows' => $affectedRows,
                'new_base_price' => $newBasePrice
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            return [
                'success' => false,
                'message' => 'Failed to update base prices: ' . $e->getMessage()
            ];
        }
    }

    // Method to be called from Admin controller after saving auction settings
    public static function updatePlayersBasePriceFromSettings($newBasePrice, $eventId)
    {
        $playerController = new Player();
        $playerController->currentEventId = $eventId; // Set event ID for static call
        return $playerController->updateAllPlayersBasePrices($newBasePrice);
    }

    // Method for AJAX calls to update base prices
    public function updateBasePrices()
    {
        $settings = $this->getAuctionSettings();
        $newBasePrice = (float) $settings['base_price'];

        $result = $this->updateAllPlayersBasePrices($newBasePrice);
        
        return $this->response->setJSON($result);
    }

    // Auto-sync all players base price when index is loaded (optional trigger)
    public function syncAllBasePrices()
    {
        $settings = $this->getAuctionSettings();
        $currentBasePrice = (float) $settings['base_price'];

        // Check if any player has different base_price than current setting for current event
        $sql = "SELECT COUNT(*) as count FROM players WHERE is_active = 1 AND base_price != ? AND event_id = ?";
        $result = $this->db->query($sql, [$currentBasePrice, $this->currentEventId])->getRowArray();

        if ($result['count'] > 0) {
            // Auto-update all players to match current setting for current event
            $this->db->query("UPDATE players SET base_price = ?, updated_at = NOW() WHERE is_active = 1 AND event_id = ?", [$currentBasePrice, $this->currentEventId]);
        }
    }
}