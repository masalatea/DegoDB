import fs from 'node:fs';
import path from 'node:path';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function read(relativePath) {
  return fs.readFileSync(path.join(sampleRoot, relativePath), 'utf8');
}

function readJson(relativePath) {
  return JSON.parse(read(relativePath));
}

for (const file of [
  'README.md',
  'reference/sync-client-input.sample.json',
  'scripts/validate-sample.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample37 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample37 must not include node_modules');
assert(!fs.existsSync(path.join(sampleRoot, 'src')), 'sample37 must not include generated app source');

const packet = readJson('reference/sync-client-input.sample.json');

assert(packet.schema_version === 'shared_state_sync_client_input.v1', 'schema_version mismatch');
assert(packet.generated_by?.tool === 'mtool', 'generated_by.tool must be mtool');
assert(packet.generated_by?.artifact === 'shared_state_sync_client_input', 'artifact name mismatch');

for (const contract of [
  'shared_state_sync_contract',
  'schema_api_contract',
  'realtime_contract',
  'node_server_input_contract',
  'client_input_contract'
]) {
  assert(packet.contracts?.[contract]?.startsWith('docs/'), `Missing contract reference: ${contract}`);
}

assert(packet.client?.ownership === 'external_app_client_owner', 'client ownership mismatch');
assert(packet.client?.source_generation === false, 'client source generation must be false');
assert(packet.client?.sdk_generation === false, 'client SDK generation must be false');

assert(packet.backend?.api_base_url_env === 'APP_BACKEND_BASE_URL', 'backend API base URL env mismatch');
assert(packet.backend?.auth?.mode === 'app_owned_sso_session', 'auth mode mismatch');
assert(packet.backend?.auth?.token_storage_owner === 'app_client_owner', 'token storage owner mismatch');
assert(packet.backend?.auth?.do_not_store_tokens_in_packet === true, 'packet must not store tokens');
for (const [key, value] of Object.entries(packet.backend?.authority ?? {})) {
  assert(value === 'app_backend', `backend authority for ${key} must be app_backend`);
}

assert(packet.room_flow?.create_room?.method === 'POST', 'create room method mismatch');
assert(packet.room_flow?.list_rooms?.method === 'GET', 'list rooms method mismatch');
assert(packet.room_flow?.join_by_invite?.method === 'POST', 'join by invite method mismatch');
assert(packet.room_flow?.join_by_invite?.raw_invite_token_storage === 'do_not_persist_after_join', 'raw invite token must not persist after join');
assert(packet.room_flow?.membership_required_after_join === true, 'membership must be required after join');

assert(packet.state_flow?.get_state?.method === 'GET', 'get state method mismatch');
assert(packet.state_flow?.update_state?.method === 'PUT', 'update state method mismatch');
assert(packet.state_flow?.update_state?.expected_revision_required === true, 'expected revision must be required');
assert(packet.state_flow?.latest_revision?.path?.endsWith('/revision'), 'latest revision path missing');
assert(packet.state_flow?.conflict_error === 'stale_revision', 'conflict error mismatch');

assert(packet.realtime_flow?.primary_transport === 'websocket', 'primary transport must be websocket');
assert(packet.realtime_flow?.websocket?.path === '/sync/ws', 'websocket path mismatch');
for (const command of ['room.subscribe', 'room.unsubscribe', 'state.update', 'ping']) {
  assert(Object.values(packet.realtime_flow?.websocket ?? {}).includes(command), `Missing websocket command: ${command}`);
}
for (const event of ['state.updated', 'membership.changed', 'room.closed', 'heartbeat', 'reconnect.required']) {
  assert(packet.realtime_flow?.events?.includes(event), `Missing event: ${event}`);
}
assert(packet.realtime_flow?.event_application_policy === 'apply_next_revision_or_fetch_latest', 'event application policy mismatch');

assert(packet.fallbacks?.sse_http?.enabled === true, 'SSE fallback must be enabled');
assert(packet.fallbacks?.sse_http?.update_transport === 'http_put', 'SSE fallback update transport mismatch');
assert(packet.fallbacks?.polling?.enabled === true, 'polling fallback must be enabled');
assert(packet.fallbacks?.polling?.realtime_claim_allowed === false, 'polling must not claim realtime');

assert(packet.reconnect?.strategy === 'backoff_resubscribe_latest_fetch', 'reconnect strategy mismatch');
for (const step of [
  'detect_disconnect_or_heartbeat_timeout',
  'reconnect_with_backoff',
  'resubscribe_rooms',
  'fetch_latest_revision',
  'fetch_latest_state_when_revision_changed_or_unknown'
]) {
  assert(packet.reconnect?.steps?.includes(step), `Missing reconnect step: ${step}`);
}
assert(packet.reconnect?.event_replay_required === false, 'event replay must not be required');

for (const check of [
  'join_room_by_invite_discards_raw_token_after_join',
  'subscribe_requires_authenticated_session',
  'state_update_sends_expected_revision',
  'stale_revision_fetches_latest_state',
  'heartbeat_timeout_reconnects',
  'reconnect_resubscribes_and_fetches_latest',
  'membership_loss_unsubscribes_room',
  'polling_does_not_claim_realtime',
  'packet_contains_no_tokens_or_secrets'
]) {
  assert(packet.validation?.required_checks?.includes(check), `Missing validation check: ${check}`);
}

for (const action of [
  'generate_client_sdk',
  'generate_react_source',
  'generate_flutter_source',
  'generate_react_native_source',
  'install_dependencies',
  'choose_token_storage',
  'persist_raw_invite_token',
  'enable_offline_sync',
  'claim_realtime_when_polling',
  'claim_crdt_or_game_loop_support'
]) {
  assert(packet.forbidden_actions?.includes(action), `Missing forbidden action: ${action}`);
}

const readme = read('README.md');
assert(readme.includes('does not install dependencies'), 'README must state no dependency install');
assert(readme.includes('generate React/Flutter/React Native source'), 'README must state no source generation');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample37-shared-state-sync-client-input',
  schema_version: packet.schema_version,
  validation_checks: packet.validation.required_checks.length,
  source_generation: packet.client.source_generation,
  sdk_generation: packet.client.sdk_generation
}, null, 2));
