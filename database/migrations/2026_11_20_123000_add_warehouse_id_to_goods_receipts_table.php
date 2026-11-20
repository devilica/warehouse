<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('goods_receipts', 'warehouse_id')) {
            return;
        }

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('purchase_order_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('goods_receipts', 'warehouse_id')) {
            return;
        }

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
        });
    }
};
