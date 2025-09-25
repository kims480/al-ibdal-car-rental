<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'contact_email',
        'contact_phone',
        'latitude',
        'longitude',
        'active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'active' => 'boolean',
    ];

    // Relationships
    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
