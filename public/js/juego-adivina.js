// No necesitamos 'DOMContentLoaded' porque usamos "defer" en el <script>

// --- 1. Seleccionar Elementos ---
const guessInput = document.querySelector("#guess-input");
const guessButton = document.querySelector("#guess-button");
const feedback = document.querySelector("#feedback");
const attemptsText = document.querySelector("#attempts");
const restartButton = document.querySelector("#restart-button");

// --- 2. Estado del Juego (Variables) ---
let secretNumber;
let attempts;

function initGame() {
    secretNumber = Math.floor(Math.random() * 100) + 1;
    attempts = 0;
    feedback.textContent = "¡Buena suerte!";
    attemptsText.textContent = "Intentos: 0";
    guessInput.value = "";
    guessButton.classList.remove('hidden');
    guessInput.disabled = false;
    restartButton.classList.add('hidden');
}

// --- 3. Lógica Principal ---
function checkGuess() {
    const userGuess = parseInt(guessInput.value);

    if (isNaN(userGuess) || userGuess < 1 || userGuess > 100) {
        feedback.textContent = "Por favor, introduce un número del 1 al 100.";
        return;
    }

    attempts++;
    attemptsText.textContent = `Intentos: ${attempts}`;

    if (userGuess < secretNumber) {
        feedback.textContent = "¡Demasiado BAJO! 🥶";
    } else if (userGuess > secretNumber) {
        feedback.textContent = "¡Demasiado ALTO! 🔥";
    } else {
        feedback.textContent = `¡Correcto! El número era ${secretNumber}. Has tardado ${attempts} intentos.`;
        endGame();
    }
}

// --- 4. Fin del Juego y Guardado ---
function endGame() {
    guessButton.classList.add('hidden');
    guessInput.disabled = true;
    restartButton.classList.remove('hidden');
    const score = Math.max(0, 100 - (attempts * 5));
    saveScore(score);
}

// --- 5. Event Listeners ---
guessButton.addEventListener('click', checkGuess);
guessInput.addEventListener('keyup', (event) => {
    if (event.key === 'Enter') checkGuess();
});
restartButton.addEventListener('click', initGame);

// --- 6. AJAX (Fetch) para Guardar Score ---
async function saveScore(puntuacion) {

    const token = document.querySelector('meta[name="csrf-token"]').content;
    const url = "/juegos/adivina/guardar"; // ¡Coincide con nuestra ruta POST!

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ puntuacion: puntuacion })
        });

        const data = await response.json();

        if(data.status === 'success') {
            console.log('Puntuación guardada.');
            feedback.textContent += " ¡Puntuación guardada!";
        }
    } catch (error) {
        console.error('Error al guardar la puntuación:', error);
        feedback.textContent += " (Error al guardar puntuación)";
    }
}

// --- Empezar el juego por primera vez ---
initGame();