<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubcontractorController extends Controller
{
    /**
     * Display a listing of the subcontractors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all subcontractors
        $subcontractors = Subcontractor::all();
        
        return response()->json([
            'success' => true,
            'data' => $subcontractors
        ]);
    }

    /**
     * Store a newly created subcontractor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:subcontractors,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'services' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create subcontractor
        $subcontractor = Subcontractor::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Subcontractor created successfully',
            'data' => $subcontractor
        ], 201);
    }

    /**
     * Display the specified subcontractor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Find subcontractor with related service requests
        $subcontractor = Subcontractor::with('serviceRequests')->find($id);

        if (!$subcontractor) {
            return response()->json([
                'success' => false,
                'message' => 'Subcontractor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $subcontractor
        ]);
    }

    /**
     * Update the specified subcontractor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Find subcontractor
        $subcontractor = Subcontractor::find($id);

        if (!$subcontractor) {
            return response()->json([
                'success' => false,
                'message' => 'Subcontractor not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:subcontractors,email,' . $id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'nullable|string',
            'services' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update subcontractor
        $subcontractor->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Subcontractor updated successfully',
            'data' => $subcontractor
        ]);
    }

    /**
     * Remove the specified subcontractor from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        // Find subcontractor
        $subcontractor = Subcontractor::find($id);

        if (!$subcontractor) {
            return response()->json([
                'success' => false,
                'message' => 'Subcontractor not found'
            ], 404);
        }

        // Check if subcontractor has related data before deleting
        if ($subcontractor->serviceRequests()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subcontractor with related service requests'
            ], 400);
        }

        // Delete subcontractor
        $subcontractor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcontractor deleted successfully'
        ]);
    }

    /**
     * Update subcontractor active status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        // Find subcontractor
        $subcontractor = Subcontractor::find($id);

        if (!$subcontractor) {
            return response()->json([
                'success' => false,
                'message' => 'Subcontractor not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update status
        $subcontractor->active = $request->active;
        $subcontractor->save();

        return response()->json([
            'success' => true,
            'message' => 'Subcontractor status updated successfully',
            'data' => $subcontractor
        ]);
    }

    /**
     * Get subcontractors by service type.
     *
     * @param  string  $serviceType
     * @return \Illuminate\Http\JsonResponse
     */
    public function byServiceType($serviceType): JsonResponse
    {
        // Find subcontractors that provide this service
        $subcontractors = Subcontractor::where('active', true)
            ->whereJsonContains('services', $serviceType)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcontractors
        ]);
    }

    /**
     * Get subcontractor statistics.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics($id): JsonResponse
    {
        // Find subcontractor
        $subcontractor = Subcontractor::find($id);

        if (!$subcontractor) {
            return response()->json([
                'success' => false,
                'message' => 'Subcontractor not found'
            ], 404);
        }

        // Get statistics
        $serviceRequestCount = $subcontractor->serviceRequests()->count();
        $completedServiceRequests = $subcontractor->serviceRequests()->where('status', 'completed')->count();
        $pendingServiceRequests = $subcontractor->serviceRequests()->where('status', '!=', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'serviceRequestCount' => $serviceRequestCount,
                'completedServiceRequestCount' => $completedServiceRequests,
                'pendingServiceRequestCount' => $pendingServiceRequests
            ]
        ]);
    }
}