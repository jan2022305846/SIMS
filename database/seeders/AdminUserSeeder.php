<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if users already exist to avoid duplicates
        if (User::where('username', 'admin')->exists()) {
            $this->command->info('Admin user already exists. Skipping seeder.');
            return;
        }

        // Prepare base user data
        $adminData = [
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@ustp.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'Supply Office',
        ];

        $facultyData = [
            'name' => 'Faculty User',
            'username' => 'faculty1',
            'email' => 'faculty@ustp.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'department' => 'Computer Science',
        ];

        // Add school_id if the column exists
        if (Schema::hasColumn('users', 'school_id')) {
            $adminData['school_id'] = 'ADMIN001';
            $facultyData['school_id'] = 'FAC001';
        }

        // Add office_id if the column exists (for newer migrations)
        if (Schema::hasColumn('users', 'office_id')) {
            $adminData['office_id'] = null; // Admin doesn't need to belong to specific office
            $facultyData['office_id'] = null; // Will be assigned later
        }

        try {
            // Create admin user
            User::create($adminData);
            $this->command->info('✅ Admin user created successfully (username: admin, password: password)');

            // Create faculty user
            User::create($facultyData);
            $this->command->info('✅ Sample faculty user created successfully (username: faculty1, password: password)');

        } catch (\Exception $e) {
            $this->command->error('❌ Failed to create users: ' . $e->getMessage());
            
            // Try creating with minimal data if there are column issues
            try {
                $minimalAdminData = [
                    'name' => 'Admin User',
                    'username' => 'admin',
                    'email' => 'admin@ustp.edu.ph',
                    'password' => Hash::make('password'),
                ];

                // Add role if column exists
                if (Schema::hasColumn('users', 'role')) {
                    $minimalAdminData['role'] = 'admin';
                }

                DB::table('users')->insert($minimalAdminData);
                $this->command->info('✅ Admin user created with minimal data');

            } catch (\Exception $fallbackException) {
                $this->command->error('❌ Fallback user creation also failed: ' . $fallbackException->getMessage());
            }
        }
    }
}
