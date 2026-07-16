const roomSlug = globalThis.SAMPLE43_ROOM_SLUG ?? 'general';
const canvas = document.querySelector('#arena');
const context = canvas.getContext('2d');
const joinButton = document.querySelector('#join');
const statusEl = document.querySelector('#status');
const roomLabel = document.querySelector('#roomLabel');

let playerId = null;
let gameState = null;
let currentAngle = 0;
let audioContext = null;

roomLabel.textContent = `Room: ${roomSlug}`;

function getAudioContext() {
  const AudioContextClass = globalThis.AudioContext ?? globalThis.webkitAudioContext;
  if (!AudioContextClass) {
    return null;
  }
  if (!audioContext) {
    audioContext = new AudioContextClass();
  }
  if (audioContext.state === 'suspended') {
    audioContext.resume().catch(() => {});
  }
  return audioContext;
}

function playTone({ frequency, duration = 0.08, type = 'square', gain = 0.04, slideTo = null }) {
  const audio = getAudioContext();
  if (!audio) {
    return;
  }
  const oscillator = audio.createOscillator();
  const envelope = audio.createGain();
  const now = audio.currentTime;
  oscillator.type = type;
  oscillator.frequency.setValueAtTime(frequency, now);
  if (slideTo !== null) {
    oscillator.frequency.exponentialRampToValueAtTime(slideTo, now + duration);
  }
  envelope.gain.setValueAtTime(0.0001, now);
  envelope.gain.exponentialRampToValueAtTime(gain, now + 0.01);
  envelope.gain.exponentialRampToValueAtTime(0.0001, now + duration);
  oscillator.connect(envelope).connect(audio.destination);
  oscillator.start(now);
  oscillator.stop(now + duration + 0.02);
}

function playFireSound() {
  playTone({ frequency: 140, slideTo: 55, duration: 0.11, type: 'sawtooth', gain: 0.055 });
}

function playExplosionSound() {
  playTone({ frequency: 90, slideTo: 35, duration: 0.18, type: 'sawtooth', gain: 0.07 });
  window.setTimeout(() => playTone({ frequency: 58, slideTo: 28, duration: 0.16, type: 'triangle', gain: 0.045 }), 55);
}

function drawTank(tank) {
  context.save();
  context.translate(tank.x, tank.y);
  context.rotate(tank.angle * Math.PI / 180);
  context.fillStyle = tank.id === playerId ? '#38bdf8' : tank.alive ? '#f97316' : '#64748b';
  context.fillRect(-14, -14, 28, 28);
  context.fillStyle = tank.alive ? '#fde68a' : '#ef4444';
  context.fillRect(2, -4, 22, 8);
  context.restore();

  context.fillStyle = '#e5edf9';
  context.font = '13px system-ui, sans-serif';
  context.fillText(`${tank.name} HP:${tank.hp}`, tank.x - 34, tank.y - 24);
  if (!tank.alive) {
    context.fillStyle = '#fb7185';
    context.fillText('BOOM', tank.x - 18, tank.y + 34);
  }
}

function draw() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  context.fillStyle = '#111827';
  context.fillRect(0, 0, canvas.width, canvas.height);
  context.strokeStyle = '#334155';
  context.strokeRect(18, 18, canvas.width - 36, canvas.height - 36);

  if (!gameState) {
    context.fillStyle = '#94a3b8';
    context.font = '24px system-ui, sans-serif';
    context.fillText('Join to enter the tank arena', 36, 60);
    return;
  }

  context.fillStyle = '#475569';
  for (const obstacle of gameState.obstacles ?? []) {
    context.fillRect(obstacle.x, obstacle.y, obstacle.width, obstacle.height);
    context.strokeStyle = '#94a3b8';
    context.strokeRect(obstacle.x, obstacle.y, obstacle.width, obstacle.height);
  }

  for (const tank of Object.values(gameState.players)) {
    drawTank(tank);
  }

  context.fillStyle = '#fde047';
  for (const bullet of gameState.bullets) {
    context.beginPath();
    context.arc(bullet.x, bullet.y, 5, 0, Math.PI * 2);
    context.fill();
  }

  for (const explosion of gameState.explosions ?? []) {
    context.strokeStyle = '#fb7185';
    context.lineWidth = 3;
    context.beginPath();
    context.arc(explosion.x, explosion.y, 26, 0, Math.PI * 2);
    context.stroke();
  }

  if (gameState.winner) {
    context.fillStyle = '#facc15';
    context.font = '28px system-ui, sans-serif';
    context.fillText(`Winner: ${gameState.winner}`, 36, 54);
  }
}

function renderStatus() {
  const players = Object.fromEntries(Object.entries(gameState?.players ?? {}).map(([id, tank]) => [
    id,
    { hp: tank.hp, alive: tank.alive, angle: tank.angle }
  ]));
  statusEl.textContent = JSON.stringify({
    playerId,
    revision: gameState?.revision,
    phase: gameState?.phase,
    winner: gameState?.winner,
    players
  }, null, 2);
}

async function refreshState() {
  const previousExplosionCount = gameState?.explosions?.length ?? 0;
  gameState = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/state`).then(response => response.json());
  if ((gameState.explosions?.length ?? 0) > previousExplosionCount) {
    playExplosionSound();
  }
  if (playerId && gameState.players[playerId]) {
    currentAngle = gameState.players[playerId].angle;
  }
  renderStatus();
  draw();
}

function connectEvents() {
  const events = new EventSource(`/api/rooms/${encodeURIComponent(roomSlug)}/events`);
  events.addEventListener('tank.updated', async () => {
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
  if (result.state) {
    gameState = result.state;
    if (command.type === 'fire') {
      playFireSound();
    }
    if ((result.state.explosions?.length ?? 0) > 0) {
      playExplosionSound();
    }
    renderStatus();
    draw();
  }
}

joinButton.addEventListener('click', async () => {
  getAudioContext();
  const result = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/join`, {
    method: 'POST',
    headers: { 'content-type': 'application/json' },
    body: JSON.stringify({ name: `tank-${Math.floor(Math.random() * 1000)}` })
  }).then(response => response.json());
  playerId = result.player.id;
  currentAngle = result.player.angle;
  gameState = result.state;
  connectEvents();
  renderStatus();
  draw();
});

document.addEventListener('keydown', event => {
  getAudioContext();
  if (event.key === 'ArrowLeft' || event.key === 'a' || event.key === 'A') {
    event.preventDefault();
    currentAngle -= 15;
    postCommand({ type: 'turn', angle: currentAngle });
  } else if (event.key === 'ArrowRight' || event.key === 'd' || event.key === 'D') {
    event.preventDefault();
    currentAngle += 15;
    postCommand({ type: 'turn', angle: currentAngle });
  } else if (event.key === 'ArrowUp' || event.key === 'w' || event.key === 'W') {
    event.preventDefault();
    postCommand({ type: 'drive', angle: currentAngle, distance: 26 });
  } else if (event.key === 'ArrowDown' || event.key === 's' || event.key === 'S') {
    event.preventDefault();
    postCommand({ type: 'drive', angle: currentAngle, distance: -26 });
  } else if (event.key === ' ') {
    event.preventDefault();
    postCommand({ type: 'fire' });
  }
});

await refreshState();

export { draw, refreshState };
