<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAdjustmentItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['inventory_adjustment_id', 'product_id', 'warehouse_location_id', 'quantity_delta', 'reason'];

    public function product() { return $this->belongsTo(Product::class); }
    public function adjustment() { return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id'); }
}