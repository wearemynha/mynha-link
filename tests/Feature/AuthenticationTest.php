<?php

namespace Tests\Feature;

use App\Models\User;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_registration_uses_mynha_branding_and_preserves_submitted_values(): void
    {
        Route::get('/_test/register', [RegisteredUserController::class, 'create'])
            ->middleware('web')->name('register');
        Route::getRoutes()->refreshNameLookups();

        $response = $this->withSession(['_old_input' => [
            'name' => 'Test User',
            'littlelink_name' => 'test-user',
            'email' => 'test@mynha.example',
        ]])->get('/_test/register');

        $response->assertOk()
            ->assertSee('class="mynha-ui mynha-auth"', false)
            ->assertSee('mynha-auth-card--wide', false)
            ->assertSee('assets/mynha-assets/mynha-icon-preto-verde.svg', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('assets/mynha-assets/mynha.js', false)
            ->assertSee('value="Test User"', false)
            ->assertSee('value="test-user"', false)
            ->assertSee('value="test@mynha.example"', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('autocomplete="new-password"', false)
            ->assertSee('id="submit-btn"', false)
            ->assertDontSee('hope-ui.min.css', false)
            ->assertDontSee('assets/linkstack/images/logo.svg', false);
    }

    public function test_login_uses_mynha_branding_and_an_accessible_password_toggle(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('mynha-ui mynha-auth', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('assets/mynha-assets/mynha.js', false)
            ->assertSee('assets/mynha-assets/mynha-icon-preto-verde.svg', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('aria-controls="password"', false)
            ->assertSee('name="_token"', false)
            ->assertDontSee(route('password.request'), false)
            ->assertDontSee('detect-dark-mode.js', false)
            ->assertDontSee('hope-ui.min.css', false)
            ->assertDontSee('assets/linkstack/images/logo.svg', false);
    }

    public function test_login_preserves_the_email_after_validation_errors(): void
    {
        $this->withSession(['_old_input' => ['email' => 'test@mynha.example']])
            ->get('/login')
            ->assertOk()
            ->assertSee('value="test@mynha.example"', false);
    }

    public function test_other_guest_pages_use_the_shared_mynha_layout(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('mynha-ui mynha-auth', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('mynha-auth-card', false)
            ->assertDontSee('hope-ui.min.css', false);
    }

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
