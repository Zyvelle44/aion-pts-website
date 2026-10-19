<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\News;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:News');
    }

    public function view(AuthUser $authUser, News $news): bool
    {
        return $authUser->can('View:News');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:News');
    }

    public function update(AuthUser $authUser, News $news): bool
    {
        return $authUser->can('Update:News');
    }

    public function delete(AuthUser $authUser, News $news): bool
    {
        return $authUser->can('Delete:News');
    }

    public function restore(AuthUser $authUser, News $news): bool
    {
        return $authUser->can('Restore:News');
    }

    public function forceDelete(AuthUser $authUser, News $news): bool
    {
        return $authUser->can('ForceDelete:News');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:News');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:News');
    }

    public function replicate(AuthUser $authUser, News $news): bool
    {
        return $authUser->can('Replicate:News');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:News');
    }

}