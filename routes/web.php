<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingController;


Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->middleware(['auth','admin.only'])->name('dashboard'); //Usamos el Middleware de admin.only ()
});

// Rutas para empleados (solo pueden ver y modificar sus reservas)
Route::middleware(['auth', 'role:employee'])->prefix('reservas')->name('public.bookings.')->group(function () {
    Route::get('/', [BookingController::class, 'mine'])->name('index'); // listado
    Route::get('/create', [BookingController::class, 'create'])->name('create'); // formulario crear
    Route::post('/', [BookingController::class, 'store'])->name('store'); // guardar
    Route::get('/{booking}/edit', [BookingController::class, 'edit'])->name('edit'); // formulario editar
    Route::put('/{booking}', [BookingController::class, 'update'])->name('update'); // actualizar
    Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy'); // eliminar
    Route::put('/{booking}/publish', [BookingController::class, 'publish'])->name('publish'); //Publicar reserva (pasar estado de draft a pending)
    Route::put('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel'); //Cancelar reserva (pasar de estado pending a cancelled)
});
Route::redirect('/register', '/login');

require __DIR__.'/settings.php';
