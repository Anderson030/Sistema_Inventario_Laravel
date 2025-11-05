<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            // Evita error si ya existe
            if (!Schema::hasColumn('caja_movimientos', 'venta_id')) {
                // Si NO quieres FK estricta:
                $table->unsignedBigInteger('venta_id')->nullable()->after('id')->index();

                // Si prefieres FK real, usa esta en su lugar:
                // $table->foreignId('venta_id')->nullable()->constrained('envios')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('caja_movimientos', 'venta_id')) {
                // Si usaste FK real, primero: $table->dropForeign(['venta_id']);
                $table->dropColumn('venta_id');
            }
        });
    }
};
