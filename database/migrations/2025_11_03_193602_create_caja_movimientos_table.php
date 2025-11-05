<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->string('categoria', 80)->nullable();
            $table->string('descripcion', 200)->nullable();
            $table->integer('monto');
            $table->timestamps();

            $table->index(['fecha', 'tipo']);
            $table->index(['categoria']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('caja_movimientos');
    }
};
