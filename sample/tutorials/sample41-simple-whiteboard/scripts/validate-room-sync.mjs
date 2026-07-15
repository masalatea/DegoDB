import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { once } from 'node:events';
import { WhiteboardRoomStore, BOARD_INACTIVE_CLEAR_MS } from '../src/whiteboard-room-store.mjs';
import { createServer } from '../src/server.mjs';

let now = Date.UTC(2026, 6, 16, 0, 0, 0);
const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample41-room-sync-'));
const store = new WhiteboardRoomStore({
  filePath: path.join(tempRoot, 'whiteboard-room-store.json'),
  now: () => now
});
const server = createServer({ store });

function listen() {
  return new Promise((resolve, reject) => {
    server.once('error', reject);
    server.listen(0, '127.0.0.1', () => {
      server.off('error', reject);
      const address = server.address();
      resolve(`http://${address.address}:${address.port}`);
    });
  });
}

async function close() {
  server.close();
  await once(server, 'close');
}

async function jsonFetch(baseUrl, pathName, { method = 'GET', body = undefined } = {}) {
  const response = await fetch(`${baseUrl}${pathName}`, {
    method,
    headers: body === undefined ? {} : { 'content-type': 'application/json' },
    body: body === undefined ? undefined : JSON.stringify(body)
  });
  const text = await response.text();
  const payload = text === '' ? null : JSON.parse(text);
  return { response, payload };
}

async function waitForBoardUpdatedEvent(baseUrl, roomSlug) {
  const response = await fetch(`${baseUrl}/api/rooms/${encodeURIComponent(roomSlug)}/events`);
  assert.equal(response.status, 200, 'SSE event stream opens');
  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';
  try {
    const deadline = Date.now() + 3000;
    while (Date.now() < deadline) {
      const { value, done } = await reader.read();
      if (done) {
        break;
      }
      buffer += decoder.decode(value, { stream: true });
      if (buffer.includes('event: board.updated')) {
        const dataLine = buffer.split('\n').find(line => line.startsWith('data: ') && line.includes('revision'));
        return JSON.parse(dataLine.slice('data: '.length));
      }
    }
  } finally {
    await reader.cancel();
  }
  throw new Error(`Timed out waiting for board.updated event. Buffer: ${buffer}`);
}

try {
  const baseUrl = await listen();

  const roomPage = await fetch(`${baseUrl}/r/Team%20Board!!`);
  assert.equal(roomPage.status, 200, 'room page loads');
  const roomHtml = await roomPage.text();
  assert.match(roomHtml, /team-board/, 'room page includes normalized room slug');
  assert.match(roomHtml, /SAMPLE41_ROOM_SLUG/, 'room page injects room slug for client sync');

  const opened = await jsonFetch(baseUrl, '/api/rooms/Team%20Board!!', { method: 'POST' });
  assert.equal(opened.response.status, 200, 'room open API succeeds');
  assert.equal(opened.payload.room.room_slug, 'team-board', 'room slug is normalized');
  assert.equal(opened.payload.registry.recreate_count, 1, 'first open records recreation');
  assert.equal(opened.payload.board.revision, 0, 'new board starts at revision 0');

  const eventPromise = waitForBoardUpdatedEvent(baseUrl, 'team-board');

  const strokeAppend = await jsonFetch(baseUrl, '/api/rooms/team-board/operations', {
    method: 'POST',
    body: {
      expected_revision: 0,
      operation: {
        type: 'stroke',
        tool: 'pen',
        color: '#2563eb',
        size: 6,
        points: [{ x: 1, y: 2 }, { x: 3, y: 4 }]
      }
    }
  });
  assert.equal(strokeAppend.response.status, 200, 'stroke operation append succeeds');
  assert.equal(strokeAppend.payload.board.revision, 1, 'stroke append increments revision');
  assert.equal(strokeAppend.payload.operation.expires_at, undefined, 'whiteboard operation has no per-operation TTL');
  const pushedEvent = await eventPromise;
  assert.equal(pushedEvent.revision, 1, 'SSE board.updated event carries latest revision');

  const conflict = await jsonFetch(baseUrl, '/api/rooms/team-board/operations', {
    method: 'POST',
    body: {
      expected_revision: 0,
      operation: {
        type: 'text',
        text: 'late',
        x: 10,
        y: 20,
        color: '#111827',
        size: 8
      }
    }
  });
  assert.equal(conflict.response.status, 409, 'stale revision is rejected');
  assert.equal(conflict.payload.error, 'revision_conflict', 'stale revision returns stable error');

  const textAppend = await jsonFetch(baseUrl, '/api/rooms/team-board/operations', {
    method: 'POST',
    body: {
      expected_revision: 1,
      operation: {
        type: 'text',
        text: 'hello',
        x: 40,
        y: 50,
        color: '#111827',
        size: 8
      }
    }
  });
  assert.equal(textAppend.response.status, 200, 'text operation append succeeds');
  assert.equal(textAppend.payload.board.revision, 2, 'text append increments revision');

  const board = await jsonFetch(baseUrl, '/api/rooms/team-board/board');
  assert.equal(board.response.status, 200, 'board fetch succeeds');
  assert.equal(board.payload.operations.length, 2, 'board fetch returns both operations');
  assert.equal(board.payload.operations.some(operation => 'expires_at' in operation), false, 'no operation contains per-item expiry');

  now += BOARD_INACTIVE_CLEAR_MS - 1;
  const beforeCleanup = await jsonFetch(baseUrl, '/api/cleanup', { method: 'POST' });
  assert.equal(beforeCleanup.response.status, 200, 'cleanup before inactivity threshold succeeds');
  assert.equal(beforeCleanup.payload.boards['team-board'].operations.length, 2, 'board is kept before 7 inactive days');

  now += 1;
  const afterCleanup = await jsonFetch(baseUrl, '/api/cleanup', { method: 'POST' });
  assert.equal(afterCleanup.response.status, 200, 'cleanup after inactivity threshold succeeds');
  assert.equal(afterCleanup.payload.boards['team-board'].operations.length, 0, 'entire board is cleared after 7 inactive days');
  assert.equal(afterCleanup.payload.boards['team-board'].revision, 0, 'cleared board restarts at revision 0');
  assert.equal(afterCleanup.payload.room_registry['team-board'].clear_count, 1, 'room registry records board clear');
  assert.ok(afterCleanup.payload.room_registry['team-board'], 'room registry remains after board clear');

  const reopened = await jsonFetch(baseUrl, '/api/rooms/team-board', { method: 'POST' });
  assert.equal(reopened.response.status, 200, 'same URL reopens after clear');
  assert.equal(reopened.payload.board.operations.length, 0, 'same URL starts as empty board after clear');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample41-simple-whiteboard',
    validator: 'room-sync',
    room_slug: 'team-board',
    board_revision_after_append: textAppend.payload.board.revision,
    inactive_clear_days: BOARD_INACTIVE_CLEAR_MS / (24 * 60 * 60 * 1000),
    operation_ttl: false,
    client_room_hook: true,
    sse_board_updated: true,
    room_registry_preserved: true
  }, null, 2));
} finally {
  await close();
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
