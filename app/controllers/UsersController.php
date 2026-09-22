<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UsersController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UsersModel');
        $this->call->library('session');
    }

    /**
     * Retrieve all users and display them in the view.
     */
    public function index()
    {
        $users = $this->UsersModel->all();

        $data['users'] = $users;
        $data['username'] = $this->session->userdata('username');
        $data['role'] = $this->session->userdata('role') ?: 'user';

        $this->call->view('users_view', $data);
    }
}

