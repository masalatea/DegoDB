import fs from 'node:fs';
import path from 'node:path';

const DAY_MS = 24 * 60 * 60 * 1000;
const WORLD = Object.freeze({ width: 1600, height: 1000 });
const PLAYER_RADIUS = 16;
const PLAYER_SPEED = 32;
const PLAYER_MAX_HP = 100;
const PLAYER_REGEN_PER_TICK = 1;
const IDLE_REGEN_MS = 1600;
const ENEMY_RADIUS = 15;
const ENEMY_SPEED = 9;
const ENEMY_MAX_HP = 45;
const ENEMY_TOUCH_DAMAGE = 4;
const ENEMY_ATTACK_COOLDOWN_MS = 850;
const SWORD_RANGE = 54;
const SWORD_DAMAGE = 25;
const ENEMY_SPAWN_MIN_DISTANCE = 180;
const ENEMY_SPAWN_MAX_DISTANCE = 360;
const MAX_ENEMIES = 8;
const ENEMY_EXP = 10;
const ENEMY_GOLD = 6;
const INACTIVE_RESET_MS = 7 * DAY_MS;
const OBSTACLES = Object.freeze([
  { id: 'tree-grove-west', type: 'trees', x: 230, y: 190, width: 150, height: 86 },
  { id: 'rock-north', type: 'rocks', x: 720, y: 110, width: 130, height: 72 },
  { id: 'pond-east', type: 'pond', x: 1190, y: 220, width: 170, height: 110 },
  { id: 'tree-grove-center', type: 'trees', x: 940, y: 470, width: 180, height: 105 },
  { id: 'rock-south-west', type: 'rocks', x: 350, y: 740, width: 160, height: 80 },
  { id: 'tree-grove-south', type: 'trees', x: 1040, y: 760, width: 190, height: 95 }
]);
const RPG_RULES = Object.freeze({
  max_players: null,
  pvp_enabled: false,
  player_hp: PLAYER_MAX_HP,
  player_speed: PLAYER_SPEED,
  player_regen_per_tick: PLAYER_REGEN_PER_TICK,
  idle_regen_ms: IDLE_REGEN_MS,
  enemy_hp: ENEMY_MAX_HP,
  enemy_speed: ENEMY_SPEED,
  enemy_touch_damage: ENEMY_TOUCH_DAMAGE,
  sword_range: SWORD_RANGE,
  sword_damage: SWORD_DAMAGE,
  max_enemies: MAX_ENEMIES,
  enemy_spawn_min_distance: ENEMY_SPAWN_MIN_DISTANCE,
  enemy_spawn_max_distance: ENEMY_SPAWN_MAX_DISTANCE,
  reward_exp: ENEMY_EXP,
  reward_gold: ENEMY_GOLD,
  inactive_reset_ms: INACTIVE_RESET_MS,
  world: WORLD,
  obstacles: OBSTACLES,
  commands: ['move', 'attack'],
  events: ['rpg.updated']
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
    players: {},
    enemies: {},
    next_enemy_seq: 1,
    effects: [],
    obstacles: clone(OBSTACLES),
    world: clone(WORLD)
  };
}

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
}

function normalizeVector(dx, dy) {
  const length = Math.hypot(dx, dy);
  if (length === 0) {
    return { x: 0, y: 0 };
  }
  return { x: dx / length, y: dy / length };
}

function normalizeFacing(facing) {
  if (['up', 'down', 'left', 'right'].includes(facing)) {
    return facing;
  }
  return 'down';
}

function facingVector(facing) {
  return {
    up: { x: 0, y: -1 },
    down: { x: 0, y: 1 },
    left: { x: -1, y: 0 },
    right: { x: 1, y: 0 }
  }[normalizeFacing(facing)];
}

function playerSpawn(playerNumber) {
  const center = { x: WORLD.width / 2, y: WORLD.height / 2 };
  const offsets = [
    { x: 0, y: 0 },
    { x: 60, y: 40 },
    { x: -70, y: 30 },
    { x: 50, y: -70 },
    { x: -80, y: -60 }
  ];
  const offset = offsets[(playerNumber - 1) % offsets.length];
  return {
    x: clamp(center.x + offset.x, PLAYER_RADIUS, WORLD.width - PLAYER_RADIUS),
    y: clamp(center.y + offset.y, PLAYER_RADIUS, WORLD.height - PLAYER_RADIUS)
  };
}

