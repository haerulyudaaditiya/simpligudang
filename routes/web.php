<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BastController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/items/{item}/print-qr', [ItemController::class, 'printQr'])->name('items.print-qr');
    Route::get('/borrow-requests/{borrowRequest}/bast', [BastController::class, 'download'])->name('borrow-requests.bast');
});
