import fs from 'node:fs';
import path from 'node:path';

const DAY_MS = 24 * 60 * 60 * 1000;
const CELL_SIZE = 64;
const TURN_DEGREES = 5;
const MOVE_STEP = 24;
const SHOT_DAMAGE = 25;
const INACTIVE_RESET_MS = 7 * DAY_MS;
const GRID_MAP = Object.freeze([
  '111111111111',
  '100000000001',
  '101111011101',
  '100001000001',
  '111101110101',
  '100100000101',
  '101101111101',
  '100000100001',
  '101110101101',
  '100000000001',
  '111111111111'
]);
const ARENA = Object.freeze({
  width: GRID_MAP[0].length * CELL_SIZE,
  height: GRID_MAP.length * CELL_SIZE
});
const FPS_RULES = Object.freeze({
  max_players: null,
  player_hp: 100,
  cell_size: CELL_SIZE,
  turn_degrees: TURN_DEGREES,
  move_step: MOVE_STEP,
  shot_damage: SHOT_DAMAGE,
  inactive_reset_ms: INACTIVE_RESET_MS,
  map: GRID_MAP,
  arena: ARENA,
  commands: ['move', 'turn', 'shoot'],
  events: ['fps.updated']
});

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function normalizeRoomSlug(slug) {
  const normalized = String(slug ?? '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9-]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return normalized === '' ? 'general' : normalized.slice(0, 80);
}

function defaultState() {
  return {
    room_registry: {},
    rooms: {}
  };
}

function initialGame(roomSlug) {
  return {
    room_slug: roomSlug,
    revision: 0,
    phase: 'open',
    winner: null,
    map: [...GRID_MAP],
    players: {},
    shots: [],
    defeats: []
  };
}

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
}

function normalizeAngle(angle) {
  const normalized = Number(angle);
  if (!Number.isFinite(normalized)) {
    return 0;
  }
  return ((normalized % 360) + 360) % 360;
}

function angleToVector(angle) {
  const radian = normalizeAngle(angle) * Math.PI / 180;
  return {
    x: Math.cos(radian),
    y: Math.sin(radian)
  };
}

function isWallAt(x, y) {
  if (x < 0 || y < 0 || x >= ARENA.width || y >= ARENA.height) {
    return true;
  }
  const gridX = Math.floor(x / CELL_SIZE);
  const gridY = Math.floor(y / CELL_SIZE);
  return GRID_MAP[gridY]?.[gridX] === '1';
}

function spawnPoint(playerNumber) {
  const spawns = [
    { x: 1.5 * CELL_SIZE, y: 1.5 * CELL_SIZE, angle: 0 },
    { x: 10.5 * CELL_SIZE, y: 9.5 * CELL_SIZE, angle: 180 },
    { x: 10.5 * CELL_SIZE, y: 1.5 * CELL_SIZE, angle: 135 },
    { x: 1.5 * CELL_SIZE, y: 9.5 * CELL_SIZE, angle: 315 }
  ];
  const base = spawns[(playerNumber - 1) % spawns.length];
  return { ...base };
}

function castRay({ x, y, angle, maxDistance = ARENA.width }) {
  const vector = angleToVector(angle);
  const step = 4;
  for (let distance = 0; distance <= maxDistance; distance += step) {
    const hitX = x + vector.x * distance;
    const hitY = y + vector.y * distance;
    if (isWallAt(hitX, hitY)) {
      return {
        hit: 'wall',
        distance,
        x: hitX,
        y: hitY
      };
    }
  }
  return {
    hit: 'none',
    distance: maxDistance,
    x: x + vector.x * maxDistance,
    y: y + vector.y * maxDistance
  };
}

function targetInSight(game, shooter) {
  const facing = angleToVector(shooter.angle);
  const wallHit = castRay({ x: shooter.x, y: shooter.y, angle: shooter.angle });
  let bestTarget = null;
  for (const target of Object.values(game.players)) {
    if (target.id === shooter.id || !target.alive) {
      continue;
    }
    const dx = target.x - shooter.x;
    const dy = target.y - shooter.y;
    const distance = Math.hypot(dx, dy);
    if (distance > wallHit.distance) {
      continue;
    }
    const alignment = (dx * facing.x + dy * facing.y) / Math.max(distance, 1);
    const cross = Math.abs(dx * facing.y - dy * facing.x);
    if (alignment > 0.97 && cross < 18) {
      if (!bestTarget || distance < bestTarget.distance) {
        bestTarget = { target, distance };
      }
    }
  }
  return bestTarget?.target ?? null;
}

export class RaycastFpsStore {
  constructor({ filePath, now = () => Date.now() }) {
    this.filePath = filePath;
    this.now = now;
    fs.mkdirSync(path.dirname(filePath), { recursive: true });
    if (!fs.existsSync(filePath)) {
      this.write(defaultState());
    }
  }

