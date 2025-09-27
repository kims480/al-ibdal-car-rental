<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Car;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RentalController extends Controller
{
    /**
     * Display a listing of rentals.
     */
    public function index(Request $request)
    {
        $query = Rental::with(['customer', 'car.branch']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer if provided
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by car if provided
        if ($request->has('car_id')) {
            $query->where('car_id', $request->car_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('pickup_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('return_date', '<=', $request->end_date);
        }

        $rentals = $query->orderBy('created_at', 'desc')->get();

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
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:pickup_date',
            'rental_type' => 'required|in:daily,weekly,monthly',
            'security_deposit' => 'nullable|numeric|min:0',
            'insurance_included' => 'boolean',
            'notes' => 'nullable|string|max:1000'
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
                $query->whereBetween('pickup_date', [$request->pickup_date, $request->return_date])
                      ->orWhereBetween('return_date', [$request->pickup_date, $request->return_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('pickup_date', '<=', $request->pickup_date)
                            ->where('return_date', '>=', $request->return_date);
                      });
            })
            ->whereIn('status', ['active'])
            ->exists();

        if ($existingRental) {
            return response()->json([
                'success' => false,
                'message' => 'Car is already booked for the selected dates'
            ], 400);
        }

        // Calculate total amount based on rental type
        $startDate = Carbon::parse($request->pickup_date);
        $endDate = Carbon::parse($request->return_date);
        
        $totalAmount = $this->calculateRentalAmount($car, $startDate, $endDate, $request->rental_type);

        $rental = Rental::create([
            'customer_id' => $request->customer_id,
            'car_id' => $request->car_id,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'total_amount' => $totalAmount,
            'security_deposit' => $request->security_deposit ?? 0,
            'rental_type' => $request->rental_type,
            'insurance_included' => $request->insurance_included ?? false,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        // Update car status
        $car->update(['status' => 'rented']);

        return response()->json([
            'success' => true,
            'message' => 'Rental created successfully',
            'data' => $rental->load(['customer', 'car.branch'])
        ], 201);
    }

    /**
     * Display the specified rental.
     */
    public function show(string $id)
    {
        $rental = Rental::with(['customer', 'car.branch'])->findOrFail($id);

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
        $rental = Rental::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'pickup_date' => 'sometimes|required|date',
            'return_date' => 'sometimes|required|date|after:pickup_date',
            'actual_pickup_date' => 'nullable|date',
            'actual_return_date' => 'nullable|date',
            'status' => 'sometimes|required|in:active,completed,cancelled',
            'additional_charges' => 'nullable|numeric|min:0',
            'pickup_notes' => 'nullable|string|max:1000',
            'return_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'pickup_date', 'return_date', 'actual_pickup_date', 'actual_return_date',
            'status', 'additional_charges', 'pickup_notes', 'return_notes', 'notes'
        ]);

        // Update car status based on rental status
        if ($request->has('status')) {
            if ($request->status === 'completed' || $request->status === 'cancelled') {
                $rental->car->update(['status' => 'available']);
            } elseif ($request->status === 'active') {
                $rental->car->update(['status' => 'rented']);
            }
        }

        // Recalculate total if dates changed
        if ($request->has('pickup_date') || $request->has('return_date')) {
            $startDate = Carbon::parse($request->pickup_date ?? $rental->pickup_date);
            $endDate = Carbon::parse($request->return_date ?? $rental->return_date);
            $updateData['total_amount'] = $this->calculateRentalAmount(
                $rental->car, 
                $startDate, 
                $endDate, 
                $rental->rental_type
            );
        }

        $rental->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Rental updated successfully',
            'data' => $rental->load(['customer', 'car.branch'])
        ]);
    }

    /**
     * Remove the specified rental.
     */
    public function destroy(string $id)
    {
        $rental = Rental::findOrFail($id);
        
        // Free up the car if rental is active
        if ($rental->status === 'active') {
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
    public function statistics()
    {
        $totalRentals = Rental::count();
        $activeRentals = Rental::where('status', 'active')->count();
        $completedRentals = Rental::where('status', 'completed')->count();
        $totalRevenue = Rental::where('status', 'completed')
            ->selectRaw('SUM(total_amount + additional_charges) as total')
            ->first()->total ?? 0;

        $monthlyRentals = Rental::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $overdueRentals = Rental::where('status', 'active')
            ->where('return_date', '<', Carbon::today())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_rentals' => $totalRentals,
                'active_rentals' => $activeRentals,
                'completed_rentals' => $completedRentals,
                'overdue_rentals' => $overdueRentals,
                'total_revenue' => $totalRevenue,
                'monthly_rentals' => $monthlyRentals
            ]
        ]);
    }

    /**
     * Get rentals by customer.
     */
    public function byCustomer(string $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $rentals = $customer->rentals()->with(['car.branch'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'rentals' => $rentals
            ]
        ]);
    }

    /**
     * Calculate rental amount based on type and duration.
     */
    private function calculateRentalAmount($car, $startDate, $endDate, $rentalType)
    {
        $days = $startDate->diffInDays($endDate) + 1;

        return match($rentalType) {
            'weekly' => ceil($days / 7) * ($car->daily_rate * 6), // 1 day discount for weekly
            'monthly' => ceil($days / 30) * ($car->daily_rate * 25), // 5 days discount for monthly
            default => $days * $car->daily_rate
        };
    }
}
