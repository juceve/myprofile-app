<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presencia_deuda_cortes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('importacion_cartera_id')->constrained('importacion_carteras')->cascadeOnDelete();
            $table->foreignId('deuda_id')->constrained('deudas')->cascadeOnDelete();
            $table->string('estado', 20);
            $table->timestamps();

            $table->unique(['importacion_cartera_id', 'deuda_id']);
            $table->index(['deuda_id', 'estado']);
        });

        DB::table('deudas')
            ->whereNotNull('importacion_cartera_id')
            ->get(['id', 'importacion_cartera_id'])
            ->each(function (object $deuda): void {
                DB::table('presencia_deuda_cortes')->insert([
                    'importacion_cartera_id' => $deuda->importacion_cartera_id,
                    'deuda_id' => $deuda->id,
                    'estado' => 'presente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presencia_deuda_cortes');
    }
};
