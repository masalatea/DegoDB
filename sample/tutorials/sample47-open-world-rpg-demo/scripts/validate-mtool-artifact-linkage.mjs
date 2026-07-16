import { spawnSync } from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { RPG_RULES, RpgRoomStore } from '../src/rpg-room-store.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const repoRoot = path.resolve(sampleRoot, '../../..');
const gamePacketPath = path.join(sampleRoot, 'reference/open-world-rpg-input.sample.json');

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
  'reference/open-world-rpg-input.sample.json',
  'scripts/validate-mtool-artifact-linkage.mjs',
  'src/rpg-room-store.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample47 Mtool linkage must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample47 Mtool linkage must not include node_modules');

const gamePacket = readJson(gamePacketPath);

assert.equal(gamePacket.schema_version, 'open_world_rpg_input.v1', 'game packet schema_version mismatch');
assert.equal(gamePacket.generated_by?.tool, 'mtool', 'game packet must identify mtool as generator');
assert.equal(gamePacket.generated_by?.artifact, 'open_world_rpg_input', 'game packet artifact mismatch');
assert.equal(gamePacket.runtime_boundary?.owner, 'external_game_runtime_owner', 'runtime ownership boundary mismatch');
assert.equal(gamePacket.runtime_boundary?.production_runtime_generated, false, 'game packet must not claim production runtime generation');
assert.equal(gamePacket.shared_state_mapping?.state_key, 'open_world_rpg', 'RPG state key mismatch');
assert.equal(gamePacket.shared_state_mapping?.revision_required, true, 'revision must be required');
assert.equal(gamePacket.shared_state_mapping?.fanout_scope, 'room', 'fanout scope must be room');
assert.equal(gamePacket.shared_state_mapping?.transport?.event, RPG_RULES.events[0], 'event name must match runtime rule');
assert.equal(gamePacket.room_policy?.max_players, RPG_RULES.max_players, 'max players must match runtime rule');
assert.equal(gamePacket.room_policy?.pvp_enabled, false, 'PvP must remain disabled');
assert.equal(gamePacket.room_policy?.cross_room_broadcast_allowed, false, 'cross-room broadcast must be forbidden');
assert.deepEqual(gamePacket.game_rules?.world, RPG_RULES.world, 'world must match runtime rule');
assert.equal(gamePacket.game_rules?.player_hp, RPG_RULES.player_hp, 'player HP must match runtime rule');
assert.equal(gamePacket.game_rules?.idle_regen_ms, RPG_RULES.idle_regen_ms, 'idle regen must match runtime rule');
assert.equal(gamePacket.game_rules?.enemy_hp, RPG_RULES.enemy_hp, 'enemy HP must match runtime rule');
assert.equal(gamePacket.game_rules?.enemy_speed, RPG_RULES.enemy_speed, 'enemy speed must match runtime rule');
assert.equal(gamePacket.game_rules?.enemy_touch_damage, RPG_RULES.enemy_touch_damage, 'enemy damage must match runtime rule');
assert.equal(gamePacket.game_rules?.sword_range, RPG_RULES.sword_range, 'sword range must match runtime rule');
assert.equal(gamePacket.game_rules?.sword_damage, RPG_RULES.sword_damage, 'sword damage must match runtime rule');
assert.equal(gamePacket.game_rules?.max_enemies, RPG_RULES.max_enemies, 'max enemies must match runtime rule');
assert.deepEqual(gamePacket.game_rules?.commands, RPG_RULES.commands, 'commands must match runtime rule');
assert.deepEqual(gamePacket.game_rules?.events, RPG_RULES.events, 'events must match runtime rule');

