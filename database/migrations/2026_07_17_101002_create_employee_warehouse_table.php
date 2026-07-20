<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id');
            $table->timestamps();

            $table->unique(['employee_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_warehouse');
    }
};
