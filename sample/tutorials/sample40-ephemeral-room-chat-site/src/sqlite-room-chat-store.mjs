import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { DatabaseSync } from 'node:sqlite';
import { MESSAGE_TTL_MS, ROOM_INACTIVE_TTL_MS, normalizeRoomSlug } from './ephemeral-room-chat-store.mjs';

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

function rowToMessage(row) {
  return {
    id: row.id,
    room_slug: row.room_slug,
    sender_name: row.sender_name,
    body: row.body,
    attachments: JSON.parse(row.attachments_json || '[]'),
    created_at: row.created_at,
    expires_at: row.expires_at
  };
}

export class SqliteRoomChatStore {
  constructor({ filePath, now = () => Date.now(), imageStore = null }) {
    this.filePath = filePath;
    this.now = now;
    this.imageStore = imageStore;
    fs.mkdirSync(path.dirname(filePath), { recursive: true });
    this.db = new DatabaseSync(filePath);
    this.bootstrap();
  }

  bootstrap() {
    this.db.exec(`
      CREATE TABLE IF NOT EXISTS room_registry (
        room_slug TEXT PRIMARY KEY,
        first_created_at INTEGER NOT NULL,
        recreate_count INTEGER NOT NULL,
        last_recreated_at INTEGER NOT NULL
      );
      CREATE TABLE IF NOT EXISTS rooms (
        room_slug TEXT PRIMARY KEY,
        created_at INTEGER NOT NULL,
        last_seen_at INTEGER NOT NULL
      );
      CREATE TABLE IF NOT EXISTS messages (
        id TEXT PRIMARY KEY,
        room_slug TEXT NOT NULL,
        sender_name TEXT NOT NULL,
        body TEXT NOT NULL,
        attachments_json TEXT NOT NULL,
        created_at INTEGER NOT NULL,
        expires_at INTEGER NOT NULL
      );
      CREATE INDEX IF NOT EXISTS idx_messages_room_expires
        ON messages (room_slug, expires_at);
    `);
  }

  openRoom(roomSlugInput) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    this.removeExpiredMessages(roomSlug, now);

    let registry = this.db.prepare('SELECT * FROM room_registry WHERE room_slug = ?').get(roomSlug);
    if (!registry) {
      registry = {
        room_slug: roomSlug,
        first_created_at: now,
        recreate_count: 0,
        last_recreated_at: now
      };
      this.db.prepare(`
        INSERT INTO room_registry (room_slug, first_created_at, recreate_count, last_recreated_at)
        VALUES (?, ?, ?, ?)
      `).run(registry.room_slug, registry.first_created_at, registry.recreate_count, registry.last_recreated_at);
    }

    let room = this.db.prepare('SELECT * FROM rooms WHERE room_slug = ?').get(roomSlug);
    if (!room) {
      registry = {
        ...registry,
        recreate_count: Number(registry.recreate_count) + 1,
        last_recreated_at: now
      };
      room = {
        room_slug: roomSlug,
        created_at: now,
        last_seen_at: now
      };
      this.db.prepare(`
        UPDATE room_registry SET recreate_count = ?, last_recreated_at = ? WHERE room_slug = ?
      `).run(registry.recreate_count, registry.last_recreated_at, roomSlug);
      this.db.prepare(`
        INSERT INTO rooms (room_slug, created_at, last_seen_at)
        VALUES (?, ?, ?)
      `).run(room.room_slug, room.created_at, room.last_seen_at);
    } else {
      room = { ...room, last_seen_at: now };
      this.db.prepare('UPDATE rooms SET last_seen_at = ? WHERE room_slug = ?').run(now, roomSlug);
    }

