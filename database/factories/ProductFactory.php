<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'barcode' => fake()->ean13(),
            'category_id' => ProductCategory::factory(),
            'supplier_id' => Supplier::factory(),
            'purchase_price' => fake()->randomFloat(2, 1, 100),
            'selling_price' => fake()->randomFloat(2, 5, 200),
            'min_stock' => fake()->numberBetween(5, 20),
        ];
    }
}
