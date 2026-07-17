const config = {
  width: 800,
  height: 520,
  requiredCharms: 3,
  playerStart: { x: 92, y: 392, radius: 16, speed: 3.2 },
  gate: { x: 704, y: 118, radius: 34 },
  charms: [
    { id: 'charm_entry', x: 210, y: 342 },
    { id: 'charm_clearing', x: 438, y: 242 },
    { id: 'charm_gate', x: 630, y: 360 }
  ],
  hazards: [
    { id: 'shadow_one', x: 310, y: 160, radius: 24, dx: 1.35, dy: 0.95 },
    { id: 'shadow_two', x: 570, y: 290, radius: 28, dx: -1.1, dy: 1.2 }
  ]
};

const canvas = document.querySelector('#gameCanvas');
const ctx = canvas.getContext('2d');
const charmCount = document.querySelector('#charmCount');
const gameState = document.querySelector('#gameState');
const restartButton = document.querySelector('#restartButton');
const keys = new Set();
let audioContext;
let state;

function reset() {
  state = {
    player: { ...config.playerStart },
    charms: config.charms.map(charm => ({ ...charm, collected: false })),
    hazards: config.hazards.map(hazard => ({ ...hazard })),
    won: false,
    lost: false,
    tick: 0
  };
  updateHud('Find the lantern charms');
}

function updateHud(message) {
  const collected = state.charms.filter(charm => charm.collected).length;
  charmCount.textContent = `Charms ${collected}/${config.requiredCharms}`;
  gameState.textContent = message;
}

function ensureAudio() {
  if (!audioContext) {
    audioContext = new AudioContext();
  }
}

function playTone(frequency, durationMs, gainValue) {
  if (!audioContext) return;
  const oscillator = audioContext.createOscillator();
  const gain = audioContext.createGain();
  oscillator.frequency.value = frequency;
  oscillator.type = 'sine';
  gain.gain.value = gainValue;
  oscillator.connect(gain);
  gain.connect(audioContext.destination);
  oscillator.start();
  gain.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + durationMs / 1000);
  oscillator.stop(audioContext.currentTime + durationMs / 1000);
}

function distance(a, b) {
  return Math.hypot(a.x - b.x, a.y - b.y);
}

function update() {
  if (state.won || state.lost) return;

  const player = state.player;
  let vx = 0;
  let vy = 0;
  if (keys.has('ArrowLeft') || keys.has('a')) vx -= 1;
  if (keys.has('ArrowRight') || keys.has('d')) vx += 1;
  if (keys.has('ArrowUp') || keys.has('w')) vy -= 1;
  if (keys.has('ArrowDown') || keys.has('s')) vy += 1;
  if (vx !== 0 || vy !== 0) {
    const length = Math.hypot(vx, vy);
    player.x += (vx / length) * player.speed;
    player.y += (vy / length) * player.speed;
  }
  player.x = Math.max(player.radius, Math.min(config.width - player.radius, player.x));
  player.y = Math.max(player.radius, Math.min(config.height - player.radius, player.y));

  for (const hazard of state.hazards) {
    hazard.x += hazard.dx;
    hazard.y += hazard.dy;
    if (hazard.x < hazard.radius || hazard.x > config.width - hazard.radius) hazard.dx *= -1;
    if (hazard.y < hazard.radius || hazard.y > config.height - hazard.radius) hazard.dy *= -1;
    if (distance(player, hazard) < player.radius + hazard.radius - 4) {
      state.lost = true;
      updateHud('The shadows caught the light. Restart to try again.');
      playTone(120, 260, 0.08);
    }
  }

  for (const charm of state.charms) {
    if (!charm.collected && distance(player, charm) < player.radius + 18) {
      charm.collected = true;
      updateHud('Lantern charm found');
      playTone(660, 130, 0.07);
    }
  }

  const collected = state.charms.filter(charm => charm.collected).length;
  if (collected === config.requiredCharms && distance(player, config.gate) < player.radius + config.gate.radius) {
    state.won = true;
    updateHud('Gate opened. Sample complete.');
    playTone(880, 180, 0.08);
    setTimeout(() => playTone(1175, 220, 0.07), 140);
  } else if (collected === config.requiredCharms) {
    updateHud('All charms collected. Reach the gate.');
  }

  state.tick += 1;
}

