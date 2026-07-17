import * as THREE from '/vendor/three/three.module.js';

const sceneRoot = document.querySelector('#scene');
const apiText = document.querySelector('#apiText');
const seedText = document.querySelector('#seedText');
const heightText = document.querySelector('#heightText');
const positionText = document.querySelector('#positionText');

let packet;
let mapConfig;
let terrain;
let playerMarker;
let dragging = false;
let lastPointer = { x: 0, y: 0 };
let yaw = Math.PI / 4;
let pitch = 0.42;
let distance = 34;
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

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
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

function heightAt(col, row) {
  const terrainRules = mapConfig.terrain;
  let amplitude = 1;
  let frequency = 24;
  let total = 0;
  let max = 0;
  for (let octave = 0; octave < terrainRules.octaves; octave += 1) {
    total += valueNoise(col, row, frequency, mapConfig.seed + octave * 101) * amplitude;
    max += amplitude;
    amplitude *= terrainRules.persistence;
    frequency /= terrainRules.lacunarity;
  }
  const ridge = 1 - Math.abs(0.5 - valueNoise(col + 33, row - 17, 10, mapConfig.seed + 701)) * 2;
  return clamp(total / max * 0.84 + ridge * 0.16, 0, 1);
}

function colorForHeight(height) {
  const material = mapConfig.materials.find(entry => height <= entry.max_height) ?? mapConfig.materials.at(-1);
  return new THREE.Color(material.color);
}

function worldToMapCoord(x, z) {
  const half = mapConfig.world_size / 2;
  return {
    col: clamp((x + half) / mapConfig.world_size * (mapConfig.columns - 1), 0, mapConfig.columns - 1),
    row: clamp((z + half) / mapConfig.world_size * (mapConfig.rows - 1), 0, mapConfig.rows - 1)
  };
}

function heightAtWorld(x, z) {
  const map = worldToMapCoord(x, z);
  return heightAt(map.col, map.row) * mapConfig.height_scale;
}

function placePlayer() {
  if (!playerMarker || !mapConfig) return;
  const groundY = heightAtWorld(player.x, player.z);
  playerMarker.position.set(player.x, groundY + 1.0, player.z);
  heightText.textContent = `Height ${(groundY / mapConfig.height_scale).toFixed(2)}`;
  positionText.textContent = `Pos ${player.x.toFixed(1)}, ${player.z.toFixed(1)}`;
}

function buildTerrain() {
  if (terrain) {
    scene.remove(terrain);
    terrain.geometry.dispose();
    terrain.material.dispose();
  }

  const geometry = new THREE.PlaneGeometry(mapConfig.world_size, mapConfig.world_size, mapConfig.columns - 1, mapConfig.rows - 1);
  geometry.rotateX(-Math.PI / 2);
  const positions = geometry.attributes.position;
  const colors = [];

  for (let index = 0; index < positions.count; index += 1) {
    const col = index % mapConfig.columns;
    const row = Math.floor(index / mapConfig.columns);
    const h = heightAt(col, row);
    positions.setY(index, h * mapConfig.height_scale);
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

  player = { ...mapConfig.player_start };
  placePlayer();
  apiText.textContent = `/api/map -> ${packet.schema_version}`;
  seedText.textContent = `Seed ${mapConfig.seed}`;
}

function updateCamera() {
  if (!playerMarker) return;
  pitch = clamp(pitch, 0.22, 1.0);
  distance = clamp(distance, 16, 58);
  const forwardVector = new THREE.Vector2(-Math.cos(yaw), -Math.sin(yaw));
  const lookAhead = 7.5;
  const target = new THREE.Vector3(
    player.x + forwardVector.x * lookAhead,
    playerMarker.position.y + 1.1,
    player.z + forwardVector.y * lookAhead
  );
  camera.position.set(
    player.x + Math.cos(yaw) * Math.cos(pitch) * distance,
    playerMarker.position.y + Math.sin(pitch) * distance,
    player.z + Math.sin(yaw) * Math.cos(pitch) * distance
  );
  camera.lookAt(target);
}

function keyIsDown(...keys) {
  return keys.some(key => pressedKeys.has(key));
}

function updatePlayer(deltaSeconds) {
  if (!mapConfig) return;
  let forward = 0;
  if (keyIsDown('ArrowUp', 'w', 'W')) forward += 1;
  if (keyIsDown('ArrowDown', 's', 'S')) forward -= 1;

  const turn = (keyIsDown('ArrowRight', 'd', 'D') ? 1 : 0) - (keyIsDown('ArrowLeft', 'a', 'A') ? 1 : 0);
  if (turn !== 0) yaw += turn * 2.6 * deltaSeconds;
  if (forward === 0) return;

  const forwardVector = new THREE.Vector2(-Math.cos(yaw), -Math.sin(yaw));
  const speed = 10.5;
  player.x += forwardVector.x * forward * speed * deltaSeconds;
  player.z += forwardVector.y * forward * speed * deltaSeconds;
  const edge = mapConfig.world_size / 2 - 1;
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
  if (playerMarker) playerMarker.rotation.y += 0.01;
  renderer.render(scene, camera);
  requestAnimationFrame(animate);
}

async function loadMap(seedOverride = null) {
  const url = seedOverride === null ? '/api/map' : `/api/map?seed=${encodeURIComponent(seedOverride)}`;
  apiText.textContent = `Fetching ${url}`;
  const response = await fetch(url, { cache: 'no-store' });
  if (!response.ok) throw new Error(`map API failed: ${response.status}`);
  packet = await response.json();
  mapConfig = packet.map;
  buildTerrain();
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
    const nextSeed = (mapConfig?.seed ?? 52052) + 97;
    loadMap(nextSeed).catch(error => {
      apiText.textContent = error.message;
    });
  }
});

document.addEventListener('keyup', event => {
  pressedKeys.delete(event.key);
});

window.addEventListener('resize', resize);

resize();
animate();
loadMap().catch(error => {
  apiText.textContent = error.message;
});
