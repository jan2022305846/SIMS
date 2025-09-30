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
        // Get office IDs
        $supplyOfficeId = \App\Models\Office::where('name', 'Supply Office')->first()->id;
        $informationTechnologyId = \App\Models\Office::where('name', 'Information Technology')->first()->id;

        // Seed Denver Ian Gemino (Admin)
        User::updateOrCreate(
            ['email' => 'denverian@ustp.edu.ph'],
            [
                'name' => 'Denver Ian Gemino',
                'username' => '2025101010',
                'email' => 'denverian@ustp.edu.ph',
                'email_verified_at' => null,
                'password' => '$2y$12$nIO93N0C1p3lAUbfOOXgkuZONjGP0JBna./70oVppK.uO4b42JHBe', // Already hashed
                'role' => 'admin',
                'must_set_password' => 0,
                'office_id' => $supplyOfficeId,
                'remember_token' => null,
                'created_at' => '2025-09-10 23:16:30',
                'updated_at' => '2025-09-20 15:58:05',
            ]
        );

        // Seed Mark Rey Embudo (Faculty)
        User::updateOrCreate(
            ['email' => 'markrey@ustp.edu.ph'],
            [
                'name' => 'Mark Rey Embudo',
                'username' => '2025202020',
                'email' => 'markrey@ustp.edu.ph',
                'email_verified_at' => null,
                'password' => '$2y$12$QwYzswRoyWrpl2Rhist.YOBbLWfS2qjSYtItxBMtsVf3OTK6hDSPu', // Already hashed
                'role' => 'faculty',
                'must_set_password' => 0,
                'office_id' => $informationTechnologyId,
                'remember_token' => null,
                'created_at' => '2025-09-10 23:16:30',
                'updated_at' => '2025-09-20 15:59:04',
            ]
        );
    }
}
