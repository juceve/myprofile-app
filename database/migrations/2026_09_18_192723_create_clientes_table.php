<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea el catálogo de clientes de la cartera.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo_externo')->unique();
            $table->string('nombre');
            $table->string('documento_identidad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('tipo_ubicacion')->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('limite_credito', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Elimina el catálogo de clientes.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
