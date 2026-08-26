<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class UploadAndThemeTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_user_can_upload_a_profile_image(): void
    {
        $user = $this->createUser([
            'name' => 'Upload User',
            'littlelink_name' => 'upload-user',
        ]);
        $pattern = base_path("assets/img/{$user->id}_*");
        $before = glob($pattern) ?: [];

        $this->actingAs($user)->post(route('editPage'), [
            'name' => 'Upload User',
            'littlelink_name' => 'upload-user',
            'pageDescription' => 'Profile with image',
            'image' => $this->pngUpload('avatar.png'),
        ])->assertRedirect('/studio/page');

        $created = array_values(array_diff(glob($pattern) ?: [], $before));
        $this->createdFiles = array_merge($this->createdFiles, $created);

        $this->assertCount(1, $created);
        $this->assertFileExists($created[0]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'littlelink_name' => 'upload-user',
            'littlelink_description' => 'Profile with image',
        ]);
    }

    public function test_profile_rejects_a_non_image_upload(): void
    {
        $user = $this->createUser([
            'name' => 'Invalid Upload User',
            'littlelink_name' => 'invalid-upload-user',
        ]);
        $pattern = base_path("assets/img/{$user->id}_*");
        $before = glob($pattern) ?: [];

        $this->actingAs($user)->from('/studio/page')->post(route('editPage'), [
            'name' => 'Invalid Upload User',
            'littlelink_name' => 'invalid-upload-user',
            'pageDescription' => 'Invalid upload',
            'image' => UploadedFile::fake()->createWithContent('payload.txt', 'not an image'),
        ])->assertRedirect('/studio/page')->assertSessionHasErrors('image');

        $this->assertSame($before, glob($pattern) ?: []);
    }

    public function test_user_can_upload_a_custom_theme_background(): void
    {
        $user = $this->createUser();
        $pattern = base_path("assets/img/background-img/{$user->id}_*");
        $before = glob($pattern) ?: [];

        $this->actingAs($user)->post(route('themeBackground'), [
            'image' => $this->pngUpload('background.png'),
        ])->assertRedirect('/studio/theme');

        $created = array_values(array_diff(glob($pattern) ?: [], $before));
        $this->createdFiles = array_merge($this->createdFiles, $created);

        $this->assertCount(1, $created);
        $this->assertFileExists($created[0]);
    }

    public function test_user_can_select_and_render_an_installed_theme(): void
    {
        $user = $this->createUser([
            'name' => 'Themed User',
            'littlelink_name' => 'themed-user',
            'littlelink_description' => 'Themed profile',
            'theme' => 'default',
        ]);
        $button = $this->createButton(['name' => 'github', 'alt' => 'GitHub']);
        $this->createLink($user, $button, ['title' => 'Themed Link']);

        $this->actingAs($user)
            ->get(route('showTheme'))
            ->assertOk()
            ->assertSeeText('Select a theme');

        $this->actingAs($user)->post(route('editTheme'), [
            'theme' => 'galaxy',
        ])->assertRedirect('/studio/theme');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'theme' => 'galaxy',
        ]);

        $this->get(route('littlelink', ['littlelink' => 'themed-user']))
            ->assertOk()
            ->assertSee('LinkStack Theme: "galaxy"', false)
            ->assertSee('themes/galaxy/skeleton-auto.css', false)
            ->assertSeeText('Themed Link');

        $this->get(route('theme', ['littlelink' => 'themed-user']))
            ->assertOk()
            ->assertSeeText('Theme: Galaxy')
            ->assertSeeText('Theme Name: Galaxy');
    }

    private function pngUpload(string $name): UploadedFile
    {
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $contents);
    }
}
