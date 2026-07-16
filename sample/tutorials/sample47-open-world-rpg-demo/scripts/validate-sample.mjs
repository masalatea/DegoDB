import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { once } from 'node:events';
import { INACTIVE_RESET_MS, RPG_RULES, RpgRoomStore } from '../src/rpg-room-store.mjs';
import { createServer } from '../src/server.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/game.js',
  'reference/open-world-rpg-input.sample.json',
  'src/rpg-room-store.mjs',
  'src/server.mjs',
  'scripts/validate-sample.mjs',
  'scripts/validate-mtool-artifact-linkage.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample47 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample47 must not include node_modules');

let currentNow = Date.UTC(2026, 6, 16, 0, 0, 0);
const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample47-rpg-'));
const store = new RpgRoomStore({
  filePath: path.join(tempRoot, 'rpg-room-store.json'),
  now: () => currentNow
});
const server = createServer({ store, tickMs: 25 });

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

async function waitForRpgUpdatedEvent(baseUrl, roomSlug) {
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
      if (buffer.includes('event: rpg.updated')) {
        const dataLine = buffer.split('\n').find(line => line.startsWith('data: ') && line.includes('revision'));
        return JSON.parse(dataLine.slice('data: '.length));
      }
    }
  } finally {
    await reader.cancel();
  }
  throw new Error(`Timed out waiting for rpg.updated event. Buffer: ${buffer}`);
}

