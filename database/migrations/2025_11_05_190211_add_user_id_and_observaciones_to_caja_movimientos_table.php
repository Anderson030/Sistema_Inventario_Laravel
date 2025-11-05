<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            // agrega user_id si no existe
            if (!Schema::hasColumn('caja_movimientos', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete()
                      ->after('monto');
            }

            // agrega observaciones si no existe (lo usas en el validate)
            if (!Schema::hasColumn('caja_movimientos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('caja_movimientos', 'user_id')) {
                $table->dropConstrainedForeignId('user_id'); // Laravel 9+ helper
                // Si tu versión no soporta el helper:
                // $table->dropForeign(['user_id']);
                // $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('caja_movimientos', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
        });
    }
};
