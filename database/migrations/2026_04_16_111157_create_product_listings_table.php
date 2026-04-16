<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product Listings (Marketplace-specific products)
        Schema::create('product_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('marketplace'); // wb, ozon, yandex
            $table->string('external_id'); // ID on marketplace side
            $table->string('vendor_code')->nullable(); // Supplier SKU on marketplace
            $table->bigInteger('price')->nullable();
            $table->bigInteger('old_price')->nullable();
            $table->integer('discount')->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0); // MP Commission %
            $table->bigInteger('logistic_cost')->default(0); // Cost to deliver to customer
            $table->string('status')->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['marketplace', 'external_id']);
            $table->index(['product_id', 'marketplace']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_listings');
    }
};
