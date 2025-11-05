<?php

use Illuminate\Support\Facades\Route;

// Controladores principales
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CompraController;

// Facturación
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

// Dashboard
use App\Http\Controllers\DashboardController;

/* ---------------------------------------
| Raíz -> Envíos
|----------------------------------------*/
Route::redirect('/', '/envios');

/* ---------------------------------------
| Dashboard contable (con filtros de fecha)
|----------------------------------------*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/* ---------------------------------------
| Rutas protegidas (requieren auth)
|----------------------------------------*/
Route::middleware('auth')->group(function () {

    /* -----------------------------------
    | Compras (crear / editar / actualizar / eliminar)
    |------------------------------------*/
    Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
    Route::get('compras/{compra}/edit', [CompraController::class, 'edit'])->name('compras.edit');
    Route::put('compras/{compra}', [CompraController::class, 'update'])->name('compras.update');
    Route::delete('compras/{compra}', [CompraController::class, 'destroy'])->name('compras.destroy');

    /* -----------------------------------
    | Perfil de usuario
    |------------------------------------*/
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* -----------------------------------
    | Envíos (resource sin show) + marcar entrega
    |------------------------------------*/
    Route::resource('envios', EnvioController::class)->except(['show']);
    Route::post('envios/{envio}/entregar', [EnvioController::class, 'entregar'])->name('envios.entregar');

    /* -----------------------------------
    | Facturación
    |------------------------------------*/
    // Finder
    Route::get('facturacion', [InvoiceController::class, 'finder'])->name('facturacion.index');
    Route::get('facturacion/buscar', [InvoiceController::class, 'findByEnvio'])->name('facturacion.buscar');
    // Facturar desde un envío
    Route::post('envios/{envio}/facturar', [InvoiceController::class, 'fromEnvio'])->name('envios.facturar');

    // Invoices CRUD mínimo
    Route::resource('invoices', InvoiceController::class)
        ->only(['index','create','store','show','edit','update','destroy']);

    // Ítems de factura
    Route::post('invoices/{invoice}/items', [InvoiceController::class,'addItem'])->name('invoices.items.add');
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class,'removeItem'])->name('invoices.items.remove');

    // Pagos parciales
    Route::post('invoices/{invoice}/payments', [PaymentController::class,'store'])->name('invoices.payments.store');
    Route::delete('invoices/{invoice}/payments/{payment}', [PaymentController::class,'destroy'])->name('invoices.payments.destroy');

    // PDF + envío por correo
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class,'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/email', [InvoiceController::class,'sendEmail'])->name('invoices.email');

    /* -----------------------------------
    | Clientes (resource sin show)
    |------------------------------------*/
    Route::resource('clientes', ClienteController::class)->except(['show']);

    /* -----------------------------------
    | Proveedores (resource sin show)
    |------------------------------------*/
    Route::resource('proveedores', ProveedorController::class)
        ->except(['show'])
        ->parameters(['proveedores' => 'proveedor']);

    /* -----------------------------------
    | Conductores (resource sin show) + asignar envío
    |------------------------------------*/
    Route::resource('conductores', ConductorController::class)
        ->except(['show'])
        ->parameters(['conductores' => 'conductor']);
    Route::post('conductores/{conductor}/asignar', [ConductorController::class, 'asignarEnvio'])
        ->name('conductores.asignar');

    /* -----------------------------------
    | Caja (listado / crear / editar / actualizar / eliminar)
    |------------------------------------*/
    Route::get('/caja',                [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja',               [CajaController::class, 'store'])->name('caja.store');
    Route::get('/caja/{mov}/edit',     [CajaController::class, 'edit'])->name('caja.edit');     // editar un movimiento
    Route::put('/caja/{mov}',          [CajaController::class, 'update'])->name('caja.update'); // actualizar
    Route::delete('/caja/{mov}',       [CajaController::class, 'destroy'])->name('caja.destroy'); // eliminar
});

/* ---------------------------------------
| Auth scaffolding
|----------------------------------------*/
require __DIR__ . '/auth.php';
