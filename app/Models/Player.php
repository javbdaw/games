<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    /**
     * Los atributos que SÍ se pueden rellenar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];
}