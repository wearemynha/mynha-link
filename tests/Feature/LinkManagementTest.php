<?php

namespace Tests\Feature;

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class LinkManagementTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_user_can_create_and_edit_a_link(): void
    {
        $user = $this->createUser();
        $this->createButton(['id' => 1, 'name' => 'custom']);

        $this->actingAs($user)->post(route('addLink'), [
            'typename' => 'link',
            'title' => 'Mynha Website',
            'link' => 'https://mynha.example',
            'GetSiteIcon' => 0,
        ])->assertRedirect('studio/links');

        $this->assertDatabaseHas('links', [
            'user_id' => $user->id,
            'button_id' => 1,
            'title' => 'Mynha Website',
            'link' => 'https://mynha.example',
            'type' => 'link',
        ]);

        $link = Link::where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)->post(route('addLink'), [
            'typename' => 'link',
            'linkid' => $link->id,
            'title' => 'Updated Website',
            'link' => 'https://updated.mynha.example',
            'GetSiteIcon' => 0,
        ])->assertRedirect('studio/links');

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'user_id' => $user->id,
            'title' => 'Updated Website',
            'link' => 'https://updated.mynha.example',
        ]);
    }

    public function test_user_can_sort_and_delete_owned_links(): void
    {
        $user = $this->createUser();
        $button = $this->createButton();
        $first = $this->createLink($user, $button, ['title' => 'First', 'order' => 10]);
        $second = $this->createLink($user, $button, ['title' => 'Second', 'order' => 11]);

        $this->actingAs($user)->postJson(route('sortLinks'), [
            'linkOrders' => [$second->id, $first->id],
            'currentPage' => 1,
            'perPage' => 10,
        ])->assertOk()->assertJson([
            'status' => 'OK',
            'linkOrders' => [
                (string) $second->id => 0,
                (string) $first->id => 1,
            ],
        ]);

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);

        $this->actingAs($user)
            ->get(route('deleteLink', ['id' => $first->id]))
            ->assertRedirect('/studio/links');

        $this->assertDatabaseMissing('links', ['id' => $first->id]);
        $this->assertDatabaseHas('links', ['id' => $second->id]);
    }

    public function test_user_cannot_edit_or_delete_another_users_link(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $button = $this->createButton(['id' => 1, 'name' => 'custom']);
        $link = $this->createLink($owner, $button);

        $this->actingAs($otherUser)->post(route('addLink'), [
            'typename' => 'link',
            'linkid' => $link->id,
            'title' => 'Unauthorized change',
            'link' => 'https://attacker.example',
            'GetSiteIcon' => 0,
        ])->assertForbidden();

        $this->actingAs($otherUser)
            ->get(route('deleteLink', ['id' => $link->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'user_id' => $owner->id,
            'title' => 'Example',
            'link' => 'https://example.com',
        ]);
    }
}
