<?php

namespace Tests\Feature;

use App\Http\Controllers\InstallerController;
use App\Models\Button;
use App\Models\User;
use App\Services\AdvancedConfigManager;
use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Support\CreatesApplicationData;

class InstallationTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_installer_landing_page_renders_without_a_query_string_before_the_users_table_exists(): void
    {
        Route::post('/_test/edit-config-installer', fn () => response()->noContent())
            ->name('editConfigInstaller');
        Route::getRoutes()->refreshNameLookups();

        Schema::shouldReceive('hasTable')
            ->once()
            ->with('users')
            ->andReturn(false);
        DB::shouldReceive('table')
            ->never();

        $html = app(InstallerController::class)->showInstaller()->render();

        $this->assertStringContainsString('language-form', $html);
    }

    public function test_installer_creates_a_missing_locale_setting(): void
    {
        EnvEditor::shouldReceive('keyExists')->once()->with('LOCALE')->andReturn(false);
        EnvEditor::shouldReceive('addKey')->once()->with('LOCALE', '"pt-BR"');

        $response = app(InstallerController::class)->editConfigInstaller(
            Request::create('/editConfigInstaller', 'POST', ['value' => 'pt-BR'])
        );

        $this->assertSame('http://localhost', $response->getTargetUrl());
    }

    public function test_installer_renders_the_dependency_step_for_a_bare_query_string(): void
    {
        $this->app->instance('request', Request::create('/?2'));

        $html = app(InstallerController::class)->showInstaller()->render();

        $this->assertStringContainsString('BCMath:', $html);
        $this->assertStringContainsString('PostgreSQL:', $html);
    }

    public function test_installer_creates_missing_final_configuration_settings(): void
    {
        User::factory()->create([
            'littlelink_name' => 'mynha-admin',
        ]);

        foreach (['ALLOW_REGISTRATION', 'REGISTER_AUTH', 'HOME_URL', 'APP_NAME'] as $key) {
            EnvEditor::shouldReceive('keyExists')->once()->with($key)->andReturn(false);
        }

        EnvEditor::shouldReceive('addKey')->once()->with('ALLOW_REGISTRATION', 'true');
        EnvEditor::shouldReceive('addKey')->once()->with('REGISTER_AUTH', 'verified');
        EnvEditor::shouldReceive('addKey')->once()->with('HOME_URL', '');
        EnvEditor::shouldReceive('addKey')->once()->with('APP_NAME', '"Mynha Link"');

        $advancedConfigManager = \Mockery::mock(AdvancedConfigManager::class);
        $advancedConfigManager->shouldReceive('finalizeInstallation')->once();
        $this->app->instance(AdvancedConfigManager::class, $advancedConfigManager);

        $installingPath = storage_path('app/INSTALLING');
        $installingContents = file_exists($installingPath) ? file_get_contents($installingPath) : null;

        try {
            $response = app(InstallerController::class)->options(Request::create('/options', 'POST', [
                'register' => 'Yes',
                'verify' => 'Yes',
                'page' => 'No',
                'app' => 'Mynha Link',
            ]));

            $this->assertSame('http://localhost/dashboard', $response->getTargetUrl());
        } finally {
            if ($installingContents !== null && !file_exists($installingPath)) {
                file_put_contents($installingPath, $installingContents);
            }
        }
    }

    public function test_database_preparation_seeds_an_empty_database_and_is_idempotent(): void
    {
        $controller = app(InstallerController::class);

        $firstResponse = $controller->prepareDatabase();

        $this->assertSame('http://localhost?4', $firstResponse->getTargetUrl());
        $this->assertDatabaseCount('pages', 1);
        $this->assertGreaterThan(100, \DB::table('buttons')->count());

        $pageCount = \DB::table('pages')->count();
        $buttonCount = \DB::table('buttons')->count();

        $secondResponse = $controller->prepareDatabase();

        $this->assertSame('http://localhost?4', $secondResponse->getTargetUrl());
        $this->assertSame($pageCount, \DB::table('pages')->count());
        $this->assertSame($buttonCount, \DB::table('buttons')->count());
    }

    public function test_database_preparation_preserves_existing_application_data(): void
    {
        $controller = app(InstallerController::class);
        $controller->prepareDatabase();

        $user = User::factory()->create([
            'littlelink_name' => 'existing-user',
        ]);

        $controller->prepareDatabase();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
            'littlelink_name' => 'existing-user',
        ]);
    }

    public function test_installer_creates_a_verified_administrator_with_a_strong_password(): void
    {
        EnvEditor::shouldReceive('keyExists')->once()->with('ADMIN_EMAIL')->andReturn(false);
        EnvEditor::shouldReceive('addKey')->once()->with('ADMIN_EMAIL', '"admin@mynha.example"');

        $lockPath = storage_path('app/INSTALLERLOCK');
        $lockAlreadyExisted = file_exists($lockPath);

        try {
            $response = app(InstallerController::class)->createAdmin($this->installerRequest([
                'name' => 'Mynha Admin',
                'handle' => 'mynha-admin',
                'email' => 'admin@mynha.example',
                'password' => 'StrongPassword!123',
                'password_confirmation' => 'StrongPassword!123',
            ]));

            $this->assertSame('http://localhost?5', $response->getTargetUrl());

            $admin = User::where('email', 'admin@mynha.example')->firstOrFail();

            $this->assertSame('admin', $admin->role);
            $this->assertSame('no', $admin->block);
            $this->assertSame('mynha-admin', $admin->littlelink_name);
            $this->assertNotNull($admin->email_verified_at);
            $this->assertTrue(Hash::check('StrongPassword!123', $admin->password));
        } finally {
            if (!$lockAlreadyExisted && file_exists($lockPath)) {
                unlink($lockPath);
            }
        }
    }

    public function test_installer_rejects_a_weak_administrator_password(): void
    {
        try {
            app(InstallerController::class)->createAdmin($this->installerRequest([
                'name' => 'Mynha Admin',
                'handle' => 'mynha-admin',
                'email' => 'admin@mynha.example',
                'password' => 'weakpassword',
                'password_confirmation' => 'weakpassword',
            ]));

            $this->fail('A weak password should not be accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
            $this->assertDatabaseCount('users', 0);
        }
    }

    private function installerRequest(array $data): Request
    {
        $request = Request::create('/create-admin', 'POST', $data);
        return $request;
    }
}
