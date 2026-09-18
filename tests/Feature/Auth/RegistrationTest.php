<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('nickname', 'test-user')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('Guest'));
        $this->assertTrue($user->can('dashboard.view'));
    }

    public function test_users_can_register_with_a_dot_in_their_username(): void
    {
        $this->seed(RolePermissionSeeder::class);

        Volt::test('pages.auth.register')
            ->set('name', 'Dot User')
            ->set('nickname', 'dot.user')
            ->set('email', 'dot@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', ['nickname' => 'dot.user']);
    }
}
