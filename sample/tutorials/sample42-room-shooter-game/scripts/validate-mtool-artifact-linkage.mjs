import { spawnSync } from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { GAME_RULES, ShooterRoomStore } from '../src/shooter-room-store.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const repoRoot = path.resolve(sampleRoot, '../../..');
const gamePacketPath = path.join(sampleRoot, 'reference/room-shooter-game-input.sample.json');

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
  'reference/room-shooter-game-input.sample.json',
  'scripts/validate-mtool-artifact-linkage.mjs',
  'src/shooter-room-store.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample42 Mtool linkage must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample42 Mtool linkage must not include node_modules');

const gamePacket = readJson(gamePacketPath);

assert.equal(gamePacket.schema_version, 'room_shooter_game_input.v1', 'game packet schema_version mismatch');
assert.equal(gamePacket.generated_by?.tool, 'mtool', 'game packet must identify mtool as generator');
assert.equal(gamePacket.generated_by?.artifact, 'room_shooter_game_input', 'game packet artifact mismatch');
assert.equal(gamePacket.runtime_boundary?.owner, 'external_game_runtime_owner', 'runtime ownership boundary mismatch');
assert.equal(gamePacket.runtime_boundary?.production_runtime_generated, false, 'game packet must not claim production runtime generation');
assert.equal(gamePacket.shared_state_mapping?.state_key, 'game', 'game state key mismatch');
assert.equal(gamePacket.shared_state_mapping?.revision_required, true, 'revision must be required');
assert.equal(gamePacket.shared_state_mapping?.fanout_scope, 'room', 'fanout scope must be room');
assert.equal(gamePacket.shared_state_mapping?.transport?.event, GAME_RULES.events[0], 'event name must match runtime rule');
assert.equal(gamePacket.room_policy?.max_players, GAME_RULES.max_players, 'max players must match runtime rule');
assert.equal(gamePacket.room_policy?.cross_room_broadcast_allowed, false, 'cross-room broadcast must be forbidden');
assert.equal(gamePacket.game_rules?.schema_version, GAME_RULES.schema_version, 'game schema version must match runtime rule');
assert.deepEqual(gamePacket.game_rules?.arena, GAME_RULES.arena, 'arena must match runtime rule');
assert.equal(gamePacket.game_rules?.player_hp, GAME_RULES.player_hp, 'player HP must match runtime rule');
assert.equal(gamePacket.game_rules?.shot_damage, GAME_RULES.shot_damage, 'shot damage must match runtime rule');
assert.equal(gamePacket.game_rules?.shot_hit_radius, GAME_RULES.shot_hit_radius, 'shot hit radius must match runtime rule');
assert.equal(gamePacket.game_rules?.perspective, 'local_player_bottom_opponent_top', 'perspective rule must be explicit');
assert.equal(gamePacket.game_rules?.shot_direction, 'local_player_upward', 'local shot direction must be explicit');
assert.equal(gamePacket.game_rules?.projectile_tick, 'server_side_interval', 'projectile tick rule must be explicit');
assert.deepEqual(gamePacket.game_rules?.commands, GAME_RULES.commands, 'commands must match runtime rule');
assert.deepEqual(gamePacket.game_rules?.events, GAME_RULES.events, 'events must match runtime rule');

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
  'game_contract_matches_runtime_rules',
  'old_local_room_state_resets_when_runtime_rule_changes',
  'room_join_limit_matches_contract',
  'commands_match_contract',
  'event_name_matches_contract',
  'cross_room_broadcast_forbidden',
  'packet_contains_no_tokens_or_secrets'
]) {
  assert.equal(gamePacket.validation?.required_checks?.includes(check), true, `Missing validation check: ${check}`);
}

for (const forbidden of [
  'store_sso_tokens_in_game_packet',
  'broadcast_to_all_rooms',
  'claim_production_game_server_generation',
  'claim_matchmaking_or_anticheat_support'
]) {
  assert.equal(gamePacket.forbidden_actions?.includes(forbidden), true, `Missing forbidden action: ${forbidden}`);
}

