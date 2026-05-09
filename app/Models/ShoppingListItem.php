<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model
{
    protected $fillable = ['shopping_list_id', 'shopping_item_id', 'quantity', 'price', 'is_bought', 'is_personal'];

    protected $casts = [
        'price' => 'encrypted',
        'is_bought' => 'boolean',
        'is_personal' => 'boolean',
    ];

    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class);
    }

    public function item()
    {
        return $this->belongsTo(ShoppingItem::class, 'shopping_item_id');
    }
}
