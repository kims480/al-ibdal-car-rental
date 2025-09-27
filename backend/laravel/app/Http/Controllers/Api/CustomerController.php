<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index()
    {
        try {
            $customers = Customer::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified customer
     */
    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'city' => 'required|string|max:100',
            'civilId' => 'required|string|max:50',
            'licenseId' => 'required|string|max:50',
            'civilIdFront' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'civilIdBack' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'licenseFront' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'licenseBack' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store images
        $civilIdFrontPath = $request->file('civilIdFront')->store('customers/civil_id/front', 'public');
        $civilIdBackPath = $request->file('civilIdBack')->store('customers/civil_id/back', 'public');
        $licenseFrontPath = $request->file('licenseFront')->store('customers/license/front', 'public');
        $licenseBackPath = $request->file('licenseBack')->store('customers/license/back', 'public');

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'city' => $request->city,
            'civil_id' => $request->civilId,
            'license_id' => $request->licenseId,
            'civil_id_front' => $civilIdFrontPath,
            'civil_id_back' => $civilIdBackPath,
            'license_front' => $licenseFrontPath,
            'license_back' => $licenseBackPath,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer added successfully',
            'data' => $customer
        ], 201);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'city' => 'required|string|max:100',
                'civilId' => 'required|string|max:50',
                'licenseId' => 'required|string|max:50',
                'civilIdFront' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'civilIdBack' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'licenseFront' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'licenseBack' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update basic information
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'city' => $request->city,
                'civil_id' => $request->civilId,
                'license_id' => $request->licenseId,
            ];

            // Handle file uploads - only update if new files provided
            if ($request->hasFile('civilIdFront')) {
                // Delete old file if exists
                if ($customer->civil_id_front) {
                    Storage::disk('public')->delete($customer->civil_id_front);
                }
                $updateData['civil_id_front'] = $request->file('civilIdFront')->store('customers/civil_id/front', 'public');
            }

            if ($request->hasFile('civilIdBack')) {
                // Delete old file if exists
                if ($customer->civil_id_back) {
                    Storage::disk('public')->delete($customer->civil_id_back);
                }
                $updateData['civil_id_back'] = $request->file('civilIdBack')->store('customers/civil_id/back', 'public');
            }

            if ($request->hasFile('licenseFront')) {
                // Delete old file if exists
                if ($customer->license_front) {
                    Storage::disk('public')->delete($customer->license_front);
                }
                $updateData['license_front'] = $request->file('licenseFront')->store('customers/license/front', 'public');
            }

            if ($request->hasFile('licenseBack')) {
                // Delete old file if exists
                if ($customer->license_back) {
                    Storage::disk('public')->delete($customer->license_back);
                }
                $updateData['license_back'] = $request->file('licenseBack')->store('customers/license/back', 'public');
            }

            $customer->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $customer->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
