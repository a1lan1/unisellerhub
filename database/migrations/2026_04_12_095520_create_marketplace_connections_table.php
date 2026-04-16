<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('marketplace'); // wb, ozon, etc.
            $table->string('name');
            $table->text('credentials'); // Encrypted JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'marketplace']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_connections');
    }
};
