<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotalCents(): int
    {
        return (int) $this->items()->with('product')->get()->sum(function ($item) {
            return $item->quantity * $item->product->price_cents;
        });
    }
}
