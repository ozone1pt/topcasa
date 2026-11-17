<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Approve/reject a pending review: allowed for the listing owner or an admin.
     */
    public function moderate(User $user, Review $review): bool
    {
        return $user->id === $review->anunciante_id || $user->isAdmin();
    }
}
