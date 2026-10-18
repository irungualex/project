<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_citizen_can_register_and_is_signed_in(): void
    {
        $this->post(route('register'), [
            'name' => 'Amina Mwangi',
            'email' => 'amina@example.test',
            'password' => 'CitizenPass123',
            'password_confirmation' => 'CitizenPass123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'amina@example.test',
            'role' => 'citizen',
        ]);
    }

    public function test_a_citizen_can_sign_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
