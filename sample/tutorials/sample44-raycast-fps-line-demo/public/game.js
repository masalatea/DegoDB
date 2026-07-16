const roomSlug = globalThis.SAMPLE44_ROOM_SLUG ?? 'general';
const view = document.querySelector('#view');
const viewContext = view.getContext('2d');
const map = document.querySelector('#map');
const mapContext = map.getContext('2d');
const joinButton = document.querySelector('#join');
const statusEl = document.querySelector('#status');
const roomLabel = document.querySelector('#roomLabel');

const cellSize = 64;
const fieldOfView = 64;
const rayCount = 160;
const maxRayDistance = 900;
const turnDegrees = 5;

let playerId = null;
let gameState = null;
let currentAngle = 0;
let audioContext = null;
let shotEffectUntil = 0;

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

function playShotSound() {
  playTone({ frequency: 180, slideTo: 80, duration: 0.09, type: 'square', gain: 0.05 });
}

function playDefeatSound() {
  playTone({ frequency: 90, slideTo: 35, duration: 0.18, type: 'sawtooth', gain: 0.065 });
}

function degToRad(degrees) {
  return degrees * Math.PI / 180;
}

function normalizeAngle(angle) {
  return ((angle % 360) + 360) % 360;
}

function isWallAt(x, y) {
  const gridX = Math.floor(x / cellSize);
  const gridY = Math.floor(y / cellSize);
  return gameState?.map?.[gridY]?.[gridX] !== '0';
}

function castRay(x, y, angle) {
  const radian = degToRad(angle);
  const dx = Math.cos(radian);
  const dy = Math.sin(radian);
  for (let distance = 0; distance < maxRayDistance; distance += 4) {
    const hitX = x + dx * distance;
    const hitY = y + dy * distance;
    if (isWallAt(hitX, hitY)) {
      return { distance, x: hitX, y: hitY };
    }
  }
  return {
    distance: maxRayDistance,
    x: x + dx * maxRayDistance,
    y: y + dy * maxRayDistance
  };
}

function drawRaycastView(player) {
  viewContext.fillStyle = '#020617';
  viewContext.fillRect(0, 0, view.width, view.height);
  viewContext.fillStyle = '#111827';
  viewContext.fillRect(0, 0, view.width, view.height / 2);
  viewContext.fillStyle = '#0f172a';
  viewContext.fillRect(0, view.height / 2, view.width, view.height / 2);

  const stripWidth = view.width / rayCount;
  for (let column = 0; column < rayCount; column += 1) {
    const rayAngle = player.angle - fieldOfView / 2 + (column / (rayCount - 1)) * fieldOfView;
    const hit = castRay(player.x, player.y, rayAngle);
    const correctedDistance = Math.max(1, hit.distance * Math.cos(degToRad(rayAngle - player.angle)));
    const wallHeight = Math.min(view.height, (cellSize * 460) / correctedDistance);
    const shade = Math.max(44, 210 - correctedDistance * 0.22);
    viewContext.strokeStyle = `rgb(${shade}, ${shade + 12}, ${Math.min(255, shade + 34)})`;
    viewContext.lineWidth = Math.ceil(stripWidth);
    viewContext.beginPath();
    viewContext.moveTo(column * stripWidth, (view.height - wallHeight) / 2);
    viewContext.lineTo(column * stripWidth, (view.height + wallHeight) / 2);
    viewContext.stroke();
  }

  viewContext.strokeStyle = '#22c55e';
  viewContext.beginPath();
  viewContext.moveTo(view.width / 2 - 12, view.height / 2);
  viewContext.lineTo(view.width / 2 + 12, view.height / 2);
  viewContext.moveTo(view.width / 2, view.height / 2 - 12);
  viewContext.lineTo(view.width / 2, view.height / 2 + 12);
  viewContext.stroke();

  drawShotEffect();
}

function drawShotEffect() {
  if (performance.now() > shotEffectUntil) {
    return;
  }
  viewContext.save();
  viewContext.strokeStyle = '#fde047';
  viewContext.fillStyle = 'rgba(250, 204, 21, 0.75)';
  viewContext.lineWidth = 4;
  viewContext.beginPath();
  viewContext.moveTo(view.width / 2, view.height / 2 + 18);
  viewContext.lineTo(view.width / 2, view.height / 2 - 92);
  viewContext.stroke();
  viewContext.beginPath();
  viewContext.arc(view.width / 2, view.height / 2 + 26, 13, 0, Math.PI * 2);
  viewContext.fill();
  viewContext.restore();
}

