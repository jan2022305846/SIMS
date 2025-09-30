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
            $this->command->info('✅ Admin user already exists. Skipping seeder.');
            return;
        }

        // Prepare base user data
        $adminData = [
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@ustp.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ];

        $facultyData = [
            'name' => 'Faculty User',
            'username' => 'faculty1',
            'email' => 'faculty@ustp.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'faculty',
        ];

        // Check for additional columns without using Schema::hasColumn (MySQL compatibility issue)
        $this->command->info('🔍 Checking table structure...');
        
        try {
            // Use raw SQL to check columns (compatible with older MySQL versions)
            $columns = DB::select("SHOW COLUMNS FROM users");
            $columnNames = array_column($columns, 'Field');
            
            $this->command->info('📋 Available columns: ' . implode(', ', $columnNames));
            
            // Add office_id if the column exists
            if (in_array('office_id', $columnNames)) {
                $adminData['office_id'] = null; // Admin doesn't need to belong to specific office
                $facultyData['office_id'] = null; // Will be assigned later
                $this->command->info('✅ Added office_id to user data');
            }

        } catch (\Exception $e) {
            $this->command->warn('⚠️  Column structure check failed: ' . $e->getMessage());
            $this->command->warn('🔄 Proceeding with basic user data...');
        }

        try {
            // Try creating users with Eloquent
            User::create($adminData);
            $this->command->info('✅ Admin user created successfully via Eloquent (username: admin, password: password)');

            User::create($facultyData);
            $this->command->info('✅ Sample faculty user created successfully (username: faculty1, password: password)');

        } catch (\Exception $e) {
            $this->command->error('❌ Eloquent user creation failed: ' . $e->getMessage());
            $this->command->info('🔄 Attempting manual database insertion...');
            
            // Fallback to raw SQL insertion
            try {
                // Prepare data for raw insertion
                $adminRawData = $adminData;
                $adminRawData['created_at'] = now();
                $adminRawData['updated_at'] = now();

                // Build dynamic SQL based on available columns
                $columns = array_keys($adminRawData);
                $placeholders = ':' . implode(', :', $columns);
                $columnsList = implode(', ', $columns);

                DB::statement("INSERT INTO users ($columnsList) VALUES ($placeholders)", $adminRawData);
                $this->command->info('✅ Admin user created via raw SQL insertion');

            } catch (\Exception $fallbackException) {
                $this->command->error('❌ All admin user creation attempts failed: ' . $fallbackException->getMessage());
                
                // Final fallback - try with absolute minimal data
                try {
                    DB::statement("
                        INSERT INTO users (name, username, email, password, role, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                    ", [
                        'Admin User',
                        'admin', 
                        'admin@ustp.edu.ph',
                        Hash::make('password'),
                        'admin'
                    ]);
                    $this->command->info('✅ Admin user created with minimal data via prepared statement');
                    
                } catch (\Exception $finalException) {
                    $this->command->error('❌ Final fallback also failed: ' . $finalException->getMessage());
                    $this->command->error('🆘 Manual intervention required - please create admin user manually');
                }
            }
        }
    }
}
