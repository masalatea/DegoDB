import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/game.js',
  'reference/choice-adventure-input.sample.json',
  'scripts/validate-sample.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample46 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample46 must not include node_modules');

const packet = JSON.parse(fs.readFileSync(path.join(sampleRoot, 'reference/choice-adventure-input.sample.json'), 'utf8'));

assert.equal(packet.schema_version, 'choice_adventure_game_input.v1', 'schema_version mismatch');
assert.equal(packet.generated_by?.tool, 'mtool', 'generated_by.tool must be mtool');
assert.equal(packet.generated_by?.artifact, 'choice_adventure_game_input', 'artifact mismatch');
assert.equal(packet.runtime_boundary?.nodejs_required, false, 'choice adventure sample must not require Node.js');
assert.equal(packet.runtime_boundary?.peer_communication_required, false, 'choice adventure sample must not require peer communication');
assert.equal(packet.runtime_boundary?.adventure_api_required, true, 'choice adventure sample must model adventure API communication');
assert.equal(packet.runtime_boundary?.default_api_mode, 'mock_adapter', 'default API mode must be mock adapter');
assert.equal(packet.api_contract?.choose?.method, 'POST', 'choose API must be POST');
assert.equal(packet.api_contract?.choose?.path, '/adventure/choose', 'choose API path mismatch');
assert.equal(packet.api_contract?.nodejs_required, false, 'API contract must not require Node.js');
assert.equal(packet.api_contract?.peer_communication, false, 'API contract must not be peer communication');
assert.equal(packet.game?.opening_scene, 'opening', 'opening scene mismatch');
assert.equal(packet.game?.goal_scene, 'ending_good', 'goal scene mismatch');
assert.equal(packet.game?.game_over_scene, 'ending_bad', 'game over scene mismatch');
assert.equal(packet.game?.minimum_goal_choices, 5, 'goal path should require about five choices');

for (const inputMode of ['keyboard_up_down_enter', 'mouse_click', 'touch_tap']) {
  assert.equal(packet.game.input_modes.includes(inputMode), true, `Missing input mode: ${inputMode}`);
}

const scenes = new Map(packet.scenes.map(scene => [scene.id, scene]));
for (const scene of packet.scenes) {
  for (const field of packet.scene_schema.required_fields) {
    assert.notEqual(scene[field], undefined, `Scene ${scene.id} missing ${field}`);
  }
  assert.equal(Array.isArray(scene.choices), true, `Scene ${scene.id} choices must be array`);
  for (const choice of scene.choices) {
    assert.equal(typeof choice.label, 'string', `Scene ${scene.id} choice label missing`);
    assert.equal(typeof choice.target, 'string', `Scene ${scene.id} choice target missing`);
    assert.equal(choice.target === '__back__' || scenes.has(choice.target), true, `Scene ${scene.id} choice target missing scene: ${choice.target}`);
  }
}

let sceneId = packet.game.opening_scene;
const goalPath = [];
while (sceneId !== packet.game.goal_scene) {
  const scene = scenes.get(sceneId);
  goalPath.push(sceneId);
  const next = scene.choices.find(choice => choice.target !== packet.game.game_over_scene && choice.target !== '__back__');
  assert.notEqual(next, undefined, `No forward goal path from ${sceneId}`);
  sceneId = next.target;
  assert.equal(goalPath.length <= 8, true, 'goal path should stay small');
}
goalPath.push(sceneId);
assert.equal(goalPath.length - 1 >= packet.game.minimum_goal_choices, true, 'goal path must include at least five choices');

const gameOver = scenes.get(packet.game.game_over_scene);
assert.equal(gameOver.kind, 'game_over', 'game over scene kind mismatch');
assert.equal(gameOver.choices.some(choice => choice.target === '__back__'), true, 'game over must allow return/back');
assert.equal(gameOver.choices.some(choice => choice.target === packet.game.opening_scene), true, 'game over must allow restart');

for (const check of [
  'nodejs_not_required',
  'peer_communication_not_required',
  'adventure_api_contract',
  'mock_api_adapter',
  'opening_scene_present',
  'five_choice_goal_path',
  'game_over_path',
  'back_or_restart_after_game_over',
  'keyboard_up_down_enter',
  'mouse_or_touch_choice',
  'structured_scenario_data',
  'no_external_image_assets_required'
]) {
  assert.equal(packet.validation.required_checks.includes(check), true, `Missing validation check: ${check}`);
}

const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
assert.match(html, /choices/, 'HTML includes choices container');
assert.match(html, /Mouse\/touch supported/, 'HTML advertises mouse/touch support');

const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
assert.match(js, /ArrowUp/, 'keyboard up is handled');
assert.match(js, /ArrowDown/, 'keyboard down is handled');
assert.match(js, /Enter/, 'keyboard enter is handled');
assert.match(js, /addEventListener\('click'/, 'choice click is handled');
assert.match(js, /__back__/, 'back target is handled');
assert.match(js, /createAdventureApi/, 'runtime uses adventure API adapter');
assert.match(js, /createMockAdventureApi/, 'runtime includes mock API adapter');
assert.match(js, /\/adventure\/choose/, 'runtime can call choose API endpoint');
assert.match(js, /fetch/, 'runtime supports API fetch communication');
assert.match(js, /choice-adventure-input\.sample\.json/, 'runtime imports structured scenario data');

const css = fs.readFileSync(path.join(sampleRoot, 'public/styles.css'), 'utf8');
for (const frame of ['moon_gate', 'archive_hall', 'spiral_stair', 'clock_room', 'paper_bridge', 'keeper_desk', 'sunrise_exit', 'lost_pages']) {
  assert.match(css, new RegExp(`\\.${frame}`), `CSS frame missing: ${frame}`);
}

console.log(JSON.stringify({
  ok: true,
  sample: 'sample46-choice-adventure-game',
  scenes: packet.scenes.length,
  goal_choices: goalPath.length - 1,
  keyboard: true,
  mouse_touch: true,
  nodejs_required: packet.runtime_boundary.nodejs_required,
  peer_communication_required: packet.runtime_boundary.peer_communication_required,
  adventure_api_required: packet.runtime_boundary.adventure_api_required
}, null, 2));
