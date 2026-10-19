<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => env('SEED_USER_EMAIL', 'admin@example.com')],
            [
                'name' => 'Admin',
                'password' => env('SEED_USER_PASSWORD', 'password'),
            ],
        );

        if ($user->schedules()->doesntExist()) {
            Schedule::factory(3)->for($user)->create();
        }
    }
}
