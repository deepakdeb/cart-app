<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    /**
     * Get cart items for a user.
     */
    public function getCart(User $user): Collection
    {
        return Cart::with('product')->where('user_id', $user->id)->get();
    }

    /**
     * Add or update a cart item.
     */
    public function addOrUpdateItem(User $user, int $productId, int $quantity): Cart
    {
        return Cart::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $productId],
            ['quantity' => $quantity]
        )->load('product');
    }

    /**
     * Update a cart item's quantity.
     */
    public function updateItem(Cart $cart, int $quantity): Cart
    {
        $cart->update(['quantity' => $quantity]);
        return $cart->load('product');
    }

    /**
     * Delete a cart item.
     */
    public function deleteItem(Cart $cart): void
    {
        $cart->delete();
    }

    /**
     * Batch synchronize cart items.
     */
    public function batchSync(User $user, array $items): Collection
    {
        if (empty($items)) {
            Cart::where('user_id', $user->id)->delete();
            return collect();
        }

        foreach ($items as $item) {
            if ($item['quantity'] === 0) {
                Cart::where('user_id', $user->id)
                    ->where('product_id', $item['product_id'])
                    ->delete();
            } else {
                Cart::updateOrCreate(
                    ['user_id' => $user->id, 'product_id' => $item['product_id']],
                    ['quantity' => $item['quantity']]
                );
            }
        }

        $productIds = collect($items)->pluck('product_id');
        Cart::where('user_id', $user->id)
            ->whereNotIn('product_id', $productIds)
            ->delete();

        return $this->getCart($user);
    }
}