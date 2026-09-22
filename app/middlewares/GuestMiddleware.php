<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * Middleware: GuestMiddleware
 *
 * Prevents an already-authenticated user from accessing login/register pages.
 */
class GuestMiddleware
{
    public function handle(Closure $next)
    {
        $lava = lava_instance();
        $lava->call->library('session');

        if ($lava->session->userdata('user_id')) {
            redirect(site_url('products'));
            return;
        }

        return $next();
    }
}
