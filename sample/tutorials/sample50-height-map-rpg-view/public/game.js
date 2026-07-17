const canvas = document.querySelector('#terrain');
const context = canvas.getContext('2d');
const seedText = document.querySelector('#seedText');
const heightText = document.querySelector('#heightText');

const config = {
  cols: 48,
  rows: 48,
  tileWidth: 42,
  tileHeight: 24,
  heightScale: 46,
  octaves: 5,
  persistence: 0.52,
  lacunarity: 2
};

let seed = 50050;
let heights = [];
const camera = { x: 0, y: -130 };
const keys = new Set();

function hashNoise(x, y, seedValue) {
  let n = x * 374761393 + y * 668265263 + seedValue * 2246822519;
  n = (n ^ (n >>> 13)) * 1274126177;
  n = (n ^ (n >>> 16)) >>> 0;
  return n / 4294967295;
}

function smoothStep(t) {
  return t * t * (3 - 2 * t);
}

function lerp(a, b, t) {
  return a + (b - a) * t;
}

function valueNoise(x, y, frequency, seedValue) {
  const sx = x / frequency;
  const sy = y / frequency;
  const x0 = Math.floor(sx);
  const y0 = Math.floor(sy);
  const tx = smoothStep(sx - x0);
  const ty = smoothStep(sy - y0);
  const a = hashNoise(x0, y0, seedValue);
  const b = hashNoise(x0 + 1, y0, seedValue);
  const c = hashNoise(x0, y0 + 1, seedValue);
  const d = hashNoise(x0 + 1, y0 + 1, seedValue);
  return lerp(lerp(a, b, tx), lerp(c, d, tx), ty);
}

function heightAt(col, row, seedValue) {
  let amplitude = 1;
  let frequency = 18;
  let total = 0;
  let max = 0;
  for (let octave = 0; octave < config.octaves; octave += 1) {
    total += valueNoise(col, row, frequency, seedValue + octave * 101) * amplitude;
    max += amplitude;
    amplitude *= config.persistence;
    frequency /= config.lacunarity;
  }
  const ridge = 1 - Math.abs(0.5 - valueNoise(col + 33, row - 17, 9, seedValue + 701)) * 2;
  return Math.max(0, Math.min(1, total / max * 0.82 + ridge * 0.18));
}

function generateHeights() {
  heights = [];
  for (let row = 0; row < config.rows; row += 1) {
    const line = [];
    for (let col = 0; col < config.cols; col += 1) {
      line.push(heightAt(col, row, seed));
    }
    heights.push(line);
  }
  seedText.textContent = `Seed ${seed}`;
}

function project(col, row, height) {
  return {
    x: (col - row) * config.tileWidth / 2 + canvas.width / 2 - camera.x,
    y: (col + row) * config.tileHeight / 2 - height * config.heightScale + 60 - camera.y
  };
}

function terrainColor(height) {
  if (height < 0.28) return '#0f766e';
  if (height < 0.42) return '#15803d';
  if (height < 0.62) return '#4d7c0f';
  if (height < 0.78) return '#78716c';
  return '#e2e8f0';
}

function drawTile(col, row) {
  const h = heights[row][col];
  const top = project(col, row, h);
  const right = project(col + 1, row, heights[row]?.[col + 1] ?? h);
  const bottom = project(col + 1, row + 1, heights[row + 1]?.[col + 1] ?? h);
  const left = project(col, row + 1, heights[row + 1]?.[col] ?? h);
  context.beginPath();
  context.moveTo(top.x, top.y);
  context.lineTo(right.x, right.y);
  context.lineTo(bottom.x, bottom.y);
  context.lineTo(left.x, left.y);
  context.closePath();
  context.fillStyle = terrainColor(h);
  context.fill();
  context.strokeStyle = `rgba(15, 23, 42, ${0.18 + h * 0.16})`;
  context.stroke();
}

function drawPlayer() {
  const col = 23.5;
  const row = 24.5;
  const h = heightAt(col, row, seed);
  const point = project(col, row, h);
  context.fillStyle = '#38bdf8';
  context.shadowColor = '#7dd3fc';
  context.shadowBlur = 18;
  context.beginPath();
  context.arc(point.x, point.y - 14, 12, 0, Math.PI * 2);
  context.fill();
  context.shadowBlur = 0;
  context.strokeStyle = '#f8fafc';
  context.lineWidth = 3;
  context.beginPath();
  context.moveTo(point.x, point.y - 4);
  context.lineTo(point.x + 18, point.y - 22);
  context.stroke();
  heightText.textContent = `Player height ${h.toFixed(2)}`;
}

function draw() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  const sky = context.createLinearGradient(0, 0, 0, canvas.height);
  sky.addColorStop(0, '#0f172a');
  sky.addColorStop(1, '#082f49');
  context.fillStyle = sky;
  context.fillRect(0, 0, canvas.width, canvas.height);

  for (let row = 0; row < config.rows - 1; row += 1) {
    for (let col = config.cols - 2; col >= 0; col -= 1) {
      drawTile(col, row);
    }
  }
  drawPlayer();
}

function updateCamera() {
  const speed = 12;
  if (keys.has('ArrowLeft') || keys.has('a')) camera.x -= speed;
  if (keys.has('ArrowRight') || keys.has('d')) camera.x += speed;
  if (keys.has('ArrowUp') || keys.has('w')) camera.y -= speed;
  if (keys.has('ArrowDown') || keys.has('s')) camera.y += speed;
}

function loop() {
  updateCamera();
  draw();
  requestAnimationFrame(loop);
}

document.addEventListener('keydown', event => {
  if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'w', 'a', 's', 'd'].includes(event.key)) {
    event.preventDefault();
    keys.add(event.key);
  }
  if (event.key === 'r' || event.key === 'R') {
    seed += 97;
    generateHeights();
  }
});

document.addEventListener('keyup', event => {
  keys.delete(event.key);
});

generateHeights();
loop();
