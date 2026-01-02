<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            VatCodeSeeder::class,
            ChartOfAccountSeeder::class,
        ]);
    }

    /**
     * Seed demo data for testing/demonstration
     */
    public function demo(): void
    {
        $this->call([
            DemoSeeder::class,
        ]);
    }
}
