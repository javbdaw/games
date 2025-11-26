/**
 * DINO RUNNER - Script Principal
 * Controla la lógica del juego, renderizado y actualizaciones.
 */

// ==========================================
// 1. CONFIGURACIÓN INICIAL Y VARIABLES DOM
// ==========================================
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const scoreEl = document.getElementById('score');
const introEl = document.getElementById('introScreen');
const gameOverEl = document.getElementById('gameOver');
const speedUpEl = document.getElementById('speedUpMsg');

// Configuración del suelo
const groundY = 280;
const groundImageYOffset = 8;
let groundX = 0;

// Variables de estado del sistema
let debugMode = false;
let assetsLoaded = 0;
const totalAssets = 6;

// ==========================================
// 2. CARGA DE ASSETS (IMÁGENES)
// ==========================================

/**
 * Se ejecuta cada vez que una imagen termina de cargar.
 * Inicia el juego solo cuando todos los assets están listos.
 */
function onAssetLoad() {
    assetsLoaded++;
    if (assetsLoaded === totalAssets) {
        gameLoop();
    }
}

// Definición y carga de imágenes
const dinoSprite = new Image();
dinoSprite.onload = onAssetLoad;
dinoSprite.src = '/img/dino.png';

const cactus1Sprite = new Image();
cactus1Sprite.onload = onAssetLoad;
cactus1Sprite.src = '/img/cactus1.png';

const cactus2Sprite = new Image();
cactus2Sprite.onload = onAssetLoad;
cactus2Sprite.src = '/img/cactus2.png';

const groundSprite = new Image();
groundSprite.onload = onAssetLoad;
groundSprite.src = '/img/suelo.png';

const cloudSprite = new Image();
cloudSprite.onload = onAssetLoad;
cloudSprite.src = '/img/nube.png';

const mountainSprite = new Image();
mountainSprite.onload = onAssetLoad;
mountainSprite.src = '/img/mountain.png';

// ==========================================
// 3. ESTADO DEL JUEGO
// ==========================================
let gameState = {
    isRunning: false,
    isGameOver: false,
    score: 0,
    frameCount: 0,
    gameSpeed: 6
};

// ==========================================
// 4. OBJETO JUGADOR (DINO)
// ==========================================
const dino = {
    // Posición y dimensiones
    x: 84,
    y: groundY - 84,
    width: 84,
    height: 84,

    // Física
    dy: 0,
    gravity: 0.8,
    jumpPower: -15.2,
    isJumping: false,

    // Animación
    spriteWidth: 84,
    totalFrames: 4,
    currentFrame: 0,
    animationTimer: 0,
    animationSpeed: 6,

    /**
     * Dibuja al dinosaurio en el canvas recortando el sprite correcto.
     */
    draw() {
        const frameX = this.currentFrame * this.spriteWidth;

        ctx.drawImage(
            dinoSprite,
            frameX, 0,
            this.spriteWidth, this.height,
            this.x, this.y,
            this.width, this.height
        );
    },

    /**
     * Aplica fuerza de salto si está en el suelo.
     */
    jump() {
        if (!this.isJumping) {
            audio.play(audio.jump)
            this.dy = this.jumpPower;
            this.isJumping = true;
        }
    },

    /**
     * Actualiza posición física y animación.
     */
    update() {
        // Lógica de animación
        if (gameState.isRunning && !gameState.isGameOver) {
            this.animationTimer++;
            if (this.animationTimer > this.animationSpeed) {
                if (this.currentFrame === 1) {
                    this.currentFrame = 2;
                } else {
                    this.currentFrame = 1;
                }
                this.animationTimer = 0;
            }
            if (this.isJumping) {
                this.currentFrame = 0;
            }
        }

        // Lógica de física
        this.dy += this.gravity;
        this.y += this.dy;

        // Colisión con el suelo
        const restingY = groundY - this.height;
        if (this.y >= restingY) {
            this.y = restingY;
            this.dy = 0;
            this.isJumping = false;
        }
    },

    /**
     * Devuelve el rectángulo de colisión (hitbox) ajustado.
     */
    getHitbox() {
        return {
            x: this.x + 20,
            y: this.y + 10,
            width: 45,
            height: 60
        };
    }
};

