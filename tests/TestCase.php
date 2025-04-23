<?php

namespace Tests;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected function setUp(): void
    {
        parent::setUp();

        // Force re-migration for in-memory sqlite
        Artisan::call('migrate:fresh');
    }


}
