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
  'reference/height-map-threejs-input.sample.json',
  'vendor/three/three.module.js',
  'vendor/three/three.core.js',
  'vendor/three/LICENSE',
  'scripts/validate-sample.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample51 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample51 must not include node_modules');

const packet = JSON.parse(fs.readFileSync(path.join(sampleRoot, 'reference/height-map-threejs-input.sample.json'), 'utf8'));
assert.equal(packet.schema_version, 'height_map_threejs_view_input.v1', 'schema_version mismatch');
assert.equal(packet.runtime_boundary?.nodejs_required, false, 'sample51 must not require a Node.js app server');
assert.equal(packet.runtime_boundary?.static_server_required_for_modules, true, 'sample51 should require only a static module server');
assert.equal(packet.runtime_boundary?.package_dependencies_required, false, 'sample51 must not require npm packages');
assert.equal(packet.runtime_boundary?.external_assets_required, false, 'sample51 must not require external assets');
assert.equal(packet.vendored_dependencies?.[0]?.name, 'three', 'three dependency declaration missing');
assert.equal(packet.vendored_dependencies?.[0]?.version, '0.185.1', 'three version mismatch');
assert.equal(packet.vendored_dependencies?.[0]?.support_paths?.includes('vendor/three/three.core.js'), true, 'three core support path mismatch');
assert.equal(packet.vendored_dependencies?.[0]?.license_path, 'vendor/three/LICENSE', 'three license path mismatch');
assert.equal(packet.view?.renderer, 'threejs_webgl', 'renderer mismatch');
assert.equal(packet.view?.projection, 'perspective_45_degree_camera', 'projection mismatch');
assert.equal(fs.existsSync(path.join(repoRoot, packet.source_inspiration?.canvas_height_map_sample)), true, 'sample50 source ref must exist');

const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
const vendor = fs.readFileSync(path.join(sampleRoot, 'vendor/three/three.module.js'), 'utf8');
assert.equal(html.includes('type="module"'), true, 'sample51 client must run as a module');
assert.equal(js.includes("import * as THREE from '../vendor/three/three.module.js'"), true, 'sample51 must import vendored three');
assert.equal(js.includes('new THREE.WebGLRenderer'), true, 'sample51 must use WebGLRenderer');
assert.equal(js.includes('new THREE.PlaneGeometry'), true, 'sample51 must create terrain mesh');
assert.equal(js.includes('geometry.computeVertexNormals'), true, 'sample51 must compute terrain normals');
assert.equal(js.includes('Math.PI / 4'), true, 'sample51 must initialize 45-degree camera orientation');
assert.equal(js.includes('DirectionalLight'), true, 'sample51 must include directional light');
assert.equal(vendor.includes('REVISION'), true, 'vendored three module should look like a three.js build');

for (const action of [
  'install_dependencies',
  'load_cdn_runtime_dependency',
  'generate_game_engine_project',
  'claim_production_terrain_engine',
  'claim_collision_or_pathfinding_support',
  'publish_or_deploy'
]) {
  assert.equal(packet.forbidden_actions.includes(action), true, `Missing forbidden action: ${action}`);
}

console.log('sample51 validation OK');
