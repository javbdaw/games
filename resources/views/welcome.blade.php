<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dino Runner</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="antialiased bg-sky-700 text-gray-900 dark:text-gray-100" style="font-family: 'Press Start 2P', cursive">
    <div class="min-h-screen flex flex-col items-center justify-center gap-8 pb-10">
        <h1 class="text-4xl font-bold mb-8 [text-shadow:_4px_4px_0_#000]">🦖 DINO RUNNER</h1>

        <div class="w-full max-w-5xl flex flex-col md:flex-row justify-center gap-8 px-8">

            <div class="w-full md:w-1/2 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border-4 border-gray-200">

                <form method="POST" action="{{ route('player.register') }}">
                    @csrf
                    <h2 class="text-2xl font-semibold text-center mb-4">REGISTER</h2>
                    <div>
                        <label for="name_reg" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Choose your name</label>
                        <input id="name_reg" type="text" name="name"
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm mt-1"
                               required autofocus value="{{ old('name') }}">

                        @error('name', 'default')
                            <span class="text-sm text-red-600 mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            ¡REGISTER & PLAY!
                        </button>
                    </div>
                </form>

                <hr class="my-8 border-gray-600">

                <form method="POST" action="{{ route('player.login') }}">
                    @csrf
                    <h2 class="text-2xl font-semibold text-center mb-4">CONTINUE</h2>
                    <div>
                        <label for="name_login" class="block font-medium text-sm text-gray-700 dark:text-gray-300">My name</label>
                        <input id="name_login" type="text" name="name"
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm mt-1"
                               required>

                        @error('name', 'default')
                            <span class="text-sm text-red-600 mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            ¡LET'S PLAY!
                        </button>
                    </div>
                </form>
            </div>

            <div class="w-full md:w-1/2 px-10 py-10 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border-4 border-gray-200">
                <h2 class="text-2xl font-semibold text-center mb-4 text-gray-900 dark:text-gray-100">
                    Top 10 Players
                </h2>

                @if($highScores->isEmpty())
                    <p class="text-center text-gray-500 dark:text-gray-400">
                        There are no scores yet! Be the first!
                    </p>
                @else
                    <ol class="list-decimal list-inside space-y-3 text-gray-700 dark:text-gray-300">
                        @foreach($highScores as $index => $score)
                            @php
                                $estilo = 'text-grey';

                                if($index == 0) $estilo = 'text-yellow-300 text-sm font-bold'; // Oro
                                if($index == 1) $estilo = 'text-slate-300 text-sm font-bold';   // Plata
                                if($index == 2) $estilo = 'text-orange-300 text-sm font-bold';  // Bronce
                            @endphp
                            <li class="mb-2 {{ $estilo }} {{ $index > 2 ? 'text-xs' : '' }}">                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @endif

                                <span class="font-bold">{{ $score->player->name }}</span>
                                - {{ $score->points }} points
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
