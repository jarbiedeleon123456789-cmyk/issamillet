<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UsersModel');
        $this->call->library('session');
    }

    public function before_action()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(site_url('login'));
            return;
        }

        if ($this->session->userdata('role') !== 'admin') {
            redirect(site_url('products'));
            return;
        }
    }

    public function create()
    {
        $user = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'username' => '',
            'role' => 'user',
        ];
        $error = null;

        if ($this->request->is_post()) {
            $user = [
                'firstname' => trim((string) $this->request->post('firstname')),
                'lastname' => trim((string) $this->request->post('lastname')),
                'email' => trim((string) $this->request->post('email')),
                'username' => trim((string) $this->request->post('username')),
                'role' => $this->request->post('role') === 'admin' ? 'admin' : 'user',
            ];
            $password = (string) $this->request->post('password');

            if ($user['firstname'] === '' || $user['lastname'] === '' ||
                !filter_var($user['email'], FILTER_VALIDATE_EMAIL) ||
                $user['username'] === '' || strlen($password) < 8) {
                $error = 'Enter all details and a password with at least 8 characters.';
            } elseif ($this->UsersModel->find_by('username', $user['username']) ||
                $this->UsersModel->find_by('email', $user['email'])) {
                $error = 'That username or email is already in use.';
            } else {
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                $this->UsersModel->insert($user);
                redirect(site_url('products'));
                return;
            }
        }

        $this->call->view('users/form', [
            'user' => $user,
            'error' => $error,
        ]);
    }
}
