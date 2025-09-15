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
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Display backup management page
     */
    public function index()
    {
        // Get existing backups
        $backups = $this->getExistingBackups();
        
        // Get database statistics
        $stats = [
            'users' => User::count(),
            'categories' => Category::count(),
            'items' => Item::count(),
            'requests' => SupplyRequest::count(),
            'activity_logs' => ActivityLog::count(),
            'acknowledgments' => RequestAcknowledgment::count(),
            'scan_logs' => ItemScanLog::count()
        ];
        
        return view('admin.backup.index', compact('backups', 'stats'));
    }

    /**
     * Create a full system backup
     */
    public function createFullBackup(Request $request)
    {
        try {
            $backupName = 'full_backup_' . Carbon::now()->format('Y_m_d_H_i_s');
            $backupPath = 'backups/' . $backupName;
            
            // Create backup directory
            Storage::makeDirectory($backupPath);
            
            // Backup all critical data
            $this->backupUsers($backupPath);
            $this->backupCategories($backupPath);
            $this->backupItems($backupPath);
            $this->backupRequests($backupPath);
            $this->backupActivityLogs($backupPath);
            $this->backupAcknowledgments($backupPath);
            $this->backupScanLogs($backupPath);
            
            // Create metadata file
            $this->createBackupMetadata($backupPath, 'full');
            
            // Create ZIP file
            $zipPath = $this->createZipBackup($backupPath, $backupName);
            
            // Clean up temporary files
            Storage::deleteDirectory($backupPath);
            
            return response()->json([
                'success' => true,
                'message' => 'Full backup created successfully',
                'backup_name' => $backupName . '.zip',
                'download_url' => route('backup.download', $backupName . '.zip')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create selective backup
     */
    public function createSelectiveBackup(Request $request)
    {
        $request->validate([
            'tables' => 'required|array',
            'tables.*' => 'in:users,categories,items,requests,activity_logs,acknowledgments,scan_logs'
        ]);

        try {
            $backupName = 'selective_backup_' . Carbon::now()->format('Y_m_d_H_i_s');
            $backupPath = 'backups/' . $backupName;
            $tables = $request->input('tables', []);
            
            // Create backup directory
            Storage::makeDirectory($backupPath);
            
            // Backup selected tables
            foreach ($tables as $table) {
                switch ($table) {
                    case 'users':
                        $this->backupUsers($backupPath);
                        break;
                    case 'categories':
                        $this->backupCategories($backupPath);
                        break;
                    case 'items':
                        $this->backupItems($backupPath);
                        break;
                    case 'requests':
                        $this->backupRequests($backupPath);
                        break;
                    case 'activity_logs':
                        $this->backupActivityLogs($backupPath);
                        break;
                    case 'acknowledgments':
                        $this->backupAcknowledgments($backupPath);
                        break;
                    case 'scan_logs':
                        $this->backupScanLogs($backupPath);
                        break;
                }
            }
            
            // Create metadata file
            $this->createBackupMetadata($backupPath, 'selective', $tables);
            
            // Create ZIP file
            $zipPath = $this->createZipBackup($backupPath, $backupName);
            
            // Clean up temporary files
            Storage::deleteDirectory($backupPath);
            
            return response()->json([
                'success' => true,
                'message' => 'Selective backup created successfully',
                'backup_name' => $backupName . '.zip',
                'download_url' => route('backup.download', $backupName . '.zip')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($filename)
    {
        $path = 'backups/' . $filename;
        
        if (!Storage::exists($path)) {
            abort(404, 'Backup file not found');
        }
        
        return Storage::download($path);
    }

    /**
     * Delete backup file
     */
    public function delete(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);
        
        $filename = $request->input('filename');
        $path = 'backups/' . $filename;
        
        if (!Storage::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found'
            ], 404);
        }
        
        Storage::delete($path);
        
        return response()->json([
            'success' => true,
            'message' => 'Backup deleted successfully'
        ]);
    }

    /**
     * Backup users table
     */
    private function backupUsers($backupPath)
    {
        $users = User::all()->map(function ($user) {
            // Remove sensitive data
            unset($user['password'], $user['remember_token']);
            return $user;
        });
        
        Storage::put($backupPath . '/users.json', json_encode($users, JSON_PRETTY_PRINT));
    }

    /**
     * Backup categories table
     */
    private function backupCategories($backupPath)
    {
        $categories = Category::all();
        Storage::put($backupPath . '/categories.json', json_encode($categories, JSON_PRETTY_PRINT));
    }

    /**
     * Backup items table
     */
    private function backupItems($backupPath)
    {
        $items = Item::with('category')->get();
        Storage::put($backupPath . '/items.json', json_encode($items, JSON_PRETTY_PRINT));
    }

    /**
     * Backup requests table
     */
    private function backupRequests($backupPath)
    {
        $requests = SupplyRequest::with(['user', 'item', 'item.category'])->get();
        Storage::put($backupPath . '/requests.json', json_encode($requests, JSON_PRETTY_PRINT));
    }

    /**
     * Backup activity logs
     */
    private function backupActivityLogs($backupPath)
    {
        $logs = ActivityLog::with('causer')->latest()->limit(10000)->get();
        Storage::put($backupPath . '/activity_logs.json', json_encode($logs, JSON_PRETTY_PRINT));
    }

    /**
     * Backup acknowledgments
     */
    private function backupAcknowledgments($backupPath)
    {
        $acknowledgments = RequestAcknowledgment::with(['request', 'user'])->get();
        Storage::put($backupPath . '/acknowledgments.json', json_encode($acknowledgments, JSON_PRETTY_PRINT));
    }

    /**
     * Backup scan logs
     */
    private function backupScanLogs($backupPath)
    {
        $scanLogs = ItemScanLog::with(['item', 'user'])->latest()->limit(10000)->get();
        Storage::put($backupPath . '/scan_logs.json', json_encode($scanLogs, JSON_PRETTY_PRINT));
    }

    /**
     * Create backup metadata file
     */
    private function createBackupMetadata($backupPath, $type, $tables = null)
    {
        $metadata = [
            'backup_type' => $type,
            'created_at' => Carbon::now()->toISOString(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_name' => config('database.connections.mysql.database'),
            'tables_included' => $tables ?: ['users', 'categories', 'items', 'requests', 'activity_logs', 'acknowledgments', 'scan_logs'],
            'record_counts' => [
                'users' => User::count(),
                'categories' => Category::count(),
                'items' => Item::count(),
                'requests' => SupplyRequest::count(),
                'activity_logs' => ActivityLog::count(),
                'acknowledgments' => RequestAcknowledgment::count(),
                'scan_logs' => ItemScanLog::count(),
            ]
        ];
        
        Storage::put($backupPath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
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
        
        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });
        
        return $backups;
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStats()
    {
        return [
            'users' => User::count(),
            'categories' => Category::count(),
            'items' => Item::count(),
            'requests' => SupplyRequest::count(),
            'activity_logs' => ActivityLog::count(),
            'acknowledgments' => RequestAcknowledgment::count(),
            'scan_logs' => ItemScanLog::count(),
            'total_size' => $this->calculateDatabaseSize()
        ];
    }

    /**
     * Calculate approximate database size
     */
    private function calculateDatabaseSize()
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [config('database.connections.mysql.database')]);
            
            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}