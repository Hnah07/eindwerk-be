<?php

use App\Enums\ConcertSource;
use App\Enums\ConcertStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concerts', function (Blueprint $table) {
            $table->string('source')->default(ConcertSource::API->value);
            $table->string('status')->default(ConcertStatus::VERIFIED->value);
        });
    }

    public function down(): void
    {
        Schema::table('concerts', function (Blueprint $table) {
            $table->dropColumn(['source', 'status']);
        });
    }
};
