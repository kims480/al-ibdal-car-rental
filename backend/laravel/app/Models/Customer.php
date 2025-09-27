<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'city',
        'civil_id',
        'license_id',
        'civil_id_front',
        'civil_id_back',
        'license_front',
        'license_back',
        'created_by',
    ];

    /**
     * Get the rentals for the customer.
     */
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Get active rentals for the customer.
     */
    public function activeRentals()
    {
        return $this->hasMany(Rental::class)->where('status', 'active');
    }

    /**
     * Get the user who created this customer.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
