import fs from 'node:fs';
import path from 'node:path';

const DAY_MS = 24 * 60 * 60 * 1000;
const COLS = 35;
const ROWS = 35;
const CELL = 48;
const ROTATION_RATE = 90;
const RACER_SPEED = 145;
const RACER_RADIUS = 13;
const INACTIVE_RESET_MS = 7 * DAY_MS;
const COLORS = ['#38bdf8', '#f97316', '#a78bfa', '#22c55e'];
const NAMES = ['Blue', 'Orange', 'Violet', 'Green'];
const STARTS = [
  { col: 1, row: 1, angle: 0 },
  { col: 33, row: 1, angle: 90 },
  { col: 1, row: 33, angle: 270 },
  { col: 33, row: 33, angle: 180 }
];
const GOAL = Object.freeze({ col: 17, row: 17 });

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
  return { room_registry: {}, rooms: {} };
}

function buildMaze() {
  const grid = Array.from({ length: ROWS }, () => Array(COLS).fill(1));
  const stack = [[1, 1]];
  grid[1][1] = 0;
  const dirs = [[0, -2], [2, 0], [0, 2], [-2, 0]];
  let seed = 49049;
  const random = () => {
    seed = (seed * 1664525 + 1013904223) >>> 0;
    return seed / 4294967296;
  };
  while (stack.length > 0) {
    const current = stack[stack.length - 1];
    const options = dirs
      .map(([dx, dy]) => [current[0] + dx, current[1] + dy, dx, dy])
      .filter(([x, y]) => x > 0 && y > 0 && x < COLS - 1 && y < ROWS - 1 && grid[y][x] === 1);
    if (options.length === 0) {
      stack.pop();
      continue;
    }
    const [nx, ny, dx, dy] = options[Math.floor(random() * options.length)];
    grid[current[1] + dy / 2][current[0] + dx / 2] = 0;
    grid[ny][nx] = 0;
    stack.push([nx, ny]);
  }
  for (let y = 15; y <= 19; y += 1) {
    for (let x = 15; x <= 19; x += 1) grid[y][x] = 0;
  }
  for (const { col, row } of [...STARTS, GOAL]) grid[row][col] = 0;
  return grid;
}

function cellCenter(col, row) {
  return { x: col * CELL + CELL / 2, y: row * CELL + CELL / 2 };
}

function initialGame(roomSlug) {
  const maze = { cols: COLS, rows: ROWS, cell: CELL, grid: buildMaze() };
  const racers = {};
  STARTS.forEach((start, index) => {
    racers[`slot${index}`] = {
      id: `slot${index}`,
      name: `${NAMES[index]} AI`,
      color: COLORS[index],
      ai: true,
      slot: index,
      ...cellCenter(start.col, start.row),
      angle: start.angle,
      holding: false,
      finished: false,
      finish_time: null,
      path: []
    };
  });
  const game = {
    room_slug: roomSlug,
    revision: 0,
    winner: null,
    racers,
    maze,
    goal: clone(GOAL),
    rules: {
      rotation_rate_degrees_per_second: ROTATION_RATE,
      space_hold_behavior: 'drive_forward_without_rotation',
      space_released_behavior: 'rotate_in_place'
    }
  };
  assignAiPaths(game);
  return game;
}

function gridPos(entity) {
  return {
    col: Math.max(0, Math.min(COLS - 1, Math.floor(entity.x / CELL))),
    row: Math.max(0, Math.min(ROWS - 1, Math.floor(entity.y / CELL)))
  };
}

function isOpen(game, col, row) {
  return row >= 0 && col >= 0 && row < ROWS && col < COLS && game.maze.grid[row][col] === 0;
}

function computePath(game, start, goal) {
  const queue = [start];
  const key = point => `${point.col},${point.row}`;
  const prev = new Map([[key(start), null]]);
  for (let i = 0; i < queue.length; i += 1) {
    const current = queue[i];
    if (current.col === goal.col && current.row === goal.row) break;
    for (const dir of [{ col: 1, row: 0 }, { col: -1, row: 0 }, { col: 0, row: 1 }, { col: 0, row: -1 }]) {
      const next = { col: current.col + dir.col, row: current.row + dir.row };
      const nextKey = key(next);
      if (!isOpen(game, next.col, next.row) || prev.has(nextKey)) continue;
      prev.set(nextKey, current);
      queue.push(next);
    }
  }
  const path = [];
  let cursor = goal;
  while (cursor) {
    path.push(cellCenter(cursor.col, cursor.row));
    cursor = prev.get(key(cursor));
  }
  return path.reverse();
}

function assignAiPaths(game) {
  for (const racer of Object.values(game.racers)) {
    if (!racer.ai) continue;
    racer.path = computePath(game, gridPos(racer), GOAL);
    racer.target_index = 1;
  }
}

function wallAtPixel(game, x, y) {
  return !isOpen(game, Math.floor(x / CELL), Math.floor(y / CELL));
}

function canMoveTo(game, x, y) {
  return !wallAtPixel(game, x - RACER_RADIUS, y - RACER_RADIUS)
    && !wallAtPixel(game, x + RACER_RADIUS, y - RACER_RADIUS)
    && !wallAtPixel(game, x - RACER_RADIUS, y + RACER_RADIUS)
    && !wallAtPixel(game, x + RACER_RADIUS, y + RACER_RADIUS);
}

function shortestAngleDelta(from, to) {
  return ((to - from + 540) % 360) - 180;
}

