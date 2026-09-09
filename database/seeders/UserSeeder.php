<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('users')->insert([
    [
        'name' => 'User Pelapor',
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ],
    [
        'name' => 'Moderator Safety',
        'email' => 'moderator@example.com',
        'password' => bcrypt('password'),
        'role' => 'moderator',
    ],
    [
        'name' => 'ERM Supervisor',
        'email' => 'erm@example.com',
        'password' => bcrypt('password'),
        'role' => 'erm',
    ]
]);

    }
}