try {
  const baseUrl = await listen();

  const roomPage = await fetch(`${baseUrl}/r/RPG%20Room`);
  assert.equal(roomPage.status, 200, 'room page loads');
  const roomHtml = await roomPage.text();
  assert.match(roomHtml, /SAMPLE47_ROOM_SLUG/, 'room page injects room slug');
  assert.match(roomHtml, /No player-vs-player combat/, 'room page explains no PvP boundary');

  const join1 = await jsonFetch(baseUrl, '/api/rooms/rpg-room/join', {
    method: 'POST',
    body: { name: 'alpha' }
  });
  assert.equal(join1.response.status, 200, 'first hero joins');
  assert.equal(join1.payload.player.name, 'alpha', 'player name is stored');
  assert.equal(Object.keys(join1.payload.state.enemies).length, RPG_RULES.max_enemies, 'enemies spawn when a player joins');
  assert.equal(join1.payload.state.obstacles.length, RPG_RULES.obstacles.length, 'blocked terrain is included in state');

  const playerId = join1.payload.player.id;
  const firstEnemy = Object.values(join1.payload.state.enemies)[0];
  const distanceToEnemy = Math.hypot(firstEnemy.x - join1.payload.player.x, firstEnemy.y - join1.payload.player.y);
  assert.equal(distanceToEnemy >= RPG_RULES.enemy_spawn_min_distance - 2, true, 'enemy spawns away from player');

  const eventPromise = waitForRpgUpdatedEvent(baseUrl, 'rpg-room');
  const join2 = await jsonFetch(baseUrl, '/api/rooms/rpg-room/join', {
    method: 'POST',
    body: { name: 'bravo' }
  });
  assert.equal(join2.response.status, 200, 'second hero can join same room');
  assert.equal(Object.keys(join2.payload.state.players).length, 2, 'room supports multiple players');
  const pushedEvent = await eventPromise;
  assert.equal(pushedEvent.room_slug, 'rpg-room', 'SSE event is scoped to room');

  const moved = await jsonFetch(baseUrl, '/api/rooms/rpg-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'move', dx: 1, dy: 0 }
    }
  });
  assert.equal(moved.response.status, 200, 'move command succeeds');
  assert.equal(moved.payload.state.players[playerId].x > join1.payload.player.x, true, 'move command changes player x');

  const rawForObstacle = store.getRawState();
  rawForObstacle.rooms['rpg-room'].game.players[playerId].x = 210;
  rawForObstacle.rooms['rpg-room'].game.players[playerId].y = 230;
  store.write(rawForObstacle);
  const blocked = await jsonFetch(baseUrl, '/api/rooms/rpg-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'move', dx: 1, dy: 0 }
    }
  });
  assert.equal(blocked.response.status, 200, 'blocked move still returns state');
  assert.equal(blocked.payload.state.players[playerId].x, 210, 'tree obstacle blocks player movement');

  const rawForAttack = store.getRawState();
  const targetEnemyId = Object.keys(rawForAttack.rooms['rpg-room'].game.enemies)[0];
  rawForAttack.rooms['rpg-room'].game.players[playerId].x = 500;
  rawForAttack.rooms['rpg-room'].game.players[playerId].y = 500;
  rawForAttack.rooms['rpg-room'].game.players[playerId].facing = 'right';
  rawForAttack.rooms['rpg-room'].game.players[playerId].hp = RPG_RULES.player_hp;
  rawForAttack.rooms['rpg-room'].game.enemies[targetEnemyId].x = 554;
  rawForAttack.rooms['rpg-room'].game.enemies[targetEnemyId].y = 500;
  rawForAttack.rooms['rpg-room'].game.enemies[targetEnemyId].hp = RPG_RULES.enemy_hp;
  store.write(rawForAttack);

  const attack1 = await jsonFetch(baseUrl, '/api/rooms/rpg-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'attack' }
    }
  });
  assert.equal(attack1.response.status, 200, 'attack command succeeds');
  assert.equal(attack1.payload.state.enemies[targetEnemyId].hp, RPG_RULES.enemy_hp - RPG_RULES.sword_damage, 'sword attack damages enemy');
  assert.equal(attack1.payload.state.players[playerId].gold, 0, 'non-defeating hit gives no gold yet');

  const rawForDefeat = store.getRawState();
  rawForDefeat.rooms['rpg-room'].game.players[playerId].x = 500;
  rawForDefeat.rooms['rpg-room'].game.players[playerId].y = 500;
  rawForDefeat.rooms['rpg-room'].game.players[playerId].facing = 'right';
  rawForDefeat.rooms['rpg-room'].game.enemies[targetEnemyId].x = 554;
  rawForDefeat.rooms['rpg-room'].game.enemies[targetEnemyId].y = 500;
  rawForDefeat.rooms['rpg-room'].game.enemies[targetEnemyId].hp = RPG_RULES.sword_damage;
  store.write(rawForDefeat);
  const attack2 = await jsonFetch(baseUrl, '/api/rooms/rpg-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'attack' }
    }
  });
  assert.equal(attack2.response.status, 200, 'defeating attack succeeds');
  assert.equal(attack2.payload.state.players[playerId].exp >= RPG_RULES.reward_exp, true, 'defeat rewards EXP');
  assert.equal(attack2.payload.state.players[playerId].gold >= RPG_RULES.reward_gold, true, 'defeat rewards Gold');

  const rawForEnemyTick = store.getRawState();
  const tickEnemyId = Object.keys(rawForEnemyTick.rooms['rpg-room'].game.enemies)[0];
  rawForEnemyTick.rooms['rpg-room'].game.players[playerId].x = 700;
  rawForEnemyTick.rooms['rpg-room'].game.players[playerId].y = 700;
  rawForEnemyTick.rooms['rpg-room'].game.players[playerId].hp = 50;
  rawForEnemyTick.rooms['rpg-room'].game.players[playerId].last_move_at = currentNow - RPG_RULES.idle_regen_ms - 100;
  rawForEnemyTick.rooms['rpg-room'].game.enemies[tickEnemyId].x = 736;
  rawForEnemyTick.rooms['rpg-room'].game.enemies[tickEnemyId].y = 700;
  store.write(rawForEnemyTick);
  currentNow += 1000;
  await new Promise(resolve => setTimeout(resolve, 60));
  const tickedState = await jsonFetch(baseUrl, '/api/rooms/rpg-room/state');
  assert.equal(tickedState.payload.players[playerId].hp >= 47, true, 'enemy weak attack does not instantly destroy the player');
  assert.equal(tickedState.payload.players[playerId].hp > 46, true, 'idle regen can recover some HP after tick');
  assert.notEqual(tickedState.payload.enemies[tickEnemyId].x, 736, 'enemy moves slowly on server tick');

  const rawForPvp = store.getRawState();
  const secondPlayerId = join2.payload.player.id;
  rawForPvp.rooms['rpg-room'].game.players[playerId].x = 500;
  rawForPvp.rooms['rpg-room'].game.players[playerId].y = 500;
  rawForPvp.rooms['rpg-room'].game.players[playerId].facing = 'right';
  rawForPvp.rooms['rpg-room'].game.players[secondPlayerId].x = 554;
  rawForPvp.rooms['rpg-room'].game.players[secondPlayerId].y = 500;
  rawForPvp.rooms['rpg-room'].game.players[secondPlayerId].hp = 100;
  store.write(rawForPvp);
  const pvpAttempt = await jsonFetch(baseUrl, '/api/rooms/rpg-room/commands', {
    method: 'POST',
    body: {
      player_id: playerId,
      command: { type: 'attack' }
    }
  });
  assert.equal(pvpAttempt.response.status, 200, 'attack near another player succeeds as command');
  assert.equal(pvpAttempt.payload.state.players[secondPlayerId].hp, 100, 'player-vs-player damage is forbidden');

  const registryBeforeReset = store.openRoom('rpg-room').registry;
  currentNow += INACTIVE_RESET_MS + 1;
  const resetState = store.openRoom('rpg-room');
  assert.equal(Object.keys(resetState.state.players).length, 0, '7 inactive days reset the room state');
  assert.equal(resetState.registry.room_slug, registryBeforeReset.room_slug, 'room registry slug remains stable');

  const js = fs.readFileSync(path.join(sampleRoot, 'public/game.js'), 'utf8');
  assert.match(js, /EventSource/, 'client subscribes to SSE events');
  assert.match(js, /rpg\.updated/, 'client listens for RPG update events');
  assert.match(js, /keydown/, 'client handles keyboard input');
  assert.match(js, /drawObstacles/, 'client draws blocked terrain');
  assert.match(js, /pvp_enabled: false/, 'client status shows PvP disabled');
  assert.match(js, /AudioContext/, 'client can synthesize sample sound without audio assets');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample47-open-world-rpg-demo',
    room_slug: 'rpg-room',
    pvp_enabled: false,
    max_enemies: RPG_RULES.max_enemies,
    obstacles: RPG_RULES.obstacles.length,
    event: 'rpg.updated',
    inactive_reset_days: INACTIVE_RESET_MS / (24 * 60 * 60 * 1000),
    game_packet: 'reference/open-world-rpg-input.sample.json',
    dependency_free: true
  }, null, 2));
} finally {
  await close();
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
