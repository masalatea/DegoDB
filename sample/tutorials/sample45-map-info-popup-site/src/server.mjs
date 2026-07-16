import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const sampleRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const packetPath = path.join(sampleRoot, 'reference/map-info-site-input.sample.json');

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

function readPacket() {
  return JSON.parse(fs.readFileSync(packetPath, 'utf8'));
}

function createServer() {
  return http.createServer((request, response) => {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1');

    if (request.method === 'GET' && (url.pathname === '/' || url.pathname === '/index.html')) {
      send(response, 200, fs.readFileSync(path.join(sampleRoot, 'public/index.html')), 'text/html; charset=utf-8');
      return;
    }

    const staticFile = {
      '/styles.css': ['public/styles.css', 'text/css; charset=utf-8'],
      '/app.js': ['public/app.js', 'text/javascript; charset=utf-8']
    }[url.pathname];
    if (request.method === 'GET' && staticFile) {
      const [relativePath, contentType] = staticFile;
      send(response, 200, fs.readFileSync(path.join(sampleRoot, relativePath)), contentType);
      return;
    }

    if (request.method === 'GET' && url.pathname === '/api/site') {
      const packet = readPacket();
      sendJson(response, 200, {
        site: packet.site,
        map_provider: packet.map_provider,
        marker_schema: packet.marker_schema,
        locations: packet.locations
      });
      return;
    }

    send(response, 404, 'not found');
  });
}

function startServer({
  port = Number(process.env.PORT ?? 8790),
  host = '127.0.0.1',
  dataDir = process.env.SAMPLE45_DATA_DIR ?? path.join(os.tmpdir(), 'sample45-map-info-popup-site')
} = {}) {
  fs.mkdirSync(dataDir, { recursive: true });
  const server = createServer();
  server.listen(port, host, () => {
    console.log(`sample45 listening on http://${host}:${port}/`);
    console.log(`data dir: ${dataDir}`);
  });
  return { server, dataDir };
}

const isCli = process.argv[1] === fileURLToPath(import.meta.url);
if (isCli) {
  startServer();
}

export { createServer, readPacket, startServer };
