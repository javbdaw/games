<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Score;
USE Illuminate\Http\Request;

class JuegoDinoController extends Controller
{
    public function index()
    {
        if(!session()->has('player_id')) {
            return redirect()->route('welcome');
        }

        $player = Player::find(session('player_id'));

        return view('juegos.dino', ['player' => $player]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'puntuacion' => 'required|integer',
        ]);

        Score::create([
            'points' => $request->puntuacion,
            'player_id' => session('player_id')
        ]);
        return response()->json(['status' => 'success']);
    }
}
