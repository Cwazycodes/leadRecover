<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Render Blade without a compiled Vite manifest so the suite runs on a
        // fresh checkout / CI without first building front-end assets.
        $this->withoutVite();
    }
}
