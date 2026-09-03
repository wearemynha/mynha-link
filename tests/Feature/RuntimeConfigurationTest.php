<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RuntimeConfigurationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class RuntimeConfigurationTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_debug_and_deployment_settings_cannot_be_changed_from_the_admin_panel(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        foreach ([
            ['type' => 'debug', 'entry' => 'APP_DEBUG'],
            ['type' => 'smtp', 'entry' => 'MAIL_MAILER'],
        ] as $payload) {
            $this->actingAs($admin)
                ->post(route('editConfig'), $payload)
                ->assertStatus(422);
        }

        foreach (['FORCE_HTTPS', 'ALLOW_CUSTOM_CODE_IN_THEMES'] as $entry) {
            $this->actingAs($admin)
                ->from('/admin/config')
                ->post(route('editConfig'), [
                    'type' => 'toggle',
                    'entry' => $entry,
                    'toggle' => 'on',
                ])
                ->assertRedirect('/admin/config')
                ->assertSessionHasErrors('entry');
        }
    }

    public function test_raw_environment_editor_and_code_updaters_are_not_routable(): void
    {
        $this->assertFalse(Route::has('showEnvironmentEditor'));
        $this->assertFalse(Route::has('editENV'));
        $this->assertFalse(Route::has('updateThemes'));
        $this->assertFalse(Route::has('deleteTheme'));
    }

    public function test_only_supported_locales_are_available(): void
    {
        $this->assertSame(['en', 'es', 'pt-BR'], config('app.supported_locales'));

        $this->expectException(ValidationException::class);

        app(RuntimeConfigurationManager::class)->setLocale('../views');
    }

    public function test_theme_selection_only_accepts_a_theme_shipped_with_the_application(): void
    {
        $user = $this->createUser(['theme' => 'default']);

        $this->actingAs($user)
            ->post(route('editTheme'), [
                'theme' => 'not-installed',
                'zip' => UploadedFile::fake()->create('theme.zip', 1, 'application/zip'),
            ])
            ->assertSessionHasErrors('theme');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'theme' => 'default',
        ]);
    }

    public function test_runtime_command_rejects_debug_in_production(): void
    {
        $previousEnvironment = getenv('APP_ENV');
        $previousDebug = getenv('APP_DEBUG');

        putenv('APP_ENV=production');
        putenv('APP_DEBUG=true');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('APP_DEBUG must be false');

            $this->artisan('runtime:sync-environment');
        } finally {
            $this->restoreEnvironment('APP_ENV', $previousEnvironment);
            $this->restoreEnvironment('APP_DEBUG', $previousDebug);
        }
    }

    public function test_runtime_command_clears_missing_deployment_values_and_preserves_application_values(): void
    {
        $temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'mynha-env-'.Str::uuid();
        File::makeDirectory($temporaryDirectory);
        File::put(
            $temporaryDirectory.DIRECTORY_SEPARATOR.'.env',
            "APP_KEY=base64:persistent-key\nMAIL_USERNAME=legacy-user\nAPP_NAME=\"Panel Name\"\n"
        );

        config([
            'env-editor.paths.env' => $temporaryDirectory,
            'env-editor.envFileName' => '.env',
        ]);

        $previousAppKey = getenv('APP_KEY');
        $previousMailUsername = getenv('MAIL_USERNAME');
        putenv('APP_KEY');
        putenv('MAIL_USERNAME');

        try {
            $this->artisan('runtime:sync-environment', [
                '--clear-missing' => true,
                '--process-keys' => 'APP_ENV,APP_DEBUG',
            ])
                ->assertSuccessful();

            $contents = File::get($temporaryDirectory.DIRECTORY_SEPARATOR.'.env');

            $this->assertStringContainsString('APP_KEY=base64:persistent-key', $contents);
            $this->assertStringContainsString('APP_NAME="Panel Name"', $contents);
            $this->assertStringNotContainsString('MAIL_USERNAME=', $contents);
        } finally {
            $this->restoreEnvironment('APP_KEY', $previousAppKey);
            $this->restoreEnvironment('MAIL_USERNAME', $previousMailUsername);
            File::deleteDirectory($temporaryDirectory);
        }
    }

    private function restoreEnvironment(string $key, string|false $value): void
    {
        if ($value === false) {
            putenv($key);

            return;
        }

        putenv($key.'='.$value);
    }
}
