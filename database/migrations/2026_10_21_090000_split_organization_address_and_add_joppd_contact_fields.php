<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('address', 'street');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('house_number')->nullable();
            $table->string('email')->nullable();
            $table->string('responsible_person_first_name')->nullable();
            $table->string('responsible_person_last_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'house_number',
                'email',
                'responsible_person_first_name',
                'responsible_person_last_name',
            ]);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('street', 'address');
        });
    }
};
