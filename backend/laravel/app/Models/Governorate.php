<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar', 
        'code',
        'description',
        'latitude',
        'longitude',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];

    /**
     * Get the wilayats for the governorate
     */
    public function wilayats(): HasMany
    {
        return $this->hasMany(Wilayat::class);
    }

    /**
     * Get active wilayats only
     */
    public function activeWilayats(): HasMany
    {
        return $this->hasMany(Wilayat::class)->where('is_active', true);
    }

    /**
     * Scope to get active governorates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
