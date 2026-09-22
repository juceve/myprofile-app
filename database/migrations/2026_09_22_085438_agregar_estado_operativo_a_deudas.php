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
        Schema::table('deudas', function (Blueprint $table): void {
            $table->string('estado_operativo', 30)->default('vigente')->after('estado_origen');
            $table->index('estado_operativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deudas', function (Blueprint $table): void {
            $table->dropIndex(['estado_operativo']);
            $table->dropColumn('estado_operativo');
        });
    }
};