function drawGround() {
  const gradient = ctx.createLinearGradient(0, 0, config.width, config.height);
  gradient.addColorStop(0, '#16291e');
  gradient.addColorStop(0.5, '#19351f');
  gradient.addColorStop(1, '#12243a');
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, config.width, config.height);

  ctx.strokeStyle = 'rgba(134, 239, 172, 0.15)';
  ctx.lineWidth = 2;
  for (let x = 40; x < config.width; x += 80) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x - 100, config.height);
    ctx.stroke();
  }
}

function drawGate() {
  const collected = state.charms.filter(charm => charm.collected).length;
  ctx.save();
  ctx.translate(config.gate.x, config.gate.y);
  ctx.fillStyle = collected === config.requiredCharms ? '#fde68a' : '#475569';
  ctx.shadowColor = collected === config.requiredCharms ? '#facc15' : 'transparent';
  ctx.shadowBlur = 28;
  ctx.beginPath();
  ctx.arc(0, 0, config.gate.radius, 0, Math.PI * 2);
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.fillStyle = '#0f172a';
  ctx.fillRect(-10, -28, 20, 56);
  ctx.restore();
}

function drawCharms() {
  for (const charm of state.charms) {
    if (charm.collected) continue;
    const pulse = Math.sin(state.tick / 18) * 3;
    ctx.save();
    ctx.translate(charm.x, charm.y);
    ctx.fillStyle = '#facc15';
    ctx.shadowColor = '#fde047';
    ctx.shadowBlur = 20;
    ctx.beginPath();
    ctx.moveTo(0, -16 - pulse);
    ctx.lineTo(12, 0);
    ctx.lineTo(0, 16 + pulse);
    ctx.lineTo(-12, 0);
    ctx.closePath();
    ctx.fill();
    ctx.restore();
  }
}

function drawHazards() {
  for (const hazard of state.hazards) {
    ctx.save();
    ctx.translate(hazard.x, hazard.y);
    ctx.fillStyle = 'rgba(15, 23, 42, 0.82)';
    ctx.shadowColor = '#020617';
    ctx.shadowBlur = 18;
    ctx.beginPath();
    ctx.arc(0, 0, hazard.radius, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  }
}

function drawPlayer() {
  const player = state.player;
  ctx.save();
  ctx.translate(player.x, player.y);
  ctx.fillStyle = '#bbf7d0';
  ctx.shadowColor = '#86efac';
  ctx.shadowBlur = 18;
  ctx.beginPath();
  ctx.arc(0, 0, player.radius, 0, Math.PI * 2);
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.fillStyle = '#14532d';
  ctx.fillRect(-5, -18, 10, 13);
  ctx.restore();
}

function drawOverlay() {
  if (!state.won && !state.lost) return;
  ctx.fillStyle = 'rgba(2, 6, 23, 0.72)';
  ctx.fillRect(0, 0, config.width, config.height);
  ctx.fillStyle = '#f8fafc';
  ctx.font = '700 34px system-ui, sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText(state.won ? 'Gate Opened' : 'Light Lost', config.width / 2, config.height / 2 - 10);
  ctx.font = '18px system-ui, sans-serif';
  ctx.fillText('Press Restart to play again', config.width / 2, config.height / 2 + 28);
}

function draw() {
  drawGround();
  drawGate();
  drawCharms();
  drawHazards();
  drawPlayer();
  drawOverlay();
}

function loop() {
  update();
  draw();
  requestAnimationFrame(loop);
}

document.addEventListener('keydown', event => {
  if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'w', 'a', 's', 'd'].includes(event.key)) {
    event.preventDefault();
    ensureAudio();
    keys.add(event.key);
  }
});

document.addEventListener('keyup', event => {
  keys.delete(event.key);
});

restartButton.addEventListener('click', () => {
  ensureAudio();
  reset();
});

reset();
loop();
