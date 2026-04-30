<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'name', 'amount', 'date', 'payer', 'image', 
        'user_id', 'type', 'debt_direction', 'is_personal', 'is_recurring', 'is_active', 
        'payment_method_id', 'due_day', 'category_id', 'trip_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    protected $casts = [
        'name' => 'encrypted',
        'amount' => 'encrypted',
        'payer' => 'encrypted',
        'is_personal' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];
}
