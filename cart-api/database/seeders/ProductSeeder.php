<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Headphones',  'description' => 'Premium noise-cancelling headphones.', 'price' => 99.99,  'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400'],
            ['name' => 'Mechanical Keyboard',  'description' => 'RGB backlit mechanical keyboard.',     'price' => 79.99,  'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400'],
            ['name' => 'USB-C Hub',             'description' => '7-in-1 multiport USB-C adapter.',     'price' => 39.99,  'image' => 'https://images.unsplash.com/photo-1625842268584-8f3296236761?w=400'],
            ['name' => 'Webcam HD',             'description' => '1080p wide-angle webcam.',            'price' => 59.99,  'image' => 'https://images.unsplash.com/photo-1596565308004-c57fd8b98e02?w=400'],
            ['name' => 'Desk Lamp',             'description' => 'LED smart desk lamp.',                'price' => 29.99,  'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=400'],
            ['name' => 'Mouse Pad XL',          'description' => 'Extended gaming mouse pad.',          'price' => 19.99,  'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}