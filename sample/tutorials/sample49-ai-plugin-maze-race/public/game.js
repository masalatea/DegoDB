const roomSlug = globalThis.SAMPLE49_ROOM_SLUG ?? 'general';
const canvas = document.querySelector('#maze');
const context = canvas.getContext('2d');
const rankText = document.querySelector('#rankText');
const ruleText = document.querySelector('#ruleText');
const restartButton = document.querySelector('#restart');

const config = { cell: 48, rotationRate: 90, racerRadius: 13, goalRadius: 48 };
let playerId = sessionStorage.getItem(`sample49-player-${roomSlug}`);
let gameState = null;
let held = false;

async function postJson(url, payload) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'content-type': 'application/json' },
    body: JSON.stringify(payload)
  });
  return response.json();
}

async function join() {
  const result = await postJson(`/api/rooms/${encodeURIComponent(roomSlug)}/join`, {
    player_id: playerId,
    name: `Racer-${Math.floor(Math.random() * 900 + 100)}`
  });
  if (!result.ok) {
    gameState = result.state ?? gameState;
    rankText.textContent = result.error === 'room_full' ? 'Room full' : 'Join failed';
    draw();
    return;
  }
  playerId = result.player.id;
  sessionStorage.setItem(`sample49-player-${roomSlug}`, playerId);
  gameState = result.state;
  draw();
}

