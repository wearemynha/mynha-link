<?php

namespace Tests\Unit;

use Tests\TestCase;

class ApplicationConfigTest extends TestCase
{
    public function test_default_supported_locales_are_parsed(): void
    {
        $this->assertSame(
            ['de', 'es', 'pt', 'zh', 'ms'],
            config('app.supported_locales')
        );
    }
}
