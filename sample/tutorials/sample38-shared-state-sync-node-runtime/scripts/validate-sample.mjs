import fs from 'node:fs';
import path from 'node:path';
import { SharedStateSyncRuntime, eventContainsForbiddenSecrets } from '../src/shared-state-sync-runtime.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const repoRoot = path.resolve(sampleRoot, '../../..');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function readJson(absolutePath) {
  return JSON.parse(fs.readFileSync(absolutePath, 'utf8'));
}

for (const file of [
  'README.md',
  'src/shared-state-sync-runtime.mjs',
  'scripts/validate-sample.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample38 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample38 must not include node_modules');

const serverPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json'));
const clientPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json'));

const runtime = new SharedStateSyncRuntime({ serverPacket, clientPacket });

runtime.createRoom({
  roomId: 'room-alpha',
  initialState: {
    board: { title: 'Draft board', cards: [] }
  },
  members: {
    owner_1: 'owner',
    editor_1: 'editor',
    viewer_1: 'viewer'
  }
});

runtime.createRoom({
  roomId: 'room-beta',
  initialState: {
    board: { title: 'Other room', cards: [] }
  },
  members: {
    editor_2: 'editor'
  }
});

const ownerEvents = [];
const viewerEvents = [];
const betaEvents = [];

const ownerSubscribe = runtime.subscribe({
  roomId: 'room-alpha',
  userId: 'owner_1',
  onEvent: event => ownerEvents.push(event)
});
assert(ownerSubscribe.ok === true, 'authenticated member can subscribe');
assert(ownerSubscribe.latest_revision_summary?.[0]?.revision === 1, 'subscribe includes latest revision summary');

const viewerSubscribe = runtime.subscribe({
  roomId: 'room-alpha',
  userId: 'viewer_1',
  onEvent: event => viewerEvents.push(event)
});
assert(viewerSubscribe.ok === true, 'viewer member can subscribe for read events');

const betaSubscribe = runtime.subscribe({
  roomId: 'room-beta',
  userId: 'editor_2',
  onEvent: event => betaEvents.push(event)
});
assert(betaSubscribe.ok === true, 'second room member can subscribe');

const nonMemberSubscribe = runtime.subscribe({
  roomId: 'room-alpha',
  userId: 'outsider',
  onEvent: () => {}
});
assert(nonMemberSubscribe.ok === false && nonMemberSubscribe.error === 'membership_required', 'non-member cannot subscribe');

const viewerUpdate = runtime.updateState({
  roomId: 'room-alpha',
  stateKey: 'board',
  userId: 'viewer_1',
  expectedRevision: 1,
  body: { title: 'Viewer edit should fail', cards: [] }
});
assert(viewerUpdate.ok === false && viewerUpdate.error === 'update_forbidden', 'viewer cannot update');

const editorUpdate = runtime.updateState({
  roomId: 'room-alpha',
  stateKey: 'board',
  userId: 'editor_1',
  expectedRevision: 1,
  body: { title: 'Published board', cards: [{ id: 'card-1', text: 'hello' }] }
});
assert(editorUpdate.ok === true, 'editor can update state');
assert(editorUpdate.state.revision === 2, 'accepted update increments revision');
assert(ownerEvents.length === 1, 'same room owner receives update event');
assert(viewerEvents.length === 1, 'same room viewer receives update event');
assert(betaEvents.length === 0, 'other room does not receive event');
assert(ownerEvents[0].type === 'state.updated', 'event type is state.updated');
assert(ownerEvents[0].room_id === 'room-alpha', 'event includes room id');
assert(!eventContainsForbiddenSecrets(ownerEvents[0]), 'event does not contain forbidden secret keys');

const staleUpdate = runtime.updateState({
  roomId: 'room-alpha',
  stateKey: 'board',
  userId: 'editor_1',
  expectedRevision: 1,
  body: { title: 'Stale edit', cards: [] }
});
assert(staleUpdate.ok === false && staleUpdate.error === 'stale_revision', 'stale revision is rejected');
assert(staleUpdate.latest.revision === 2, 'stale response includes latest state');

const latest = runtime.reconnectLatestFetch({
  roomId: 'room-alpha',
  stateKey: 'board',
  knownRevision: 1
});
assert(latest.changed === true, 'reconnect latest fetch detects changed revision');
assert(latest.latest.revision === 2, 'reconnect latest fetch returns latest revision');

assert(runtime.latestRevision({ roomId: 'room-alpha', stateKey: 'board' }) === 2, 'latestRevision returns revision 2');
assert(runtime.auditEvents.every(event => !eventContainsForbiddenSecrets(event)), 'audit events contain no forbidden secrets');

const readme = fs.readFileSync(path.join(sampleRoot, 'README.md'), 'utf8');
assert(readme.includes('does not install dependencies'), 'README states no dependency install');
assert(readme.includes('in-process event bus'), 'README states event bus boundary');
assert(readme.includes('does not install dependencies, initialize a production Node.js project, open a public port, run a real WebSocket server'), 'README states production boundary');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample38-shared-state-sync-node-runtime',
  server_schema: serverPacket.schema_version,
  client_schema: clientPacket.schema_version,
  accepted_revision: editorUpdate.state.revision,
  same_room_events: ownerEvents.length + viewerEvents.length,
  cross_room_events: betaEvents.length,
  dependency_free: true,
  production_server_generated: false
}, null, 2));
