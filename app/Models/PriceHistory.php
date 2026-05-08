<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    public $timestamps = false;
    protected $fillable = ['shopping_item_id', 'price', 'recorded_at'];

    protected $casts = [
        'price' => 'encrypted',
        'recorded_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(ShoppingItem::class, 'shopping_item_id');
    }
}
