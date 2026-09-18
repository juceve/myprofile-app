<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las obligaciones importadas desde la cartera.
     */
    public function up(): void
    {
        Schema::create('deudas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('numero_documento');
            $table->date('fecha_documento');
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('importe_original', 15, 2);
            $table->decimal('saldo_actual', 15, 2);
            $table->unsignedSmallInteger('plazo_dias')->nullable();
            $table->date('fecha_ultimo_pago')->nullable();
            $table->string('estado_origen', 10)->nullable();
            $table->string('jefe_vendedor_nombre')->nullable();
            $table->string('supervisor_nombre')->nullable();
            $table->string('vendedor_nombre')->nullable();
            $table->date('fecha_carga')->nullable();
            $table->string('origen_archivo')->nullable();
            $table->unsignedInteger('origen_fila')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'numero_documento', 'fecha_documento']);
            $table->index(['fecha_vencimiento', 'saldo_actual']);
        });
    }

    /**
     * Elimina las obligaciones importadas.
     */
    public function down(): void
    {
        Schema::dropIfExists('deudas');
    }
};