const serializedPacket = JSON.stringify(gamePacket).toLowerCase();
for (const forbiddenToken of ['access_token', 'refresh_token', 'client_secret', 'password']) {
  assert.equal(serializedPacket.includes(forbiddenToken), false, `Game packet must not contain ${forbiddenToken}`);
}

const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'mtool-sample42-linkage-'));
const serverTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-SERVER-INPUT');
const clientTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-CLIENT-INPUT');

try {
  run('php', [
    'mtool/scripts/create_shared_state_sync_server_input.php',
    '--project-key=SAMPLE42',
    '--backend-base-url-env=SAMPLE42_BACKEND_URL',
    `--target-dir=${serverTarget}`
  ]);
  run('php', [
    'mtool/scripts/create_shared_state_sync_client_input.php',
    '--project-key=SAMPLE42',
    '--api-base-url-env=SAMPLE42_BACKEND_URL',
    `--target-dir=${clientTarget}`
  ]);

  const serverPacket = readJson(path.join(serverTarget, 'sync-server-input.json'));
  const clientPacket = readJson(path.join(clientTarget, 'sync-client-input.json'));

  assert.equal(serverPacket.schema_version, 'shared_state_sync_server_input.v1', 'server packet schema mismatch');
  assert.equal(clientPacket.schema_version, 'shared_state_sync_client_input.v1', 'client packet schema mismatch');
  assert.equal(serverPacket.project?.project_key, 'SAMPLE42', 'server packet project key mismatch');
  assert.equal(clientPacket.project?.project_key, 'SAMPLE42', 'client packet project key mismatch');
  assert.equal(serverPacket.backend_integration?.base_url_env, 'SAMPLE42_BACKEND_URL', 'server backend env mismatch');
  assert.equal(clientPacket.backend?.api_base_url_env, 'SAMPLE42_BACKEND_URL', 'client backend env mismatch');
  assert.equal(serverPacket.events?.fanout_scope, gamePacket.shared_state_mapping.fanout_scope, 'server fanout scope must support game packet');
  assert.equal(clientPacket.fallbacks?.sse_http?.enabled, true, 'client packet must support SSE/HTTP fallback');
  assert.equal(serverPacket.mutation_performed, false, 'server packet must not claim mutation');
  assert.equal(clientPacket.mutation_performed, false, 'client packet must not claim mutation');

  const store = new ShooterRoomStore({
    filePath: path.join(tempRoot, 'sample42-game-state.json'),
    now: () => Date.UTC(2026, 6, 16, 0, 0, 0)
  });
  const player1 = store.joinRoom('Artifact Arena');
  const player2 = store.joinRoom('Artifact Arena');
  const player3 = store.joinRoom('Artifact Arena');

  assert.equal(player1.ok, true, 'first player can join according to contract');
  assert.equal(player2.ok, true, 'second player can join according to contract');
  assert.equal(player3.ok, false, 'third player is rejected according to contract');
  assert.equal(player3.error, gamePacket.room_policy.third_player_error, 'third player error matches contract');
  assert.equal(Object.keys(player2.state.players).length, gamePacket.room_policy.max_players, 'runtime player count matches contract');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample42-room-shooter-game',
    slice: 'mtool_artifact_linkage',
    generated_server_packet: 'SHARED-STATE-SYNC-SERVER-INPUT/sync-server-input.json',
    generated_client_packet: 'SHARED-STATE-SYNC-CLIENT-INPUT/sync-client-input.json',
    game_packet: 'reference/room-shooter-game-input.sample.json',
    project_key: 'SAMPLE42',
    state_key: gamePacket.shared_state_mapping.state_key,
    event: gamePacket.shared_state_mapping.transport.event,
    max_players: gamePacket.room_policy.max_players,
    dependency_free: true,
    production_runtime_generated: false
  }, null, 2));
} finally {
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
