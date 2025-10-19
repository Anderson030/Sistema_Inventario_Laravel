<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('envios', function (Blueprint $table) {
            if (!Schema::hasColumn('envios', 'tipo_grano')) {
                $table->enum('tipo_grano', ['premium','eco'])->default('premium')->after('cliente_id');
            }
        });
    }
    public function down(): void {
        Schema::table('envios', function (Blueprint $table) {
            if (Schema::hasColumn('envios', 'tipo_grano')) {
                $table->dropColumn('tipo_grano');
            }
        });
    }
};
