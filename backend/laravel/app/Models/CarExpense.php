<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'expense_type',
        'amount',
        'description',
        'expense_date',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'float',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