  openRoom(roomSlugInput) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const state = this.read();
    const registry = state.room_registry[roomSlug] ?? {
      room_slug: roomSlug,
      first_created_at: now,
      recreate_count: 0
    };
    let room = state.rooms[roomSlug];
    if (!room || now - room.last_activity_at >= INACTIVE_RESET_MS) {
      registry.recreate_count += 1;
      registry.last_recreated_at = now;
      room = {
        room_slug: roomSlug,
        created_at: now,
        last_activity_at: now,
        game: initialGame(roomSlug)
      };
    }
    state.room_registry[roomSlug] = registry;
    state.rooms[roomSlug] = room;
    this.write(state);
    return {
      room: clone(room),
      registry: clone(registry),
      state: clone(room.game)
    };
  }

  joinRoom(roomSlugInput, nameInput = '') {
    const now = this.now();
    const opened = this.openRoom(roomSlugInput);
    const roomSlug = opened.room.room_slug;
    const state = this.read();
    const room = state.rooms[roomSlug];
    const nextNumber = Object.keys(room.game.players).length + 1;
    const playerId = `f${nextNumber}-${String(now).slice(-5)}`;
    const spawn = spawnPoint(nextNumber);
    room.game.players[playerId] = {
      id: playerId,
      name: String(nameInput || playerId).slice(0, 24),
      hp: FPS_RULES.player_hp,
      alive: true,
      x: spawn.x,
      y: spawn.y,
      angle: spawn.angle,
      joined_at: now,
      last_active_at: now
    };
    room.game.phase = 'open';
    room.game.winner = null;
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return {
      ok: true,
      player: clone(room.game.players[playerId]),
      state: clone(room.game)
    };
  }

  applyCommand({ roomSlug: roomSlugInput, playerId, command }) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const state = this.read();
    const room = state.rooms[roomSlug];
    if (!room) {
      return { ok: false, error: 'room_not_found' };
    }
    const player = room.game.players[playerId];
    if (!player) {
      return { ok: false, error: 'player_not_found', state: clone(room.game) };
    }
    if (!player.alive) {
      return { ok: false, error: 'player_defeated', state: clone(room.game) };
    }

    if (command?.type === 'move') {
      this.move(player, command);
    } else if (command?.type === 'turn') {
      this.turn(player, command.delta ?? 0);
    } else if (command?.type === 'shoot') {
      this.shoot(room.game, player, now);
    } else {
      return { ok: false, error: 'unsupported_command', state: clone(room.game) };
    }

    player.last_active_at = now;
    this.updateWinner(room.game);
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return { ok: true, state: clone(room.game) };
  }

  move(player, command) {
    const direction = command.direction === 'backward' ? -1 : 1;
    const distance = clamp(Number(command.distance ?? MOVE_STEP), 0, MOVE_STEP) * direction;
    const vector = angleToVector(player.angle);
    const nextX = clamp(player.x + vector.x * distance, CELL_SIZE / 2, ARENA.width - CELL_SIZE / 2);
    const nextY = clamp(player.y + vector.y * distance, CELL_SIZE / 2, ARENA.height - CELL_SIZE / 2);
    if (!isWallAt(nextX, nextY)) {
      player.x = nextX;
      player.y = nextY;
    }
  }

  turn(player, delta) {
    player.angle = normalizeAngle(player.angle + clamp(Number(delta), -TURN_DEGREES, TURN_DEGREES));
  }

  shoot(game, player, now) {
    const wallHit = castRay({ x: player.x, y: player.y, angle: player.angle });
    const target = targetInSight(game, player);
    let hit = 'wall';
    let targetId = null;
    if (target) {
      target.hp = Math.max(0, target.hp - SHOT_DAMAGE);
      hit = 'player';
      targetId = target.id;
      if (target.hp === 0) {
        target.alive = false;
        game.defeats.push({
          id: `defeat-${target.id}-${now}`,
          player_id: target.id,
          x: target.x,
          y: target.y,
          created_at: now
        });
      }
    }
    game.shots.push({
      id: `${player.id}-${now}-${game.shots.length + 1}`,
      owner_id: player.id,
      angle: player.angle,
      hit,
      target_id: targetId,
      wall_distance: wallHit.distance,
      created_at: now
    });
    game.shots = game.shots.slice(-20);
  }

  updateWinner(game) {
    const alivePlayers = Object.values(game.players).filter(player => player.alive);
    if (Object.keys(game.players).length > 1 && alivePlayers.length === 1) {
      game.phase = 'finished';
      game.winner = alivePlayers[0].id;
    } else if (alivePlayers.length > 1) {
      game.phase = 'open';
      game.winner = null;
    }
  }

  getState(roomSlugInput) {
    return this.openRoom(roomSlugInput).state;
  }

  getRawState() {
    return this.read();
  }

  read() {
    return JSON.parse(fs.readFileSync(this.filePath, 'utf8'));
  }

  write(state) {
    fs.writeFileSync(this.filePath, JSON.stringify(state, null, 2));
  }
}

export { ARENA, CELL_SIZE, FPS_RULES, GRID_MAP, INACTIVE_RESET_MS, TURN_DEGREES, castRay, normalizeRoomSlug };
