<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_renders_the_three_mynha_links_from_the_installation_defaults(): void
    {
        $this->seed(PageSeeder::class);
        config(['advanced-config' => require storage_path('templates/advanced-config.php')]);

        $this->get('/demo-page')->assertOk()
            ->assertSee('class="mynha-ui mynha-demo"', false)
            ->assertSee('assets/mynha-assets/mynha.css', false)
            ->assertSee('assets/mynha-assets/mynha-icon-preto-verde.svg', false)
            ->assertSee('assets/mynha-assets/mynha-site-favicon.png', false)
            ->assertSee('bi-instagram', false)
            ->assertDontSee('skeleton-auto.css', false)
            ->assertDontSee('assets/linkstack/images/logo.svg', false)
            ->assertDontSee('background:#848948;', false)
            ->assertSee('href="https://www.instagram.com/wearemynha/"', false)
            ->assertSee('href="https://mynha.com.br/"', false)
            ->assertSee('href="https://docs.google.com/forms/d/e/1FAIpQLScYqsSq8IhLNsY-JD0U1frGohndQEBk9uOh_MgRWjEDZmFe0w/viewform"', false)
            ->assertSeeInOrder(['<span>Mynha</span>', '<span>Instagram</span>', '<span>Quer ser nosso Designer?</span>'], false)
            ->assertDontSee('href="https://github.com/linkstackorg/linkstack"', false)
            ->assertDontSee('href="https://linkstack.org"', false)
            ->assertDontSee('href="https://linkstack.org/donate"', false)
            ->assertDontSee('Help us out');
    }
}
