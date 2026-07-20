<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_levels', function (Blueprint $table) {
            $table->index(['warehouse_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_levels', function (Blueprint $table) {
            $table->dropIndex(['warehouse_id', 'product_id']);
            $table->dropIndex(['product_id']);
        });
    }
};
