<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryCountItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['inventory_count_id', 'product_id', 'warehouse_location_id', 'expected_quantity', 'counted_quantity'];

    public function product() { return $this->belongsTo(Product::class); }
    public function count() { return $this->belongsTo(InventoryCount::class, 'inventory_count_id'); }
}