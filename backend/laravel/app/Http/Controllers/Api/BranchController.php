<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        // Filter active branches only for non-admin users
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            $query->where('active', true);
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        $branches = $query->orderBy('city')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $branches
        ]);
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can create branches'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $branch = Branch::create([
            'name' => $request->name,
            'city' => $request->city,
            'address' => $request->address,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'data' => $branch
        ], 201);
    }

    /**
     * Display the specified branch.
     */
    public function show(string $id)
    {
        $branch = Branch::with(['cars', 'users'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $branch
        ]);
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can update branches'
            ], 403);
        }

        $branch = Branch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'address' => 'sometimes|required|string|max:500',
            'contact_email' => 'sometimes|required|email|max:255',
            'contact_phone' => 'sometimes|required|string|max:20',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $branch->update($request->only([
            'name', 'city', 'address', 'contact_email', 'contact_phone',
            'latitude', 'longitude', 'active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully',
            'data' => $branch
        ]);
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can delete branches'
            ], 403);
        }

        $branch = Branch::findOrFail($id);

        // Check if branch has associated cars or users
        if ($branch->cars()->count() > 0 || $branch->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete branch with associated cars or users'
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully'
        ]);
    }

    /**
     * Get branches by city
     */
    public function byCity(Request $request, string $city)
    {
        $branches = Branch::where('city', $city)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $branches
        ]);
    }

    /**
     * Get branch statistics
     */
    public function statistics(Request $request, string $id)
    {
        $user = $request->user();
        $branch = Branch::findOrFail($id);

        // Check access - admin can view all, branch managers can only view their own branch
        if (!$user->isAdmin() && $user->branch_id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Basic statistics
        $stats = [
            'service_requests_count' => $branch->serviceRequests()->count(),
            'cars_count' => $branch->cars()->count(),
            'active_rentals_count' => $branch->serviceRequests()->whereIn('status', ['assigned', 'confirmed', 'picked_up'])->count(),
            'staff_count' => $branch->users()->count(),
        ];

        // Additional detailed statistics for detailed view
        $detailedStats = [
            'service_requests_count' => $branch->serviceRequests()->count(),
            'pending_requests' => $branch->serviceRequests()->where('status', 'pending')->count(),
            'confirmed_requests' => $branch->serviceRequests()->where('status', 'confirmed')->count(),
            'completed_requests' => $branch->serviceRequests()->where('status', 'completed')->count(),
            'cars_count' => $branch->cars()->count(),
            'available_cars' => $branch->cars()->where('status', 'available')->count(),
            'rented_cars' => $branch->cars()->where('status', 'rented')->count(),
            'maintenance_cars' => $branch->cars()->where('status', 'maintenance')->count(),
            'active_rentals_count' => $branch->serviceRequests()->whereIn('status', ['assigned', 'confirmed', 'picked_up'])->count(),
            'staff_count' => $branch->users()->count(),
            'admin_count' => $branch->users()->where('role', 'admin')->count(),
            'manager_count' => $branch->users()->where('role', 'branch_manager')->count(),
            'partner_count' => $branch->users()->where('role', 'partner')->count(),
            'monthly_revenue' => '0.00', // Placeholder - would calculate from invoices
            'yearly_revenue' => '0.00',  // Placeholder - would calculate from invoices
            'total_revenue' => '0.00',   // Placeholder - would calculate from invoices
        ];

        return response()->json([
            'success' => true,
            'data' => $detailedStats
        ]);
    }
}
