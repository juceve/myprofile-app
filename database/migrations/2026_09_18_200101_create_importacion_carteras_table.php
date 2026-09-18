<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea los cortes auditables recibidos desde la fuente externa.
     */
    public function up(): void
    {
        Schema::create('importacion_carteras', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_archivo');
            $table->string('hash_archivo', 64)->unique();
            $table->string('archivo_resguardado')->nullable();
            $table->string('hoja');
            $table->string('estado', 20)->default('procesando');
            $table->unsignedInteger('filas_leidas')->default(0);
            $table->unsignedInteger('filas_omitidas')->default(0);
            $table->unsignedInteger('clientes_creados')->default(0);
            $table->unsignedInteger('clientes_actualizados')->default(0);
            $table->unsignedInteger('deudas_creadas')->default(0);
            $table->unsignedInteger('deudas_actualizadas')->default(0);
            $table->unsignedInteger('deudas_sin_cambios')->default(0);
            $table->decimal('saldo_reportado', 18, 2)->default(0);
            $table->timestamp('procesado_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina el historial de cortes.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacion_carteras');
    }
};
