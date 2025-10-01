<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si la tabla ya existe (creada desde phpMyAdmin), no hagas nada
        if (Schema::hasTable('envios')) {
            return;
        }

        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valor_envio');
            $table->unsignedInteger('numero_bulto');
            $table->unsignedBigInteger('valor_bulto');
            $table->bigInteger('ganancia_total');
            $table->unsignedBigInteger('pago_contado')->default(0);
            $table->unsignedBigInteger('pago_a_plazo')->default(0);
            $table->date('fecha_contado')->nullable();
            $table->date('fecha_plazo')->nullable();
            $table->date('fecha_envio')->nullable();
            $table->decimal('costo_semanal', 18, 2)->storedAs('CASE
                WHEN GREATEST(1, TIMESTAMPDIFF(WEEK, fecha_envio, fecha_plazo)) > 0
                THEN pago_a_plazo / GREATEST(1, TIMESTAMPDIFF(WEEK, fecha_envio, fecha_plazo))
                ELSE NULL
            END');
            $table->timestamps();
            $table->index(['fecha_envio','fecha_plazo']);
        });
    }

    public function down(): void
    {
        // Solo elimina si existe (por si quieres revertir en desarrollo)
        Schema::dropIfExists('envios');
    }
};
