<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GovernorateController extends Controller
{
    /**
     * Display a listing of the governorates
     */
    public function index(Request $request): JsonResponse
    {
        $query = Governorate::query()->with(['activeWilayats']);
        
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
        
        $governorates = $query->orderBy('name_en')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $governorates
        ]);
    }

    /**
     * Store a newly created governorate
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name_en' => 'required|string|max:255|unique:governorates',
            'name_ar' => 'required|string|max:255|unique:governorates',
            'code' => 'required|string|max:10|unique:governorates',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ]);

        $governorate = Governorate::create($request->all());
        $governorate->load('activeWilayats');

        return response()->json([
            'status' => 'success',
            'message' => 'Governorate created successfully',
            'data' => $governorate
        ], 201);
    }

    /**
     * Display the specified governorate
     */
    public function show(string $id): JsonResponse
    {
        $governorate = Governorate::with(['wilayats' => function ($query) {
            $query->orderBy('name_en');
        }])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $governorate
        ]);
    }

    /**
     * Update the specified governorate
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $governorate = Governorate::findOrFail($id);
        
        $request->validate([
            'name_en' => 'required|string|max:255|unique:governorates,name_en,' . $id,
            'name_ar' => 'required|string|max:255|unique:governorates,name_ar,' . $id,
            'code' => 'required|string|max:10|unique:governorates,code,' . $id,
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ]);

        $governorate->update($request->all());
        $governorate->load('activeWilayats');

        return response()->json([
            'status' => 'success',
            'message' => 'Governorate updated successfully',
            'data' => $governorate
        ]);
    }

    /**
     * Remove the specified governorate
     */
    public function destroy(string $id): JsonResponse
    {
        $governorate = Governorate::findOrFail($id);
        
        // Check if governorate has wilayats
        if ($governorate->wilayats()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete governorate with existing wilayats'
            ], 400);
        }
        
        $governorate->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Governorate deleted successfully'
        ]);
    }

    /**
     * Get wilayats for a specific governorate
     */
    public function wilayats(string $id): JsonResponse
    {
        $governorate = Governorate::findOrFail($id);
        $wilayats = $governorate->wilayats()->active()->orderBy('name_en')->get();

        return response()->json([
            'status' => 'success',
            'data' => $wilayats
        ]);
    }
}
