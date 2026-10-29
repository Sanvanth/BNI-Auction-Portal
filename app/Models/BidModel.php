<?php
namespace App\Models;

use CodeIgniter\Model;

class BidModel extends Model
{
    protected $table = 'bids';
    protected $primaryKey = 'bid_id';
    protected $allowedFields = ['player_id', 'team_id', 'bid_amount', 'event_id'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get bid logs filtered by event_id
     */
    public function getBidLogs($eventId = null)
    {
        $builder = $this->select('bids.*, players.player_name, teams.team_name')
                    ->join('players', 'players.player_id = bids.player_id')
                    ->join('teams', 'teams.team_id = bids.team_id')
                    ->orderBy('bids.created_at', 'DESC');
        
        if ($eventId) {
            $builder->where('bids.event_id', $eventId);
        }
        
        return $builder->findAll();
    }

    /**
     * Get last bid for a specific player filtered by event_id
     */
    public function getLastBidForPlayer($playerId, $eventId = null)
    {
        $builder = $this->where('player_id', $playerId)
                    ->orderBy('created_at', 'DESC');
        
        if ($eventId) {
            $builder->where('event_id', $eventId);
        }
        
        return $builder->first();
    }

    /**
     * Get all bids for a specific event
     */
    public function getBidsByEvent($eventId)
    {
        return $this->select('bids.*, players.player_name, players.player_role, players.company_name, teams.team_name, teams.team_logo')
                    ->join('players', 'players.player_id = bids.player_id', 'left')
                    ->join('teams', 'teams.team_id = bids.team_id', 'left')
                    ->where('bids.event_id', $eventId)
                    ->orderBy('bids.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get bid statistics for a specific event
     */
    public function getBidStatisticsByEvent($eventId)
    {
        $totalBids = $this->where('event_id', $eventId)
                         ->where('bid_amount >', 0)
                         ->countAllResults();

        $totalValue = $this->selectSum('bid_amount')
                          ->where('event_id', $eventId)
                          ->where('bid_amount >', 0)
                          ->get()
                          ->getRow()
                          ->bid_amount ?? 0;

        $avgBid = $totalBids > 0 ? ($totalValue / $totalBids) : 0;

        $highestBid = $this->selectMax('bid_amount')
                          ->where('event_id', $eventId)
                          ->where('bid_amount >', 0)
                          ->get()
                          ->getRow()
                          ->bid_amount ?? 0;

        return [
            'total_bids' => $totalBids,
            'total_bid_value' => $totalValue,
            'average_bid' => $avgBid,
            'highest_bid' => $highestBid
        ];
    }

    /**
     * Get highest bidder for a player in a specific event
     */
    public function getHighestBidderForPlayer($playerId, $eventId)
    {
        return $this->select('bids.*, teams.team_name')
                    ->join('teams', 'teams.team_id = bids.team_id', 'left')
                    ->where('bids.player_id', $playerId)
                    ->where('bids.event_id', $eventId)
                    ->orderBy('bids.bid_amount', 'DESC')
                    ->orderBy('bids.created_at', 'DESC')
                    ->first();
    }

    /**
     * Get team's total spending in an event
     */
    public function getTeamSpendingByEvent($teamId, $eventId)
    {
        return $this->selectSum('bid_amount')
                    ->where('team_id', $teamId)
                    ->where('event_id', $eventId)
                    ->get()
                    ->getRow()
                    ->bid_amount ?? 0;
    }

    /**
     * Get bidding history for a specific player in an event
     */
    public function getPlayerBiddingHistory($playerId, $eventId)
    {
        return $this->select('bids.*, teams.team_name, teams.team_logo')
                    ->join('teams', 'teams.team_id = bids.team_id', 'left')
                    ->where('bids.player_id', $playerId)
                    ->where('bids.event_id', $eventId)
                    ->orderBy('bids.created_at', 'ASC')
                    ->findAll();
    }
}