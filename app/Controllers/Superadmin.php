<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Superadmin extends BaseController
{
    protected $eventModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->userModel = new UserModel();
        $this->session = session();

        // Check if user is logged in and is superadmin
        if (!$this->session->get('user_id') || $this->session->get('role') !== 'superadmin') {
            redirect()->to('/login')->send();
            exit;
        }
    }

    public function dashboard()
    {
        $data['title'] = 'Superadmin Dashboard';
        $data['username'] = $this->session->get('username');
        
        // Get dashboard statistics
        $data['totalEvents'] = $this->eventModel->countAllResults();
        $data['activeEvents'] = $this->eventModel->where('is_active', 1)->countAllResults();
        $data['totalAdmins'] = $this->userModel->where('role', 'admin')->countAllResults();
        $data['totalSuperadmins'] = $this->userModel->where('role', 'superadmin')->countAllResults();

        // Get recent events
        $data['recentEvents'] = $this->eventModel->orderBy('created_at', 'DESC')->limit(5)->findAll();

        // Get additional stats for better dashboard overview
        $data['inactiveEvents'] = $this->eventModel->where('is_active', 0)->countAllResults();
        $data['activeAdmins'] = $this->userModel->where('role', 'admin')->where('is_active', 1)->countAllResults();
        $data['inactiveAdmins'] = $this->userModel->where('role', 'admin')->where('is_active', 0)->countAllResults();
        $data['activeSuperadmins'] = $this->userModel->where('role', 'superadmin')->where('is_active', 1)->countAllResults();

        // Get events with admin counts for overview
        $data['eventsOverview'] = $this->eventModel->select('events.*, COUNT(users.user_id) as admin_count')
            ->join('users', 'users.event_id = events.event_id AND users.role = "admin"', 'left')
            ->groupBy('events.event_id')
            ->orderBy('events.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // Get recent admin registrations
        $data['recentAdmins'] = $this->userModel->select('users.username, users.created_at, events.event_name')
            ->join('events', 'events.event_id = users.event_id', 'left')
            ->where('users.role', 'admin')
            ->orderBy('users.created_at', 'DESC')
            ->limit(5)
            ->findAll();

        return view('superadmin/dashboard', $data);
    }

    public function index()
    {
        // Redirect to dashboard
        return redirect()->to('/superadmin/dashboard');
    }

    // Quick Stats API for AJAX calls
    public function getQuickStats()
    {
        try {
            $stats = [
                'total_events' => $this->eventModel->countAllResults(),
                'active_events' => $this->eventModel->where('is_active', 1)->countAllResults(),
                'inactive_events' => $this->eventModel->where('is_active', 0)->countAllResults(),
                'total_admins' => $this->userModel->where('role', 'admin')->countAllResults(),
                'active_admins' => $this->userModel->where('role', 'admin')->where('is_active', 1)->countAllResults(),
                'inactive_admins' => $this->userModel->where('role', 'admin')->where('is_active', 0)->countAllResults(),
                'total_superadmins' => $this->userModel->where('role', 'superadmin')->countAllResults(),
                'active_superadmins' => $this->userModel->where('role', 'superadmin')->where('is_active', 1)->countAllResults(),
                'events_with_admins' => $this->userModel->select('DISTINCT event_id')
                    ->where('role', 'admin')
                    ->where('event_id IS NOT NULL', null, false)
                    ->countAllResults(),
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get quick stats: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load statistics'
            ])->setStatusCode(500);
        }
    }

    // Recent Activity API
    public function getRecentActivity()
    {
        try {
            // Get recent events
            $recentEvents = $this->eventModel->select('event_name, created_at, "event" as type')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll();

            // Get recent admin registrations
            $recentAdmins = $this->userModel->select('users.username, users.created_at, events.event_name, "admin" as type')
                ->join('events', 'events.event_id = users.event_id', 'left')
                ->where('users.role', 'admin')
                ->orderBy('users.created_at', 'DESC')
                ->limit(5)
                ->findAll();

            // Combine and sort by date
            $activities = array_merge($recentEvents, $recentAdmins);
            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            // Limit to 10 most recent
            $activities = array_slice($activities, 0, 10);

            return $this->response->setJSON([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get recent activity: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load recent activity'
            ])->setStatusCode(500);
        }
    }

    // System Health Check
    public function getSystemHealth()
    {
        try {
            $health = [
                'database' => 'OK',
                'uploads_directory' => 'OK',
                'events_table' => 'OK',
                'users_table' => 'OK',
                'permissions' => 'OK'
            ];

            // Check database connection
            try {
                $db = \Config\Database::connect();
                $db->query('SELECT 1');
            } catch (\Exception $e) {
                $health['database'] = 'ERROR: ' . $e->getMessage();
            }

            // Check uploads directory
            if (!is_dir(ROOTPATH . 'uploads/events/')) {
                $health['uploads_directory'] = 'ERROR: Directory does not exist';
            } elseif (!is_writable(ROOTPATH . 'uploads/events/')) {
                $health['uploads_directory'] = 'ERROR: Directory is not writable';
            }

            // Check events table
            try {
                $this->eventModel->findAll(1);
            } catch (\Exception $e) {
                $health['events_table'] = 'ERROR: ' . $e->getMessage();
            }

            // Check users table
            try {
                $this->userModel->findAll(1);
            } catch (\Exception $e) {
                $health['users_table'] = 'ERROR: ' . $e->getMessage();
            }

            // Check if current user has proper permissions
            if ($this->session->get('role') !== 'superadmin') {
                $health['permissions'] = 'ERROR: Insufficient permissions';
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $health
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get system health: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to check system health'
            ])->setStatusCode(500);
        }
    }
}