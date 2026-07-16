import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';
import { RpgRoomStore, normalizeRoomSlug } from './rpg-room-store.mjs';

const sampleRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

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

function createDefaultStore({ dataDir }) {
  return new RpgRoomStore({
    filePath: path.join(dataDir, 'rpg-room-store.json')
  });
}

function createServer({ store, tickMs = null } = {}) {
  const subscribersByRoom = new Map();

  function subscribe(roomSlug, response) {
    const subscribers = subscribersByRoom.get(roomSlug) ?? new Set();
    subscribers.add(response);
    subscribersByRoom.set(roomSlug, subscribers);
    response.writeHead(200, {
      'content-type': 'text/event-stream; charset=utf-8',
      'cache-control': 'no-store',
      connection: 'keep-alive'
    });
    response.write(`event: ready\ndata: ${JSON.stringify({ room_slug: roomSlug })}\n\n`);
    return () => {
      subscribers.delete(response);
      if (subscribers.size === 0) {
        subscribersByRoom.delete(roomSlug);
      }
    };
  }

  function publish(roomSlug, state) {
    const subscribers = subscribersByRoom.get(roomSlug);
    if (!subscribers) {
      return;
    }
    const event = `event: rpg.updated\ndata: ${JSON.stringify({ room_slug: roomSlug, revision: state.revision })}\n\n`;
    for (const response of subscribers) {
      response.write(event);
    }
  }

  const server = http.createServer(async (request, response) => {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1');
    try {
      if (request.method === 'GET' && url.pathname.startsWith('/r/')) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(url.pathname.slice('/r/'.length)));
        store.openRoom(roomSlug);
        const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
        const roomScript = `<script>globalThis.SAMPLE47_ROOM_SLUG = ${JSON.stringify(roomSlug)};</script>`;
        send(response, 200, html.replace('<script type="module" src="/game.js"></script>', `${roomScript}\n  <script type="module" src="/game.js"></script>`), 'text/html; charset=utf-8');
        return;
      }

      const staticFile = {
        '/styles.css': ['public/styles.css', 'text/css; charset=utf-8'],
        '/game.js': ['public/game.js', 'text/javascript; charset=utf-8']
      }[url.pathname];
      if (request.method === 'GET' && staticFile) {
        const [relativePath, contentType] = staticFile;
        send(response, 200, fs.readFileSync(path.join(sampleRoot, relativePath)), contentType);
        return;
      }

      const stateMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/state$/);
      if (request.method === 'GET' && stateMatch) {
        sendJson(response, 200, store.getState(decodeURIComponent(stateMatch[1])));
        return;
      }

      const joinMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/join$/);
      if (request.method === 'POST' && joinMatch) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(joinMatch[1]));
        const body = await readJson(request);
        const result = store.joinRoom(roomSlug, body.name);
        if (result.ok) {
          publish(roomSlug, result.state);
        }
        sendJson(response, 200, result);
        return;
      }

      const commandMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/commands$/);
      if (request.method === 'POST' && commandMatch) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(commandMatch[1]));
        const body = await readJson(request);
        const result = store.applyCommand({
          roomSlug,
          playerId: body.player_id,
          command: body.command
        });
        if (result.ok) {
          publish(roomSlug, result.state);
        }
        sendJson(response, result.ok ? 200 : 400, result);
        return;
      }

      const eventsMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/events$/);
      if (request.method === 'GET' && eventsMatch) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(eventsMatch[1]));
        store.openRoom(roomSlug);
        const unsubscribe = subscribe(roomSlug, response);
        request.on('close', unsubscribe);
        return;
      }

      send(response, 404, 'not found');
    } catch (error) {
      sendJson(response, 500, { ok: false, error: error.message });
    }
  });

  if (tickMs !== null) {
    const timer = setInterval(() => {
      for (const update of store.advanceActiveRooms()) {
        publish(update.room_slug, update.state);
      }
    }, tickMs);
    timer.unref?.();
    server.on('close', () => clearInterval(timer));
  }

  return server;
}

function startServer({
  port = Number(process.env.PORT ?? 8790),
  host = '127.0.0.1',
  dataDir = process.env.SAMPLE47_DATA_DIR ?? path.join(os.tmpdir(), 'sample47-open-world-rpg-demo')
} = {}) {
  const store = createDefaultStore({ dataDir });
  const server = createServer({ store, tickMs: 180 });
  server.listen(port, host, () => {
    console.log(`sample47 listening on http://${host}:${port}/r/general`);
    console.log(`data dir: ${dataDir}`);
  });
  return { server, store, dataDir };
}

const isCli = process.argv[1] === fileURLToPath(import.meta.url);
if (isCli) {
  startServer();
}

export { createDefaultStore, createServer, startServer };
