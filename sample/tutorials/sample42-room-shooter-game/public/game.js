const roomSlug = globalThis.SAMPLE42_ROOM_SLUG ?? 'general';
const canvas = document.querySelector('#arena');
const context = canvas.getContext('2d');
const joinButton = document.querySelector('#join');
const statusEl = document.querySelector('#status');
const roomLabel = document.querySelector('#roomLabel');

let playerId = null;
let gameState = null;

roomLabel.textContent = `Room: ${roomSlug}`;

function toScreen(point) {
  if (playerId === 'p2') {
    return {
      x: canvas.width - point.x,
      y: canvas.height - point.y
    };
  }
  return { x: point.x, y: point.y };
}

function directionToWorld(direction) {
  if (playerId !== 'p2') {
    return direction;
  }
  return {
    up: 'down',
    down: 'up',
    left: 'right',
    right: 'left'
  }[direction] ?? direction;
}

function draw() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  context.fillStyle = '#0f172a';
  context.fillRect(0, 0, canvas.width, canvas.height);
  context.strokeStyle = '#334155';
  context.strokeRect(20, 20, canvas.width - 40, canvas.height - 40);

  if (!gameState) {
    context.fillStyle = '#94a3b8';
    context.font = '24px system-ui, sans-serif';
    context.fillText('Join to start', 36, 60);
    return;
  }

  for (const player of Object.values(gameState.players)) {
    const screen = toScreen(player);
    context.fillStyle = player.id === playerId ? '#38bdf8' : '#f97316';
    context.beginPath();
    context.arc(screen.x, screen.y, 16, 0, Math.PI * 2);
    context.fill();
    context.fillStyle = player.id === playerId ? '#bae6fd' : '#fed7aa';
    context.beginPath();
    context.moveTo(screen.x, screen.y - 24);
    context.lineTo(screen.x - 8, screen.y - 8);
    context.lineTo(screen.x + 8, screen.y - 8);
    context.closePath();
    context.fill();
    context.fillStyle = '#e5edf9';
    context.font = '14px system-ui, sans-serif';
    context.fillText(`${player.id} HP:${player.hp}`, screen.x - 26, screen.y - 32);
  }

  context.fillStyle = '#fde047';
  for (const shot of gameState.shots) {
    const screen = toScreen(shot);
    context.beginPath();
    context.arc(screen.x, screen.y, 7, 0, Math.PI * 2);
    context.fill();
  }

  if (gameState.winner) {
    context.fillStyle = '#facc15';
    context.font = '28px system-ui, sans-serif';
    context.fillText(`Winner: ${gameState.winner}`, 36, 54);
  }
}

async function refreshState() {
  gameState = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/state`).then(response => response.json());
  statusEl.textContent = JSON.stringify({ playerId, revision: gameState.revision, players: gameState.players }, null, 2);
  draw();
}

function connectEvents() {
  const events = new EventSource(`/api/rooms/${encodeURIComponent(roomSlug)}/events`);
  events.addEventListener('game.updated', async () => {
    await refreshState();
  });
}

async function postCommand(command) {
  if (!playerId) {
    return;
  }
  const result = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/commands`, {
    method: 'POST',
    headers: { 'content-type': 'application/json' },
    body: JSON.stringify({ player_id: playerId, command })
  }).then(response => response.json());
  gameState = result.state;
  statusEl.textContent = JSON.stringify({ playerId, revision: gameState.revision, players: gameState.players }, null, 2);
  draw();
}

joinButton.addEventListener('click', async () => {
  const result = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/join`, { method: 'POST' }).then(response => response.json());
  if (!result.ok) {
    statusEl.textContent = JSON.stringify(result, null, 2);
    return;
  }
  playerId = result.player.id;
  gameState = result.state;
  connectEvents();
  statusEl.textContent = JSON.stringify({ playerId, revision: gameState.revision, players: gameState.players }, null, 2);
  draw();
});

document.addEventListener('keydown', event => {
  const keyToDirection = {
    ArrowUp: 'up',
    w: 'up',
    W: 'up',
    ArrowDown: 'down',
    s: 'down',
    S: 'down',
    ArrowLeft: 'left',
    a: 'left',
    A: 'left',
    ArrowRight: 'right',
    d: 'right',
    D: 'right'
  };
  if (keyToDirection[event.key]) {
    event.preventDefault();
    postCommand({ type: 'move', direction: directionToWorld(keyToDirection[event.key]) });
  }
  if (event.key === ' ') {
    event.preventDefault();
    postCommand({ type: 'shoot' });
  }
});

await refreshState();

export { draw, refreshState };
