<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relaciona la obligación con el último corte externo que la reportó.
     */
    public function up(): void
    {
        Schema::table('deudas', function (Blueprint $table): void {
            $table->foreignId('importacion_cartera_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Elimina la referencia al último corte externo.
     */
    public function down(): void
    {
        Schema::table('deudas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('importacion_cartera_id');
        });
    }
};
