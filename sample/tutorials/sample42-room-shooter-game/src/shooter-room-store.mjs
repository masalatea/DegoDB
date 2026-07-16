import fs from 'node:fs';
import path from 'node:path';

const ARENA = { width: 900, height: 560 };
const PLAYER_RADIUS = 16;
const PLAYER_SPEED = 24;
const SHOT_SPEED = 48;
const SHOT_DAMAGE = 25;
const GAME_RULES = Object.freeze({
  max_players: 2,
  player_hp: 100,
  player_radius: PLAYER_RADIUS,
  player_speed: PLAYER_SPEED,
  shot_speed: SHOT_SPEED,
  shot_damage: SHOT_DAMAGE,
  arena: ARENA,
  commands: ['move', 'shoot'],
  events: ['game.updated']
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
    shots: [],
    winner: null
  };
}

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
}

function playerSpawn(playerNumber) {
  return playerNumber === 1
    ? { x: 120, y: ARENA.height / 2, facing: 'right' }
    : { x: ARENA.width - 120, y: ARENA.height / 2, facing: 'left' };
}

export class ShooterRoomStore {
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
    if (!room) {
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

  joinRoom(roomSlugInput) {
    const now = this.now();
    const opened = this.openRoom(roomSlugInput);
    const roomSlug = opened.room.room_slug;
    const state = this.read();
    const room = state.rooms[roomSlug];
    const playerCount = Object.keys(room.game.players).length;
    if (playerCount >= GAME_RULES.max_players) {
      return { ok: false, error: 'room_full', state: clone(room.game) };
    }
    const playerNumber = playerCount + 1;
    const playerId = `p${playerNumber}`;
    const spawn = playerSpawn(playerNumber);
    room.game.players[playerId] = {
      id: playerId,
      hp: GAME_RULES.player_hp,
      x: spawn.x,
      y: spawn.y,
      facing: spawn.facing,
      joined_at: now
    };
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
      this.movePlayer(player, command.direction);
    } else if (command?.type === 'shoot') {
      this.shoot(room.game, player, command.direction ?? player.facing, now);
    } else {
      return { ok: false, error: 'unsupported_command', state: clone(room.game) };
    }

    this.advanceShots(room.game);
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return { ok: true, state: clone(room.game) };
  }

  movePlayer(player, direction) {
    const delta = {
      up: [0, -PLAYER_SPEED],
      down: [0, PLAYER_SPEED],
      left: [-PLAYER_SPEED, 0],
      right: [PLAYER_SPEED, 0]
    }[direction] ?? [0, 0];
    if (direction && ['up', 'down', 'left', 'right'].includes(direction)) {
      player.facing = direction;
    }
    player.x = clamp(player.x + delta[0], PLAYER_RADIUS, ARENA.width - PLAYER_RADIUS);
    player.y = clamp(player.y + delta[1], PLAYER_RADIUS, ARENA.height - PLAYER_RADIUS);
  }

  shoot(game, player, direction, now) {
    const vector = {
      up: [0, -SHOT_SPEED],
      down: [0, SHOT_SPEED],
      left: [-SHOT_SPEED, 0],
      right: [SHOT_SPEED, 0]
    }[direction] ?? [SHOT_SPEED, 0];
    player.facing = direction;
    game.shots.push({
      id: `${player.id}-${now}-${game.shots.length + 1}`,
      owner_id: player.id,
      x: player.x,
      y: player.y,
      vx: vector[0],
      vy: vector[1],
      created_at: now
    });
  }

  advanceShots(game) {
    const liveShots = [];
    for (const shot of game.shots) {
      const moved = {
        ...shot,
        x: shot.x + shot.vx,
        y: shot.y + shot.vy
      };
      const target = Object.values(game.players).find(player => (
        player.id !== moved.owner_id &&
        player.hp > 0 &&
        Math.hypot(player.x - moved.x, player.y - moved.y) <= PLAYER_RADIUS + 6
      ));
      if (target) {
        target.hp = Math.max(0, target.hp - SHOT_DAMAGE);
        if (target.hp === 0) {
          game.winner = moved.owner_id;
        }
        continue;
      }
      if (moved.x >= 0 && moved.x <= ARENA.width && moved.y >= 0 && moved.y <= ARENA.height) {
        liveShots.push(moved);
      }
    }
    game.shots = liveShots;
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

export { ARENA, GAME_RULES, SHOT_DAMAGE, normalizeRoomSlug };
