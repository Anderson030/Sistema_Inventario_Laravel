<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('cantidad_bultos');
            $table->unsignedBigInteger('precio_por_bulto'); // en pesos (enteros)
            $table->unsignedBigInteger('total');            // guardamos total para reportes rápidos
            $table->date('fecha_compra')->nullable();
            $table->string('observacion', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('compras');
    }
};
