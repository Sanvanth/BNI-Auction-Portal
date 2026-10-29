<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class SsuperAdmin extends BaseController
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
        $data['title'] = 'Manage Superadmins';
        $data['superadmins'] = $this->userModel->where('role', 'superadmin')
            ->where('is_active', 1)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Calculate statistics for the view
        $data['stats'] = [
            'total_superadmins' => $this->userModel->where('role', 'superadmin')->countAllResults(),
            'active_superadmins' => $this->userModel->where('role', 'superadmin')->where('is_active', 1)->countAllResults(),
            'inactive_superadmins' => $this->userModel->where('role', 'superadmin')->where('is_active', 0)->countAllResults()
        ];

        return view('superadmin/superadmins', $data);
    }

    public function inactive()
    {
        $data['title'] = 'Inactive Superadmins';
        $data['superadmins'] = $this->userModel->where('role', 'superadmin')
            ->where('is_active', 0)
            ->orderBy('updated_at', 'DESC')
            ->findAll();

        // Calculate statistics for the view
        $data['stats'] = [
            'total_superadmins' => $this->userModel->where('role', 'superadmin')->countAllResults(),
            'active_superadmins' => $this->userModel->where('role', 'superadmin')->where('is_active', 1)->countAllResults(),
            'inactive_superadmins' => $this->userModel->where('role', 'superadmin')->where('is_active', 0)->countAllResults()
        ];

        $data['showing_inactive'] = true;

        return view('superadmin/superadmins', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Superadmin';
        return view('superadmin/add_superadmin', $data);
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
                ]
            ];

            $validation->setRules($rules);

            if (!$validation->withRequest($this->request)->run()) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            // Prepare superadmin data
            $superadminData = [
                'username' => trim($this->request->getPost('username')),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role' => 'superadmin',
                'event_id' => null,
                'team_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $superadminId = $this->userModel->insert($superadminData);

            if (!$superadminId) {
                throw new \Exception('Failed to create superadmin account');
            }

            $response['success'] = true;
            $response['message'] = 'Superadmin account created successfully';
        } catch (\Exception $e) {
            log_message('error', 'Superadmin creation failed: ' . $e->getMessage());
            $response['message'] = 'Failed to create superadmin account: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function get($userId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'superadmin' => null
        ];

        try {
            $superadmin = $this->userModel->where('user_id', $userId)
                ->where('role', 'superadmin')
                ->first();

            if (!$superadmin) {
                $response['message'] = 'Superadmin not found';
                return $this->response->setJSON($response);
            }

            $response['success'] = true;
            $response['superadmin'] = $superadmin;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get superadmin: ' . $e->getMessage());
            $response['message'] = 'Failed to load superadmin data: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function update($userId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        try {
            $superadmin = $this->userModel->where('user_id', $userId)
                ->where('role', 'superadmin')
                ->first();

            if (!$superadmin) {
                $response['message'] = 'Superadmin not found';
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

            // Prepare update data
            $updateData = [
                'username' => trim($this->request->getPost('username')),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->userModel->update($userId, $updateData)) {
                $response['success'] = true;
                $response['message'] = 'Superadmin updated successfully';
            } else {
                $response['message'] = 'Failed to update superadmin';
            }
        } catch (\Exception $e) {
            log_message('error', 'Superadmin update failed: ' . $e->getMessage());
            $response['message'] = 'Failed to update superadmin: ' . $e->getMessage();
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
            $superadmin = $this->userModel->where('user_id', $userId)
                ->where('role', 'superadmin')
                ->first();

            if (!$superadmin) {
                $response['message'] = 'Superadmin not found';
                return $this->response->setJSON($response);
            }

            // Check if this is the current logged-in superadmin
            if ($userId == $this->session->get('user_id')) {
                $response['message'] = 'You cannot deactivate your own account';
                return $this->response->setJSON($response);
            }

            // Check if there's only one active superadmin left
            $activeSuperadminCount = $this->userModel->where('role', 'superadmin')
                ->where('is_active', 1)
                ->countAllResults();

            if ($activeSuperadminCount <= 1) {
                $response['message'] = 'Cannot deactivate the last active superadmin account';
                return $this->response->setJSON($response);
            }

            // Soft delete by setting is_active to 0
            if ($this->userModel->update($userId, [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ])) {
                $response['success'] = true;
                $response['message'] = 'Superadmin deactivated successfully';
            } else {
                $response['message'] = 'Failed to deactivate superadmin';
            }
        } catch (\Exception $e) {
            log_message('error', 'Superadmin deactivation failed: ' . $e->getMessage());
            $response['message'] = 'Failed to deactivate superadmin: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function activate($userId)
    {
        $response = [
            'success' => false,
            'message' => ''
        ];

        try {
            $superadmin = $this->userModel->where('user_id', $userId)
                ->where('role', 'superadmin')
                ->first();

            if (!$superadmin) {
                $response['message'] = 'Superadmin not found';
                return $this->response->setJSON($response);
            }

            if ($this->userModel->update($userId, [
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ])) {
                $response['success'] = true;
                $response['message'] = 'Superadmin activated successfully';
            } else {
                $response['message'] = 'Failed to activate superadmin';
            }
        } catch (\Exception $e) {
            log_message('error', 'Superadmin activation failed: ' . $e->getMessage());
            $response['message'] = 'Failed to activate superadmin: ' . $e->getMessage();
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
            $superadmin = $this->userModel->where('user_id', $userId)
                ->where('role', 'superadmin')
                ->first();

            if (!$superadmin) {
                $response['message'] = 'Superadmin not found';
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
            log_message('error', 'Superadmin password reset failed: ' . $e->getMessage());
            $response['message'] = 'Failed to reset password: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function getList()
    {
        try {
            $isActive = $this->request->getGet('active') !== '0' ? 1 : 0;
            
            $superadmins = $this->userModel->where('role', 'superadmin')
                ->where('is_active', $isActive)
                ->orderBy($isActive ? 'created_at' : 'updated_at', 'DESC')
                ->findAll();

            // Process data for DataTables
            $processedSuperadmins = [];
            foreach ($superadmins as $superadmin) {
                $processedSuperadmins[] = [
                    'user_id' => $superadmin['user_id'],
                    'username' => $superadmin['username'],
                    'role' => $superadmin['role'],
                    'is_active' => $superadmin['is_active'],
                    'created_at' => $superadmin['created_at'],
                    'updated_at' => $superadmin['updated_at']
                ];
            }

            return $this->response->setJSON($processedSuperadmins);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get superadmins list: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to load superadmins data'
            ])->setStatusCode(500);
        }
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
}