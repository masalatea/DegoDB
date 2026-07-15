import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const DAY_MS = 24 * 60 * 60 * 1000;
const MESSAGE_TTL_MS = DAY_MS;
const ROOM_INACTIVE_TTL_MS = 7 * DAY_MS;

function normalizeRoomSlug(slug) {
  const normalized = String(slug ?? '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9-]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return normalized === '' ? 'general' : normalized.slice(0, 80);
}

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function normalizeAttachments(attachments) {
  return (attachments ?? []).map(attachment => ({
    attachment_id: String(attachment.attachment_id),
    type: 'image',
    file_name: String(attachment.file_name),
    mime_type: String(attachment.mime_type),
    size_bytes: Number(attachment.size_bytes),
    storage_key: String(attachment.storage_key)
  }));
}

function defaultState() {
  return {
    room_registry: {},
    rooms: {},
    messages: {}
  };
}

export class EphemeralRoomChatStore {
  constructor({ filePath, now = () => Date.now(), imageStore = null }) {
    this.filePath = filePath;
    this.now = now;
    this.imageStore = imageStore;
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
        last_seen_at: now
      };
    } else {
      room.last_seen_at = now;
    }

    state.room_registry[roomSlug] = registry;
    state.rooms[roomSlug] = room;
    state.messages[roomSlug] = this.liveMessages(state.messages[roomSlug] ?? [], now);
    this.write(state);

    return {
      room: clone(room),
      registry: clone(registry),
      messages: clone(state.messages[roomSlug])
    };
  }

  postMessage({ roomSlug: roomSlugInput, senderName, body, attachments = [] }) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const cleanBody = String(body ?? '').trim();
    const cleanAttachments = normalizeAttachments(attachments);
    if (cleanBody === '' && cleanAttachments.length === 0) {
      return { ok: false, error: 'message_body_required' };
    }

    const opened = this.openRoom(roomSlug);
    const state = this.read();
    const message = {
      id: crypto.randomUUID(),
      room_slug: opened.room.room_slug,
      sender_name: String(senderName ?? 'anonymous').trim().slice(0, 40) || 'anonymous',
      body: cleanBody.slice(0, 2000),
      attachments: cleanAttachments,
      created_at: now,
      expires_at: now + MESSAGE_TTL_MS
    };
    state.messages[roomSlug] = [...this.liveMessages(state.messages[roomSlug] ?? [], now), message];
    state.rooms[roomSlug].last_seen_at = now;
    this.write(state);

    return { ok: true, message: clone(message), messages: clone(state.messages[roomSlug]) };
  }

  listMessages(roomSlugInput) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    const state = this.read();
    const messages = this.liveMessages(state.messages[roomSlug] ?? [], now);
    state.messages[roomSlug] = messages;
    if (state.rooms[roomSlug]) {
      state.rooms[roomSlug].last_seen_at = now;
    }
    this.write(state);
    return clone(messages);
  }

  cleanup() {
    const now = this.now();
    const state = this.read();

    for (const roomSlug of Object.keys(state.messages)) {
      state.messages[roomSlug] = this.liveMessages(state.messages[roomSlug], now);
    }

    for (const [roomSlug, room] of Object.entries(state.rooms)) {
      if (room.last_seen_at + ROOM_INACTIVE_TTL_MS <= now) {
        this.removeRoomMessages(state.messages[roomSlug]);
        delete state.rooms[roomSlug];
        delete state.messages[roomSlug];
      }
    }

    this.write(state);
    return clone(state);
  }

  getRawState() {
    return this.read();
  }

  liveMessages(messages, now) {
    const live = [];
    for (const message of messages) {
      if (message.expires_at > now) {
        live.push(message);
      } else if (this.imageStore) {
        this.imageStore.removeMessageAttachments(message);
      }
    }
    return live;
  }

  removeRoomMessages(messages) {
    if (!this.imageStore) {
      return;
    }
    for (const message of messages ?? []) {
      this.imageStore.removeMessageAttachments(message);
    }
  }

  read() {
    return JSON.parse(fs.readFileSync(this.filePath, 'utf8'));
  }

  write(state) {
    fs.writeFileSync(this.filePath, JSON.stringify(state, null, 2));
  }
}

export { MESSAGE_TTL_MS, ROOM_INACTIVE_TTL_MS, normalizeRoomSlug };
