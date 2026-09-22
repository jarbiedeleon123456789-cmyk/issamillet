<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Middleware: AdminMiddleware
 *
 * Restricts product mutations to authenticated administrator accounts.
 */
class AdminMiddleware
{
    public function handle(Closure $next)
    {
        $lava = lava_instance();
        $lava->call->library('session');

        if (!$lava->session->userdata('user_id')) {
            $lava->session->set_flashdata('error', 'Please log in to continue.');
            $lava->session->set_userdata('redirect_after_login', $lava->request->uri());
            redirect(site_url('login'));
            return;
        }

        if ($lava->session->userdata('role') !== 'admin') {
            $lava->session->set_flashdata('error', 'Administrator access is required for that action.');
            redirect(site_url('products'));
            return;
        }

        return $next();
    }
}
