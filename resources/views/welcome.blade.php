<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Juegos de Navidad</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center pt-6 sm:pt-0">
        <h1 class="text-4xl font-bold mb-8">Juegos de Navidad</h1>

        <div class="w-full max-w-4xl flex flex-col md:flex-row justify-center gap-6 px-6">

            <div class="w-full md:w-1/2 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                
                <form method="POST" action="{{ route('player.register') }}">
                    @csrf
                    <h2 class="text-2xl font-semibold text-center mb-4">Soy Nuevo</h2>
                    <div>
                        <label for="name_reg" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Elige tu Nombre</label>
                        <input id="name_reg" type="text" name="name" 
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm mt-1" 
                               required autofocus value="{{ old('name') }}">
                        
                        @error('name', 'default')
                            <span class="text-sm text-red-600 mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            ¡Registrar y Jugar!
                        </button>
                    </div>
                </form>

                <hr class="my-8 border-gray-600">

                <form method="POST" action="{{ route('player.login') }}">
                    @csrf
                    <h2 class="text-2xl font-semibold text-center mb-4">Ya tengo Usuario</h2>
                    <div>
                        <label for="name_login" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Mi Nombre</label>
                        <input id="name_login" type="text" name="name" 
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm mt-1" 
                               required>
                               
                        @error('name', 'default')
                            <span class="text-sm text-red-600 mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            ¡Entrar y Jugar!
                        </button>
                    </div>
                </form>
            </div>

            <div class="w-full md:w-1/2 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <h2 class="text-2xl font-semibold text-center mb-4 text-gray-900 dark:text-gray-100">
                    Top 10 - Adivina el Número
                </h2>
                
                @if($highScores->isEmpty())
                    <p class="text-center text-gray-500 dark:text-gray-400">
                        ¡Aún no hay puntuaciones! ¡Sé el primero!
                    </p>
                @else
                    <ol class="list-decimal list-inside space-y-3 text-gray-700 dark:text-gray-300">
                        @foreach($highScores as $index => $score)
                            <li class="text-lg">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @endif

                                <span class="font-bold">{{ $score->player->name }}</span>
                                - {{ $score->points }} puntos
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

        </div>
        </div>
</body>
</html>