<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Branch;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceRequestNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ServiceRequestController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService ?? new \stdClass(); // Temporary fix if the service is not implemented
    }
    /**
     * Display a listing of the service requests.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ServiceRequest::with(['car', 'branch', 'partner', 'subcontractor', 'rental']);

        // Filter by user role
        if ($user->isBranchManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isPartner() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isCustomer()) {
            $query->where('customer_phone', $user->phone);
        }

        // Apply additional filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
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

        // Auto-assign to nearest branch based on location
        $branch = $this->findNearestBranch($request->customer_location);

        $serviceRequest = ServiceRequest::create([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_location' => $request->customer_location,
            'rental_start' => $request->rental_start,
            'rental_end' => $request->rental_end,
            'branch_id' => $branch ? $branch->id : null,
            'service_type' => $request->service_type,
            'priority' => $request->priority ?? 'medium',
            'status' => 'pending',
            'notes' => $request->notes,
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
        $serviceRequest = ServiceRequest::with(['car', 'branch', 'partner', 'subcontractor', 'rental'])->findOrFail($id);

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
            'status' => 'sometimes|required|in:pending,assigned,confirmed,in_progress,completed,cancelled',
            'car_id' => 'sometimes|nullable|exists:cars,id',
            'partner_id' => 'sometimes|nullable|exists:partners,id',
            'subcontractor_id' => 'sometimes|nullable|exists:subcontractors,id',
            'service_type' => 'sometimes|nullable|string|max:50',
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
            'rental_start', 'rental_end', 'status', 'car_id', 
            'partner_id', 'subcontractor_id', 'service_type', 
            'priority', 'notes'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Service request updated successfully',
            'data' => $serviceRequest->load(['car', 'branch', 'partner', 'subcontractor', 'rental'])
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
        if (method_exists($this->notificationService, 'sendServiceRequestNotification')) {
            $this->notificationService->sendServiceRequestNotification($serviceRequest->fresh(), 'assigned');
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
        if (method_exists($this->notificationService, 'sendServiceRequestNotification')) {
            $this->notificationService->sendServiceRequestNotification($serviceRequest->fresh(), 'assigned_subcontractor');
        }

        return response()->json([
            'success' => true,
            'message' => 'Service request assigned to subcontractor successfully',
            'data' => $serviceRequest->load(['car', 'branch', 'subcontractor'])
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
}
