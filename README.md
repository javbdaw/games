# 🦖 Dino Runner - Game Loop Architecture

Una recreación completa del juego del dinosaurio de Chrome. Implementa un motor de juego construido
desde cero con JavaScript (Vanilla) y la Canvas API para el frontend, integrado con un backend en
Laravel para la gestión de sesiones de usuario y persistencia de puntuaciones (High Scores) en base de
datos.

# 🎮 Características Principales
- Motor de física personalizado con gravedad, aceleración y salto realista
- Sistema de colisiones AABB (Axis-Aligned Bounding Box) con hitboxes ajustables
- Efectos parallax multicapa (nubes y montañas con velocidades diferenciadas)
- Progresión dinámica: velocidad incremental cada 100 puntos
- Ciclos visuales: 4 fases temáticas (día → atardecer → noche → neón)
- Sistema de audio integrado con efectos de salto, muerte y logros
- Persistencia de datos con guardado automático de puntuaciones vía AJAX
- Modo debug para visualizar hitboxes (tecla D)
- Generación procedural de entorno: variabilidad aleatoria en tamaño y posición de elementos
- Animación de sprites por cuadros (frames) para el movimiento del personaje
- Interfaz reactiva: Pantalla de intro y notificaciones visuales (Speed Up)

## El Corazón del Motor: `gameLoop()`

El **Game Loop** es el componente central que orquesta todo el ciclo de vida del juego mediante un patrón de ejecución continua sincronizado con el refresh rate del navegador. Este bucle implementa la arquitectura clásica **Update-Render** separando claramente la lógica de negocio del renderizado visual.

---

## Anatomía del Game Loop
```javascript
function gameLoop() {
    // 1. FASE DE LIMPIEZA
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 2. FASE DE ACTUALIZACIÓN (UPDATE)
    if (gameState.isRunning && !gameState.isGameOver) {
        gameState.frameCount++;

        dino.update();
        ground.update();
        updateScore();

        // Gestión de Nubes
        spawnCloud();
        for (let i = clouds.length - 1; i >= 0; i--) {
            clouds[i].update();
            if (clouds[i].isOffScreen()) clouds.splice(i, 1);
        }

        // Gestión de Montañas
        spawnMountain();
        for (let i = mountains.length - 1; i >= 0; i--) {
            mountains[i].update();
            if (mountains[i].isOffScreen()) mountains.splice(i, 1);
        }

        // Gestión de Obstáculos
        spawnObstacle();
        for (let i = obstacles.length - 1; i >= 0; i--) {
            obstacles[i].update();
            if (obstacles[i].collidesWith(dino)) endGame();
            if (obstacles[i].isOffScreen()) obstacles.splice(i, 1);
        }
    }

    // 3. FASE DE RENDERIZADO (RENDER)
    clouds.forEach(cloud => cloud.draw());
    mountains.forEach(mountain => mountain.draw());
    obstacles.forEach(obstacle => obstacle.draw());
    ground.draw();
    dino.draw();
    drawDebugHitboxes();

    // 4. FASE DE SCHEDULING
    requestAnimationFrame(gameLoop);
}
```

---

## Fases del Game Loop

### **1. Fase de Limpieza (Clear Phase)**
```javascript
ctx.clearRect(0, 0, canvas.width, canvas.height);
```

**Propósito:** Eliminar completamente el contenido del frame anterior del canvas.

**Proceso:**
- Borra todos los píxeles en el área del canvas (800×300 px)
- Resetea el buffer de dibujo a transparente
- Previene el "ghosting" visual (superposición de frames)

**Criticidad:** Esta operación **debe ejecutarse incondicionalmente** en cada iteración, independientemente del estado del juego (`isRunning`, `isGameOver`). Incluso cuando el juego está pausado, el bucle continúa limpiando y redibujando para mantener la UI responsiva.

---

### **2. Fase de Actualización (Update Phase)**

Esta fase **solo se ejecuta** cuando el juego está activo:
```javascript
if (gameState.isRunning && !gameState.isGameOver) {
    // Lógica de actualización...
}
```

#### **2.1. Actualización del Estado Global**
```javascript
gameState.frameCount++;
```

**Función:** Contador monotónico que rastrea el número total de frames procesados desde el inicio de la partida.

**Usos:**
- Base temporal para sistemas de spawn probabilísticos
- Debugging y profiling de rendimiento
- Sincronización de eventos temporales

#### **2.2. Actualización de Entidades Core**
```javascript
dino.update();
ground.update();
updateScore();
```

