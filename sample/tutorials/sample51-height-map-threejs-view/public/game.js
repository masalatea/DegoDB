import * as THREE from '../vendor/three/three.module.js';

const sceneRoot = document.querySelector('#scene');
const seedText = document.querySelector('#seedText');
const heightText = document.querySelector('#heightText');
const positionText = document.querySelector('#positionText');

const config = {
  cols: 96,
  rows: 96,
  worldSize: 44,
  heightScale: 7.5,
  octaves: 5,
  persistence: 0.52,
  lacunarity: 2
};

let seed = 51051;
let terrain;
let playerMarker;
let dragging = false;
let lastPointer = { x: 0, y: 0 };
let yaw = Math.PI / 4;
let pitch = Math.PI / 4;
let distance = 54;
let player = { x: 0, z: 0 };
let lastFrameTime = 0;

const pressedKeys = new Set();

const scene = new THREE.Scene();
scene.background = new THREE.Color(0x07111f);
scene.fog = new THREE.Fog(0x07111f, 42, 96);

const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 220);
const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
renderer.shadowMap.enabled = true;
sceneRoot.append(renderer.domElement);

const ambient = new THREE.AmbientLight(0xcbd5e1, 0.58);
scene.add(ambient);

const sun = new THREE.DirectionalLight(0xfef3c7, 2.2);
sun.position.set(18, 30, 14);
sun.castShadow = true;
scene.add(sun);

const water = new THREE.Mesh(
  new THREE.CircleGeometry(38, 96),
  new THREE.MeshStandardMaterial({ color: 0x0e7490, roughness: 0.68, metalness: 0.05, transparent: true, opacity: 0.42 })
);
water.rotation.x = -Math.PI / 2;
water.position.y = -0.24;
scene.add(water);

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
  let frequency = 24;
  let total = 0;
  let max = 0;
  for (let octave = 0; octave < config.octaves; octave += 1) {
    total += valueNoise(col, row, frequency, seedValue + octave * 101) * amplitude;
    max += amplitude;
    amplitude *= config.persistence;
    frequency /= config.lacunarity;
  }
  const ridge = 1 - Math.abs(0.5 - valueNoise(col + 33, row - 17, 10, seedValue + 701)) * 2;
  return Math.max(0, Math.min(1, total / max * 0.84 + ridge * 0.16));
}

function colorForHeight(height) {
  if (height < 0.28) return new THREE.Color(0x0f766e);
  if (height < 0.42) return new THREE.Color(0x15803d);
  if (height < 0.62) return new THREE.Color(0x4d7c0f);
  if (height < 0.78) return new THREE.Color(0x78716c);
  return new THREE.Color(0xe2e8f0);
}

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
}

function worldToMapCoord(x, z) {
  const half = config.worldSize / 2;
  return {
    col: clamp((x + half) / config.worldSize * (config.cols - 1), 0, config.cols - 1),
    row: clamp((z + half) / config.worldSize * (config.rows - 1), 0, config.rows - 1)
  };
}

function heightAtWorld(x, z) {
  const map = worldToMapCoord(x, z);
  return heightAt(map.col, map.row, seed) * config.heightScale;
}

function placePlayer() {
  if (!playerMarker) return;
  const groundY = heightAtWorld(player.x, player.z);
  playerMarker.position.set(player.x, groundY + 1.0, player.z);
  heightText.textContent = `Player height ${(groundY / config.heightScale).toFixed(2)}`;
  positionText.textContent = `Pos ${player.x.toFixed(1)}, ${player.z.toFixed(1)}`;
}

function buildTerrain() {
  if (terrain) {
    scene.remove(terrain);
    terrain.geometry.dispose();
    terrain.material.dispose();
  }

  const geometry = new THREE.PlaneGeometry(config.worldSize, config.worldSize, config.cols - 1, config.rows - 1);
  geometry.rotateX(-Math.PI / 2);
  const positions = geometry.attributes.position;
  const colors = [];

  for (let index = 0; index < positions.count; index += 1) {
    const col = index % config.cols;
    const row = Math.floor(index / config.cols);
    const h = heightAt(col, row, seed);
    positions.setY(index, h * config.heightScale);
    const color = colorForHeight(h);
    colors.push(color.r, color.g, color.b);
  }

  geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
  geometry.computeVertexNormals();

  terrain = new THREE.Mesh(
    geometry,
    new THREE.MeshStandardMaterial({
      vertexColors: true,
      roughness: 0.86,
      metalness: 0.02
    })
  );
  terrain.receiveShadow = true;
  scene.add(terrain);

  if (!playerMarker) {
    playerMarker = new THREE.Mesh(
      new THREE.SphereGeometry(0.55, 24, 16),
      new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x082f49, roughness: 0.42 })
    );
    playerMarker.castShadow = true;
    scene.add(playerMarker);
  }
  player.x = clamp(player.x, -config.worldSize / 2 + 1, config.worldSize / 2 - 1);
  player.z = clamp(player.z, -config.worldSize / 2 + 1, config.worldSize / 2 - 1);
  placePlayer();
  seedText.textContent = `Seed ${seed}`;
}

