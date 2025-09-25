<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of cars.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Car::with('branch');

        // Filter by user role
        if ($user->isBranchManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isPartner() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('branch_id') && ($user->isAdmin() || $user->isBranchManager())) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('available_only') && $request->available_only) {
            $query->where('status', 'available');
        }

        $cars = $query->orderBy('make')->orderBy('model')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $cars
        ]);
    }

    /**
     * Store a newly created car.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isBranchManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'registration' => 'required|string|max:20|unique:cars,registration',
            'color' => 'nullable|string|max:50',
            'daily_rate' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
              'description' => 'nullable|string|max:1000',
              'vin' => 'nullable|string|max:50',
              'features' => 'nullable|string|max:1000',
              'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // If branch manager, assign to their branch
        $branchId = $request->branch_id;
        if ($user->isBranchManager()) {
            $branchId = $user->branch_id;
        }

        $car = Car::create([
            'make' => $request->make,
            'model' => $request->model,
            'year' => $request->year,
            'registration' => $request->registration,
            'color' => $request->color,
            'daily_rate' => $request->daily_rate,
            'branch_id' => $branchId,
                'description' => $request->description,
                'vin' => $request->vin,
                'features' => $request->features,
                'notes' => $request->notes,
            'status' => 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Car created successfully',
            'data' => $car->load('branch')
        ], 201);
    }

    /**
     * Display the specified car.
     */
    public function show(Request $request, string $id)
    {
        $car = Car::with('branch')->findOrFail($id);
        $user = $request->user();

        // Check access permissions
        if (!$this->canAccessCar($user, $car)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $car
        ]);
    }

    /**
     * Update the specified car.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $car = Car::findOrFail($id);

        if (!$user->isAdmin() && !($user->isBranchManager() && $user->branch_id == $car->branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'sometimes|required|string|max:100',
            'model' => 'sometimes|required|string|max:100',
            'year' => 'sometimes|required|integer|min:1990|max:' . (date('Y') + 1),
            'registration' => 'sometimes|required|string|max:20|unique:cars,registration,' . $id,
            'color' => 'sometimes|nullable|string|max:50',
            'daily_rate' => 'sometimes|required|numeric|min:0',
            'branch_id' => 'sometimes|nullable|exists:branches,id',
            'status' => 'sometimes|required|in:available,rented,maintenance',
              'description' => 'sometimes|nullable|string|max:1000',
              'vin' => 'sometimes|nullable|string|max:50',
              'features' => 'sometimes|nullable|string|max:1000',
              'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Restrict branch changes for branch managers
        $updateData = $request->only([
              'make', 'model', 'year', 'registration', 'color',
              'daily_rate', 'status', 'description', 'vin', 'features', 'notes'
        ]);

        if ($user->isAdmin() && $request->has('branch_id')) {
            $updateData['branch_id'] = $request->branch_id;
        }

        $car->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Car updated successfully',
            'data' => $car->load('branch')
        ]);
    }

    /**
     * Remove the specified car.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can delete cars'
            ], 403);
        }

        $car = Car::findOrFail($id);
        
        // Check if car is currently rented
        if ($car->status === 'rented') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a rented car'
            ], 422);
        }

        $car->delete();

        return response()->json([
            'success' => true,
            'message' => 'Car deleted successfully'
        ]);
    }

    /**
     * Get available cars for rental
     */
    public function available(Request $request)
    {
        $query = Car::with('branch')->where('status', 'available');

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('city')) {
            $query->whereHas('branch', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        $cars = $query->orderBy('daily_rate')->get();

        return response()->json([
            'success' => true,
            'data' => $cars
        ]);
    }

    /**
     * Update car status
     */
    public function updateStatus(Request $request, string $id)
    {
        $user = $request->user();
        $car = Car::findOrFail($id);

        if (!$this->canEditCar($user, $car)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,rented,maintenance'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
                'errors' => $validator->errors()
            ], 422);
        }

        $car->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Car status updated successfully',
            'data' => $car
        ]);
    }

    /**
     * Check if user can access car information
     */
    private function canAccessCar($user, $car)
    {
        if ($user->isAdmin() || $user->isCustomer()) {
            return true;
        }
        
        if (($user->isBranchManager() || $user->isPartner()) && $user->branch_id == $car->branch_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can edit car
     */
    private function canEditCar($user, $car)
    {
        if ($user->isAdmin()) {
            return true;
        }
        
        if ($user->isBranchManager() && $user->branch_id == $car->branch_id) {
            return true;
        }
        
        return false;
    }
}
