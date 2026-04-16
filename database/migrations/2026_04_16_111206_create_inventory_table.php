<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stocks
        Schema::create('inventory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_listing_id')->constrained('product_listings')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('reserved')->default(0);
            $table->timestamps();

            $table->unique(['product_listing_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
