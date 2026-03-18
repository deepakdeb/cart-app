<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getUser(Request $request)
    {
        return $request->get('auth_user');
    }

    // GET /api/cart
    public function index(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        $cart = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        return response()->json(['success' => true, 'data' => $cart]);
    }

    // POST /api/cart
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = $this->getUser($request);

        $cartItem = Cart::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json([
            'success' => true,
            'data'    => $cartItem->load('product'),
        ], 201);
    }

    // PUT /api/cart/{cart}
    public function update(Request $request, Cart $cart): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $this->getUser($request);
        if ($cart->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true, 'data' => $cart->load('product')]);
    }

    // DELETE /api/cart/{cart}
    public function destroy(Request $request, Cart $cart): JsonResponse
    {
        $user = $this->getUser($request);
        if ($cart->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $cart->delete();

        return response()->json(['success' => true, 'message' => 'Item removed.']);
    }

    // POST /api/cart/batch  ← for debounced sync
    public function batch(Request $request): JsonResponse
    {
        $request->validate([
            'items'              => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:0',
        ]);

        $user = $this->getUser($request);

        foreach ($request->items as $item) {
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

        $cart = Cart::with('product')->where('user_id', $user->id)->get();

        return response()->json(['success' => true, 'data' => $cart]);
    }
}