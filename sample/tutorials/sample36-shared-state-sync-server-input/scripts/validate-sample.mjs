import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const fixturePath = path.join(sampleRoot, 'reference/sync-server-input.sample.json');

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

const requiredFiles = [
  'README.md',
  'reference/sync-server-input.sample.json',
  'scripts/validate-sample.mjs'
];

for (const file of requiredFiles) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample36 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample36 must not include node_modules');
assert(!fs.existsSync(path.join(sampleRoot, 'src/server.js')), 'sample36 must not include production server source');

const packet = readJson('reference/sync-server-input.sample.json');

assert(packet.schema_version === 'shared_state_sync_server_input.v1', 'schema_version mismatch');
assert(packet.generated_by?.tool === 'mtool', 'generated_by.tool must be mtool');
assert(packet.generated_by?.artifact === 'shared_state_sync_server_input', 'artifact name mismatch');

const requiredContracts = [
  'shared_state_sync_contract',
  'schema_api_contract',
  'realtime_contract',
  'node_server_input_contract'
];
for (const contract of requiredContracts) {
  assert(packet.contracts?.[contract]?.startsWith('docs/'), `Missing contract reference: ${contract}`);
}

assert(packet.server?.runtime === 'nodejs', 'server runtime must be nodejs');
assert(packet.server?.ownership === 'external_runtime_owner', 'server ownership boundary mismatch');
assert(packet.server?.production_runtime_generated === false, 'sample must not claim production runtime generation');
for (const profile of ['websocket', 'sse_http', 'polling']) {
  assert(packet.server?.transport_profiles?.includes(profile), `Missing transport profile: ${profile}`);
}

assert(packet.backend_integration?.authority === 'app_backend', 'backend authority must remain app_backend');
assert(packet.backend_integration?.base_url_env === 'APP_BACKEND_BASE_URL', 'backend base URL env mismatch');
assert(packet.backend_integration?.auth_context?.app_user_id_source === 'verified_backend_session', 'app_user_id source mismatch');
assert(packet.backend_integration?.auth_context?.sso_token_broadcast_allowed === false, 'SSO token broadcast must be false');
for (const operation of ['verify_session', 'check_room_membership', 'read_state', 'update_state', 'read_latest_revision', 'record_event']) {
  assert(packet.backend_integration?.required_backend_operations?.includes(operation), `Missing backend operation: ${operation}`);
}
assert(packet.backend_integration?.conflict_policy === 'reject_stale_revision', 'conflict policy mismatch');

assert(packet.routes?.websocket?.path === '/sync/ws', 'WebSocket route mismatch');
assert(packet.routes?.websocket?.enabled === true, 'WebSocket route must be enabled');
for (const command of ['room.subscribe', 'room.unsubscribe', 'state.update', 'ping']) {
  assert(packet.routes?.websocket?.commands?.includes(command), `Missing WebSocket command: ${command}`);
}
for (const event of ['state.updated', 'membership.changed', 'room.closed', 'heartbeat', 'reconnect.required']) {
  assert(packet.routes?.websocket?.events?.includes(event), `Missing WebSocket event: ${event}`);
}

assert(packet.routes?.sse?.server_to_client_only === true, 'SSE must be server-to-client only');
assert(packet.routes?.http_update?.method === 'PUT', 'HTTP update method must be PUT');
assert(packet.routes?.polling?.revision_path?.includes('/revision'), 'Polling revision path missing');

assert(packet.auth?.required === true, 'auth must be required');
assert(packet.auth?.session_verification === 'delegate_to_app_backend', 'session verification must delegate to backend');
assert(packet.auth?.connection_identity === 'app_user_id', 'connection identity mismatch');
assert(packet.auth?.room_authorization === 'active_membership_required', 'room authorization mismatch');
for (const forbidden of ['sso_token', 'refresh_token', 'raw_invite_token', 'secret']) {
  assert(packet.auth?.forbidden_in_events?.includes(forbidden), `Missing forbidden event payload marker: ${forbidden}`);
}

assert(packet.rooms?.membership_required === true, 'room subscription must require membership');
assert(packet.rooms?.cross_room_broadcast_allowed === false, 'cross-room broadcast must be forbidden');

assert(packet.state?.state_body_type === 'json', 'state body must be json');
assert(packet.state?.expected_revision_required === true, 'expected revision must be required');
assert(packet.state?.accepted_update_event === 'state.updated', 'accepted update event mismatch');
assert(packet.state?.large_binary_payload_allowed === false, 'large binary payload must be out of scope');

assert(packet.events?.fanout_scope === 'room', 'event fanout scope must be room');
assert(packet.events?.delivery_guarantee === 'best_effort_realtime_plus_latest_fetch', 'delivery guarantee mismatch');
assert(packet.events?.replay_required === false, 'guaranteed replay must not be required');
assert(packet.events?.heartbeat?.enabled === true, 'heartbeat must be enabled');
assert(packet.events?.heartbeat?.timeout_action === 'reconnect_and_latest_fetch', 'heartbeat timeout action mismatch');
assert(packet.events?.reconnect?.client_action === 'reconnect_resubscribe_latest_fetch', 'reconnect action mismatch');

assert(packet.fallbacks?.sse_http?.enabled === true, 'SSE fallback must be enabled');
assert(packet.fallbacks?.sse_http?.update_transport === 'http_put', 'SSE fallback update transport mismatch');
assert(packet.fallbacks?.polling?.enabled === true, 'polling fallback must be enabled');
assert(packet.fallbacks?.polling?.realtime_claim_allowed === false, 'polling must not claim realtime UX');

for (const check of [
  'authenticated_member_can_subscribe',
  'non_member_cannot_subscribe',
  'viewer_cannot_update',
  'editor_can_update',
  'accepted_update_emits_state_updated',
  'other_room_does_not_receive_event',
  'stale_revision_returns_conflict_when_enabled',
  'heartbeat_timeout_triggers_reconnect',
  'reconnect_fetches_latest_state',
  'events_do_not_contain_tokens_or_secrets'
]) {
  assert(packet.validation?.required_checks?.includes(check), `Missing validation check: ${check}`);
}
assert(packet.validation?.implementation_required_before_production === true, 'production must require implementation validation');

for (const action of [
  'install_node_dependencies',
  'initialize_node_project',
  'start_production_server',
  'open_public_port',
  'store_raw_sso_token',
  'broadcast_sso_token',
  'enable_cross_room_broadcast',
  'claim_guaranteed_event_replay',
  'claim_crdt_or_game_loop_support'
]) {
  assert(packet.forbidden_actions?.includes(action), `Missing forbidden action: ${action}`);
}

const readme = read('README.md');
assert(readme.includes('does not install dependencies'), 'README must state no dependency install');
assert(readme.includes('does not install dependencies, initialize a Node.js project, open a port, run WebSocket/SSE runtime'), 'README must state execution-neutral boundary');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample36-shared-state-sync-server-input',
  schema_version: packet.schema_version,
  transport_profiles: packet.server.transport_profiles,
  validation_checks: packet.validation.required_checks.length,
  production_runtime_generated: packet.server.production_runtime_generated
}, null, 2));
