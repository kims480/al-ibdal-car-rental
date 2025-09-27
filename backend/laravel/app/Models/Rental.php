<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'car_id',
        'pickup_date',
        'return_date',
        'actual_pickup_date',
        'actual_return_date',
        'total_amount',
        'security_deposit',
        'additional_charges',
        'status',
        'rental_type',
        'insurance_included',
        'notes',
        'pickup_notes',
        'return_notes',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'return_date' => 'date',
        'actual_pickup_date' => 'date',
        'actual_return_date' => 'date',
        'total_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'insurance_included' => 'boolean',
    ];

    /**
     * Get the customer that owns the rental.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the car being rented.
     */
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the invoices for this rental.
     */
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    /**
     * Calculate rental duration in days.
     */
    public function getRentalDaysAttribute()
    {
        $startDate = Carbon::parse($this->pickup_date);
        $endDate = Carbon::parse($this->return_date);
        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Calculate actual rental duration in days.
     */
    public function getActualRentalDaysAttribute()
    {
        if (!$this->actual_pickup_date || !$this->actual_return_date) {
            return null;
        }
        
        $startDate = Carbon::parse($this->actual_pickup_date);
        $endDate = Carbon::parse($this->actual_return_date);
        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Calculate total cost including additional charges.
     */
    public function getTotalCostAttribute()
    {
        return $this->total_amount + $this->additional_charges;
    }

    /**
     * Check if rental is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->status === 'active' && Carbon::parse($this->return_date)->isPast();
    }

    /**
     * Get rental status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Scope to get active rentals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get overdue rentals.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                    ->where('return_date', '<', Carbon::today());
    }

    /**
     * Scope to get rentals by customer.
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
