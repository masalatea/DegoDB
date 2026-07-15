import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { SharedStateSyncRuntime } from '../src/shared-state-sync-runtime.mjs';
import { createSharedStateSyncHttpServer } from '../src/shared-state-sync-http-server.mjs';

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
  'src/shared-state-sync-runtime.mjs',
  'src/shared-state-sync-http-server.mjs',
  'scripts/validate-mtool-artifact-linkage.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample38 Mtool linkage must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample38 Mtool linkage must not include node_modules');

const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'mtool-sample38-linkage-'));
const serverTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-SERVER-INPUT');
const clientTarget = path.join(tempRoot, 'SHARED-STATE-SYNC-CLIENT-INPUT');

try {
  run('php', [
    'mtool/scripts/create_shared_state_sync_server_input.php',
    '--project-key=SAMPLE38',
    '--backend-base-url-env=SAMPLE38_BACKEND_URL',
    `--target-dir=${serverTarget}`
  ]);
  run('php', [
    'mtool/scripts/create_shared_state_sync_client_input.php',
    '--project-key=SAMPLE38',
    '--api-base-url-env=SAMPLE38_BACKEND_URL',
    `--target-dir=${clientTarget}`
  ]);

  const serverPacketPath = path.join(serverTarget, 'sync-server-input.json');
  const clientPacketPath = path.join(clientTarget, 'sync-client-input.json');
  assert(fs.existsSync(serverPacketPath), 'Mtool server packet was emitted');
  assert(fs.existsSync(clientPacketPath), 'Mtool client packet was emitted');
  assert(fs.existsSync(path.join(serverTarget, 'SYNC-SERVER-INPUT.md')), 'Mtool server markdown was emitted');
  assert(fs.existsSync(path.join(clientTarget, 'SYNC-CLIENT-INPUT.md')), 'Mtool client markdown was emitted');

  const serverPacket = readJson(serverPacketPath);
  const clientPacket = readJson(clientPacketPath);

  assert(serverPacket.project?.project_key === 'SAMPLE38', 'server packet project key mismatch');
  assert(clientPacket.project?.project_key === 'SAMPLE38', 'client packet project key mismatch');
  assert(serverPacket.backend_integration?.base_url_env === 'SAMPLE38_BACKEND_URL', 'server backend env mismatch');
  assert(clientPacket.backend?.api_base_url_env === 'SAMPLE38_BACKEND_URL', 'client backend env mismatch');
  assert(serverPacket.mutation_performed === false, 'server packet must not claim mutation');
  assert(clientPacket.mutation_performed === false, 'client packet must not claim mutation');

  const runtime = new SharedStateSyncRuntime({ serverPacket, clientPacket });
  runtime.createRoom({
    roomId: 'room-generated',
    initialState: {
      board: { title: 'Generated packet board', cards: [] }
    },
    members: {
      editor_1: 'editor',
      viewer_1: 'viewer'
    }
  });

  const viewerEvents = [];
  const subscribe = runtime.subscribe({
    roomId: 'room-generated',
    userId: 'viewer_1',
    onEvent: event => viewerEvents.push(event)
  });
  assert(subscribe.ok === true, 'runtime can subscribe using generated packets');

  const update = runtime.updateState({
    roomId: 'room-generated',
    stateKey: 'board',
    userId: 'editor_1',
    expectedRevision: 1,
    body: { title: 'Generated packet update', cards: [{ id: 'card-1' }] }
  });
  assert(update.ok === true, 'runtime can update using generated packets');
  assert(update.state.revision === 2, 'generated packet runtime update increments revision');
  assert(viewerEvents.length === 1, 'generated packet runtime emits same-room event');

  const httpServer = createSharedStateSyncHttpServer({ runtime });
  await new Promise(resolve => {
    httpServer.listen(0, '127.0.0.1', resolve);
  });
  const address = httpServer.address();
  assert(address.address === '127.0.0.1', 'HTTP linkage server must be loopback only');
  await new Promise(resolve => httpServer.close(resolve));

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample38-shared-state-sync-node-runtime',
    slice: 'mtool_artifact_linkage',
    generated_server_packet: 'SHARED-STATE-SYNC-SERVER-INPUT/sync-server-input.json',
    generated_client_packet: 'SHARED-STATE-SYNC-CLIENT-INPUT/sync-client-input.json',
    project_key: 'SAMPLE38',
    dependency_free: true,
    production_server_generated: false,
    runtime_revision: update.state.revision
  }, null, 2));
} finally {
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
