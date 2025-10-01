<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->foreignId('conductor_id')->nullable()
                  ->constrained('conductores')->nullOnDelete();

            $table->string('origen')->nullable();
            $table->string('destino')->nullable();
            $table->dateTime('hora_salida')->nullable();
            $table->dateTime('hora_llegada')->nullable();
            $table->string('estado')->default('en_camino'); // en_camino|entregado
        });
    }

    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropForeign(['conductor_id']);
            $table->dropColumn([
                'conductor_id', 'origen', 'destino',
                'hora_salida', 'hora_llegada', 'estado'
            ]);
        });
    }
};
