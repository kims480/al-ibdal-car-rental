<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\SubcontractorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public branch and car information
Route::get('/branches', [BranchController::class, 'index']);
Route::get('/branches/{id}', [BranchController::class, 'show']);
Route::get('/branches/city/{city}', [BranchController::class, 'byCity']);
Route::get('/cars/available', [CarController::class, 'available']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Service Request routes
    Route::apiResource('service-requests', ServiceRequestController::class);
    Route::post('/service-requests/{id}/assign', [ServiceRequestController::class, 'assignToPartner']);
    Route::post('/service-requests/{id}/assign-subcontractor', [ServiceRequestController::class, 'assignToSubcontractor']);

    // Car routes
    Route::apiResource('cars', CarController::class);
    Route::patch('/cars/{id}/status', [CarController::class, 'updateStatus']);

    // Branch routes (authenticated users can view, admin can manage)
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{id}', [BranchController::class, 'update']);
    Route::delete('/branches/{id}', [BranchController::class, 'destroy']);
    Route::get('/branches/{id}/statistics', [BranchController::class, 'statistics']);

    // Rental routes
    Route::apiResource('rentals', RentalController::class);
    Route::get('/rentals/statistics', [RentalController::class, 'statistics']);

    // User routes (admin only)
    Route::apiResource('users', UserController::class);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);

    // Invoice routes
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf']);
    Route::post('/invoices/{id}/email', [InvoiceController::class, 'emailPdf']);

    // Customer routes
    Route::post('/customers', [\App\Http\Controllers\Api\CustomerController::class, 'store']);

    // Contract routes
    Route::apiResource('contracts', ContractController::class);
    Route::get('/contracts/{id}/pdf', [ContractController::class, 'downloadPdf']);
    Route::post('/contracts/{id}/email', [ContractController::class, 'emailPdf']);
    
    // Partner routes
    Route::apiResource('partners', PartnerController::class);
    Route::patch('/partners/{id}/status', [PartnerController::class, 'updateStatus']);
    Route::get('/partners/{id}/statistics', [PartnerController::class, 'statistics']);
    
    // Subcontractor routes
    Route::apiResource('subcontractors', SubcontractorController::class);
    Route::patch('/subcontractors/{id}/status', [SubcontractorController::class, 'updateStatus']);
    Route::get('/subcontractors/{id}/statistics', [SubcontractorController::class, 'statistics']);
    Route::get('/subcontractors/service/{serviceType}', [SubcontractorController::class, 'byServiceType']);
});