// ==========================================
// 5. GESTIÓN DE OBSTÁCULOS
// ==========================================
const obstacles = [];
let obstacleTimer = 0;
let obstacleInterval = 100;

class Obstacle {
    constructor() {
        this.x = canvas.width;

        // Selección aleatoria de cactus
        if (Math.random() > 0.5) {
            this.sprite = cactus1Sprite;
            this.width = 46;
            this.height = 96;
        } else {
            this.sprite = cactus2Sprite;
            this.width = 98;
            this.height = 66;
        }

        this.y = groundY - this.height;
    }

    draw() {
        ctx.drawImage(this.sprite, this.x, this.y, this.width, this.height);
    }

    update() {
        this.x -= gameState.gameSpeed;
    }

    isOffScreen() {
        return this.x + this.width < 0;
    }

    getHitbox() {
        return {
            x: this.x + 15,
            y: this.y + 5,
            width: this.width - 30,
            height: this.height - 10
        };
    }

    collidesWith(dino) {
        const dinoBox = dino.getHitbox();
        const obstacleBox = this.getHitbox();

        // Algoritmo AABB (Axis-Aligned Bounding Box)
        return (
            dinoBox.x < obstacleBox.x + obstacleBox.width &&
            dinoBox.x + dinoBox.width > obstacleBox.x &&
            dinoBox.y < obstacleBox.y + obstacleBox.height &&
            dinoBox.y + dinoBox.height > obstacleBox.y
        );
    }
}

// ==========================================
// 6. GESTIÓN DE NUBES
// ==========================================
const clouds = [];
let cloudTimer = 0;
let cloudInterval = Math.floor(Math.random() * 100) + 50;

class Cloud {
    constructor() {
        this.x = canvas.width;

        // Factor de escala aleatorio (80% - 130%)
        const scale = Math.random() * 0.5 + 0.8;

        // Tamaño base
        const baseWidth = 80;
        const baseHeight = 30;

        // Aplicar escala
        this.width = Math.floor(baseWidth * scale);
        this.height = Math.floor(baseHeight * scale);

        // Posición Y aleatoria en el cielo
        this.y = Math.floor(Math.random() * 80) + 10;

        // Velocidad basada en escala (efecto parallax)
        this.speedFactor = 0.3 * scale;
    }

    draw() {
        ctx.globalAlpha = 0.8;
        ctx.drawImage(cloudSprite, this.x, this.y, this.width, this.height);
        ctx.globalAlpha = 1;
    }

    update() {
        this.x -= gameState.gameSpeed * this.speedFactor;
    }

    isOffScreen() {
        return this.x + this.width < 0;
    }
}

function spawnCloud() {
    if (gameState.isRunning && !gameState.isGameOver) {
        cloudTimer++;
        if (cloudTimer > cloudInterval) {
            clouds.push(new Cloud());
            cloudTimer = 0;
            cloudInterval = Math.floor(Math.random() * 200) + 100;
        }
    }
}

// ==========================================
// 7. GESTIÓN DE MONTAÑAS (PARALLAX)
// ==========================================
const mountains = [];
let mountainTimer = 0;
let mountainInterval = Math.floor(Math.random() * 400) + 100;

class Mountain {
    constructor() {
        this.x = canvas.width;

        // Factor de escala aleatorio (50% - 130%)
        const scale = Math.random() * 0.8 + 0.5;

        // Dimensiones base
        const baseWidth = 320;
        const baseHeight = 120;

        // Aplicar escala
        this.width = Math.floor(baseWidth * scale);
        this.height = Math.floor(baseHeight * scale);

        // Posición Y ajustada al suelo
        this.y = groundY - this.height + 10;

        // Velocidad basada en escala (efecto parallax)
        this.speedFactor = 0.15 * scale;
    }

    draw() {
        ctx.globalAlpha = 0.8;
        ctx.drawImage(mountainSprite, this.x, this.y, this.width, this.height);
        ctx.globalAlpha = 1.0;
    }

    update() {
        this.x -= gameState.gameSpeed * this.speedFactor;
    }

    isOffScreen() {
        return this.x + this.width < 0;
    }
}

