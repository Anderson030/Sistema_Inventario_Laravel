<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ConductorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Redirige la raíz al listado de envíos y protege todo con auth.
*/

Route::redirect('/', '/envios');

// Dashboard opcional (Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas (requiere iniciar sesión)
Route::middleware('auth')->group(function () {

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Envíos
    Route::resource('envios', EnvioController::class)->except(['show']);
    Route::post('envios/{envio}/entregar', [EnvioController::class, 'entregar'])->name('envios.entregar');

    // Clientes y Proveedores
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::resource('proveedores', ProveedorController::class)->except(['show']);

    // Conductores
    Route::resource('conductores', ConductorController::class)->except(['show']);
    Route::post('conductores/{conductor}/asignar', [ConductorController::class, 'asignarEnvio'])
        ->name('conductores.asignar');
});

// Rutas de autenticación generadas por Breeze
require __DIR__ . '/auth.php';
