<?php

namespace Tests\Feature;

use App\Services\RuntimeConfigurationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_admin_can_disable_maintenance_mode_with_the_dedicated_post_action(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $runtimeConfigurationManager = Mockery::mock(RuntimeConfigurationManager::class);
        $runtimeConfigurationManager->shouldReceive('setMaintenanceMode')->once()->with(false);
        $runtimeConfigurationManager->shouldReceive('rebuildCache')->once();
        $this->app->instance(RuntimeConfigurationManager::class, $runtimeConfigurationManager);

        $this->actingAs($admin)
            ->post(route('disableMaintenance'))
            ->assertRedirect('/dashboard');
    }

    public function test_guest_and_regular_user_cannot_disable_maintenance_mode(): void
    {
        $runtimeConfigurationManager = Mockery::mock(RuntimeConfigurationManager::class);
        $runtimeConfigurationManager->shouldNotReceive('setMaintenanceMode');
        $runtimeConfigurationManager->shouldNotReceive('rebuildCache');
        $this->app->instance(RuntimeConfigurationManager::class, $runtimeConfigurationManager);

        $this->post(route('disableMaintenance'))
            ->assertRedirect('/login');

        $user = $this->createUser();

        $this->actingAs($user)
            ->post(route('disableMaintenance'))
            ->assertRedirect('/dashboard');
    }

    public function test_maintenance_page_uses_a_post_form_and_ignores_the_legacy_query_string(): void
    {
        Route::get('/_test/maintenance', fn () => view('maintenance'));
        Route::getRoutes()->refreshNameLookups();

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/_test/maintenance?maintenance=off')
            ->assertOk()
            ->assertSee('action="'.route('disableMaintenance').'"', false)
            ->assertSee('method="POST"', false)
            ->assertDontSee('?maintenance=off', false);
    }
}
