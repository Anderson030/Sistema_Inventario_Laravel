<?php

use Illuminate\Support\Facades\Route;

// Controladores existentes
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CompraController;

// Controladores de facturación básica
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

Route::redirect('/', '/envios');

// Dashboard (opcional)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas
Route::middleware('auth')->group(function () {

    // -----------------------------
    // Compras
    // -----------------------------
    Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
    Route::get('compras/{compra}/edit', [CompraController::class, 'edit'])->name('compras.edit');
    Route::put('compras/{compra}', [CompraController::class, 'update'])->name('compras.update');
    Route::delete('compras/{compra}', [CompraController::class, 'destroy'])->name('compras.destroy');

    // -----------------------------
    // Perfil
    // -----------------------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // -----------------------------
    // Envíos
    // -----------------------------
    Route::resource('envios', EnvioController::class)->except(['show']);
    Route::post('envios/{envio}/entregar', [EnvioController::class, 'entregar'])->name('envios.entregar');

    // Buscar envío y facturar desde envío (módulo de facturación)
    Route::get('facturacion', [InvoiceController::class, 'finder'])->name('facturacion.index');
    Route::get('facturacion/buscar', [InvoiceController::class, 'findByEnvio'])->name('facturacion.buscar');
    Route::post('envios/{envio}/facturar', [InvoiceController::class, 'fromEnvio'])->name('envios.facturar');

    // -----------------------------
    // Clientes y Proveedores
    // -----------------------------
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::resource('proveedores', ProveedorController::class)->except(['show']);

    // -----------------------------
    // Conductores + asignación
    // -----------------------------
    Route::resource('conductores', ConductorController::class)->except(['show']);
    Route::post('conductores/{conductor}/asignar', [ConductorController::class, 'asignarEnvio'])
        ->name('conductores.asignar');

    // =========================================================
    // FACTURACIÓN BÁSICA (editable, pagos parciales, PDF, email)
    // =========================================================
    // Facturas (CRUD mínimo)
    Route::resource('invoices', InvoiceController::class)
        ->only(['index','create','store','show','edit','update','destroy']);

    // Ítems de factura (agregar / eliminar)
    Route::post('invoices/{invoice}/items', [InvoiceController::class,'addItem'])->name('invoices.items.add');
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class,'removeItem'])->name('invoices.items.remove');

    // Pagos parciales
    Route::post('invoices/{invoice}/payments', [PaymentController::class,'store'])->name('invoices.payments.store');
    Route::delete('invoices/{invoice}/payments/{payment}', [PaymentController::class,'destroy'])->name('invoices.payments.destroy');

    // PDF y envío por correo
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class,'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/email', [InvoiceController::class,'sendEmail'])->name('invoices.email');
});

require __DIR__ . '/auth.php';
