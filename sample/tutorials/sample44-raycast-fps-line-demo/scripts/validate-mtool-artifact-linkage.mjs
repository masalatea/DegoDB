import { spawnSync } from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { FPS_RULES, INACTIVE_RESET_MS, RaycastFpsStore } from '../src/raycast-fps-store.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const repoRoot = path.resolve(sampleRoot, '../../..');
const gamePacketPath = path.join(sampleRoot, 'reference/raycast-fps-line-input.sample.json');

function readJson(absolutePath) {
  return JSON.parse(fs.readFileSync(absolutePath, 'utf8'));
}

function run(command, args) {
  const result = spawnSync(command, args, {
    cwd: repoRoot,
    encoding: 'utf8'
  });
  if (result.status !== 0) {
    throw new Error([
      `command failed: ${command} ${args.join(' ')}`,
      result.stdout,
      result.stderr
    ].join('\n'));
  }
  return result;
}

for (const file of [
  'reference/raycast-fps-line-input.sample.json',
  'scripts/validate-mtool-artifact-linkage.mjs',
  'src/raycast-fps-store.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample44 Mtool linkage must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample44 Mtool linkage must not include node_modules');

const gamePacket = readJson(gamePacketPath);

assert.equal(gamePacket.schema_version, 'raycast_fps_line_input.v1', 'game packet schema_version mismatch');
assert.equal(gamePacket.generated_by?.tool, 'mtool', 'game packet must identify mtool as generator');
assert.equal(gamePacket.generated_by?.artifact, 'raycast_fps_line_input', 'game packet artifact mismatch');
assert.equal(gamePacket.runtime_boundary?.owner, 'external_game_runtime_owner', 'runtime ownership boundary mismatch');
assert.equal(gamePacket.runtime_boundary?.production_runtime_generated, false, 'game packet must not claim production runtime generation');
assert.equal(gamePacket.shared_state_mapping?.state_key, 'raycast_fps', 'state key mismatch');
assert.equal(gamePacket.shared_state_mapping?.revision_required, true, 'revision must be required');
assert.equal(gamePacket.shared_state_mapping?.fanout_scope, 'room', 'fanout scope must be room');
assert.equal(gamePacket.shared_state_mapping?.transport?.event, FPS_RULES.events[0], 'event name must match runtime rule');
assert.equal(gamePacket.room_policy?.max_players, FPS_RULES.max_players, 'max players must match runtime rule');
assert.equal(gamePacket.room_policy?.join_mid_game_allowed, true, 'mid-game join must be allowed');
assert.equal(gamePacket.room_policy?.inactive_reset_after_days, INACTIVE_RESET_MS / (24 * 60 * 60 * 1000), 'inactive reset days mismatch');
assert.equal(gamePacket.rendering?.style, 'line_only_raycasting', 'rendering style mismatch');
assert.equal(gamePacket.rendering?.webgl_required, false, 'WebGL must not be required');
assert.equal(gamePacket.rendering?.texture_assets_required, false, 'texture assets must not be required');
assert.equal(gamePacket.rendering?.angle_granularity_degrees, FPS_RULES.turn_degrees, 'turn granularity mismatch');
assert.equal(gamePacket.game_rules?.player_hp, FPS_RULES.player_hp, 'player HP must match runtime rule');
assert.equal(gamePacket.game_rules?.shot_damage, FPS_RULES.shot_damage, 'shot damage must match runtime rule');
assert.deepEqual(gamePacket.game_rules?.commands, FPS_RULES.commands, 'commands must match runtime rule');
assert.deepEqual(gamePacket.game_rules?.events, FPS_RULES.events, 'events must match runtime rule');

for (const contract of [
  'shared_state_sync_contract',
  'schema_api_contract',
  'realtime_contract',
  'node_server_input_contract',
  'client_input_contract'
]) {
  assert.match(gamePacket.contracts?.[contract] ?? '', /^docs\//, `Missing contract reference: ${contract}`);
}

for (const check of [
  'mtool_server_packet_can_be_emitted',
  'mtool_client_packet_can_be_emitted',
  'raycast_contract_matches_runtime_rules',
  'line_only_canvas_rendering',
  'fine_grained_angle_turning',
  'wall_collision_blocks_move',
  'forward_angle_shooting',
  'shot_hit_reduces_life',
  'life_zero_defeats_player',
  'last_alive_wins',
  'inactive_7_day_reset',
  'cross_room_broadcast_forbidden',
  'packet_contains_no_tokens_or_secrets'
]) {
  assert.equal(gamePacket.validation?.required_checks?.includes(check), true, `Missing validation check: ${check}`);
}

for (const forbidden of [
  'store_sso_tokens_in_game_packet',
  'broadcast_to_all_rooms',
  'claim_production_game_server_generation',
  'claim_matchmaking_or_anticheat_support',
  'claim_authoritative_production_tick_loop',
  'require_texture_or_model_assets'
]) {
  assert.equal(gamePacket.forbidden_actions?.includes(forbidden), true, `Missing forbidden action: ${forbidden}`);
}

const serializedPacket = JSON.stringify(gamePacket).toLowerCase();
for (const forbiddenToken of ['access_token', 'refresh_token', 'client_secret', 'password']) {
  assert.equal(serializedPacket.includes(forbiddenToken), false, `Game packet must not contain ${forbiddenToken}`);
}

const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'mtool-sample44-linkage-'));
const serverTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-SERVER-INPUT');
const clientTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-CLIENT-INPUT');

