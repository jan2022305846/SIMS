<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'school_id' => 'ADMIN001',
            'email' => 'admin@ustp.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'Supply Office',
        ]);

        User::create([
            'name' => 'Faculty User',
            'username' => 'faculty1',
            'school_id' => 'FAC001',
            'email' => 'faculty@ustp.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'department' => 'Computer Science',
        ]);
    }
}
