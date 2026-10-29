<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PlayerModel;
use App\Models\TeamModel;
use App\Models\BidModel;

class Auction extends Controller
{
    protected $db;
    protected $playerModel;
    protected $teamModel;
    protected $bidModel;
    protected $eventId;

    /* ===== Dynamic Auction Settings ===== */
    protected $settings = [];
    protected $basePrice = 500000;            // fallback (₹5 L)
    protected $bidIncrementRules = [];

    public function __construct()
    {
        $this->db          = \Config\Database::connect();
        $this->playerModel = new PlayerModel();
        $this->teamModel   = new TeamModel();
        $this->bidModel    = new BidModel();
        
        // Get event_id from session
        $session = session();
        $this->eventId = $session->get('event_id');
        
        if (!$this->eventId) {
            throw new \Exception('Event ID not found in session. Please log in again.');
        }

        $this->loadAuctionSettings();
    }

    /* ----------------------------------------------------------
     |  SETTINGS
     * ----------------------------------------------------------*/
    private function loadAuctionSettings(): void
    {
        $row = $this->db->table('auction_settings')
                       ->where('event_id', $this->eventId)
                       ->get()
                       ->getRowArray();

        if (!$row) {
            // Minimal safe defaults – prevents hard-crash if migration missing
            $row = [
                'base_price'          => 500000,
                'bid_increment_rules' => '[]',
            ];
        }

        $this->settings         = $row;
        $this->basePrice        = (float) ($row['base_price'] ?? 500000);
        $this->bidIncrementRules = json_decode($row['bid_increment_rules'] ?? '[]', true);
    }

    /* ----------------------------------------------------------
     |  DASHBOARD
     * ----------------------------------------------------------*/
    public function index()
    {
        $sql   = "SELECT team_id, team_name, team_logo, purse FROM teams WHERE is_active = 1 AND event_id = ? ORDER BY team_name ASC";
        $teams = $this->db->query($sql, [$this->eventId])->getResultArray();

        $stats = $this->getAuctionStats();

        return view('admin/auction', [
            'teams'      => $teams,
            'stats'      => $stats,
            'activeMenu' => 'auction',
        ]);
    }

    /* ----------------------------------------------------------
     |  USER-SIDE DISPLAY
     * ----------------------------------------------------------*/
    public function userView()
    {
        return view('auction/user_display');
    }

    /* ----------------------------------------------------------
     |  SEARCH / DETAILS
     * ----------------------------------------------------------*/
    public function searchPlayers()
    {
        $query = $this->request->getGet('q');

        if (empty($query)) {
            return $this->response->setJSON([]);
        }

        $sql = "
            SELECT player_id, player_name, player_role, base_price, profile_pic,
                   chapter_name, company_name, auction_status
            FROM players
            WHERE (player_id LIKE ? OR player_name LIKE ?)
              AND is_active = 1
              AND event_id = ?
              AND auction_status IN ('available','unsold')
            ORDER BY player_id ASC
            LIMIT 10
        ";

        $searchTerm = '%' . $query . '%';
        $players    = $this->db->query($sql, [$searchTerm, $searchTerm, $this->eventId])->getResultArray();

        return $this->response->setJSON($players);
    }

    public function getPlayerDetails($playerId)
    {
        $sql = "
            SELECT p.*, t.team_name AS current_team_name
            FROM players p
            LEFT JOIN teams t ON p.current_team = t.team_id AND t.event_id = ?
            WHERE p.player_id = ?
              AND p.is_active = 1
              AND p.event_id = ?
              AND auction_status IN ('available','unsold')
        ";

        $player = $this->db->query($sql, [$this->eventId, $playerId, $this->eventId])->getRowArray();

        if (!$player) {
            return $this->response->setJSON(['error' => 'Player not found or not available for auction']);
        }

        return $this->response->setJSON($player);
    }

