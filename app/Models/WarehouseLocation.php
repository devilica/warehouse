<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseLocation extends Model
{
    use HasFactory;
    
    protected $fillable = ['warehouse_id', 'warehouse_shelf_id', 'code', 'name', 'is_active'];

    public function shelf() { return $this->belongsTo(WarehouseShelf::class, "warehouse_shelf_id"); }
}