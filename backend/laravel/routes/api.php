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
use App\Http\Controllers\Api\GovernorateController;
use App\Http\Controllers\Api\WilayatController;
use App\Http\Controllers\Api\WilayatBranchAssignmentController;

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

// Public governorate and wilayat information
Route::get('/governorates', [GovernorateController::class, 'index']);
Route::get('/governorates/{id}', [GovernorateController::class, 'show']);
Route::get('/governorates/{id}/wilayats', [GovernorateController::class, 'wilayats']);
Route::get('/wilayats', [WilayatController::class, 'index']);
Route::get('/wilayats/{id}', [WilayatController::class, 'show']);
Route::get('/wilayats/governorate/{governorateId}', [WilayatController::class, 'byGovernorate']);
Route::get('/wilayats/{id}/assigned-branch', [WilayatController::class, 'assignedBranch']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Car expenses and revenue
    Route::get('/car-expenses', [\App\Http\Controllers\Api\CarExpenseController::class, 'index']);
    Route::post('/car-expenses', [\App\Http\Controllers\Api\CarExpenseController::class, 'store']);
    Route::get('/car-expenses/revenue', [\App\Http\Controllers\Api\CarExpenseController::class, 'carRevenue']);
    Route::get('/car-expenses/total-revenue', [\App\Http\Controllers\Api\CarExpenseController::class, 'totalRevenue']);
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Service Request routes
    Route::apiResource('service-requests', ServiceRequestController::class);
    Route::post('/service-requests/{id}/assign', [ServiceRequestController::class, 'assignToPartner']);
    Route::post('/service-requests/{id}/assign-subcontractor', [ServiceRequestController::class, 'assignToSubcontractor']);
    
    // Service Request Workflow routes
    Route::post('/service-requests/{id}/review', [ServiceRequestController::class, 'reviewServiceRequest']);
    Route::post('/service-requests/{id}/assign-to-branch', [ServiceRequestController::class, 'assignServiceRequest']);
    Route::post('/service-requests/{id}/auto-assign', [ServiceRequestController::class, 'autoAssignServiceRequest']);
    Route::get('/service-requests/{id}/branch-recommendations', [ServiceRequestController::class, 'getBranchRecommendations']);
    Route::post('/service-requests/{id}/allocate-car', [ServiceRequestController::class, 'allocateCar']);
    Route::get('/service-requests/{id}/available-cars', [ServiceRequestController::class, 'getAvailableCars']);
    Route::post('/service-requests/bulk-allocate-cars', [ServiceRequestController::class, 'bulkAllocateCars']);
    Route::post('/service-requests/{id}/notify-customer', [ServiceRequestController::class, 'notifyCustomer']);
    Route::post('/service-requests/{id}/send-return-reminder', [ServiceRequestController::class, 'sendReturnReminder']);
    Route::post('/service-requests/bulk-return-reminders', [ServiceRequestController::class, 'sendBulkReturnReminders']);
    Route::post('/service-requests/{id}/deliver-car', [ServiceRequestController::class, 'deliverCar']);
    Route::post('/service-requests/{id}/return-car', [ServiceRequestController::class, 'returnCar']);
    Route::post('/service-requests/{id}/process-car-return', [ServiceRequestController::class, 'processCarReturn']);
    Route::post('/service-requests/{id}/process-payment', [ServiceRequestController::class, 'processPayment']);
    Route::get('/service-requests/{id}/invoice', [ServiceRequestController::class, 'getServiceRequestInvoice']);
    Route::post('/service-requests/{id}/regenerate-invoice', [ServiceRequestController::class, 'regenerateInvoice']);
    Route::get('/service-requests/{id}/payment-receipt', [ServiceRequestController::class, 'generatePaymentReceipt']);
    Route::post('/service-requests/{id}/cancel', [ServiceRequestController::class, 'cancelServiceRequest']);
    Route::get('/service-requests/pending/reviews', [ServiceRequestController::class, 'getPendingReviews']);
    Route::get('/service-requests/workflow/stats', [ServiceRequestController::class, 'getWorkflowStats']);

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
    
    // Governorate and Wilayat management routes (admin only)
    Route::apiResource('governorates', GovernorateController::class)->except(['index', 'show']);
    Route::apiResource('wilayats', WilayatController::class)->except(['index', 'show']);
    
    // Wilayat-Branch assignment routes (admin only)
    Route::apiResource('wilayat-branch-assignments', WilayatBranchAssignmentController::class);
    Route::post('/wilayat-branch-assignments/bulk-assign', [WilayatBranchAssignmentController::class, 'bulkAssign']);
    Route::get('/wilayat-branch-assignments/unassigned/wilayats', [WilayatBranchAssignmentController::class, 'unassigned']);
});
