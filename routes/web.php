<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\JuegoAdivinaController;

// --- Rutas de Sesión del Jugador ---

// Muestra la portada con los formularios
Route::get('/', [PlayerController::class, 'welcome'])->name('welcome');

// Recibe el formulario de registro
Route::post('/register', [PlayerController::class, 'register'])->name('player.register');

// Recibe el formulario de login
Route::post('/login', [PlayerController::class, 'login'])->name('player.login');

// Cierra la sesión
Route::get('/logout', [PlayerController::class, 'logout'])->name('player.logout');

// --- Rutas del Juego (Protegidas) ---

// Agrupamos todas las rutas de juegos bajo el "guardia"
Route::middleware(['player.session'])->group(function () {

    Route::get('/juegos/adivina', [JuegoAdivinaController::class, 'index'])
        ->name('juegos.adivina');

    Route::post('/juegos/adivina/guardar', [JuegoAdivinaController::class, 'store'])
        ->name('juegos.adivina.store');
    
});

// --------------------------------------------------

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//require __DIR__.'/auth.php';
