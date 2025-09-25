<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RentalController extends Controller
{
    /**
     * Display a listing of rentals.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Rental::with(['user', 'car.branch']);

        // Filter by user role
        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isBranchManager()) {
            $query->whereHas('car', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch if provided and user is admin
        if ($request->has('branch_id') && $user->isAdmin()) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $rentals = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $rentals
        ]);
    }

    /**
     * Store a newly created rental.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'pickup_location' => 'required|string|max:255',
            'return_location' => 'required|string|max:255',
            'special_requests' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if car is available
        $car = Car::findOrFail($request->car_id);
        
        if ($car->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Car is not available for rental'
            ], 400);
        }

        // Check for existing rentals in the same period
        $existingRental = Rental::where('car_id', $request->car_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->exists();

        if ($existingRental) {
            return response()->json([
                'success' => false,
                'message' => 'Car is already booked for the selected dates'
            ], 400);
        }

        // Calculate total amount
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;
        $totalAmount = $days * $car->daily_rate;

        $rentalData = $request->all();
        $rentalData['user_id'] = $user->id;
        $rentalData['status'] = 'pending';
        $rentalData['total_amount'] = $totalAmount;
        $rentalData['rental_days'] = $days;

        $rental = Rental::create($rentalData);

        // Update car status
        $car->update(['status' => 'rented']);

        return response()->json([
            'success' => true,
            'message' => 'Rental created successfully',
            'data' => $rental->load(['user', 'car.branch'])
        ], 201);
    }

    /**
     * Display the specified rental.
     */
    public function show(string $id, Request $request)
    {
        $user = $request->user();
        
        $rental = Rental::with(['user', 'car.branch'])->findOrFail($id);

        // Check authorization
        if ($user->isCustomer() && $rental->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        if ($user->isBranchManager() && $rental->car->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $rental
        ]);
    }

    /**
     * Update the specified rental.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        
        $rental = Rental::findOrFail($id);

        // Check authorization
        if ($user->isCustomer() && $rental->user_id !== $user->id && $rental->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify confirmed rental'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'pickup_location' => 'sometimes|required|string|max:255',
            'return_location' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:pending,confirmed,active,completed,cancelled',
            'special_requests' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['start_date', 'end_date', 'pickup_location', 'return_location', 'special_requests']);

        // Only staff can change status
        if (($user->isAdmin() || $user->isBranchManager()) && $request->has('status')) {
            $updateData['status'] = $request->status;

            // Update car status based on rental status
            if ($request->status === 'completed' || $request->status === 'cancelled') {
                $rental->car->update(['status' => 'available']);
            }
        }

        // Recalculate total if dates changed
        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date ?? $rental->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date ?? $rental->end_date);
            $days = $startDate->diffInDays($endDate) + 1;
            $updateData['total_amount'] = $days * $rental->car->daily_rate;
            $updateData['rental_days'] = $days;
        }

        $rental->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Rental updated successfully',
            'data' => $rental->load(['user', 'car.branch'])
        ]);
    }

    /**
     * Remove the specified rental.
     */
    public function destroy(string $id, Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $rental = Rental::findOrFail($id);
        
        // Free up the car if rental is cancelled
        if (in_array($rental->status, ['pending', 'confirmed', 'active'])) {
            $rental->car->update(['status' => 'available']);
        }

        $rental->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rental deleted successfully'
        ]);
    }

    /**
     * Get rental statistics.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isBranchManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = Rental::query();

        if ($user->isBranchManager()) {
            $query->whereHas('car', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        $totalRentals = $query->count();
        $activeRentals = (clone $query)->where('status', 'active')->count();
        $completedRentals = (clone $query)->where('status', 'completed')->count();
        $totalRevenue = (clone $query)->where('status', 'completed')->sum('total_amount');

        $monthlyRentals = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_rentals' => $totalRentals,
                'active_rentals' => $activeRentals,
                'completed_rentals' => $completedRentals,
                'total_revenue' => $totalRevenue,
                'monthly_rentals' => $monthlyRentals
            ]
        ]);
    }
}
