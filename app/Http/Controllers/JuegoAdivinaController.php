<?php
namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Score;
use Illuminate\Http\Request;

class JuegoAdivinaController extends Controller
{
    // Muestra la página del juego
    public function index()
    {
        // Busca al jugador en sesión para mostrar su nombre
        $playerId = session('player_id');
        $player = Player::find($playerId);

        return view('juegos.adivina', ['player' => $player]);
    }

    // Guarda la puntuación (recibida por JS/AJAX)
    public function store(Request $request)
    {
        $request->validate([
            'puntuacion' => 'required|integer'
        ]);

        Score::create([
            'points'      => $request->puntuacion,
            'player_id'   => session('player_id') // Coge el ID de la sesión
        ]);

        return response()->json(['status' => 'success']);
    }
}