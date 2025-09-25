<?php

namespace App\Services;

use App\Mail\ServiceRequestNotification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification for service request events
     */
    public function sendServiceRequestNotification(ServiceRequest $serviceRequest, string $type)
    {
        try {
            switch ($type) {
                case 'created':
                    $this->notifyOnServiceRequestCreated($serviceRequest);
                    break;
                
                case 'assigned':
                    $this->notifyOnServiceRequestAssigned($serviceRequest);
                    break;
                
                case 'confirmed':
                    $this->notifyOnServiceRequestConfirmed($serviceRequest);
                    break;
                
                case 'picked_up':
                    $this->notifyOnVehiclePickedUp($serviceRequest);
                    break;
                
                case 'returned':
                    $this->notifyOnVehicleReturned($serviceRequest);
                    break;
                
                case 'completed':
                    $this->notifyOnRentalCompleted($serviceRequest);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'service_request_id' => $serviceRequest->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify when a new service request is created
     */
    private function notifyOnServiceRequestCreated(ServiceRequest $serviceRequest)
    {
        // Notify HQ admin
        $admins = User::where('role', 'admin')->where('active', true)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new ServiceRequestNotification($serviceRequest, $admin, 'created')
            );
        }

        // Notify branch manager if branch is assigned
        if ($serviceRequest->branch_id) {
            $branchManagers = User::where('role', 'branch_manager')
                ->where('branch_id', $serviceRequest->branch_id)
                ->where('active', true)
                ->get();
            
            foreach ($branchManagers as $manager) {
                Mail::to($manager->email)->send(
                    new ServiceRequestNotification($serviceRequest, $manager, 'created')
                );
            }
        }

        Log::info('Service request created notifications sent', [
            'service_request_id' => $serviceRequest->id
        ]);
    }

    /**
     * Notify when a service request is assigned to a partner
     */
    private function notifyOnServiceRequestAssigned(ServiceRequest $serviceRequest)
    {
        // Notify assigned partner
        if ($serviceRequest->partner) {
            Mail::to($serviceRequest->partner->email)->send(
                new ServiceRequestNotification($serviceRequest, $serviceRequest->partner, 'assigned')
            );
        }

        // Notify HQ
        $admins = User::where('role', 'admin')->where('active', true)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new ServiceRequestNotification($serviceRequest, $admin, 'assigned')
            );
        }

        Log::info('Service request assignment notifications sent', [
            'service_request_id' => $serviceRequest->id,
            'partner_id' => $serviceRequest->partner_id
        ]);
    }

    /**
     * Notify when a service request is confirmed
     */
    private function notifyOnServiceRequestConfirmed(ServiceRequest $serviceRequest)
    {
        // Create a temporary customer user object for the email
        $customer = new User([
            'name' => $serviceRequest->customer_name,
            'email' => $this->getCustomerEmail($serviceRequest),
            'phone' => $serviceRequest->customer_phone
        ]);

        if ($customer->email) {
            Mail::to($customer->email)->send(
                new ServiceRequestNotification($serviceRequest, $customer, 'confirmed')
            );
        }

        // TODO: Send SMS/WhatsApp notification
        $this->sendSMSNotification($serviceRequest->customer_phone, 
            "Your rental is confirmed! Pickup details: {$serviceRequest->branch->name}, {$serviceRequest->branch->contact_phone}");

        Log::info('Service request confirmation notifications sent', [
            'service_request_id' => $serviceRequest->id
        ]);
    }

    /**
     * Notify when vehicle is picked up
     */
    private function notifyOnVehiclePickedUp(ServiceRequest $serviceRequest)
    {
        // Notify branch and HQ
        if ($serviceRequest->branch_id) {
            $branchUsers = User::where('branch_id', $serviceRequest->branch_id)
                ->whereIn('role', ['branch_manager', 'partner'])
                ->where('active', true)
                ->get();
            
            foreach ($branchUsers as $user) {
                Mail::to($user->email)->send(
                    new ServiceRequestNotification($serviceRequest, $user, 'picked_up')
                );
            }
        }

        Log::info('Vehicle pickup notifications sent', [
            'service_request_id' => $serviceRequest->id
        ]);
    }

    /**
     * Notify when vehicle is returned
     */
    private function notifyOnVehicleReturned(ServiceRequest $serviceRequest)
    {
        // Notify branch and HQ
        if ($serviceRequest->branch_id) {
            $branchUsers = User::where('branch_id', $serviceRequest->branch_id)
                ->whereIn('role', ['branch_manager', 'partner'])
                ->where('active', true)
                ->get();
            
            foreach ($branchUsers as $user) {
                Mail::to($user->email)->send(
                    new ServiceRequestNotification($serviceRequest, $user, 'returned')
                );
            }
        }

        Log::info('Vehicle return notifications sent', [
            'service_request_id' => $serviceRequest->id
        ]);
    }

    /**
     * Notify when rental is completed
     */
    private function notifyOnRentalCompleted(ServiceRequest $serviceRequest)
    {
        // Create a temporary customer user object for the email
        $customer = new User([
            'name' => $serviceRequest->customer_name,
            'email' => $this->getCustomerEmail($serviceRequest),
            'phone' => $serviceRequest->customer_phone
        ]);

        if ($customer->email) {
            Mail::to($customer->email)->send(
                new ServiceRequestNotification($serviceRequest, $customer, 'completed')
            );
        }

        // TODO: Send customer satisfaction survey
        
        Log::info('Rental completion notifications sent', [
            'service_request_id' => $serviceRequest->id
        ]);
    }

    /**
     * Send SMS notification (placeholder for SMS service integration)
     */
    private function sendSMSNotification(string $phone, string $message)
    {
        // TODO: Integrate with SMS service (Twilio, local provider, etc.)
        Log::info('SMS notification queued', [
            'phone' => $phone,
            'message' => substr($message, 0, 50) . '...'
        ]);
    }

    /**
     * Send WhatsApp notification (placeholder for WhatsApp API integration)
     */
    private function sendWhatsAppNotification(string $phone, string $message)
    {
        // TODO: Integrate with WhatsApp API
        Log::info('WhatsApp notification queued', [
            'phone' => $phone,
            'message' => substr($message, 0, 50) . '...'
        ]);
    }

    /**
     * Get customer email (placeholder - in production, collect during request)
     */
    private function getCustomerEmail(ServiceRequest $serviceRequest)
    {
        // In production, customer email should be collected during service request
        // For now, we'll return null and rely on SMS/WhatsApp
        return null;
    }
}