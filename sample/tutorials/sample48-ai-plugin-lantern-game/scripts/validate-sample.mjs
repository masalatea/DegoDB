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
  'reference/lantern-game-input.sample.json',
  'scripts/validate-sample.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample48 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample48 must not include node_modules');

const packet = JSON.parse(fs.readFileSync(path.join(sampleRoot, 'reference/lantern-game-input.sample.json'), 'utf8'));

assert.equal(packet.schema_version, 'ai_plugin_lantern_game_input.v1', 'schema_version mismatch');
assert.equal(packet.generated_by?.tool, 'mtool', 'generated_by.tool must be mtool');
assert.equal(packet.runtime_boundary?.nodejs_required, false, 'sample48 must not require Node.js');
assert.equal(packet.runtime_boundary?.package_dependencies_required, false, 'sample48 must not require packages');
assert.equal(packet.runtime_boundary?.external_assets_required, false, 'sample48 must not require external assets');
assert.equal(packet.runtime_boundary?.production_runtime_generated, false, 'sample48 must not claim production runtime generation');
assert.equal(packet.audio_metadata?.audio_files_generated, false, 'sample48 must not generate audio files');
assert.equal(packet.audio_metadata?.asset_licensing_decided, false, 'sample48 must not decide asset licensing');

for (const sourceRef of Object.values(packet.plugin_sources)) {
  assert.equal(fs.existsSync(path.join(repoRoot, sourceRef)), true, `Missing plugin source ref: ${sourceRef}`);
}

const charmIds = packet.game.charms.map(charm => charm.id);
assert.equal(new Set(charmIds).size, charmIds.length, 'charm ids must be unique');
assert.equal(packet.game.required_charms, packet.game.charms.length, 'required charms must match declared charms');

for (const action of [
  'install_dependencies',
  'generate_audio_assets',
  'decide_asset_licensing',
  'generate_game_engine_project',
  'claim_production_runtime_generation',
  'publish_or_deploy'
]) {
  assert.equal(packet.forbidden_actions.includes(action), true, `Missing forbidden action: ${action}`);
}

const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
assert.equal(html.includes('<canvas'), true, 'sample48 should render a canvas game');
assert.equal(js.includes('AudioContext'), true, 'sample48 should use metadata-only browser tone adapter');
assert.equal(js.includes('requestAnimationFrame'), true, 'sample48 should have a game loop');

console.log('sample48 validation OK');
