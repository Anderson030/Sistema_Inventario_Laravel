<?php

use Illuminate\Support\Facades\Route;

// Controladores principales
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CompraController;

// Facturación básica
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

// Dashboard (nuevo)
use App\Http\Controllers\DashboardController;

/* ---------------------------------------
| Redirección raíz -> Envíos
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
    Route::post('compras', [CompraController::class, 'store'])->name('compras.store');               // Crear
    Route::get('compras/{compra}/edit', [CompraController::class, 'edit'])->name('compras.edit');    // Form editar
    Route::put('compras/{compra}', [CompraController::class, 'update'])->name('compras.update');     // Actualizar
    Route::delete('compras/{compra}', [CompraController::class, 'destroy'])->name('compras.destroy');// Eliminar

    /* -----------------------------------
    | Perfil de usuario (ver/editar, actualizar, eliminar)
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
    | Facturación: finder + buscar por envío + facturar desde envío
    |------------------------------------*/
    Route::get('facturacion', [InvoiceController::class, 'finder'])->name('facturacion.index');
    Route::get('facturacion/buscar', [InvoiceController::class, 'findByEnvio'])->name('facturacion.buscar');
    Route::post('envios/{envio}/facturar', [InvoiceController::class, 'fromEnvio'])->name('envios.facturar');

    /* -----------------------------------
    | Clientes (resource sin show)
    |------------------------------------*/
    Route::resource('clientes', ClienteController::class)->except(['show']);

    /* -----------------------------------
    | Proveedores (resource sin show)
    | * Forzamos el nombre del parámetro a {proveedor}
    |------------------------------------*/
    Route::resource('proveedores', ProveedorController::class)
        ->except(['show'])
        ->parameters(['proveedores' => 'proveedor']);

    /* -----------------------------------
    | Conductores (resource sin show)
    | * Forzamos el nombre del parámetro a {conductor}
    |------------------------------------*/
    Route::resource('conductores', ConductorController::class)
        ->except(['show'])
        ->parameters(['conductores' => 'conductor']);

    /* -----------------------------------
    | Conductores: asignar envío a un conductor
    |------------------------------------*/
    Route::post('conductores/{conductor}/asignar', [ConductorController::class, 'asignarEnvio'])
        ->name('conductores.asignar');

    /* -----------------------------------
    | Invoices (CRUD mínimo)
    | index/create/store/show/edit/update/destroy
    |------------------------------------*/
    Route::resource('invoices', InvoiceController::class)
        ->only(['index','create','store','show','edit','update','destroy']);

    /* -----------------------------------
    | Ítems de factura (agregar / eliminar)
    |------------------------------------*/
    Route::post('invoices/{invoice}/items', [InvoiceController::class,'addItem'])->name('invoices.items.add');
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class,'removeItem'])->name('invoices.items.remove');

    /* -----------------------------------
    | Pagos parciales (registrar / eliminar)
    |------------------------------------*/
    Route::post('invoices/{invoice}/payments', [PaymentController::class,'store'])->name('invoices.payments.store');
    Route::delete('invoices/{invoice}/payments/{payment}', [PaymentController::class,'destroy'])->name('invoices.payments.destroy');

    /* -----------------------------------
    | PDF de factura + envío por correo
    |------------------------------------*/
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class,'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/email', [InvoiceController::class,'sendEmail'])->name('invoices.email');
});

/* ---------------------------------------
| Auth scaffolding (Login/Registro/etc.)
|----------------------------------------*/
require __DIR__ . '/auth.php';
