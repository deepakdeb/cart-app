<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * Product API Controller
 *
 * Handles product listing and detail retrieval.
 */
class ProductController extends Controller
{
    /**
     * Get all products.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $products = Product::all();
            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products.'
            ], 500);
        }
    }

    /**
     * Get a specific product by ID.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product.'
            ], 500);
        }
    }
}