**Orden de ejecución crítico:**

1. **`dino.update()`** - Procesa física (gravedad, colisión con suelo) y animación del sprite
2. **`ground.update()`** - Actualiza posición horizontal del suelo infinito
3. **`updateScore()`** - Incrementa puntuación y maneja escalado de velocidad

**Secuencialidad:** Este orden garantiza que la física del jugador se resuelva antes de verificar colisiones con obstáculos.

#### **2.3. Sistema de Gestión de Entidades Dinámicas**

El loop implementa un **patrón de gestión de pools de objetos** para tres tipos de entidades: nubes, montañas y obstáculos. Cada tipo sigue el mismo flujo de 4 pasos:

**Patrón Genérico:**
```javascript
// PASO 1: Spawn (Generación probabilística)
spawnEntity();

// PASO 2: Update (Actualización de estado)
for (let i = entities.length - 1; i >= 0; i--) {
    entities[i].update();
    
    // PASO 3: Collision Check (solo obstáculos)
    if (entities[i].collidesWith(dino)) endGame();
    
    // PASO 4: Cleanup (Eliminación de entidades fuera de pantalla)
    if (entities[i].isOffScreen()) entities.splice(i, 1);
}
```

##### **Análisis de cada Subsistema:**

**A) Gestión de Nubes (Clouds)**
```javascript
spawnCloud();
for (let i = clouds.length - 1; i >= 0; i--) {
    clouds[i].update();
    if (clouds[i].isOffScreen()) clouds.splice(i, 1);
}
```

**Lógica de `spawnCloud()`:**
```javascript
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
```

- **Timer incremental:** `cloudTimer++` se ejecuta cada frame
- **Condición de spawn:** Cuando `cloudTimer > cloudInterval` (entre 100-300 frames)
- **Reset del timer:** Después del spawn, `cloudTimer = 0` y se genera un nuevo `cloudInterval` aleatorio
- **Resultado:** Aparición impredecible de nubes cada ~1.6-5 segundos a 60 FPS

**Método `update()` de Cloud:**
```javascript
update() {
    this.x -= gameState.gameSpeed * this.speedFactor; // speedFactor = 0.3 * scale
}
```

Las nubes se mueven al 30% de la velocidad base (`gameSpeed`), multiplicado por su factor de escala individual, creando **parallax multi-capa**.

**B) Gestión de Montañas (Mountains)**
```javascript
spawnMountain();
for (let i = mountains.length - 1; i >= 0; i--) {
    mountains[i].update();
    if (mountains[i].isOffScreen()) mountains.splice(i, 1);
}
```

**Diferencias clave con nubes:**

- **Intervalo más largo:** `mountainInterval = Math.floor(Math.random() * 600) + 300` (300-900 frames)
- **Velocidad menor:** `speedFactor = 0.15 * scale` (15% de `gameSpeed`)
- **Efecto visual:** Montañas en el fondo lejano se mueven más lento que nubes intermedias

**C) Gestión de Obstáculos (Obstacles)**
```javascript
spawnObstacle();
for (let i = obstacles.length - 1; i >= 0; i--) {
    obstacles[i].update();
    if (obstacles[i].collidesWith(dino)) endGame();
    if (obstacles[i].isOffScreen()) obstacles.splice(i, 1);
}
```

**Componente crítico adicional:** **Detección de Colisiones**
```javascript
if (obstacles[i].collidesWith(dino)) endGame();
```

**Flujo de colisión:**

1. **Invocación:** Se llama al método `collidesWith(dino)` del obstáculo
2. **Algoritmo AABB:** Verifica superposición de hitboxes (ver detalles en la sección siguiente)
3. **Resultado:** Si retorna `true`, se ejecuta `endGame()` inmediatamente
4. **Consecuencia:** El estado cambia a `gameState.isGameOver = true`, deteniendo futuras actualizaciones

**Lógica de `spawnObstacle()`:**
```javascript
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
```

- **Intervalo dinámico:** Entre 50-90 frames (~0.83-1.5 segundos a 60 FPS)
- **Variación aleatoria del sprite:** El constructor de `Obstacle` elige aleatoriamente entre `cactus1Sprite` (46×96px) o `cactus2Sprite` (98×66px)

#### **2.4. Iteración Reversa en Arrays**

**Patrón repetido en el código:**
```javascript
for (let i = entities.length - 1; i >= 0; i--) {
    // ...
    if (condition) entities.splice(i, 1);
}
```

