<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name', 'address', 'capacity', 'manager_id'];

    public function zones() { return $this->hasMany(WarehouseZone::class); }
    public function stockLevels() { return $this->hasMany(StockLevel::class); }
}