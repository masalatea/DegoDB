import http from 'node:http';
import fs from 'node:fs';
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

function requestJson({ baseUrl, method, path: requestPath, userId, body }) {
  const url = new URL(requestPath, baseUrl);
  const payload = body === undefined ? null : JSON.stringify(body);

  return new Promise((resolve, reject) => {
    const request = http.request(url, {
      method,
      headers: {
        ...(userId ? { 'x-user-id': userId } : {}),
        ...(payload ? {
          'content-type': 'application/json',
          'content-length': Buffer.byteLength(payload)
        } : {})
      }
    }, response => {
      let responseBody = '';
      response.setEncoding('utf8');
      response.on('data', chunk => {
        responseBody += chunk;
      });
      response.on('end', () => {
        try {
          resolve({
            statusCode: response.statusCode,
            body: responseBody === '' ? null : JSON.parse(responseBody)
          });
        } catch (error) {
          reject(new Error(`invalid JSON response: ${error.message}; body=${responseBody}`));
        }
      });
    });
    request.on('error', reject);
    if (payload) {
      request.write(payload);
    }
    request.end();
  });
}

function openSse({ baseUrl, path: requestPath, userId, onEvent }) {
  const url = new URL(requestPath, baseUrl);
  const request = http.request(url, {
    method: 'GET',
    headers: {
      'x-user-id': userId,
      accept: 'text/event-stream'
    }
  });

  let buffer = '';
  request.on('response', response => {
    assert(response.statusCode === 200, `SSE status should be 200, got ${response.statusCode}`);
    response.setEncoding('utf8');
    response.on('data', chunk => {
      buffer += chunk;
      let separatorIndex = buffer.indexOf('\n\n');
      while (separatorIndex !== -1) {
        const rawEvent = buffer.slice(0, separatorIndex);
        buffer = buffer.slice(separatorIndex + 2);
        const lines = rawEvent.split('\n');
        const eventName = lines.find(line => line.startsWith('event: '))?.slice('event: '.length);
        const dataLine = lines.find(line => line.startsWith('data: '));
        const data = dataLine ? JSON.parse(dataLine.slice('data: '.length)) : null;
        onEvent({ eventName, data });
        separatorIndex = buffer.indexOf('\n\n');
      }
    });
  });
  request.end();

  return {
    close() {
      request.destroy();
    }
  };
}

for (const file of [
  'src/shared-state-sync-runtime.mjs',
  'src/shared-state-sync-http-server.mjs',
  'scripts/validate-http-sse-sample.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample38 HTTP/SSE slice must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample38 HTTP/SSE slice must not include node_modules');

const serverPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json'));
const clientPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json'));
const runtime = new SharedStateSyncRuntime({ serverPacket, clientPacket });

runtime.createRoom({
  roomId: 'room-http',
  initialState: {
    board: { title: 'HTTP board', cards: [] }
  },
  members: {
    editor_1: 'editor',
    viewer_1: 'viewer'
  }
});

const server = createSharedStateSyncHttpServer({ runtime });
await new Promise(resolve => {
  server.listen(0, '127.0.0.1', resolve);
});

try {
  const address = server.address();
  const baseUrl = `http://127.0.0.1:${address.port}`;
  const sseEvents = [];
  const sse = openSse({
    baseUrl,
    path: '/sync/rooms/room-http/events',
    userId: 'viewer_1',
    onEvent: event => sseEvents.push(event)
  });

  await new Promise(resolve => setTimeout(resolve, 20));
  assert(sseEvents.some(event => event.eventName === 'sync.ready'), 'SSE stream emits ready event');

  const readResponse = await requestJson({
    baseUrl,
    method: 'GET',
    path: '/sync/rooms/room-http/states/board',
    userId: 'viewer_1'
  });
  assert(readResponse.statusCode === 200, 'viewer can read state over HTTP');
  assert(readResponse.body.state.revision === 1, 'HTTP read returns revision 1');

  const outsiderRead = await requestJson({
    baseUrl,
    method: 'GET',
    path: '/sync/rooms/room-http/states/board',
    userId: 'outsider'
  });
  assert(outsiderRead.statusCode === 403, 'non-member read is rejected');

  const viewerUpdate = await requestJson({
    baseUrl,
    method: 'PUT',
    path: '/sync/rooms/room-http/states/board',
    userId: 'viewer_1',
    body: {
      expected_revision: 1,
      body: { title: 'Viewer update should fail', cards: [] }
    }
  });
  assert(viewerUpdate.statusCode === 403, 'viewer update over HTTP is rejected');

  const editorUpdate = await requestJson({
    baseUrl,
    method: 'PUT',
    path: '/sync/rooms/room-http/states/board',
    userId: 'editor_1',
    body: {
      expected_revision: 1,
      body: { title: 'HTTP updated board', cards: [{ id: 'card-1', text: 'hello' }] }
    }
  });
  assert(editorUpdate.statusCode === 200, 'editor update over HTTP succeeds');
  assert(editorUpdate.body.state.revision === 2, 'HTTP update increments revision');

  await new Promise(resolve => setTimeout(resolve, 20));
  const stateUpdated = sseEvents.find(event => event.eventName === 'state.updated');
  assert(stateUpdated, 'SSE stream receives state.updated event');
  assert(stateUpdated.data.room_id === 'room-http', 'SSE event includes room id');
  assert(stateUpdated.data.revision === 2, 'SSE event includes revision 2');
  assert(!JSON.stringify(stateUpdated.data).includes('sso_token'), 'SSE event does not include SSO token');
  assert(!JSON.stringify(stateUpdated.data).includes('refresh_token'), 'SSE event does not include refresh token');
  assert(!JSON.stringify(stateUpdated.data).includes('raw_invite_token'), 'SSE event does not include raw invite token');
  assert(!JSON.stringify(stateUpdated.data).includes('secret'), 'SSE event does not include secret key');

  const staleUpdate = await requestJson({
    baseUrl,
    method: 'PUT',
    path: '/sync/rooms/room-http/states/board',
    userId: 'editor_1',
    body: {
      expected_revision: 1,
      body: { title: 'stale update', cards: [] }
    }
  });
  assert(staleUpdate.statusCode === 409, 'stale update over HTTP returns conflict');
  assert(staleUpdate.body.latest.revision === 2, 'stale update includes latest revision');

  const revisionResponse = await requestJson({
    baseUrl,
    method: 'GET',
    path: '/sync/rooms/room-http/states/board/revision',
    userId: 'viewer_1'
  });
  assert(revisionResponse.statusCode === 200, 'latest revision endpoint succeeds');
  assert(revisionResponse.body.revision === 2, 'latest revision endpoint returns revision 2');

  sse.close();

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample38-shared-state-sync-node-runtime',
    slice: 'http_sse_fallback_reference',
    dependency_free: true,
    loopback_only: true,
    accepted_revision: editorUpdate.body.state.revision,
    sse_state_updated_events: sseEvents.filter(event => event.eventName === 'state.updated').length
  }, null, 2));
} finally {
  await new Promise(resolve => server.close(resolve));
}
