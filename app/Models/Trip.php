<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'name', 'destination', 'start_date', 'end_date', 'budget', 
        'description', 'is_personal', 'is_active', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    protected $casts = [
        'name' => 'encrypted',
        'destination' => 'encrypted',
        'budget' => 'encrypted',
        'description' => 'encrypted',
        'is_personal' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
