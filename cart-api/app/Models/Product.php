<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'image'];

    protected $casts = [
        'price' => 'float',
    ];

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}