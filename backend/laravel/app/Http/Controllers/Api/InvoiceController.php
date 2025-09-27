<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\Rental;
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
        
        $query = Invoice::with([
            'invoiceable' => function($morphTo) {
                $morphTo->morphWith([
                    ServiceRequest::class => ['car', 'branch'],
                    Rental::class => ['car.branch', 'customer']
                ]);
            },
            // Keep backward compatibility
            'serviceRequest.car', 
            'serviceRequest.branch'
        ]);
        
        // Filter based on user role
        if ($user && $user->role === 'branch_manager') {
            $query->where(function($q) use ($user) {
                // Service request invoices
                $q->whereHasMorph('invoiceable', [ServiceRequest::class], function($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                })
                // Rental invoices
                ->orWhereHasMorph('invoiceable', [Rental::class], function($query) use ($user) {
                    $query->whereHas('car.branch', function($carQuery) use ($user) {
                        $carQuery->where('id', $user->branch_id);
                    });
                })
                // Legacy service request invoices
                ->orWhereHas('serviceRequest', function($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                });
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
            'invoiceable_type' => 'required|in:service_request,rental',
            'invoiceable_id' => 'required|integer',
            // Legacy support
            'service_request_id' => 'nullable|exists:service_requests,id',
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

        // Determine the invoiceable model and ID
        $invoiceableType = $request->invoiceable_type;
        $invoiceableId = $request->invoiceable_id;
        $invoiceableModel = null;
        
        // Handle legacy service_request_id parameter
        if (!$invoiceableType && $request->service_request_id) {
            $invoiceableType = 'service_request';
            $invoiceableId = $request->service_request_id;
        }

        // Validate and get the invoiceable model
        if ($invoiceableType === 'service_request') {
            $invoiceableModel = ServiceRequest::find($invoiceableId);
            $modelClass = 'App\\Models\\ServiceRequest';
        } elseif ($invoiceableType === 'rental') {
            $invoiceableModel = Rental::find($invoiceableId);
            $modelClass = 'App\\Models\\Rental';
        }

        if (!$invoiceableModel) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($invoiceableType) . ' not found'
            ], 404);
        }
        
        // Check if invoice already exists for this entity
        $existingInvoice = Invoice::where('invoiceable_type', $modelClass)
                                 ->where('invoiceable_id', $invoiceableId)
                                 ->first();
        
        // Also check legacy service requests
        if ($invoiceableType === 'service_request') {
            $legacyInvoice = Invoice::where('service_request_id', $invoiceableId)->first();
            if ($legacyInvoice) {
                $existingInvoice = $legacyInvoice;
            }
        }
                                 
        if ($existingInvoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice already exists for this ' . str_replace('_', ' ', $invoiceableType)
            ], 422);
        }

        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

        $invoiceData = [
            'invoiceable_type' => $modelClass,
            'invoiceable_id' => $invoiceableId,
            'invoice_number' => $invoiceNumber,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'discount_amount' => $request->discount_amount ?? 0,
            'total_amount' => $request->amount + ($request->tax_amount ?? 0) - ($request->discount_amount ?? 0),
            'notes' => $request->notes,
            'status' => 'pending',
            'issued_by' => Auth::id() ?? 1, // Default to user 1 if not authenticated
        ];
        
        // Keep legacy support
        if ($invoiceableType === 'service_request') {
            $invoiceData['service_request_id'] = $invoiceableId;
        }

        $invoice = Invoice::create($invoiceData);

        // Load the appropriate relationships
        $relationshipToLoad = $invoiceableType === 'rental' 
            ? ['invoiceable.car.branch', 'invoiceable.customer']
            : ['invoiceable.car', 'invoiceable.branch'];

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $invoice->load($relationshipToLoad)
        ], 201);
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'invoiceable' => function($morphTo) {
                $morphTo->morphWith([
                    ServiceRequest::class => ['car', 'branch'],
                    Rental::class => ['car.branch', 'customer']
                ]);
            },
            // Keep backward compatibility
            'serviceRequest.car', 
            'serviceRequest.branch'
        ])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user && $user->role === 'branch_manager') {
            $hasAccess = false;
            
            // Check polymorphic relationships
            if ($invoice->invoiceable instanceof ServiceRequest) {
                $hasAccess = $invoice->invoiceable->branch_id === $user->branch_id;
            } elseif ($invoice->invoiceable instanceof Rental) {
                $hasAccess = $invoice->invoiceable->car->branch_id === $user->branch_id;
            }
            
            // Check legacy relationship
            if (!$hasAccess && $invoice->serviceRequest) {
                $hasAccess = $invoice->serviceRequest->branch_id === $user->branch_id;
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }
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
        $invoice = Invoice::with([
            'invoiceable' => function($morphTo) {
                $morphTo->morphWith([
                    ServiceRequest::class => ['car', 'branch'],
                    Rental::class => ['car.branch', 'customer']
                ]);
            },
            // Keep backward compatibility
            'serviceRequest.car', 
            'serviceRequest.branch'
        ])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check access permissions (temporarily disabled for testing)
        $user = Auth::user();
        if ($user && $user->role === 'branch_manager') {
            $hasAccess = false;
            
            // Check polymorphic relationships
            if ($invoice->invoiceable instanceof ServiceRequest) {
                $hasAccess = $invoice->invoiceable->branch_id === $user->branch_id;
            } elseif ($invoice->invoiceable instanceof Rental) {
                $hasAccess = $invoice->invoiceable->car->branch_id === $user->branch_id;
            }
            
            // Check legacy relationship
            if (!$hasAccess && $invoice->serviceRequest) {
                $hasAccess = $invoice->serviceRequest->branch_id === $user->branch_id;
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }
        }

        try {
            $pdf = Pdf::loadView('pdfs.invoice', compact('invoice'));
            return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Email invoice PDF to customer
     */
    public function emailPdf($id)
    {
        $invoice = Invoice::with([
            'invoiceable' => function($morphTo) {
                $morphTo->morphWith([
                    ServiceRequest::class => ['car', 'branch'],
                    Rental::class => ['car.branch', 'customer']
                ]);
            },
            // Keep backward compatibility
            'serviceRequest.car', 
            'serviceRequest.branch'
        ])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check access permissions
        $user = Auth::user();
        if ($user && $user->role === 'branch_manager') {
            $hasAccess = false;
            
            // Check polymorphic relationships
            if ($invoice->invoiceable instanceof ServiceRequest) {
                $hasAccess = $invoice->invoiceable->branch_id === $user->branch_id;
            } elseif ($invoice->invoiceable instanceof Rental) {
                $hasAccess = $invoice->invoiceable->car->branch_id === $user->branch_id;
            }
            
            // Check legacy relationship
            if (!$hasAccess && $invoice->serviceRequest) {
                $hasAccess = $invoice->serviceRequest->branch_id === $user->branch_id;
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }
        }

        try {
            $pdf = Pdf::loadView('pdfs.invoice', compact('invoice'));
            $pdfContent = $pdf->output();

            // Resolve recipient email
            $toEmail = null;
            
            // For polymorphic relationships
            if ($invoice->invoiceable instanceof ServiceRequest) {
                $toEmail = $invoice->invoiceable->customer_email;
            } elseif ($invoice->invoiceable instanceof Rental && $invoice->invoiceable->customer) {
                $toEmail = $invoice->invoiceable->customer->email;
            }
            
            // Legacy fallback
            if (!$toEmail && $invoice->serviceRequest && $invoice->serviceRequest->customer_email) {
                $toEmail = $invoice->serviceRequest->customer_email;
            }
            
            // If no direct email, try to find a user by phone
            if (!$toEmail) {
                $phone = null;
                if ($invoice->invoiceable instanceof ServiceRequest) {
                    $phone = $invoice->invoiceable->customer_phone;
                } elseif ($invoice->invoiceable instanceof Rental && $invoice->invoiceable->customer) {
                    $phone = $invoice->invoiceable->customer->phone;
                } elseif ($invoice->serviceRequest) {
                    $phone = $invoice->serviceRequest->customer_phone;
                }
                
                if ($phone) {
                    $recipientUser = User::where('phone', $phone)->first();
                    if ($recipientUser && $recipientUser->email) {
                        $toEmail = $recipientUser->email;
                    }
                }
            }

            if (!$toEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not available for this invoice'
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
                'message' => 'Invoice emailed successfully to ' . $toEmail
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
            'data' => $invoice->load(['serviceRequest.car', 'serviceRequest.branch'])
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