function circleIntersectsRect(circle, rect) {
  const nearestX = clamp(circle.x, rect.x, rect.x + rect.width);
  const nearestY = clamp(circle.y, rect.y, rect.y + rect.height);
  return Math.hypot(circle.x - nearestX, circle.y - nearestY) <= circle.radius;
}

function collidesWithObstacle(entity, radius) {
  return OBSTACLES.some(obstacle => circleIntersectsRect({ x: entity.x, y: entity.y, radius }, obstacle));
}

function nearestAlivePlayer(game, point) {
  const alivePlayers = Object.values(game.players).filter(player => player.hp > 0);
  if (alivePlayers.length === 0) {
    return null;
  }
  return alivePlayers.reduce((nearest, player) => {
    const distance = Math.hypot(player.x - point.x, player.y - point.y);
    if (!nearest || distance < nearest.distance) {
      return { player, distance };
    }
    return nearest;
  }, null);
}

export class RpgRoomStore {
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
    const playerId = `hero${nextNumber}-${String(now).slice(-5)}`;
    const spawn = playerSpawn(nextNumber);
    room.game.players[playerId] = {
      id: playerId,
      name: String(nameInput || playerId).slice(0, 24),
      hp: PLAYER_MAX_HP,
      max_hp: PLAYER_MAX_HP,
      exp: 0,
      gold: 0,
      level: 1,
      x: spawn.x,
      y: spawn.y,
      facing: 'down',
      attacking_until: 0,
      joined_at: now,
      last_active_at: now,
      last_move_at: 0,
      last_damage_at: 0
    };
    this.ensureEnemyPopulation(room.game, now);
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
    if (player.hp <= 0) {
      return { ok: false, error: 'player_defeated', state: clone(room.game) };
    }

    if (command?.type === 'move') {
      this.movePlayer(player, command);
    } else if (command?.type === 'attack') {
      this.attack(room.game, player, now);
    } else {
      return { ok: false, error: 'unsupported_command', state: clone(room.game) };
    }

