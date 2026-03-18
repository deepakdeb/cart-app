<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = ['firebase_uid', 'name', 'email', 'avatar'];

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}