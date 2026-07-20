<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use HasFactory;
    
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'confirmed_at' => 'datetime', 'approved_at' => 'datetime'];

    protected $fillable = ['user_id', 'auditable_type', 'auditable_id', 'action', 'old_values', 'new_values', 'ip_address', 'user_agent'];

    public function user() { return $this->belongsTo(User::class); }
}