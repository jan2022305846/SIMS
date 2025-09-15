<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Request as SupplyRequest;
use App\Models\RequestAcknowledgment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AcknowledgmentController extends Controller
{
    /**
     * Show acknowledgment form for a request
     */
    public function show(SupplyRequest $request)
    {
        // Check if user can acknowledge this request
        if (!$this->canAcknowledge($request)) {
            abort(403, 'You are not authorized to acknowledge this request.');
        }

        // Check if already acknowledged
        if ($request->acknowledgment) {
            return redirect()->route('requests.acknowledgment.receipt', $request)
                ->with('info', 'This request has already been acknowledged.');
        }

        $witnesses = User::where('role', 'admin')
            ->orWhere('role', 'office_head')
            ->where('id', '!=', Auth::id())
            ->get();

        return view('admin.requests.acknowledgment', compact('request', 'witnesses'));
    }

    /**
     * Store acknowledgment
     */
    public function store(Request $request, SupplyRequest $supplyRequest)
    {
        $request->validate([
            'signature_data' => 'required|string',
            'signature_type' => 'required|in:digital,manual,photo',
            'acknowledgment_notes' => 'nullable|string|max:1000',
            'witnessed_by' => 'nullable|exists:users,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'items_received' => 'required|array'
        ]);

        // Check authorization
        if (!$this->canAcknowledge($supplyRequest)) {
            abort(403, 'You are not authorized to acknowledge this request.');
        }

        // Check if already acknowledged
        if ($supplyRequest->acknowledgment) {
            return redirect()->route('requests.acknowledgment.receipt', $supplyRequest)
                ->with('error', 'This request has already been acknowledged.');
        }

        DB::beginTransaction();
        try {
            // Handle photo upload
            $photoPath = null;
            $photoOriginalName = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $photoOriginalName = $file->getClientOriginalName();
                $photoPath = $file->store('acknowledgments', 'public');
            }

            // Create acknowledgment
            $acknowledgment = RequestAcknowledgment::create([
                'request_id' => $supplyRequest->id,
                'acknowledged_by' => Auth::id(),
                'witnessed_by' => $request->input('witnessed_by'),
                'signature_data' => $request->input('signature_data'),
                'signature_type' => $request->input('signature_type'),
                'acknowledged_at' => Carbon::now(),
                'acknowledgment_notes' => $request->input('acknowledgment_notes'),
                'items_received' => $request->input('items_received'),
                'photo_path' => $photoPath,
                'photo_original_name' => $photoOriginalName,
                'receipt_data' => $this->generateReceiptData($supplyRequest, $request->input('items_received')),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Update request status to acknowledged/completed
            $supplyRequest->update([
                'status' => 'completed',
                'workflow_status' => 'acknowledged'
            ]);

            DB::commit();

            return redirect()->route('requests.acknowledgment.receipt', $supplyRequest)
                ->with('success', 'Request acknowledged successfully! Receipt generated.');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded photo if exists
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return back()->withInput()
                ->with('error', 'Failed to acknowledge request: ' . $e->getMessage());
        }
    }

    /**
     * Show digital receipt
     */
    public function receipt(SupplyRequest $request)
    {
        $acknowledgment = $request->acknowledgment;
        
        if (!$acknowledgment) {
            abort(404, 'Acknowledgment not found for this request.');
        }

        // Check if user can view this receipt
        if (!$this->canViewReceipt($request, $acknowledgment)) {
            abort(403, 'You are not authorized to view this receipt.');
        }

        return view('admin.requests.receipt', compact('request', 'acknowledgment'));
    }

    /**
     * Download receipt as PDF
     */
    public function downloadReceipt(SupplyRequest $request)
    {
        /** @var RequestAcknowledgment|null $acknowledgment */
        $acknowledgment = $request->acknowledgment;
        
        if (!$acknowledgment) {
            abort(404, 'Acknowledgment not found for this request.');
        }

        // Check authorization
        if (!$this->canViewReceipt($request, $acknowledgment)) {
            abort(403, 'You are not authorized to download this receipt.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.requests.receipt-pdf', compact('request', 'acknowledgment'));
        
        $filename = 'Receipt_' . $acknowledgment->receipt_number . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Verify acknowledgment integrity
     */
    public function verify(SupplyRequest $request)
    {
        /** @var RequestAcknowledgment|null $acknowledgment */
        $acknowledgment = $request->acknowledgment;
        
        if (!$acknowledgment) {
            return response()->json(['verified' => false, 'message' => 'Acknowledgment not found.']);
        }

        $isValid = $acknowledgment->verifyIntegrity();
        
        return response()->json([
            'verified' => $isValid,
            'message' => $isValid ? 'Acknowledgment is valid and verified.' : 'Acknowledgment verification failed.',
            'receipt_number' => $acknowledgment->receipt_number,
            'acknowledged_at' => $acknowledgment->acknowledged_at->format('Y-m-d H:i:s'),
            'verification_hash' => $acknowledgment->verification_hash
        ]);
    }

    /**
     * Check if current user can acknowledge the request
     */
    private function canAcknowledge(SupplyRequest $request)
    {
        $user = Auth::user();
        
        // Must be the requester or an admin/office head
        return $user->id === $request->user_id || 
               $user->role === 'admin' || 
               $user->role === 'office_head';
    }

    /**
     * Check if current user can view the receipt
     */
    private function canViewReceipt(SupplyRequest $request, RequestAcknowledgment $acknowledgment)
    {
        $user = Auth::user();
        
        // Requester, acknowledger, witness, admin, or office head can view
        return $user->id === $request->user_id ||
               $user->id === $acknowledgment->acknowledged_by ||
               ($acknowledgment->witnessed_by && $user->id === $acknowledgment->witnessed_by) ||
               $user->role === 'admin' ||
               $user->role === 'office_head';
    }

    /**
     * Generate receipt data
     */
    private function generateReceiptData(SupplyRequest $request, array $itemsReceived)
    {
        return [
            'request_details' => [
                'id' => $request->id,
                'requested_by' => $request->user->name,
                'department' => $request->department,
                'purpose' => $request->purpose,
                'request_date' => $request->created_at->format('Y-m-d H:i:s'),
                'needed_date' => $request->needed_date ? $request->needed_date->format('Y-m-d') : null
            ],
            'items_received' => $itemsReceived,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()->name
        ];
    }
}