**Razón técnica:** Iterar desde el final hacia el inicio previene errores de índice al eliminar elementos durante la iteración.

**Problema con iteración normal (forward):**
```javascript
// ❌ INCORRECTO
for (let i = 0; i < entities.length; i++) {
    if (condition) entities.splice(i, 1);
    // Después de splice(), el elemento en i+1 se mueve a i
    // La siguiente iteración (i++) salta ese elemento
}
```

**Solución con iteración reversa:**
```javascript
// ✅ CORRECTO
for (let i = entities.length - 1; i >= 0; i--) {
    if (condition) entities.splice(i, 1);
    // Los elementos anteriores (i-1, i-2...) no se ven afectados
}
```

**Ejemplo visual:**
```
Array inicial: [A, B, C, D, E]
                     ^
                   i = 2
Eliminar C: [A, B, D, E]
            ^
          i = 1 (siguiente iteración)
// ✅ No se salta ningún elemento
```

---

### **3. Fase de Renderizado (Render Phase)**
```javascript
clouds.forEach(cloud => cloud.draw());
mountains.forEach(mountain => mountain.draw());
obstacles.forEach(obstacle => obstacle.draw());
ground.draw();
dino.draw();
drawDebugHitboxes();
```

**Característica crítica:** Esta fase se ejecuta **incondicionalmente** en cada frame, sin verificar `gameState.isRunning`.

**Razón:** Mantener la visualización actualizada incluso cuando el juego está pausado o en Game Over, permitiendo ver el último estado antes de la colisión.

#### **Orden de Renderizado (Z-Index Implícito)**

El orden de las llamadas a `draw()` determina las capas de profundidad visual:
```
FONDO (dibujado primero, más lejano)
    ↓
1. clouds        → Cielo (más lejano)
2. mountains     → Montañas de fondo
3. obstacles     → Obstáculos (cactus)
4. ground        → Suelo
5. dino          → Jugador (primer plano)
6. debugHitboxes → Overlays de depuración
    ↓
FRENTE (dibujado último, más cercano)
```

**Implementación del patrón Painter's Algorithm:** Los objetos dibujados primero quedan "detrás" de los dibujados después, creando profundidad sin necesidad de ordenamiento Z explícito.

#### **Método `draw()` del Dinosaurio**
```javascript
draw() {
    const frameX = this.currentFrame * this.spriteWidth;
    
    ctx.drawImage(
        dinoSprite,              // Sprite sheet completo (336×84 px)
        frameX, 0,               // Posición de recorte en el sprite
        this.spriteWidth, this.height,  // Tamaño del recorte (84×84)
        this.x, this.y,          // Posición en el canvas
        this.width, this.height  // Tamaño de renderizado
    );
}
```

**Técnica de Sprite Sheets:**

- El sprite `dino.png` contiene 4 frames horizontales (336px total / 84px por frame)
- `currentFrame` (0-3) determina qué porción del sprite se dibuja
- `frameX = currentFrame * 84` calcula el offset horizontal de recorte

**Ejemplo:**
```
currentFrame = 0 → frameX = 0   → Dibuja píxeles [0-83]   (frame salto)
currentFrame = 1 → frameX = 84  → Dibuja píxeles [84-167] (frame carrera 1)
currentFrame = 2 → frameX = 168 → Dibuja píxeles [168-251](frame carrera 2)
currentFrame = 3 → frameX = 252 → Dibuja píxeles [252-335](frame muerte)
```

#### **Sistema de Suelo Infinito**
```javascript
const ground = {
    x: 0,
    y: groundY,
    sprite: groundSprite,
    
    update() {
        this.x -= gameState.gameSpeed;
        
        if (this.x <= -this.sprite.width) {
            this.x += this.sprite.width;
        }
    },
    
    draw() {
        const drawY = this.y - this.offset;
        ctx.drawImage(this.sprite, this.x, drawY);
        ctx.drawImage(this.sprite, this.x + this.sprite.width, drawY);
    }
}
```

**Técnica de Tiling Seamless:**

1. Se dibujan **dos copias** del sprite de suelo lado a lado
2. `this.x` se decrementa continuamente (`this.x -= gameSpeed`)
3. Cuando `this.x <= -sprite.width`, se resetea sumando `sprite.width`
4. Esto crea un loop perfecto donde el segundo sprite reemplaza al primero

**Diagrama del ciclo:**
```
Estado Inicial:
[Sprite 1][Sprite 2]
x=0       x=800

Después de N frames:
    [Sprite 1][Sprite 2]
    x=-400    x=400

Cuando x <= -800:
[Sprite 1][Sprite 2]
x=0       x=800
// ↑ Reset, el ciclo se repite
```

