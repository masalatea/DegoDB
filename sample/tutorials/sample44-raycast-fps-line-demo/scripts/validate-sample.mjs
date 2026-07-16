import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { once } from 'node:events';
import { FPS_RULES, INACTIVE_RESET_MS, RaycastFpsStore, castRay } from '../src/raycast-fps-store.mjs';
import { createServer } from '../src/server.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/game.js',
  'reference/raycast-fps-line-input.sample.json',
  'src/raycast-fps-store.mjs',
  'src/server.mjs',
  'scripts/validate-sample.mjs',
  'scripts/validate-mtool-artifact-linkage.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample44 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample44 must not include node_modules');

let currentNow = Date.UTC(2026, 6, 16, 0, 0, 0);
const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample44-fps-'));
const store = new RaycastFpsStore({
  filePath: path.join(tempRoot, 'raycast-fps-store.json'),
  now: () => currentNow
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

async function waitForFpsUpdatedEvent(baseUrl, roomSlug) {
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
      if (buffer.includes('event: fps.updated')) {
        const dataLine = buffer.split('\n').find(line => line.startsWith('data: ') && line.includes('revision'));
        return JSON.parse(dataLine.slice('data: '.length));
      }
    }
  } finally {
    await reader.cancel();
  }
  throw new Error(`Timed out waiting for fps.updated event. Buffer: ${buffer}`);
}

