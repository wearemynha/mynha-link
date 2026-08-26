<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_public_profile_renders_user_information_and_links(): void
    {
        $user = $this->createUser([
            'name' => 'Public User',
            'littlelink_name' => 'public-user',
            'littlelink_description' => 'Public description',
            'theme' => 'default',
        ]);
        $button = $this->createButton(['name' => 'github', 'alt' => 'GitHub']);
        $this->createLink($user, $button, [
            'title' => 'Portfolio',
            'link' => 'https://portfolio.example',
        ]);

        $this->get(route('littlelink', ['littlelink' => 'public-user']))
            ->assertOk()
            ->assertSeeText('Public User')
            ->assertSeeText('Public description')
            ->assertSeeText('Portfolio')
            ->assertSee('https://portfolio.example', false);
    }

    public function test_missing_and_blocked_public_profiles_return_not_found(): void
    {
        $blocked = $this->createUser([
            'littlelink_name' => 'blocked-user',
            'block' => 'yes',
            'theme' => 'default',
        ]);

        $this->get(route('littlelink', ['littlelink' => $blocked->littlelink_name]))
            ->assertNotFound();

        $this->get(route('littlelink', ['littlelink' => 'missing-user']))
            ->assertNotFound();
    }
}
