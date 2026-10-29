<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Team extends Controller
{
    protected $db;
    protected $eventId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        // Get event_id from session
        $session = session();
        $this->eventId = $session->get('event_id');

        // Handle different user roles
        $role = $session->get('role');

        // If no event_id in session and not a team user, redirect to login or handle appropriately
        if ($this->eventId === null && $role !== 'team') {
            // You might want to redirect to login or show an error
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No event selected');
        }

        // For team users, get event_id from their team association if not in session
        if ($this->eventId === null && $role === 'team') {
            $teamId = $session->get('team_id');
            if ($teamId) {
                $teamResult = $this->db->query("SELECT event_id FROM teams WHERE team_id = ? AND is_active = 1", [$teamId])->getRowArray();
                if ($teamResult) {
                    $this->eventId = $teamResult['event_id'];
                    // Update session with event_id
                    $session->set('event_id', $this->eventId);
                }
            }
        }
    }

    /**
     * Encode event ID for URL
     */
    private function encodeEventId($eventId)
    {
        // Use base64 encoding with some obfuscation
        $salt = 'cricket_auction_2024'; // Change this to your own salt
        $encoded = base64_encode($eventId . '|' . $salt);
        return str_replace(['=', '+', '/'], ['', '-', '_'], $encoded);
    }

    /**
     * Decode event ID from URL
     */
    private function decodeEventId($encodedId)
    {
        try {
            $salt = 'cricket_auction_2024'; // Same salt as encoding
            $encoded = str_replace(['-', '_'], ['+', '/'], $encodedId);
            // Add padding if needed
            $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);

            $decoded = base64_decode($encoded);
            if ($decoded === false) {
                return false;
            }

            $parts = explode('|', $decoded);
            if (count($parts) !== 2 || $parts[1] !== $salt) {
                return false;
            }

            return (int) $parts[0];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate public URL for teams details
     */
    public function getPublicTeamsUrl()
    {
        $encodedId = $this->encodeEventId($this->eventId);
        return base_url("public/teams/{$encodedId}");
    }

    protected function getAuctionSettings()
    {
        $settings = $this->db->table('auction_settings')->getWhere(['event_id' => $this->eventId])->getRowArray();

        // Return default settings if none exist
        if (!$settings) {
            return [
                'max_purse' => 100000000, // Default ₹10 Crores
                'base_price' => 2000000,  // Default ₹20 Lakhs
                'total_players_per_team' => 11, // Auction players only
                'hold_amount' => 22000000, // Default ₹2.2 Crores
                'number_of_rounds' => 5,
                'players_per_round' => 20,
                'bid_increment_rules' => '[]',
                'event_id' => $this->eventId
            ];
        }

        return $settings;
    }

    /**
     * Generate username from team name and team ID
     */
    private function generateUsername($teamName, $teamId)
    {
        $teamNameLength = strlen($teamName);

        if ($teamNameLength <= 5) {
            // Take full team name + team ID
            return $teamName . $teamId;
        } else {
            // Take first 5 characters + team ID
            return substr($teamName, 0, 5) . $teamId;
        }
    }

    /**
     * Generate password from username
     */
    private function generatePassword($username)
    {
        return $username . 'pass';
    }

    public function index()
    {
        $showDeleted = $this->request->getGet('deleted') === '1';

        $sql = "SELECT t.*, 
                      cp.player_name AS captain_name,
                      op.player_name AS owner_name,
                      (SELECT COUNT(*) FROM players p WHERE p.sold_to = t.team_id AND p.is_active = 1 AND p.is_cap = 0 AND p.event_id = ?) AS auction_players,
                      (SELECT COUNT(*) FROM players p WHERE p.sold_to = t.team_id AND p.is_active = 1 AND p.event_id = ?) AS total_players_with_cap_owner
                FROM teams t
                LEFT JOIN players cp ON t.captain_player_id = cp.player_id AND cp.event_id = ?
                LEFT JOIN players op ON t.owner_player_id = op.player_id AND op.event_id = ?
                WHERE t.is_active = ? AND t.event_id = ?
                ORDER BY t.team_id DESC";
        $teams = $this->db->query($sql, [
            $this->eventId,
            $this->eventId,
            $this->eventId,
            $this->eventId,
            $showDeleted ? 0 : 1,
            $this->eventId
        ])->getResultArray();

        // Get available players (not sold to any team and not captain/owner) for this event
        $sql = "SELECT * FROM players WHERE is_active = 1 AND (sold_to IS NULL OR sold_to = 0) AND is_cap = 0 AND event_id = ? ORDER BY player_name";
        $players = $this->db->query($sql, [$this->eventId])->getResultArray();

        // Get auction settings for display
        $settings = $this->getAuctionSettings();

        // Generate public URL
        $publicUrl = $this->getPublicTeamsUrl();

        return view('admin/teams', [
            'teams' => $teams,
            'players' => $players,
            'showDeleted' => $showDeleted,
            'activeMenu' => 'teams',
            'settings' => $settings,
            'eventId' => $this->eventId,
            'publicTeamsUrl' => $publicUrl
        ]);
    }

    /**
     * Public teams details page with encoded event ID
     */
    public function publicTeamsDetails($encodedEventId = null)
    {
        if (!$encodedEventId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid event ID');
        }

        $eventId = $this->decodeEventId($encodedEventId);
        if (!$eventId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid or expired event ID');
        }

        // Verify event exists and is active
        $event = $this->db->table('events')->getWhere(['event_id' => $eventId, 'is_active' => 1])->getRowArray();
        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Event not found or inactive');
        }

        $sql = "
        SELECT 
            t.team_id,
            t.team_name,
            t.team_logo,
            t.is_active,
            t.purse,
            t.hold_amount,
            t.max_purse,
            t.created_at,
            COUNT(p.player_id) AS player_count
        FROM teams t
        LEFT JOIN players p ON p.sold_to = t.team_id AND p.is_active = 1 AND p.event_id = ?
        WHERE t.is_active = 1 AND t.event_id = ?
        GROUP BY t.team_id, t.team_name, t.team_logo, t.is_active, t.purse, t.hold_amount, t.max_purse, t.created_at
        ORDER BY t.team_name
    ";
        $teams = $this->db->query($sql, [$eventId, $eventId])->getResultArray();

        return view('public/teams_details', [
            'teams' => $teams,
            'eventId' => $eventId,
            'event' => $event,
            'encodedEventId' => $encodedEventId
        ]);
    }

    public function teamsDetails()
    {
        $sql = "
        SELECT 
            t.team_id,
            t.team_name,
            t.team_logo,
            t.is_active,
            t.purse,
            t.hold_amount,
            t.max_purse,
            t.created_at,
            COUNT(p.player_id) AS player_count
        FROM teams t
        LEFT JOIN players p ON p.sold_to = t.team_id AND p.is_active = 1 AND p.event_id = ?
        WHERE t.is_active = 1 AND t.event_id = ?
        GROUP BY t.team_id, t.team_name, t.team_logo, t.is_active, t.purse, t.hold_amount, t.max_purse, t.created_at
        ORDER BY t.team_name
    ";
        $teams = $this->db->query($sql, [$this->eventId, $this->eventId])->getResultArray();
        return view('auction/teams_details', ['teams' => $teams, 'eventId' => $this->eventId]);
    }

    /**
     * STORE - create a new team
     */
    public function store()
    {
        $settings = $this->getAuctionSettings();

        $team_name = $this->request->getPost('team_name');
        $captain_player_id = $this->request->getPost('captain_player_id');
        $owner_player_id = $this->request->getPost('owner_player_id');
        $captain_price = (float) ($this->request->getPost('captain_price') ?: 0);
        $owner_price = (float) ($this->request->getPost('owner_price') ?: 0);

        // Get settings values
        $max_purse = (float) $settings['max_purse'];
        $base_price = (float) $settings['base_price'];
        $total_auction_players = (int) $settings['total_players_per_team']; // Only auction players

        // Calculate hold amount: only for auction players (captain/owner prices not included)
        $hold_amount = $total_auction_players * $base_price;

        // Calculate available purse: max_purse - hold_amount - captain_price - owner_price
        $available_purse = $max_purse - $hold_amount - $captain_price - $owner_price;

        // Validation
        if (empty($team_name)) {
            return redirect()->back()->with('error', 'Team name is required');
        }

        if (empty($captain_player_id)) {
            return redirect()->back()->with('error', 'Captain is required');
        }

        if (empty($owner_player_id)) {
            return redirect()->back()->with('error', 'Owner is required');
        }

        if ($captain_price < 0) {
            return redirect()->back()->with('error', 'Captain price must be equal to or greater than 0');
        }

        if ($owner_price < 0) {
            return redirect()->back()->with('error', 'Owner price must be equal to or greater than 0');
        }

        if ($available_purse < 0) {
            return redirect()->back()->with('error', 'Total captain and owner prices exceed available budget');
        }

        // Validate that captain and owner are different
        if ($captain_player_id && $owner_player_id && $captain_player_id == $owner_player_id) {
            return redirect()->back()->with('error', 'Captain and Owner cannot be the same player');
        }

        // Check if players are already assigned to other teams in this event
        if ($captain_player_id) {
            $sql = "SELECT COUNT(*) as count FROM teams WHERE (captain_player_id = ? OR owner_player_id = ?) AND is_active = 1 AND event_id = ?";
            $result = $this->db->query($sql, [$captain_player_id, $captain_player_id, $this->eventId])->getRowArray();
            if ($result['count'] > 0) {
                return redirect()->back()->with('error', 'Selected captain is already assigned to another team');
            }
        }
        if ($owner_player_id) {
            $sql = "SELECT COUNT(*) as count FROM teams WHERE (captain_player_id = ? OR owner_player_id = ?) AND is_active = 1 AND event_id = ?";
            $result = $this->db->query($sql, [$owner_player_id, $owner_player_id, $this->eventId])->getRowArray();
            if ($result['count'] > 0) {
                return redirect()->back()->with('error', 'Selected owner is already assigned to another team');
            }
        }

        // Handle team logo upload
        $team_logo = null;
        $files = $this->request->getFiles();
        $file = isset($files['team_logo']) ? $files['team_logo'] : null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $uploadPath = FCPATH . 'uploads/teams';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $newName);
            $team_logo = $newName;
        }

        $this->db->transBegin();

        try {
            // Insert team with event_id
            $sql = "INSERT INTO teams (team_name, captain_player_id, owner_player_id, max_purse, hold_amount, purse, team_logo, event_id, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
            $result = $this->db->query($sql, [
                $team_name,
                $captain_player_id,
                $owner_player_id,
                $max_purse,
                $hold_amount,
                $available_purse,
                $team_logo,
                $this->eventId
            ]);

            if (!$result) {
                throw new \Exception('Failed to create team');
            }

            $team_id = $this->db->insertID();

            // Generate username and password for team login
            $username = $this->generateUsername($team_name, $team_id);
            $password = $this->generatePassword($username);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user record for team using UserModel
            $userModel = new \App\Models\UserModel();
            $userResult = $userModel->createTeamUser($team_id, $this->eventId, $username, $password);

            if (!$userResult) {
                throw new \Exception('Failed to create team user account');
            }

            // Update captain player (marked as sold, is_cap = 1)
            if ($captain_player_id) {
                $sql = "UPDATE players SET sold_to = ?, final_price = ?, auction_status = 'sold', is_cap = 1, updated_at = NOW() WHERE player_id = ? AND event_id = ?";
                $this->db->query($sql, [$team_id, $captain_price, $captain_player_id, $this->eventId]);
            }

            // Update owner player (marked as sold, is_cap = 1)
            if ($owner_player_id) {
                $sql = "UPDATE players SET sold_to = ?, final_price = ?, auction_status = 'sold', is_cap = 1, updated_at = NOW() WHERE player_id = ? AND event_id = ?";
                $this->db->query($sql, [$team_id, $owner_price, $owner_player_id, $this->eventId]);
            }

            $this->db->transCommit();

            return redirect()->to('/admin/teams')->with(
                'success',
                'Team added successfully. Hold Amount: ₹' . number_format($hold_amount, 0, '.', ',') .
                ', Available Purse: ₹' . number_format($available_purse, 0, '.', ',') .
                '. Team login credentials - Username: ' . $username . ', Password: ' . $password
            );
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to add team: ' . $e->getMessage());
        }
    }

    /**
     * DELETE - soft delete team and reset players
     */
    public function delete($id)
    {
        $this->db->transBegin();

        try {
            $sql = "SELECT * FROM teams WHERE team_id = ? AND event_id = ?";
            $team = $this->db->query($sql, [$id, $this->eventId])->getRowArray();

            if (!$team) {
                throw new \Exception('Team not found');
            }

            // Reset all players associated with this team in this event
            $sql = "UPDATE players SET sold_to = NULL, final_price = NULL, auction_status = 'available', is_cap = 0, updated_at = NOW() WHERE sold_to = ? AND event_id = ?";
            $this->db->query($sql, [$id, $this->eventId]);

            // Deactivate team user account using UserModel
            $userModel = new \App\Models\UserModel();
            $userModel->deactivateUsersByTeam($id);

            // Soft delete team
            $sql = "UPDATE teams SET is_active = 0, updated_at = NOW() WHERE team_id = ? AND event_id = ?";
            $result = $this->db->query($sql, [$id, $this->eventId]);

            if (!$result) {
                throw new \Exception('Failed to delete team');
            }

            $this->db->transCommit();
            return redirect()->to('/admin/teams')->with('success', 'Team deleted successfully');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to delete team: ' . $e->getMessage());
        }
    }

    /**
     * RESTORE - bring back a soft-deleted team
     */
    public function restore($id)
    {
        $this->db->transBegin();

        try {
            $result = $this->db->query("UPDATE teams SET is_active = 1, updated_at = NOW() WHERE team_id = ? AND event_id = ?", [$id, $this->eventId]);

            if (!$result) {
                throw new \Exception('Failed to restore team');
            }

            // Reactivate team user account using UserModel
            $userModel = new \App\Models\UserModel();
            $userModel->activateUsersByTeam($id);

            $this->db->transCommit();
            return redirect()->to('/admin/teams')->with('success', 'Team restored successfully');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to restore team: ' . $e->getMessage());
        }
    }

    /**
     * EXPORT - pdf or excel
     */
    public function export($type = 'pdf')
    {
        $sql = "SELECT t.*, 
                      cp.player_name AS captain_name,
                      op.player_name AS owner_name,
                      (SELECT COUNT(*) FROM players p WHERE p.sold_to = t.team_id AND p.is_active = 1 AND p.is_cap = 0 AND p.event_id = ?) AS auction_players,
                      (SELECT COUNT(*) FROM players p WHERE p.sold_to = t.team_id AND p.is_active = 1 AND p.event_id = ?) AS total_players,
                      (SELECT COALESCE(SUM(p.final_price), 0) FROM players p WHERE p.sold_to = t.team_id AND p.is_active = 1 AND p.event_id = ?) AS total_spent
                FROM teams t
                LEFT JOIN players cp ON t.captain_player_id = cp.player_id AND cp.event_id = ?
                LEFT JOIN players op ON t.owner_player_id = op.player_id AND op.event_id = ?
                WHERE t.is_active = 1 AND t.event_id = ?
                ORDER BY t.team_name";
        $teams = $this->db->query($sql, [
            $this->eventId,
            $this->eventId,
            $this->eventId,
            $this->eventId,
            $this->eventId,
            $this->eventId
        ])->getResultArray();

        if ($type === 'pdf') {
            $html = '<h3>Team List</h3><table border="1" cellspacing="0" cellpadding="5">
                    <tr>
                        <th>Team Name</th><th>Captain</th><th>Owner</th><th>Auction Players</th><th>Total Players</th>
                        <th>Hold Amount</th><th>Available Purse</th><th>Total Spent</th><th>Max Purse</th>
                    </tr>';
            foreach ($teams as $team) {
                $html .= '<tr>
                        <td>' . esc($team['team_name']) . '</td>
                        <td>' . esc($team['captain_name'] ?? '-') . '</td>
                        <td>' . esc($team['owner_name'] ?? '-') . '</td>
                        <td>' . number_format($team['auction_players'], 0, '.', ',') . '</td>
                        <td>' . number_format($team['total_players'], 0, '.', ',') . '</td>
                        <td>₹' . number_format($team['hold_amount'], 0, '.', ',') . '</td>
                        <td>₹' . number_format($team['purse'], 0, '.', ',') . '</td>
                        <td>₹' . number_format($team['total_spent'], 0, '.', ',') . '</td>
                        <td>₹' . number_format($team['max_purse'], 0, '.', ',') . '</td>
                    </tr>';
            }
            $html .= '</table>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'teams_' . date('Ymd_His') . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit;
        } elseif ($type === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Team Name')
                ->setCellValue('B1', 'Captain')
                ->setCellValue('C1', 'Owner')
                ->setCellValue('D1', 'Auction Players')
                ->setCellValue('E1', 'Total Players')
                ->setCellValue('F1', 'Hold Amount')
                ->setCellValue('G1', 'Available Purse')
                ->setCellValue('H1', 'Total Spent')
                ->setCellValue('I1', 'Max Purse');

            $row = 2;
            foreach ($teams as $team) {
                $sheet->setCellValue('A' . $row, $team['team_name'])
                    ->setCellValue('B' . $row, $team['captain_name'] ?? '-')
                    ->setCellValue('C' . $row, $team['owner_name'] ?? '-')
                    ->setCellValue('D' . $row, $team['auction_players'])
                    ->setCellValue('E' . $row, $team['total_players'])
                    ->setCellValue('F' . $row, $team['hold_amount'])
                    ->setCellValue('G' . $row, $team['purse'])
                    ->setCellValue('H' . $row, $team['total_spent'])
                    ->setCellValue('I' . $row, $team['max_purse']);
                $row++;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="teams.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Invalid export type: " . esc($type));
        }
    }

    // Additional helper method to recalculate team finances when settings change
    public function recalculateTeamFinances()
    {
        $settings = $this->getAuctionSettings();
        $max_purse = (float) $settings['max_purse'];
        $base_price = (float) $settings['base_price'];
        $total_auction_players = (int) $settings['total_players_per_team'];

        $this->db->transBegin();

        try {
            $teams = $this->db->query("SELECT * FROM teams WHERE is_active = 1 AND event_id = ?", [$this->eventId])->getResultArray();

            foreach ($teams as $team) {
                // Calculate hold amount for auction players only
                $hold_amount = $total_auction_players * $base_price;

                // Get captain and owner prices
                $captain_price = 0;
                $owner_price = 0;

                if (!empty($team['captain_player_id'])) {
                    $result = $this->db->query("SELECT final_price FROM players WHERE player_id = ? AND event_id = ?", [$team['captain_player_id'], $this->eventId])->getRowArray();
                    $captain_price = (float) ($result['final_price'] ?? 0);
                }

                if (!empty($team['owner_player_id'])) {
                    $result = $this->db->query("SELECT final_price FROM players WHERE player_id = ? AND event_id = ?", [$team['owner_player_id'], $this->eventId])->getRowArray();
                    $owner_price = (float) ($result['final_price'] ?? 0);
                }

                // Get spending on auctioned players
                $spent_result = $this->db->query("SELECT COALESCE(SUM(final_price), 0) as spent FROM players WHERE sold_to = ? AND is_active = 1 AND is_cap = 0 AND event_id = ?", [$team['team_id'], $this->eventId])->getRowArray();
                $spent_on_players = (float) $spent_result['spent'];

                // Calculate new purse
                $available_purse = $max_purse - $hold_amount - $captain_price - $owner_price - $spent_on_players;

                // Update team
                $this->db->query("
                    UPDATE teams 
                    SET max_purse = ?, hold_amount = ?, purse = ?, updated_at = NOW() 
                    WHERE team_id = ? AND event_id = ?
                ", [$max_purse, $hold_amount, $available_purse, $team['team_id'], $this->eventId]);
            }

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Team finances recalculated successfully for ' . count($teams) . ' teams'
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to recalculate team finances: ' . $e->getMessage()
            ]);
        }
    }

    // Method to get available players for editing including current captain/owner
    public function getAvailablePlayers($teamId)
    {
        // Get available players (not sold to any team and not captain/owner, excluding current team's players) for this event
        $sql = "SELECT player_id, player_name 
                FROM players 
                WHERE is_active = 1 
                AND (sold_to IS NULL OR sold_to = 0) 
                AND is_cap = 0 
                AND event_id = ?
                ORDER BY player_name";
        $availablePlayers = $this->db->query($sql, [$this->eventId])->getResultArray();

        // Get current team's captain and owner details
        $sql = "SELECT t.captain_player_id, t.owner_player_id,
                       cp.player_name AS captain_name,
                       op.player_name AS owner_name
                FROM teams t
                LEFT JOIN players cp ON t.captain_player_id = cp.player_id AND cp.event_id = ?
                LEFT JOIN players op ON t.owner_player_id = op.player_id AND op.event_id = ?
                WHERE t.team_id = ? AND t.event_id = ?";
        $teamData = $this->db->query($sql, [$this->eventId, $this->eventId, $teamId, $this->eventId])->getRowArray();

        // Add current captain and owner to available list if they exist
        if (!empty($teamData['captain_player_id']) && !empty($teamData['captain_name'])) {
            $availablePlayers[] = [
                'player_id' => $teamData['captain_player_id'],
                'player_name' => $teamData['captain_name']
            ];
        }

        if (!empty($teamData['owner_player_id']) && !empty($teamData['owner_name'])) {
            $availablePlayers[] = [
                'player_id' => $teamData['owner_player_id'],
                'player_name' => $teamData['owner_name']
            ];
        }

        // Remove duplicates if captain/owner appear in both lists
        $uniquePlayers = [];
        $seenIds = [];
        foreach ($availablePlayers as $player) {
            if (!in_array($player['player_id'], $seenIds)) {
                $uniquePlayers[] = $player;
                $seenIds[] = $player['player_id'];
            }
        }

        // Sort by player name
        usort($uniquePlayers, function ($a, $b) {
            return strcmp($a['player_name'], $b['player_name']);
        });

        return $this->response->setJSON([
            'availablePlayers' => $uniquePlayers,
            'currentCaptain' => $teamData['captain_player_id'] ?? null,
            'currentOwner' => $teamData['owner_player_id'] ?? null
        ]);
    }

    // Get team info for edit modal/ajax
    public function edit($id)
    {
        $sql = "SELECT t.*, 
                      cp.player_name AS captain_name,
                      op.player_name AS owner_name
                FROM teams t
                LEFT JOIN players cp ON t.captain_player_id = cp.player_id AND cp.event_id = ?
                LEFT JOIN players op ON t.owner_player_id = op.player_id AND op.event_id = ?
                WHERE t.team_id = ? AND t.event_id = ?";
        $team = $this->db->query($sql, [$this->eventId, $this->eventId, $id, $this->eventId])->getRowArray();

        if (!$team) {
            return $this->response->setJSON(['error' => 'Team not found']);
        }

        // Get captain and owner prices
        if (!empty($team['captain_player_id'])) {
            $sql = "SELECT final_price FROM players WHERE player_id = ? AND event_id = ?";
            $captain_price = $this->db->query($sql, [$team['captain_player_id'], $this->eventId])->getRowArray();
            $team['captain_price'] = $captain_price['final_price'] ?? 0;
        } else {
            $team['captain_price'] = 0;
        }

        if (!empty($team['owner_player_id'])) {
            $sql = "SELECT final_price FROM players WHERE player_id = ? AND event_id = ?";
            $owner_price = $this->db->query($sql, [$team['owner_player_id'], $this->eventId])->getRowArray();
            $team['owner_price'] = $owner_price['final_price'] ?? 0;
        } else {
            $team['owner_price'] = 0;
        }

        return $this->response->setJSON($team);
    }

    /**
     * UPDATE - update an existing team
     */
    public function update($id)
    {
        $settings = $this->getAuctionSettings();

        $team_name = $this->request->getPost('team_name');
        $captain_player_id = $this->request->getPost('captain_player_id');
        $owner_player_id = $this->request->getPost('owner_player_id');
        $captain_price = (float) ($this->request->getPost('captain_price') ?: 0);
        $owner_price = (float) ($this->request->getPost('owner_price') ?: 0);

        // Get current team data
        $sql = "SELECT * FROM teams WHERE team_id = ? AND event_id = ?";
        $current_team = $this->db->query($sql, [$id, $this->eventId])->getRowArray();

        if (!$current_team) {
            return redirect()->back()->with('error', 'Team not found');
        }

        // Validation
        if (empty($team_name)) {
            return redirect()->back()->with('error', 'Team name is required');
        }

        if (empty($captain_player_id)) {
            return redirect()->back()->with('error', 'Captain is required');
        }

        if (empty($owner_player_id)) {
            return redirect()->back()->with('error', 'Owner is required');
        }

        if ($captain_price < 0) {
            return redirect()->back()->with('error', 'Captain price must be equal to or greater than 0');
        }

        if ($owner_price < 0) {
            return redirect()->back()->with('error', 'Owner price must be equal to or greater than 0');
        }

        // Validate that captain and owner are different
        if ($captain_player_id && $owner_player_id && $captain_player_id == $owner_player_id) {
            return redirect()->back()->with('error', 'Captain and Owner cannot be the same player');
        }

        // Check if players are already assigned to other teams (excluding current team) in this event
        if ($captain_player_id && $captain_player_id != $current_team['captain_player_id']) {
            $sql = "SELECT COUNT(*) as count FROM teams WHERE (captain_player_id = ? OR owner_player_id = ?) AND is_active = 1 AND team_id != ? AND event_id = ?";
            $result = $this->db->query($sql, [$captain_player_id, $captain_player_id, $id, $this->eventId])->getRowArray();
            if ($result['count'] > 0) {
                return redirect()->back()->with('error', 'Selected captain is already assigned to another team');
            }
        }
        if ($owner_player_id && $owner_player_id != $current_team['owner_player_id']) {
            $sql = "SELECT COUNT(*) as count FROM teams WHERE (captain_player_id = ? OR owner_player_id = ?) AND is_active = 1 AND team_id != ? AND event_id = ?";
            $result = $this->db->query($sql, [$owner_player_id, $owner_player_id, $id, $this->eventId])->getRowArray();
            if ($result['count'] > 0) {
                return redirect()->back()->with('error', 'Selected owner is already assigned to another team');
            }
        }

        // Calculate new hold amount and purse using dynamic settings
        $max_purse = (float) $settings['max_purse'];
        $base_price = (float) $settings['base_price'];
        $total_auction_players = (int) $settings['total_players_per_team'];

        // Calculate hold amount: only for auction players
        $hold_amount = $total_auction_players * $base_price;

        // Get current spending on auctioned players (excluding captain/owner)
        $sql = "SELECT COALESCE(SUM(final_price), 0) as spent FROM players WHERE sold_to = ? AND is_active = 1 AND is_cap = 0 AND event_id = ?";
        $spent_result = $this->db->query($sql, [$id, $this->eventId])->getRowArray();
        $spent_on_players = (float) $spent_result['spent'];

        // Calculate available purse
        $available_purse = $max_purse - $hold_amount - $captain_price - $owner_price - $spent_on_players;

        if ($available_purse < 0) {
            return redirect()->back()->with('error', 'Total captain and owner prices plus current spending exceed available budget');
        }

        $team_logo = null;
        $files = $this->request->getFiles();
        $file = isset($files['team_logo']) ? $files['team_logo'] : null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $uploadPath = FCPATH . 'uploads/teams';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $newName);
            $team_logo = $newName;
        }

        $this->db->transBegin();

        try {
            // Check if team name has changed - if yes, update username using UserModel
            $updateUsername = false;
            $newUsername = null;
            $newPassword = null;

            if ($current_team['team_name'] !== $team_name) {
                $newUsername = $this->generateUsername($team_name, $id);
                $newPassword = $this->generatePassword($newUsername);
                $updateUsername = true;
            }

            // Reset previous captain and owner if changed
            if (!empty($current_team['captain_player_id']) && $current_team['captain_player_id'] != $captain_player_id) {
                $sql = "UPDATE players SET sold_to = NULL, final_price = NULL, auction_status = 'available', is_cap = 0, updated_at = NOW() WHERE player_id = ? AND event_id = ?";
                $this->db->query($sql, [$current_team['captain_player_id'], $this->eventId]);
            }
            if (!empty($current_team['owner_player_id']) && $current_team['owner_player_id'] != $owner_player_id) {
                $sql = "UPDATE players SET sold_to = NULL, final_price = NULL, auction_status = 'available', is_cap = 0, updated_at = NOW() WHERE player_id = ? AND event_id = ?";
                $this->db->query($sql, [$current_team['owner_player_id'], $this->eventId]);
            }

            // Update team
            $sql = "UPDATE teams SET
                        team_name = ?,
                        captain_player_id = ?,
                        owner_player_id = ?,
                        max_purse = ?,
                        hold_amount = ?,
                        purse = ?,
                        updated_at = NOW()";
            if ($team_logo) {
                $sql .= ", team_logo = ?";
            }
            $sql .= " WHERE team_id = ? AND event_id = ?";

            $params = [
                $team_name,
                $captain_player_id,
                $owner_player_id,
                $max_purse,
                $hold_amount,
                $available_purse
            ];
            if ($team_logo) {
                $params[] = $team_logo;
            }
            $params[] = $id;
            $params[] = $this->eventId;

            $result = $this->db->query($sql, $params);

            if (!$result) {
                throw new \Exception('Failed to update team');
            }

            // Update team user credentials if team name changed using UserModel
            if ($updateUsername) {
                $userModel = new \App\Models\UserModel();
                $userModel->updateTeamCredentials($id, $newUsername, $newPassword);
            }

            // Update new captain player
            if ($captain_player_id) {
                $sql = "UPDATE players SET sold_to = ?, final_price = ?, auction_status = 'sold', is_cap = 1, updated_at = NOW() WHERE player_id = ? AND event_id = ?";
                $this->db->query($sql, [$id, $captain_price, $captain_player_id, $this->eventId]);
            }

            // Update new owner player
            if ($owner_player_id) {
                $sql = "UPDATE players SET sold_to = ?, final_price = ?, auction_status = 'sold', is_cap = 1, updated_at = NOW() WHERE player_id = ? AND event_id = ?";
                $this->db->query($sql, [$id, $owner_price, $owner_player_id, $this->eventId]);
            }

            $this->db->transCommit();

            $successMessage = 'Team updated successfully';
            if ($updateUsername) {
                $successMessage .= '. New login credentials - Username: ' . $newUsername . ', Password: ' . $newPassword;
            }

            return redirect()->to('/admin/teams')->with('success', $successMessage);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Failed to update team: ' . $e->getMessage());
        }
    }
    /**
     * Team account page - Shows team's own players and auction details
     */
    public function account()
    {
        // Get team_id from session
        $session = session();
        $teamId = $session->get('team_id');
        $eventId = $session->get('event_id');
        $role = $session->get('role');

        // Ensure this is a team user
        if ($role !== 'team' || !$teamId) {
            return redirect()->to('/login')->with('error', 'Access denied');
        }

        return view('Team/teamaccount', [
            'teamId' => $teamId,
            'eventId' => $eventId
        ]);
    }

    /**
     * Get team data for logged-in team
     */
    public function getTeamData()
    {
        $session = session();
        $teamId = $session->get('team_id');
        $eventId = $session->get('event_id');
        $role = $session->get('role');

        if ($role !== 'team' || !$teamId) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        // Get team details
        $sql = "SELECT t.*, 
                      cp.player_name AS captain_name,
                      op.player_name AS owner_name
                FROM teams t
                LEFT JOIN players cp ON t.captain_player_id = cp.player_id AND cp.event_id = ?
                LEFT JOIN players op ON t.owner_player_id = op.player_id AND op.event_id = ?
                WHERE t.team_id = ? AND t.event_id = ? AND t.is_active = 1";

        $team = $this->db->query($sql, [$eventId, $eventId, $teamId, $eventId])->getRowArray();

        if (!$team) {
            return $this->response->setJSON(['error' => 'Team not found']);
        }

        // Get team logo URL
        if ($team['team_logo']) {
            $team['team_logo_url'] = base_url('uploads/teams/' . $team['team_logo']);
        } else {
            $team['team_logo_url'] = base_url('assets/images/default-team.png');
        }

        return $this->response->setJSON([
            'success' => true,
            'team' => $team
        ]);
    }

    /**
     * Get team players for logged-in team
     */
    public function getTeamPlayers()
    {
        $session = session();
        $teamId = $session->get('team_id');
        $eventId = $session->get('event_id');
        $role = $session->get('role');

        if ($role !== 'team' || !$teamId) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $sql = "SELECT p.player_id, p.player_name, p.player_role, p.final_price, p.is_cap, p.profile_pic, p.cricheroes_url
            FROM players p
            WHERE p.sold_to = ? AND p.event_id = ? AND p.is_active = 1
            ORDER BY p.is_cap DESC, p.final_price DESC, p.player_name";

        $players = $this->db->query($sql, [$teamId, $eventId])->getResultArray();

        // Add profile picture URLs
        foreach ($players as &$player) {
            if ($player['profile_pic']) {
                $player['profile_pic_url'] = base_url('uploads/players/' . $player['profile_pic']);
            } else {
                $player['profile_pic_url'] = base_url('assets/images/default-player.png');
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'players' => $players
        ]);
    }

    /**
     * Get available players for team view
     */
    public function getAvailablePlayersForTeam()
    {
        $session = session();
        $eventId = $session->get('event_id');
        $role = $session->get('role');

        if ($role !== 'team') {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $sql = "SELECT p.player_id, p.player_name, p.player_role, p.base_price, p.profile_pic, p.cricheroes_url
            FROM players p
            WHERE p.event_id = ? AND p.is_active = 1 
            AND (p.sold_to IS NULL OR p.sold_to = 0) 
            AND p.is_cap = 0
            AND p.auction_status = 'available'
            ORDER BY p.player_name";

        $players = $this->db->query($sql, [$eventId])->getResultArray();

        // Add profile picture URLs
        foreach ($players as &$player) {
            if ($player['profile_pic']) {
                $player['profile_pic_url'] = base_url('uploads/players/' . $player['profile_pic']);
            } else {
                $player['profile_pic_url'] = base_url('assets/images/default-player.png');
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'players' => $players
        ]);
    }

    /**
     * Get unsold players for team view
     */
    public function getUnsoldPlayersForTeam()
    {
        $session = session();
        $eventId = $session->get('event_id');
        $role = $session->get('role');

        if ($role !== 'team') {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $sql = "SELECT p.player_id, p.player_name, p.player_role, p.base_price, p.profile_pic, p.cricheroes_url
            FROM players p
            WHERE p.event_id = ? AND p.is_active = 1 
            AND p.auction_status = 'unsold'
            AND p.is_cap = 0
            ORDER BY p.player_name";

        $players = $this->db->query($sql, [$eventId])->getResultArray();

        // Add profile picture URLs
        foreach ($players as &$player) {
            if ($player['profile_pic']) {
                $player['profile_pic_url'] = base_url('uploads/players/' . $player['profile_pic']);
            } else {
                $player['profile_pic_url'] = base_url('assets/images/default-player.png');
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'players' => $players
        ]);
    }
}