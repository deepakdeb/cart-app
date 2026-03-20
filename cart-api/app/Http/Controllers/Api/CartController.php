<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Http\Requests\Api\BatchCartRequest;
use App\Models\Cart;
use App\Services\CartService;
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
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get the authenticated user from the request.
     */
    private function getUser(Request $request)
    {
        return $request->get('auth_user');
    }

    /**
     * Get all cart items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            $cart = $this->cartService->getCart($user);

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
     */
    public function store(StoreCartRequest $request): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            $cartItem = $this->cartService->addOrUpdateItem(
                $user,
                $request->product_id,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'data' => $cartItem,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart.'
            ], 500);
        }
    }

    /**
     * Update the quantity of a specific cart item.
     */
    public function update(UpdateCartRequest $request, Cart $cart): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            if ($cart->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.'
                ], 403);
            }

            $updatedCart = $this->cartService->updateItem($cart, $request->quantity);

            return response()->json([
                'success' => true,
                'data' => $updatedCart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item.'
            ], 500);
        }
    }

    /**
     * Remove a specific item from the cart.
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

            $this->cartService->deleteItem($cart);

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
     */
    public function batch(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser($request);
            $items = $request->input('items', []);
            
            // Handle empty cart case
            if (empty($items)) {
                Cart::where('user_id', $user->id)->delete();

                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $request->validate(
                [
                    'items' => 'required|array',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.quantity' => 'required|integer|min:0',
                ]
            );
            
            $cart = $this->cartService->batchSync($user, $items);

            return response()->json([
                'success' => true,
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize cart.'
            ], 500);
        }
    }
}