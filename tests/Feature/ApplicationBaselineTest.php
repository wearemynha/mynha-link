<?php

namespace Tests\Feature;

use App\Http\Livewire\UserTable;
use App\Models\Link;
use App\Models\User;
use App\Services\VCardBuilder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tests\TestCase;

class ApplicationBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_linkstack_uri_builds_a_protocol_relative_application_url(): void
    {
        $this->assertSame('//localhost/assets/theme.css', linkstack_uri('assets/theme.css'));
    }

    public function test_essential_routes_are_registered(): void
    {
        foreach (['home', 'login', 'panelIndex', 'littlelink', 'vcard'] as $route) {
            $this->assertTrue(Route::has($route), "Expected route [{$route}] to be registered.");
        }
    }

    public function test_named_routes_are_unique_and_littlelink_is_canonical(): void
    {
        $routeNames = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter();

        $this->assertCount($routeNames->unique()->count(), $routeNames);
        $this->assertSame('http://localhost/@example', route('littlelink', ['littlelink' => 'example']));
    }

    public function test_home_page_is_available(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/')->assertOk()
            ->assertSee('class="mynha-ui mynha-home"', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('assets/mynha-assets/mynha-icon-preto-verde.svg', false)
            ->assertSee(route('login'), false)
            ->assertSee('src="'.url('/demo-page').'"', false)
            ->assertDontSee('hope-ui.min.css', false)
            ->assertDontSee('detect-dark-mode.js', false);
    }

    public function test_home_page_preserves_its_custom_message(): void
    {
        $this->seed(PageSeeder::class);
        \DB::table('pages')->update(['home_message' => '<p>Welcome to our company links.</p>']);

        $this->get('/')->assertOk()
            ->assertSee('<p>Welcome to our company links.</p>', false)
            ->assertSee('content="Welcome to our company links."', false);
    }

    public function test_home_page_offers_the_dashboard_to_authenticated_users(): void
    {
        $this->seed(PageSeeder::class);

        $this->actingAs(User::factory()->create())->get('/')->assertOk()
            ->assertSee('href="'.url('dashboard').'"', false)
            ->assertDontSee('href="'.route('login').'"', false);
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

    public function test_vcard_generation_contains_and_validates_contact_data(): void
    {
        $output = app(VCardBuilder::class)->build([
            'prefix' => 'Dra.',
            'first_name' => 'Maria',
            'middle_name' => 'Clara',
            'last_name' => 'Silva',
            'suffix' => 'PhD',
            'organization' => 'Mynha',
            'vtitle' => 'Diretora',
            'role' => 'Gestão',
            'email' => 'maria@example.com',
            'work_email' => 'maria@mynha.example',
            'work_url' => 'https://mynha.example',
            'home_phone' => '+55 11 1111-1111',
            'work_phone' => '+55 11 2222-2222',
            'cell_phone' => '+55 11 99999-9999',
            'home_address_street' => 'Rua A, 10',
            'home_address_city' => 'São Paulo',
            'home_address_state' => 'SP',
            'home_address_zip' => '01000-000',
            'home_address_country' => 'Brasil',
            'work_address_street' => 'Avenida B, 20',
            'work_address_city' => 'São Paulo',
            'work_address_state' => 'SP',
            'work_address_zip' => '02000-000',
            'work_address_country' => 'Brasil',
        ]);

        $vcard = \Sabre\VObject\Reader::read($output);

        $this->assertStringContainsString('BEGIN:VCARD', $output);
        $this->assertStringContainsString('END:VCARD', $output);
        $this->assertSame('Dra. Maria Clara Silva PhD', (string) $vcard->FN);
        $this->assertSame(['Silva', 'Maria', 'Clara', 'Dra.', 'PhD'], $vcard->N->getParts());
        $this->assertSame('Mynha', (string) $vcard->ORG);
        $this->assertSame('Diretora', (string) $vcard->TITLE);
        $this->assertSame('Gestão', (string) $vcard->ROLE);
        $this->assertCount(2, $vcard->EMAIL);
        $this->assertSame('maria@mynha.example', (string) $vcard->getByType('EMAIL', 'WORK'));
        $this->assertSame('+55 11 99999-9999', (string) $vcard->getByType('TEL', 'CELL'));
        $this->assertSame('Rua A, 10', $vcard->getByType('ADR', 'HOME')->getParts()[2]);
        $this->assertSame([], $vcard->validate());
    }

    public function test_vcard_endpoint_downloads_the_contact_and_counts_the_click(): void
    {
        $user = User::factory()->create();
        $link = new Link([
            'title' => 'Contato',
            'type' => 'vcard',
            'link' => json_encode([
                'first_name' => 'Maria',
                'last_name' => 'Silva',
                'email' => 'maria@example.com',
            ]),
        ]);
        $link->user_id = $user->id;
        $link->save();

        $response = $this->get(route('vcard', ['id' => $link->id]));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/vcard; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="contact.vcf"');

        $vcard = \Sabre\VObject\Reader::read($response->getContent());

        $this->assertSame('Maria Silva', (string) $vcard->FN);
        $this->assertSame('maria@example.com', (string) $vcard->EMAIL);
        $this->assertSame(1, $link->fresh()->click_number);
    }
}
