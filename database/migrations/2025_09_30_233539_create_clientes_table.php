<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',120);
            $table->string('documento',50)->nullable()->index();   // NIT/CC
            $table->string('telefono',50)->nullable();
            $table->string('email',120)->nullable();
            $table->string('direccion',180)->nullable();
            $table->string('ciudad',120)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('clientes');
    }
};
