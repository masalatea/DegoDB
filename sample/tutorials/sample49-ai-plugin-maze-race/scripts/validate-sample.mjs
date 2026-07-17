import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const repoRoot = path.resolve(sampleRoot, '../../..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/game.js',
  'reference/maze-race-input.sample.json',
  'src/maze-room-store.mjs',
  'src/server.mjs',
  'scripts/validate-sample.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample49 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample49 must not include node_modules');

const packet = JSON.parse(fs.readFileSync(path.join(sampleRoot, 'reference/maze-race-input.sample.json'), 'utf8'));

assert.equal(packet.schema_version, 'ai_plugin_maze_race_input.v1', 'schema_version mismatch');
assert.equal(packet.runtime_boundary?.nodejs_required, true, 'sample49 must use a local Node.js room server');
assert.equal(packet.runtime_boundary?.package_dependencies_required, false, 'sample49 must not require packages');
assert.equal(packet.runtime_boundary?.external_assets_required, false, 'sample49 must not require external assets');
assert.equal(packet.runtime_boundary?.production_runtime_generated, false, 'sample49 must not claim production runtime generation');
assert.equal(packet.shared_state_mapping?.transport?.event, 'maze.updated', 'sample49 must expose maze.updated SSE event');
assert.equal(packet.game_rules?.maze?.columns >= 35, true, 'maze must be large');
assert.equal(packet.game_rules?.maze?.rows >= 35, true, 'maze must be large');
assert.equal(packet.game_rules?.maze?.scrolling_camera, true, 'maze must scroll');
assert.equal(packet.game_rules?.racers?.count, 4, 'four racers required');
assert.deepEqual(packet.game_rules?.racers?.starts, ['north_west', 'north_east', 'south_west', 'south_east']);
assert.equal(packet.game_rules?.movement?.rotation_rate_degrees_per_second, 90, 'rotation rate must be 90 degrees/sec');
assert.equal(packet.game_rules?.movement?.space_hold_behavior, 'drive_forward_without_rotation');
assert.equal(packet.game_rules?.movement?.space_released_behavior, 'rotate_in_place');

const pluginRoot = packet.source_inspiration?.ai_plugin_content_root;
assert.equal(fs.existsSync(path.join(repoRoot, pluginRoot)), true, 'AI plugin content root ref must exist');

const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
const server = fs.readFileSync(path.join(sampleRoot, 'src/server.mjs'), 'utf8');
const store = fs.readFileSync(path.join(sampleRoot, 'src/maze-room-store.mjs'), 'utf8');
assert.equal(html.includes('<canvas'), true, 'sample49 should render a canvas game');
assert.equal(html.includes('type="module"'), true, 'sample49 client should run as a module');
assert.equal(js.includes("event.code === 'Space'"), true, 'game.js should use Space hold');
assert.equal(js.includes('camera()'), true, 'game.js should implement camera scrolling');
assert.equal(js.includes('EventSource'), true, 'game.js should subscribe to room events');
assert.equal(js.includes('/api/rooms/'), true, 'game.js should use room API');
assert.equal(server.includes('maze.updated'), true, 'server should publish maze.updated');
assert.equal(server.includes('/api/rooms/'), true, 'server should expose room APIs');
assert.equal(store.includes('const ROTATION_RATE = 90'), true, 'store should own 90 deg/sec rotation');
assert.equal(store.includes('function computePath'), true, 'AI racers should race through maze with paths');
assert.equal(store.includes('holding'), true, 'store should model Space hold state');
assert.equal(store.includes('room_full'), true, 'store should cap human joins at the four corner slots');

for (const action of [
  'install_dependencies',
  'generate_game_engine_project',
  'claim_production_multiplayer',
  'claim_matchmaking_or_anticheat_support',
  'publish_or_deploy'
]) {
  assert.equal(packet.forbidden_actions.includes(action), true, `Missing forbidden action: ${action}`);
}

console.log('sample49 validation OK');
