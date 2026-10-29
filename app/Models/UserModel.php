<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'username', 
        'password_hash', 
        'role', 
        'event_id', 
        'team_id', 
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
        'password_hash' => 'required',
        'role' => 'required|in_list[superadmin,admin,team,participant]'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters long',
            'max_length' => 'Username cannot exceed 50 characters',
            'is_unique' => 'Username already exists'
        ],
        'role' => [
            'required' => 'User role is required',
            'in_list' => 'Invalid user role'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    /**
     * Authenticate user login
     */
    public function authenticate($username, $password)
    {
        $user = $this->where('username', $username)
                     ->where('is_active', true)
                     ->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get admin users with their event information
     */
    public function getAdminsWithEvents()
    {
        return $this->select('users.*, events.event_name')
                    ->join('events', 'events.event_id = users.event_id', 'left')
                    ->where('users.role', 'admin')
                    ->orderBy('users.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get team users with their team and event information
     */
    public function getTeamsWithDetails()
    {
        return $this->select('users.*, teams.team_name, events.event_name')
                    ->join('teams', 'teams.team_id = users.team_id', 'left')
                    ->join('events', 'events.event_id = users.event_id', 'left')
                    ->where('users.role', 'team')
                    ->where('users.is_active', 1)
                    ->orderBy('users.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get user with event information
     */
    public function getUserWithEvent($userId)
    {
        return $this->select('users.*, events.event_name, events.event_description')
                    ->join('events', 'events.event_id = users.event_id', 'left')
                    ->where('users.user_id', $userId)
                    ->first();
    }

    /**
     * Get user with team and event information (for team users)
     */
    public function getUserWithTeamAndEvent($userId)
    {
        return $this->select('users.*, teams.team_name, teams.team_logo, events.event_name, events.event_description')
                    ->join('teams', 'teams.team_id = users.team_id', 'left')
                    ->join('events', 'events.event_id = users.event_id', 'left')
                    ->where('users.user_id', $userId)
                    ->first();
    }

    /**
     * Check if username exists (excluding specific user ID)
     */
    public function usernameExists($username, $excludeUserId = null)
    {
        $query = $this->where('username', $username);
        
        if ($excludeUserId) {
            $query->where('user_id !=', $excludeUserId);
        }
        
        return $query->first() !== null;
    }

    /**
     * Create admin user for event
     */
    public function createAdminForEvent($eventId, $username, $password)
    {
        $adminData = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'event_id' => $eventId,
            'team_id' => null,
            'is_active' => 1
        ];

        return $this->insert($adminData);
    }

    /**
     * Create team user for team
     */
    public function createTeamUser($teamId, $eventId, $username, $password)
    {
        $teamData = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'team',
            'event_id' => $eventId,
            'team_id' => $teamId,
            'is_active' => 1
        ];

        return $this->insert($teamData);
    }

    /**
     * Get users by event ID
     */
    public function getUsersByEvent($eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get team users by event ID
     */
    public function getTeamUsersByEvent($eventId)
    {
        return $this->select('users.*, teams.team_name')
                    ->join('teams', 'teams.team_id = users.team_id', 'left')
                    ->where('users.event_id', $eventId)
                    ->where('users.role', 'team')
                    ->where('users.is_active', 1)
                    ->orderBy('teams.team_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get active users count by role
     */
    public function getActiveUserCountByRole($role)
    {
        return $this->where('role', $role)
                    ->where('is_active', 1)
                    ->countAllResults();
    }

    /**
     * Deactivate users by event ID
     */
    public function deactivateUsersByEvent($eventId)
    {
        return $this->where('event_id', $eventId)
                    ->set('is_active', 0)
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
    }

    /**
     * Deactivate team users by team ID
     */
    public function deactivateUsersByTeam($teamId)
    {
        return $this->where('team_id', $teamId)
                    ->where('role', 'team')
                    ->set('is_active', 0)
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
    }

    /**
     * Activate team users by team ID
     */
    public function activateUsersByTeam($teamId)
    {
        return $this->where('team_id', $teamId)
                    ->where('role', 'team')
                    ->set('is_active', 1)
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
    }

    /**
     * Get admin user for specific event
     */
    public function getAdminByEvent($eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('role', 'admin')
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Get team user for specific team
     */
    public function getTeamUser($teamId)
    {
        return $this->where('team_id', $teamId)
                    ->where('role', 'team')
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Update team user credentials
     */
    public function updateTeamCredentials($teamId, $username, $password = null)
    {
        $updateData = [
            'username' => $username,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($password) {
            $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $this->where('team_id', $teamId)
                    ->where('role', 'team')
                    ->set($updateData)
                    ->update();
    }

    /**
     * Check if team user exists for team
     */
    public function teamUserExists($teamId)
    {
        return $this->where('team_id', $teamId)
                    ->where('role', 'team')
                    ->countAllResults() > 0;
    }
}