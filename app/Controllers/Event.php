<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Event extends BaseController
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
        $data['title'] = 'Manage Events';
        // Get stats for the statistics cards
        $data['stats'] = $this->eventModel->getEventsCount();

        return view('superadmin/events', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Event';
        return view('superadmin/create_event', $data);
    }

    public function store()
    {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => [],
            'admin_credentials' => null
        ];

        try {
            $validation = \Config\Services::validation();

            // Define validation rules
            $rules = [
                'event_name' => [
                    'rules' => 'required|min_length[3]|max_length[100]|is_unique[events.event_name]',
                    'errors' => [
                        'required' => 'Event name is required',
                        'min_length' => 'Event name must be at least 3 characters long',
                        'max_length' => 'Event name cannot exceed 100 characters',
                        'is_unique' => 'Event name already exists'
                    ]
                ],
                // 'event_description' => [
                //     'rules' => 'required|min_length[10]',
                //     'errors' => [
                //         'required' => 'Event description is required',
                //         'min_length' => 'Event description must be at least 10 characters long'
                //     ]
                // ],
                'start_date' => [
                    'rules' => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required' => 'Start date is required',
                        'valid_date' => 'Please provide a valid start date'
                    ]
                ],
                'end_date' => [
                    'rules' => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required' => 'End date is required',
                        'valid_date' => 'Please provide a valid end date'
                    ]
                ],
                'admin_username' => [
                    'rules' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                    'errors' => [
                        'required' => 'Admin username is required',
                        'min_length' => 'Username must be at least 3 characters long',
                        'max_length' => 'Username cannot exceed 50 characters',
                        'is_unique' => 'Username already exists'
                    ]
                ],
                'admin_password' => [
                    'rules' => 'required|min_length[6]',
                    'errors' => [
                        'required' => 'Admin password is required',
                        'min_length' => 'Password must be at least 6 characters long'
                    ]
                ]
            ];

            // Add file upload validation if file is provided
            $file = $this->request->getFile('event_logo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $rules['event_logo'] = [
                    'rules' => 'uploaded[event_logo]|is_image[event_logo]|max_size[event_logo,2048]',
                    'errors' => [
                        'uploaded' => 'Please select a valid image file',
                        'is_image' => 'Only image files are allowed',
                        'max_size' => 'Image size should not exceed 2MB'
                    ]
                ];
            }

            $validation->setRules($rules);

            if (!$validation->withRequest($this->request)->run()) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            // Custom validation for date range
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');

            if (strtotime($endDate) <= strtotime($startDate)) {
                $response['errors']['end_date'] = 'End date must be after start date';
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // Handle file upload
            $logoFileName = null;
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $logoFileName = $file->getRandomName();
                $file->move(ROOTPATH . 'uploads/events/', $logoFileName);
            }

            // Prepare event data - status will be set by database trigger
            $eventData = [
                'event_name' => trim($this->request->getPost('event_name')),
                'event_description' => trim($this->request->getPost('event_description')),
                'event_logo' => $logoFileName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert event
            $eventId = $this->eventModel->insert($eventData);

            if (!$eventId) {
                throw new \Exception('Failed to create event');
            }

            // Create admin account
            $adminUsername = trim($this->request->getPost('admin_username'));
            $adminPassword = $this->request->getPost('admin_password');

            $adminData = [
                'username' => $adminUsername,
                'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                'role' => 'admin',
                'event_id' => $eventId,
                'team_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $adminId = $this->userModel->insert($adminData);

            if (!$adminId) {
                throw new \Exception('Failed to create admin account');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            $response['success'] = true;
            $response['message'] = 'Event created successfully with admin account';
            $response['admin_credentials'] = [
                'username' => $adminUsername,
                'password' => $adminPassword
            ];

        } catch (\Exception $e) {
            log_message('error', 'Event creation failed: ' . $e->getMessage());
            $response['message'] = 'Failed to create event: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function edit($eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Event not found');
        }

        $data['title'] = 'Edit Event';
        $data['event'] = $event;

        return view('superadmin/edit_event', $data);
    }

    // Method to get event data for AJAX edit modal
    public function getEventData($eventId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'data' => null
        ];

        try {
            $event = $this->eventModel->find($eventId);

            if (!$event) {
                $response['message'] = 'Event not found';
                return $this->response->setJSON($response);
            }

            // Explicitly format dates to ensure they're in YYYY-MM-DD format
            if (isset($event['start_date'])) {
                // Extract just the date part if it's a datetime
                $event['start_date'] = date('Y-m-d', strtotime($event['start_date']));
            }

            if (isset($event['end_date'])) {
                // Extract just the date part if it's a datetime
                $event['end_date'] = date('Y-m-d', strtotime($event['end_date']));
            }

            $response['success'] = true;
            $response['data'] = $event;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get event data: ' . $e->getMessage());
            $response['message'] = 'Failed to load event data: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function update($eventId)
    {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        try {
            $event = $this->eventModel->find($eventId);

            if (!$event) {
                $response['message'] = 'Event not found';
                return $this->response->setJSON($response);
            }

            $validation = \Config\Services::validation();

            $rules = [
                'event_name' => [
                    'rules' => "required|min_length[3]|max_length[100]|is_unique[events.event_name,event_id,{$eventId}]",
                    'errors' => [
                        'required' => 'Event name is required',
                        'min_length' => 'Event name must be at least 3 characters long',
                        'max_length' => 'Event name cannot exceed 100 characters',
                        'is_unique' => 'Event name already exists'
                    ]
                ],
                // 'event_description' => [
                //     'rules' => 'required|min_length[10]',
                //     'errors' => [
                //         'required' => 'Event description is required',
                //         'min_length' => 'Event description must be at least 10 characters long'
                //     ]
                // ],
                'start_date' => [
                    'rules' => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required' => 'Start date is required',
                        'valid_date' => 'Please provide a valid start date'
                    ]
                ],
                'end_date' => [
                    'rules' => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required' => 'End date is required',
                        'valid_date' => 'Please provide a valid end date'
                    ]
                ]
            ];

            $file = $this->request->getFile('event_logo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $rules['event_logo'] = [
                    'rules' => 'uploaded[event_logo]|is_image[event_logo]|max_size[event_logo,2048]',
                    'errors' => [
                        'uploaded' => 'Please select a valid image file',
                        'is_image' => 'Only image files are allowed',
                        'max_size' => 'Image size should not exceed 2MB'
                    ]
                ];
            }

            $validation->setRules($rules);

            if (!$validation->withRequest($this->request)->run()) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            // Custom validation for date range
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');

            if (strtotime($endDate) <= strtotime($startDate)) {
                $response['errors']['end_date'] = 'End date must be after start date';
                $response['message'] = 'Please fix the validation errors';
                return $this->response->setJSON($response);
            }

            // Handle file upload
            $logoFileName = $event['event_logo']; // Keep existing logo by default
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Delete old logo if exists
                if ($event['event_logo'] && file_exists(ROOTPATH . 'uploads/events/' . $event['event_logo'])) {
                    unlink(ROOTPATH . 'uploads/events/' . $event['event_logo']);
                }

                $logoFileName = $file->getRandomName();
                $file->move(ROOTPATH . 'uploads/events/', $logoFileName);
            }

            // Prepare event data - status will be updated by database trigger
            $eventData = [
                'event_name' => trim($this->request->getPost('event_name')),
                'event_description' => trim($this->request->getPost('event_description')),
                'event_logo' => $logoFileName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->eventModel->update($eventId, $eventData)) {
                $response['success'] = true;
                $response['message'] = 'Event updated successfully';
            } else {
                $response['message'] = 'Failed to update event';
            }

        } catch (\Exception $e) {
            log_message('error', 'Event update failed: ' . $e->getMessage());
            $response['message'] = 'Failed to update event: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    // Updated delete method for soft delete
    public function delete($eventId)
    {
        $response = [
            'success' => false,
            'message' => ''
        ];

        try {
            $event = $this->eventModel->find($eventId);

            if (!$event) {
                $response['message'] = 'Event not found';
                return $this->response->setJSON($response);
            }

            // Soft delete - just set is_active to 0
            if ($this->eventModel->update($eventId, ['is_active' => 0, 'status' => 'Inactive', 'updated_at' => date('Y-m-d H:i:s')])) {
                $response['success'] = true;
                $response['message'] = 'Event deleted successfully';
            } else {
                $response['message'] = 'Failed to delete event';
            }

        } catch (\Exception $e) {
            log_message('error', 'Event deletion failed: ' . $e->getMessage());
            $response['message'] = 'Failed to delete event: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    // Updated getList method to properly handle the filter parameter
    public function getList()
    {
        try {
            // Get filter parameter from the DataTables AJAX request
            $filter = $this->request->getGet('filter') ?? 'active';
            
            // Use the EventModel method with the filter
            $events = $this->eventModel->getEventsWithAdminCount($filter);

            // Return the events data directly as JSON
            return $this->response->setJSON($events);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get events list: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to load events data'
            ])->setStatusCode(500);
        }
    }

    public function get($eventId)
    {
        try {
            $event = $this->eventModel->getEventWithAdmin($eventId);

            if (!$event) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Event not found'
                ])->setStatusCode(404);
            }

            // Explicitly format dates to ensure they're in YYYY-MM-DD format
            if (isset($event['start_date'])) {
                $event['start_date'] = date('Y-m-d', strtotime($event['start_date']));
            }

            if (isset($event['end_date'])) {
                $event['end_date'] = date('Y-m-d', strtotime($event['end_date']));
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $event
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get event: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load event data'
            ])->setStatusCode(500);
        }
    }
}