try {
  run('php', [
    'mtool/scripts/create_shared_state_sync_server_input.php',
    '--project-key=SAMPLE44',
    '--backend-base-url-env=SAMPLE44_BACKEND_URL',
    `--target-dir=${serverTarget}`
  ]);
  run('php', [
    'mtool/scripts/create_shared_state_sync_client_input.php',
    '--project-key=SAMPLE44',
    '--api-base-url-env=SAMPLE44_BACKEND_URL',
    `--target-dir=${clientTarget}`
  ]);

  const serverPacket = readJson(path.join(serverTarget, 'sync-server-input.json'));
  const clientPacket = readJson(path.join(clientTarget, 'sync-client-input.json'));

  assert.equal(serverPacket.schema_version, 'shared_state_sync_server_input.v1', 'server packet schema mismatch');
  assert.equal(clientPacket.schema_version, 'shared_state_sync_client_input.v1', 'client packet schema mismatch');
  assert.equal(serverPacket.project?.project_key, 'SAMPLE44', 'server packet project key mismatch');
  assert.equal(clientPacket.project?.project_key, 'SAMPLE44', 'client packet project key mismatch');
  assert.equal(serverPacket.events?.fanout_scope, gamePacket.shared_state_mapping.fanout_scope, 'server fanout scope must support game packet');
  assert.equal(clientPacket.fallbacks?.sse_http?.enabled, true, 'client packet must support SSE/HTTP fallback');
  assert.equal(serverPacket.mutation_performed, false, 'server packet must not claim mutation');
  assert.equal(clientPacket.mutation_performed, false, 'client packet must not claim mutation');

  const store = new RaycastFpsStore({
    filePath: path.join(tempRoot, 'sample44-game-state.json'),
    now: () => Date.UTC(2026, 6, 16, 0, 0, 0)
  });
  const player1 = store.joinRoom('Artifact FPS', 'alpha');
  const player2 = store.joinRoom('Artifact FPS', 'bravo');
  const player3 = store.joinRoom('Artifact FPS', 'charlie');

  assert.equal(player1.ok, true, 'first player can join according to contract');
  assert.equal(player2.ok, true, 'second player can join according to contract');
  assert.equal(player3.ok, true, 'third player can join because max_players is unlimited');
  assert.equal(Object.keys(player3.state.players).length, 3, 'runtime player count follows unlimited contract');
  assert.deepEqual(player3.state.map, FPS_RULES.map, 'runtime exposes contract map');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample44-raycast-fps-line-demo',
    slice: 'mtool_artifact_linkage',
    generated_server_packet: 'SHARED-STATE-SYNC-SERVER-INPUT/sync-server-input.json',
    generated_client_packet: 'SHARED-STATE-SYNC-CLIENT-INPUT/sync-client-input.json',
    game_packet: 'reference/raycast-fps-line-input.sample.json',
    project_key: 'SAMPLE44',
    state_key: gamePacket.shared_state_mapping.state_key,
    event: gamePacket.shared_state_mapping.transport.event,
    angle_granularity_degrees: gamePacket.rendering.angle_granularity_degrees,
    dependency_free: true,
    production_runtime_generated: false
  }, null, 2));
} finally {
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
