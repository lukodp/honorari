<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            $table->foreignId('local_government_id')
                ->nullable()
                ->after('city')
                ->constrained('local_governments')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('local_government_id');
        });
    }
};