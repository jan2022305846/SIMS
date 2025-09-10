<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive database health check';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Supply API Database Health Check ===');
        $this->newLine();

        // 1. Check table existence and structure
        $tables = ['categories', 'items', 'users', 'requests', 'offices', 'activity_logs', 'item_scan_logs'];

        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $this->line("✓ Table '{$table}' exists with {$count} records");
            } catch (\Exception $e) {
                $this->error("✗ Table '{$table}' issue: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('=== Data Integrity Checks ===');

        // 2. Check categories data
        $this->newLine();
        $this->line('--- Categories Check ---');
        $categories = DB::table('categories')->select('id', 'name', 'type')->get();
        foreach ($categories as $category) {
            $expectedType = 'non-consumable'; // Default
            
            // Categories that should be consumable
            $consumableKeywords = ['consumable', 'supplies', 'office supplies', 'cleaning', 'medical'];
            $consumableNames = ['Consumable', 'Office Supplies', 'Cleaning Supplies', 'Medical Supplies', 'Consumables'];
            
            if (in_array($category->name, $consumableNames)) {
                $expectedType = 'consumable';
            }
            
            $status = ($category->type === $expectedType) ? '✓' : '✗';
            $this->line("{$status} Category ID {$category->id}: {$category->name} -> {$category->type} (expected: {$expectedType})");
        }

        // 3. Check foreign key relationships
        $this->newLine();
        $this->line('--- Foreign Key Relationships ---');

        // Items -> Categories
        $orphanedItems = DB::table('items')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->whereNull('categories.id')
            ->count();
        $status = ($orphanedItems === 0) ? '✓' : '✗';
        $this->line("{$status} Items -> Categories: {$orphanedItems} orphaned records");

        // Items -> Users (current_holder)
        $orphanedItemHolders = DB::table('items')
            ->leftJoin('users', 'items.current_holder_id', '=', 'users.id')
            ->whereNotNull('items.current_holder_id')
            ->whereNull('users.id')
            ->count();
        $status = ($orphanedItemHolders === 0) ? '✓' : '✗';
        $this->line("{$status} Items -> Users (holders): {$orphanedItemHolders} orphaned records");

        // Requests -> Items
        $orphanedRequestItems = DB::table('requests')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
            ->whereNull('items.id')
            ->count();
        $status = ($orphanedRequestItems === 0) ? '✓' : '✗';
        $this->line("{$status} Requests -> Items: {$orphanedRequestItems} orphaned records");

        // Requests -> Users
        $orphanedRequestUsers = DB::table('requests')
            ->leftJoin('users', 'requests.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->count();
        $status = ($orphanedRequestUsers === 0) ? '✓' : '✗';
        $this->line("{$status} Requests -> Users: {$orphanedRequestUsers} orphaned records");

        // Users -> Offices
        $orphanedUserOffices = DB::table('users')
            ->leftJoin('offices', 'users.office_id', '=', 'offices.id')
            ->whereNotNull('users.office_id')
            ->whereNull('offices.id')
            ->count();
        $status = ($orphanedUserOffices === 0) ? '✓' : '✗';
        $this->line("{$status} Users -> Offices: {$orphanedUserOffices} orphaned records");

        // 4. Check item stock consistency
        $this->newLine();
        $this->line('--- Item Stock Consistency ---');
        $stockIssues = DB::table('items')
            ->where(function($query) {
                $query->where('current_stock', '<', 0)
                      ->orWhere('minimum_stock', '<', 0)
                      ->orWhere('maximum_stock', '<', 0)
                      ->orWhereRaw('current_stock > maximum_stock');
            })
            ->count();
        $status = ($stockIssues === 0) ? '✓' : '✗';
        $this->line("{$status} Item stock levels: {$stockIssues} issues found");

        // 5. Check duplicate QR codes
        $this->newLine();
        $this->line('--- QR Code Uniqueness ---');
        $duplicateQRs = DB::table('items')
            ->select('qr_code')
            ->groupBy('qr_code')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        $status = ($duplicateQRs === 0) ? '✓' : '✗';
        $this->line("{$status} Duplicate QR codes: {$duplicateQRs} found");

        // 6. Check user roles
        $this->newLine();
        $this->line('--- User Roles Check ---');
        $userRoles = DB::table('users')->select('role')->groupBy('role')->get();
        $validRoles = ['admin', 'office_head', 'user'];
        foreach ($userRoles as $roleData) {
            $valid = in_array($roleData->role, $validRoles) ? '✓' : '✗';
            $count = DB::table('users')->where('role', $roleData->role)->count();
            $this->line("{$valid} Role '{$roleData->role}': {$count} users");
        }

        // 7. Check ERD compliance
        $this->newLine();
        $this->line('--- ERD Compliance Check ---');
        
        // Check if all required tables exist
        $requiredTables = ['categories', 'items', 'requests', 'users', 'offices'];
        $allTablesExist = true;
        foreach ($requiredTables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $allTablesExist = false;
                break;
            }
        }
        $status = $allTablesExist ? '✓' : '✗';
        $this->line("{$status} All required ERD tables exist");

        // Check workflow status enum in requests
        $workflowStatuses = DB::select("SHOW COLUMNS FROM requests LIKE 'workflow_status'");
        $hasWorkflowEnum = !empty($workflowStatuses) && 
            strpos($workflowStatuses[0]->Type ?? '', 'enum') !== false;
        $status = $hasWorkflowEnum ? '✓' : '✗';
        $this->line("{$status} Request workflow status enum configured");

        $this->newLine();
        $this->info('=== Database Health Check Complete ===');

        return self::SUCCESS;
    }
}
