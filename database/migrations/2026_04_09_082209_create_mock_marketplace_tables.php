<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mock Accounts
        Schema::create('mock_marketplace_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('marketplace');
            $table->string('name');
            $table->timestamps();
        });

        // 2. Mock Credentials
        Schema::create('mock_marketplace_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mock_marketplace_account_id')->constrained()->cascadeOnDelete();
            $table->string('key'); // token, client_id, api_key, ms_token
            $table->string('value')->index();
            $table->timestamps();

            $table->unique(['mock_marketplace_account_id', 'key']);
        });

        // 3. Mock Products
        Schema::create('mock_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mock_marketplace_account_id')->constrained('mock_marketplace_accounts')->cascadeOnDelete();
            $table->string('marketplace');
            $table->string('external_id');
            $table->string('vendor_code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('old_price', 12, 2)->nullable();
            $table->decimal('discount', 5, 2)->default(0);
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->json('images')->nullable();
            $table->json('attributes')->nullable();

            // Dimensions
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('depth', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mock_marketplace_account_id', 'external_id']);
            $table->index(['mock_marketplace_account_id', 'vendor_code']);
        });

        // 4. Mock Warehouses
        Schema::create('mock_warehouses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mock_marketplace_account_id')->constrained('mock_marketplace_accounts')->cascadeOnDelete();
            $table->string('marketplace');
            $table->string('external_id');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mock_marketplace_account_id', 'external_id'], 'idx_mock_wh_unique');
        });

        // 5. Mock Stocks
        Schema::create('mock_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mock_marketplace_account_id')->constrained('mock_marketplace_accounts')->cascadeOnDelete();
            $table->string('marketplace');
            $table->string('external_product_id');
            $table->string('sku')->nullable();
            $table->string('external_warehouse_id');
            $table->integer('quantity');
            $table->integer('reserved')->default(0);
            $table->timestamps();

            $table->index(['mock_marketplace_account_id', 'external_product_id', 'external_warehouse_id'], 'idx_mock_stocks_lookup');
        });

        // 6. Mock Orders
        Schema::create('mock_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mock_marketplace_account_id')->constrained('mock_marketplace_accounts')->cascadeOnDelete();
            $table->string('marketplace');
            $table->string('external_order_id')->unique();
            $table->string('status');
            $table->decimal('total_price', 12, 2);
            $table->json('items');
            $table->json('delivery_info')->nullable();
            $table->string('delivery_type')->default('fbs'); // fbs, fbo
            $table->timestamp('shipment_date')->nullable();
            $table->timestamp('order_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_orders');
        Schema::dropIfExists('mock_stocks');
        Schema::dropIfExists('mock_warehouses');
        Schema::dropIfExists('mock_products');
        Schema::dropIfExists('mock_marketplace_credentials');
        Schema::dropIfExists('mock_marketplace_accounts');
    }
};
