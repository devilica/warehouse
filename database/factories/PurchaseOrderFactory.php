<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'number' => 'PO-' . fake()->unique()->numerify('######'),
            'supplier_id' => Supplier::factory(),
            'status' => 'draft',
            'expected_delivery_date' => fake()->dateTimeBetween('now', '+30 days'),
            'created_by' => User::factory(),
        ];
    }
}