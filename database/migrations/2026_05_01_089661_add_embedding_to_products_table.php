<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('ai.vector_search.enabled')) {
            return;
        }

        Schema::ensureVectorExtensionExists();

        Schema::table('products', function (Blueprint $table): void {
            $table->vector('embedding', dimensions: 1536)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! config('ai.vector_search.enabled')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('embedding');
        });
    }
};