function updateCamera() {
  pitch = Math.max(0.28, Math.min(1.22, pitch));
  distance = Math.max(22, Math.min(86, distance));
  const target = new THREE.Vector3(player.x, playerMarker ? playerMarker.position.y : 1.4, player.z);
  camera.position.set(
    target.x + Math.cos(yaw) * Math.cos(pitch) * distance,
    target.y + Math.sin(pitch) * distance,
    target.z + Math.sin(yaw) * Math.cos(pitch) * distance
  );
  camera.lookAt(target);
}

function keyIsDown(...keys) {
  return keys.some(key => pressedKeys.has(key));
}

function updatePlayer(deltaSeconds) {
  let forward = 0;
  let right = 0;
  if (keyIsDown('ArrowUp', 'w', 'W')) forward += 1;
  if (keyIsDown('ArrowDown', 's', 'S')) forward -= 1;
  if (keyIsDown('ArrowRight', 'd', 'D')) right += 1;
  if (keyIsDown('ArrowLeft', 'a', 'A')) right -= 1;
  if (forward === 0 && right === 0) return;

  const length = Math.hypot(forward, right) || 1;
  forward /= length;
  right /= length;

  const forwardVector = new THREE.Vector2(-Math.cos(yaw), -Math.sin(yaw));
  const rightVector = new THREE.Vector2(Math.sin(yaw), -Math.cos(yaw));
  const speed = 10.5;
  player.x += (forwardVector.x * forward + rightVector.x * right) * speed * deltaSeconds;
  player.z += (forwardVector.y * forward + rightVector.y * right) * speed * deltaSeconds;
  const edge = config.worldSize / 2 - 1;
  player.x = clamp(player.x, -edge, edge);
  player.z = clamp(player.z, -edge, edge);
  placePlayer();
}

function resize() {
  const width = sceneRoot.clientWidth || window.innerWidth;
  const height = sceneRoot.clientHeight || window.innerHeight;
  renderer.setSize(width, height, false);
  camera.aspect = width / height;
  camera.updateProjectionMatrix();
}

function animate(now = 0) {
  const deltaSeconds = Math.min((now - lastFrameTime) / 1000 || 0, 0.05);
  lastFrameTime = now;
  updatePlayer(deltaSeconds);
  updateCamera();
  playerMarker.rotation.y += 0.01;
  renderer.render(scene, camera);
  requestAnimationFrame(animate);
}

sceneRoot.addEventListener('pointerdown', event => {
  dragging = true;
  lastPointer = { x: event.clientX, y: event.clientY };
  sceneRoot.setPointerCapture(event.pointerId);
});

sceneRoot.addEventListener('pointermove', event => {
  if (!dragging) return;
  const dx = event.clientX - lastPointer.x;
  const dy = event.clientY - lastPointer.y;
  yaw -= dx * 0.006;
  pitch -= dy * 0.004;
  lastPointer = { x: event.clientX, y: event.clientY };
});

sceneRoot.addEventListener('pointerup', event => {
  dragging = false;
  sceneRoot.releasePointerCapture(event.pointerId);
});

sceneRoot.addEventListener('wheel', event => {
  event.preventDefault();
  distance += event.deltaY * 0.035;
}, { passive: false });

document.addEventListener('keydown', event => {
  if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'w', 'a', 's', 'd', 'W', 'A', 'S', 'D'].includes(event.key)) {
    event.preventDefault();
    pressedKeys.add(event.key);
    updatePlayer(0.08);
  }
  if (event.key === 'r' || event.key === 'R') {
    seed += 97;
    buildTerrain();
  }
});

document.addEventListener('keyup', event => {
  pressedKeys.delete(event.key);
});

window.addEventListener('resize', resize);

buildTerrain();
resize();
animate();