function spawnMountain() {
    if (gameState.isRunning && !gameState.isGameOver) {
        mountainTimer++;
        if (mountainTimer > mountainInterval) {
            mountains.push(new Mountain());
            mountainTimer = 0;
            mountainInterval = Math.floor(Math.random() * 600) + 300;
        }
    }
}

// ==========================================
// 8. FUNCIONES DE DIBUJADO Y LÓGICA
// ==========================================

/**
 * Dibuja el suelo infinito.
 */
function drawGround() {
    const drawY = groundY - groundImageYOffset;
    ctx.drawImage(groundSprite, groundX, drawY);
    ctx.drawImage(groundSprite, groundX + groundSprite.width, drawY);
}

/**
 * Dibuja los rectángulos de colisión en modo debug (Tecla 'D').
 */
function drawDebugHitboxes() {
    if (!debugMode) return;

    // Hitbox del dino (azul)
    const dinoBox = dino.getHitbox();
    ctx.strokeStyle = 'blue';
    ctx.lineWidth = 2;
    ctx.strokeRect(dinoBox.x, dinoBox.y, dinoBox.width, dinoBox.height);

    // Hitbox de obstáculos (rojo)
    ctx.strokeStyle = 'red';
    ctx.lineWidth = 2;
    for (let i = 0; i < obstacles.length; i++) {
        const obstacleBox = obstacles[i].getHitbox();
        ctx.strokeRect(obstacleBox.x, obstacleBox.y, obstacleBox.width, obstacleBox.height);
    }
}

/**
 * Actualiza la puntuación y aumenta la dificultad progresivamente.
 */
function updateScore() {
    if (gameState.isRunning && !gameState.isGameOver) {
        gameState.score++;
        const currentScore = Math.floor(gameState.score / 10);
        scoreEl.textContent = currentScore;

        // Aumentar velocidad cada 100 puntos
        if (currentScore > 0 && currentScore % 100 === 0 && gameState.score % 10 === 0) {
            audio.play(audio.score)
            gameState.gameSpeed += 1;
            showSpeedUpEffect();
        }

        // Sistema de ciclos de fases visuales (Día, Atardecer, Noche, Neón)
        const cycleScore = currentScore % 400;

        if (cycleScore >= 300) {
            canvas.className = 'phase-neon';
        } else if (cycleScore >= 200) {
            canvas.className = 'phase-night';
        } else if (cycleScore >= 100) {
            canvas.className = 'phase-sunset';
        } else {
            canvas.className = '';
        }
    }
}

/**
 * Genera nuevos obstáculos en intervalos aleatorios.
 */
function spawnObstacle() {
    if (gameState.isRunning && !gameState.isGameOver) {
        obstacleTimer++;
        if (obstacleTimer > obstacleInterval) {
            obstacles.push(new Obstacle());
            obstacleTimer = 0;
            obstacleInterval = Math.floor(Math.random() * 40) + 50;
        }
    }
}

let speedUpTimeout;

/**
 * Muestra el mensaje de aumento de velocidad.
 */
function showSpeedUpEffect() {
    if (!speedUpEl) return;

    speedUpEl.style.display = 'block';
    clearTimeout(speedUpTimeout);

    speedUpTimeout = setTimeout(() => {
        speedUpEl.style.display = 'none';
    }, 2000);
}

/**
 * Maneja el estado de Game Over.
 */
function endGame() {
    audio.play(audio.gameOver)
    audio.play(audio.die);
    gameState.isGameOver = true;
    gameState.isRunning = false;
    gameOverEl.style.display = 'block';
    dino.currentFrame = 3;

    if (speedUpEl) speedUpEl.style.display = 'none';
    clearTimeout(speedUpTimeout);

    const finalScore = Math.floor(gameState.score / 10);

    if(finalScore > 0) {
        saveScore(finalScore);
    }
}

/**
 * Guarda la puntuación en la base de datos vía AJAX.
 */
async function saveScore(puntuacion) {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const token = tokenMeta ? tokenMeta.content : '';
    const url = "/juegos/dino/guardar";

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
            console.log('✅ Puntuación guardada correctamente en la base de datos.');
        }
    } catch (error) {
        console.error('❌ Error al guardar la puntuación:', error);
    }
}

/**
 * Reinicia todas las variables para una nueva partida.
 */
