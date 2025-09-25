<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class ContractController extends Controller
{
    /**
     * Display a listing of contracts
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = Contract::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch']);
        
        // Filter based on user role
        if ($user->role === 'branch_manager') {
            $query->whereHas('serviceRequest', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        $contracts = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);
    }

    /**
     * Store a newly created contract
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'rental_amount' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'additional_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest = ServiceRequest::find($request->service_request_id);
        
        // Check if contract already exists for this service request
        $existingContract = Contract::where('service_request_id', $request->service_request_id)->first();
        if ($existingContract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract already exists for this service request'
            ], 422);
        }

        // Generate contract number
        $contractNumber = 'CON-' . date('Y') . '-' . str_pad(Contract::count() + 1, 6, '0', STR_PAD_LEFT);

        $contract = Contract::create([
            'service_request_id' => $request->service_request_id,
            'contract_number' => $contractNumber,
            'rental_amount' => $request->rental_amount,
            'security_deposit' => $request->security_deposit,
            'terms_conditions' => $request->terms_conditions ?? $this->getDefaultTermsConditions(),
            'additional_notes' => $request->additional_notes,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract created successfully',
            'data' => $contract->load(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])
        ], 201);
    }

    /**
     * Display the specified contract
     */
    public function show($id)
    {
        $contract = Contract::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])->find($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user->role === 'branch_manager' && $contract->serviceRequest->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $contract
        ]);
    }

    /**
     * Generate and download contract PDF
     */
    public function downloadPdf($id)
    {
        $contract = Contract::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])->find($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user->role === 'branch_manager' && $contract->serviceRequest->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $pdf = PDF::loadView('pdfs.contract', compact('contract'));
        
        return $pdf->download('contract-' . $contract->contract_number . '.pdf');
    }

    /**
     * Email contract PDF to customer
     */
    public function emailPdf($id)
    {
        $contract = Contract::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])->find($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user->role === 'branch_manager' && $contract->serviceRequest->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            $pdf = PDF::loadView('pdfs.contract', compact('contract'));
            $pdfContent = $pdf->output();

            Mail::send('emails.contract', compact('contract'), function ($message) use ($contract, $pdfContent) {
                $message->to($contract->serviceRequest->email)
                       ->subject('Rental Contract - ' . $contract->contract_number . ' - AL IBDAL TRADING LLC')
                       ->attachData($pdfContent, 'contract-' . $contract->contract_number . '.pdf', [
                           'mime' => 'application/pdf',
                       ]);
            });

            // Update contract status
            $contract->update(['status' => 'sent']);

            return response()->json([
                'success' => true,
                'message' => 'Contract emailed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified contract
     */
    public function update(Request $request, $id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        // Don't allow editing if contract is signed
        if ($contract->status === 'signed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit a signed contract'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rental_amount' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'additional_notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,sent,signed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = array_filter($request->only([
            'rental_amount', 'security_deposit', 'terms_conditions', 
            'additional_notes', 'status'
        ]), function($value) {
            return $value !== null;
        });

        $contract->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully',
            'data' => $contract->load(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])
        ]);
    }

    /**
     * Remove the specified contract
     */
    public function destroy($id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        // Only allow deletion if contract is draft
        if ($contract->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete contract that has been sent or signed'
            ], 422);
        }

        $contract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract deleted successfully'
        ]);
    }

    /**
     * Get default terms and conditions
     */
    private function getDefaultTermsConditions()
    {
        return "RENTAL TERMS AND CONDITIONS

1. RENTAL PERIOD
The rental period begins on the date specified in this agreement and ends on the return date. Late returns may incur additional charges.

2. SECURITY DEPOSIT
A security deposit is required and will be refunded upon return of the vehicle in good condition, minus any damages or additional charges.

3. DRIVER REQUIREMENTS
- Valid driving license required
- Minimum age of 21 years
- No major traffic violations in the past 3 years

4. VEHICLE CONDITION
The renter acknowledges receiving the vehicle in good condition and agrees to return it in the same condition, normal wear excepted.

5. INSURANCE
Basic insurance is included. Additional coverage may be purchased separately.

6. FUEL POLICY
Vehicle must be returned with the same fuel level as when rented.

7. PROHIBITED USES
- Commercial use without authorization
- Racing or off-road driving
- Transportation of illegal goods
- Use by unauthorized drivers

8. LIABILITY
Renter is responsible for all damages, fines, and penalties incurred during the rental period.

9. CANCELLATION POLICY
Cancellations must be made 24 hours in advance to avoid charges.

10. GOVERNING LAW
This agreement is governed by the laws of the Sultanate of Oman.

AL IBDAL TRADING LLC reserves the right to modify these terms and conditions.";
    }
}