function triggerShotEffect() {
  shotEffectUntil = performance.now() + 140;
  draw();
  window.setTimeout(draw, 160);
}

function drawMap(player) {
  const scale = map.width / (gameState.map[0].length * cellSize);
  mapContext.fillStyle = '#020617';
  mapContext.fillRect(0, 0, map.width, map.height);
  for (let y = 0; y < gameState.map.length; y += 1) {
    for (let x = 0; x < gameState.map[y].length; x += 1) {
      mapContext.fillStyle = gameState.map[y][x] === '1' ? '#475569' : '#0f172a';
      mapContext.fillRect(x * cellSize * scale, y * cellSize * scale, cellSize * scale, cellSize * scale);
    }
  }
  for (const other of Object.values(gameState.players)) {
    mapContext.fillStyle = other.id === playerId ? '#22c55e' : other.alive ? '#f97316' : '#64748b';
    mapContext.beginPath();
    mapContext.arc(other.x * scale, other.y * scale, 4, 0, Math.PI * 2);
    mapContext.fill();
  }
  const radian = degToRad(player.angle);
  mapContext.strokeStyle = '#22c55e';
  mapContext.beginPath();
  mapContext.moveTo(player.x * scale, player.y * scale);
  mapContext.lineTo((player.x + Math.cos(radian) * 50) * scale, (player.y + Math.sin(radian) * 50) * scale);
  mapContext.stroke();
}

function draw() {
  if (!gameState || !playerId || !gameState.players[playerId]) {
    viewContext.fillStyle = '#020617';
    viewContext.fillRect(0, 0, view.width, view.height);
    viewContext.fillStyle = '#94a3b8';
    viewContext.font = '24px system-ui, sans-serif';
    viewContext.fillText('Join to render line-only raycast view', 36, 60);
    return;
  }
  const player = gameState.players[playerId];
  drawRaycastView(player);
  drawMap(player);
}

function renderStatus() {
  const players = Object.fromEntries(Object.entries(gameState?.players ?? {}).map(([id, player]) => [
    id,
    { hp: player.hp, alive: player.alive, angle: player.angle }
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
  const previousDefeatCount = gameState?.defeats?.length ?? 0;
  gameState = await fetch(`/api/rooms/${encodeURIComponent(roomSlug)}/state`).then(response => response.json());
  if ((gameState.defeats?.length ?? 0) > previousDefeatCount) {
    playDefeatSound();
  }
  if (playerId && gameState.players[playerId]) {
    currentAngle = gameState.players[playerId].angle;
  }
  renderStatus();
  draw();
}

function connectEvents() {
  const events = new EventSource(`/api/rooms/${encodeURIComponent(roomSlug)}/events`);
  events.addEventListener('fps.updated', async () => {
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
    if (command.type === 'shoot') {
      playShotSound();
      triggerShotEffect();
    }
    if ((result.state.defeats?.length ?? 0) > 0) {
      playDefeatSound();
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
    body: JSON.stringify({ name: `fps-${Math.floor(Math.random() * 1000)}` })
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
    currentAngle = normalizeAngle(currentAngle - turnDegrees);
    postCommand({ type: 'turn', delta: -turnDegrees });
  } else if (event.key === 'ArrowRight' || event.key === 'd' || event.key === 'D') {
    event.preventDefault();
    currentAngle = normalizeAngle(currentAngle + turnDegrees);
    postCommand({ type: 'turn', delta: turnDegrees });
  } else if (event.key === 'ArrowUp' || event.key === 'w' || event.key === 'W') {
    event.preventDefault();
    postCommand({ type: 'move', direction: 'forward' });
  } else if (event.key === 'ArrowDown' || event.key === 's' || event.key === 'S') {
    event.preventDefault();
    postCommand({ type: 'move', direction: 'backward' });
  } else if (event.key === ' ') {
    event.preventDefault();
    postCommand({ type: 'shoot' });
  }
});

await refreshState();

export { castRay, draw, refreshState };
