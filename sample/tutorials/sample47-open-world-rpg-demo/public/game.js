const roomSlug = globalThis.SAMPLE47_ROOM_SLUG ?? 'general';
const canvas = document.querySelector('#world');
const context = canvas.getContext('2d');
const joinButton = document.querySelector('#join');
const statusEl = document.querySelector('#status');
const roomLabel = document.querySelector('#roomLabel');

let playerId = null;
let gameState = null;
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

function playTone({ frequency, duration = 0.08, type = 'square', gain = 0.04 }) {
  const audio = getAudioContext();
  if (!audio) {
    return;
  }
  const oscillator = audio.createOscillator();
  const envelope = audio.createGain();
  const now = audio.currentTime;
  oscillator.type = type;
  oscillator.frequency.setValueAtTime(frequency, now);
  envelope.gain.setValueAtTime(0.0001, now);
  envelope.gain.exponentialRampToValueAtTime(gain, now + 0.01);
  envelope.gain.exponentialRampToValueAtTime(0.0001, now + duration);
  oscillator.connect(envelope).connect(audio.destination);
  oscillator.start(now);
  oscillator.stop(now + duration + 0.02);
}

function playSwingSound() {
  playTone({ frequency: 360, duration: 0.07, type: 'triangle', gain: 0.035 });
}

function playRewardSound() {
  playTone({ frequency: 660, duration: 0.08, type: 'sine', gain: 0.04 });
  window.setTimeout(() => playTone({ frequency: 880, duration: 0.07, type: 'sine', gain: 0.03 }), 70);
}

function currentPlayer() {
  return playerId ? gameState?.players?.[playerId] : null;
}

function camera() {
  const player = currentPlayer();
  if (!player) {
    return { x: 0, y: 0 };
  }
  return {
    x: Math.max(0, Math.min(player.x - canvas.width / 2, gameState.world.width - canvas.width)),
    y: Math.max(0, Math.min(player.y - canvas.height / 2, gameState.world.height - canvas.height))
  };
}

function toScreen(entity, view) {
  return {
    x: entity.x - view.x,
    y: entity.y - view.y
  };
}

function drawGrid(view) {
  context.strokeStyle = 'rgba(187, 247, 208, 0.12)';
  context.lineWidth = 1;
  for (let x = -view.x % 80; x < canvas.width; x += 80) {
    context.beginPath();
    context.moveTo(x, 0);
    context.lineTo(x, canvas.height);
    context.stroke();
  }
  for (let y = -view.y % 80; y < canvas.height; y += 80) {
    context.beginPath();
    context.moveTo(0, y);
    context.lineTo(canvas.width, y);
    context.stroke();
  }
}

function drawObstacles(view) {
  for (const obstacle of gameState.obstacles ?? []) {
    const screen = toScreen(obstacle, view);
    if (obstacle.type === 'pond') {
      context.fillStyle = '#0ea5e9';
      context.strokeStyle = '#bae6fd';
    } else if (obstacle.type === 'rocks') {
      context.fillStyle = '#64748b';
      context.strokeStyle = '#cbd5e1';
    } else {
      context.fillStyle = '#166534';
      context.strokeStyle = '#86efac';
    }
    context.fillRect(screen.x, screen.y, obstacle.width, obstacle.height);
    context.strokeRect(screen.x, screen.y, obstacle.width, obstacle.height);
    if (obstacle.type === 'trees') {
      context.fillStyle = '#22c55e';
      for (let x = 18; x < obstacle.width; x += 36) {
        for (let y = 18; y < obstacle.height; y += 34) {
          context.beginPath();
          context.arc(screen.x + x, screen.y + y, 13, 0, Math.PI * 2);
          context.fill();
        }
      }
    }
  }
}

function drawPlayer(player, view) {
  const screen = toScreen(player, view);
  context.fillStyle = player.id === playerId ? '#38bdf8' : '#a78bfa';
  context.beginPath();
  context.arc(screen.x, screen.y, 16, 0, Math.PI * 2);
  context.fill();
  const facing = {
    up: [0, -24],
    down: [0, 24],
    left: [-24, 0],
    right: [24, 0]
  }[player.facing] ?? [0, 24];
  context.strokeStyle = player.attacking_until > Date.now() ? '#fde047' : '#dbeafe';
  context.lineWidth = player.attacking_until > Date.now() ? 8 : 3;
  context.beginPath();
  context.moveTo(screen.x, screen.y);
  context.lineTo(screen.x + facing[0], screen.y + facing[1]);
  context.stroke();
  context.fillStyle = '#e0f2fe';
  context.font = '13px system-ui, sans-serif';
  context.fillText(`${player.name} HP:${player.hp}`, screen.x - 38, screen.y - 26);
}

