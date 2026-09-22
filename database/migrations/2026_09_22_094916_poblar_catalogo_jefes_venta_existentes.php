<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('deudas')
            ->join('clientes', 'clientes.id', '=', 'deudas.cliente_id')
            ->whereNotNull('deudas.jefe_vendedor_nombre')
            ->where('deudas.jefe_vendedor_nombre', '<>', '')
            ->select('clientes.empresa_mandante_id', 'deudas.jefe_vendedor_nombre')
            ->distinct()
            ->orderBy('clientes.empresa_mandante_id')
            ->get()
            ->each(function (object $jefeVenta): void {
                DB::table('jefe_ventas')->insertOrIgnore([
                    'empresa_mandante_id' => $jefeVenta->empresa_mandante_id,
                    'nombre' => $jefeVenta->jefe_vendedor_nombre,
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
        Schema::dropIfExists('jefe_ventas');
    }
};
