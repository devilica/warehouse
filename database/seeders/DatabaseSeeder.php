<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'name' => 'WMS Admin',
            'email' => 'admin@wms.test',
            'password' => Hash::make('password'),
            'locale' => 'en',
        ]);
        $admin->assignRole('super-admin');

        User::factory()->create([
            'name' => 'Warehouse Manager',
            'email' => 'manager@wms.test',
            'password' => Hash::make('password'),
        ])->assignRole('warehouse-manager');

        Department::create(['name' => 'Operations', 'description' => 'Warehouse operations']);
        Supplier::factory(3)->create();
        Warehouse::factory(2)->create();
        $category = ProductCategory::create(['name' => 'General', 'slug' => 'general']);
        Product::factory(10)->create(['category_id' => $category->id]);
    }
}