<?php

namespace App\Policies;

use App\Models\Admin;

class ImportPolicy
{
    public function view(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }
}
