<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();

            // Identifikacija obračuna
            $table->string('report_id', 10)->index();

            // Povijesni podaci organizacije / isplatitelja
            $table->string('organization_name');
            $table->string('organization_address');
            $table->string('organization_postal_code', 10);
            $table->string('organization_city');
            $table->string('organization_oib', 11);
            $table->string('organization_iban', 34);

            // Podaci o primatelju u trenutku obračuna
            $table->foreignId('referee_id')
                ->nullable()
                ->constrained('referees')
                ->nullOnDelete();

            $table->string('referee_first_name');
            $table->string('referee_last_name');
            $table->string('referee_oib', 11);
            $table->string('referee_address');
            $table->string('referee_postal_code', 10);
            $table->string('referee_city');
            $table->string('referee_iban', 34);

            // Datum isplate
            $table->date('payment_date')->index();

            // Parametri obračuna u trenutku izračuna
            $table->decimal('net_amount', 12, 2);
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('coefficient', 10, 6);
            $table->decimal('tax_amount', 12, 2);

            // Povijesna kopija poreznih podataka
            $table->string('tax_city');
            $table->date('tax_valid_from');
            $table->date('tax_valid_until')->nullable();

            // Status obračuna
            $table->string('status', 20)->default('draft');

            $table->timestamps();

            $table->index(['referee_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};