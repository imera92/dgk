<?php

namespace App\Policies\Voyager;

use App\User;
use App\Database;
use Illuminate\Auth\Access\HandlesAuthorization;
use TCG\Voyager\Facades\Voyager;

class DatabasePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the given database configuration can be read by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Database  $database
     * @return bool
     */
    public function read_db(User $user, Database $database)
    {
        return Voyager::can('read_db');
    }

    /**
     * Determine if the given database configuration can be edited by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Database  $database
     * @return bool
     */
    public function edit_db(User $user, Database $database)
    {
        return Voyager::can('edit_db');
    }

    /**
     * Determine if new database configuration can be added by the user.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function add_db(User $user)
    {
        return Voyager::can('add_db');
    }

    /**
     * Determine if the given database configuration can be deleted by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Database  $database
     * @return bool
     */
    public function delete_db(User $user, Database $database = null)
    {
        return Voyager::can('delete_db');
    }
}
