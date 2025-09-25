<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = Invoice::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch']);
        
        // Filter based on user role
        if ($user->role === 'branch_manager') {
            $query->whereHas('serviceRequest', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest = ServiceRequest::find($request->service_request_id);
        
        // Check if invoice already exists for this service request
        $existingInvoice = Invoice::where('service_request_id', $request->service_request_id)->first();
        if ($existingInvoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice already exists for this service request'
            ], 422);
        }

        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'service_request_id' => $request->service_request_id,
            'invoice_number' => $invoiceNumber,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'discount_amount' => $request->discount_amount ?? 0,
            'total_amount' => $request->amount + ($request->tax_amount ?? 0) - ($request->discount_amount ?? 0),
            'notes' => $request->notes,
            'status' => 'pending',
            'issued_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $invoice->load(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])
        ], 201);
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user->role === 'branch_manager' && $invoice->serviceRequest->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Generate and download invoice PDF
     */
    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user->role === 'branch_manager' && $invoice->serviceRequest->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

    $pdf = Pdf::loadView('pdfs.invoice', compact('invoice'));
    return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Email invoice PDF to customer
     */
    public function emailPdf($id)
    {
        $invoice = Invoice::with(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user->role === 'branch_manager' && $invoice->serviceRequest->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            $pdf = Pdf::loadView('pdfs.invoice', compact('invoice'));
            $pdfContent = $pdf->output();

            // Resolve recipient email
            $toEmail = null;
            // Try to find a user by phone
            if ($invoice->serviceRequest && $invoice->serviceRequest->customer_phone) {
                $recipientUser = User::where('phone', $invoice->serviceRequest->customer_phone)->first();
                if ($recipientUser && $recipientUser->email) {
                    $toEmail = $recipientUser->email;
                }
            }

            if (!$toEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not available for this service request'
                ], 422);
            }

            Mail::send('emails.invoice', compact('invoice'), function ($message) use ($invoice, $pdfContent, $toEmail) {
                $message->to($toEmail)
                       ->subject('Invoice - ' . $invoice->invoice_number . ' - AL IBDAL TRADING LLC')
                       ->attachData($pdfContent, 'invoice-' . $invoice->invoice_number . '.pdf', [
                           'mime' => 'application/pdf',
                       ]);
            });

            // Update invoice status
            $invoice->update(['status' => 'sent']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice emailed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,sent,paid,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = array_filter($request->only([
            'amount', 'tax_amount', 'discount_amount', 'notes', 'status'
        ]), function($value) {
            return $value !== null;
        });

        // Recalculate total if amounts changed
        if (isset($updateData['amount']) || isset($updateData['tax_amount']) || isset($updateData['discount_amount'])) {
            $amount = $updateData['amount'] ?? $invoice->amount;
            $taxAmount = $updateData['tax_amount'] ?? $invoice->tax_amount;
            $discountAmount = $updateData['discount_amount'] ?? $invoice->discount_amount;
            
            $updateData['total_amount'] = $amount + $taxAmount - $discountAmount;
        }

        $invoice->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => $invoice->load(['serviceRequest.user', 'serviceRequest.car', 'serviceRequest.branch'])
        ]);
    }

    /**
     * Remove the specified invoice
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Only allow deletion if invoice is pending
        if ($invoice->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete invoice that has been sent or paid'
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully'
        ]);
    }
}
