import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { once } from 'node:events';
import { createDefaultStores, createServer } from '../src/server.mjs';

const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample40-http-routes-'));
const { store, imageStore, storeDriver } = createDefaultStores({ dataDir: tempRoot });
const server = createServer({ store, imageStore });

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

try {
  const baseUrl = await listen();
  assert.equal(storeDriver, 'sqlite', 'HTTP route validation uses SQLite store by default');
  assert.equal(fs.existsSync(path.join(tempRoot, 'chat-store.sqlite')), true, 'default SQLite store file is created');

  const roomPage = await fetch(`${baseUrl}/r/Team%20Room!!`);
  assert.equal(roomPage.status, 200, 'room page loads');
  assert.match(await roomPage.text(), /#team-room/, 'room page shows normalized slug');

  const opened = await jsonFetch(baseUrl, '/api/rooms/Team%20Room!!', { method: 'POST' });
  assert.equal(opened.response.status, 200, 'room open API succeeds');
  assert.equal(opened.payload.room.room_slug, 'team-room', 'room open API normalizes slug');

  const firstMessage = await jsonFetch(baseUrl, '/api/rooms/team-room/messages', {
    method: 'POST',
    body: {
      sender_name: 'Alice',
      body: 'hello through http'
    }
  });
  assert.equal(firstMessage.response.status, 200, 'message post API succeeds');
  assert.equal(firstMessage.payload.message.body, 'hello through http', 'message body is stored through API');

  const imageUpload = await jsonFetch(baseUrl, '/api/rooms/team-room/images', {
    method: 'POST',
    body: {
      file_name: 'tiny.png',
      mime_type: 'image/png',
      data_base64: Buffer.from([0x89, 0x50, 0x4e, 0x47]).toString('base64')
    }
  });
  assert.equal(imageUpload.response.status, 200, 'image upload API succeeds');
  assert.equal(imageUpload.payload.attachment.type, 'image', 'image upload returns attachment metadata');
  assert.equal(imageUpload.payload.attachment.size_bytes, 4, 'image upload stores bytes outside message state');

  const imageMessage = await jsonFetch(baseUrl, '/api/rooms/team-room/messages', {
    method: 'POST',
    body: {
      sender_name: 'Alice',
      body: '',
      attachments: [imageUpload.payload.attachment]
    }
  });
  assert.equal(imageMessage.response.status, 200, 'image-only message post API succeeds');
  assert.equal(imageMessage.payload.message.attachments.length, 1, 'image-only message stores attachment metadata');
  assert.equal('bytes' in imageMessage.payload.message.attachments[0], false, 'message API does not expose raw bytes');

  const messages = await jsonFetch(baseUrl, '/api/rooms/team-room/messages');
  assert.equal(messages.response.status, 200, 'message list API succeeds');
  assert.equal(messages.payload.length, 2, 'message list API returns text and image messages');

  const attachmentResponse = await fetch(`${baseUrl}/attachments/${encodeURIComponent(imageUpload.payload.attachment.storage_key)}`);
  assert.equal(attachmentResponse.status, 200, 'attachment route serves uploaded image');
  assert.equal(attachmentResponse.headers.get('content-type'), 'image/png', 'attachment route preserves content type');
  assert.deepEqual(
    Array.from(new Uint8Array(await attachmentResponse.arrayBuffer())),
    [0x89, 0x50, 0x4e, 0x47],
    'attachment route serves stored bytes'
  );

  const badImage = await jsonFetch(baseUrl, '/api/rooms/team-room/images', {
    method: 'POST',
    body: {
      file_name: 'bad.svg',
      mime_type: 'image/svg+xml',
      data_base64: Buffer.from('<svg></svg>').toString('base64')
    }
  });
  assert.equal(badImage.response.status, 400, 'unsupported image mime is rejected through API');
  assert.equal(badImage.payload.error, 'unsupported_image_mime_type', 'unsupported image API returns stable error');

  const emptyMessage = await jsonFetch(baseUrl, '/api/rooms/team-room/messages', {
    method: 'POST',
    body: {
      sender_name: 'Alice',
      body: ''
    }
  });
  assert.equal(emptyMessage.response.status, 400, 'empty message without attachment is rejected through API');
  assert.equal(emptyMessage.payload.error, 'message_body_required', 'empty message API returns stable error');

  const cleanup = await jsonFetch(baseUrl, '/api/cleanup', { method: 'POST' });
  assert.equal(cleanup.response.status, 200, 'cleanup API succeeds');
  assert.ok(cleanup.payload.rooms['team-room'], 'cleanup keeps active room');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample40-ephemeral-room-chat-site',
    validator: 'http-routes',
    checked_routes: [
      'GET /r/:roomSlug',
      'POST /api/rooms/:roomSlug',
      'GET /api/rooms/:roomSlug/messages',
      'POST /api/rooms/:roomSlug/messages',
      'POST /api/rooms/:roomSlug/images',
      'GET /attachments/:storageKey',
      'POST /api/cleanup'
    ],
    store_driver: storeDriver,
    messages: messages.payload.length,
    image_attachments: imageMessage.payload.message.attachments.length
  }, null, 2));
} finally {
  await close();
  if (typeof store.close === 'function') {
    store.close();
  }
  fs.rmSync(tempRoot, { recursive: true, force: true });
}
