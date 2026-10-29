<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class SAdmin extends BaseController
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

    public function index()
    {
        $data['title'] = 'Manage Admins';
        $data['admins'] = $this->userModel->select('users.*, events.event_name, events.is_active as event_is_active')
            ->join('events', 'events.event_id = users.event_id', 'left')
            ->where('users.role', 'admin')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        // Get events for the create form
        $data['events'] = $this->eventModel->where('is_active', 1)->findAll();

        // Get statistics
        $data['stats'] = [
            'total_admins' => $this->userModel->where('role', 'admin')->countAllResults(),
            'active_admins' => $this->userModel->where('role', 'admin')->where('is_active', 1)->countAllResults(),
            'inactive_admins' => $this->userModel->where('role', 'admin')->where('is_active', 0)->countAllResults(),
            'events_with_admins' => $this->userModel->select('DISTINCT event_id')->where('role', 'admin')->where('event_id IS NOT NULL', null, false)->countAllResults()
        ];

        return view('superadmin/admins', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Admin';
        $data['events'] = $this->eventModel->where('is_active', 1)->findAll();

        return view('superadmin/add_admin', $data);
    }

    public function store()
    {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        try {
            $validation = \Config\Services::validation();

            // Define validation rules
            $rules = [
                'username' => [
                    'rules' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                    'errors' => [
                        'required' => 'Username is required',
                        'min_length' => 'Username must be at least 3 characters long',
                        'max_length' => 'Username cannot exceed 50 characters',
                        'is_unique' => 'Username already exists'
                    ]
                ],
                'password' => [
                    'rules' => 'required|min_length[6]',
                    'errors' => [
                        'required' => 'Password is required',
                        'min_length' => 'Password must be at least 6 characters long'
                    ]
                ],
                'event_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Event assignment is required',
                        'integer' => 'Please select a valid event'
                    ]
                ]
            ];

            $validation->setRules($rules);

            if (!$validation->withRequest($this->request)->run()) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            // Check if event exists and is active
            $event = $this->eventModel->find($this->request->getPost('event_id'));
            if (!$event || $event['is_active'] != 1) {
                $response['errors']['event_id'] = 'Selected event is not available';
                $response['message'] = 'Please select a valid active event';
                return $this->response->setJSON($response);
            }

            // Prepare admin data
            $adminData = [
                'username' => trim($this->request->getPost('username')),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role' => 'admin',
                'event_id' => $this->request->getPost('event_id'),
                'team_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $adminId = $this->userModel->insert($adminData);

            if (!$adminId) {
                throw new \Exception('Failed to create admin account');
            }

            $response['success'] = true;
            $response['message'] = 'Admin account created successfully';

        } catch (\Exception $e) {
            log_message('error', 'Admin creation failed: ' . $e->getMessage());
            $response['message'] = 'Failed to create admin account: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }



    private function generateRandomPassword($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function get($userId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'admin' => null
        ];

        try {
            $admin = $this->userModel->select('users.*, events.event_name')
                ->join('events', 'events.event_id = users.event_id', 'left')
                ->where('users.user_id', $userId)
                ->where('users.role', 'admin')
                ->first();

            if (!$admin) {
                $response['message'] = 'Admin not found';
                return $this->response->setJSON($response);
            }

            $response['success'] = true;
            $response['admin'] = $admin;

        } catch (\Exception $e) {
            log_message('error', 'Failed to get admin: ' . $e->getMessage());
            $response['message'] = 'Failed to load admin data: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function getList()
    {
        try {
            $showInactive = $this->request->getGet('show_inactive') === 'true';
            $currentDate = date('Y-m-d');

            $builder = $this->userModel->select('users.*, events.event_name, events.is_active as event_is_active, events.end_date')
                ->join('events', 'events.event_id = users.event_id', 'left')
                ->where('users.role', 'admin')
                ->where('users.is_active', 1); // Only get non-soft-deleted admins

            if ($showInactive) {
                // Show admins whose events have ended
                $builder->where('events.end_date <', $currentDate);
            } else {
                // Show admins whose events are still active or have no event
                $builder->groupStart()
                    ->where('events.end_date >=', $currentDate)
                    ->orWhere('events.end_date IS NULL')
                    ->groupEnd();
            }

            $admins = $builder->orderBy('users.created_at', 'DESC')->findAll();

            // Process data for DataTables
            $processedAdmins = [];
            foreach ($admins as $admin) {
                $isInactive = $admin['end_date'] && strtotime($admin['end_date']) < strtotime($currentDate);

                $processedAdmins[] = [
                    'user_id' => $admin['user_id'],
                    'username' => $admin['username'],
                    'event_id' => $admin['event_id'],
                    'event_name' => $admin['event_name'] ?? 'No Event Assigned',
                    'event_is_active' => $admin['event_is_active'] ?? 0,
                    'end_date' => $admin['end_date'],
                    'is_active' => $admin['is_active'],
                    'admin_status' => $isInactive ? 'inactive' : 'active',
                    'created_at' => $admin['created_at'],
                    'updated_at' => $admin['updated_at']
                ];
            }

            return $this->response->setJSON($processedAdmins);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get admins list: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to load admins data'
            ])->setStatusCode(500);
        }
    }

    public function update($userId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        try {
            $admin = $this->userModel->where('user_id', $userId)->where('role', 'admin')->first();
            if (!$admin) {
                $response['message'] = 'Admin not found';
                return $this->response->setJSON($response);
            }

            $validation = \Config\Services::validation();
            $rules = [
                'username' => [
                    'rules' => "required|min_length[3]|max_length[50]|is_unique[users.username,user_id,{$userId}]",
                    'errors' => [
                        'required' => 'Username is required',
                        'min_length' => 'Username must be at least 3 characters long',
                        'max_length' => 'Username cannot exceed 50 characters',
                        'is_unique' => 'Username already exists'
                    ]
                ]
            ];

            $validation->setRules($rules);
            if (!$validation->withRequest($this->request)->run()) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            // Only update username
            $updateData = [
                'username' => trim($this->request->getPost('username')),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->userModel->update($userId, $updateData)) {
                $response['success'] = true;
                $response['message'] = 'Admin updated successfully';
            } else {
                $response['message'] = 'Failed to update admin';
            }

        } catch (\Exception $e) {
            log_message('error', 'Admin update failed: ' . $e->getMessage());
            $response['message'] = 'Failed to update admin: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function delete($userId)
    {
        $response = [
            'success' => false,
            'message' => ''
        ];

        try {
            $admin = $this->userModel->where('user_id', $userId)->where('role', 'admin')->first();
            if (!$admin) {
                $response['message'] = 'Admin not found';
                return $this->response->setJSON($response);
            }

            // Soft delete: set is_active to 0 instead of deleting
            if (
                $this->userModel->update($userId, [
                    'is_active' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
            ) {
                $response['success'] = true;
                $response['message'] = 'Admin deleted successfully';
            } else {
                $response['message'] = 'Failed to delete admin';
            }

        } catch (\Exception $e) {
            log_message('error', 'Admin deletion failed: ' . $e->getMessage());
            $response['message'] = 'Failed to delete admin: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    // Add this new method to update admin status based on event end dates
    public function updateAdminStatusByEventDate()
    {
        try {
            $currentDate = date('Y-m-d');

            // Get all active admins with their events
            $admins = $this->userModel->select('users.user_id, events.end_date')
                ->join('events', 'events.event_id = users.event_id', 'left')
                ->where('users.role', 'admin')
                ->where('users.is_active', 1)
                ->findAll();

            $updated = 0;
            foreach ($admins as $admin) {
                if ($admin['end_date'] && strtotime($admin['end_date']) < strtotime($currentDate)) {
                    // Event has ended, make admin inactive
                    $this->userModel->update($admin['user_id'], [
                        'is_active' => 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $updated++;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "{$updated} admin(s) status updated based on event end dates"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update admin status: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update admin status'
            ])->setStatusCode(500);
        }
    }


    public function toggleStatus($userId)
    {
        $response = [
            'success' => false,
            'message' => ''
        ];

        try {
            $admin = $this->userModel->where('user_id', $userId)->where('role', 'admin')->first();

            if (!$admin) {
                $response['message'] = 'Admin not found';
                return $this->response->setJSON($response);
            }

            $newStatus = $admin['is_active'] ? 0 : 1;

            if ($this->userModel->update($userId, ['is_active' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')])) {
                $response['success'] = true;
                $response['message'] = 'Admin status updated successfully';
                $response['new_status'] = $newStatus;
            } else {
                $response['message'] = 'Failed to update admin status';
            }

        } catch (\Exception $e) {
            log_message('error', 'Admin status toggle failed: ' . $e->getMessage());
            $response['message'] = 'Failed to update admin status: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function resetPassword($userId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'new_password' => null
        ];

        try {
            $admin = $this->userModel->where('user_id', $userId)->where('role', 'admin')->first();

            if (!$admin) {
                $response['message'] = 'Admin not found';
                return $this->response->setJSON($response);
            }

            // Generate new random password
            $newPassword = $this->generateRandomPassword(8);

            if (
                $this->userModel->update($userId, [
                    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'updated_at' => date('Y-m-d H:i:s')
                ])
            ) {
                $response['success'] = true;
                $response['message'] = 'Password reset successfully';
                $response['new_password'] = $newPassword;
            } else {
                $response['message'] = 'Failed to reset password';
            }

        } catch (\Exception $e) {
            log_message('error', 'Password reset failed: ' . $e->getMessage());
            $response['message'] = 'Failed to reset password: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }
}