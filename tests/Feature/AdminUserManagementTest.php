<?php

namespace Tests\Feature;

use App\Services\AdvancedConfigManager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_regular_user_cannot_access_user_administration(): void
    {
        $user = $this->createUser(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('showUsers'))
            ->assertRedirect(url('dashboard'));
    }

    public function test_admin_can_view_block_verify_and_edit_a_user(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $user = $this->createUser([
            'name' => 'Managed User',
            'email' => 'managed@mynha.example',
            'littlelink_name' => 'managed-user',
            'role' => 'user',
            'block' => 'no',
        ]);

        $usersResponse = $this->actingAs($admin)
            ->get(route('showUsers'))
            ->assertOk();

        $usersHtml = $usersResponse->getContent();
        $livewireScriptPosition = strpos($usersHtml, 'livewire/livewire');
        $sortableScriptPosition = strpos($usersHtml, 'assets/js/livewire-sortable.js');

        $this->assertNotFalse($livewireScriptPosition);
        $this->assertNotFalse($sortableScriptPosition);
        $this->assertLessThan($sortableScriptPosition, $livewireScriptPosition);

        $this->actingAs($admin)
            ->get(route('showUser', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('Managed User');

        $advancedConfigManager = \Mockery::mock(AdvancedConfigManager::class);
        $advancedConfigManager->shouldReceive('ensureExists')
            ->once()
            ->andReturn(storage_path('templates/advanced-config.php'));
        $this->app->instance(AdvancedConfigManager::class, $advancedConfigManager);

        $this->actingAs($admin)
            ->get(route('showConfig'))
            ->assertOk()
            ->assertSee('custom_url_prefix');

        $this->actingAs($admin)
            ->get(route('blockUser', ['block' => 'no', 'id' => $user->id]))
            ->assertRedirect('admin/users/all');

        $this->assertSame('yes', $user->fresh()->block);

        $this->actingAs($admin)
            ->get(route('verifyUser', ['verify' => 'false', 'id' => $user->id]))
            ->assertOk();

        $this->assertNull($user->fresh()->email_verified_at);

        $this->actingAs($admin)->post(route('editUser', ['id' => $user->id]), [
            'name' => 'Updated User',
            'email' => 'updated@mynha.example',
            'password' => '',
            'littlelink_name' => 'updated-user',
            'littlelink_description' => 'Updated description',
            'role' => 'vip',
            'theme' => 'galaxy',
        ])->assertRedirect('admin/users/all');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@mynha.example',
            'littlelink_name' => 'updated-user',
            'littlelink_description' => 'Updated description',
            'role' => 'vip',
            'theme' => 'galaxy',
        ]);
    }

    public function test_admin_can_delete_a_user_and_their_links(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $user = $this->createUser();
        $button = $this->createButton();
        $link = $this->createLink($user, $button);

        $this->actingAs($admin)
            ->get(route('deleteUser', ['id' => $user->id]))
            ->assertRedirect('admin/users/all');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('links', ['id' => $link->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
