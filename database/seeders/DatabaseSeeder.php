<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            ContentSeeder::class,
        ]);

        // Admin login. In production set ADMIN_EMAIL / ADMIN_PASSWORD in the
        // environment; the defaults below are for local development only.
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@isdb.example')],
            [
                'name' => env('ADMIN_NAME', 'Administrateur ISDB'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'is_admin' => true,
            ],
        );
    }
}