async function refreshState() {
  gameState = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/state`).then(response => response.json());
  draw();
}

function connectEvents() {
  const events = new EventSource(`/api/rooms/${encodeURIComponent(roomSlug)}/events`);
  events.addEventListener('maze.updated', refreshState);
}

function sendHolding(value) {
  if (!playerId) return;
  postJson(`/api/rooms/${encodeURIComponent(roomSlug)}/commands`, {
    player_id: playerId,
    command: { type: 'hold', holding: value }
  }).then(result => {
    if (result.state) {
      gameState = result.state;
      draw();
    }
  }).catch(() => {});
}

function currentPlayer() {
  return gameState?.racers?.[playerId] ?? Object.values(gameState?.racers ?? {})[0] ?? null;
}

function camera() {
  const player = currentPlayer();
  if (!player || !gameState) return { x: 0, y: 0 };
  const worldW = gameState.maze.cols * gameState.maze.cell;
  const worldH = gameState.maze.rows * gameState.maze.cell;
  return {
    x: Math.max(0, Math.min(player.x - canvas.width / 2, worldW - canvas.width)),
    y: Math.max(0, Math.min(player.y - canvas.height / 2, worldH - canvas.height))
  };
}

function cellCenter(col, row) {
  return {
    x: col * gameState.maze.cell + gameState.maze.cell / 2,
    y: row * gameState.maze.cell + gameState.maze.cell / 2
  };
}

function drawMaze(view) {
  context.fillStyle = '#0f172a';
  context.fillRect(0, 0, canvas.width, canvas.height);
  const cell = gameState.maze.cell;
  const startCol = Math.max(0, Math.floor(view.x / cell) - 1);
  const endCol = Math.min(gameState.maze.cols - 1, Math.ceil((view.x + canvas.width) / cell) + 1);
  const startRow = Math.max(0, Math.floor(view.y / cell) - 1);
  const endRow = Math.min(gameState.maze.rows - 1, Math.ceil((view.y + canvas.height) / cell) + 1);

  for (let row = startRow; row <= endRow; row += 1) {
    for (let col = startCol; col <= endCol; col += 1) {
      const x = col * cell - view.x;
      const y = row * cell - view.y;
      if (gameState.maze.grid[row][col] === 1) {
        context.fillStyle = '#1e293b';
        context.fillRect(x, y, cell, cell);
        context.strokeStyle = 'rgba(148, 163, 184, 0.22)';
        context.strokeRect(x, y, cell, cell);
      } else {
        context.fillStyle = row % 2 === 0 ? '#12301f' : '#142b20';
        context.fillRect(x, y, cell, cell);
      }
    }
  }
}

function drawGoal(view) {
  const goal = cellCenter(gameState.goal.col, gameState.goal.row);
  const x = goal.x - view.x;
  const y = goal.y - view.y;
  context.fillStyle = '#facc15';
  context.shadowColor = '#fde047';
  context.shadowBlur = 26;
  context.beginPath();
  context.arc(x, y, config.goalRadius, 0, Math.PI * 2);
  context.fill();
  context.shadowBlur = 0;
  context.fillStyle = '#422006';
  context.font = '700 16px system-ui, sans-serif';
  context.textAlign = 'center';
  context.fillText('GOAL', x, y + 5);
}

function drawRacer(racer, view) {
  const x = racer.x - view.x;
  const y = racer.y - view.y;
  context.save();
  context.translate(x, y);
  context.rotate(racer.angle * Math.PI / 180);
  context.fillStyle = racer.color;
  context.shadowColor = racer.id === playerId ? '#38bdf8' : racer.color;
  context.shadowBlur = racer.id === playerId ? 18 : 8;
  context.beginPath();
  context.arc(0, 0, config.racerRadius, 0, Math.PI * 2);
  context.fill();
  context.shadowBlur = 0;
  context.fillStyle = '#f8fafc';
  context.beginPath();
  context.moveTo(24, 0);
  context.lineTo(5, -8);
  context.lineTo(5, 8);
  context.closePath();
  context.fill();
  context.restore();
  context.fillStyle = racer.ai ? '#cbd5e1' : '#e0f2fe';
  context.font = '12px system-ui, sans-serif';
  context.textAlign = 'center';
  context.fillText(`${racer.name}${racer.finished ? ' FIN' : ''}`, x, y - 22);
}

function drawMiniMap() {
  const scale = 0.11;
  const cell = gameState.maze.cell;
  const x0 = canvas.width - gameState.maze.cols * cell * scale - 14;
  const y0 = 14;
  context.fillStyle = 'rgba(15, 23, 42, 0.82)';
  context.fillRect(x0 - 8, y0 - 8, gameState.maze.cols * cell * scale + 16, gameState.maze.rows * cell * scale + 16);
  context.fillStyle = '#334155';
  for (let row = 0; row < gameState.maze.rows; row += 1) {
    for (let col = 0; col < gameState.maze.cols; col += 1) {
      if (gameState.maze.grid[row][col] === 1) {
        context.fillRect(x0 + col * cell * scale, y0 + row * cell * scale, cell * scale, cell * scale);
      }
    }
  }
  for (const racer of Object.values(gameState.racers)) {
    context.fillStyle = racer.color;
    context.fillRect(x0 + racer.x * scale - 2, y0 + racer.y * scale - 2, 4, 4);
  }
}

function drawOverlay() {
  if (!gameState?.winner) return;
  context.fillStyle = 'rgba(2, 6, 23, 0.68)';
  context.fillRect(0, 0, canvas.width, canvas.height);
  context.fillStyle = '#f8fafc';
  context.font = '700 34px system-ui, sans-serif';
  context.textAlign = 'center';
  context.fillText(`${gameState.winner.name} wins`, canvas.width / 2, canvas.height / 2 - 8);
  context.font = '18px system-ui, sans-serif';
  context.fillText('Restart this room for another race', canvas.width / 2, canvas.height / 2 + 30);
}

function draw() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  if (!gameState) {
    context.fillStyle = '#94a3b8';
    context.font = '24px system-ui, sans-serif';
    context.fillText('Joining maze race...', 36, 60);
    return;
  }
  const view = camera();
  drawMaze(view);
  drawGoal(view);
  for (const racer of Object.values(gameState.racers)) drawRacer(racer, view);
  drawMiniMap();
  drawOverlay();
  const player = currentPlayer();
  rankText.textContent = gameState.winner ? `${gameState.winner.name} wins` : `Room: ${roomSlug}`;
  ruleText.textContent = player?.holding ? 'Driving straight' : 'Rotating at 90 deg/sec';
}

document.addEventListener('keydown', event => {
  if (event.code === 'Space') {
    event.preventDefault();
    if (!held) {
      held = true;
      sendHolding(true);
    }
  }
});

document.addEventListener('keyup', event => {
  if (event.code === 'Space') {
    event.preventDefault();
    held = false;
    sendHolding(false);
  }
});

canvas.addEventListener('pointerdown', event => {
  event.preventDefault();
  held = true;
  sendHolding(true);
});

canvas.addEventListener('pointerup', () => {
  held = false;
  sendHolding(false);
});

canvas.addEventListener('pointercancel', () => {
  held = false;
  sendHolding(false);
});

restartButton.addEventListener('click', async () => {
  await postJson(`/api/rooms/${encodeURIComponent(roomSlug)}/reset`, {});
  sessionStorage.removeItem(`sample49-player-${roomSlug}`);
  playerId = null;
  await join();
});

draw();
await join();
connectEvents();
