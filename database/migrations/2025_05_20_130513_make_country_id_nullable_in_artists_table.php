<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['country_id']);
            // Then modify the column to be nullable
            $table->foreignId('country_id')->nullable()->change();
            // Re-add the foreign key constraint
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['country_id']);
            // Make it non-nullable again
            $table->foreignId('country_id')->nullable(false)->change();
            // Re-add the foreign key constraint
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }
};
