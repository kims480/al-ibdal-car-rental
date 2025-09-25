<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{
    /**
     * Display a listing of the partners.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all partners with their related branch
        $partners = Partner::with('branch')->get();
        
        return response()->json([
            'success' => true,
            'data' => $partners
        ]);
    }

    /**
     * Store a newly created partner in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:partners,email',
            'phone' => 'required|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'nullable|in:partner,staff',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create partner
        $partner = Partner::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Partner created successfully',
            'data' => $partner
        ], 201);
    }

    /**
     * Display the specified partner.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Find partner with related branch and contracts
        $partner = Partner::with(['branch', 'contracts'])->find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Partner not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $partner
        ]);
    }

    /**
     * Update the specified partner in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Find partner
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Partner not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:partners,email,' . $id,
            'phone' => 'sometimes|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'nullable|in:partner,staff',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update partner
        $partner->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Partner updated successfully',
            'data' => $partner
        ]);
    }

    /**
     * Remove the specified partner from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        // Find partner
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Partner not found'
            ], 404);
        }

        // Check if partner has related data before deleting
        if ($partner->contracts()->count() > 0 || $partner->serviceRequests()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete partner with related data'
            ], 400);
        }

        // Delete partner
        $partner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partner deleted successfully'
        ]);
    }

    /**
     * Update partner active status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        // Find partner
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Partner not found'
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
        $partner->active = $request->active;
        $partner->save();

        return response()->json([
            'success' => true,
            'message' => 'Partner status updated successfully',
            'data' => $partner
        ]);
    }

    /**
     * Get partner statistics.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics($id): JsonResponse
    {
        // Find partner
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Partner not found'
            ], 404);
        }

        // Get statistics
        $contractCount = $partner->contracts()->count();
        $serviceRequestCount = $partner->serviceRequests()->count();
        $activeServiceRequests = $partner->serviceRequests()->where('status', '!=', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'contractCount' => $contractCount,
                'serviceRequestCount' => $serviceRequestCount,
                'activeServiceRequestCount' => $activeServiceRequests
            ]
        ]);
    }
}