function drawEnemy(enemy, view) {
  const screen = toScreen(enemy, view);
  context.fillStyle = '#ef4444';
  context.beginPath();
  context.arc(screen.x, screen.y, 15, 0, Math.PI * 2);
  context.fill();
  context.fillStyle = '#fecaca';
  context.fillRect(screen.x - 18, screen.y - 26, 36, 5);
  context.fillStyle = '#22c55e';
  context.fillRect(screen.x - 18, screen.y - 26, 36 * (enemy.hp / enemy.max_hp), 5);
}

function drawEffects(view) {
  for (const effect of gameState.effects ?? []) {
    const screen = toScreen(effect, view);
    if (effect.type === 'defeat') {
      context.fillStyle = '#facc15';
      context.font = '16px system-ui, sans-serif';
      context.fillText('+EXP +Gold', screen.x - 34, screen.y - 20);
    } else {
      context.strokeStyle = effect.type === 'enemy-hit' ? '#fb7185' : '#fde047';
      context.lineWidth = 3;
      context.beginPath();
      context.arc(screen.x, screen.y, 26, 0, Math.PI * 2);
      context.stroke();
    }
  }
}

function draw() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  context.fillStyle = '#143b23';
  context.fillRect(0, 0, canvas.width, canvas.height);
  if (!gameState) {
    context.fillStyle = '#bbf7d0';
    context.font = '24px system-ui, sans-serif';
    context.fillText('Join the open world', 36, 60);
    return;
  }
  const view = camera();
  drawGrid(view);
  drawObstacles(view);
  for (const enemy of Object.values(gameState.enemies)) {
    drawEnemy(enemy, view);
  }
  for (const player of Object.values(gameState.players)) {
    drawPlayer(player, view);
  }
  drawEffects(view);
}

function renderStatus() {
  const player = currentPlayer();
  statusEl.textContent = JSON.stringify({
    playerId,
    revision: gameState?.revision,
    hp: player?.hp,
    max_hp: player?.max_hp,
    level: player?.level,
    exp: player?.exp,
    gold: player?.gold,
    enemies: Object.keys(gameState?.enemies ?? {}).length,
    pvp_enabled: false
  }, null, 2);
}

async function refreshState() {
  const previousGold = currentPlayer()?.gold ?? 0;
  gameState = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/state`).then(response => response.json());
  if ((currentPlayer()?.gold ?? 0) > previousGold) {
    playRewardSound();
  }
  renderStatus();
  draw();
}

function connectEvents() {
  const events = new EventSource(`/api/rooms/${encodeURIComponent(roomSlug)}/events`);
  events.addEventListener('rpg.updated', async () => {
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
    renderStatus();
    draw();
  }
}

joinButton.addEventListener('click', async () => {
  getAudioContext();
  const result = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/join`, {
    method: 'POST',
    headers: { 'content-type': 'application/json' },
    body: JSON.stringify({ name: `hero-${Math.floor(Math.random() * 1000)}` })
  }).then(response => response.json());
  playerId = result.player.id;
  gameState = result.state;
  connectEvents();
  renderStatus();
  draw();
});

document.addEventListener('keydown', event => {
  getAudioContext();
  const keyToVector = {
    ArrowUp: [0, -1],
    w: [0, -1],
    W: [0, -1],
    ArrowDown: [0, 1],
    s: [0, 1],
    S: [0, 1],
    ArrowLeft: [-1, 0],
    a: [-1, 0],
    A: [-1, 0],
    ArrowRight: [1, 0],
    d: [1, 0],
    D: [1, 0]
  };
  if (keyToVector[event.key]) {
    event.preventDefault();
    const [dx, dy] = keyToVector[event.key];
    postCommand({ type: 'move', dx, dy });
  } else if (event.key === ' ') {
    event.preventDefault();
    playSwingSound();
    postCommand({ type: 'attack' });
  }
});

await refreshState();

export { draw, refreshState };
