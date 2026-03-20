<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    public function test_can_get_cart_for_authenticated_user()
    {
        // Add item to cart
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAsFirebaseUser($this->user)
                         ->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'product_id', 'quantity', 'product' => ['id', 'name']]
                     ]
                 ])
                 ->assertJson(['success' => true]);
    }

    public function test_returns_empty_cart_when_no_items()
    {
        $response = $this->actingAsFirebaseUser($this->user)
                         ->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'data' => []]);
    }

    public function test_can_add_item_to_cart()
    {
        $payload = [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ];

        $response = $this->actingAsFirebaseUser($this->user)
                         ->postJson('/api/cart', $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    }

    public function test_fails_to_add_item_with_invalid_data()
    {
        $payload = ['product_id' => 999, 'quantity' => -1]; // Invalid

        $response = $this->actingAsFirebaseUser($this->user)
                         ->postJson('/api/cart', $payload);

        $response->assertStatus(422)
                 ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_can_update_cart_item_quantity()
    {
        $cartItem = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $payload = ['quantity' => 5];

        $response = $this->actingAsFirebaseUser($this->user)
                         ->putJson("/api/cart/{$cartItem->id}", $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_fails_to_update_nonexistent_cart_item()
    {
        $payload = ['quantity' => 3];

        $response = $this->actingAsFirebaseUser($this->user)
                         ->putJson('/api/cart/999', $payload);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    public function test_can_remove_cart_item()
    {
        $cartItem = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAsFirebaseUser($this->user)
                         ->deleteJson("/api/cart/{$cartItem->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Item removed.']);

        $this->assertDatabaseMissing('carts', ['id' => $cartItem->id]);
    }

    public function test_fails_to_remove_others_cart_item()
    {
        $otherUser = User::factory()->create();
        $cartItem = Cart::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAsFirebaseUser($this->user)
                         ->deleteJson("/api/cart/{$cartItem->id}");

        $response->assertStatus(403)
                 ->assertJson(['success' => false]);
    }

    public function test_can_batch_sync_cart()
    {
        $payload = [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ];

        $response = $this->actingAsFirebaseUser($this->user)
                         ->postJson('/api/cart/batch', $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_batch_sync_clears_cart_when_empty_array()
    {
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $payload = ['items' => []];

        $response = $this->actingAsFirebaseUser($this->user)
                         ->postJson('/api/cart/batch', $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('carts', ['user_id' => $this->user->id]);
    }

    public function test_unauthenticated_access_returns_401()
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }
}