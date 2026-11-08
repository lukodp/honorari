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
        Schema::table('calculations', function (Blueprint $table) {
            if (! Schema::hasColumn('calculations', 'organization_name')) {
                $table->string('organization_name')->nullable();
            }
            if (! Schema::hasColumn('calculations', 'organization_address')) {
                $table->string('organization_address')->nullable();
            }
            if (! Schema::hasColumn('calculations', 'organization_postal_code')) {
                $table->string('organization_postal_code', 10)->nullable();
            }
            if (! Schema::hasColumn('calculations', 'organization_city')) {
                $table->string('organization_city')->nullable();
            }
            if (! Schema::hasColumn('calculations', 'organization_oib')) {
                $table->string('organization_oib', 11)->nullable();
            }
            if (! Schema::hasColumn('calculations', 'organization_iban')) {
                $table->string('organization_iban', 34)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            //
        });
    }
};
