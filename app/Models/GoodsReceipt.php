<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasFactory;
    
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'confirmed_at' => 'datetime', 'approved_at' => 'datetime'];

    protected $fillable = ['purchase_order_id', 'warehouse_id', 'status', 'confirmed_at', 'confirmed_by'];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function items() { return $this->hasMany(GoodsReceiptItem::class); }
}