<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthModel extends Model
{
    /**
     * Table Name of the Database
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Primary Key of the Database Column
     *
     * @var string
     */
    protected $primary_key = 'id';

    /**
     * Fillable attributes for Mass Assignment
     *
     * @var array
     */
    protected $fillable = ['username', 'email', 'password', 'role', 'is_active'];

    /**
     * Timestamps handled by DB defaults (created_at) / manual updated_at
     *
     * @var boolean
     */
    protected $timestamps = false;

    /**
     * Find a user by username or email (used at login)
     *
     * @param string $identity
     * @return mixed
     */
    public function find_by_identity($identity)
    {
        return $this->db->table($this->table)
            ->where('username', $identity)
            ->or_where('email', $identity)
            ->get();
    }
}
