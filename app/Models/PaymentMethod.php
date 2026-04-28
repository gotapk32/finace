<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'cut_day', 'payment_day'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
