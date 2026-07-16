import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { once } from 'node:events';
import { INACTIVE_RESET_MS, TANK_RULES, TankRoomStore } from '../src/tank-room-store.mjs';
import { createServer } from '../src/server.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/game.js',
  'reference/tank-survival-game-input.sample.json',
  'src/tank-room-store.mjs',
  'src/server.mjs',
  'scripts/validate-sample.mjs',
  'scripts/validate-mtool-artifact-linkage.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample43 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample43 must not include node_modules');

let currentNow = Date.UTC(2026, 6, 16, 0, 0, 0);
const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample43-tank-'));
const store = new TankRoomStore({
  filePath: path.join(tempRoot, 'tank-room-store.json'),
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

async function waitForTankUpdatedEvent(baseUrl, roomSlug) {
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
      if (buffer.includes('event: tank.updated')) {
        const dataLine = buffer.split('\n').find(line => line.startsWith('data: ') && line.includes('revision'));
        return JSON.parse(dataLine.slice('data: '.length));
      }
    }
  } finally {
    await reader.cancel();
  }
  throw new Error(`Timed out waiting for tank.updated event. Buffer: ${buffer}`);
}

try {
  const baseUrl = await listen();

  const roomPage = await fetch(`${baseUrl}/r/Tank%20Arena`);
  assert.equal(roomPage.status, 200, 'room page loads');
  assert.match(await roomPage.text(), /SAMPLE43_ROOM_SLUG/, 'room page injects room slug');

  const join1 = await jsonFetch(baseUrl, '/api/rooms/tank-arena/join', {
    method: 'POST',
    body: { name: 'alpha' }
  });
  assert.equal(join1.response.status, 200, 'first tank joins');
  assert.equal(join1.payload.player.name, 'alpha', 'player name is stored');

  const eventPromise = waitForTankUpdatedEvent(baseUrl, 'tank-arena');

  const join2 = await jsonFetch(baseUrl, '/api/rooms/tank-arena/join', {
    method: 'POST',
    body: { name: 'bravo' }
  });
  assert.equal(join2.response.status, 200, 'second tank joins');

  const join3 = await jsonFetch(baseUrl, '/api/rooms/tank-arena/join', {
    method: 'POST',
    body: { name: 'charlie' }
  });
  assert.equal(join3.response.status, 200, 'third tank can join mid-game');
  assert.equal(Object.keys(join3.payload.state.players).length, 3, 'room supports more than two tanks');

  const pushedEvent = await eventPromise;
  assert.equal(pushedEvent.room_slug, 'tank-arena', 'SSE event is scoped to room');
  assert.equal(pushedEvent.revision >= join2.payload.state.revision, true, 'SSE event carries a current revision');

  const playerId = join1.payload.player.id;
  const beforeTurn = join3.payload.state.players[playerId].angle;
  const turned = await jsonFetch(baseUrl, '/api/rooms/tank-arena/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'turn', angle: beforeTurn + 45 }
    }
  });
  assert.equal(turned.response.status, 200, 'turn command succeeds');
  assert.equal(turned.payload.state.players[playerId].angle, beforeTurn + 45, 'tank can turn to arbitrary angle');

  const moved = await jsonFetch(baseUrl, '/api/rooms/tank-arena/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'drive', angle: 45, distance: TANK_RULES.tank_speed }
    }
  });
  assert.equal(moved.response.status, 200, 'drive command succeeds');
  assert.notEqual(moved.payload.state.players[playerId].x, turned.payload.state.players[playerId].x, 'omnidirectional drive changes x');
  assert.notEqual(moved.payload.state.players[playerId].y, turned.payload.state.players[playerId].y, 'omnidirectional drive changes y');

  const raw = store.getRawState();
  raw.rooms['tank-arena'].game.players[playerId].x = 410;
  raw.rooms['tank-arena'].game.players[playerId].y = 300;
  raw.rooms['tank-arena'].game.players[playerId].angle = 0;
  store.write(raw);

  const blocked = await jsonFetch(baseUrl, '/api/rooms/tank-arena/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'drive', angle: 0, distance: TANK_RULES.tank_speed }
    }
  });
  assert.equal(blocked.response.status, 200, 'obstacle collision still returns a valid state');
  assert.equal(blocked.payload.state.players[playerId].x, 410, 'obstacle blocks tank movement');

  const rawForHit = store.getRawState();
  const targetId = join2.payload.player.id;
  rawForHit.rooms['tank-arena'].game.players[playerId].x = 280;
  rawForHit.rooms['tank-arena'].game.players[playerId].y = 280;
  rawForHit.rooms['tank-arena'].game.players[playerId].angle = 0;
  rawForHit.rooms['tank-arena'].game.players[targetId].x = 354;
  rawForHit.rooms['tank-arena'].game.players[targetId].y = 280;
  store.write(rawForHit);

  const shot = await jsonFetch(baseUrl, '/api/rooms/tank-arena/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'fire' }
    }
  });
  assert.equal(shot.response.status, 200, 'fire command succeeds');
  assert.equal(shot.payload.state.players[targetId].hp, TANK_RULES.player_hp - TANK_RULES.bullet_damage, 'forward bullet hit reduces opponent HP');

  const rawForWin = store.getRawState();
  rawForWin.rooms['tank-arena'].game.players[playerId].alive = true;
  for (const [id, tank] of Object.entries(rawForWin.rooms['tank-arena'].game.players)) {
    if (id !== playerId) {
      tank.hp = TANK_RULES.bullet_damage;
      tank.alive = true;
      tank.x = rawForWin.rooms['tank-arena'].game.players[playerId].x + 74;
      tank.y = rawForWin.rooms['tank-arena'].game.players[playerId].y;
    }
  }
  rawForWin.rooms['tank-arena'].game.players[playerId].angle = 0;
  rawForWin.rooms['tank-arena'].game.bullets = [];
  store.write(rawForWin);

  for (const id of Object.keys(rawForWin.rooms['tank-arena'].game.players).filter(id => id !== playerId)) {
    const rawLoop = store.getRawState();
    rawLoop.rooms['tank-arena'].game.players[id].x = rawLoop.rooms['tank-arena'].game.players[playerId].x + 74;
    rawLoop.rooms['tank-arena'].game.players[id].y = rawLoop.rooms['tank-arena'].game.players[playerId].y;
    rawLoop.rooms['tank-arena'].game.players[id].hp = TANK_RULES.bullet_damage;
    store.write(rawLoop);
    const result = await jsonFetch(baseUrl, '/api/rooms/tank-arena/commands', {
      method: 'POST',
      body: {
        player_id: playerId,
        command: { type: 'fire' }
      }
    });
    assert.equal(result.response.status, 200, `destroy ${id} succeeds`);
  }

  const finalState = await jsonFetch(baseUrl, '/api/rooms/tank-arena/state');
  assert.equal(finalState.payload.phase, 'finished', 'game finishes when one tank remains');
  assert.equal(finalState.payload.winner, playerId, 'last alive tank wins');
  assert.equal(finalState.payload.explosions.length >= 2, true, 'destroyed tanks create explosions');

  const joinLate = await jsonFetch(baseUrl, '/api/rooms/tank-arena/join', {
    method: 'POST',
    body: { name: 'late' }
  });
  assert.equal(joinLate.response.status, 200, 'late join is allowed even after finish');
  assert.equal(joinLate.payload.state.phase, 'open', 'late join reopens active play');

  const registryBeforeReset = store.openRoom('tank-arena').registry;
  currentNow += INACTIVE_RESET_MS + 1;
  const resetState = store.openRoom('tank-arena');
  assert.equal(Object.keys(resetState.state.players).length, 0, '7 inactive days reset the room state');
  assert.equal(resetState.registry.room_slug, registryBeforeReset.room_slug, 'room registry slug remains stable');
  assert.equal(resetState.registry.recreate_count, registryBeforeReset.recreate_count + 1, 'room recreate count increments after inactivity reset');

  const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
  assert.match(js, /EventSource/, 'client subscribes to SSE events');
  assert.match(js, /tank\.updated/, 'client listens for tank update events');
  assert.match(js, /keydown/, 'client handles keyboard input');
  assert.match(js, /fire/, 'client can send fire command');
  assert.match(js, /AudioContext/, 'client can synthesize sample sound without audio assets');
  assert.match(js, /playFireSound/, 'client has fire sound hook');
  assert.match(js, /playExplosionSound/, 'client has explosion sound hook');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample43-tank-survival-game',
    room_slug: 'tank-arena',
    unlimited_join: true,
    obstacles: TANK_RULES.obstacles.length,
    event: 'tank.updated',
    inactive_reset_days: INACTIVE_RESET_MS / (24 * 60 * 60 * 1000),
    game_packet: 'reference/tank-survival-game-input.sample.json',
    dependency_free: true
  }, null, 2));
} finally {
  await close();
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
