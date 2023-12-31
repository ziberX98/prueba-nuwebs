<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use App\Utils\DsPermission;

class PostPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAbleTo(DsPermission::POSTS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->isAbleTo(DsPermission::POSTS_UPDATE) || $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->isAbleTo(DsPermission::POSTS_DELETE) || $user->id === $post->user_id;
    }
}
