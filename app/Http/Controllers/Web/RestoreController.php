<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Request as SupplyRequest;
use App\Models\ActivityLog;
use App\Models\RequestAcknowledgment;
use App\Models\ItemScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use ZipArchive;

class RestoreController extends Controller
{
    /**
     * Display restore management page
     */
    public function index()
    {
        // Get list of existing backups
        $backups = $this->getExistingBackups();
        
        return view('admin.restore.index', compact('backups'));
    }

    /**
     * Analyze backup file before restore
     */
    public function analyzeBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip'
        ]);

        try {
            $file = $request->file('backup_file');
            $tempPath = 'temp_restore/' . uniqid();
            
            // Store uploaded file temporarily
            $uploadedPath = $file->store($tempPath);
            
            // Extract and analyze backup
            $analysis = $this->extractAndAnalyzeBackup($uploadedPath, $tempPath);
            
            // Clean up temporary files
            Storage::deleteDirectory($tempPath);
            
            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore from backup file
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip',
            'restore_options' => 'required|array',
            'restore_mode' => 'required|in:replace,merge',
            'tables' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            
            $file = $request->file('backup_file');
            $options = $request->input('restore_options', []);
            $mode = $request->input('restore_mode');
            $tables = $request->input('tables', []);
            $tempPath = 'temp_restore/' . uniqid();
            
            // Store and extract backup file
            $uploadedPath = $file->store($tempPath);
            $extractedData = $this->extractBackupData($uploadedPath, $tempPath);
            
            // Validate backup integrity
            $this->validateBackupIntegrity($extractedData);
            
            $restoredCounts = [];
            
            // Restore selected tables
            foreach ($tables as $table) {
                if (isset($extractedData[$table])) {
                    $count = $this->restoreTable($table, $extractedData[$table], $mode, $options);
                    $restoredCounts[$table] = $count;
                }
            }
            
            // Log restore activity
            ActivityLog::log('System restore completed')
                ->inLog('system_management')
                ->withProperties([
                    'restore_mode' => $mode,
                    'tables_restored' => $tables,
                    'record_counts' => $restoredCounts,
                    'backup_file' => $file->getClientOriginalName()
                ])
                ->save();
            
            // Clean up temporary files
            Storage::deleteDirectory($tempPath);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Restore completed successfully',
                'restored_counts' => $restoredCounts
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create backup before restore (safety backup)
     */
    public function createSafetyBackup()
    {
        try {
            $backupName = 'safety_backup_' . Carbon::now()->format('Y_m_d_H_i_s');
            $backupPath = 'backups/' . $backupName;
            
            Storage::makeDirectory($backupPath);
            
            // Quick backup of critical data
            $this->quickBackupUsers($backupPath);
            $this->quickBackupCategories($backupPath);
            $this->quickBackupItems($backupPath);
            $this->quickBackupRequests($backupPath);
            
            // Create metadata
            $metadata = [
                'backup_type' => 'safety',
                'created_at' => Carbon::now()->toISOString(),
                'purpose' => 'Pre-restore safety backup'
            ];
            Storage::put($backupPath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            
            // Create ZIP
            $this->createZipBackup($backupPath, $backupName);
            Storage::deleteDirectory($backupPath);
            
            return response()->json([
                'success' => true,
                'backup_name' => $backupName . '.zip'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create safety backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract and analyze backup file
     */
    private function extractAndAnalyzeBackup($zipPath, $tempPath)
    {
        $zip = new ZipArchive();
        $extractPath = storage_path('app/' . $tempPath . '/extracted');
        
        if ($zip->open(storage_path('app/' . $zipPath)) !== TRUE) {
            throw new \Exception('Cannot open backup file');
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
        
        // Read metadata if available
        $metadataPath = $extractPath . '/metadata.json';
        $metadata = file_exists($metadataPath) ? json_decode(file_get_contents($metadataPath), true) : null;
        
        // Analyze available data files
        $availableTables = [];
        $tableFiles = ['users', 'categories', 'items', 'requests', 'activity_logs', 'acknowledgments', 'scan_logs'];
        
        foreach ($tableFiles as $table) {
            $filePath = $extractPath . '/' . $table . '.json';
            if (file_exists($filePath)) {
                $data = json_decode(file_get_contents($filePath), true);
                $availableTables[$table] = [
                    'record_count' => count($data),
                    'file_size' => filesize($filePath),
                    'last_modified' => filemtime($filePath)
                ];
            }
        }
        
        return [
            'metadata' => $metadata,
            'available_tables' => $availableTables,
            'total_tables' => count($availableTables),
            'backup_valid' => !empty($availableTables)
        ];
    }

    /**
     * Extract backup data
     */
    private function extractBackupData($zipPath, $tempPath)
    {
        $zip = new ZipArchive();
        $extractPath = storage_path('app/' . $tempPath . '/extracted');
        
        if ($zip->open(storage_path('app/' . $zipPath)) !== TRUE) {
            throw new \Exception('Cannot open backup file');
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
        
        $data = [];
        $tableFiles = ['users', 'categories', 'items', 'requests', 'activity_logs', 'acknowledgments', 'scan_logs'];
        
        foreach ($tableFiles as $table) {
            $filePath = $extractPath . '/' . $table . '.json';
            if (file_exists($filePath)) {
                $data[$table] = json_decode(file_get_contents($filePath), true);
            }
        }
        
        return $data;
    }

    /**
     * Validate backup integrity
     */
    private function validateBackupIntegrity($data)
    {
        if (empty($data)) {
            throw new \Exception('Backup file contains no valid data');
        }
        
        // Validate data structure for each table
        foreach ($data as $table => $records) {
            if (!is_array($records)) {
                throw new \Exception("Invalid data format for table: {$table}");
            }
            
            // Basic validation for each table type
            switch ($table) {
                case 'users':
                    $this->validateUsersData($records);
                    break;
                case 'categories':
                    $this->validateCategoriesData($records);
                    break;
                case 'items':
                    $this->validateItemsData($records);
                    break;
                case 'requests':
                    $this->validateRequestsData($records);
                    break;
            }
        }
    }

    /**
     * Restore specific table
     */
    private function restoreTable($table, $data, $mode, $options)
    {
        switch ($table) {
            case 'users':
                return $this->restoreUsers($data, $mode, $options);
            case 'categories':
                return $this->restoreCategories($data, $mode, $options);
            case 'items':
                return $this->restoreItems($data, $mode, $options);
            case 'requests':
                return $this->restoreRequests($data, $mode, $options);
            case 'activity_logs':
                return $this->restoreActivityLogs($data, $mode, $options);
            case 'acknowledgments':
                return $this->restoreAcknowledgments($data, $mode, $options);
            case 'scan_logs':
                return $this->restoreScanLogs($data, $mode, $options);
            default:
                return 0;
        }
    }

    /**
     * Restore users
     */
    private function restoreUsers($data, $mode, $options)
    {
        if ($mode === 'replace') {
            // Keep current admin user for safety
            $currentAdmin = User::where('role', 'admin')->first();
            User::truncate();
            if ($currentAdmin) {
                $currentAdmin->save();
            }
        }
        
        $count = 0;
        foreach ($data as $userData) {
            // Skip if user already exists in merge mode
            if ($mode === 'merge' && User::where('email', $userData['email'])->exists()) {
                continue;
            }
            
            // Set default password for restored users
            $userData['password'] = Hash::make('password123');
            $userData['email_verified_at'] = now();
            
            User::create($userData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Restore categories
     */
    private function restoreCategories($data, $mode, $options)
    {
        if ($mode === 'replace') {
            Category::truncate();
        }
        
        $count = 0;
        foreach ($data as $categoryData) {
            if ($mode === 'merge' && Category::where('name', $categoryData['name'])->exists()) {
                continue;
            }
            
            Category::create($categoryData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Restore items
     */
    private function restoreItems($data, $mode, $options)
    {
        if ($mode === 'replace') {
            Item::truncate();
        }
        
        $count = 0;
        foreach ($data as $itemData) {
            // Remove nested category data if present
            unset($itemData['category']);
            
            if ($mode === 'merge' && Item::where('item_code', $itemData['item_code'])->exists()) {
                continue;
            }
            
            Item::create($itemData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Restore requests
     */
    private function restoreRequests($data, $mode, $options)
    {
        if ($mode === 'replace') {
            SupplyRequest::truncate();
        }
        
        $count = 0;
        foreach ($data as $requestData) {
            // Remove nested relationships
            unset($requestData['user'], $requestData['item']);
            
            // Skip if request already exists
            if ($mode === 'merge' && SupplyRequest::where('id', $requestData['id'])->exists()) {
                continue;
            }
            
            SupplyRequest::create($requestData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Restore activity logs
     */
    private function restoreActivityLogs($data, $mode, $options)
    {
        if ($mode === 'replace') {
            ActivityLog::truncate();
        }
        
        $count = 0;
        foreach ($data as $logData) {
            // Remove nested causer data
            unset($logData['causer']);
            
            ActivityLog::create($logData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Restore acknowledgments
     */
    private function restoreAcknowledgments($data, $mode, $options)
    {
        if ($mode === 'replace') {
            RequestAcknowledgment::truncate();
        }
        
        $count = 0;
        foreach ($data as $ackData) {
            // Remove nested relationships
            unset($ackData['request'], $ackData['user']);
            
            if ($mode === 'merge' && RequestAcknowledgment::where('id', $ackData['id'])->exists()) {
                continue;
            }
            
            RequestAcknowledgment::create($ackData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Restore scan logs
     */
    private function restoreScanLogs($data, $mode, $options)
    {
        if ($mode === 'replace') {
            ItemScanLog::truncate();
        }
        
        $count = 0;
        foreach ($data as $scanData) {
            // Remove nested relationships
            unset($scanData['item'], $scanData['user']);
            
            ItemScanLog::create($scanData);
            $count++;
        }
        
        return $count;
    }

    /**
     * Data validation methods
     */
    private function validateUsersData($data)
    {
        foreach ($data as $user) {
            if (empty($user['email']) || empty($user['name'])) {
                throw new \Exception('Invalid user data: missing required fields');
            }
        }
    }

    private function validateCategoriesData($data)
    {
        foreach ($data as $category) {
            if (empty($category['name'])) {
                throw new \Exception('Invalid category data: missing name');
            }
        }
    }

    private function validateItemsData($data)
    {
        foreach ($data as $item) {
            if (empty($item['name']) || empty($item['item_code'])) {
                throw new \Exception('Invalid item data: missing required fields');
            }
        }
    }

    private function validateRequestsData($data)
    {
        foreach ($data as $request) {
            if (empty($request['user_id']) || empty($request['item_id'])) {
                throw new \Exception('Invalid request data: missing required fields');
            }
        }
    }

    /**
     * Quick backup methods for safety backup
     */
    private function quickBackupUsers($backupPath)
    {
        $users = User::all()->makeHidden(['password', 'remember_token']);
        Storage::put($backupPath . '/users.json', json_encode($users, JSON_PRETTY_PRINT));
    }

    private function quickBackupCategories($backupPath)
    {
        $categories = Category::all();
        Storage::put($backupPath . '/categories.json', json_encode($categories, JSON_PRETTY_PRINT));
    }

    private function quickBackupItems($backupPath)
    {
        $items = Item::all();
        Storage::put($backupPath . '/items.json', json_encode($items, JSON_PRETTY_PRINT));
    }

    private function quickBackupRequests($backupPath)
    {
        $requests = SupplyRequest::all();
        Storage::put($backupPath . '/requests.json', json_encode($requests, JSON_PRETTY_PRINT));
    }

    /**
     * Create ZIP backup file
     */
    private function createZipBackup($backupPath, $backupName)
    {
        $zipPath = storage_path('app/backups/' . $backupName . '.zip');
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Cannot create ZIP file');
        }
        
        $files = Storage::allFiles($backupPath);
        foreach ($files as $file) {
            $zip->addFile(storage_path('app/' . $file), basename($file));
        }
        
        $zip->close();
        
        return $zipPath;
    }

    /**
     * Get existing backup files
     */
    private function getExistingBackups()
    {
        $backupFiles = Storage::files('backups');
        $backups = [];
        
        foreach ($backupFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $filename = basename($file);
                $backups[] = [
                    'filename' => $filename,
                    'size' => Storage::size($file),
                    'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file)),
                    'download_url' => route('backup.download', $filename)
                ];
            }
        }
        
        return $backups;
    }
}