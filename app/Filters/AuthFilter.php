<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $uri = $request->getUri();
        $currentPath = $uri->getPath();
        
        // Check if user is logged in
        if (!$session->get('user_id')) {
            return redirect()->to('/login');
        }
        
        // Get user role
        $role = $session->get('role');
        
        // Role-based access control
        if (strpos($currentPath, '/superadmin') === 0) {
            // Superadmin routes - only superadmin role allowed
            if ($role !== 'superadmin') {
                return redirect()->to('/login')->with('error', 'Access denied: Insufficient privileges');
            }
        } elseif (strpos($currentPath, '/admin') === 0) {
            // Admin routes - admin and superadmin roles allowed
            if (!in_array($role, ['admin', 'superadmin'])) {
                return redirect()->to('/login')->with('error', 'Access denied: Insufficient privileges');
            }
        } elseif (strpos($currentPath, '/team') === 0) {
            // Team routes - only team role allowed
            if ($role !== 'team') {
                return redirect()->to('/login')->with('error', 'Access denied: Team access only');
            }
        } elseif (strpos($currentPath, '/dashboard') === 0) {
            // Dashboard routes - admin and superadmin roles allowed
            if (!in_array($role, ['admin', 'superadmin'])) {
                return redirect()->to('/login')->with('error', 'Access denied: Insufficient privileges');
            }
        }
        
        // Additional validation for team users
        if ($role === 'team') {
            $teamId = $session->get('team_id');
            if (!$teamId) {
                return redirect()->to('/login')->with('error', 'Invalid team session');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}