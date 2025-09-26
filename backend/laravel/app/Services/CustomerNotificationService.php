<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceRequestNotification;

class CustomerNotificationService
{
    /**
     * Send notification to customer about service request updates
     */
    public function sendServiceRequestNotification(ServiceRequest $serviceRequest, string $type, array $additionalData = [])
    {
        try {
            // Try to find customer by phone
            $customer = User::where('phone', $serviceRequest->customer_phone)->first();
            
            $notificationData = $this->prepareNotificationData($serviceRequest, $type, $additionalData);
            
            // Send email if customer found or email provided
            if ($customer && $customer->email) {
                $this->sendEmailNotification($customer->email, $customer->name, $notificationData);
            } elseif (isset($additionalData['email'])) {
                $this->sendEmailNotification($additionalData['email'], $serviceRequest->customer_name, $notificationData);
            }
            
            // Send SMS notification
            $this->sendSmsNotification($serviceRequest->customer_phone, $notificationData);
            
            // Log notification
            Log::info('Customer notification sent', [
                'service_request_id' => $serviceRequest->id,
                'type' => $type,
                'customer_phone' => $serviceRequest->customer_phone,
                'methods' => $this->getNotificationMethods($customer, $additionalData)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Customer notification failed', [
                'service_request_id' => $serviceRequest->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Send SMS notification to customer
     */
    public function sendSmsNotification(string $phoneNumber, array $notificationData)
    {
        try {
            $message = $this->formatSmsMessage($notificationData);
            
            // TODO: Integrate with actual SMS service (e.g., Twilio, AWS SNS, local SMS gateway)
            // For now, we'll log the SMS content
            Log::info('SMS notification would be sent', [
                'phone' => $phoneNumber,
                'message' => $message
            ]);
            
            // Placeholder for actual SMS sending logic:
            // $this->smsGateway->sendSms($phoneNumber, $message);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('SMS notification failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send email notification to customer
     */
    public function sendEmailNotification(string $email, string $customerName, array $notificationData)
    {
        try {
            // Create a pseudo user object for the mail template
            $pseudoUser = new User([
                'name' => $customerName,
                'email' => $email
            ]);
            
            Mail::to($email)->queue(new ServiceRequestNotification(
                $notificationData['service_request'], 
                $pseudoUser, 
                $notificationData['type']
            ));
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Email notification failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Prepare notification data based on type
     */
    private function prepareNotificationData(ServiceRequest $serviceRequest, string $type, array $additionalData = [])
    {
        $baseData = [
            'service_request' => $serviceRequest->load(['allocatedCar', 'assignedBranch', 'partner']),
            'type' => $type,
            'customer_name' => $serviceRequest->customer_name,
            'customer_phone' => $serviceRequest->customer_phone,
        ];
        
        switch ($type) {
            case 'car_ready':
                return array_merge($baseData, [
                    'subject' => 'Your rental car is ready for pickup',
                    'title' => 'Car Ready for Pickup',
                    'message' => $this->getCarReadyMessage($serviceRequest, $additionalData),
                    'action_required' => 'Please come to pick up your car',
                    'pickup_instructions' => $additionalData['pickup_instructions'] ?? null,
                ]);
                
            case 'car_delivered':
                return array_merge($baseData, [
                    'subject' => 'Your rental has started - Enjoy your trip!',
                    'title' => 'Car Delivered Successfully',
                    'message' => $this->getCarDeliveredMessage($serviceRequest, $additionalData),
                    'rental_start' => $serviceRequest->rental_start,
                    'rental_end' => $serviceRequest->rental_end,
                ]);
                
            case 'reminder_return':
                return array_merge($baseData, [
                    'subject' => 'Reminder: Car return due soon',
                    'title' => 'Car Return Reminder',
                    'message' => $this->getReturnReminderMessage($serviceRequest, $additionalData),
                    'return_due' => $serviceRequest->rental_end,
                    'action_required' => 'Please return the car by the due date',
                ]);
                
            case 'payment_due':
                return array_merge($baseData, [
                    'subject' => 'Payment due for your rental',
                    'title' => 'Payment Required',
                    'message' => $this->getPaymentDueMessage($serviceRequest, $additionalData),
                    'amount_due' => $serviceRequest->calculated_amount,
                    'due_date' => $serviceRequest->payment_due_date,
                ]);
                
            case 'completed':
                return array_merge($baseData, [
                    'subject' => 'Rental completed - Thank you!',
                    'title' => 'Rental Completed',
                    'message' => $this->getCompletedMessage($serviceRequest, $additionalData),
                    'total_amount' => $serviceRequest->payment_amount,
                ]);
                
            default:
                return array_merge($baseData, [
                    'subject' => 'Service request update',
                    'title' => 'Service Request Update',
                    'message' => 'Your service request has been updated.',
                ]);
        }
    }
    
    /**
     * Format SMS message
     */
    private function formatSmsMessage(array $notificationData)
    {
        $message = "Al-Ibdal Car Rental\n\n";
        $message .= "Dear {$notificationData['customer_name']},\n\n";
        $message .= $notificationData['message'];
        
        if (isset($notificationData['action_required'])) {
            $message .= "\n\n" . $notificationData['action_required'];
        }
        
        $message .= "\n\nFor assistance, call: +968 XXXX XXXX";
        $message .= "\n\nThank you for choosing Al-Ibdal!";
        
        return $message;
    }
    
    /**
     * Get notification methods used
     */
    private function getNotificationMethods($customer, array $additionalData = [])
    {
        $methods = ['sms'];
        
        if (($customer && $customer->email) || isset($additionalData['email'])) {
            $methods[] = 'email';
        }
        
        return $methods;
    }
    
    /**
     * Generate car ready message
     */
    private function getCarReadyMessage(ServiceRequest $serviceRequest, array $additionalData = [])
    {
        $car = $serviceRequest->allocatedCar;
        $branch = $serviceRequest->assignedBranch;
        
        $message = "Great news! Your rental car is ready for pickup.\n\n";
        $message .= "Car Details:\n";
        $message .= "- {$car->make} {$car->model} ({$car->year})\n";
        $message .= "- Plate: {$car->license_plate}\n";
        $message .= "- Color: {$car->color}\n\n";
        
        if ($branch) {
            $message .= "Pickup Location:\n";
            $message .= "{$branch->name}\n";
            $message .= "{$branch->address}\n";
            $message .= "Phone: {$branch->phone}\n\n";
        }
        
        $message .= "Rental Period: " . date('M j, Y', strtotime($serviceRequest->rental_start));
        $message .= " to " . date('M j, Y', strtotime($serviceRequest->rental_end));
        
        if (isset($additionalData['pickup_instructions'])) {
            $message .= "\n\nPickup Instructions:\n" . $additionalData['pickup_instructions'];
        }
        
        return $message;
    }
    
    /**
     * Generate car delivered message
     */
    private function getCarDeliveredMessage(ServiceRequest $serviceRequest, array $additionalData = [])
    {
        $car = $serviceRequest->allocatedCar;
        
        $message = "Your car has been delivered successfully! Enjoy your trip.\n\n";
        $message .= "Car: {$car->make} {$car->model} - {$car->license_plate}\n";
        $message .= "Rental End: " . date('M j, Y', strtotime($serviceRequest->rental_end)) . "\n\n";
        $message .= "Please remember to return the car on time to avoid additional charges.";
        
        return $message;
    }
    
    /**
     * Generate return reminder message
     */
    private function getReturnReminderMessage(ServiceRequest $serviceRequest, array $additionalData = [])
    {
        $returnDate = date('M j, Y', strtotime($serviceRequest->rental_end));
        $daysLeft = max(0, ceil((strtotime($serviceRequest->rental_end) - time()) / 86400));
        
        $message = "This is a friendly reminder that your rental car return is due ";
        if ($daysLeft == 0) {
            $message .= "today ({$returnDate}).";
        } elseif ($daysLeft == 1) {
            $message .= "tomorrow ({$returnDate}).";
        } else {
            $message .= "in {$daysLeft} days ({$returnDate}).";
        }
        
        $message .= "\n\nPlease ensure the car is returned on time to avoid late fees.";
        
        return $message;
    }
    
    /**
     * Generate payment due message
     */
    private function getPaymentDueMessage(ServiceRequest $serviceRequest, array $additionalData = [])
    {
        $amount = number_format($serviceRequest->calculated_amount, 2);
        $dueDate = date('M j, Y', strtotime($serviceRequest->payment_due_date));
        
        $message = "Your rental has been completed. Payment is now due.\n\n";
        $message .= "Amount Due: OMR {$amount}\n";
        $message .= "Due Date: {$dueDate}\n\n";
        $message .= "Please visit our office or contact us to arrange payment.";
        
        return $message;
    }
    
    /**
     * Generate completed message
     */
    private function getCompletedMessage(ServiceRequest $serviceRequest, array $additionalData = [])
    {
        $amount = number_format($serviceRequest->payment_amount, 2);
        
        $message = "Thank you for choosing Al-Ibdal Car Rental!\n\n";
        $message .= "Your rental has been completed successfully.\n";
        $message .= "Total Paid: OMR {$amount}\n\n";
        $message .= "We hope you had a great experience. Looking forward to serving you again!";
        
        return $message;
    }
}