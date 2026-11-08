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
        $table->string('tax_iban', 34)->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('calculations', function (Blueprint $table) {
        $table->dropColumn('tax_iban');
    });
}
};