    /* ----------------------------------------------------------
     |  SELECT PLAYER
     * ----------------------------------------------------------*/
    public function selectPlayer()
    {
        $playerId = $this->request->getPost('player_id');

        if (!$playerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Player ID is required']);
        }

        $this->db->transBegin();

        try {
            // Unselect all for this event
            $this->db->query("UPDATE players SET is_selected = 0 WHERE is_active = 1 AND event_id = ?", [$this->eventId]);

            // Select new
            $result = $this->db->query(
                "UPDATE players SET is_selected = 1 WHERE player_id = ? AND is_active = 1 AND event_id = ?",
                [$playerId, $this->eventId]
            );

            if (!$result) {
                throw new \Exception('Failed to select player');
            }

            $player = $this->db->query(
                "SELECT player_id, player_name, player_role, batting_type, bowling_type,
                        base_price, profile_pic, company_name, chapter_name, auction_status,
                        final_price, sold_to
                 FROM players WHERE player_id = ? AND event_id = ?",
                [$playerId, $this->eventId]
            )->getRowArray();

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Player selected for display',
                'player'  => $player,
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ----------------------------------------------------------
     |  CURRENTLY SELECTED PLAYER
     * ----------------------------------------------------------*/
    public function getSelectedPlayer()
    {
        $sql = "
            SELECT p.player_id, p.player_name, p.player_role, p.batting_type, p.bowling_type,
                   p.base_price, p.profile_pic, p.company_name, p.chapter_name, p.auction_status,
                   p.final_price, p.sold_to, p.phone_number, p.current_price, p.business_category,
                   t.team_name, t.team_logo, current_t.team_logo
            FROM players p
            LEFT JOIN teams t         ON p.sold_to     = t.team_id AND t.event_id = ?
            LEFT JOIN teams current_t ON p.current_team = current_t.team_id AND current_t.event_id = ?
            WHERE p.is_selected = 1 AND p.is_active = 1 AND p.event_id = ?
            LIMIT 1
        ";

        $player = $this->db->query($sql, [$this->eventId, $this->eventId, $this->eventId])->getRowArray();

        if (!$player) {
            return $this->response->setJSON(['success' => false, 'message' => 'No player currently selected']);
        }

        return $this->response->setJSON([
            'success' => true,
            'player'  => $player,
        ]);
    }

    public function getCompletedAuctionsCount()
    {
        $result = $this->db->query("
            SELECT COUNT(*) AS completed_count
            FROM players
            WHERE auction_status IN ('sold','unsold') AND is_active = 1 AND is_cap = 0 AND event_id = ?
        ", [$this->eventId])->getRow();

        return $this->response->setJSON([
            'success'         => true,
            'completed_count' => (int) $result->completed_count,
        ]);
    }

    /* ----------------------------------------------------------
     |  TEAM PURSE VALIDATION
     * ----------------------------------------------------------*/
    public function validateTeamPurse()
    {
        $teamId     = $this->request->getPost('team_id');
        $finalPrice = (float) $this->request->getPost('final_price');

        if (!$teamId || $finalPrice <= 0) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Invalid team or price']);
        }

        $team = $this->db->query("
            SELECT team_name, purse, hold_amount
            FROM teams
            WHERE team_id = ? AND is_active = 1 AND event_id = ?
        ", [$teamId, $this->eventId])->getRowArray();

        if (!$team) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Team not found']);
        }

        $availableFromHold = min($this->basePrice, $team['hold_amount']);
        $valid             = $finalPrice <= ($team['purse'] + $availableFromHold);

        return $this->response->setJSON([
            'valid'           => $valid,
            'message'         => $valid
                ? 'Valid'
                : 'Insufficient purse. Available: ₹' . number_format($team['purse']),
            'available_purse' => $team['purse'],
            'team_name'       => $team['team_name'],
        ]);
    }

    /* ----------------------------------------------------------
     |  MARK PLAYER AS SOLD
     * ----------------------------------------------------------*/
    public function markAsSold()
    {
        $playerId   = $this->request->getPost('player_id');
        $teamId     = $this->request->getPost('team_id');
        $finalPrice = (float) $this->request->getPost('final_price');

        if (!$playerId || !$teamId || $finalPrice <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid data provided']);
        }

        $this->db->transBegin();

        try {
            $player = $this->db->query("
                SELECT player_id, player_name, base_price
                FROM players
                WHERE player_id = ? AND is_active = 1 AND event_id = ?
                  AND auction_status IN ('available','unsold')
            ", [$playerId, $this->eventId])->getRowArray();

            if (!$player) {
                throw new \Exception('Player not found or not available for auction');
            }

            if ($finalPrice < $player['base_price']) {
                throw new \Exception(
                    'Final price cannot be less than base price of ₹' . number_format($player['base_price'])
                );
            }

            $team = $this->db->query("
                SELECT team_id, team_name, purse, hold_amount
                FROM teams
                WHERE team_id = ? AND is_active = 1 AND event_id = ?
            ", [$teamId, $this->eventId])->getRowArray();

            if (!$team) {
                throw new \Exception('Team not found');
            }

            // Dynamic hold deduction
            $fromHold  = min($this->basePrice, $finalPrice, $team['hold_amount']);
            $fromPurse = $finalPrice - $fromHold;

            if ($fromPurse > $team['purse']) {
                throw new \Exception(
                    'Insufficient team purse. Available: ₹' . number_format($team['purse']) .
                    ', Needed: ₹' . number_format($fromPurse)
                );
            }

            $newPurse = $team['purse'] - $fromPurse;
            $newHold  = $team['hold_amount'] - $fromHold;

            // Update player
            $this->db->query("
                UPDATE players
                SET sold_to = ?, final_price = ?, auction_status = 'sold', is_unsold = 0
                WHERE player_id = ? AND event_id = ?
            ", [$teamId, $finalPrice, $playerId, $this->eventId]);

            // Update team
            $this->db->query("
                UPDATE teams
                SET purse = ?, hold_amount = ?
                WHERE team_id = ? AND event_id = ?
            ", [$newPurse, $newHold, $teamId, $this->eventId]);

            // Bid record
            $this->db->query("
                INSERT INTO bids (player_id, team_id, bid_amount, event_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", [$playerId, $teamId, $finalPrice, $this->eventId]);

            // Logs
            $this->db->table('auction_logs')->where(['player_id' => $playerId, 'event_id' => $this->eventId])->update(['is_active' => 0]);
            $this->db->table('auction_logs')->insert([
                'player_id'          => $playerId,
                'team_id'            => $teamId,
                'current_bid_amount' => $finalPrice,
                'event_id'           => $this->eventId,
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

            $this->db->transCommit();

            return $this->response->setJSON([
                'success'          => true,
                'message'          => $player['player_name'] . ' sold to ' . $team['team_name'] .
                                      ' for ₹' . number_format($finalPrice),
                'player_name'      => $player['player_name'],
                'team_name'        => $team['team_name'],
                'team_id'          => $teamId,
                'final_price'      => $finalPrice,
                'from_hold'        => $fromHold,
                'from_purse'       => $fromPurse,
                'new_purse'        => $newPurse,
                'new_hold_amount'  => $newHold,
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ----------------------------------------------------------
     |  MARK UNSOLD
     * ----------------------------------------------------------*/
    public function markAsUnsold()
    {
        $playerId = $this->request->getPost('player_id');

        if (!$playerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Player ID is required']);
        }

        $this->db->transBegin();

        try {
            $player = $this->db->query("
                SELECT player_id, player_name
                FROM players
                WHERE player_id = ? AND is_active = 1 AND event_id = ? AND auction_status IN ('available','unsold')
            ", [$playerId, $this->eventId])->getRowArray();

            if (!$player) {
                throw new \Exception('Player not found or not available for auction');
            }

            $this->db->query("
                UPDATE players
                SET auction_status = 'unsold', is_unsold = 1, sold_to = NULL,
                    final_price = NULL, current_team = 0, current_price = 0
                WHERE player_id = ? AND event_id = ?
            ", [$playerId, $this->eventId]);

            $this->db->query("
                INSERT INTO bids (player_id, team_id, bid_amount, event_id, created_at)
                VALUES (?, NULL, 0, ?, NOW())
            ", [$playerId, $this->eventId]);

            $this->db->table('auction_logs')->where(['player_id' => $playerId, 'event_id' => $this->eventId])->update(['is_active' => 0]);
            $this->db->table('auction_logs')->insert([
                'player_id'          => $playerId,
                'team_id'            => null,
                'current_bid_amount' => 0,
                'event_id'           => $this->eventId,
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

            $this->db->transCommit();

            return $this->response->setJSON([
                'success'     => true,
                'message'     => $player['player_name'] . ' marked as unsold',
                'player_name' => $player['player_name'],
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ----------------------------------------------------------
     |  UNDO LAST AUCTION ACTION
     * ----------------------------------------------------------*/
    public function undoLastAction()
    {
        $this->db->transBegin();

        try {
            $lastAction = $this->db->query("
                SELECT b.bid_id, b.player_id, b.team_id, b.bid_amount, b.created_at,
                       p.player_name, t.team_name, t.purse, t.hold_amount
                FROM bids b
                INNER JOIN players p ON b.player_id = p.player_id AND p.event_id = ?
                LEFT JOIN teams t   ON b.team_id  = t.team_id AND t.event_id = ?
                WHERE b.event_id = ?
                ORDER BY b.created_at DESC, b.bid_id DESC
                LIMIT 1
            ", [$this->eventId, $this->eventId, $this->eventId])->getRowArray();

            if (!$lastAction) {
                throw new \Exception('No auction actions found to undo');
            }

            $message = '';

            if ($lastAction['team_id'] !== null) {
                // Undo sold
                $this->db->query("
                    UPDATE players
                    SET sold_to = NULL, final_price = NULL,
                        auction_status = 'available', is_unsold = 0
                    WHERE player_id = ? AND event_id = ?
                ", [$lastAction['player_id'], $this->eventId]);

                $fromHold  = min($this->basePrice, $lastAction['bid_amount']);
                $fromPurse = $lastAction['bid_amount'] - $fromHold;

                $newPurse = $lastAction['purse']       + $fromPurse;
                $newHold  = $lastAction['hold_amount'] + $fromHold;

                $this->db->query("
                    UPDATE teams
                    SET purse = ?, hold_amount = ?
                    WHERE team_id = ? AND event_id = ?
                ", [$newPurse, $newHold, $lastAction['team_id'], $this->eventId]);

                $message = $lastAction['player_name'] . ' returned from ' . $lastAction['team_name'] .
                           ', ₹' . number_format($fromPurse) . ' restored to purse, ₹' .
                           number_format($fromHold) . ' to hold';
            } else {
                // Undo unsold
                $this->db->query("
                    UPDATE players
                    SET auction_status = 'available', is_unsold = 0,
                        sold_to = NULL, final_price = NULL
                    WHERE player_id = ? AND event_id = ?
                ", [$lastAction['player_id'], $this->eventId]);

                $message = $lastAction['player_name'] . ' made available for auction again';
            }

            // Delete bid record
            $this->db->query("DELETE FROM bids WHERE bid_id = ? AND event_id = ?", [$lastAction['bid_id'], $this->eventId]);

            $this->db->transCommit();

            return $this->response->setJSON([
                'success'     => true,
                'message'     => 'Action undone: ' . $message,
                'player_name' => $lastAction['player_name'],
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ----------------------------------------------------------
     |  UNDO AUCTION LAST ACTION
     * ----------------------------------------------------------*/
    public function undoAuctionLastAction()
    {
        $db       = \Config\Database::connect();
        $builder  = $db->table('auction_logs');
        $lastLog  = $builder->where('event_id', $this->eventId)
                           ->orderBy('auction_log_id', 'DESC')
                           ->get(1)
                           ->getRow();

        if (!$lastLog) {
            return $this->response->setJSON(['success' => false, 'message' => 'No log entry found to delete']);
        }

        $playerId = $lastLog->player_id;
        $builder->where(['auction_log_id' => $lastLog->auction_log_id, 'event_id' => $this->eventId])->delete();

        $latestLog = $builder
            ->where(['player_id' => $playerId, 'event_id' => $this->eventId])
            ->orderBy('auction_log_id', 'DESC')
            ->get(1)
            ->getRow();

        $playerModel = new \App\Models\PlayerModel();

        if ($latestLog) {
            $playerModel->where('event_id', $this->eventId)->update($playerId, [
                'current_team'  => $latestLog->team_id,
                'current_price' => $latestLog->current_bid_amount,
            ]);
        } else {
            $playerModel->where('event_id', $this->eventId)->update($playerId, [
                'current_team'  => null,
                'current_price' => 0,
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    /* ----------------------------------------------------------
     |  AUCTION STATS
     * ----------------------------------------------------------*/
    public function getAuctionStats()
    {
        $sql           = "SELECT COUNT(*) AS total FROM players WHERE is_active = 1 AND event_id = ?";
        $totalPlayers  = $this->db->query($sql, [$this->eventId])->getRowArray()['total'];

        $sql           = "SELECT COUNT(*) AS sold FROM players WHERE is_active = 1 AND event_id = ? AND auction_status = 'sold'";
        $soldPlayers   = $this->db->query($sql, [$this->eventId])->getRowArray()['sold'];

        $sql           = "SELECT COUNT(*) AS unsold FROM players WHERE is_active = 1 AND event_id = ? AND auction_status = 'unsold'";
        $unsoldPlayers = $this->db->query($sql, [$this->eventId])->getRowArray()['unsold'];

        $sql           = "SELECT COUNT(*) AS available FROM players WHERE is_active = 1 AND event_id = ? AND auction_status = 'available'";
        $available     = $this->db->query($sql, [$this->eventId])->getRowArray()['available'];

        $sql           = "SELECT COALESCE(SUM(final_price),0) AS total_spent FROM players WHERE is_active = 1 AND event_id = ? AND auction_status = 'sold'";
        $totalSpent    = $this->db->query($sql, [$this->eventId])->getRowArray()['total_spent'];

        $teamStats = $this->db->query("
            SELECT t.team_id, t.team_name, t.team_logo, t.purse,
                   COALESCE(SUM(p.final_price),0) AS spent,
                   COUNT(CASE WHEN p.auction_status='sold' THEN 1 END) AS players_bought
            FROM teams t
            LEFT JOIN players p ON t.team_id = p.sold_to AND p.is_active = 1 AND p.auction_status='sold' AND p.event_id = ?
            WHERE t.is_active = 1 AND t.event_id = ?
            GROUP BY t.team_id, t.team_name, t.team_logo, t.purse
            ORDER BY spent DESC
        ", [$this->eventId, $this->eventId])->getResultArray();

        return [
            'total_players'     => $totalPlayers,
            'sold_players'      => $soldPlayers,
            'unsold_players'    => $unsoldPlayers,
            'available_players' => $available,
            'total_spent'       => $totalSpent,
            'team_stats'        => $teamStats,
        ];
    }

    /* ----------------------------------------------------------
     |  HISTORY
     * ----------------------------------------------------------*/
    public function getHistory()
    {
        $history = $this->db->query("
            SELECT b.bid_id, b.player_id, b.team_id, b.bid_amount, b.created_at,
                   p.player_name, p.player_role, p.base_price, p.final_price,
                   t.team_name, t.team_logo,
                   DATE_FORMAT(b.created_at, '%d/%m/%Y %H:%i:%s') AS formatted_time,
                   CASE
                       WHEN b.team_id IS NOT NULL THEN 'sold'
                       WHEN b.team_id IS NULL AND b.bid_amount = 0 THEN 'unsold'
                       ELSE 'unknown'
                   END AS action_type
            FROM bids b
            INNER JOIN players p ON b.player_id = p.player_id AND p.event_id = ?
            LEFT JOIN teams t ON b.team_id = t.team_id AND t.event_id = ?
            WHERE b.event_id = ?
            ORDER BY b.created_at DESC, b.bid_id DESC
            LIMIT 100
        ", [$this->eventId, $this->eventId, $this->eventId])->getResultArray();

        return $this->response->setJSON($history);
    }

    /* ----------------------------------------------------------
     |  REFRESH TEAM DATA
     * ----------------------------------------------------------*/
    public function refreshTeamData()
    {
        $teams = $this->db->query("
            SELECT team_id, team_name, team_logo, purse
            FROM teams
            WHERE is_active = 1 AND event_id = ?
            ORDER BY team_name ASC
        ", [$this->eventId])->getResultArray();

        return $this->response->setJSON($teams);
    }

    /* ----------------------------------------------------------
     |  DEBUG / HELPERS
     * ----------------------------------------------------------*/
    public function checkPlayerUpdate($playerId)
    {
        $player = $this->db->query("
            SELECT player_id, player_name, auction_status, sold_to,
                   final_price, is_unsold
            FROM players WHERE player_id = ? AND event_id = ?
        ", [$playerId, $this->eventId])->getRowArray();

        return $this->response->setJSON($player);
    }

    public function checkBidRecords($playerId = null)
    {
        $sql = "
            SELECT b.*, p.player_name, t.team_name,
                   CASE
                       WHEN b.team_id IS NOT NULL THEN 'sold'
                       WHEN b.team_id IS NULL AND b.bid_amount = 0 THEN 'unsold'
                       ELSE 'unknown'
                   END AS action_type
            FROM bids b
            LEFT JOIN players p ON b.player_id = p.player_id AND p.event_id = ?
            LEFT JOIN teams t   ON b.team_id  = t.team_id AND t.event_id = ?
            WHERE b.event_id = ?
        ";

        $params = [$this->eventId, $this->eventId, $this->eventId];
        if ($playerId) {
            $sql    .= " AND b.player_id = ?";
            $params[] = $playerId;
        }

        $sql  .= " ORDER BY b.created_at DESC LIMIT 50";
        $bids  = $this->db->query($sql, $params)->getResultArray();

        return $this->response->setJSON($bids);
    }

    public function canUndoLastAction()
    {
        $log = $this->db->table('auction_logs')
            ->where('event_id', $this->eventId)
            ->orderBy('auction_log_id', 'DESC')
            ->get(1)
            ->getRow();

        return $this->response->setJSON(['can_undo' => $log && $log->is_active == 0]);
    }
}