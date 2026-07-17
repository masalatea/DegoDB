import http from 'node:http';
import path from 'node:path';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';
import { createMapPacket } from './map-packet.mjs';

const sampleRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const staticFiles = {
  '/': ['public/index.html', 'text/html; charset=utf-8'],
  '/styles.css': ['public/styles.css', 'text/css; charset=utf-8'],
  '/game.js': ['public/game.js', 'text/javascript; charset=utf-8'],
  '/vendor/three/three.module.js': ['vendor/three/three.module.js', 'text/javascript; charset=utf-8'],
  '/vendor/three/three.core.js': ['vendor/three/three.core.js', 'text/javascript; charset=utf-8']
};

function send(response, statusCode, body, contentType = 'text/plain; charset=utf-8', method = 'GET') {
  response.writeHead(statusCode, {
    'content-type': contentType,
    'cache-control': 'no-store'
  });
  response.end(method === 'HEAD' ? undefined : body);
}

function sendJson(request, response, statusCode, payload) {
  send(response, statusCode, JSON.stringify(payload, null, 2), 'application/json; charset=utf-8', request.method);
}

function createServer() {
  return http.createServer((request, response) => {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1');

    try {
      if ((request.method === 'GET' || request.method === 'HEAD') && url.pathname === '/api/map') {
        const seed = Number.parseInt(url.searchParams.get('seed') ?? '', 10);
        sendJson(request, response, 200, createMapPacket(Number.isInteger(seed) ? { seed } : {}));
        return;
      }

      const staticFile = staticFiles[url.pathname];
      if ((request.method === 'GET' || request.method === 'HEAD') && staticFile) {
        const [relativePath, contentType] = staticFile;
        send(response, 200, fs.readFileSync(path.join(sampleRoot, relativePath)), contentType, request.method);
        return;
      }

      sendJson(request, response, 404, { ok: false, error: 'not_found' });
    } catch (error) {
      sendJson(request, response, 500, { ok: false, error: error.message });
    }
  });
}

function defaultPort() {
  return Number(globalThis.process?.env?.PORT ?? 8892);
}

function startServer({ port = defaultPort(), host = '127.0.0.1' } = {}) {
  const server = createServer();
  server.listen(port, host, () => {
    console.log(`sample52 listening on http://${host}:${port}/`);
    console.log(`map API: http://${host}:${port}/api/map`);
  });
  return server;
}

const isCli = globalThis.process?.argv?.[1] === fileURLToPath(import.meta.url);
if (isCli) startServer();

export { createServer, startServer };
