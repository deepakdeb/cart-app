<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_products()
    {
        // Seed products
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'description', 'price', 'image']
                     ]
                 ])
                 ->assertJson(['success' => true]);
    }

    public function test_returns_empty_list_when_no_products()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'data' => []]);
    }
}