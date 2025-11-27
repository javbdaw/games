<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dino Runner Arcade</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/dino-styles.css') }}">
</head>
<body>

<div class="arcade-console">

    <h1 class="game-title">DINO RUNNER</h1>
    <p style="font-size: 12px; color: #aaa; margin-bottom: 10px">
        Player: {{ $player->name ?? 'Invitado' }}
    </p>

    <div class="screen-container">
        <div class="score-board">SCORE: <span id="score">0</span></div>

        <div class="intro-overlay" id="introScreen">
            <h1>DINO RUNNER</h1>
            <p class="blink">PRESS SPACE TO START</p>
        </div>

        <div id="speedUpMsg" class="speed-up-msg" style="display: none;">SPEED UP!</div>

        <canvas id="gameCanvas" width="800" height="300"></canvas>


        <div class="game-over-overlay" id="gameOver">
            <div class="game-over-box">
                <h2>GAME OVER</h2>
                <p class="final-score">SCORE:<span id="finalScoreDisplay">0</span></p>
                <p>PRESS SPACEBAR</p>
                <p>TO RESTART</p>
            </div>
        </div>

        <div class="scanline"></div> </div>

    <div class="controls-hint">
        <span class="key">SPACEBAR</span> OR <span class="key">↑</span> TO JUMP
        <span class="key">ESC</span> TO EXIT
    </div>
</div>

<script src="{{ asset('js/dino-script.js') }}"></script>
</body>
</html>