function updateAi(game, racer) {
  const target = racer.path[racer.target_index];
  if (!target) return;
  if (Math.hypot(target.x - racer.x, target.y - racer.y) < 12) {
    racer.target_index = Math.min(racer.path.length - 1, racer.target_index + 1);
  }
  const next = racer.path[racer.target_index] ?? target;
  const targetAngle = Math.atan2(next.y - racer.y, next.x - racer.x) * 180 / Math.PI;
  racer.holding = Math.abs(shortestAngleDelta(racer.angle, targetAngle)) < 7;
}

function tickRacer(game, racer, dt, now) {
  if (racer.finished || game.winner) return;
  if (racer.ai) updateAi(game, racer);
  if (racer.holding) {
    const radians = racer.angle * Math.PI / 180;
    const nextX = racer.x + Math.cos(radians) * RACER_SPEED * dt;
    const nextY = racer.y + Math.sin(radians) * RACER_SPEED * dt;
    if (canMoveTo(game, nextX, racer.y)) racer.x = nextX;
    if (canMoveTo(game, racer.x, nextY)) racer.y = nextY;
  } else {
    racer.angle = (racer.angle + ROTATION_RATE * dt) % 360;
  }
  const goal = cellCenter(GOAL.col, GOAL.row);
  if (Math.hypot(racer.x - goal.x, racer.y - goal.y) < 48) {
    racer.finished = true;
    racer.finish_time = now;
    game.winner = { id: racer.id, name: racer.name };
  }
}

export class MazeRoomStore {
  constructor({ filePath, now = () => Date.now() }) {
    this.filePath = filePath;
    this.now = now;
    fs.mkdirSync(path.dirname(filePath), { recursive: true });
    if (!fs.existsSync(filePath)) this.write(defaultState());
  }

  openRoom(roomSlugInput) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const state = this.read();
    const registry = state.room_registry[roomSlug] ?? { room_slug: roomSlug, first_created_at: now, recreate_count: 0 };
    let room = state.rooms[roomSlug];
    if (!room || now - room.last_activity_at >= INACTIVE_RESET_MS) {
      registry.recreate_count += 1;
      registry.last_recreated_at = now;
      room = { room_slug: roomSlug, created_at: now, last_activity_at: now, last_tick_at: now, game: initialGame(roomSlug) };
    }
    state.room_registry[roomSlug] = registry;
    state.rooms[roomSlug] = room;
    this.write(state);
    return { room: clone(room), registry: clone(registry), state: clone(room.game) };
  }

  joinRoom(roomSlugInput, { playerId = '', name = '' } = {}) {
    const now = this.now();
    const opened = this.openRoom(roomSlugInput);
    const state = this.read();
    const room = state.rooms[opened.room.room_slug];
    if (playerId && room.game.racers[playerId] && !room.game.racers[playerId].ai) {
      return { ok: true, player: clone(room.game.racers[playerId]), state: clone(room.game) };
    }
    const aiSlot = Object.values(room.game.racers).find(racer => racer.ai);
    if (!aiSlot) {
      return { ok: false, error: 'room_full', state: clone(room.game) };
    }
    const slot = aiSlot.slot;
    const start = STARTS[slot % STARTS.length];
    const id = `p${slot}-${String(now).slice(-5)}`;
    room.game.racers[id] = {
      id,
      name: String(name || NAMES[slot] || id).slice(0, 24),
      color: COLORS[slot % COLORS.length],
      ai: false,
      slot,
      ...cellCenter(start.col, start.row),
      angle: start.angle,
      holding: false,
      finished: false,
      finish_time: null
    };
    delete room.game.racers[aiSlot.id];
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return { ok: true, player: clone(room.game.racers[id]), state: clone(room.game) };
  }

  applyCommand({ roomSlug: roomSlugInput, playerId, command }) {
    const now = this.now();
    const state = this.read();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const room = state.rooms[roomSlug];
    if (!room) return { ok: false, error: 'room_not_found' };
    const player = room.game.racers[playerId];
    if (!player || player.ai) return { ok: false, error: 'player_not_found', state: clone(room.game) };
    if (command?.type === 'hold') {
      player.holding = command.holding === true;
    } else {
      return { ok: false, error: 'unsupported_command', state: clone(room.game) };
    }
    room.game.revision += 1;
    room.last_activity_at = now;
    this.write(state);
    return { ok: true, state: clone(room.game) };
  }

  resetRoom(roomSlugInput) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const state = this.read();
    state.rooms[roomSlug] = { room_slug: roomSlug, created_at: now, last_activity_at: now, last_tick_at: now, game: initialGame(roomSlug) };
    this.write(state);
    return { ok: true, state: clone(state.rooms[roomSlug].game) };
  }

  advanceActiveRooms() {
    const now = this.now();
    const state = this.read();
    const updates = [];
    for (const [roomSlug, room] of Object.entries(state.rooms)) {
      if (now - room.last_activity_at >= INACTIVE_RESET_MS) continue;
      const dt = Math.min(0.05, Math.max(0.001, (now - room.last_tick_at) / 1000));
      for (const racer of Object.values(room.game.racers)) tickRacer(room.game, racer, dt, now);
      room.game.revision += 1;
      room.last_tick_at = now;
      updates.push({ room_slug: roomSlug, state: clone(room.game) });
    }
    if (updates.length > 0) this.write(state);
    return updates;
  }

  getState(roomSlugInput) {
    return this.openRoom(roomSlugInput).state;
  }

  read() {
    return JSON.parse(fs.readFileSync(this.filePath, 'utf8'));
  }

  write(state) {
    fs.writeFileSync(this.filePath, JSON.stringify(state, null, 2) + '\n');
  }
}

export { normalizeRoomSlug };
