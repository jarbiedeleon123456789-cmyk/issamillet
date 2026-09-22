<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('AuthModel');
        $this->call->library('session');
    }

    /**
     * Show the login form
     */
    public function login()
    {
        $data['error']   = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');
        $data['old']     = $this->session->flashdata('old') ?: [];

        $this->call->view('login_view', $data);
    }

    /**
     * Handle login form submission
     */
    public function authenticate()
    {
        if (!$this->request->is_post()) {
            redirect(site_url('login'));
        }

        $identity = trim((string) $this->request->post('identity'));
        $password = (string) $this->request->post('password');

        if ($identity === '' || $password === '') {
            $this->session->set_flashdata('error', 'Please enter your username/email and password.');
            $this->session->set_flashdata('old', ['identity' => $identity]);
            redirect(site_url('login'));
        }

        $user = $this->AuthModel->find_by_identity($identity);

        if (!is_array($user) || empty($user['password']) || !password_verify($password, $user['password'])) {
            $this->session->set_flashdata('error', 'Invalid credentials. Please try again.');
            $this->session->set_flashdata('old', ['identity' => $identity]);
            redirect(site_url('login'));
            return;
        }

        if (isset($user['is_active']) && (int) $user['is_active'] !== 1) {
            $this->session->set_flashdata('error', 'This account has been deactivated.');
            redirect(site_url('login'));
            return;
        }

        $this->session->regenerate_on_login(true);

        $this->session->set_userdata([
            'user_id'  => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'] ?? 'user',
        ]);

        $redirect_to = $this->session->userdata('redirect_after_login');
        $this->session->unset_userdata('redirect_after_login');

        $this->session->set_flashdata('success', 'Welcome back, ' . $user['username'] . '!');

        redirect($redirect_to ?: site_url('products'));
        return;
    }

    /**
     * Show the registration form
     */
    public function register()
    {
        $data['error'] = $this->session->flashdata('error');
        $data['old']   = $this->session->flashdata('old') ?: [];

        $this->call->view('login_view', $data);
    }

    /**
     * Handle registration form submission
     */
    public function store()
    {
        if (!$this->request->is_post()) {
            redirect(site_url('register'));
        }

        $username = trim((string) $this->request->post('username'));
        $email    = trim((string) $this->request->post('email'));
        $password = (string) $this->request->post('password');
        $confirm  = (string) $this->request->post('confirm_password');

        $old = ['username' => $username, 'email' => $email];

        if ($username === '' || $email === '' || $password === '') {
            $this->session->set_flashdata('error', 'All fields are required.');
            $this->session->set_flashdata('old', $old);
            redirect(site_url('register'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Please enter a valid email address.');
            $this->session->set_flashdata('old', $old);
            redirect(site_url('register'));
        }

        if (strlen($password) < 6) {
            $this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
            $this->session->set_flashdata('old', $old);
            redirect(site_url('register'));
        }

        if ($password !== $confirm) {
            $this->session->set_flashdata('error', 'Passwords do not match.');
            $this->session->set_flashdata('old', $old);
            redirect(site_url('register'));
        }

        if ($this->AuthModel->find_by_identity($username) || $this->AuthModel->find_by_identity($email)) {
            $this->session->set_flashdata('error', 'That username or email is already registered.');
            $this->session->set_flashdata('old', $old);
            redirect(site_url('register'));
        }

        $this->AuthModel->insert([
            'username'  => $username,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'role'      => 'user',
            'is_active' => 1,
        ]);

        $this->session->set_flashdata('success', 'Account created successfully. Please log in.');
        redirect(site_url('login'));
    }

    /**
     * Log the current user out
     */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect(site_url('login'));
    }
}