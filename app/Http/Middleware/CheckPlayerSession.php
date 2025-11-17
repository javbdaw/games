<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPlayerSession
{
    public function handle(Request $request, Closure $next)
    {
        // Si la sesión NO tiene 'player_id', lo echamos a la portada
        if (!session()->has('player_id')) {
            return redirect('/');
        }
        
        // Si la tiene, déjalo pasar
        return $next($request);
    }
}