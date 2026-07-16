import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { once } from 'node:events';
import { GAME_RULES, ShooterRoomStore, SHOT_DAMAGE } from '../src/shooter-room-store.mjs';
import { createServer } from '../src/server.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/game.js',
  'reference/room-shooter-game-input.sample.json',
  'src/shooter-room-store.mjs',
  'src/server.mjs',
  'scripts/validate-sample.mjs',
  'scripts/validate-mtool-artifact-linkage.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample42 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample42 must not include node_modules');

const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample42-shooter-'));
const store = new ShooterRoomStore({
  filePath: path.join(tempRoot, 'shooter-room-store.json'),
  now: () => Date.UTC(2026, 6, 16, 0, 0, 0)
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

async function waitForGameUpdatedEvent(baseUrl, roomSlug) {
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
      if (buffer.includes('event: game.updated')) {
        const dataLine = buffer.split('\n').find(line => line.startsWith('data: ') && line.includes('revision'));
        return JSON.parse(dataLine.slice('data: '.length));
      }
    }
  } finally {
    await reader.cancel();
  }
  throw new Error(`Timed out waiting for game.updated event. Buffer: ${buffer}`);
}

try {
  const baseUrl = await listen();

  const roomPage = await fetch(`${baseUrl}/r/Arena%201`);
  assert.equal(roomPage.status, 200, 'room page loads');
  assert.match(await roomPage.text(), /SAMPLE42_ROOM_SLUG/, 'room page injects room slug');

  const join1 = await jsonFetch(baseUrl, '/api/rooms/arena-1/join', { method: 'POST' });
  assert.equal(join1.response.status, 200, 'first player joins');
  assert.equal(join1.payload.player.id, 'p1', 'first player id is p1');

  const eventPromise = waitForGameUpdatedEvent(baseUrl, 'arena-1');

  const join2 = await jsonFetch(baseUrl, '/api/rooms/arena-1/join', { method: 'POST' });
  assert.equal(join2.response.status, 200, 'second player joins');
  assert.equal(join2.payload.player.id, 'p2', 'second player id is p2');

  const pushedEvent = await eventPromise;
  assert.equal(pushedEvent.room_slug, 'arena-1', 'SSE event is scoped to room');
  assert.equal(pushedEvent.revision, join2.payload.state.revision, 'SSE event carries latest revision');

  const join3 = await jsonFetch(baseUrl, '/api/rooms/arena-1/join', { method: 'POST' });
  assert.equal(join3.response.status, 409, 'third player cannot join first-slice duel');
  assert.equal(join3.payload.error, 'room_full', 'room full error is stable');

  const moved = await jsonFetch(baseUrl, '/api/rooms/arena-1/commands', {
    method: 'POST',
    body: {
      player_id: 'p1',
      command: { type: 'move', direction: 'right' }
    }
  });
  assert.equal(moved.response.status, 200, 'move command succeeds');
  assert.equal(moved.payload.state.players.p1.x > join1.payload.player.x, true, 'move command changes player position');

  const raw = store.getRawState();
  raw.rooms['arena-1'].game.players.p1.x = raw.rooms['arena-1'].game.players.p2.x - 48;
  raw.rooms['arena-1'].game.players.p1.y = raw.rooms['arena-1'].game.players.p2.y;
  store.write(raw);

  const shot = await jsonFetch(baseUrl, '/api/rooms/arena-1/commands', {
    method: 'POST',
    body: {
      player_id: 'p1',
      command: { type: 'shoot', direction: 'right' }
    }
  });
  assert.equal(shot.response.status, 200, 'shoot command succeeds');
  assert.equal(shot.payload.state.players.p2.hp, GAME_RULES.player_hp - SHOT_DAMAGE, 'shot hit reduces opponent HP');

  const state = await jsonFetch(baseUrl, '/api/rooms/arena-1/state');
  assert.equal(state.response.status, 200, 'state fetch succeeds');
  assert.equal(Object.keys(state.payload.players).length, 2, 'state includes both players');

  const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
  assert.match(js, /EventSource/, 'client subscribes to SSE events');
  assert.match(js, /game\.updated/, 'client listens for game update events');
  assert.match(js, /keydown/, 'client handles keyboard input');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample42-room-shooter-game',
    room_slug: 'arena-1',
    players: 2,
    sse_game_updated: true,
    shot_damage: SHOT_DAMAGE,
    game_packet: 'reference/room-shooter-game-input.sample.json',
    dependency_free: true
  }, null, 2));
} finally {
  await close();
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
