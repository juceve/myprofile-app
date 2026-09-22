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
        Schema::create('jefe_ventas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_mandante_id')->constrained('empresa_mandantes')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['empresa_mandante_id', 'nombre']);
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jefe_ventas');
    }
};
