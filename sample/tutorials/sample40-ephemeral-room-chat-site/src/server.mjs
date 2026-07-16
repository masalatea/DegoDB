import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { EphemeralRoomChatStore as JsonRoomChatStore, normalizeRoomSlug } from './ephemeral-room-chat-store.mjs';
import { EphemeralImageStore } from './ephemeral-image-store.mjs';
import { SqliteRoomChatStore } from './sqlite-room-chat-store.mjs';

function send(response, statusCode, body, contentType = 'text/plain; charset=utf-8') {
  response.writeHead(statusCode, {
    'content-type': contentType,
    'cache-control': 'no-store'
  });
  response.end(body);
}

function sendJson(response, statusCode, payload) {
  send(response, statusCode, JSON.stringify(payload), 'application/json; charset=utf-8');
}

function readJson(request) {
  return new Promise((resolve, reject) => {
    let body = '';
    request.setEncoding('utf8');
    request.on('data', chunk => {
      body += chunk;
    });
    request.on('end', () => {
      if (body === '') {
        resolve({});
        return;
      }
      try {
        resolve(JSON.parse(body));
      } catch (error) {
        reject(error);
      }
    });
    request.on('error', reject);
  });
}

function page(roomSlug) {
  const safeRoom = normalizeRoomSlug(roomSlug);
  return `<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ephemeral Room Chat: ${safeRoom}</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 760px; margin: 2rem auto; padding: 0 1rem; }
    textarea { width: 100%; min-height: 5rem; }
    li { margin: 0.5rem 0; }
    img { display: block; max-width: min(100%, 360px); margin-top: 0.35rem; border-radius: 0.5rem; }
  </style>
</head>
<body>
  <h1>#${safeRoom}</h1>
  <p>Messages expire after 24 hours. Rooms expire after 7 inactive days and are recreated when opened again.</p>
  <ul id="messages"></ul>
  <input id="sender" placeholder="name" value="anonymous">
  <textarea id="body" placeholder="message"></textarea>
  <input id="image" type="file" accept="image/png,image/jpeg,image/webp,image/gif">
  <button id="send">Send</button>
  <script>
    const room = ${JSON.stringify(safeRoom)};
    function escapeHtml(value) {
      return String(value).replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[char]));
    }
    function renderMessage(m) {
      const attachments = (m.attachments || []).map(a =>
        '<img alt="' + escapeHtml(a.file_name) + '" src="/attachments/' + encodeURIComponent(a.storage_key) + '">'
      ).join('');
      return '<li><strong>' + escapeHtml(m.sender_name) + '</strong>: ' + escapeHtml(m.body || '') + attachments + '</li>';
    }
    async function fileToBase64(file) {
      const bytes = new Uint8Array(await file.arrayBuffer());
      let binary = '';
      for (const byte of bytes) {
        binary += String.fromCharCode(byte);
      }
      return btoa(binary);
    }
    async function load() {
      await fetch('/api/rooms/' + room, { method: 'POST' });
      const messages = await fetch('/api/rooms/' + room + '/messages').then(r => r.json());
      document.querySelector('#messages').innerHTML = messages.map(renderMessage).join('');
    }
    document.querySelector('#send').addEventListener('click', async () => {
      const imageInput = document.querySelector('#image');
      const attachments = [];
      if (imageInput.files.length > 0) {
        const file = imageInput.files[0];
        const upload = await fetch('/api/rooms/' + room + '/images', {
          method: 'POST',
          headers: { 'content-type': 'application/json' },
          body: JSON.stringify({
            file_name: file.name,
            mime_type: file.type,
            data_base64: await fileToBase64(file)
          })
        }).then(r => r.json());
        if (!upload.ok) {
          alert(upload.error);
          return;
        }
        attachments.push(upload.attachment);
      }
      await fetch('/api/rooms/' + room + '/messages', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          sender_name: document.querySelector('#sender').value,
          body: document.querySelector('#body').value,
          attachments
        })
      });
      document.querySelector('#body').value = '';
      imageInput.value = '';
      await load();
    });
    load();
  </script>
</body>
</html>`;
}

