<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function view(Admin $admin, Category $category): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function update(Admin $admin, Category $category): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function delete(Admin $admin, Category $category): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }
}
