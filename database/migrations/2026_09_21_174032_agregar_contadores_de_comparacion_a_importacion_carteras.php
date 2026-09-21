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
        Schema::table('importacion_carteras', function (Blueprint $table): void {
            $table->unsignedInteger('deudas_ausentes')->default(0)->after('deudas_sin_cambios');
            $table->unsignedInteger('deudas_reingresadas')->default(0)->after('deudas_ausentes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('importacion_carteras', function (Blueprint $table): void {
            $table->dropColumn(['deudas_ausentes', 'deudas_reingresadas']);
        });
    }
};
