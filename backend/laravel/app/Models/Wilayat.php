<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayat extends Model
{
    use HasFactory;

    protected $fillable = [
        'governorate_id',
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
     * Get the governorate that owns the wilayat
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get service requests for this wilayat
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Get branch assignments for this wilayat
     */
    public function branchAssignments(): HasMany
    {
        return $this->hasMany(WilayatBranchAssignment::class);
    }

    /**
     * Get the assigned branch for this wilayat
     */
    public function assignedBranch()
    {
        return $this->branchAssignments()->with('branch')->where('is_active', true)->first()?->branch;
    }

    /**
     * Scope to get active wilayats
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by governorate
     */
    public function scopeByGovernorate($query, $governorateId)
    {
        return $query->where('governorate_id', $governorateId);
    }
}
