<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('envios', function (Blueprint $table) {
            $table->unsignedBigInteger('ganancia_total')->default(0)->change();
        });
    }
    public function down(): void {
        Schema::table('envios', function (Blueprint $table) {
            $table->unsignedBigInteger('ganancia_total')->default(null)->change();
        });
    }
};
