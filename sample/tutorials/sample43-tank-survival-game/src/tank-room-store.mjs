import fs from 'node:fs';
import path from 'node:path';

const DAY_MS = 24 * 60 * 60 * 1000;
const ARENA = Object.freeze({ width: 960, height: 600 });
const TANK_SIZE = 28;
const TANK_SPEED = 26;
const BULLET_SPEED = 52;
const BULLET_DAMAGE = 25;
const INACTIVE_RESET_MS = 7 * DAY_MS;
const OBSTACLES = Object.freeze([
  { id: 'wall-center', x: 430, y: 250, width: 100, height: 100 },
  { id: 'wall-north-west', x: 220, y: 120, width: 140, height: 42 },
  { id: 'wall-south-east', x: 600, y: 430, width: 150, height: 42 },
  { id: 'wall-south-west', x: 170, y: 390, width: 54, height: 120 },
  { id: 'wall-north-east', x: 730, y: 105, width: 54, height: 130 }
]);
const TANK_RULES = Object.freeze({
  max_players: null,
  player_hp: 100,
  tank_size: TANK_SIZE,
  tank_speed: TANK_SPEED,
  bullet_speed: BULLET_SPEED,
  bullet_damage: BULLET_DAMAGE,
  inactive_reset_ms: INACTIVE_RESET_MS,
  arena: ARENA,
  obstacles: OBSTACLES,
  commands: ['drive', 'turn', 'fire'],
  events: ['tank.updated']
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
    players: {},
    bullets: [],
    explosions: [],
    obstacles: clone(OBSTACLES)
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

function rectsOverlap(a, b) {
  return (
    a.x < b.x + b.width &&
    a.x + a.width > b.x &&
    a.y < b.y + b.height &&
    a.y + a.height > b.y
  );
}

function tankRect(tank) {
  return {
    x: tank.x - TANK_SIZE / 2,
    y: tank.y - TANK_SIZE / 2,
    width: TANK_SIZE,
    height: TANK_SIZE
  };
}

function pointInsideRect(point, rect) {
  return (
    point.x >= rect.x &&
    point.x <= rect.x + rect.width &&
    point.y >= rect.y &&
    point.y <= rect.y + rect.height
  );
}

function collidesWithObstacle(tank) {
  return OBSTACLES.some(obstacle => rectsOverlap(tankRect(tank), obstacle));
}

function playerSpawn(playerNumber) {
  const spawnPoints = [
    { x: 90, y: 90, angle: 0 },
    { x: ARENA.width - 90, y: ARENA.height - 90, angle: 180 },
    { x: ARENA.width - 90, y: 90, angle: 135 },
    { x: 90, y: ARENA.height - 90, angle: 315 },
    { x: ARENA.width / 2, y: 80, angle: 90 },
    { x: ARENA.width / 2, y: ARENA.height - 80, angle: 270 }
  ];
  const base = spawnPoints[(playerNumber - 1) % spawnPoints.length];
  const lap = Math.floor((playerNumber - 1) / spawnPoints.length);
  return {
    x: clamp(base.x + lap * 18, TANK_SIZE, ARENA.width - TANK_SIZE),
    y: clamp(base.y + lap * 18, TANK_SIZE, ARENA.height - TANK_SIZE),
    angle: base.angle
  };
}

export class TankRoomStore {
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
    const playerId = `t${nextNumber}-${String(now).slice(-5)}`;
    const spawn = playerSpawn(nextNumber);
    room.game.players[playerId] = {
      id: playerId,
      name: String(nameInput || playerId).slice(0, 24),
      hp: TANK_RULES.player_hp,
      alive: true,
      exploded: false,
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
      return { ok: false, error: 'player_destroyed', state: clone(room.game) };
    }

    if (command?.type === 'drive') {
      this.drive(player, command);
    } else if (command?.type === 'turn') {
      this.turn(player, command.angle);
    } else if (command?.type === 'fire') {
      this.fire(room.game, player, now);
    } else {
      return { ok: false, error: 'unsupported_command', state: clone(room.game) };
    }

    player.last_active_at = now;
    this.advanceBullets(room.game, now);
    this.updateWinner(room.game);
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return { ok: true, state: clone(room.game) };
  }

  drive(player, command) {
    if (command.angle !== undefined) {
      player.angle = normalizeAngle(command.angle);
    }
    const distance = clamp(Number(command.distance ?? TANK_SPEED), -TANK_SPEED, TANK_SPEED);
    const vector = angleToVector(player.angle);
    const candidate = {
      ...player,
      x: clamp(player.x + vector.x * distance, TANK_SIZE / 2, ARENA.width - TANK_SIZE / 2),
      y: clamp(player.y + vector.y * distance, TANK_SIZE / 2, ARENA.height - TANK_SIZE / 2)
    };
    if (!collidesWithObstacle(candidate)) {
      player.x = candidate.x;
      player.y = candidate.y;
    }
  }

  turn(player, angle) {
    player.angle = normalizeAngle(angle);
  }

  fire(game, player, now) {
    const vector = angleToVector(player.angle);
    game.bullets.push({
      id: `${player.id}-${now}-${game.bullets.length + 1}`,
      owner_id: player.id,
      x: player.x + vector.x * (TANK_SIZE / 2 + 8),
      y: player.y + vector.y * (TANK_SIZE / 2 + 8),
      vx: vector.x * BULLET_SPEED,
      vy: vector.y * BULLET_SPEED,
      created_at: now
    });
  }

  advanceBullets(game, now) {
    const liveBullets = [];
    for (const bullet of game.bullets) {
      const moved = {
        ...bullet,
        x: bullet.x + bullet.vx,
        y: bullet.y + bullet.vy
      };
      if (moved.x < 0 || moved.x > ARENA.width || moved.y < 0 || moved.y > ARENA.height) {
        continue;
      }
      if (OBSTACLES.some(obstacle => pointInsideRect(moved, obstacle))) {
        continue;
      }
      const target = Object.values(game.players).find(player => (
        player.id !== moved.owner_id &&
        player.alive &&
        Math.hypot(player.x - moved.x, player.y - moved.y) <= TANK_SIZE / 2 + 6
      ));
      if (target) {
        target.hp = Math.max(0, target.hp - BULLET_DAMAGE);
        if (target.hp === 0) {
          target.alive = false;
          target.exploded = true;
          game.explosions.push({
            id: `boom-${target.id}-${now}`,
            player_id: target.id,
            x: target.x,
            y: target.y,
            created_at: now
          });
        }
        continue;
      }
      liveBullets.push(moved);
    }
    game.bullets = liveBullets;
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

export { ARENA, INACTIVE_RESET_MS, OBSTACLES, TANK_RULES, normalizeRoomSlug };
