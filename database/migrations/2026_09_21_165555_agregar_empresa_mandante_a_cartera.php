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
        Schema::table('clientes', function (Blueprint $table): void {
            $table->foreignId('empresa_mandante_id')->nullable()->after('id')->constrained('empresa_mandantes')->nullOnDelete();
        });

        Schema::table('importacion_carteras', function (Blueprint $table): void {
            $table->foreignId('empresa_mandante_id')->nullable()->after('id')->constrained('empresa_mandantes')->nullOnDelete();
        });

        if (DB::table('clientes')->exists() || DB::table('importacion_carteras')->exists()) {
            $empresaId = DB::table('empresa_mandantes')->insertGetId([
                'codigo' => 'BBO',
                'razon_social' => 'BBO',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('clientes')->update(['empresa_mandante_id' => $empresaId]);
            DB::table('importacion_carteras')->update(['empresa_mandante_id' => $empresaId]);
        }

        Schema::table('clientes', function (Blueprint $table): void {
            $table->dropUnique('clientes_codigo_externo_unique');
            $table->unique(['empresa_mandante_id', 'codigo_externo']);
            $table->foreignId('empresa_mandante_id')->nullable(false)->change();
        });

        Schema::table('importacion_carteras', function (Blueprint $table): void {
            $table->foreignId('empresa_mandante_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('importacion_carteras', function (Blueprint $table): void {
            $table->dropForeign(['empresa_mandante_id']);
            $table->dropColumn('empresa_mandante_id');
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->dropUnique('clientes_empresa_mandante_id_codigo_externo_unique');
            $table->unique('codigo_externo');
            $table->dropForeign(['empresa_mandante_id']);
            $table->dropColumn('empresa_mandante_id');
        });
    }
};