try {
  const baseUrl = await listen();

  const roomPage = await fetch(`${baseUrl}/r/FPS%20Room`);
  assert.equal(roomPage.status, 200, 'room page loads');
  assert.match(await roomPage.text(), /SAMPLE44_ROOM_SLUG/, 'room page injects room slug');

  const join1 = await jsonFetch(baseUrl, '/api/rooms/fps-room/join', {
    method: 'POST',
    body: { name: 'alpha' }
  });
  assert.equal(join1.response.status, 200, 'first player joins');
  assert.equal(join1.payload.player.name, 'alpha', 'player name is stored');

  const eventPromise = waitForFpsUpdatedEvent(baseUrl, 'fps-room');

  const join2 = await jsonFetch(baseUrl, '/api/rooms/fps-room/join', {
    method: 'POST',
    body: { name: 'bravo' }
  });
  assert.equal(join2.response.status, 200, 'second player joins');

  const join3 = await jsonFetch(baseUrl, '/api/rooms/fps-room/join', {
    method: 'POST',
    body: { name: 'charlie' }
  });
  assert.equal(join3.response.status, 200, 'third player can join');
  assert.equal(Object.keys(join3.payload.state.players).length, 3, 'room supports more than two players');

  const pushedEvent = await eventPromise;
  assert.equal(pushedEvent.room_slug, 'fps-room', 'SSE event is scoped to room');
  assert.equal(pushedEvent.revision >= join2.payload.state.revision, true, 'SSE event carries a current revision');

  const playerId = join1.payload.player.id;
  const beforeTurn = join3.payload.state.players[playerId].angle;
  const turned = await jsonFetch(baseUrl, '/api/rooms/fps-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'turn', delta: FPS_RULES.turn_degrees }
    }
  });
  assert.equal(turned.response.status, 200, 'turn command succeeds');
  assert.equal(turned.payload.state.players[playerId].angle, beforeTurn + FPS_RULES.turn_degrees, 'angle turns by fine-grained degrees, not only 90 degrees');

  const moved = await jsonFetch(baseUrl, '/api/rooms/fps-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'move', direction: 'forward' }
    }
  });
  assert.equal(moved.response.status, 200, 'move command succeeds');
  assert.notEqual(moved.payload.state.players[playerId].x, turned.payload.state.players[playerId].x, 'forward movement changes x at fine angle');

  const raw = store.getRawState();
  raw.rooms['fps-room'].game.players[playerId].x = 34;
  raw.rooms['fps-room'].game.players[playerId].y = 96;
  raw.rooms['fps-room'].game.players[playerId].angle = 180;
  store.write(raw);

  const blocked = await jsonFetch(baseUrl, '/api/rooms/fps-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'move', direction: 'forward' }
    }
  });
  assert.equal(blocked.response.status, 200, 'wall collision still returns a valid state');
  assert.equal(blocked.payload.state.players[playerId].x, 34, 'wall blocks movement');

  const rawForHit = store.getRawState();
  const targetId = join2.payload.player.id;
  rawForHit.rooms['fps-room'].game.players[playerId].x = 96;
  rawForHit.rooms['fps-room'].game.players[playerId].y = 96;
  rawForHit.rooms['fps-room'].game.players[playerId].angle = 0;
  rawForHit.rooms['fps-room'].game.players[targetId].x = 160;
  rawForHit.rooms['fps-room'].game.players[targetId].y = 96;
  store.write(rawForHit);

  const ray = castRay({ x: 96, y: 96, angle: 0 });
  assert.equal(ray.hit, 'wall', 'raycasting hits a wall');
  assert.equal(ray.distance > 0, true, 'raycasting reports wall distance');

  const shot = await jsonFetch(baseUrl, '/api/rooms/fps-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'shoot' }
    }
  });
  assert.equal(shot.response.status, 200, 'shoot command succeeds');
  assert.equal(shot.payload.state.players[targetId].hp, FPS_RULES.player_hp - FPS_RULES.shot_damage, 'forward-angle shot reduces opponent HP');

  const rawForWin = store.getRawState();
  rawForWin.rooms['fps-room'].game.players[playerId].alive = true;
  for (const [id, player] of Object.entries(rawForWin.rooms['fps-room'].game.players)) {
    if (id !== playerId) {
      player.hp = FPS_RULES.shot_damage;
      player.alive = true;
      player.x = rawForWin.rooms['fps-room'].game.players[playerId].x + 64;
      player.y = rawForWin.rooms['fps-room'].game.players[playerId].y;
    }
  }
  rawForWin.rooms['fps-room'].game.players[playerId].angle = 0;
  rawForWin.rooms['fps-room'].game.shots = [];
  store.write(rawForWin);

  for (const id of Object.keys(rawForWin.rooms['fps-room'].game.players).filter(id => id !== playerId)) {
    const rawLoop = store.getRawState();
    rawLoop.rooms['fps-room'].game.players[id].x = rawLoop.rooms['fps-room'].game.players[playerId].x + 64;
    rawLoop.rooms['fps-room'].game.players[id].y = rawLoop.rooms['fps-room'].game.players[playerId].y;
    rawLoop.rooms['fps-room'].game.players[id].hp = FPS_RULES.shot_damage;
    store.write(rawLoop);
    const result = await jsonFetch(baseUrl, '/api/rooms/fps-room/commands', {
      method: 'POST',
      body: {
        player_id: playerId,
        command: { type: 'shoot' }
      }
    });
    assert.equal(result.response.status, 200, `defeat ${id} succeeds`);
  }

  const finalState = await jsonFetch(baseUrl, '/api/rooms/fps-room/state');
  assert.equal(finalState.payload.phase, 'finished', 'game finishes when one player remains');
  assert.equal(finalState.payload.winner, playerId, 'last alive player wins');
  assert.equal(finalState.payload.defeats.length >= 2, true, 'defeated players are recorded');

  const registryBeforeReset = store.openRoom('fps-room').registry;
  currentNow += INACTIVE_RESET_MS + 1;
  const resetState = store.openRoom('fps-room');
  assert.equal(Object.keys(resetState.state.players).length, 0, '7 inactive days reset the room state');
  assert.equal(resetState.registry.room_slug, registryBeforeReset.room_slug, 'room registry slug remains stable');

  const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
  assert.match(js, /rayCount/, 'client renders with multiple rays');
  assert.match(js, /fieldOfView/, 'client uses field of view');
  assert.match(js, /turnDegrees = 5/, 'client turns in fine-grained increments');
  assert.match(js, /lineTo/, 'client renders lines');
  assert.doesNotMatch(js, /WebGLRenderingContext|three/i, 'client must not require WebGL or Three.js');
  assert.match(js, /AudioContext/, 'client can synthesize sample sound without audio assets');
  assert.match(js, /EventSource/, 'client subscribes to SSE events');
  assert.match(js, /fps\.updated/, 'client listens for FPS update events');
  assert.match(js, /keydown/, 'client handles keyboard input');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample44-raycast-fps-line-demo',
    room_slug: 'fps-room',
    rendering: 'line_only_raycasting',
    turn_degrees: FPS_RULES.turn_degrees,
    event: 'fps.updated',
    inactive_reset_days: INACTIVE_RESET_MS / (24 * 60 * 60 * 1000),
    game_packet: 'reference/raycast-fps-line-input.sample.json',
    dependency_free: true
  }, null, 2));
} finally {
  await close();
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
