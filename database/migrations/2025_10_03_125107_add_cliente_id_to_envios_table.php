<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('envios', function (Blueprint $table) {
            if (!Schema::hasColumn('envios', 'cliente_id')) {
                $table->foreignId('cliente_id')
                      ->nullable()
                      ->after('conductor_id')
                      ->constrained('clientes')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('envios', function (Blueprint $table) {
            if (Schema::hasColumn('envios', 'cliente_id')) {
                $table->dropConstrainedForeignId('cliente_id');
            }
        });
    }
};
