<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'event_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'event_name',
        'event_description', 
        'event_logo',
        'start_date',
        'end_date',
        'is_active',
        'status',        // NEW: include status column
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = false; // we control created_at/updated_at manually
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setStatusOnInsert'];
    protected $beforeUpdate = ['setStatusOnUpdate'];

    /**
     * Ensure status is set correctly before insert
     */
    protected function setStatusOnInsert(array $data)
    {
        if (isset($data['data']['end_date'])) {
            date_default_timezone_set('Asia/Kolkata');
            $endDate = strtotime($data['data']['end_date']);
            $now = time();

            $data['data']['status'] = ($endDate < $now) ? 'Inactive' : 'Active';
        }
        return $data;
    }

    /**
     * Ensure status is updated correctly before update
     */
    protected function setStatusOnUpdate(array $data)
    {
        if (isset($data['data']['end_date'])) {
            date_default_timezone_set('Asia/Kolkata');
            $endDate = strtotime($data['data']['end_date']);
            $now = time();

            $data['data']['status'] = ($endDate < $now) ? 'Inactive' : 'Active';
        }
        return $data;
    }

    /**
     * Get all events with admin count
     */
    public function getEventsWithAdminCount($filter = 'all')
    {
        $builder = $this->db->table('events e');
        $builder->select('e.*, COUNT(u.user_id) as admin_count');
        $builder->join('users u', 'u.event_id = e.event_id AND u.role = "admin"', 'left');
        $builder->where('e.is_active', 1); // ignore soft-deleted events

        if ($filter === 'active') {
            $builder->where('e.status', 'Active');
        } elseif ($filter === 'inactive') {
            $builder->where('e.status', 'Inactive');
        }

        $builder->groupBy('e.event_id');
        $builder->orderBy('e.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get event with admin details
     */
    public function getEventWithAdmin($eventId)
    {
        $builder = $this->db->table('events e');
        $builder->select('e.*, u.username as admin_username, u.user_id as admin_id');
        $builder->join('users u', 'u.event_id = e.event_id AND u.role = "admin"', 'left');
        $builder->where('e.event_id', $eventId);
        $builder->where('e.is_active', 1);

        return $builder->get()->getRowArray();
    }

    /**
     * Get active events only
     */
    public function getActiveEvents()
    {
        return $this->where('is_active', 1)
                    ->where('status', 'Active')
                    ->findAll();
    }

    /**
     * Get ended/inactive events only
     */
    public function getInactiveEvents()
    {
        return $this->where('is_active', 1)
                    ->where('status', 'Inactive')
                    ->findAll();
    }

    /**
     * Check if a specific event is ended
     */
    public function isEventEnded($eventId)
    {
        $event = $this->find($eventId);
        return $event ? ($event['status'] === 'Inactive') : false;
    }

    /**
     * Get events count by status
     */
    public function getEventsCount()
    {
        $total = $this->where('is_active', 1)->countAllResults(false);
        $active = $this->where('is_active', 1)
                       ->where('status', 'Active')
                       ->countAllResults(false);
        $inactive = $this->where('is_active', 1)
                         ->where('status', 'Inactive')
                         ->countAllResults();

        return [
            'total'   => $total,
            'active'  => $active,
            'inactive'=> $inactive
        ];
    }
}
