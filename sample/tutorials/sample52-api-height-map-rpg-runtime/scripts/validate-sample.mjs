import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createMapPacket } from '../src/map-packet.mjs';

const sampleRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function assert(condition, message) {
  if (!condition) throw new Error(message);
}

function readJson(relativePath) {
  return JSON.parse(fs.readFileSync(path.join(sampleRoot, relativePath), 'utf8'));
}

function exists(relativePath) {
  return fs.existsSync(path.join(sampleRoot, relativePath));
}

const packet = createMapPacket();
const reference = readJson('reference/api-height-map-packet.sample.json');

assert(packet.schema_version === 'mtool_height_map_packet.v1', 'packet schema_version mismatch');
assert(reference.schema_version === packet.schema_version, 'reference schema_version mismatch');
assert(packet.generated_by.tool === 'mtool', 'packet must declare mtool generator');
assert(packet.generated_by.interface === 'ai_facing_map_api', 'packet must declare API interface');
assert(packet.map.columns >= 32 && packet.map.rows >= 32, 'map grid must be large enough for terrain');
assert(packet.map.world_size > 0, 'world_size must be positive');
assert(packet.map.height_scale > 0, 'height_scale must be positive');
assert(Number.isInteger(packet.map.seed), 'seed must be an integer');
assert(packet.map.terrain.kind === 'smooth_value_noise', 'terrain kind must be smooth_value_noise');
assert(packet.map.terrain.normalized_range[0] === 0 && packet.map.terrain.normalized_range[1] === 1, 'height range must be normalized');
assert(packet.map.materials.length >= 3, 'materials must include terrain bands');
assert(packet.map.materials.at(-1).max_height === 1, 'last material must cover max height');
assert(typeof packet.map.player_start.x === 'number' && typeof packet.map.player_start.z === 'number', 'player_start must include x/z');

for (const relativePath of [
  'src/server.mjs',
  'src/map-packet.mjs',
  'public/index.html',
  'public/game.js',
  'public/styles.css',
  'vendor/three/three.module.js',
  'vendor/three/three.core.js',
  'vendor/three/LICENSE'
]) {
  assert(exists(relativePath), `missing ${relativePath}`);
}

const serverSource = fs.readFileSync(path.join(sampleRoot, 'src/server.mjs'), 'utf8');
const gameSource = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
assert(serverSource.includes('/api/map'), 'server must expose /api/map');
assert(gameSource.includes("fetch(url"), 'runtime must fetch API map packet');
assert(gameSource.includes("import * as THREE from '/vendor/three/three.module.js'"), 'runtime must use vendored Three.js');

console.log('sample52 validation OK');