---

### **4. Fase de Scheduling**
```javascript
requestAnimationFrame(gameLoop);
```

**Función:** Encola la siguiente ejecución de `gameLoop()` sincronizada con el refresh rate del display.

**Ventajas sobre `setInterval()`:**

| Aspecto | `requestAnimationFrame` | `setInterval` |
|---------|------------------------|---------------|
| **Sincronización** | Con V-Sync del monitor | Independiente |
| **FPS** | Adaptativo (~60Hz) | Fijo (puede causar tearing) |
| **Pausado en tabs inactivos** | Sí (ahorra CPU) | No |
| **Rendimiento** | Optimizado para animaciones | Para tareas generales |

**Flujo del timing:**
```
Frame 1 (t=0ms):
    gameLoop() ejecuta
    └─ requestAnimationFrame(gameLoop)
        └─ Navegador programa Frame 2 para t≈16.67ms

Frame 2 (t≈16.67ms):
    gameLoop() ejecuta nuevamente
    └─ requestAnimationFrame(gameLoop)
        └─ Navegador programa Frame 3 para t≈33.33ms

// Ciclo continuo a ~60 FPS
```

**Cálculo del Delta Time implícito:**

Aunque el código no implementa delta time explícito, asume un framerate constante de 60 FPS:

$$
\Delta t = \frac{1}{60} \text{ segundos} \approx 16.67 \text{ ms}
$$

Todas las velocidades (`gameSpeed`, `dy`, `speedFactor`) están calibradas para este framerate.

---

## Arquitectura de Estados

El game loop está controlado por el objeto `gameState`:
```javascript
let gameState = {
    isRunning: false,      // ¿El juego está activo?
    isGameOver: false,     // ¿El jugador ha perdido?
    score: 0,              // Puntuación acumulada
    frameCount: 0,         // Contador de frames
    gameSpeed: 6           // Velocidad base (px/frame)
};
```

### **Diagrama de Estados**
```
┌─────────────┐
│   LOADING   │ (assetsLoaded < 6)
└──────┬──────┘
       │ assetsLoaded === 6
       ↓
┌─────────────┐
│  INTRO      │ (isRunning=false, isGameOver=false)
│  (Paused)   │  → Renderiza pero no actualiza
└──────┬──────┘
       │ Space/Click
       ↓
┌─────────────┐
│  RUNNING    │ (isRunning=true, isGameOver=false)
│  (Playing)  │  → Update + Render activos
└──────┬──────┘
       │ Colisión detectada
       ↓
┌─────────────┐
│ GAME OVER   │ (isRunning=false, isGameOver=true)
│  (Ended)    │  → Solo renderiza estado final
└──────┬──────┘
       │ Space/Click
       ↓
    restart()
       │
       └──────→ Vuelve a INTRO
```

### **Condiciones de Ejecución por Fase**

| Fase | Condición | INTRO | RUNNING | GAME OVER |
|------|-----------|-------|---------|-----------|
| **Clear** | Siempre | ✅ | ✅ | ✅ |
| **Update** | `isRunning && !isGameOver` | ❌ | ✅ | ❌ |
| **Render** | Siempre | ✅ | ✅ | ✅ |
| **Schedule** | Siempre | ✅ | ✅ | ✅ |

---

## Análisis de Rendimiento

### **Complejidad Temporal por Frame**

**Fase de Update:**
```
O(n_clouds + n_mountains + n_obstacles)
```

Donde:
- `n_clouds` ≈ 3-5 instancias simultáneas
- `n_mountains` ≈ 2-3 instancias simultáneas
- `n_obstacles` ≈ 4-6 instancias simultáneas

**Total:** ~O(12) operaciones por frame → **Complejidad constante** en la práctica.

**Fase de Render:**
```
O(n_clouds + n_mountains + n_obstacles + 3)
```

Los `+3` corresponden a: `ground.draw()`, `dino.draw()`, `drawDebugHitboxes()`.

### **Optimizaciones Implementadas**

1. **Garbage Collection Proactiva:**
```javascript
   if (entities[i].isOffScreen()) entities.splice(i, 1);
```
   Elimina objetos fuera de pantalla para prevenir memory leaks.

2. **Conditional Update:**
```javascript
   if (gameState.isRunning && !gameState.isGameOver) { ... }
```
   Evita cálculos innecesarios cuando el juego está pausado.

