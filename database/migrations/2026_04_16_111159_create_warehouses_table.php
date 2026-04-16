<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name');
            $table->string('marketplace')->nullable(); // null if own warehouse
            $table->string('external_id')->nullable(); // ID on marketplace side
            $table->string('address')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'marketplace', 'external_id'], 'warehouse_org_market_ext_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
