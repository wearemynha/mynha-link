<?php

namespace Tests\Feature;

use App\Http\Controllers\InstallerController;
use App\Models\Button;
use App\Models\User;
use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Support\CreatesApplicationData;

class InstallationTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

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
        EnvEditor::shouldReceive('addKey')->once()->with('ADMIN_EMAIL', 'admin@mynha.example');

        $lockPath = base_path('INSTALLERLOCK');
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
