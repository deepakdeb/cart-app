<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cart API Controller
 *
 * Handles cart operations including viewing, adding, updating, removing items,
 * and batch synchronization for optimistic UI updates.
 */
class CartController extends Controller
{
    /**
     * Get the authenticated user from the request.
     *
     * @param Request $request
     * @return mixed
     */
    private function getUser(Request $request)
    {
        return $request->get('auth_user');
    }

    /**
     * Get all cart items for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            $cart = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart items.'
            ], 500);
        }
    }

    /**
     * Add or update a single item in the cart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $user = $this->getUser($request);

            $cartItem = Cart::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $request->product_id],
                ['quantity' => $request->quantity]
            );

            return response()->json([
                'success' => true,
                'data' => $cartItem->load('product'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart.'
            ], 500);
        }
    }

    /**
     * Update the quantity of a specific cart item.
     *
     * @param Request $request
     * @param Cart $cart
     * @return JsonResponse
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $user = $this->getUser($request);
            if ($cart->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.'
                ], 403);
            }

            $cart->update(['quantity' => $request->quantity]);

            return response()->json([
                'success' => true,
                'data' => $cart->load('product')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item.'
            ], 500);
        }
    }

    /**
     * Remove a specific item from the cart.
     *
     * @param Request $request
     * @param Cart $cart
     * @return JsonResponse
     */
    public function destroy(Request $request, Cart $cart): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            if ($cart->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.'
                ], 403);
            }

            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart item.'
            ], 500);
        }
    }

    /**
     * Batch synchronize cart items (used for optimistic UI updates).
     *
     * Accepts an array of items with product_id and quantity.
     * Items with quantity 0 are removed.
     * Items not in the array are removed from cart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batch(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            $items = $request->input('items', []);

            // If empty array, clear all cart items
            if (is_array($items) && count($items) === 0) {
                Cart::where('user_id', $user->id)->delete();
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Validate the request
            $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:0',
            ]);

            // Process each item
            foreach ($items as $item) {
                if ($item['quantity'] === 0) {
                    // Remove item if quantity is 0
                    Cart::where('user_id', $user->id)
                        ->where('product_id', $item['product_id'])
                        ->delete();
                } else {
                    // Update or create item
                    Cart::updateOrCreate(
                        ['user_id' => $user->id, 'product_id' => $item['product_id']],
                        ['quantity' => $item['quantity']]
                    );
                }
            }

            // Remove items not in the batch (user removed them locally)
            $productIds = collect($items)->pluck('product_id');
            Cart::where('user_id', $user->id)
                ->whereNotIn('product_id', $productIds)
                ->delete();

            // Return updated cart
            $cart = Cart::with('product')->where('user_id', $user->id)->get();

            return response()->json([
                'success' => true,
                'data' => $cart
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize cart.'
            ], 500);
        }
    }
}