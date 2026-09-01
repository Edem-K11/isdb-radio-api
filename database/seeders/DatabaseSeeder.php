<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Safe to run on every deploy: the admin account is always synced from the
     * ADMIN_* environment variables (so you can rotate the password by changing
     * the env var and redeploying), while the demo content and default settings
     * are only created on the very first run and never overwrite dashboard edits.
     */
    public function run(): void
    {
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

        // First run only — a redeploy must not clobber what's been edited in the
        // back-office.
        if (Episode::query()->exists()) {
            return;
        }

        $this->call([
            SettingsSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
