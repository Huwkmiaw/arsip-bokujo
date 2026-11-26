<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@arsip.test'],
            [
                'name' => 'Admin Arsip',
                'password' => 'admin12345',
                'is_admin' => true,
            ],
        );

        $this->call(BookSeeder::class);
    }
}
