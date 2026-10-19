<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Tidak memakai factory/Faker agar bisa dijalankan di image produksi
     * (composer install --no-dev).
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
            $user->schedules()->createMany([
                [
                    'title' => 'Kuliah Konstruksi dan Evolusi Perangkat Lunak',
                    'description' => 'Membahas CI/CD dan Docker',
                    'location' => 'Ruang 301',
                    'start_at' => now()->addDay()->setTime(8, 0),
                    'end_at' => now()->addDay()->setTime(9, 30),
                ],
                [
                    'title' => 'Rapat kelompok',
                    'description' => 'Pembagian tugas proyek akhir',
                    'location' => 'Perpustakaan',
                    'start_at' => now()->addDays(2)->setTime(13, 0),
                    'end_at' => now()->addDays(2)->setTime(14, 30),
                ],
                [
                    'title' => 'Olahraga pagi',
                    'start_at' => now()->addDays(3)->setTime(6, 0),
                    'end_at' => now()->addDays(3)->setTime(7, 0),
                ],
            ]);
        }
    }
}
