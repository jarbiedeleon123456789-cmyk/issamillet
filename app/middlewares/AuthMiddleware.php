<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * Middleware: AuthMiddleware
 *
 * Blocks unauthenticated visitors from reaching protected routes.
 */
class AuthMiddleware
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

        return $next();
    }
}
