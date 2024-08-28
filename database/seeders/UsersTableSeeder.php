<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'John Doe',
                'role' => 1, // Assuming 1 is an admin role
                'email' => 'john@example.com',
                'email_verified_at' => now(),
                'phone_number' => '123-456-7890',
                'address' => '123 Main St, Anytown, USA',
                'password' => Hash::make('password'), // Encrypt the password
//                'remember_token' => \Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'role' => 0, // Assuming 0 is a regular user role
                'email' => 'jane@example.com',
                'email_verified_at' => now(),
                'phone_number' => '098-765-4321',
                'address' => '456 Elm St, Othertown, USA',
                'password' => Hash::make('password'), // Encrypt the password
//                'remember_token' => \Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
