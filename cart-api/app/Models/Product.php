<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'price', 'image'];

    protected $casts = [
        'price' => 'float',
    ];

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}