    return {
      room: clone(room),
      registry: clone(registry),
      messages: this.listMessages(roomSlug)
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
    const message = {
      id: crypto.randomUUID(),
      room_slug: opened.room.room_slug,
      sender_name: String(senderName ?? 'anonymous').trim().slice(0, 40) || 'anonymous',
      body: cleanBody.slice(0, 2000),
      attachments: cleanAttachments,
      created_at: now,
      expires_at: now + MESSAGE_TTL_MS
    };

    this.removeExpiredMessages(roomSlug, now);
    this.db.prepare(`
      INSERT INTO messages (id, room_slug, sender_name, body, attachments_json, created_at, expires_at)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(
      message.id,
      message.room_slug,
      message.sender_name,
      message.body,
      JSON.stringify(message.attachments),
      message.created_at,
      message.expires_at
    );
    this.db.prepare('UPDATE rooms SET last_seen_at = ? WHERE room_slug = ?').run(now, roomSlug);

    return { ok: true, message: clone(message), messages: this.listMessages(roomSlug) };
  }

  listMessages(roomSlugInput) {
    const now = this.now();
    const roomSlug = normalizeRoomSlug(roomSlugInput);
    this.removeExpiredMessages(roomSlug, now);
    if (this.db.prepare('SELECT room_slug FROM rooms WHERE room_slug = ?').get(roomSlug)) {
      this.db.prepare('UPDATE rooms SET last_seen_at = ? WHERE room_slug = ?').run(now, roomSlug);
    }
    const rows = this.db.prepare(`
      SELECT * FROM messages
      WHERE room_slug = ? AND expires_at > ?
      ORDER BY created_at ASC, id ASC
    `).all(roomSlug, now);
    return rows.map(rowToMessage);
  }

  cleanup() {
    const now = this.now();
    this.removeExpiredMessages(null, now);

    const staleRooms = this.db.prepare('SELECT * FROM rooms WHERE last_seen_at + ? <= ?')
      .all(ROOM_INACTIVE_TTL_MS, now);
    for (const room of staleRooms) {
      const messages = this.db.prepare('SELECT * FROM messages WHERE room_slug = ?').all(room.room_slug).map(rowToMessage);
      this.removeRoomMessages(messages);
      this.db.prepare('DELETE FROM messages WHERE room_slug = ?').run(room.room_slug);
      this.db.prepare('DELETE FROM rooms WHERE room_slug = ?').run(room.room_slug);
    }

    return this.getRawState();
  }

  getRawState() {
    const registryRows = this.db.prepare('SELECT * FROM room_registry ORDER BY room_slug').all();
    const roomRows = this.db.prepare('SELECT * FROM rooms ORDER BY room_slug').all();
    const messageRows = this.db.prepare('SELECT * FROM messages ORDER BY room_slug, created_at, id').all();

    const state = {
      room_registry: {},
      rooms: {},
      messages: {}
    };
    for (const registry of registryRows) {
      state.room_registry[registry.room_slug] = clone(registry);
    }
    for (const room of roomRows) {
      state.rooms[room.room_slug] = clone(room);
    }
    for (const row of messageRows) {
      const message = rowToMessage(row);
      state.messages[message.room_slug] = state.messages[message.room_slug] ?? [];
      state.messages[message.room_slug].push(message);
    }
    return state;
  }

  removeExpiredMessages(roomSlug, now) {
    const rows = roomSlug === null
      ? this.db.prepare('SELECT * FROM messages WHERE expires_at <= ?').all(now)
      : this.db.prepare('SELECT * FROM messages WHERE room_slug = ? AND expires_at <= ?').all(roomSlug, now);

    for (const row of rows) {
      this.removeRoomMessages([rowToMessage(row)]);
    }

    if (roomSlug === null) {
      this.db.prepare('DELETE FROM messages WHERE expires_at <= ?').run(now);
    } else {
      this.db.prepare('DELETE FROM messages WHERE room_slug = ? AND expires_at <= ?').run(roomSlug, now);
    }
  }

  removeRoomMessages(messages) {
    if (!this.imageStore) {
      return;
    }
    for (const message of messages ?? []) {
      this.imageStore.removeMessageAttachments(message);
    }
  }

  close() {
    this.db.close();
  }
}
