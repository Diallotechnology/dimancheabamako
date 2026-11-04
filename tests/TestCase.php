<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Les vues Inertia (admin.blade.php) utilisent @vite : pas de build requis en test.
        $this->withoutVite();
    }
}
