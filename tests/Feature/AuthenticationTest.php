<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_authenticate_with_their_nickname(): void
    {
        $user = User::factory()->create([
            'nickname' => 'carlos.mendoza',
            'password' => 'password',
        ]);

        $response = Volt::test('pages.auth.login')
            ->set('form.nickname', 'carlos.mendoza')
            ->set('form.password', 'password')
            ->call('login');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_cannot_authenticate_with_an_email_in_the_nickname_field(): void
    {
        User::factory()->create([
            'nickname' => 'carlos.mendoza',
            'email' => 'carlos@example.test',
            'password' => 'password',
        ]);

        $response = Volt::test('pages.auth.login')
            ->set('form.nickname', 'carlos@example.test')
            ->set('form.password', 'password')
            ->call('login');

        $response->assertHasErrors('form.nickname');
        $this->assertGuest();
    }
}
