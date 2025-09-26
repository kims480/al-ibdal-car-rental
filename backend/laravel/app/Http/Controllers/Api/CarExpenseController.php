<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarExpense;
use App\Models\Car;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarExpenseController extends Controller
{
    // Add a new expense for a car
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'expense_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $expense = CarExpense::create($request->all());
        return response()->json([
            'success' => true,
            'data' => $expense
        ], 201);
    }

    // Get all expenses for a car in a month
    public function index(Request $request)
    {
        $carId = $request->query('car_id');
        $month = $request->query('month'); // format YYYY-MM
        $query = CarExpense::query();
        if ($carId) {
            $query->where('car_id', $carId);
        }
        if ($month) {
            $query->whereRaw('DATE_FORMAT(expense_date, "%Y-%m") = ?', [$month]);
        }
        $expenses = $query->orderBy('expense_date')->get();
        return response()->json([
            'success' => true,
            'data' => $expenses
        ]);
    }

    // Get car revenue for a month
    public function carRevenue(Request $request)
    {
        $carId = $request->query('car_id');
        $month = $request->query('month'); // format YYYY-MM
        $revenue = 0;
        if ($carId && $month) {
            $revenue = DB::table('rentals')
                ->where('car_id', $carId)
                ->whereRaw('DATE_FORMAT(start_date, "%Y-%m") = ?', [$month])
                ->sum('amount');
        }
        return response()->json([
            'success' => true,
            'car_id' => $carId,
            'month' => $month,
            'revenue' => $revenue
        ]);
    }

    // Get total cars revenue for a month
    public function totalRevenue(Request $request)
    {
        $month = $request->query('month'); // format YYYY-MM
        $revenue = 0;
        if ($month) {
            $revenue = DB::table('rentals')
                ->whereRaw('DATE_FORMAT(start_date, "%Y-%m") = ?', [$month])
                ->sum('amount');
        }
        return response()->json([
            'success' => true,
            'month' => $month,
            'revenue' => $revenue
        ]);
    }
}