    player.last_active_at = now;
    this.tickGame(room.game, now);
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return { ok: true, state: clone(room.game) };
  }

  advanceActiveRooms() {
    const now = this.now();
    const state = this.read();
    const updatedRooms = [];
    for (const [roomSlug, room] of Object.entries(state.rooms)) {
      if (now - room.last_activity_at >= INACTIVE_RESET_MS) {
        continue;
      }
      if (Object.keys(room.game.players).length === 0) {
        continue;
      }
      this.tickGame(room.game, now);
      room.game.revision += 1;
      room.last_activity_at = now;
      updatedRooms.push({
        room_slug: roomSlug,
        state: clone(room.game)
      });
    }
    if (updatedRooms.length > 0) {
      this.write(state);
    }
    return updatedRooms;
  }

  movePlayer(player, command) {
    const dx = Number(command.dx ?? 0);
    const dy = Number(command.dy ?? 0);
    const vector = normalizeVector(dx, dy);
    if (Math.abs(vector.x) > Math.abs(vector.y)) {
      player.facing = vector.x < 0 ? 'left' : 'right';
    } else if (vector.y !== 0) {
      player.facing = vector.y < 0 ? 'up' : 'down';
    }
    const candidate = {
      x: clamp(player.x + vector.x * PLAYER_SPEED, PLAYER_RADIUS, WORLD.width - PLAYER_RADIUS),
      y: clamp(player.y + vector.y * PLAYER_SPEED, PLAYER_RADIUS, WORLD.height - PLAYER_RADIUS)
    };
    if (!collidesWithObstacle(candidate, PLAYER_RADIUS)) {
      player.x = candidate.x;
      player.y = candidate.y;
    }
    player.last_move_at = this.now();
  }

  attack(game, player, now) {
    const vector = facingVector(player.facing);
    const swordPoint = {
      x: player.x + vector.x * SWORD_RANGE,
      y: player.y + vector.y * SWORD_RANGE
    };
    player.attacking_until = now + 180;
    let defeated = 0;
    for (const enemy of Object.values(game.enemies)) {
      if (enemy.hp <= 0) {
        continue;
      }
      const distance = Math.hypot(enemy.x - swordPoint.x, enemy.y - swordPoint.y);
      if (distance <= ENEMY_RADIUS + 30) {
        enemy.hp = Math.max(0, enemy.hp - SWORD_DAMAGE);
        game.effects.push({
          id: `slash-${player.id}-${now}-${enemy.id}`,
          type: 'slash',
          x: enemy.x,
          y: enemy.y,
          created_at: now
        });
        if (enemy.hp === 0) {
          defeated += 1;
          game.effects.push({
            id: `drop-${enemy.id}-${now}`,
            type: 'defeat',
            x: enemy.x,
            y: enemy.y,
            created_at: now
          });
        }
      }
    }
    if (defeated > 0) {
      player.exp += defeated * ENEMY_EXP;
      player.gold += defeated * ENEMY_GOLD;
      player.level = 1 + Math.floor(player.exp / 50);
    }
    for (const [id, enemy] of Object.entries(game.enemies)) {
      if (enemy.hp <= 0) {
        delete game.enemies[id];
      }
    }
    this.ensureEnemyPopulation(game, now);
  }

  tickGame(game, now) {
    this.ensureEnemyPopulation(game, now);
    for (const enemy of Object.values(game.enemies)) {
      const nearest = nearestAlivePlayer(game, enemy);
      if (!nearest) {
        continue;
      }
      const vector = normalizeVector(nearest.player.x - enemy.x, nearest.player.y - enemy.y);
      const candidate = {
        x: clamp(enemy.x + vector.x * ENEMY_SPEED, ENEMY_RADIUS, WORLD.width - ENEMY_RADIUS),
        y: clamp(enemy.y + vector.y * ENEMY_SPEED, ENEMY_RADIUS, WORLD.height - ENEMY_RADIUS)
      };
      if (!collidesWithObstacle(candidate, ENEMY_RADIUS)) {
        enemy.x = candidate.x;
        enemy.y = candidate.y;
      }
      enemy.target_player_id = nearest.player.id;
      if (nearest.distance <= PLAYER_RADIUS + ENEMY_RADIUS + 4 && now - nearest.player.last_damage_at >= ENEMY_ATTACK_COOLDOWN_MS) {
        nearest.player.hp = Math.max(0, nearest.player.hp - ENEMY_TOUCH_DAMAGE);
        nearest.player.last_damage_at = now;
        game.effects.push({
          id: `bite-${enemy.id}-${now}`,
          type: 'enemy-hit',
          x: nearest.player.x,
          y: nearest.player.y,
          created_at: now
        });
      }
    }
    for (const player of Object.values(game.players)) {
      if (player.hp > 0 && player.hp < player.max_hp && now - player.last_move_at >= IDLE_REGEN_MS) {
        player.hp = Math.min(player.max_hp, player.hp + PLAYER_REGEN_PER_TICK);
      }
    }
    game.effects = game.effects.filter(effect => now - effect.created_at < 900);
  }

  ensureEnemyPopulation(game, now) {
    const alivePlayers = Object.values(game.players).filter(player => player.hp > 0);
    if (alivePlayers.length === 0) {
      return;
    }
    while (Object.keys(game.enemies).length < MAX_ENEMIES) {
      const basePlayer = alivePlayers[Object.keys(game.enemies).length % alivePlayers.length];
      const enemyId = `e${game.next_enemy_seq ?? 1}-${now}`;
      game.next_enemy_seq = (game.next_enemy_seq ?? 1) + 1;
      const angle = ((Object.keys(game.enemies).length * 137) % 360) * Math.PI / 180;
      const distance = ENEMY_SPAWN_MIN_DISTANCE + (Object.keys(game.enemies).length % 4) * ((ENEMY_SPAWN_MAX_DISTANCE - ENEMY_SPAWN_MIN_DISTANCE) / 3);
      game.enemies[enemyId] = {
        id: enemyId,
        hp: ENEMY_MAX_HP,
        max_hp: ENEMY_MAX_HP,
        x: clamp(basePlayer.x + Math.cos(angle) * distance, ENEMY_RADIUS, WORLD.width - ENEMY_RADIUS),
        y: clamp(basePlayer.y + Math.sin(angle) * distance, ENEMY_RADIUS, WORLD.height - ENEMY_RADIUS),
        target_player_id: basePlayer.id,
        spawned_at: now
      };
      if (collidesWithObstacle(game.enemies[enemyId], ENEMY_RADIUS)) {
        game.enemies[enemyId].x = clamp(basePlayer.x - Math.cos(angle) * distance, ENEMY_RADIUS, WORLD.width - ENEMY_RADIUS);
        game.enemies[enemyId].y = clamp(basePlayer.y - Math.sin(angle) * distance, ENEMY_RADIUS, WORLD.height - ENEMY_RADIUS);
      }
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

export { INACTIVE_RESET_MS, RPG_RULES, WORLD, normalizeRoomSlug };
