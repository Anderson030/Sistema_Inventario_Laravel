<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            // Relación opcional: un envío puede o no tener factura asociada
            $table->foreignId('invoice_id')
                  ->nullable()
                  ->constrained('invoices')
                  ->nullOnDelete(); // si se borra la factura, deja el campo en null
        });
    }

    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            // elimina FK y columna de forma segura
            $table->dropConstrainedForeignId('invoice_id');
        });
    }
};
