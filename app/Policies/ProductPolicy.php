<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Product;

class ProductPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function view(Admin $admin, Product $product): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function update(Admin $admin, Product $product): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function delete(Admin $admin, Product $product): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    /**
     * Dedicated permission for approving/rejecting affiliate candidate URLs.
     */
    public function approveAffiliate(Admin $admin, Product $product): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }
}
