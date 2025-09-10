<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Sample Admin user
        User::create([
            'name'              => 'Denver Gemino',
            'username'          => 'denver123',
            'email'             => 'denverg@ustp.edu.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'remember_token'    => Str::random(10),
        ]);

        // Seed Sample Regular user
        User::create([
            'name'              => 'Mark Rey Embudo',
            'department'        => 'BSIT',
            'username'          => 'markrey123',
            'email'             => 'markreyembudo@ustp.edu.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => 'faculty',
            'remember_token'    => Str::random(10),
        ]);
    }
}
