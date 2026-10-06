<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    public function test_active_admin_can_view_login_page(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('Portal Administrator');
    }

    public function test_active_admin_can_login_successfully(): void
    {
        $admin = Admin::where('email', 'admin@imersa.co.id')->first();
        if (!$admin) {
            $admin = Admin::create([
                'name' => 'Test Admin',
                'email' => 'admin@imersa.co.id',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@imersa.co.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $inactiveAdmin = Admin::updateOrCreate(
            ['email' => 'inactive@imersa.co.id'],
            [
                'name' => 'Inactive Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => false,
            ]
        );

        $response = $this->post(route('admin.login.store'), [
            'email' => 'inactive@imersa.co.id',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_inactive_admin_is_automatically_logged_out_by_middleware(): void
    {
        $admin = Admin::updateOrCreate(
            ['email' => 'temp@imersa.co.id'],
            [
                'name' => 'Temp Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->actingAs($admin, 'admin');

        // Now admin becomes inactive in database
        $admin->is_active = false;
        $admin->save();

        // Attempt to visit protected dashboard
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }
}
