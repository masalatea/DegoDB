const roomSlug = globalThis.SAMPLE42_ROOM_SLUG ?? 'general';
const canvas = document.querySelector('#arena');
const context = canvas.getContext('2d');
const joinButton = document.querySelector('#join');
const statusEl = document.querySelector('#status');
const roomLabel = document.querySelector('#roomLabel');

let playerId = null;
let gameState = null;

roomLabel.textContent = `Room: ${roomSlug}`;

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
    context.fillStyle = player.id === playerId ? '#38bdf8' : '#f97316';
    context.beginPath();
    context.arc(player.x, player.y, 16, 0, Math.PI * 2);
    context.fill();
    context.fillStyle = '#e5edf9';
    context.font = '14px system-ui, sans-serif';
    context.fillText(`${player.id} HP:${player.hp}`, player.x - 26, player.y - 24);
  }

  context.fillStyle = '#fde047';
  for (const shot of gameState.shots) {
    context.beginPath();
    context.arc(shot.x, shot.y, 5, 0, Math.PI * 2);
    context.fill();
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
  playerId = result.player.id;
  gameState = result.state;
  connectEvents();
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
    postCommand({ type: 'move', direction: keyToDirection[event.key] });
  }
  if (event.key === ' ') {
    event.preventDefault();
    postCommand({ type: 'shoot', direction: 'right' });
  }
});

await refreshState();

export { draw, refreshState };