for (const check of [
  'room_join_spawns_player',
  'enemy_population_spawns_away_from_player',
  'obstacle_collision_blocks_player_movement',
  'move_command_updates_position',
  'attack_command_damages_enemy',
  'enemy_defeat_rewards_exp_and_gold',
  'enemy_tick_moves_and_weakly_attacks',
  'idle_player_regenerates_hp',
  'pvp_is_forbidden',
  'same_room_sse_event',
  'packet_contains_no_tokens_or_secrets'
]) {
  assert.equal(gamePacket.validation?.required_checks?.includes(check), true, `Missing validation check: ${check}`);
}

for (const forbidden of [
  'enable_player_vs_player_damage',
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

const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'mtool-sample47-linkage-'));
const serverTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-SERVER-INPUT');
const clientTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-CLIENT-INPUT');

try {
  run('php', [
    'mtool/scripts/create_shared_state_sync_server_input.php',
    '--project-key=SAMPLE47',
    '--backend-base-url-env=SAMPLE47_BACKEND_URL',
    `--target-dir=${serverTarget}`
  ]);
  run('php', [
    'mtool/scripts/create_shared_state_sync_client_input.php',
    '--project-key=SAMPLE47',
    '--api-base-url-env=SAMPLE47_BACKEND_URL',
    `--target-dir=${clientTarget}`
  ]);

  const serverPacket = readJson(path.join(serverTarget, 'sync-server-input.json'));
  const clientPacket = readJson(path.join(clientTarget, 'sync-client-input.json'));

  assert.equal(serverPacket.schema_version, 'shared_state_sync_server_input.v1', 'server packet schema mismatch');
  assert.equal(clientPacket.schema_version, 'shared_state_sync_client_input.v1', 'client packet schema mismatch');
  assert.equal(serverPacket.project?.project_key, 'SAMPLE47', 'server packet project key mismatch');
  assert.equal(clientPacket.project?.project_key, 'SAMPLE47', 'client packet project key mismatch');
  assert.equal(serverPacket.backend_integration?.base_url_env, 'SAMPLE47_BACKEND_URL', 'server backend env mismatch');
  assert.equal(clientPacket.backend?.api_base_url_env, 'SAMPLE47_BACKEND_URL', 'client backend env mismatch');
  assert.equal(serverPacket.events?.fanout_scope, gamePacket.shared_state_mapping.fanout_scope, 'server fanout scope must support RPG packet');
  assert.equal(clientPacket.fallbacks?.sse_http?.enabled, true, 'client packet must support SSE/HTTP fallback');
  assert.equal(serverPacket.mutation_performed, false, 'server packet must not claim mutation');
  assert.equal(clientPacket.mutation_performed, false, 'client packet must not claim mutation');

  const store = new RpgRoomStore({
    filePath: path.join(tempRoot, 'sample47-rpg-state.json'),
    now: () => Date.UTC(2026, 6, 16, 0, 0, 0)
  });
  const player1 = store.joinRoom('Artifact World', 'alpha');
  const player2 = store.joinRoom('Artifact World', 'bravo');

  assert.equal(player1.ok, true, 'first player can join according to contract');
  assert.equal(player2.ok, true, 'second player can join because max_players is unlimited');
  assert.equal(Object.keys(player2.state.enemies).length, RPG_RULES.max_enemies, 'runtime enemy population matches contract');
  assert.equal(player2.state.obstacles.length, RPG_RULES.obstacles.length, 'runtime obstacle count matches contract');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample47-open-world-rpg-demo',
    slice: 'mtool_artifact_linkage',
    generated_server_packet: 'SHARED-STATE-SYNC-SERVER-INPUT/sync-server-input.json',
    generated_client_packet: 'SHARED-STATE-SYNC-CLIENT-INPUT/sync-client-input.json',
    game_packet: 'reference/open-world-rpg-input.sample.json',
    project_key: 'SAMPLE47',
    state_key: gamePacket.shared_state_mapping.state_key,
    event: gamePacket.shared_state_mapping.transport.event,
    pvp_enabled: false,
    dependency_free: true,
    production_runtime_generated: false
  }, null, 2));
} finally {
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
