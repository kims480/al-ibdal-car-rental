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
        'customer_location',
        'rental_start',
        'rental_end',
        'car_id',
        'branch_id',
        'partner_id',
        'subcontractor_id',
        'service_type',
        'priority',
        'status',
        'notes'
    ];

    protected $casts = [
        'rental_start' => 'datetime',
        'rental_end' => 'datetime',
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function rental()
    {
        return $this->hasOne(Rental::class);
    }
    
    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }
}
