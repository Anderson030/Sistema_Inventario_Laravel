<?php
// database/migrations/2025_10_15_000001_create_invoices_basic.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('invoices', function (Blueprint $t) {
      $t->id();
      // consecutivo interno
      $t->string('prefix', 10)->default('FE');
      $t->unsignedBigInteger('number');              // 1,2,3...
      $t->date('issue_date')->nullable();

      // Empresa (demo)
      $t->string('company_name');
      $t->string('company_nit');                     // ej: 900508008-5
      $t->string('company_address')->nullable();
      $t->string('company_phone')->nullable();
      $t->string('company_email')->nullable();

      // Cliente directo (simple, sin FK)
      $t->string('customer_name');
      $t->string('customer_doc')->nullable();
      $t->string('customer_email')->nullable();
      $t->string('customer_address')->nullable();

      // Totales y estado
      $t->unsignedBigInteger('subtotal')->default(0);    // en pesos enteros
      $t->unsignedBigInteger('tax_total')->default(0);   // si no usas IVA, se queda 0
      $t->unsignedBigInteger('grand_total')->default(0);
      $t->unsignedBigInteger('amount_paid')->default(0);
      $t->unsignedBigInteger('balance_due')->default(0);
      $t->enum('status',['OPEN','PARTIALLY_PAID','PAID','CANCELED'])->default('OPEN');

      $t->string('pdf_path')->nullable();
      $t->timestamps();

      $t->unique(['prefix','number']);
    });

    Schema::create('invoice_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
      $t->string('description');
      $t->string('unit')->default('UND');
      $t->decimal('qty',12,2);
      $t->unsignedBigInteger('unit_price');   // pesos enteros
      $t->unsignedBigInteger('line_total');   // qty*unit_price redondeado
      $t->timestamps();
    });

    Schema::create('payments', function (Blueprint $t) {
      $t->id();
      $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
      $t->date('paid_at');
      $t->unsignedBigInteger('amount');       // pesos enteros
      $t->string('method')->nullable();       // EFECTIVO/TRANSFERENCIA...
      $t->string('note',300)->nullable();
      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('payments');
    Schema::dropIfExists('invoice_items');
    Schema::dropIfExists('invoices');
  }
};
