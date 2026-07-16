import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { WhiteboardRoomStore, normalizeRoomSlug } from './whiteboard-room-store.mjs';

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
  return new WhiteboardRoomStore({
    filePath: path.join(dataDir, 'whiteboard-room-store.json')
  });
}

function createServer({ store }) {
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

  function publish(roomSlug, eventName, payload) {
    const subscribers = subscribersByRoom.get(roomSlug);
    if (!subscribers) {
      return;
    }
    const event = `event: ${eventName}\ndata: ${JSON.stringify(payload)}\n\n`;
    for (const response of subscribers) {
      response.write(event);
    }
  }

  return http.createServer(async (request, response) => {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1');
    try {
      if (request.method === 'GET' && url.pathname.startsWith('/r/')) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(url.pathname.slice('/r/'.length)));
        store.openRoom(roomSlug);
        const html = await import('node:fs').then(fs => fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8'));
        const roomScript = `<script>globalThis.SAMPLE41_ROOM_SLUG = ${JSON.stringify(roomSlug)};</script>`;
        send(
          response,
          200,
          html
            .replace('Simple Whiteboard', `Simple Whiteboard: ${roomSlug}`)
            .replace('<script type="module" src="./whiteboard.js"></script>', `${roomScript}\n  <script type="module" src="/whiteboard.js"></script>`),
          'text/html; charset=utf-8'
        );
        return;
      }

      const staticFile = {
        '/styles.css': ['public/styles.css', 'text/css; charset=utf-8'],
        '/whiteboard.js': ['public/whiteboard.js', 'text/javascript; charset=utf-8']
      }[url.pathname];
      if (request.method === 'GET' && staticFile) {
        const [relativePath, contentType] = staticFile;
        const content = await import('node:fs').then(fs => fs.readFileSync(path.join(sampleRoot, relativePath)));
        send(response, 200, content, contentType);
        return;
      }

      const roomApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)$/);
      if (request.method === 'POST' && roomApiMatch) {
        sendJson(response, 200, store.openRoom(decodeURIComponent(roomApiMatch[1])));
        return;
      }

      const boardApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/board$/);
      if (request.method === 'GET' && boardApiMatch) {
        sendJson(response, 200, store.fetchBoard(decodeURIComponent(boardApiMatch[1])));
        return;
      }

      const eventsApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/events$/);
      if (request.method === 'GET' && eventsApiMatch) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(eventsApiMatch[1]));
        store.openRoom(roomSlug);
        const unsubscribe = subscribe(roomSlug, response);
        request.on('close', unsubscribe);
        return;
      }

      const operationsApiMatch = url.pathname.match(/^\/api\/rooms\/([^/]+)\/operations$/);
      if (request.method === 'POST' && operationsApiMatch) {
        const roomSlug = normalizeRoomSlug(decodeURIComponent(operationsApiMatch[1]));
        const body = await readJson(request);
        const result = store.appendOperation({
          roomSlug,
          expectedRevision: body.expected_revision,
          operation: body.operation
        });
        if (result.ok) {
          publish(roomSlug, 'board.updated', {
            room_slug: roomSlug,
            revision: result.board.revision,
            operation_id: result.operation.operation_id
          });
        }
        sendJson(response, result.ok ? 200 : 409, result);
        return;
      }

      if (request.method === 'POST' && url.pathname === '/api/cleanup') {
        const state = store.clearInactiveBoards();
        for (const [roomSlug, board] of Object.entries(state.boards)) {
          if (board.cleared_at !== null) {
            publish(roomSlug, 'board.updated', {
              room_slug: roomSlug,
              revision: board.revision,
              cleared: true
            });
          }
        }
        sendJson(response, 200, state);
        return;
      }

      send(response, 404, 'not found');
    } catch (error) {
      sendJson(response, 500, { ok: false, error: error.message });
    }
  });
}

function startServer({
  port = Number(process.env.PORT ?? 8788),
  host = '127.0.0.1',
  dataDir = process.env.SAMPLE41_DATA_DIR ?? path.join(os.tmpdir(), 'sample41-simple-whiteboard')
} = {}) {
  const store = createDefaultStore({ dataDir });
  const server = createServer({ store });
  server.listen(port, host, () => {
    console.log(`sample41 listening on http://${host}:${port}/r/general`);
    console.log(`data dir: ${dataDir}`);
  });
  return { server, store, dataDir };
}

const isCli = process.argv[1] === fileURLToPath(import.meta.url);
if (isCli) {
  startServer();
}

export { createDefaultStore, createServer, startServer };
