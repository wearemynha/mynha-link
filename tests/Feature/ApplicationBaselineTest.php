<?php

namespace Tests\Feature;

use App\Http\Livewire\UserTable;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use JeroenDesloovere\VCard\VCard;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tests\TestCase;

class ApplicationBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_essential_routes_are_registered(): void
    {
        foreach (['home', 'login', 'panelIndex', 'littlelink', 'vcard'] as $route) {
            $this->assertTrue(Route::has($route), "Expected route [{$route}] to be registered.");
        }
    }

    public function test_home_page_is_available(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/')->assertOk();
    }

    public function test_login_page_is_available(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_forwarded_headers_are_honored_for_a_trusted_proxy(): void
    {
        config(['trustedproxy.proxies' => '*']);

        Route::get('/_test/trusted-proxy', function (Request $request) {
            return response()->json([
                'ip' => $request->ip(),
                'secure' => $request->isSecure(),
            ]);
        });

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
            ->withHeaders([
                'X-Forwarded-For' => '203.0.113.25',
                'X-Forwarded-Proto' => 'https',
            ])
            ->get('/_test/trusted-proxy')
            ->assertOk()
            ->assertJson([
                'ip' => '203.0.113.25',
                'secure' => true,
            ]);
    }

    public function test_qr_code_generation_returns_svg_without_imagick(): void
    {
        $svg = QrCode::size(100)->generate('https://example.com');

        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_user_table_livewire_component_renders(): void
    {
        Livewire::test(UserTable::class)
            ->assertOk();
    }

    public function test_vcard_generation_contains_contact_data(): void
    {
        $vcard = new VCard();
        $vcard->addName('Silva', 'Maria');
        $vcard->addEmail('maria@example.com');

        $output = $vcard->buildVCard();

        $this->assertStringContainsString('BEGIN:VCARD', $output);
        $this->assertStringContainsString('Maria', $output);
        $this->assertStringContainsString('maria@example.com', $output);
        $this->assertStringContainsString('END:VCARD', $output);
    }
}
