<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WilayatBranchAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'wilayat_id',
        'branch_id',
        'is_active',
        'is_primary',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean'
    ];

    /**
     * Get the wilayat that owns the assignment
     */
    public function wilayat(): BelongsTo
    {
        return $this->belongsTo(Wilayat::class);
    }

    /**
     * Get the branch that owns the assignment
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope to get active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get primary assignments
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
