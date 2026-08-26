<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_user_can_authenticate_and_log_out(): void
    {
        $user = $this->createUser([
            'email' => 'user@mynha.example',
            'password' => Hash::make('LoginPassword!123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'LoginPassword!123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_invalid_credentials_and_blocked_users_cannot_authenticate(): void
    {
        $user = $this->createUser([
            'email' => 'blocked@mynha.example',
            'password' => Hash::make('LoginPassword!123'),
            'block' => 'yes',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'LoginPassword!123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();

        $user = $this->createUser([
            'email' => 'reset@mynha.example',
            'password' => Hash::make('OldPassword!123'),
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            }
        );

        $this->assertNotNull($token);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword!456',
            'password_confirmation' => 'NewPassword!456',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword!456', $user->fresh()->password));
    }
}
