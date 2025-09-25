<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'model',
        'year',
        'registration',
        'color',
        'status',
        'branch_id',
        'daily_rate',
           'description',
           'vin',
           'features',
           'notes'
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
