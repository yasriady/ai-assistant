<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_is_shown_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('AI Academic Assessment');
    }

    #[Test]
    public function lecturer_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'lecturer@example.com',
            'password' => 'password',
            'role' => UserRole::Lecturer,
        ]);

        $this->post(route('login.store'), [
            'email' => 'lecturer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'lecturer@example.com',
            'password' => 'password',
            'role' => UserRole::Lecturer,
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'lecturer@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function authenticated_user_is_redirected_from_login_to_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Lecturer]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }
}
