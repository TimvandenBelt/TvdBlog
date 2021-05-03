<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user or guest can view the model.
     */
    public function view(?User $user, Page $page): bool
    {
        if (isset($user)) {
            return true;
        }

        if ($page->is_draft || $page->is_private) {
            abort(404);
        }

        return true;
    }

    /**
     * Determine whether the user or guest can view the password protected content.
     */
    public function view_password_protected(?User $user, Page $page): bool
    {
        if (!$page->is_password_protected) {
            return true;
        }

        if (isset($user)) {
            return true;
        }

        if (
            $page->is_password_protected &&
            !$page->visitorHasPasswordAccessToPasswordProtected()
        ) {
            return false;
        }

        return true;
    }
}
