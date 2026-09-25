<?php

namespace Tests;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Middleware `role:*` dan spatie butuh 3 role dari project.md Bab 3.2.
         * Di-seed otomatis supaya setiap uji fokus ke skenario-nya sendiri.
         */
        if (Schema::hasTable('roles')) {
            $this->seed(RoleSeeder::class);
        }
    }
}
