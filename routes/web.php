<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

// Arahkan ke HomeController, bukan fungsi Closure langsung
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');