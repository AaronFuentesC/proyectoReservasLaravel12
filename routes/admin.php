<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return "Ejemplo";
})->middleware('auth','admin.only')->name('dashboard');
Route::resource('rooms',RoomController::class)->middleware(['admin.only','auth']);
Route::resource('items',ItemController::class)->middleware(['admin.only','auth']);
Route::resource('bookings',BookingController::class)->middleware(['admin.only','auth']);



