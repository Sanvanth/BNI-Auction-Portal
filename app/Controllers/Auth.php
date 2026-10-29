<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // If already logged in, redirect based on role
        if (session()->get('user_id')) {
            $role = session()->get('role');
            
            switch ($role) {
                case 'superadmin':
                    return redirect()->to(base_url('/superadmin/dashboard'));
                case 'admin':
                    return redirect()->to(base_url('/dashboard'));
                case 'team':
                    return redirect()->to(base_url('/team/account'));
                default:
                    return redirect()->to(base_url('/dashboard'));
            }
        }

        return view('auth/login');
    }

    public function login()
    {
        $userModel = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->authenticate($username, $password);

        if ($user) {
            session()->set([
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'event_id' => $user['event_id'],
                'team_id' => $user['team_id'],
                'logged_in' => true
            ]);

            // Redirect based on user role
            switch ($user['role']) {
                case 'superadmin':
                    return redirect()->to(base_url('/superadmin/dashboard'));
                case 'admin':
                    return redirect()->to(base_url('/dashboard'));
                case 'team':
                    return redirect()->to(base_url('/team/account'));
                default:
                    return redirect()->to(base_url('/dashboard'));
            }
        }

        // Set flash data for error message
        session()->setFlashdata('error', 'Invalid credentials');
        return redirect()->to(base_url('/login'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/login'));
    }
}