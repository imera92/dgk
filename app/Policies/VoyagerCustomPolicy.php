<?php

namespace App\Policies;

use App\User;
use App\Metadata;
use Illuminate\Auth\Access\HandlesAuthorization;
use TCG\Voyager\Facades\Voyager;

class VoyagerCustomPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Determine if the given metadata can be read by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Metadata  $metadata
     * @return bool
     */
    public function read_metadata(User $user, Metadata $metadata)
    {
        return Voyager::can('read_metadata');
    }

    /**
     * Determine if the given metadata can be edited by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Metadata  $metadata
     * @return bool
     */
    public function edit_metadata(User $user, Metadata $metadata)
    {
        return Voyager::can('edit_metadata');
    }

    /**
     * Determine if metadata can be added by the user.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function add_metadata(User $user)
    {
        return Voyager::can('add_metadata');
    }

    /**
     * Determine if the given metadata can be deleted by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Metadata  $metadata
     * @return bool
     */
    public function delete_metadata(User $user, Metadata $metadata = null)
    {
        return Voyager::can('delete_metadata');
    }
}
