<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAdjustment extends Model
{
    use HasFactory;
    
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'confirmed_at' => 'datetime', 'approved_at' => 'datetime'];

    protected $fillable = ['warehouse_id', 'status', 'reason', 'approved_at', 'approved_by'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items() { return $this->hasMany(InventoryAdjustmentItem::class); }
}