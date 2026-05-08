<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingItem extends Model
{
    protected $fillable = ['name', 'last_price', 'current_price', 'user_id'];

    protected $casts = [
        'name' => 'encrypted',
        'last_price' => 'encrypted',
        'current_price' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class)->orderBy('recorded_at', 'desc');
    }
}
