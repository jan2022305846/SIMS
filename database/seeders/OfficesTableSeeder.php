<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            'Campus Director',
            'Admin Head Office',
            'Office of the Academic Head',
            'Student Affairs Office',
            'HRMO',
            'CiTL',
            'Arts and Culture Office',
            'Sports Office',
            'CET Office',
            'Admission Office',
            'Budget Office',
            'Accounting Office',
            'Registrars Office',
            'Quaa Office',
            'Assessment Office',
            'Research and Extension Office',
            'NSTP Office',
            'School Library',
            'ICT Library',
            'Clinic',
            'IT Department Head',
            'Education Department Head',
            'MB Department Head',
            'Faculty Office',
            'Supply Office',
        ];

        foreach ($offices as $office) {
            \App\Models\Office::firstOrCreate([
                'name' => $office
            ]);
        }
    }
}
