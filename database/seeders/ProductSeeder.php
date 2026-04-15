<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Mouse', 'price_cents' => 2499, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'price_cents' => 7999, 'stock' => 30],
            ['name' => 'USB-C Hub', 'price_cents' => 3999, 'stock' => 40],
            ['name' => 'Laptop Stand', 'price_cents' => 2999, 'stock' => 25],
            ['name' => 'Noise Cancelling Headphones', 'price_cents' => 12999, 'stock' => 20],
            ['name' => 'Webcam HD', 'price_cents' => 4599, 'stock' => 35],
            ['name' => 'Bluetooth Speaker', 'price_cents' => 5999, 'stock' => 28],
            ['name' => 'External Hard Drive 1TB', 'price_cents' => 6999, 'stock' => 22],
            ['name' => 'Portable SSD 512GB', 'price_cents' => 8999, 'stock' => 18],
            ['name' => 'Gaming Mouse Pad', 'price_cents' => 1999, 'stock' => 60],
            ['name' => 'Ergonomic Chair', 'price_cents' => 19999, 'stock' => 10],
            ['name' => 'Desk Lamp LED', 'price_cents' => 3499, 'stock' => 27],
            ['name' => 'Smartphone Stand', 'price_cents' => 1499, 'stock' => 55],
            ['name' => 'Tablet Sleeve', 'price_cents' => 2599, 'stock' => 33],
            ['name' => 'Wireless Charger', 'price_cents' => 2999, 'stock' => 45],
            ['name' => 'HDMI Cable 2m', 'price_cents' => 1299, 'stock' => 70],
            ['name' => 'Ethernet Cable 5m', 'price_cents' => 1599, 'stock' => 65],
            ['name' => 'Power Bank 10000mAh', 'price_cents' => 3499, 'stock' => 38],
            ['name' => 'Smartwatch', 'price_cents' => 14999, 'stock' => 15],
            ['name' => 'Fitness Tracker', 'price_cents' => 7999, 'stock' => 20],
            ['name' => 'Laptop Backpack', 'price_cents' => 4999, 'stock' => 26],
            ['name' => 'Monitor 24 inch', 'price_cents' => 11999, 'stock' => 12],
            ['name' => 'Monitor 27 inch', 'price_cents' => 15999, 'stock' => 8],
            ['name' => 'Graphics Tablet', 'price_cents' => 10999, 'stock' => 14],
            ['name' => 'Microphone USB', 'price_cents' => 6499, 'stock' => 19],
            ['name' => 'Ring Light', 'price_cents' => 3799, 'stock' => 23],
            ['name' => 'VR Headset', 'price_cents' => 24999, 'stock' => 7],
            ['name' => 'Gaming Controller', 'price_cents' => 5499, 'stock' => 29],
            ['name' => 'Keyboard Wrist Rest', 'price_cents' => 1799, 'stock' => 41],
            ['name' => 'Mouse Bungee', 'price_cents' => 1399, 'stock' => 36],
            ['name' => 'USB Flash Drive 128GB', 'price_cents' => 2299, 'stock' => 48],
            ['name' => 'Cooling Pad for Laptop', 'price_cents' => 3199, 'stock' => 24],
        ];

        foreach ($products as $row) {
            $slug = Str::slug($row['name']);

            Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $row['name'],
                    'description' => 'Portfolio demo product',
                    'price_cents' => $row['price_cents'],
                    'stock' => $row['stock'],
                    'image_url' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
