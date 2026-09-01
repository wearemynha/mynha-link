<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesApplicationData;
use Tests\TestCase;

class DashboardBrandingTest extends TestCase
{
    use CreatesApplicationData;
    use RefreshDatabase;

    public function test_admin_dashboard_has_mynha_branding_and_site_statistics(): void
    {
        $this->seed(PageSeeder::class);
        $admin = $this->createUser(['role' => 'admin']);
        config(['linkstack.single_user_mode' => false]);

        $this->actingAs($admin)->get(route('panelIndex'))->assertOk()
            ->assertSee('class="mynha-dashboard"', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('assets/mynha-assets/mynha-icon-preto-verde.svg', false)
            ->assertSee('class="mynha-dashboard-greeting"', false)
            ->assertSee('class="mynha-dashboard-welcome"', false)
            ->assertSee('id="site-statistics-heading"', false)
            ->assertSee('id="registrations-heading"', false)
            ->assertSee('id="active-users-heading"', false)
            ->assertSee(__('messages.You haven’t added any links yet'))
            ->assertDontSee('detect-dark-mode.js', false)
            ->assertDontSee('assets/js/plugins/setting.js', false);
    }

    public function test_user_dashboard_shows_only_own_links_without_admin_statistics(): void
    {
        $this->seed(PageSeeder::class);
        $user = $this->createUser();
        $button = $this->createButton();
        $this->createLink($user, $button, ['title' => 'My portfolio', 'click_number' => 7]);
        $this->createLink($this->createUser(), $button, ['title' => 'Another user private label']);

        $this->actingAs($user)->get(route('panelIndex'))->assertOk()
            ->assertSee('My portfolio')
            ->assertSee('7 —', false)
            ->assertDontSee('Another user private label')
            ->assertDontSee('id="site-statistics-heading"', false)
            ->assertDontSee('id="registrations-heading"', false)
            ->assertDontSee('id="active-users-heading"', false)
            ->assertDontSee(__('messages.You haven’t added any links yet'));
    }

    public function test_single_user_mode_hides_site_statistics(): void
    {
        $this->seed(PageSeeder::class);
        config(['linkstack.single_user_mode' => true]);

        $this->actingAs($this->createUser(['role' => 'admin']))->get(route('panelIndex'))
            ->assertOk()->assertDontSee('id="site-statistics-heading"', false);
    }

    public function test_internal_panel_pages_keep_the_mynha_skin(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->get(route('showTheme'))->assertOk()
            ->assertSee('class="mynha-dashboard"', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('assets/mynha-assets/mynha-icon-preto-verde.svg', false)
            ->assertDontSee('detect-dark-mode.js', false)
            ->assertDontSee('assets/js/plugins/setting.js', false);
    }

    public function test_dashboard_forms_and_block_selector_use_shared_mynha_branding(): void
    {
        $blockSelectorTemplate = file_get_contents(base_path('resources/views/studio/edit-link.blade.php'));

        $this->assertStringContainsString('mynha-block-option', $blockSelectorTemplate);

        $appearanceTemplate = file_get_contents(base_path('resources/views/studio/page.blade.php'));

        $this->assertStringContainsString("asset('assets/mynha-assets/mynha-icon-preto-verde.svg')", $appearanceTemplate);

        $dashboardCss = file_get_contents(base_path('assets/mynha-assets/css/dashboard.css'));

        $this->assertStringContainsString('input.form-control[type="file"]::file-selector-button', $dashboardCss);
        $this->assertStringContainsString('background: var(--mynha-color-action) !important;', $dashboardCss);
        $this->assertStringContainsString('.mynha-block-option', $dashboardCss);
        $this->assertStringContainsString('.mynha-admin-tabs', $dashboardCss);
        $this->assertStringContainsString('.mynha-user-table', $dashboardCss);
        $this->assertStringContainsString(':is(thead, thead tr, thead th)', $dashboardCss);
        $this->assertStringNotContainsString(':is(table, thead, tbody, tr, th, td)', $dashboardCss);

        $modalTemplate = file_get_contents(base_path('resources/views/components/modal.blade.php'));

        $this->assertStringContainsString('data-dismiss="modal" data-bs-dismiss="modal"', $modalTemplate);
    }
}
