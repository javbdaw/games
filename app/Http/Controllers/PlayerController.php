<?php
namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; // Para manejar la sesión

class PlayerController extends Controller
{
    /**
     * Muestra la portada con los 2 formularios.
     */
    public function welcome()
    {
        // Si el jugador ya está logueado, lo mandamos al juego
        if (session()->has('player_id')) {
            return redirect()->route('juegos.adivina');
        }
        // 1. Buscamos el Top 10
        $highScores = Score::with('player') // 'player' es el nombre del método que creamos
                        ->orderBy('points', 'desc') // Ordenar por puntos (más altos primero)
                        ->take(10) // Tomar solo 10
                        ->get();

        // 2. Pasamos los scores a la vista
        return view('welcome', [
            'highScores' => $highScores
        ]);
    }

    /**
     * Procesa el formulario de REGISTRO.
     */
    public function register(Request $request)
    {
        // 1. Validar (required, min 3 letras, y UNICO en la tabla)
        $request->validate([
            'name' => 'required|string|min:3|unique:players,name'
        ]);

        // 2. Crear el jugador
        $player = Player::create([
            'name' => $request->name
        ]);

        // 3. Iniciar sesión (guardando el ID en la sesión)
        session(['player_id' => $player->id]);

        // 4. Redirigir al juego
        return redirect()->route('juegos.adivina');
    }

    /**
     * Procesa el formulario de LOGIN.
     */
    public function login(Request $request)
    {
        // 1. Validar (required y DEBE EXISTIR en la tabla)
        $request->validate([
            'name' => 'required|string|exists:players,name'
        ]);

        // 2. Buscar al jugador
        $player = Player::where('name', $request->name)->first();

        // 3. Iniciar sesión
        session(['player_id' => $player->id]);

        // 4. Redirigir al juego
        return redirect()->route('juegos.adivina');
    }

    /**
     * Cierra la sesión del jugador.
     */
    public function logout()
    {
        session()->forget('player_id');
        return redirect('/');
    }
}