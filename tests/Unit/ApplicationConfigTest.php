<?php

namespace Tests\Unit;

use Tests\TestCase;

class ApplicationConfigTest extends TestCase
{
    public function test_only_product_supported_locales_are_configured(): void
    {
        $this->assertSame(
            ['en', 'es', 'pt-BR'],
            config('app.supported_locales')
        );
    }
}
