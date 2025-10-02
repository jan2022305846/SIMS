<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with data from localdb.sql
     */
    public function run(): void
    {
        $this->command->info('Seeding database with data from localdb.sql...');

        // Path to the SQL dump file
        $sqlFile = resource_path('db/localdb.sql');

        if (!File::exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            return;
        }

        // Read the SQL file
        $sql = File::get($sqlFile);

        // Split the SQL file into individual statements
        $statements = $this->splitSqlStatements($sql);

        $this->command->info('Found ' . count($statements) . ' SQL statements to execute.');

        $successCount = 0;
        $errorCount = 0;

        foreach ($statements as $statement) {
            $statement = trim($statement);

            // Skip empty statements and comments
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }

            // Skip SET statements and other non-data statements
            if (preg_match('/^(SET|START TRANSACTION|COMMIT|\/\*|DROP|CREATE|ALTER|INSERT INTO `migrations`)/i', $statement)) {
                continue;
            }

            // Only process INSERT statements
            if (stripos($statement, 'INSERT INTO') === 0) {
                try {
                    DB::statement($statement);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->command->error("Failed to execute: " . substr($statement, 0, 100) . "...");
                    $this->command->error("Error: " . $e->getMessage());
                    $errorCount++;
                }
            }
        }

        $this->command->info("Seeding completed: {$successCount} statements executed successfully, {$errorCount} errors.");
    }

    /**
     * Split SQL file into individual statements
     */
    private function splitSqlStatements($sql)
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Split by semicolon, but be careful with semicolons inside strings
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];

            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar && $sql[$i - 1] !== '\\') {
                $inString = false;
                $stringChar = '';
            }

            if (!$inString && $char === ';') {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            } else {
                $currentStatement .= $char;
            }
        }

        // Add the last statement if it doesn't end with semicolon
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return array_filter($statements);
    }
}