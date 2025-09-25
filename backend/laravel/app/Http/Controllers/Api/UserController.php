<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = User::query();

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by branch if provided
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        $users = $query->with('branch')->orderBy('name')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,branch_manager,partner,customer',
            'branch_id' => 'required_if:role,branch_manager|nullable|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);
        
        $newUser = User::create($userData);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $newUser->load('branch')
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id, Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $user->id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $targetUser = User::with('branch')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $targetUser
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $user->id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $targetUser = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'phone' => 'sometimes|required|string|max:20',
            'role' => 'sometimes|required|in:admin,branch_manager,partner,customer',
            'branch_id' => 'required_if:role,branch_manager|nullable|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->all();
        
        // Hash password if provided
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Only admins can change roles
        if (!$user->isAdmin() && $request->has('role')) {
            unset($updateData['role']);
        }

        $targetUser->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $targetUser->load('branch')
        ]);
    }

    /**
     * Remove the specified user.
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

        if ($user->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        $targetUser = User::findOrFail($id);
        $targetUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Update user status (activate/deactivate).
     */
    public function updateStatus(Request $request, string $id)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $targetUser = User::findOrFail($id);
        $targetUser->update(['active' => $request->active]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => $targetUser
        ]);
    }
}