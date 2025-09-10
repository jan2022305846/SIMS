<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Office;
use App\Models\User;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create offices based on typical university departments
        $offices = [
            [
                'name' => 'Supply Office',
                'code' => 'SUPPLY',
                'description' => 'Main supply and inventory management office',
                'location' => 'Administration Building, Room 101',
            ],
            [
                'name' => 'Computer Science Department',
                'code' => 'BSIT',
                'description' => 'Bachelor of Science in Information Technology',
                'location' => 'IT Building, 2nd Floor',
            ],
            [
                'name' => 'Business Management Department',
                'code' => 'BSMB',
                'description' => 'Bachelor of Science in Management',
                'location' => 'Business Building, 3rd Floor',
            ],
            [
                'name' => 'Home Economics Department',
                'code' => 'BTLE-HE',
                'description' => 'Bachelor of Technology and Livelihood Education - Home Economics',
                'location' => 'TLE Building, 1st Floor',
            ],
            [
                'name' => 'Industrial Arts Department',
                'code' => 'BTLE-IA',
                'description' => 'Bachelor of Technology and Livelihood Education - Industrial Arts',
                'location' => 'TLE Building, 2nd Floor',
            ],
            [
                'name' => 'Engineering Department',
                'code' => 'BSIE',
                'description' => 'Bachelor of Science in Industrial Engineering',
                'location' => 'Engineering Building, Ground Floor',
            ],
        ];

        foreach ($offices as $officeData) {
            Office::firstOrCreate(
                ['code' => $officeData['code']],
                $officeData
            );
        }

        // Assign office heads and update user-office relationships
        $this->assignOfficeHeads();
        $this->updateUserOfficeRelationships();
    }

    /**
     * Assign office heads to offices
     */
    private function assignOfficeHeads(): void
    {
        // Create office head users if they don't exist
        $officeHeads = [
            [
                'name' => 'Supply Office Head',
                'username' => 'supplyhead',
                'school_id' => 'SOH001',
                'email' => 'supplyhead@ustp.edu.ph',
                'password' => bcrypt('password'),
                'role' => 'office_head',
                'department' => 'Supply Office',
            ],
            [
                'name' => 'BSIT Department Head',
                'username' => 'bsithead',
                'school_id' => 'BSITH001',
                'email' => 'bsithead@ustp.edu.ph',
                'password' => bcrypt('password'),
                'role' => 'office_head',
                'department' => 'Computer Science',
            ],
        ];

        foreach ($officeHeads as $headData) {
            $user = User::firstOrCreate(
                ['username' => $headData['username']],
                $headData
            );

            // Assign as office head
            if ($headData['department'] === 'Supply Office') {
                $office = Office::where('code', 'SUPPLY')->first();
            } elseif ($headData['department'] === 'Computer Science') {
                $office = Office::where('code', 'BSIT')->first();
            }

            if (isset($office)) {
                $office->office_head_id = $user->id;
                $office->save();
                
                $user->office_id = $office->id;
                $user->save();
            }
        }
    }

    /**
     * Update existing users to belong to offices
     */
    private function updateUserOfficeRelationships(): void
    {
        // Map departments to office codes
        $departmentOfficeMap = [
            'BSIT' => 'BSIT',
            'BSMB' => 'BSMB', 
            'BTLE-HE' => 'BTLE-HE',
            'BTLE_IA' => 'BTLE-IA',
            'Supply Office' => 'SUPPLY',
        ];

        foreach ($departmentOfficeMap as $department => $officeCode) {
            $office = Office::where('code', $officeCode)->first();
            
            if ($office) {
                User::where('department', $department)
                    ->whereNull('office_id')
                    ->update(['office_id' => $office->id]);
            }
        }

        // Assign admin users to Supply Office if no office assigned
        $supplyOffice = Office::where('code', 'SUPPLY')->first();
        if ($supplyOffice) {
            User::where('role', 'admin')
                ->whereNull('office_id')
                ->update(['office_id' => $supplyOffice->id]);
        }
    }
}
