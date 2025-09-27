<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Rental;
use App\Models\ServiceRequest;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats()
    {
        try {
            // Get total cars
            $totalCars = Car::count();
            
            // Get cars by status
            $availableCars = Car::where('status', 'available')->count();
            $rentedCars = Car::where('status', 'rented')->count();
            $maintenanceCars = Car::where('status', 'maintenance')->count();
            
            // Get active rentals (ongoing)
            $activeRentals = Rental::where('status', 'active')->count();
            
            // Get pending service requests
            $pendingServices = ServiceRequest::where('status', 'submitted')->count();
            
            // Get completed service requests
            $completedServices = ServiceRequest::where('status', 'completed')->count();
            
            // Get total service requests
            $totalServiceRequests = ServiceRequest::count();
            
            // Get invoice statistics
            $totalInvoices = Invoice::count();
            $pendingInvoices = Invoice::where('status', 'pending')->count();
            $sentInvoices = Invoice::where('status', 'sent')->count();
            $paidInvoices = Invoice::where('status', 'paid')->count();
            
            // Calculate monthly revenue (current month)
            $currentMonth = Carbon::now()->startOfMonth();
            $monthlyRevenue = Invoice::where('status', 'paid')
                ->where('created_at', '>=', $currentMonth)
                ->sum('total_amount');
                
            // Calculate total revenue
            $totalRevenue = Invoice::where('status', 'paid')
                ->sum('total_amount');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalCars' => $totalCars,
                    'availableCars' => $availableCars,
                    'rentedCars' => $rentedCars,
                    'maintenanceCars' => $maintenanceCars,
                    'activeRentals' => $activeRentals,
                    'pendingServices' => $pendingServices,
                    'completedServices' => $completedServices,
                    'totalServiceRequests' => $totalServiceRequests,
                    'totalInvoices' => $totalInvoices,
                    'pendingInvoices' => $pendingInvoices,
                    'sentInvoices' => $sentInvoices,
                    'paidInvoices' => $paidInvoices,
                    'monthlyRevenue' => floatval($monthlyRevenue),
                    'totalRevenue' => floatval($totalRevenue)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activity
     */
    public function recentActivity()
    {
        try {
            $activities = collect();

            // Recent rentals
            $recentRentals = Rental::with(['customer', 'car'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            foreach ($recentRentals as $rental) {
                $activities->push([
                    'id' => 'rental_' . $rental->id,
                    'type' => 'rental',
                    'title' => 'New rental: ' . ($rental->customer ? $rental->customer->name : 'Customer') . ' - ' . ($rental->car ? $rental->car->make . ' ' . $rental->car->model : 'Car'),
                    'created_at' => $rental->created_at,
                    'entity_id' => $rental->id,
                    'entity_type' => 'rental'
                ]);
            }

            // Recent service requests
            $recentServices = ServiceRequest::orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            foreach ($recentServices as $service) {
                $activities->push([
                    'id' => 'service_' . $service->id,
                    'type' => 'service',
                    'title' => 'Service request: ' . $service->customer_name . ' - ' . ucfirst($service->service_type),
                    'created_at' => $service->created_at,
                    'entity_id' => $service->id,
                    'entity_type' => 'service_request'
                ]);
            }

            // Recent invoices
            $recentInvoices = Invoice::with(['invoiceable'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            foreach ($recentInvoices as $invoice) {
                $customerName = 'Unknown Customer';
                if ($invoice->invoiceable instanceof ServiceRequest) {
                    $customerName = $invoice->invoiceable->customer_name ?? 'Service Customer';
                } elseif ($invoice->invoiceable instanceof Rental && $invoice->invoiceable->customer) {
                    $customerName = $invoice->invoiceable->customer->name;
                }

                $activities->push([
                    'id' => 'invoice_' . $invoice->id,
                    'type' => 'invoice',
                    'title' => 'Invoice ' . $invoice->invoice_number . ' created for ' . $customerName,
                    'created_at' => $invoice->created_at,
                    'entity_id' => $invoice->id,
                    'entity_type' => 'invoice'
                ]);
            }

            // Sort all activities by created_at descending and take the 10 most recent
            $sortedActivities = $activities->sortByDesc('created_at')->take(10)->values();

            return response()->json([
                'success' => true,
                'data' => $sortedActivities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive dashboard data
     */
    public function index()
    {
        try {
            $statsResponse = $this->stats();
            $activityResponse = $this->recentActivity();

            if (!$statsResponse->getData()->success || !$activityResponse->getData()->success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch dashboard data'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $statsResponse->getData()->data,
                    'recent_activity' => $activityResponse->getData()->data
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}