function restart() {
    gameState = {
        isRunning: false,
        isGameOver: false,
        score: 0,
        frameCount: 0,
        gameSpeed: 6
    };

    introEl.style.display = 'flex';

    clouds.length = 0;
    cloudTimer = 0;

    mountains.length = 0;
    mountainTimer = 0;

    obstacles.length = 0;
    obstacleTimer = 0;
    obstacleInterval = 100;

    dino.y = groundY - dino.height;
    dino.dy = 0;
    dino.isJumping = false;
    dino.currentFrame = 0;
    dino.animationTimer = 0;

    groundX = 0;

    gameOverEl.style.display = 'none';
    scoreEl.textContent = '0';

    canvas.className = '';
}

// ==========================================
// 9. BUCLE PRINCIPAL (GAME LOOP)
// ==========================================
function gameLoop() {
    // Limpiar canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Lógica de actualización
    if (gameState.isRunning && !gameState.isGameOver) {
        gameState.frameCount++;

        dino.update();
        updateScore();

        // Movimiento del suelo infinito
        groundX -= gameState.gameSpeed;
        if (groundX <= -groundSprite.width) {
            groundX += groundSprite.width;
        }

        // Nubes
        spawnCloud();
        for (let i = clouds.length - 1; i >= 0; i--) {
            clouds[i].update();
            if (clouds[i].isOffScreen()) clouds.splice(i, 1);
        }

        // Montañas
        spawnMountain();
        for (let i = mountains.length - 1; i >= 0; i--) {
            mountains[i].update();
            if (mountains[i].isOffScreen()) mountains.splice(i, 1);
        }

        // Obstáculos
        spawnObstacle();
        for (let i = obstacles.length - 1; i >= 0; i--) {
            obstacles[i].update();
            if (obstacles[i].collidesWith(dino)) endGame();
            if (obstacles[i].isOffScreen()) obstacles.splice(i, 1);
        }
    }

    // Renderizado (orden de capas crítico)
    clouds.forEach(cloud => cloud.draw());
    mountains.forEach(mountain => mountain.draw());
    for (let i = 0; i < obstacles.length; i++) {
        obstacles[i].draw();
    }
    drawGround();
    dino.draw();
    drawDebugHitboxes();

    requestAnimationFrame(gameLoop);
}

// ==========================================
// 10. LISTENERS DE EVENTOS (INPUT)
// ==========================================

// Teclado
document.addEventListener('keydown', (e) => {
    if (e.code === 'Space' || e.code === 'ArrowUp') {
        e.preventDefault();

        if (gameState.isGameOver) {
            restart();
        } else {
            if (!gameState.isRunning) {
                gameState.isRunning = true;
                if (introEl) introEl.style.display = 'none';
            }
            dino.jump();
        }
    }

    // Activar/Desactivar modo debug con 'D'
    if (e.code === 'KeyD') {
        e.preventDefault();
        debugMode = !debugMode;
    }

    // Cerrar sesión con 'Escape'
    if(e.code === 'Escape') {
        e.preventDefault();
        const logoutBtn = document.getElementById('Btn-logout');

        if(logoutBtn) {
            logoutBtn.click();
        } else {
            console.warn("Botón no encontrado");
            window.location.href = '/logout';
        }
    }
});

// Click
document.addEventListener('mousedown', (e) => {
    if (gameState.isGameOver) {
        restart();
    } else {
        if (!gameState.isRunning) {
            gameState.isRunning = true;
            if (introEl) introEl.style.display = 'none';
        }
        dino.jump();
    }
});

// ==========================================
// 11. SISTEMA DE AUDIO
// ==========================================

const audio = {
    jump: new Audio('/audio/jump.mp3'),
    die: new Audio('/audio/die2.mp3'),
    score: new Audio('/audio/score.mp3'),
    gameOver: new Audio('/audio/gameover.mp3'),

    initializeVolume() {
        this.jump.volume = 0.5;
        this.die.volume = 0.8;
        this.score.volume = 0.9;
        this.gameOver.volume = 0.6;
    },

    /**
     * Reproduce un sonido inmediatamente.
     */
    play(sound) {
        sound.currentTime = 0;
        sound.play().catch(e => console.warn("Error al reproducir audio:", e.message));
    }
};

audio.initializeVolume();
