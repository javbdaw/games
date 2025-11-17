<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importa esto

class Score extends Model
{
    use HasFactory;
    protected $fillable = ['points', 'player_id'];

    /**
     * Obtiene el jugador (Player) al que pertenece esta puntuación.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}