<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id', // Keep for backward compatibility
        'invoiceable_type',
        'invoiceable_id', 
        'invoice_number',
        'amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'status',
        'issued_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get the invoiceable entity (ServiceRequest or Rental)
     */
    public function invoiceable()
    {
        return $this->morphTo();
    }

    /**
     * Get the rental if invoice is for a rental
     */
    public function rental()
    {
        return $this->morphedByMany(Rental::class, 'invoiceable');
    }

    /**
     * Helper method to get the related entity (service request or rental)
     */
    public function getRelatedEntity()
    {
        if ($this->invoiceable_type && $this->invoiceable_id) {
            return $this->invoiceable;
        }
        
        // Fallback to old relationship for backward compatibility
        return $this->serviceRequest;
    }

    /**
     * Get customer information from either service request or rental
     */
    public function getCustomerAttribute()
    {
        $entity = $this->getRelatedEntity();
        
        if ($entity instanceof \App\Models\Rental) {
            return $entity->customer;
        }
        
        if ($entity instanceof \App\Models\ServiceRequest) {
            // Return user from service request as customer-like data
            $user = $entity->user;
            if ($user) {
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'city' => $user->city ?? null,
                ];
            }
        }
        
        return null;
    }

    /**
     * Get the customer name from polymorphic relationship
     */
    public function getCustomerName()
    {
        $entity = $this->getRelatedEntity();
        
        if ($entity instanceof \App\Models\Rental) {
            return $entity->customer ? $entity->customer->name : 'Valued Customer';
        }
        
        if ($entity instanceof \App\Models\ServiceRequest) {
            return $entity->customer_name ?: 'Valued Customer';
        }
        
        return 'Valued Customer';
    }

    /**
     * Get the customer email from polymorphic relationship
     */
    public function getCustomerEmail()
    {
        $entity = $this->getRelatedEntity();
        
        if ($entity instanceof \App\Models\Rental) {
            return $entity->customer ? $entity->customer->email : null;
        }
        
        if ($entity instanceof \App\Models\ServiceRequest) {
            return $entity->customer_email;
        }
        
        return null;
    }

    /**
     * Get the customer phone from polymorphic relationship
     */
    public function getCustomerPhone()
    {
        $entity = $this->getRelatedEntity();
        
        if ($entity instanceof \App\Models\Rental) {
            return $entity->customer ? $entity->customer->phone : null;
        }
        
        if ($entity instanceof \App\Models\ServiceRequest) {
            return $entity->customer_phone;
        }
        
        return null;
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
