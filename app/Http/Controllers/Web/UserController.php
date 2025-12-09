<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Notifications\SetPasswordNotification;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Office filter
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        // No role filter needed in single admin system
        // All users created through UI are faculty

        $users = $query->paginate(10)->appends($request->query());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (User::where('name', $value)->exists()) {
                        $fail('A user with this full name already exists.');
                    }
                },
            ],
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'office_id' => $request->office_id,
            'must_set_password' => false,
        ]);

        // Log user creation activity
        ActivityLogger::logUserCreated($user);

        // Send password setup notification with credentials
        $token = app(\Illuminate\Auth\Passwords\PasswordBroker::class)->createToken($user);
        $user->notify(new SetPasswordNotification($token, true, $request->password));

        return redirect()->route('users.index')
            ->with('success', 'User created successfully. A password setup email has been sent to the user.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user)
    {
        // Paginate user's requests instead of showing just 5
        $requests = $user->requests()
            ->with(['requestItems.itemable'])
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return view('admin.users.show', compact('user', 'requests'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'office_id' => $request->office_id,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // Capture original values for logging
        $originalValues = $user->toArray();

        $user->update($userData);

        // Log user update activity
        $changes = [];
        foreach ($userData as $key => $value) {
            if (isset($originalValues[$key]) && $originalValues[$key] != $value) {
                $changes[$key] = ['old' => $originalValues[$key], 'new' => $value];
            }
        }

        if (!empty($changes)) {
            ActivityLogger::logUserUpdated($user, null, $changes);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        // Log user deletion activity
        ActivityLogger::logUserDeleted($user);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Export fulfilled requests for a user as DOCX
     */
    public function exportFulfilledRequests(User $user)
    {
        // Get fulfilled requests for this user
        $fulfilledRequests = $user->requests()
            ->whereIn('status', ['fulfilled', 'claimed'])
            ->with(['requestItems.itemable'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Create new PhpWord instance
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('SIMS System');
        $properties->setCompany('USTP Supply Office');
        $properties->setTitle('Fulfilled Requests Report - ' . $user->name);
        $properties->setDescription('Request history report for ' . $user->name);
        $properties->setSubject('User Request History');

        // Add title page
        $section = $phpWord->addSection();

        // Title
        $section->addText(
            'FULFILLED REQUESTS REPORT',
            ['name' => 'Arial', 'size' => 16, 'bold' => true],
            ['alignment' => 'center']
        );

        $section->addTextBreak(1);

        // User Information
        $section->addText(
            'User Information',
            ['name' => 'Arial', 'size' => 14, 'bold' => true]
        );

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
        $table->addRow();
        $table->addCell(2000)->addText('Name:');
        $table->addCell(5000)->addText($user->name);

        $table->addRow();
        $table->addCell(2000)->addText('Email:');
        $table->addCell(5000)->addText($user->email);

        $table->addRow();
        $table->addCell(2000)->addText('Username:');
        $table->addCell(5000)->addText($user->username);

        $table->addRow();
        $table->addCell(2000)->addText('Role:');
        $table->addCell(5000)->addText($user->isAdmin() ? 'Administrator' : 'Faculty');

        $table->addRow();
        $table->addCell(2000)->addText('Department:');
        $table->addCell(5000)->addText($user->office->name ?? 'N/A');

        $table->addRow();
        $table->addCell(2000)->addText('Total Fulfilled Requests:');
        $table->addCell(5000)->addText($fulfilledRequests->count());

        $section->addTextBreak(2);

        // Request History
        $section->addText(
            'Request History',
            ['name' => 'Arial', 'size' => 14, 'bold' => true]
        );

        $section->addTextBreak(1);

        if ($fulfilledRequests->isEmpty()) {
            $section->addText(
                'No fulfilled requests found for this user.',
                ['name' => 'Arial', 'size' => 11, 'italic' => true]
            );
        } else {
            // Create a table with headers
            $historyTable = $section->addTable(['borderSize' => 6, 'borderColor' => 'CCCCCC']);

            // Table headers
            $historyTable->addRow();
            $historyTable->addCell(4000, ['bgColor' => 'E6E6E6'])->addText('Item', ['name' => 'Arial', 'size' => 11, 'bold' => true]);
            $historyTable->addCell(2000, ['bgColor' => 'E6E6E6'])->addText('Quantity', ['name' => 'Arial', 'size' => 11, 'bold' => true]);
            $historyTable->addCell(2500, ['bgColor' => 'E6E6E6'])->addText('Fulfilled Date', ['name' => 'Arial', 'size' => 11, 'bold' => true]);

            // Add rows for each fulfilled request
            foreach ($fulfilledRequests as $request) {
                // Ensure requestItems are loaded with itemable relationships
                if (!$request->relationLoaded('requestItems')) {
                    $request->load('requestItems.itemable');
                } elseif (!$request->requestItems->first() || !$request->requestItems->first()->relationLoaded('itemable')) {
                    $request->requestItems->load('itemable');
                }
                
                // Create item summary
                $itemNames = [];
                $totalQuantity = 0;
                foreach ($request->requestItems as $requestItem) {
                    $itemName = $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item';
                    $itemNames[] = $itemName;
                    $totalQuantity += $requestItem->quantity;
                }
                
                $itemSummary = count($itemNames) === 1 ? $itemNames[0] : count($itemNames) . ' items';
                
                $historyTable->addRow();
                $historyTable->addCell(4000)->addText($itemSummary);
                $historyTable->addCell(2000)->addText($totalQuantity . ' items');
                $historyTable->addCell(2500)->addText($request->updated_at->format('M j, Y'));
            }
        }

        // Footer with generation info
        $section->addTextBreak(2);
        $section->addText(
            'Report generated on ' . now()->format('M j, Y \a\t g:i A') . ' by ' . Auth::user()->name,
            ['name' => 'Arial', 'size' => 9, 'italic' => true],
            ['alignment' => 'center']
        );

        // Generate filename
        $filename = 'fulfilled_requests_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.docx';

        // Save and download
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * API endpoint to get user released items by period
     */
    public function getReleasedItems(Request $request, User $user)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        // Get date range
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get released items for this user
        $releasedItemsQuery = \App\Models\RequestItem::with(['request', 'itemable'])
            ->whereHas('request', function($query) use ($user, $dateFrom, $dateTo) {
                $query->where('user_id', $user->id)
                      ->whereIn('status', ['claimed', 'returned'])
                      ->whereBetween('updated_at', [$dateFrom, $dateTo]);
            })
            ->orderBy('request_items.created_at', 'desc')
            ->get();

        $items = $releasedItemsQuery->map(function($requestItem) {
            $itemName = 'Unknown Item';
            $unit = 'pcs';
            
            if ($requestItem->item_type && $requestItem->item_id) {
                if ($requestItem->item_type === 'consumable') {
                    $item = \App\Models\Consumable::find($requestItem->item_id);
                    if ($item) {
                        $itemName = $item->name ?: 'Item ID: ' . $requestItem->item_id;
                        $unit = $item->unit ?: 'pcs';
                    }
                } elseif ($requestItem->item_type === 'non_consumable') {
                    $item = \App\Models\NonConsumable::find($requestItem->item_id);
                    if ($item) {
                        $itemName = $item->name ?: 'Item ID: ' . $requestItem->item_id;
                        $unit = $item->unit ?: 'pcs';
                    }
                }
            }
            
            return [
                'date' => $requestItem->request->updated_at->format('M j, Y'),
                'item_name' => $itemName,
                'quantity' => $requestItem->quantity . ' ' . $unit,
            ];
        });

        return response()->json([
            'items' => $items,
            'totalCount' => $items->count()
        ]);
    }

    /**
     * Export user released items with period filter
     */
    public function exportReleasedItems(Request $request, User $user)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        // Get date range
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get released items for this user
        $releasedItemsQuery = \App\Models\RequestItem::with(['request', 'itemable'])
            ->whereHas('request', function($query) use ($user, $dateFrom, $dateTo) {
                $query->where('user_id', $user->id)
                      ->whereIn('status', ['claimed', 'returned'])
                      ->whereBetween('updated_at', [$dateFrom, $dateTo]);
            })
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        // Create new PhpWord instance
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('SIMS System');
        $properties->setCompany('USTP Supply Office');
        $properties->setTitle('Released Items Report - ' . $user->name);

        // Add section
        $section = $phpWord->addSection([
            'marginLeft' => 1000,
            'marginRight' => 1000,
            'marginTop' => 1000,
            'marginBottom' => 1000,
        ]);

        // Title
        $section->addText(
            'RELEASED ITEMS REPORT',
            ['name' => 'Arial', 'size' => 16, 'bold' => true],
            ['alignment' => 'center']
        );

        $section->addTextBreak(1);

        // Period information
        $periodText = '';
        if ($period === 'monthly') {
            $date = \Carbon\Carbon::parse($selection);
            $periodText = $date->format('F Y');
        } else if ($period === 'annual') {
            $periodText = $selection;
        }

        $section->addText(
            'Period: ' . $periodText,
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => 'center']
        );

        $section->addText(
            'User: ' . $user->name,
            ['name' => 'Arial', 'size' => 11],
            ['alignment' => 'center']
        );

        $section->addTextBreak(1);

        // Create table
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'width' => 80 * 50, // 80% of page width
            'unit' => 'pct'
        ];
        
        $table = $section->addTable($tableStyle);

        // Add header row
        $table->addRow(400);
        $table->addCell(2500, ['bgColor' => 'E6E6E6'])->addText('Date', ['bold' => true]);
        $table->addCell(4000, ['bgColor' => 'E6E6E6'])->addText('Item', ['bold' => true]);
        $table->addCell(1500, ['bgColor' => 'E6E6E6'])->addText('Quantity', ['bold' => true]);

        // Add data rows
        foreach ($releasedItemsQuery as $requestItem) {
            $itemName = 'Unknown Item';
            $unit = 'pcs';
            
            if ($requestItem->item_type && $requestItem->item_id) {
                if ($requestItem->item_type === 'consumable') {
                    $item = \App\Models\Consumable::find($requestItem->item_id);
                    if ($item) {
                        $itemName = $item->name ?: 'Item ID: ' . $requestItem->item_id;
                        $unit = $item->unit ?: 'pcs';
                    }
                } elseif ($requestItem->item_type === 'non_consumable') {
                    $item = \App\Models\NonConsumable::find($requestItem->item_id);
                    if ($item) {
                        $itemName = $item->name ?: 'Item ID: ' . $requestItem->item_id;
                        $unit = $item->unit ?: 'pcs';
                    }
                }
            }

            $table->addRow();
            $table->addCell(2500)->addText($requestItem->request->updated_at->format('M j, Y'));
            $table->addCell(4000)->addText($itemName);
            $table->addCell(1500)->addText($requestItem->quantity . ' ' . $unit);
        }

        // Footer
        $section->addTextBreak(2);
        $section->addText(
            'Generated on ' . now()->format('M j, Y \a\t g:i A'),
            ['name' => 'Arial', 'size' => 9, 'italic' => true],
            ['alignment' => 'center']
        );

        // Save and download
        $filename = 'released_items_' . $user->name . '_' . $periodText . '_' . now()->format('Y-m-d') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Check for duplicate users via AJAX
     */
    public function checkDuplicate(Request $request)
    {
        $field = $request->get('field');
        $value = $request->get('value');

        $exists = false;
        $message = '';

        switch ($field) {
            case 'name':
                $exists = User::where('name', $value)->exists();
                $message = 'A user with this full name already exists.';
                break;
            case 'username':
                $exists = User::where('username', $value)->exists();
                $message = 'This username is already taken.';
                break;
            case 'email':
                $exists = User::where('email', $value)->exists();
                $message = 'This email address is already registered.';
                break;
        }

        return response()->json([
            'exists' => $exists,
            'message' => $message
        ]);
    }
}
