<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('lot_number');
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'lot_number']);
        });

        Schema::table('stock_levels', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('product_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_levels', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
        });

        Schema::dropIfExists('product_batches');
    }
};
