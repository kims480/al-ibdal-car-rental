<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Branch;
use App\Models\Wilayat;
use App\Models\WilayatBranchAssignment;
use App\Services\CustomerNotificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceRequestNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ServiceRequestController extends Controller
{
    protected $customerNotificationService;

    public function __construct(CustomerNotificationService $customerNotificationService = null)
    {
        $this->customerNotificationService = $customerNotificationService ?? app(CustomerNotificationService::class);
    }
    /**
     * Display a listing of the service requests.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ServiceRequest::with(['car', 'branch', 'partner', 'subcontractor', 'governorate', 'wilayat']);

        // Filter by user role only if authenticated
        if ($user) {
            if (method_exists($user, 'isBranchManager') && $user->isBranchManager() && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            } elseif (method_exists($user, 'isPartner') && $user->isPartner() && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            } elseif (method_exists($user, 'isCustomer') && $user->isCustomer()) {
                $query->where('customer_phone', $user->phone);
            }
        }

        // Apply additional filters
        if ($request->has('status')) {
            $query->where('workflow_status', $request->status);
        }

        if ($request->has('workflow_status')) {
            $query->where('workflow_status', $request->workflow_status);
        }

        if ($request->has('branch_id') && $user->isAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $serviceRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $serviceRequests
        ]);
    }

    /**
     * Store a newly created service request.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_location' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
            'wilayat_id' => 'required|exists:wilayats,id',
            'rental_start' => 'required|date|after_or_equal:today',
            'rental_end' => 'required|date|after:rental_start',
            'service_type' => 'nullable|string|max:50',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Auto-assign to branch based on wilayat assignment
        $branch = $this->findAssignedBranch($request->wilayat_id);
        
        // If no specific assignment found, fallback to nearest branch based on location
        if (!$branch) {
            $branch = $this->findNearestBranch($request->customer_location);
        }

        $serviceRequest = ServiceRequest::create([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_location' => $request->customer_location,
            'governorate_id' => $request->governorate_id,
            'wilayat_id' => $request->wilayat_id,
            'rental_start' => $request->rental_start,
            'rental_end' => $request->rental_end,
            'partner_id' => $request->user() && $request->user()->role === 'partner' ? $request->user()->id : null, // Only set if user is a partner
            'priority' => $request->priority ?? 'medium',
            'workflow_status' => 'submitted', // Use new workflow status
            'notes' => $request->notes,
            'submitted_at' => now(),
        ]);

        // Send notifications
        // 1) If customer email exists on user table (matched by phone) or request includes 'email', notify requester
        try {
            $recipientUser = User::where('phone', $serviceRequest->customer_phone)->first();
            if ($recipientUser) {
                Mail::to($recipientUser->email)->queue(new ServiceRequestNotification($serviceRequest->fresh(['branch','car','partner']), $recipientUser, 'created'));
            } elseif ($request->filled('email')) {
                $pseudo = new User(['name' => $serviceRequest->customer_name, 'email' => $request->email]);
                Mail::to($request->email)->queue(new ServiceRequestNotification($serviceRequest->fresh(['branch','car','partner']), $pseudo, 'created'));
            }

            // 2) Notify admins
            $admins = User::where('role', 'admin')->where('active', true)->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new ServiceRequestNotification($serviceRequest->fresh(['branch','car','partner']), $admin, 'created'));
            }
        } catch (\Throwable $e) {
            // Log and continue without failing request
            Log::warning('ServiceRequest notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Service request created successfully',
            'data' => $serviceRequest->load(['branch'])
        ], 201);
    }

    /**
     * Display the specified service request.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::with(['car', 'branch', 'partner', 'subcontractor'])->findOrFail($id);

        // Check authorization
        if (!$this->canViewServiceRequest($user, $serviceRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $serviceRequest
        ]);
    }

    /**
     * Update the specified service request.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        // Check authorization
        if (!$this->canEditServiceRequest($user, $serviceRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_phone' => 'sometimes|required|string|max:20',
            'customer_location' => 'sometimes|required|string|max:255',
            'rental_start' => 'sometimes|required|date',
            'rental_end' => 'sometimes|required|date|after:rental_start',
            'workflow_status' => 'sometimes|required|in:submitted,under_review,assigned,car_allocated,customer_notified,car_delivered,rental_active,car_returned,payment_pending,completed,cancelled',
            'allocated_car_id' => 'sometimes|nullable|exists:cars,id',
            'partner_id' => 'sometimes|nullable|exists:partners,id',
            'subcontractor_id' => 'sometimes|nullable|exists:subcontractors,id',
            'priority' => 'sometimes|nullable|in:low,medium,high,urgent',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->update($request->only([
            'customer_name', 'customer_phone', 'customer_location',
            'rental_start', 'rental_end', 'workflow_status', 'allocated_car_id', 
            'partner_id', 'subcontractor_id', 
            'priority', 'notes'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Service request updated successfully',
            'data' => $serviceRequest->load(['car', 'branch', 'partner', 'subcontractor'])
        ]);
    }

    /**
     * Remove the specified service request.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $serviceRequest = ServiceRequest::findOrFail($id);
        $serviceRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service request deleted successfully'
        ]);
    }

    /**
     * HQ Admin: Review and process a service request
     */
    public function reviewServiceRequest(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can review service requests'
            ], 403);
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$serviceRequest->canTransitionTo('under_review')) {
            return response()->json([
                'success' => false,
                'message' => 'Service request cannot be moved to review state'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'review_notes' => 'nullable|string|max:1000',
            'assigned_by_admin_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->transitionTo('under_review', [
            'reviewed_at' => now(),
            'reviewed_by_admin_id' => $user->id,
            'review_notes' => $request->review_notes,
            'assigned_by_admin_id' => $request->assigned_by_admin_id ?? $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service request is now under review',
            'data' => $serviceRequest->fresh(['branch', 'reviewedByAdmin', 'assignedByAdmin'])
        ]);
    }

    /**
     * HQ Admin: Assign service request to branch or handle directly in Muscat
     */
    public function assignServiceRequest(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can assign service requests'
            ], 403);
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$serviceRequest->canTransitionTo('assigned')) {
            return response()->json([
                'success' => false,
                'message' => 'Service request cannot be assigned at this time'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'assignment_type' => 'required|in:branch,muscat_hq',
            'branch_id' => 'required_if:assignment_type,branch|exists:branches,id',
            'assignment_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'assigned_at' => now(),
            'assigned_by_admin_id' => $user->id,
            'assignment_notes' => $request->assignment_notes,
        ];

        if ($request->assignment_type === 'branch') {
            $updateData['assigned_branch_id'] = $request->branch_id;
            $updateData['is_muscat_direct'] = false;
        } else {
            // Handle directly in Muscat HQ
            $updateData['is_muscat_direct'] = true;
            $updateData['assigned_branch_id'] = null;
        }

        $serviceRequest->transitionTo('assigned', $updateData);

        // Auto-determine if customer is near Muscat for direct handling
        if ($request->assignment_type === 'muscat_hq' || $serviceRequest->isNearMuscat()) {
            $serviceRequest->update(['is_muscat_direct' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => $request->assignment_type === 'branch' 
                ? 'Service request assigned to branch' 
                : 'Service request will be handled directly by Muscat HQ',
            'data' => $serviceRequest->fresh(['assignedBranch', 'assignedByAdmin'])
        ]);
    }

    /**
     * Branch Manager: Allocate a car to the service request
     */
    public function allocateCar(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        // Check authorization - Admin, or Branch Manager of assigned branch
        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to allocate cars for this request'
            ], 403);
        }

        if (!$serviceRequest->canTransitionTo('car_allocated')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot allocate car at this stage'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'allocated_car_id' => 'required|exists:cars,id',
            'allocation_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if car is available for the requested dates
        $carAvailable = $this->isCarAvailable($request->allocated_car_id, $serviceRequest->rental_start, $serviceRequest->rental_end);
        
        if (!$carAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'Selected car is not available for the requested dates'
            ], 400);
        }

        $serviceRequest->transitionTo('car_allocated', [
            'allocated_car_id' => $request->allocated_car_id,
            'car_allocated_at' => now(),
            'car_allocated_by_id' => $user->id,
            'allocation_notes' => $request->allocation_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Car allocated successfully',
            'data' => $serviceRequest->fresh(['allocatedCar', 'carAllocatedBy'])
        ]);
    }

    /**
     * Notify customer that car is ready
     */
    public function notifyCustomer(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send customer notifications'
            ], 403);
        }

        if (!$serviceRequest->canTransitionTo('customer_notified')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot notify customer at this stage'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'notification_method' => 'required|in:sms,email,both',
            'pickup_instructions' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->transitionTo('customer_notified', [
            'customer_notified_at' => now(),
            'customer_notification_method' => $request->notification_method,
            'pickup_instructions' => $request->pickup_instructions,
        ]);

        // Send customer notification using the service
        try {
            $this->customerNotificationService->sendServiceRequestNotification(
                $serviceRequest, 
                'car_ready', 
                [
                    'pickup_instructions' => $request->pickup_instructions,
                    'notification_method' => $request->notification_method
                ]
            );
        } catch (\Exception $e) {
            Log::warning('Customer notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer notified successfully',
            'data' => $serviceRequest->fresh()
        ]);
    }

    /**
     * Assign a service request to a partner
     */
    public function assignToPartner(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && !$user->isBranchManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:partners,id',
            'car_id' => 'nullable|exists:cars,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->update([
            'partner_id' => $request->partner_id,
            'car_id' => $request->car_id,
            'status' => 'assigned'
        ]);

        // Send notifications
        try {
            $this->customerNotificationService->sendServiceRequestNotification($serviceRequest->fresh(), 'assigned');
        } catch (\Exception $e) {
            Log::warning('Assignment notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Service request assigned successfully',
            'data' => $serviceRequest->load(['car', 'branch', 'partner'])
        ]);
    }

    /**
     * Assign a service request to a subcontractor
     */
    public function assignToSubcontractor(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && !$user->isBranchManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'service_type' => 'required|string|max:50',
            'priority' => 'nullable|in:low,medium,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->update([
            'subcontractor_id' => $request->subcontractor_id,
            'service_type' => $request->service_type,
            'priority' => $request->priority ?? $serviceRequest->priority ?? 'medium',
            'status' => 'assigned'
        ]);

        // Send notifications
        try {
            $this->customerNotificationService->sendServiceRequestNotification($serviceRequest->fresh(), 'assigned_subcontractor');
        } catch (\Exception $e) {
            Log::warning('Subcontractor assignment notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Service request assigned to subcontractor successfully',
            'data' => $serviceRequest->load(['car', 'branch', 'subcontractor'])
        ]);
    }

    /**
     * Mark car as delivered and start rental
     */
    public function deliverCar(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to deliver cars'
            ], 403);
        }

        if (!$serviceRequest->canTransitionTo('car_delivered')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deliver car at this stage'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'delivery_notes' => 'nullable|string|max:1000',
            'odometer_reading' => 'nullable|numeric|min:0',
            'fuel_level' => 'nullable|in:empty,quarter,half,three_quarters,full',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->transitionTo('car_delivered', [
            'car_delivered_at' => now(),
            'delivery_notes' => $request->delivery_notes,
            'pickup_odometer_reading' => $request->odometer_reading,
            'pickup_fuel_level' => $request->fuel_level,
        ]);

        // Automatically transition to rental_active and send notification
        $serviceRequest->transitionTo('rental_active', [
            'rental_started_at' => now(),
        ]);

        // Send delivery confirmation notification
        try {
            $this->customerNotificationService->sendServiceRequestNotification(
                $serviceRequest, 
                'car_delivered', 
                $request->only(['delivery_notes', 'odometer_reading', 'fuel_level'])
            );
        } catch (\Exception $e) {
            Log::warning('Car delivery notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Car delivered successfully and rental is now active',
            'data' => $serviceRequest->fresh()
        ]);
    }

    /**
     * Enhanced car return processing with detailed calculations
     */
    public function processCarReturn(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::with(['allocatedCar', 'assignedBranch'])->findOrFail($id);

        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to process car returns'
            ], 403);
        }

        if (!$serviceRequest->canTransitionTo('car_returned')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process car return at this stage'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'return_notes' => 'nullable|string|max:1000',
            'return_odometer_reading' => 'nullable|numeric|min:0',
            'return_fuel_level' => 'nullable|in:empty,quarter,half,three_quarters,full',
            'damage_assessment' => 'nullable|array',
            'damage_assessment.*.type' => 'required|string|max:255',
            'damage_assessment.*.description' => 'required|string|max:500',
            'damage_assessment.*.severity' => 'required|in:minor,moderate,major',
            'damage_assessment.*.estimated_cost' => 'nullable|numeric|min:0',
            'cleaning_required' => 'nullable|boolean',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'late_return_acknowledged' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate comprehensive return details
        $returnDetails = $this->calculateReturnDetails($serviceRequest, $request->all());

        $serviceRequest->transitionTo('car_returned', [
            'car_returned_at' => now(),
            'rental_ended_at' => now(),
            'return_notes' => $request->return_notes,
            'return_odometer_reading' => $request->return_odometer_reading,
            'return_fuel_level' => $request->return_fuel_level,
            'damage_notes' => $returnDetails['damage_summary'],
            'extra_charges' => $returnDetails['total_extra_charges'],
            'late_return_hours' => $returnDetails['late_hours'],
            'cleaning_fee' => $returnDetails['cleaning_fee'],
        ]);

        // Calculate total rental amount including extras
        $baseAmount = $serviceRequest->calculateRentalAmount();
        $totalAmount = $baseAmount + $returnDetails['total_extra_charges'];
        
        $serviceRequest->transitionTo('payment_pending', [
            'calculated_amount' => $totalAmount,
            'base_rental_amount' => $baseAmount,
            'extra_charges_breakdown' => json_encode($returnDetails['charges_breakdown']),
            'payment_due_date' => now()->addDays(7),
        ]);

        // Send payment due notification with detailed breakdown
        try {
            $this->customerNotificationService->sendServiceRequestNotification(
                $serviceRequest, 
                'payment_due', 
                array_merge($request->only(['return_notes', 'damage_notes']), [
                    'return_details' => $returnDetails,
                    'base_amount' => $baseAmount,
                    'total_amount' => $totalAmount
                ])
            );
        } catch (\Exception $e) {
            Log::warning('Payment due notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Car returned successfully, payment is pending',
            'data' => [
                'service_request' => $serviceRequest->fresh(),
                'return_details' => $returnDetails,
                'base_rental_amount' => $baseAmount,
                'total_amount' => $totalAmount
            ]
        ]);
    }

    /**
     * Calculate detailed return information including late fees, damage costs, etc.
     */
    private function calculateReturnDetails(ServiceRequest $serviceRequest, array $returnData)
    {
        $details = [
            'late_hours' => 0,
            'late_fee' => 0,
            'damage_costs' => 0,
            'cleaning_fee' => 0,
            'fuel_penalty' => 0,
            'mileage_overage' => 0,
            'total_extra_charges' => 0,
            'charges_breakdown' => [],
            'damage_summary' => null,
        ];

        // Calculate late return fees
        $expectedReturn = \Carbon\Carbon::parse($serviceRequest->rental_end);
        $actualReturn = now();
        
        if ($actualReturn > $expectedReturn) {
            $details['late_hours'] = $actualReturn->diffInHours($expectedReturn);
            
            // Calculate late fees: $5 per hour for first 24 hours, then $10 per hour
            $lateHours = $details['late_hours'];
            if ($lateHours <= 24) {
                $details['late_fee'] = $lateHours * 5;
            } else {
                $details['late_fee'] = (24 * 5) + (($lateHours - 24) * 10);
            }
            
            $details['charges_breakdown']['late_return'] = [
                'hours' => $lateHours,
                'amount' => $details['late_fee'],
                'description' => "Late return fee for {$lateHours} hours"
            ];
        }

        // Process damage assessment
        if (isset($returnData['damage_assessment']) && is_array($returnData['damage_assessment'])) {
            $damageTotal = 0;
            $damageItems = [];
            
            foreach ($returnData['damage_assessment'] as $damage) {
                $cost = floatval($damage['estimated_cost'] ?? 0);
                $damageTotal += $cost;
                $damageItems[] = [
                    'type' => $damage['type'],
                    'description' => $damage['description'],
                    'severity' => $damage['severity'],
                    'cost' => $cost
                ];
            }
            
            $details['damage_costs'] = $damageTotal;
            $details['damage_summary'] = json_encode($damageItems);
            
            if ($damageTotal > 0) {
                $details['charges_breakdown']['damage_repair'] = [
                    'items' => count($damageItems),
                    'amount' => $damageTotal,
                    'description' => 'Vehicle damage repair costs'
                ];
            }
        }

        // Calculate cleaning fee
        if (isset($returnData['cleaning_required']) && $returnData['cleaning_required']) {
            $details['cleaning_fee'] = floatval($returnData['cleaning_fee'] ?? 25); // Default $25 cleaning fee
            $details['charges_breakdown']['cleaning'] = [
                'amount' => $details['cleaning_fee'],
                'description' => 'Interior/exterior cleaning fee'
            ];
        }

        // Calculate fuel penalty
        if (isset($returnData['return_fuel_level']) && isset($serviceRequest->pickup_fuel_level)) {
            $fuelPenalty = $this->calculateFuelPenalty(
                $serviceRequest->pickup_fuel_level, 
                $returnData['return_fuel_level']
            );
            
            if ($fuelPenalty > 0) {
                $details['fuel_penalty'] = $fuelPenalty;
                $details['charges_breakdown']['fuel_penalty'] = [
                    'amount' => $fuelPenalty,
                    'description' => 'Fuel replacement penalty'
                ];
            }
        }

        // Calculate mileage overage (if applicable)
        if (isset($returnData['return_odometer_reading']) && isset($serviceRequest->pickup_odometer_reading)) {
            $milesDriven = $returnData['return_odometer_reading'] - $serviceRequest->pickup_odometer_reading;
            $rentalDays = \Carbon\Carbon::parse($serviceRequest->rental_start)
                ->diffInDays(\Carbon\Carbon::parse($serviceRequest->rental_end)) + 1;
            
            $allowedMiles = $rentalDays * 200; // 200 miles per day allowance
            
            if ($milesDriven > $allowedMiles) {
                $overageMiles = $milesDriven - $allowedMiles;
                $details['mileage_overage'] = $overageMiles * 0.25; // $0.25 per extra mile
                
                $details['charges_breakdown']['mileage_overage'] = [
                    'overage_miles' => $overageMiles,
                    'amount' => $details['mileage_overage'],
                    'description' => "Mileage overage: {$overageMiles} miles at $0.25/mile"
                ];
            }
        }

        // Calculate total extra charges
        $details['total_extra_charges'] = $details['late_fee'] + 
                                         $details['damage_costs'] + 
                                         $details['cleaning_fee'] + 
                                         $details['fuel_penalty'] + 
                                         $details['mileage_overage'];

        return $details;
    }

    /**
     * Calculate fuel replacement penalty
     */
    private function calculateFuelPenalty(string $pickupLevel, string $returnLevel)
    {
        $fuelLevels = [
            'empty' => 0,
            'quarter' => 25,
            'half' => 50,
            'three_quarters' => 75,
            'full' => 100
        ];

        $pickupPercent = $fuelLevels[$pickupLevel] ?? 100;
        $returnPercent = $fuelLevels[$returnLevel] ?? 0;
        
        if ($returnPercent < $pickupPercent) {
            $fuelUsed = $pickupPercent - $returnPercent;
            return ($fuelUsed / 100) * 40; // $40 for full tank penalty
        }
        
        return 0;
    }

    /**
     * Mark car as returned and end rental (original method for compatibility)
     */
    public function returnCar(Request $request, string $id)
    {
        // Delegate to the enhanced return processing method
        return $this->processCarReturn($request, $id);
    }

    /**
     * Process payment and complete service request
     */
    public function processPayment(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to process payments'
            ], 403);
        }

        if (!$serviceRequest->canTransitionTo('completed')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process payment at this stage'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,card,bank_transfer,online',
            'payment_amount' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'generate_invoice' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->transitionTo('completed', [
            'payment_processed_at' => now(),
            'payment_method' => $request->payment_method,
            'payment_amount' => $request->payment_amount,
            'payment_reference' => $request->payment_reference,
            'invoice_generated' => $request->generate_invoice ?? false,
        ]);

        // Send completion notification
        try {
            $this->customerNotificationService->sendServiceRequestNotification(
                $serviceRequest, 
                'completed', 
                $request->only(['payment_method', 'payment_reference', 'generate_invoice'])
            );
        } catch (\Exception $e) {
            Log::warning('Completion notification failed: ' . $e->getMessage());
        }

        // TODO: Generate invoice if requested
        if ($request->generate_invoice) {
            try {
                $this->generateInvoice($serviceRequest);
            } catch (\Exception $e) {
                Log::warning('Invoice generation failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully, service request completed',
            'data' => $serviceRequest->fresh()
        ]);
    }

    /**
     * Cancel service request
     */
    public function cancelServiceRequest(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel service requests'
            ], 403);
        }

        if (!$serviceRequest->canTransitionTo('cancelled')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel service request at this stage'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:1000',
            'cancelled_by_customer' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->transitionTo('cancelled', [
            'cancelled_at' => now(),
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by_customer' => $request->cancelled_by_customer ?? false,
            'cancelled_by_user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service request cancelled successfully',
            'data' => $serviceRequest->fresh()
        ]);
    }

    /**
     * Get pending service requests for HQ admin review
     */
    public function getPendingReviews(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can view pending reviews'
            ], 403);
        }

        $pendingRequests = ServiceRequest::with(['partner', 'governorate', 'wilayat'])
            ->where('workflow_status', 'submitted')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $pendingRequests
        ]);
    }

    /**
     * Get workflow analytics and statistics
     */
    public function getWorkflowStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isBranchManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = ServiceRequest::query();
        
        // Branch managers see only their branch data
        if ($user->isBranchManager()) {
            $query->where('assigned_branch_id', $user->branch_id);
        }

        $stats = [
            'total_requests' => $query->count(),
            'status_breakdown' => $query->groupBy('workflow_status')
                ->selectRaw('workflow_status, count(*) as count')
                ->pluck('count', 'workflow_status'),
            'avg_processing_time' => $query->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, completed_at)) as avg_hours')
                ->value('avg_hours'),
            'revenue_this_month' => $query->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('payment_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Check if car is available for given dates with comprehensive conflict checking
     */
    private function isCarAvailable($carId, $startDate, $endDate)
    {
        // Check for overlapping service requests
        $conflictingRentals = ServiceRequest::where('allocated_car_id', $carId)
            ->whereIn('workflow_status', ['car_allocated', 'customer_notified', 'car_delivered', 'rental_active'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('rental_start', [$startDate, $endDate])
                      ->orWhereBetween('rental_end', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('rental_start', '<=', $startDate)
                            ->where('rental_end', '>=', $endDate);
                      });
            })
            ->count();

        // Also check regular rentals table for conflicts
        $conflictingStandardRentals = \App\Models\Rental::where('car_id', $carId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->count();

        return $conflictingRentals === 0 && $conflictingStandardRentals === 0;
    }

    /**
     * Get available cars for a service request with intelligent recommendations
     */
    public function getAvailableCars(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        // Check authorization
        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view available cars'
            ], 403);
        }

        $query = \App\Models\Car::where('status', 'available');

        // Filter by branch if assigned to branch
        if ($serviceRequest->assigned_branch_id) {
            $query->where('branch_id', $serviceRequest->assigned_branch_id);
        } elseif ($serviceRequest->is_muscat_direct) {
            // For Muscat direct handling, show cars from Muscat branches
            $muscatBranches = \App\Models\Branch::where('city', 'LIKE', '%Muscat%')
                ->where('is_active', true)
                ->pluck('id');
            $query->whereIn('branch_id', $muscatBranches);
        }

        $availableCars = $query->with(['branch', 'carType'])
            ->get()
            ->filter(function ($car) use ($serviceRequest) {
                return $this->isCarAvailable($car->id, $serviceRequest->rental_start, $serviceRequest->rental_end);
            })
            ->map(function ($car) use ($serviceRequest) {
                return [
                    'car' => $car,
                    'recommendation_score' => $this->calculateCarRecommendationScore($car, $serviceRequest),
                    'suitability_reasons' => $this->getCarSuitabilityReasons($car, $serviceRequest),
                ];
            })
            ->sortByDesc('recommendation_score')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'available_cars' => $availableCars,
                'total_available' => $availableCars->count(),
                'rental_period' => [
                    'start' => $serviceRequest->rental_start,
                    'end' => $serviceRequest->rental_end,
                    'duration_days' => now()->parse($serviceRequest->rental_start)->diffInDays($serviceRequest->rental_end) + 1
                ]
            ]
        ]);
    }

    /**
     * Calculate car recommendation score based on various factors
     */
    private function calculateCarRecommendationScore($car, $serviceRequest)
    {
        $score = 0;

        // Base availability score
        $score += 50;

        // Car condition score
        if ($car->condition === 'excellent') {
            $score += 20;
        } elseif ($car->condition === 'good') {
            $score += 15;
        } elseif ($car->condition === 'fair') {
            $score += 10;
        }

        // Mileage score (lower is better)
        if ($car->mileage < 50000) {
            $score += 15;
        } elseif ($car->mileage < 100000) {
            $score += 10;
        } elseif ($car->mileage < 150000) {
            $score += 5;
        }

        // Recent usage score (prefer less recently used cars)
        $lastRental = ServiceRequest::where('allocated_car_id', $car->id)
            ->whereIn('workflow_status', ['completed', 'cancelled'])
            ->orderBy('car_returned_at', 'desc')
            ->first();

        if (!$lastRental || $lastRental->car_returned_at < now()->subDays(7)) {
            $score += 10; // Well rested car
        }

        // Fuel efficiency for longer rentals
        $rentalDays = now()->parse($serviceRequest->rental_start)->diffInDays($serviceRequest->rental_end) + 1;
        if ($rentalDays > 7 && $car->fuel_type === 'petrol' && $car->engine_size <= 2000) {
            $score += 10; // Fuel efficient for long rentals
        }

        return $score;
    }

    /**
     * Get reasons why this car is suitable for the service request
     */
    private function getCarSuitabilityReasons($car, $serviceRequest)
    {
        $reasons = [];

        if ($car->condition === 'excellent') {
            $reasons[] = 'Excellent vehicle condition';
        }

        if ($car->mileage < 50000) {
            $reasons[] = 'Low mileage vehicle';
        } elseif ($car->mileage < 100000) {
            $reasons[] = 'Moderate mileage';
        }

        $rentalDays = now()->parse($serviceRequest->rental_start)->diffInDays($serviceRequest->rental_end) + 1;
        if ($rentalDays > 7) {
            $reasons[] = 'Suitable for extended rental period';
        }

        if ($car->fuel_type === 'petrol' && $rentalDays > 3) {
            $reasons[] = 'Cost-effective fuel type';
        }

        $lastRental = ServiceRequest::where('allocated_car_id', $car->id)
            ->whereIn('workflow_status', ['completed', 'cancelled'])
            ->orderBy('car_returned_at', 'desc')
            ->first();

        if (!$lastRental || $lastRental->car_returned_at < now()->subDays(7)) {
            $reasons[] = 'Recently available and well-maintained';
        }

        return $reasons;
    }

    /**
     * Bulk allocate cars to multiple service requests (admin feature)
     */
    public function bulkAllocateCars(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can bulk allocate cars'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'allocations' => 'required|array|min:1',
            'allocations.*.service_request_id' => 'required|exists:service_requests,id',
            'allocations.*.allocated_car_id' => 'required|exists:cars,id',
            'allocations.*.allocation_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($request->allocations as $allocation) {
            try {
                $serviceRequest = ServiceRequest::findOrFail($allocation['service_request_id']);
                
                if (!$serviceRequest->canTransitionTo('car_allocated')) {
                    $results[] = [
                        'service_request_id' => $allocation['service_request_id'],
                        'status' => 'failed',
                        'message' => 'Cannot allocate car at this stage'
                    ];
                    $failureCount++;
                    continue;
                }

                if (!$this->isCarAvailable($allocation['allocated_car_id'], $serviceRequest->rental_start, $serviceRequest->rental_end)) {
                    $results[] = [
                        'service_request_id' => $allocation['service_request_id'],
                        'status' => 'failed',
                        'message' => 'Car is not available for requested dates'
                    ];
                    $failureCount++;
                    continue;
                }

                $serviceRequest->transitionTo('car_allocated', [
                    'allocated_car_id' => $allocation['allocated_car_id'],
                    'car_allocated_at' => now(),
                    'car_allocated_by_id' => $user->id,
                    'allocation_notes' => $allocation['allocation_notes'] ?? null,
                ]);

                $results[] = [
                    'service_request_id' => $allocation['service_request_id'],
                    'status' => 'success',
                    'message' => 'Car allocated successfully'
                ];
                $successCount++;

            } catch (\Exception $e) {
                $results[] = [
                    'service_request_id' => $allocation['service_request_id'],
                    'status' => 'failed',
                    'message' => 'Allocation failed: ' . $e->getMessage()
                ];
                $failureCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk allocation completed: {$successCount} successful, {$failureCount} failed",
            'data' => [
                'results' => $results,
                'summary' => [
                    'total' => count($request->allocations),
                    'successful' => $successCount,
                    'failed' => $failureCount
                ]
            ]
        ]);
    }

    /**
     * Send return reminder to customers (automated job/manual trigger)
     */
    public function sendReturnReminder(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$user->isAdmin() && 
            (!$user->isBranchManager() || $user->branch_id !== $serviceRequest->assigned_branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send return reminders'
            ], 403);
        }

        if ($serviceRequest->workflow_status !== 'rental_active') {
            return response()->json([
                'success' => false,
                'message' => 'Return reminder can only be sent for active rentals'
            ], 400);
        }

        try {
            $this->customerNotificationService->sendServiceRequestNotification(
                $serviceRequest, 
                'reminder_return'
            );

            // Log the reminder
            Log::info('Return reminder sent', [
                'service_request_id' => $serviceRequest->id,
                'sent_by_user_id' => $user->id,
                'rental_end' => $serviceRequest->rental_end
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Return reminder sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send return reminder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk return reminders for rentals ending soon
     */
    public function sendBulkReturnReminders(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can send bulk return reminders'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'days_before' => 'nullable|integer|min:0|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $daysBefore = $request->days_before ?? 1; // Default to 1 day before
        $reminderDate = now()->addDays($daysBefore)->format('Y-m-d');

        $activeRentals = ServiceRequest::where('workflow_status', 'rental_active')
            ->whereDate('rental_end', $reminderDate)
            ->with(['allocatedCar', 'assignedBranch'])
            ->get();

        $successCount = 0;
        $failureCount = 0;

        foreach ($activeRentals as $rental) {
            try {
                $this->customerNotificationService->sendServiceRequestNotification(
                    $rental, 
                    'reminder_return'
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::warning('Bulk reminder failed for rental ' . $rental->id . ': ' . $e->getMessage());
                $failureCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk return reminders sent: {$successCount} successful, {$failureCount} failed",
            'data' => [
                'total_rentals' => $activeRentals->count(),
                'successful' => $successCount,
                'failed' => $failureCount,
                'reminder_date' => $reminderDate
            ]
        ]);
    }

    /**
     * Generate invoice (enhanced implementation)
     */
    private function generateInvoice($serviceRequest)
    {
        try {
            $invoiceData = $this->prepareInvoiceData($serviceRequest);
            
            // Create invoice record
            $invoice = \App\Models\Invoice::create([
                'service_request_id' => $serviceRequest->id,
                'customer_name' => $serviceRequest->customer_name,
                'customer_phone' => $serviceRequest->customer_phone,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => $serviceRequest->payment_due_date,
                'subtotal' => $invoiceData['subtotal'],
                'tax_amount' => $invoiceData['tax_amount'],
                'total_amount' => $serviceRequest->payment_amount,
                'status' => 'paid',
                'items' => json_encode($invoiceData['items']),
                'payment_method' => $serviceRequest->payment_method,
                'payment_date' => $serviceRequest->payment_processed_at,
                'notes' => 'Service request rental invoice'
            ]);

            // Generate PDF if needed
            if (config('app.generate_invoice_pdf', true)) {
                $this->generateInvoicePdf($invoice, $serviceRequest);
            }

            // Send invoice via email if customer has email
            $customer = \App\Models\User::where('phone', $serviceRequest->customer_phone)->first();
            if ($customer && $customer->email) {
                $this->emailInvoice($invoice, $customer->email);
            }

            Log::info("Invoice generated successfully", [
                'service_request_id' => $serviceRequest->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $serviceRequest->payment_amount
            ]);

            return $invoice;

        } catch (\Exception $e) {
            Log::error("Invoice generation failed", [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare invoice data structure
     */
    private function prepareInvoiceData($serviceRequest)
    {
        $items = [];
        $subtotal = 0;

        // Base rental charge
        $baseAmount = floatval($serviceRequest->base_rental_amount ?? $serviceRequest->calculated_amount);
        $rentalDays = \Carbon\Carbon::parse($serviceRequest->rental_start)
            ->diffInDays(\Carbon\Carbon::parse($serviceRequest->rental_end)) + 1;

        $items[] = [
            'description' => "Car Rental - {$rentalDays} days",
            'details' => "Period: " . date('M j, Y', strtotime($serviceRequest->rental_start)) . 
                        " to " . date('M j, Y', strtotime($serviceRequest->rental_end)),
            'quantity' => $rentalDays,
            'unit_price' => $baseAmount / $rentalDays,
            'total' => $baseAmount
        ];
        $subtotal += $baseAmount;

        // Add extra charges if any
        if ($serviceRequest->extra_charges > 0) {
            $chargesBreakdown = json_decode($serviceRequest->extra_charges_breakdown ?? '[]', true);
            
            if (!empty($chargesBreakdown)) {
                foreach ($chargesBreakdown as $chargeType => $chargeInfo) {
                    $items[] = [
                        'description' => ucfirst(str_replace('_', ' ', $chargeType)),
                        'details' => $chargeInfo['description'] ?? '',
                        'quantity' => 1,
                        'unit_price' => floatval($chargeInfo['amount']),
                        'total' => floatval($chargeInfo['amount'])
                    ];
                    $subtotal += floatval($chargeInfo['amount']);
                }
            } else {
                // Fallback for single extra charge
                $items[] = [
                    'description' => 'Additional Charges',
                    'details' => 'Late fees, damages, cleaning, etc.',
                    'quantity' => 1,
                    'unit_price' => floatval($serviceRequest->extra_charges),
                    'total' => floatval($serviceRequest->extra_charges)
                ];
                $subtotal += floatval($serviceRequest->extra_charges);
            }
        }

        // Calculate tax (5% VAT in Oman)
        $taxRate = 0.05;
        $taxAmount = $subtotal * $taxRate;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount
        ];
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = \App\Models\Invoice::whereRaw('YEAR(created_at) = ?', [$year])
            ->whereRaw('MONTH(created_at) = ?', [$month])
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice && preg_match('/INV-' . $year . $month . '-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Generate PDF invoice (placeholder for actual PDF generation)
     */
    private function generateInvoicePdf($invoice, $serviceRequest)
    {
        // TODO: Implement actual PDF generation using libraries like TCPDF, DOMPDF, or Laravel Snappy
        Log::info("Invoice PDF generated (placeholder)", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number
        ]);
        
        // Placeholder for PDF generation:
        // $pdf = PDF::loadView('invoices.pdf', compact('invoice', 'serviceRequest'));
        // Storage::put('invoices/' . $invoice->invoice_number . '.pdf', $pdf->output());
    }

    /**
     * Email invoice to customer
     */
    private function emailInvoice($invoice, $customerEmail)
    {
        try {
            // TODO: Create proper invoice email template
            Log::info("Invoice emailed to customer (placeholder)", [
                'invoice_id' => $invoice->id,
                'customer_email' => $customerEmail
            ]);
            
            // Placeholder for email sending:
            // Mail::to($customerEmail)->send(new InvoiceMail($invoice));
            
        } catch (\Exception $e) {
            Log::warning("Failed to email invoice: " . $e->getMessage());
        }
    }

    /**
     * Get invoice for service request
     */
    public function getServiceRequestInvoice(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        // Check authorization
        if (!$this->canViewServiceRequest($user, $serviceRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $invoice = \App\Models\Invoice::where('service_request_id', $serviceRequest->id)->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'No invoice found for this service request'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'service_request' => $serviceRequest->load(['allocatedCar', 'assignedBranch', 'partner'])
            ]
        ]);
    }

    /**
     * Regenerate invoice for service request
     */
    public function regenerateInvoice(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can regenerate invoices'
            ], 403);
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        if ($serviceRequest->workflow_status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice can only be regenerated for completed service requests'
            ], 400);
        }

        try {
            // Delete existing invoice if any
            \App\Models\Invoice::where('service_request_id', $serviceRequest->id)->delete();

            // Generate new invoice
            $invoice = $this->generateInvoice($serviceRequest);

            return response()->json([
                'success' => true,
                'message' => 'Invoice regenerated successfully',
                'data' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive payment receipt
     */
    public function generatePaymentReceipt(Request $request, string $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($id);

        if (!$this->canViewServiceRequest($user, $serviceRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        if ($serviceRequest->workflow_status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment receipt only available for completed rentals'
            ], 404);
        }

        $receiptData = [
            'receipt_number' => 'RCP-' . date('Ymd') . '-' . $serviceRequest->id,
            'service_request' => $serviceRequest->load(['allocatedCar', 'assignedBranch']),
            'payment_details' => [
                'amount_paid' => $serviceRequest->payment_amount,
                'payment_method' => $serviceRequest->payment_method,
                'payment_date' => $serviceRequest->payment_processed_at,
                'payment_reference' => $serviceRequest->payment_reference,
            ],
            'rental_details' => [
                'start_date' => $serviceRequest->rental_start,
                'end_date' => $serviceRequest->rental_end,
                'car_delivered_at' => $serviceRequest->car_delivered_at,
                'car_returned_at' => $serviceRequest->car_returned_at,
                'actual_duration' => $serviceRequest->car_delivered_at && $serviceRequest->car_returned_at
                    ? \Carbon\Carbon::parse($serviceRequest->car_delivered_at)
                        ->diffInDays(\Carbon\Carbon::parse($serviceRequest->car_returned_at)) + 1
                    : 'N/A'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $receiptData
        ]);
    }

    /**
     * Find the nearest branch based on customer location
     */
    private function findNearestBranch($location)
    {
        // Simple logic - in production, use proper geocoding and distance calculation
        $location = strtolower($location);
        
        if (strpos($location, 'muscat') !== false) {
            return Branch::where('city', 'Muscat')->first();
        } elseif (strpos($location, 'sohar') !== false) {
            return Branch::where('city', 'Sohar')->first();
        } elseif (strpos($location, 'salalah') !== false) {
            return Branch::where('city', 'Salalah')->first();
        }
        
        // Default to first Muscat branch
        return Branch::where('city', 'Muscat')->first();
    }

    /**
     * Check if user can view service request
     */
    private function canViewServiceRequest($user, $serviceRequest)
    {
        if ($user->isAdmin()) {
            return true;
        }
        
        if ($user->isBranchManager() && $user->branch_id == $serviceRequest->branch_id) {
            return true;
        }
        
        if ($user->isPartner() && ($user->branch_id == $serviceRequest->branch_id || $serviceRequest->partner_id == $user->id)) {
            return true;
        }
        
        if ($user->isCustomer() && $user->phone == $serviceRequest->customer_phone) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can edit service request
     */
    private function canEditServiceRequest($user, $serviceRequest)
    {
        if ($user->isAdmin()) {
            return true;
        }
        
        if ($user->isBranchManager() && $user->branch_id == $serviceRequest->branch_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Find the assigned branch for a wilayat with enhanced logic
     */
    private function findAssignedBranch($wilayatId)
    {
        // First, try to get the primary active assignment
        $assignment = WilayatBranchAssignment::where('wilayat_id', $wilayatId)
            ->where('is_active', true)
            ->where('is_primary', true)
            ->with('branch')
            ->first();

        if ($assignment && $assignment->branch && $assignment->branch->is_active) {
            return $assignment->branch;
        }

        // If no primary assignment, get any active assignment
        $assignment = WilayatBranchAssignment::where('wilayat_id', $wilayatId)
            ->where('is_active', true)
            ->with('branch')
            ->first();

        if ($assignment && $assignment->branch && $assignment->branch->is_active) {
            return $assignment->branch;
        }

        return null;
    }

    /**
     * Enhanced branch assignment logic with distance and capacity consideration
     */
    private function getOptimalBranchAssignment($serviceRequest)
    {
        $wilayat = \App\Models\Wilayat::with('governorate')->find($serviceRequest->wilayat_id);
        
        // Strategy 1: Check wilayat-specific assignments
        $assignedBranch = $this->findAssignedBranch($serviceRequest->wilayat_id);
        if ($assignedBranch) {
            return [
                'branch' => $assignedBranch,
                'assignment_type' => 'wilayat_assigned',
                'reason' => "Assigned to {$assignedBranch->name} based on wilayat assignment"
            ];
        }

        // Strategy 2: Check if customer is in Muscat region for direct HQ handling
        if ($this->isNearMuscat($wilayat)) {
            return [
                'branch' => null,
                'assignment_type' => 'muscat_hq',
                'reason' => 'Customer location is near Muscat, will be handled directly by HQ'
            ];
        }

        // Strategy 3: Find nearest branch based on governorate
        $nearestBranch = $this->findNearestBranchByGovernorate($wilayat->governorate);
        if ($nearestBranch) {
            return [
                'branch' => $nearestBranch,
                'assignment_type' => 'governorate_based',
                'reason' => "Assigned to {$nearestBranch->name} as nearest branch in {$wilayat->governorate->name} governorate"
            ];
        }

        // Strategy 4: Fallback to any available branch
        $fallbackBranch = \App\Models\Branch::where('is_active', true)->first();
        return [
            'branch' => $fallbackBranch,
            'assignment_type' => 'fallback',
            'reason' => $fallbackBranch 
                ? "Assigned to {$fallbackBranch->name} as fallback branch"
                : 'No active branches available for assignment'
        ];
    }

    /**
     * Check if wilayat/location is near Muscat for direct HQ handling
     */
    private function isNearMuscat($wilayat)
    {
        $muscatGovernorate = strtolower($wilayat->governorate->name);
        $muscatWilayats = ['muscat', 'bausher', 'seeb', 'al-amarat', 'muttrah', 'quriyat'];
        
        // Check governorate name
        if (strpos($muscatGovernorate, 'muscat') !== false) {
            return true;
        }
        
        // Check specific wilayats near Muscat
        $wilayatName = strtolower($wilayat->name);
        foreach ($muscatWilayats as $muscatWilayat) {
            if (strpos($wilayatName, $muscatWilayat) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Find nearest branch based on governorate
     */
    private function findNearestBranchByGovernorate($governorate)
    {
        $governorateName = strtolower($governorate->name);
        
        // Try to find branch in same governorate first
        $branch = \App\Models\Branch::where('is_active', true)
            ->whereRaw('LOWER(city) LIKE ?', ["%{$governorateName}%"])
            ->orWhereRaw('LOWER(governorate) LIKE ?', ["%{$governorateName}%"])
            ->first();
            
        if ($branch) {
            return $branch;
        }
        
        // Map governorates to nearest major cities/branches
        $governorateMapping = [
            'al batinah north' => 'Sohar',
            'al batinah south' => 'Sohar', 
            'musandam' => 'Sohar',
            'al dakhliyah' => 'Nizwa',
            'al sharqiyah north' => 'Sur',
            'al sharqiyah south' => 'Sur',
            'al dhahirah' => 'Ibri',
            'al wusta' => 'Muscat', // Fallback to Muscat
            'dhofar' => 'Salalah',
        ];
        
        $mappedCity = $governorateMapping[$governorateName] ?? 'Muscat';
        
        return \App\Models\Branch::where('is_active', true)
            ->whereRaw('LOWER(city) LIKE ?', ["%".strtolower($mappedCity)."%"])
            ->first();
    }

    /**
     * Auto-assign service request with intelligent branch selection
     */
    public function autoAssignServiceRequest(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can auto-assign service requests'
            ], 403);
        }

        $serviceRequest = ServiceRequest::with(['wilayat.governorate'])->findOrFail($id);

        if (!in_array($serviceRequest->workflow_status, ['submitted', 'under_review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Service request cannot be auto-assigned at this stage'
            ], 400);
        }

        $assignment = $this->getOptimalBranchAssignment($serviceRequest);

        $updateData = [
            'auto_assigned_at' => now(),
            'auto_assigned_by_admin_id' => $user->id,
            'assignment_reason' => $assignment['reason'],
        ];

        if ($assignment['branch']) {
            $updateData['assigned_branch_id'] = $assignment['branch']->id;
            $updateData['is_muscat_direct'] = false;
        } else {
            $updateData['is_muscat_direct'] = true;
            $updateData['assigned_branch_id'] = null;
        }

        $serviceRequest->transitionTo('assigned', $updateData);

        return response()->json([
            'success' => true,
            'message' => 'Service request auto-assigned successfully',
            'data' => [
                'service_request' => $serviceRequest->fresh(['assignedBranch']),
                'assignment' => $assignment
            ]
        ]);
    }

    /**
     * Get branch recommendations for manual assignment
     */
    public function getBranchRecommendations(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HQ admins can view branch recommendations'
            ], 403);
        }

        $serviceRequest = ServiceRequest::with(['wilayat.governorate'])->findOrFail($id);
        $assignment = $this->getOptimalBranchAssignment($serviceRequest);

        // Get alternative branches
        $alternatives = \App\Models\Branch::where('is_active', true)
            ->with(['wilayatAssignments.wilayat'])
            ->get()
            ->map(function ($branch) use ($serviceRequest) {
                $distance = $this->calculateDistanceScore($branch, $serviceRequest);
                $capacity = $this->calculateCapacityScore($branch);
                
                return [
                    'branch' => $branch,
                    'distance_score' => $distance,
                    'capacity_score' => $capacity,
                    'total_score' => $distance + $capacity,
                ];
            })
            ->sortByDesc('total_score')
            ->take(5);

        return response()->json([
            'success' => true,
            'data' => [
                'recommended' => $assignment,
                'alternatives' => $alternatives->values(),
                'muscat_option_available' => $this->isNearMuscat($serviceRequest->wilayat)
            ]
        ]);
    }

    /**
     * Calculate distance-based score for branch assignment
     */
    private function calculateDistanceScore($branch, $serviceRequest)
    {
        // Simple scoring based on wilayat assignments and location matching
        $hasWilayatAssignment = $branch->wilayatAssignments()
            ->where('wilayat_id', $serviceRequest->wilayat_id)
            ->where('is_active', true)
            ->exists();
            
        if ($hasWilayatAssignment) {
            return 100; // Perfect match
        }
        
        // Check governorate proximity
        $wilayat = $serviceRequest->wilayat;
        $governorateName = strtolower($wilayat->governorate->name);
        $branchCity = strtolower($branch->city);
        
        if (strpos($governorateName, $branchCity) !== false || 
            strpos($branchCity, $governorateName) !== false) {
            return 75; // Good match
        }
        
        return 25; // Poor match
    }

    /**
     * Calculate capacity-based score for branch assignment
     */
    private function calculateCapacityScore($branch)
    {
        // Count active service requests assigned to this branch
        $activeRequests = ServiceRequest::where('assigned_branch_id', $branch->id)
            ->whereIn('workflow_status', ['assigned', 'car_allocated', 'customer_notified', 'car_delivered', 'rental_active'])
            ->count();
            
        // Count available cars at this branch
        $availableCars = \App\Models\Car::where('branch_id', $branch->id)
            ->where('status', 'available')
            ->count();
            
        if ($availableCars === 0) {
            return 0; // No cars available
        }
        
        $utilization = $activeRequests / max($availableCars, 1);
        
        if ($utilization < 0.5) {
            return 50; // Low utilization, high capacity
        } elseif ($utilization < 0.8) {
            return 30; // Medium utilization
        } else {
            return 10; // High utilization, low capacity
        }
    }
}
