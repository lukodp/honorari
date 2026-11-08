<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();

            $table->string('city');
            $table->decimal('rate', 5, 2);
            $table->decimal('coefficient', 10, 6);

            $table->date('valid_from');
            $table->date('valid_until')->nullable();

            $table->timestamps();

            $table->index(['city', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
