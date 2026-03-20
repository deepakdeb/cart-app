<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebaseAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_authenticated_request()
    {
        $user = User::factory()->create();

        $response = $this->actingAsFirebaseUser($user)
                         ->getJson('/api/cart');

        $response->assertStatus(200); // Assuming cart is empty
    }

    public function test_blocks_unauthenticated_request()
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }
}