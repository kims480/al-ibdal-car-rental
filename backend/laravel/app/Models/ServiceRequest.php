<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_location',
        'governorate_id',
        'wilayat_id',
        'service_type',
        'priority',
        'is_near_muscat',
        'distance_to_nearest_branch',
        'rental_start',
        'rental_end',
        'car_id',
        'allocated_car_id',
        'branch_id',
        'assigned_branch_id',
        'partner_id',
        'assignee_id',
        'subcontractor_id',
        'status',
        'customer_notified_at',
        'notification_method',
        'car_delivered_at',
        'actual_rental_start',
        'actual_rental_end',
        'calculated_amount',
        'rental_days',
        'daily_rate_used',
        'payment_status',
        'payment_received_at',
        'amount_paid',
        'payment_method',
        'invoice_number',
        'invoice_delivery',
        'invoice_sent_at',
        'reviewed_at',
        'assigned_at',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'rental_start' => 'datetime',
        'rental_end' => 'datetime',
        'customer_notified_at' => 'datetime',
        'car_delivered_at' => 'datetime',
        'actual_rental_start' => 'datetime',
        'actual_rental_end' => 'datetime',
        'payment_received_at' => 'datetime',
        'invoice_sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'calculated_amount' => 'decimal:2',
        'daily_rate_used' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'distance_to_nearest_branch' => 'decimal:2',
        'is_near_muscat' => 'boolean',
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function allocatedCar()
    {
        return $this->belongsTo(Car::class, 'allocated_car_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedBranch()
    {
        return $this->belongsTo(Branch::class, 'assigned_branch_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function rental()
    {
        return $this->hasOne(Rental::class);
    }
    
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }
    
    public function user()
    {
        // Try to find a user by phone or email from service request data
        if ($this->customer_phone) {
            $user = User::where('phone', $this->customer_phone)->first();
            if ($user) return $user;
        }
        
        if ($this->customer_email) {
            $user = User::where('email', $this->customer_email)->first();
            if ($user) return $user;
        }
        
        return null;
    }
    
    /**
     * Get customer-like data for service request
     */
    public function getCustomerDataAttribute()
    {
        return (object) [
            'name' => $this->customer_name,
            'email' => $this->customer_email,
            'phone' => $this->customer_phone,
            'city' => null, // Service requests don't store city
        ];
    }
    
    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function wilayat()
    {
        return $this->belongsTo(Wilayat::class);
    }

    // Helper methods for workflow management
    public function canTransitionTo($status)
    {
        $transitions = [
            'submitted' => ['under_review', 'cancelled'],
            'under_review' => ['assigned', 'cancelled'],
            'assigned' => ['car_allocated', 'cancelled'],
            'car_allocated' => ['customer_notified', 'cancelled'],
            'customer_notified' => ['car_delivered', 'cancelled'],
            'car_delivered' => ['rental_active'],
            'rental_active' => ['car_returned'],
            'car_returned' => ['payment_pending'],
            'payment_pending' => ['completed'],
            'completed' => [],
            'cancelled' => []
        ];

        return in_array($status, $transitions[$this->status] ?? []);
    }

    public function transitionTo($status, $user = null)
    {
        if (!$this->canTransitionTo($status)) {
            throw new \Exception("Cannot transition from {$this->status} to {$status}");
        }

        $oldStatus = $this->status;
        $this->status = $status;

        // Set appropriate timestamps
        switch ($status) {
            case 'under_review':
                $this->reviewed_at = now();
                break;
            case 'assigned':
                $this->assigned_at = now();
                if ($user) {
                    $this->assignee_id = $user->id;
                }
                break;
            case 'car_delivered':
                $this->car_delivered_at = now();
                break;
            case 'rental_active':
                $this->actual_rental_start = now();
                break;
            case 'car_returned':
                $this->actual_rental_end = now();
                $this->calculateRentalAmount();
                break;
            case 'completed':
                $this->completed_at = now();
                break;
        }

        $this->save();
        return $this;
    }

    public function calculateRentalAmount()
    {
        if (!$this->actual_rental_start || !$this->actual_rental_end || !$this->allocatedCar) {
            return false;
        }

        $startDate = $this->actual_rental_start;
        $endDate = $this->actual_rental_end;
        $days = $startDate->diffInDays($endDate);
        
        // Minimum 1 day charge
        $days = max(1, $days);
        
        $dailyRate = $this->allocatedCar->daily_rate;
        $totalAmount = $days * $dailyRate;

        $this->rental_days = $days;
        $this->daily_rate_used = $dailyRate;
        $this->calculated_amount = $totalAmount;
        $this->save();

        return $totalAmount;
    }

    public function isNearMuscat()
    {
        // Check if the wilayat is in Muscat governorate
        return $this->wilayat && $this->wilayat->governorate_id == 1; // Assuming Muscat is ID 1
    }

    public function getNearestBranch()
    {
        if (!$this->wilayat_id) {
            return null;
        }

        // Get branch assignment for this wilayat
        $assignment = WilayatBranchAssignment::where('wilayat_id', $this->wilayat_id)
            ->where('is_active', true)
            ->where('is_primary', true)
            ->with('branch')
            ->first();

        return $assignment ? $assignment->branch : null;
    }

    // Scope for filtering
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assignee_id', $userId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('assigned_branch_id', $branchId);
    }
}
