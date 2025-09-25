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
}