3. **Early Exit en Colisiones:**
```javascript
   if (obstacles[i].collidesWith(dino)) endGame();
```
   Detiene la verificación de obstáculos restantes tras detectar colisión.

### **Cuellos de Botella Potenciales**

**Problema:** Acumulación de entidades si `isOffScreen()` falla.

**Mitigación actual:**
```javascript
isOffScreen() {
    return this.x + this.width < 0;
}
```
Condición robusta que garantiza eliminación cuando el sprite está completamente fuera del borde izquierdo.

---

## Integración con Sistemas Externos

### **Input Handler → Game Loop**
```javascript
document.addEventListener('keydown', (e) => {
    if (e.code === 'Space' || e.code === 'ArrowUp') {
        if (!gameState.isRunning) {
            gameState.isRunning = true;
            introEl.style.display = 'none';
        }
        dino.jump();
    }
});
```

**Flujo:**

1. Evento de teclado capturado por el navegador
2. Handler verifica `gameState.isRunning`
3. Si está en INTRO, cambia estado a RUNNING
4. Llama a `dino.jump()` que modifica `dy` y `isJumping`
5. El siguiente frame del loop detecta el cambio en su fase de Update
6. `dino.update()` aplica la física del salto

### **Game Loop → Backend (Score Persistence)**
```javascript
function endGame() {
    gameState.isGameOver = true;
    gameState.isRunning = false;
    
    const finalScore = Math.floor(gameState.score / 10);
    
    if(finalScore > 0) {
        saveScore(finalScore);  // Petición async a Laravel
    }
}
```

**Secuencia temporal:**
```
Frame N:
    obstacles[i].collidesWith(dino) → true
    └─ endGame() ejecuta (sincrónico)
        ├─ gameState.isGameOver = true
        ├─ gameOverEl.style.display = 'block'
        └─ saveScore() inicia (asíncrono, no bloquea)

Frame N+1:
    Update Phase → Saltada (isGameOver = true)
    Render Phase → Dibuja estado congelado
    
Background:
    fetch() POST a /juegos/dino/guardar
    └─ Laravel procesa y responde
        └─ Promesa se resuelve (UI no cambia)
```

**Importante:** La petición HTTP es **no bloqueante**, el game loop continúa ejecutándose mientras el servidor procesa el score.

---

## Comparación: Game Loop Ideal vs. Implementación Actual

### **Modelo Ideal (Fixed Timestep)**
```javascript
let lastTime = 0;
const fixedDeltaTime = 16.67; // ms

function gameLoop(currentTime) {
    const deltaTime = currentTime - lastTime;
    lastTime = currentTime;
    
    let accumulator = deltaTime;
    
    while (accumulator >= fixedDeltaTime) {
        update(fixedDeltaTime);  // Update con tiempo fijo
        accumulator -= fixedDeltaTime;
    }
    
    const alpha = accumulator / fixedDeltaTime;
    render(alpha);  // Interpolación para suavidad
    
    requestAnimationFrame(gameLoop);
}
```

**Ventajas:**
- Física determinista independiente del framerate
- Interpolación de renderizado para 144Hz/240Hz displays

### **Implementación Actual (Variable Timestep)**
```javascript
function gameLoop() {
    // Sin cálculo de deltaTime
    // Asume ~60 FPS constante
    
    if (gameState.isRunning && !gameState.isGameOver) {
        dino.update();  // Update sin parámetro temporal
        // ...
    }
    
    render();
    requestAnimationFrame(gameLoop);
}
```

**Ventajas:**
- Simplicidad de implementación
- Suficiente para juegos casuales

**Limitaciones:**
- Física inconsistente en monitores de alta frecuencia
- Juego más rápido en 144Hz vs. 60Hz

**Para este proyecto:** La aproximación es adecuada dado que:
1. Es un juego casual sin requisitos competitivos
2. La mayoría de usuarios juegan a 60Hz
3. La simplicidad del código facilita el mantenimiento

---

## Conclusión Técnica

El **Game Loop** de Dino Runner implementa un patrón clásico de arquitectura de juegos con separación clara de responsabilidades:

- **Update Phase:** Procesa lógica, física y reglas del juego
- **Render Phase:** Visualiza el estado actual sin modificarlo
- **Scheduling:** Sincroniza con el hardware para fluidez visual

La arquitectura es **determinista** (dado el mismo input, produce el mismo output), **extensible** (agregar nuevas entidades requiere mínimos cambios) y **eficiente** (complejidad O(n) donde n es pequeño y acotado).
