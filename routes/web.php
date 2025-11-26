<?php

use App\Http\Controllers\DinoPlayerController;
use App\Http\Controllers\JuegoDinoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\JuegoAdivinaController;

// --- Rutas de Sesión del Jugador ---

// Muestra la portada con los formularios
Route::get('/', [DinoPlayerController::class, 'welcome'])->name('welcome');

// Recibe el formulario de registro
Route::post('/register', [DinoPlayerController::class, 'register'])->name('player.register');

// Recibe el formulario de login
Route::post('/login', [DinoPlayerController::class, 'login'])->name('player.login');

// Cierra la sesión
Route::get('/logout', [DinoPlayerController::class, 'logout'])->name('player.logout');

// --- Rutas del Juego (Protegidas) ---

// Agrupamos todas las rutas de juegos bajo el "guardia"
Route::middleware(['player.session'])->group(function () {

    Route::get('/juegos/adivina', [JuegoAdivinaController::class, 'index'])
        ->name('juegos.adivina');

    Route::post('/juegos/adivina/guardar', [JuegoAdivinaController::class, 'store'])
        ->name('juegos.adivina.store');

    Route::get('/juegos/dino', [JuegoDinoController::class, 'index'])
        ->name('juegos.dino');

    Route::post('/juegos/dino/guardar', [JuegoDinoController::class, 'store'])
        ->name('juegos.dino.store');

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
