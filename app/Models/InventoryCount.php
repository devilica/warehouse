<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryCount extends Model
{
    use HasFactory;
    
    protected $fillable = ['number', 'warehouse_id', 'status', 'type', 'scheduled_at', 'started_at', 'completed_at', 'finalized_at'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items() { return $this->hasMany(InventoryCountItem::class); }
}