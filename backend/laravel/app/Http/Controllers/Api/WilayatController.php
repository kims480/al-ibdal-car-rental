<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilayat;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WilayatController extends Controller
{
    /**
     * Display a listing of the wilayats
     */
    public function index(Request $request): JsonResponse
    {
        $query = Wilayat::query()->with(['governorate']);
        
        // Filter by governorate
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }
        
        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        
        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        $wilayats = $query->orderBy('name_en')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $wilayats
        ]);
    }

    /**
     * Store a newly created wilayat
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ]);

        // Check uniqueness within governorate
        $existingWilayat = Wilayat::where('governorate_id', $request->governorate_id)
            ->where(function ($query) use ($request) {
                $query->where('name_en', $request->name_en)
                      ->orWhere('name_ar', $request->name_ar)
                      ->orWhere('code', $request->code);
            })->first();

        if ($existingWilayat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wilayat with this name or code already exists in the governorate'
            ], 400);
        }

        $wilayat = Wilayat::create($request->all());
        $wilayat->load('governorate');

        return response()->json([
            'status' => 'success',
            'message' => 'Wilayat created successfully',
            'data' => $wilayat
        ], 201);
    }

    /**
     * Display the specified wilayat
     */
    public function show(string $id): JsonResponse
    {
        $wilayat = Wilayat::with(['governorate', 'branchAssignments.branch'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $wilayat
        ]);
    }

    /**
     * Update the specified wilayat
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $wilayat = Wilayat::findOrFail($id);
        
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ]);

        // Check uniqueness within governorate (excluding current record)
        $existingWilayat = Wilayat::where('governorate_id', $request->governorate_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->where('name_en', $request->name_en)
                      ->orWhere('name_ar', $request->name_ar)
                      ->orWhere('code', $request->code);
            })->first();

        if ($existingWilayat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wilayat with this name or code already exists in the governorate'
            ], 400);
        }

        $wilayat->update($request->all());
        $wilayat->load('governorate');

        return response()->json([
            'status' => 'success',
            'message' => 'Wilayat updated successfully',
            'data' => $wilayat
        ]);
    }

    /**
     * Remove the specified wilayat
     */
    public function destroy(string $id): JsonResponse
    {
        $wilayat = Wilayat::findOrFail($id);
        
        // Check if wilayat has service requests
        if ($wilayat->serviceRequests()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete wilayat with existing service requests'
            ], 400);
        }
        
        $wilayat->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Wilayat deleted successfully'
        ]);
    }

    /**
     * Get wilayats by governorate
     */
    public function byGovernorate(string $governorateId): JsonResponse
    {
        $wilayats = Wilayat::where('governorate_id', $governorateId)
            ->active()
            ->orderBy('name_en')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $wilayats
        ]);
    }

    /**
     * Get assigned branch for a wilayat
     */
    public function assignedBranch(string $id): JsonResponse
    {
        $wilayat = Wilayat::findOrFail($id);
        $branch = $wilayat->assignedBranch();

        return response()->json([
            'status' => 'success',
            'data' => $branch
        ]);
    }
}
