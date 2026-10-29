<?php
namespace App\Models;

use CodeIgniter\Model;

class PlayerModel extends Model
{
    protected $table = 'players';
    protected $primaryKey = 'player_id';
    protected $allowedFields = [
        'player_name', 'batting_type', 'bowling_type', 'player_role', 'category', 
        'chapter_name', 'business_category', 'company_name', 'phone_number',
        'profile_pic', 'cricheroes_url', 'base_price', 'final_price','current_price','current_team', 
        'sold_to', 'is_unsold', 'is_cap', 'auction_status', 'is_active', 
        'is_selected', 'event_id'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $session;
    protected $currentEventId;

    public function __construct($db = null, $validation = null)
    {
        parent::__construct($db, $validation);
        
        // Get session and current event ID
        $this->session = \Config\Services::session();
        $this->currentEventId = $this->session->get('event_id');
    }

    // Override find methods to automatically filter by event_id
    public function find($id = null)
    {
        if ($this->currentEventId) {
            $this->where('event_id', $this->currentEventId);
        }
        return parent::find($id);
    }

    public function findAll(int $limit = 0, int $offset = 0)
    {
        if ($this->currentEventId) {
            $this->where('event_id', $this->currentEventId);
        }
        return parent::findAll($limit, $offset);
    }

    // Method to get players with teams for specific event
    public function getPlayersWithTeams($activeOnly = true, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->select('players.*, teams.team_name as sold_to_name')
                        ->join('teams', 'teams.team_id = players.sold_to AND teams.event_id = players.event_id', 'left')
                        ->where('players.event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('players.is_active', true);
        }
        
        return $builder->orderBy('players.player_id', 'DESC')->findAll();
    }
    
    // Get player statistics for current event
    public function getPlayerStats($eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $stats = [];
        $roles = ['batsman', 'bowler', 'allrounder', 'wicketkeeper'];
        
        foreach ($roles as $role) {
            $total = $this->where('player_role', $role)
                         ->where('is_active', true)
                         ->where('event_id', $eventId)
                         ->countAllResults();
                         
            $available = $this->where('player_role', $role)
                             ->where('is_active', true)
                             ->where('auction_status', 'available')
                             ->where('event_id', $eventId)
                             ->countAllResults();
                             
            $sold = $this->where('player_role', $role)
                        ->where('is_active', true)
                        ->where('auction_status', 'sold')
                        ->where('event_id', $eventId)
                        ->countAllResults();
                        
            $unsold = $this->where('player_role', $role)
                          ->where('is_active', true)
                          ->where('auction_status', 'unsold')
                          ->where('event_id', $eventId)
                          ->countAllResults();
            
            $stats[$role] = [
                'total' => $total, 
                'available' => $available,
                'sold' => $sold,
                'unsold' => $unsold
            ];
        }
        
        return $stats;
    }
    
    // Helper methods for different player statuses filtered by event
    public function getAvailablePlayers($activeOnly = true, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->where('auction_status', 'available')
                       ->where('event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    public function getSoldPlayers($activeOnly = true, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->where('auction_status', 'sold')
                       ->where('event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    public function getUnsoldPlayers($activeOnly = true, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->where('auction_status', 'unsold')
                       ->where('event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    // Method to update player status during auction (with event filtering)
    public function updatePlayerStatus($playerId, $status, $soldTo = null, $finalPrice = null, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $data = ['auction_status' => $status];
        
        if ($status === 'sold' && $soldTo !== null) {
            $data['sold_to'] = $soldTo;
            $data['final_price'] = $finalPrice;
            $data['is_unsold'] = false; // For backward compatibility
        } elseif ($status === 'unsold') {
            $data['sold_to'] = null;
            $data['final_price'] = null;
            $data['is_unsold'] = true; // For backward compatibility
        } elseif ($status === 'available') {
            $data['sold_to'] = null;
            $data['final_price'] = null;
            $data['is_unsold'] = false; // For backward compatibility
        }
        
        // Update only if player belongs to the specified event
        return $this->where('event_id', $eventId)->update($playerId, $data);
    }
    
    // Method to get players by specific event (useful for admin operations)
    public function getPlayersByEvent($eventId, $activeOnly = true)
    {
        $builder = $this->where('event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    // Method to get player count for specific event
    public function getPlayerCountByEvent($eventId, $activeOnly = true)
    {
        $builder = $this->where('event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->countAllResults();
    }
    
    // Method to get players by role and event
    public function getPlayersByRoleAndEvent($role, $eventId = null, $activeOnly = true)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->where('player_role', $role)
                       ->where('event_id', $eventId);
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    // Method to search players within event
    public function searchPlayersInEvent($searchTerm, $eventId = null, $activeOnly = true)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->where('event_id', $eventId)
                       ->groupStart()
                           ->like('player_name', $searchTerm)
                           ->orLike('company_name', $searchTerm)
                           ->orLike('chapter_name', $searchTerm)
                       ->groupEnd();
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    // Method to get players with CricHeroes URLs
    public function getPlayersWithCricHeroesUrls($eventId = null, $activeOnly = true)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        $builder = $this->where('event_id', $eventId)
                       ->where('cricheroes_url IS NOT NULL')
                       ->where('cricheroes_url !=', '');
        
        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        
        return $builder->findAll();
    }
    
    // Method to update CricHeroes URL for a player
    public function updateCricHeroesUrl($playerId, $cricHeroesUrl, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        return $this->where('player_id', $playerId)
                   ->where('event_id', $eventId)
                   ->update(null, ['cricheroes_url' => $cricHeroesUrl]);
    }
    
    // Override update method to ensure event filtering
    public function update($id = null, $data = null): bool
    {
        // If current event ID is set and event_id is not in data, ensure we only update within current event
        if ($this->currentEventId && !isset($data['event_id'])) {
            $this->where('event_id', $this->currentEventId);
        }
        
        return parent::update($id, $data);
    }
    
    // Override delete method to ensure event filtering
    public function delete($id = null, bool $purge = false)
    {
        if ($this->currentEventId) {
            $this->where('event_id', $this->currentEventId);
        }
        
        return parent::delete($id, $purge);
    }
    
    // Method to bulk update base prices for all players in an event
    public function updateBasePricesForEvent($basePrice, $eventId = null)
    {
        $eventId = $eventId ?? $this->currentEventId;
        
        return $this->where('event_id', $eventId)
                   ->where('is_active', true)
                   ->set('base_price', $basePrice)
                   ->set('updated_at', date('Y-m-d H:i:s'))
                   ->update();
    }
}