<?php

namespace Tests\Support;

use App\Models\Button;
use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesApplicationData
{
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'littlelink_name' => 'user-'.Str::lower(Str::random(10)),
            'role' => 'user',
            'block' => 'no',
        ], $attributes));
    }

    protected function createButton(array $attributes = []): Button
    {
        $button = new Button();
        $button->forceFill(array_merge([
            'name' => 'github',
            'alt' => 'GitHub',
            'exclude' => false,
            'group' => 'default',
            'mb' => false,
        ], $attributes));
        $button->save();

        return $button;
    }

    protected function createLink(User $user, Button $button, array $attributes = []): Link
    {
        $link = new Link();
        $link->forceFill(array_merge([
            'link' => 'https://example.com',
            'title' => 'Example',
            'type' => 'predefined',
            'type_params' => '[]',
            'order' => 0,
            'click_number' => 0,
            'up_link' => 'no',
            'user_id' => $user->id,
            'button_id' => $button->id,
            'custom_css' => '',
            'custom_icon' => 'fa-external-link',
        ], $attributes));
        $link->save();

        return $link;
    }
}
