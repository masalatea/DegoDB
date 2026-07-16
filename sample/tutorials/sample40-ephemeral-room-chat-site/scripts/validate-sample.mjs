import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { EphemeralRoomChatStore, MESSAGE_TTL_MS, ROOM_INACTIVE_TTL_MS, normalizeRoomSlug } from '../src/ephemeral-room-chat-store.mjs';
import { EphemeralImageStore } from '../src/ephemeral-image-store.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

for (const file of [
  'README.md',
  'src/ephemeral-image-store.mjs',
  'src/ephemeral-room-chat-store.mjs',
  'src/sqlite-room-chat-store.mjs',
  'src/server.mjs',
  'scripts/validate-sample.mjs',
  'scripts/validate-http-routes.mjs',
  'scripts/validate-sqlite-store.mjs',
  'public/.gitkeep'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample40 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample40 must not include node_modules');

let now = Date.UTC(2026, 6, 16, 0, 0, 0);
const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample40-ephemeral-chat-'));
const imageStore = new EphemeralImageStore({
  rootDir: path.join(tempRoot, 'images'),
  maxBytes: 1024
});
const store = new EphemeralRoomChatStore({
  filePath: path.join(tempRoot, 'chat-store.json'),
  now: () => now,
  imageStore
});

try {
  assert(normalizeRoomSlug(' Team Room!! ') === 'team-room', 'room slug is normalized');

  const opened = store.openRoom('Team Room!!');
  assert(opened.room.room_slug === 'team-room', 'room opens with normalized slug');
  assert(opened.registry.recreate_count === 1, 'first open creates room and records recreation');
  assert(opened.messages.length === 0, 'new room has no messages');

  const firstMessage = store.postMessage({
    roomSlug: 'team-room',
    senderName: 'Alice',
    body: 'hello'
  });
  assert(firstMessage.ok === true, 'message can be posted');
  assert(firstMessage.message.expires_at === now + MESSAGE_TTL_MS, 'message expires after 24h');
  assert(store.listMessages('team-room').length === 1, 'message is listed before expiry');

  const storedImage = imageStore.storeImage({
    attachmentId: 'img-1',
    fileName: 'hello.png',
    mimeType: 'image/png',
    bytes: Buffer.from([0x89, 0x50, 0x4e, 0x47])
  });
  assert(storedImage.ok === true, 'image attachment can be stored');
  assert(fs.existsSync(storedImage.absolute_path), 'image bytes are stored outside message state');

  const imageMessage = store.postMessage({
    roomSlug: 'team-room',
    senderName: 'Alice',
    body: '',
    attachments: [storedImage.attachment]
  });
  assert(imageMessage.ok === true, 'image-only message can be posted');
  assert(imageMessage.message.attachments.length === 1, 'message stores image attachment metadata');
  assert(!('bytes' in imageMessage.message.attachments[0]), 'message state does not contain raw image bytes');
  assert(imageStore.readImage(storedImage.attachment.storage_key).length === 4, 'image can be read before expiry');

  const badImage = imageStore.storeImage({
    attachmentId: 'bad-1',
    fileName: 'bad.svg',
    mimeType: 'image/svg+xml',
    bytes: Buffer.from('<svg></svg>')
  });
  assert(badImage.ok === false && badImage.error === 'unsupported_image_mime_type', 'unsupported image mime is rejected');

  now += MESSAGE_TTL_MS - 1;
  assert(store.listMessages('team-room').length === 2, 'messages remain just before 24h expiry');

  now += 1;
  assert(store.listMessages('team-room').length === 0, 'message expires at 24h');
  assert(!fs.existsSync(storedImage.absolute_path), 'expired image attachment is removed with message expiry');

  const stateAfterMessageExpiry = store.getRawState();
  assert(Boolean(stateAfterMessageExpiry.rooms['team-room']), 'room remains after message expiry');
  assert(Boolean(stateAfterMessageExpiry.room_registry['team-room']), 'room registry remains after message expiry');

  now += ROOM_INACTIVE_TTL_MS;
  store.cleanup();
  const stateAfterRoomCleanup = store.getRawState();
  assert(!stateAfterRoomCleanup.rooms['team-room'], 'inactive room is removed after 7 days');
  assert(!stateAfterRoomCleanup.messages['team-room'], 'messages bucket is removed with inactive room');
  assert(Boolean(stateAfterRoomCleanup.room_registry['team-room']), 'room registry remains after room cleanup');

  const reopened = store.openRoom('team-room');
  assert(reopened.room.room_slug === 'team-room', 'same URL reopens room after cleanup');
  assert(reopened.registry.recreate_count === 2, 'registry records room recreation');
  assert(reopened.messages.length === 0, 'recreated room starts empty');

  const emptyMessage = store.postMessage({
    roomSlug: 'team-room',
    senderName: 'Alice',
    body: '   '
  });
  assert(emptyMessage.ok === false && emptyMessage.error === 'message_body_required', 'empty messages are rejected');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample40-ephemeral-room-chat-site',
    message_ttl_hours: MESSAGE_TTL_MS / (60 * 60 * 1000),
    room_inactive_ttl_days: ROOM_INACTIVE_TTL_MS / (24 * 60 * 60 * 1000),
    room_recreate_count: reopened.registry.recreate_count,
    image_attachments: imageMessage.message.attachments.length,
    dependency_free: true,
    cut_out_friendly: true
  }, null, 2));
} finally {
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
