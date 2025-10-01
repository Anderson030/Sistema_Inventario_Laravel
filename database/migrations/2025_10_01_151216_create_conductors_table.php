<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('tipo_documento', 10); // 'CC', 'PTT', etc
            $table->string('documento')->unique();
            $table->string('celular')->nullable();
            $table->string('foto')->nullable(); // path a la imagen
            $table->text('descripcion')->nullable(); // descripción del viaje/historial
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductores');
    }
};
