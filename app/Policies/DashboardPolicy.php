<?php

namespace App\Policies;

use App\Models\Admin;

class DashboardPolicy
{
    public function view(Admin $admin): bool
    {
        return $admin->is_active && $admin->role === 'admin';
    }
}
