<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WilayatBranchAssignment;
use App\Models\Wilayat;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WilayatBranchAssignmentController extends Controller
{
    /**
     * Display a listing of wilayat branch assignments
     */
    public function index(Request $request): JsonResponse
    {
        $query = WilayatBranchAssignment::with(['wilayat.governorate', 'branch']);
        
        // Filter by wilayat
        if ($request->filled('wilayat_id')) {
            $query->where('wilayat_id', $request->wilayat_id);
        }
        
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        
        // Filter by primary assignments only
        if ($request->has('primary_only')) {
            $query->where('is_primary', true);
        }
        
        $assignments = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $assignments
        ]);
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'wilayat_id' => 'required|exists:wilayats,id',
            'branch_id' => 'required|exists:branches,id',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // If this is being set as primary, remove primary status from other assignments
            if ($request->boolean('is_primary')) {
                WilayatBranchAssignment::where('wilayat_id', $request->wilayat_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $assignment = WilayatBranchAssignment::create($request->all());
            $assignment->load(['wilayat.governorate', 'branch']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Assignment created successfully',
                'data' => $assignment
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified assignment
     */
    public function show(string $id): JsonResponse
    {
        $assignment = WilayatBranchAssignment::with(['wilayat.governorate', 'branch'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $assignment
        ]);
    }

    /**
     * Update the specified assignment
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $assignment = WilayatBranchAssignment::findOrFail($id);
        
        $request->validate([
            'wilayat_id' => 'required|exists:wilayats,id',
            'branch_id' => 'required|exists:branches,id',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // If this is being set as primary, remove primary status from other assignments
            if ($request->boolean('is_primary') && !$assignment->is_primary) {
                WilayatBranchAssignment::where('wilayat_id', $request->wilayat_id)
                    ->where('id', '!=', $id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $assignment->update($request->all());
            $assignment->load(['wilayat.governorate', 'branch']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Assignment updated successfully',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(string $id): JsonResponse
    {
        $assignment = WilayatBranchAssignment::findOrFail($id);
        $assignment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment deleted successfully'
        ]);
    }

    /**
     * Bulk assign wilayats to a branch
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'wilayat_ids' => 'required|array',
            'wilayat_ids.*' => 'exists:wilayats,id',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $assignments = [];
            foreach ($request->wilayat_ids as $wilayatId) {
                // If setting as primary, remove existing primary assignments
                if ($request->boolean('is_primary')) {
                    WilayatBranchAssignment::where('wilayat_id', $wilayatId)
                        ->where('is_primary', true)
                        ->update(['is_primary' => false]);
                }

                // Create or update assignment
                $assignment = WilayatBranchAssignment::updateOrCreate(
                    [
                        'wilayat_id' => $wilayatId,
                        'branch_id' => $request->branch_id
                    ],
                    [
                        'is_active' => true,
                        'is_primary' => $request->boolean('is_primary'),
                        'notes' => $request->notes
                    ]
                );
                
                $assignment->load(['wilayat.governorate', 'branch']);
                $assignments[] = $assignment;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk assignment completed successfully',
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create bulk assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unassigned wilayats
     */
    public function unassigned(): JsonResponse
    {
        $unassignedWilayats = Wilayat::whereDoesntHave('branchAssignments', function ($query) {
            $query->where('is_active', true)->where('is_primary', true);
        })
        ->with('governorate')
        ->active()
        ->orderBy('name_en')
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $unassignedWilayats
        ]);
    }
}
