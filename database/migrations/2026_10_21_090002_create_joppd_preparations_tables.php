<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joppd_preparations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('organization_oib', 11);
            $table->date('payment_date');
            $table->unsignedInteger('revision');
            $table->string('status', 20)->default('ready');
            $table->char('fingerprint', 64);
            $table->json('snapshot');
            $table->timestamps();
            $table->unique(['organization_id', 'payment_date', 'revision']);
            $table->index(['organization_oib', 'payment_date', 'status']);
        });

        Schema::create('calculation_joppd_preparation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('joppd_preparation_id')
                ->constrained('joppd_preparations')
                ->cascadeOnDelete();
            $table->foreignId('calculation_id')
                ->nullable()
                ->constrained('calculations')
                ->nullOnDelete();
            $table->unique(['joppd_preparation_id', 'calculation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_joppd_preparation');
        Schema::dropIfExists('joppd_preparations');
    }
};
