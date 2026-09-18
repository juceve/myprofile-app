<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la auditoría de valores recibidos para cada obligación.
     */
    public function up(): void
    {
        Schema::create('actualizacion_deudas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('importacion_cartera_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deuda_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos');
            $table->timestamps();

            $table->index(['deuda_id', 'created_at']);
        });
    }

    /**
     * Elimina la auditoría de valores de obligaciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualizacion_deudas');
    }
};
