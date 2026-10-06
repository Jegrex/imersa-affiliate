<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ProductReview;

class ProductReviewPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function view(Admin $admin, ProductReview $review): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function update(Admin $admin, ProductReview $review): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function delete(Admin $admin, ProductReview $review): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }
}