function createDefaultStores({
  dataDir,
  storeDriver = process.env.SAMPLE40_STORE_DRIVER ?? 'sqlite'
}) {
  const imageStore = new EphemeralImageStore({ rootDir: path.join(dataDir, 'images') });
  if (storeDriver === 'json') {
    const store = new JsonRoomChatStore({
      filePath: path.join(dataDir, 'chat-store.json'),
      imageStore
    });
    return { store, imageStore, storeDriver };
  }
  if (storeDriver !== 'sqlite') {
    throw new Error(`Unsupported SAMPLE40_STORE_DRIVER: ${storeDriver}`);
  }
  const store = new SqliteRoomChatStore({
    filePath: path.join(dataDir, 'chat-store.sqlite'),
    imageStore
  });
  return { store, imageStore, storeDriver };
}

function createServer({ store, imageStore }) {
  return http.createServer(async (request, response) => {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1');

    try {
      if (request.method === 'GET' && url.pathname.startsWith('/r/')) {
        const roomSlug = decodeURIComponent(url.pathname.slice('/r/'.length));
        store.openRoom(roomSlug);
        send(response, 200, page(roomSlug), 'text/html; charset=utf-8');
        return;
      }

      const roomApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)$/);
      if (request.method === 'POST' && roomApiMatch) {
        const opened = store.openRoom(decodeURIComponent(roomApiMatch[1]));
        sendJson(response, 200, opened);
        return;
      }

      const messagesApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/messages$/);
      if (request.method === 'GET' && messagesApiMatch) {
        sendJson(response, 200, store.listMessages(decodeURIComponent(messagesApiMatch[1])));
        return;
      }
      if (request.method === 'POST' && messagesApiMatch) {
        const body = await readJson(request);
        const result = store.postMessage({
          roomSlug: decodeURIComponent(messagesApiMatch[1]),
          senderName: body.sender_name,
          body: body.body,
          attachments: body.attachments ?? []
        });
        sendJson(response, result.ok ? 200 : 400, result);
        return;
      }

      const imagesApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/images$/);
      if (request.method === 'POST' && imagesApiMatch) {
        store.openRoom(decodeURIComponent(imagesApiMatch[1]));
        const body = await readJson(request);
        const result = imageStore.storeImage({
          attachmentId: crypto.randomUUID(),
          fileName: body.file_name ?? 'image.bin',
          mimeType: body.mime_type,
          bytes: Buffer.from(String(body.data_base64 ?? ''), 'base64')
        });
        sendJson(response, result.ok ? 200 : 400, result);
        return;
      }

      const attachmentMatch = url.pathname.match(/^\/attachments\/([^/]+)$/);
      if (request.method === 'GET' && attachmentMatch) {
        const storageKey = decodeURIComponent(attachmentMatch[1]);
        const state = store.getRawState();
        const attachment = Object.values(state.messages)
          .flat()
          .flatMap(message => message.attachments ?? [])
          .find(item => item.storage_key === storageKey);
        if (!attachment) {
          send(response, 404, 'not found');
          return;
        }
        send(response, 200, imageStore.readImage(storageKey), attachment.mime_type);
        return;
      }

      if (request.method === 'POST' && url.pathname === '/api/cleanup') {
        sendJson(response, 200, store.cleanup());
        return;
      }

      send(response, 404, 'not found');
    } catch (error) {
      sendJson(response, 500, { ok: false, error: error.message });
    }
  });
}

function startServer({
  port = Number(process.env.PORT ?? 8787),
  host = '127.0.0.1',
  dataDir = process.env.SAMPLE40_DATA_DIR ?? path.join(os.tmpdir(), 'sample40-ephemeral-room-chat-site'),
  storeDriver = process.env.SAMPLE40_STORE_DRIVER ?? 'sqlite'
} = {}) {
  const stores = createDefaultStores({ dataDir, storeDriver });
  const server = createServer(stores);
  server.listen(port, host, () => {
    console.log(`sample40 listening on http://${host}:${port}/r/general`);
    console.log(`data dir: ${dataDir}`);
    console.log(`store driver: ${stores.storeDriver}`);
  });
  return { server, ...stores, dataDir };
}

const isCli = process.argv[1] === fileURLToPath(import.meta.url);
if (isCli) {
  startServer();
}

export { createDefaultStores, createServer, startServer };
