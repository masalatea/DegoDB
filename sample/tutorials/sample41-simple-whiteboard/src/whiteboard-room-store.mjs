import fs from 'node:fs';
import path from 'node:path';

const DAY_MS = 24 * 60 * 60 * 1000;
const BOARD_INACTIVE_CLEAR_MS = 7 * DAY_MS;

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
    rooms: {},
    boards: {}
  };
}

function normalizeOperation(operation) {
  if (operation?.type === 'stroke') {
    const points = Array.isArray(operation.points)
      ? operation.points.map(point => ({
        x: Number(point.x),
        y: Number(point.y)
      })).filter(point => Number.isFinite(point.x) && Number.isFinite(point.y))
      : [];
    if (points.length < 2) {
      return { ok: false, error: 'stroke_points_required' };
    }
    return {
      ok: true,
      operation: {
        type: 'stroke',
        tool: operation.tool === 'eraser' ? 'eraser' : 'pen',
        color: String(operation.color ?? '#2563eb').slice(0, 24),
        size: Math.max(1, Math.min(64, Number(operation.size) || 6)),
        points
      }
    };
  }

  if (operation?.type === 'text') {
    const text = String(operation.text ?? '').trim().slice(0, 120);
    if (text === '') {
      return { ok: false, error: 'text_required' };
    }
    const x = Number(operation.x);
    const y = Number(operation.y);
    if (!Number.isFinite(x) || !Number.isFinite(y)) {
      return { ok: false, error: 'text_position_required' };
    }
    return {
      ok: true,
      operation: {
        type: 'text',
        text,
        x,
        y,
        color: String(operation.color ?? '#2563eb').slice(0, 24),
        size: Math.max(1, Math.min(64, Number(operation.size) || 6))
      }
    };
  }

  return { ok: false, error: 'unsupported_operation_type' };
}

export class WhiteboardRoomStore {
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
      recreate_count: 0,
      clear_count: 0
    };

    let room = state.rooms[roomSlug];
    if (!room) {
      registry.recreate_count += 1;
      registry.last_recreated_at = now;
      room = {
        room_slug: roomSlug,
        created_at: now,
        last_activity_at: now
      };
      state.boards[roomSlug] = {
        room_slug: roomSlug,
        revision: 0,
        operations: [],
        cleared_at: null
      };
    } else {
      const inactive = room.last_activity_at + BOARD_INACTIVE_CLEAR_MS <= now;
      if (inactive) {
        registry.clear_count += 1;
        registry.last_cleared_at = now;
        room = {
          ...room,
          last_activity_at: now
        };
        state.boards[roomSlug] = {
          room_slug: roomSlug,
          revision: 0,
          operations: [],
          cleared_at: now
        };
      }
    }

    state.room_registry[roomSlug] = registry;
    state.rooms[roomSlug] = room;
    state.boards[roomSlug] = state.boards[roomSlug] ?? {
      room_slug: roomSlug,
      revision: 0,
      operations: [],
      cleared_at: null
    };
    this.write(state);

    return {
      room: clone(room),
      registry: clone(registry),
      board: clone(state.boards[roomSlug])
    };
  }

  appendOperation({ roomSlug: roomSlugInput, expectedRevision, operation }) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const opened = this.openRoom(roomSlug);
    const currentRevision = opened.board.revision;
    if (Number(expectedRevision) !== currentRevision) {
      return {
        ok: false,
        error: 'revision_conflict',
        current_revision: currentRevision,
        board: opened.board
      };
    }

    const normalized = normalizeOperation(operation);
    if (!normalized.ok) {
      return normalized;
    }

    const state = this.read();
    const board = state.boards[roomSlug];
    const nextOperation = {
      ...normalized.operation,
      operation_id: `${roomSlug}-${board.revision + 1}`,
      created_at: now
    };
    board.operations.push(nextOperation);
    board.revision += 1;
    state.rooms[roomSlug].last_activity_at = now;
    this.write(state);

    return {
      ok: true,
      operation: clone(nextOperation),
      board: clone(board)
    };
  }

  fetchBoard(roomSlugInput) {
    return this.openRoom(roomSlugInput).board;
  }

  clearInactiveBoards() {
    const now = this.now();
    const state = this.read();
    for (const [roomSlug, room] of Object.entries(state.rooms)) {
      if (room.last_activity_at + BOARD_INACTIVE_CLEAR_MS <= now) {
        const registry = state.room_registry[roomSlug];
        registry.clear_count += 1;
        registry.last_cleared_at = now;
        state.boards[roomSlug] = {
          room_slug: roomSlug,
          revision: 0,
          operations: [],
          cleared_at: now
        };
        state.rooms[roomSlug] = {
          ...room,
          last_activity_at: now
        };
      }
    }
    this.write(state);
    return clone(state);
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

export { BOARD_INACTIVE_CLEAR_MS, normalizeOperation, normalizeRoomSlug };
