<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Juego: Adivina</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <nav class="bg-white dark:bg-gray-800 shadow-md">
        <div class="max-w-xl mx-auto px-6 py-4 flex justify-between items-center">
            <span class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Jugando como: {{ $player->name }}
            </span>
            <a href="{{ route('player.logout') }}" class="text-sm text-gray-700 dark:text-gray-300 underline">Salir</a>
        </div>
    </nav>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h3 class="text-lg font-medium text-center mb-4">Adivina un número entre 1 y 100</h3>

                    <input 
                        type="number" 
                        id="guess-input" 
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                        placeholder="Introduce tu número...">

                    <button 
                        id="guess-button" 
                        class="w-full mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        ¡Adivinar!
                    </button>

                    <p id="feedback" class="text-center text-lg font-semibold mt-4 min-h-[1.5em] text-gray-900 dark:text-gray-100"></p>

                    <p id="attempts" class="text-center text-sm text-gray-500 mt-2">Intentos: 0</p>

                    <button 
                        id="restart-button" 
                        class="w-full mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded hidden">
                        Jugar de Nuevo
                    </button>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/juego-adivina.js') }}" defer></script>
</body>
</html>