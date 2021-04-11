<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route("login"));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post(route("login"), [
            "email" => $user->email,
            "password" => "password",
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post(route("login"), [
            "email" => $user->email,
            "password" => "wrong-password",
        ]);

        $this->assertGuest();
    }

    public function test_user_can_authenticate_with_two_factor()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->withSession(["auth.password_confirmed_at" => time()]);

        $this->post("/user/two-factor-authentication");

        $this->post(route("logout"));

        $response = $this->post(route("login"), [
            "email" => $user->email,
            "password" => "password",
        ]);

        $response->assertRedirect(route("two-factor.login"));

        $response = $this->post(route("two-factor.login"), [
            "recovery_code" => $user->fresh()->recoveryCodes()[0],
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
