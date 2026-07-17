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
  'reference/height-map-rpg-input.sample.json',
  'scripts/validate-sample.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample50 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample50 must not include node_modules');

const packet = JSON.parse(fs.readFileSync(path.join(sampleRoot, 'reference/height-map-rpg-input.sample.json'), 'utf8'));
assert.equal(packet.schema_version, 'height_map_rpg_view_input.v1', 'schema_version mismatch');
assert.equal(packet.runtime_boundary?.nodejs_required, false, 'sample50 must not require Node.js');
assert.equal(packet.runtime_boundary?.package_dependencies_required, false, 'sample50 must not require packages');
assert.equal(packet.runtime_boundary?.external_assets_required, false, 'sample50 must not require external assets');
assert.equal(packet.runtime_boundary?.production_runtime_generated, false, 'sample50 must not claim production runtime generation');
assert.equal(packet.height_map?.columns, 48, 'columns mismatch');
assert.equal(packet.height_map?.rows, 48, 'rows mismatch');
assert.equal(packet.height_map?.noise?.kind, 'smooth_value_noise', 'noise kind mismatch');
assert.equal(packet.height_map?.noise?.octaves >= 4, true, 'smooth map should use multiple octaves');
assert.equal(packet.view?.projection, 'isometric_45_degree', 'projection must be 45-degree');
assert.equal(packet.view?.camera_scroll, true, 'camera scroll required');
assert.equal(fs.existsSync(path.join(repoRoot, packet.source_inspiration?.rpg_map_sample)), true, 'sample47 source ref must exist');
assert.equal(fs.existsSync(path.join(repoRoot, packet.source_inspiration?.ai_plugin_content_root)), true, 'AI plugin source ref must exist');

const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
assert.equal(html.includes('<canvas'), true, 'sample50 should render a canvas map');
assert.equal(js.includes('function valueNoise'), true, 'sample50 should implement smooth value noise');
assert.equal(js.includes('function project'), true, 'sample50 should implement projection');
assert.equal(js.includes('(col - row)'), true, 'projection should use 45-degree isometric x transform');
assert.equal(js.includes('(col + row)'), true, 'projection should use 45-degree isometric y transform');
assert.equal(js.includes('heightScale'), true, 'height projection must affect y');
assert.equal(js.includes('requestAnimationFrame'), true, 'sample50 should render continuously for panning');

for (const action of [
  'install_dependencies',
  'generate_game_engine_project',
  'claim_production_terrain_engine',
  'claim_collision_or_pathfinding_support',
  'claim_world_streaming_support',
  'publish_or_deploy'
]) {
  assert.equal(packet.forbidden_actions.includes(action), true, `Missing forbidden action: ${action}`);
}

console.log('sample50 validation OK');
