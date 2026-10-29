<?php

namespace App\Models;

use CodeIgniter\Model;

class TeamModel extends Model
{
    protected $table = 'teams';
    protected $primaryKey = 'team_id';
    protected $allowedFields = [
        'team_name',
        'team_logo',
        'purse',
        'max_purse',
        'hold_amount',
        'captain_player_id',
        'owner_player_id',
        'event_id',
        'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $eventId;

    public function __construct()
    {
        parent::__construct();

        // Get event_id from session
        $session = session();
        $this->eventId = $session->get('event_id');
    }

    // Override find methods to include event_id filter
    public function find($id = null)
    {
        if ($id !== null) {
            return $this->where('team_id', $id)
                ->where('event_id', $this->eventId)
                ->first();
        }

        return $this->where('event_id', $this->eventId)->findAll();
    }

    public function findAll(int $limit = 0, int $offset = 0)
    {
        return $this->where('event_id', $this->eventId)
            ->findAll($limit, $offset);
    }

    public function where($key, $value = null, bool $escape = null)
    {
        parent::where($key, $value, $escape);

        // Always add event_id filter if not already added
        if ($key !== 'event_id' && !$this->hasEventIdFilter()) {
            parent::where('event_id', $this->eventId);
        }

        return $this;
    }

    // Check if event_id filter is already applied
    private function hasEventIdFilter()
    {
        $whereClause = $this->builder()->getCompiledSelect();
        return strpos($whereClause, 'event_id') !== false;
    }

    // Get teams for specific event
    public function getTeamsByEvent($eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        return $this->where('event_id', $eventId)
            ->where('is_active', 1)
            ->findAll();
    }

    // Get team composition with player counts filtered by event
    public function getTeamComposition($eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        $teams = $this->where('event_id', $eventId)
            ->where('is_active', 1)
            ->findAll();

        $playerModel = new PlayerModel();

        foreach ($teams as &$team) {
            $players = $playerModel->where('sold_to', $team['team_id'])
                ->where('event_id', $eventId)
                ->where('is_active', 1)
                ->findAll();

            $team['total_players'] = count($players);
            $team['batsmen'] = count(array_filter($players, fn($p) => $p['player_role'] === 'batsman'));
            $team['bowlers'] = count(array_filter($players, fn($p) => $p['player_role'] === 'bowler'));
            $team['allrounders'] = count(array_filter($players, fn($p) => $p['player_role'] === 'allrounder'));
            $team['wicketkeepers'] = count(array_filter($players, fn($p) => $p['player_role'] === 'wicketkeeper'));
        }

        return $teams;
    }

    // Get team with player details
    public function getTeamWithPlayers($teamId, $eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;

        $team = $this->where('team_id', $teamId)
            ->where('event_id', $eventId)
            ->first();

        if ($team) {
            $playerModel = new PlayerModel();
            $team['players'] = $playerModel->where('sold_to', $teamId)
                ->where('event_id', $eventId)
                ->where('is_active', 1)
                ->findAll();
        }

        return $team;
    }

    // Get team statistics
    public function getTeamStats($eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        $db = \Config\Database::connect();

        $sql = "SELECT 
                    t.team_id,
                    t.team_name,
                    t.purse,
                    t.max_purse,
                    t.hold_amount,
                    COUNT(p.player_id) as total_players,
                    COUNT(CASE WHEN p.is_cap = '0' THEN 1 END) as auction_players,
                    COUNT(CASE WHEN p.is_cap = '1' THEN 1 END) as captain_owner_count,
                    COALESCE(SUM(p.final_price), 0) as total_spent,
                    COALESCE(SUM(CASE WHEN p.is_cap = '0' THEN p.final_price ELSE 0 END), 0) as auction_spent,
                    COALESCE(SUM(CASE WHEN p.is_cap = '1' THEN p.final_price ELSE 0 END), 0) as captain_owner_spent
                FROM teams t
                LEFT JOIN players p ON t.team_id = p.sold_to AND p.is_active = 1 AND p.event_id = ?
                WHERE t.event_id = ? AND t.is_active = 1
                GROUP BY t.team_id, t.team_name, t.purse, t.max_purse, t.hold_amount
                ORDER BY t.team_name";

        return $db->query($sql, [$eventId, $eventId])->getResultArray();
    }

    // Insert with event_id
    public function insert($data = null, bool $returnID = true)
    {
        if (is_array($data) && !isset($data['event_id'])) {
            $data['event_id'] = $this->eventId;
        }

        return parent::insert($data, $returnID);
    }

    // Update with event_id filter
    public function update($id = null, $data = null): bool
    {
        if ($id !== null) {
            $this->where('team_id', $id)->where('event_id', $this->eventId);
        } else {
            $this->where('event_id', $this->eventId);
        }

        return parent::update($id, $data);
    }

    // Delete with event_id filter (soft delete)
    public function delete($id = null, bool $purge = false)
    {
        if ($purge) {
            return parent::delete($id, $purge);
        }

        // Soft delete - set is_active to 0
        return $this->update($id, ['is_active' => 0]);
    }

    // Restore soft deleted team
    public function restore($id)
    {
        return $this->where('team_id', $id)
            ->where('event_id', $this->eventId)
            ->set(['is_active' => 1])
            ->update();
    }

    // Get deleted teams
    public function getDeletedTeams($eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        return $this->where('event_id', $eventId)
            ->where('is_active', 0)
            ->findAll();
    }

    // Check if team name exists in event
    public function isTeamNameExists($teamName, $excludeTeamId = null, $eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        $builder = $this->where('team_name', $teamName)
            ->where('event_id', $eventId)
            ->where('is_active', 1);

        if ($excludeTeamId) {
            $builder->where('team_id !=', $excludeTeamId);
        }

        return $builder->countAllResults() > 0;
    }

    // Get teams count for event
    public function getTeamCount($eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        return $this->where('event_id', $eventId)
            ->where('is_active', 1)
            ->countAllResults();
    }

    // Get team budget summary
    public function getBudgetSummary($eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        $db = \Config\Database::connect();

        $sql = "SELECT 
                    SUM(max_purse) as total_max_purse,
                    SUM(purse) as total_available_purse,
                    SUM(hold_amount) as total_hold_amount,
                    AVG(purse) as avg_available_purse,
                    COUNT(*) as team_count
                FROM teams 
                WHERE event_id = ? AND is_active = 1";

        return $db->query($sql, [$eventId])->getRowArray();
    }

    // Get team with captain and owner details
    public function getTeamWithCaptainOwnerDetails($teamId, $eventId = null)
    {
        $eventId = $eventId ?? $this->eventId;
        $db = \Config\Database::connect();

        $sql = "SELECT 
                    t.*,
                    cp.player_name as captain_name,
                    cp.profile_pic_url as captain_pic_url,
                    cp.player_role as captain_role,
                    cp.cricheroes_url as captain_cricheroes_url,
                    op.player_name as owner_name,
                    op.profile_pic_url as owner_pic_url,
                    op.player_role as owner_role,
                    op.cricheroes_url as owner_cricheroes_url,
                    CASE 
                        WHEN t.captain_player_id = t.owner_player_id THEN 'same'
                        ELSE 'different'
                    END as captain_owner_status
                FROM teams t
                LEFT JOIN players cp ON t.captain_player_id = cp.player_id 
                    AND cp.event_id = ? AND cp.is_active = 1
                LEFT JOIN players op ON t.owner_player_id = op.player_id 
                    AND op.event_id = ? AND op.is_active = 1
                WHERE t.team_id = ? AND t.event_id = ? AND t.is_active = 1";

        $result = $db->query($sql, [$eventId, $eventId, $teamId, $eventId])->getRowArray();

        if ($result) {
            // Add team_logo_url for consistency with existing code
            if (!empty($result['team_logo'])) {
                $result['team_logo_url'] = base_url('public/uploads/team_logos/' . $result['team_logo']);
            } else {
                $result['team_logo_url'] = base_url('public/assets/default-team-logo.png');
            }
        }

        return $result;
    }
}