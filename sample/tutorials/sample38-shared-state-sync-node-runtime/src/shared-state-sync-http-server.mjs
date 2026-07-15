import http from 'node:http';
import { eventContainsForbiddenSecrets } from './shared-state-sync-runtime.mjs';

function jsonResponse(response, statusCode, payload) {
  response.writeHead(statusCode, {
    'content-type': 'application/json; charset=utf-8',
    'cache-control': 'no-store'
  });
  response.end(JSON.stringify(payload));
}

function readBody(request) {
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
        reject(new Error(`invalid JSON body: ${error.message}`));
      }
    });
    request.on('error', reject);
  });
}

function parseStatePath(pathname) {
  const stateMatch = pathname.match(/^\/sync\/rooms\/([^/]+)\/states\/([^/]+)$/);
  if (stateMatch) {
    return {
      roomId: decodeURIComponent(stateMatch[1]),
      stateKey: decodeURIComponent(stateMatch[2]),
      revisionOnly: false
    };
  }

  const revisionMatch = pathname.match(/^\/sync\/rooms\/([^/]+)\/states\/([^/]+)\/revision$/);
  if (revisionMatch) {
    return {
      roomId: decodeURIComponent(revisionMatch[1]),
      stateKey: decodeURIComponent(revisionMatch[2]),
      revisionOnly: true
    };
  }

  return null;
}

function parseEventPath(pathname) {
  const match = pathname.match(/^\/sync\/rooms\/([^/]+)\/events$/);
  if (!match) {
    return null;
  }
  return { roomId: decodeURIComponent(match[1]) };
}

function statusForRuntimeError(error) {
  if (error === 'membership_required') {
    return 403;
  }
  if (error === 'update_forbidden') {
    return 403;
  }
  if (error === 'expected_revision_required') {
    return 400;
  }
  if (error === 'stale_revision') {
    return 409;
  }
  return 400;
}

export function createSharedStateSyncHttpServer({ runtime }) {
  const server = http.createServer(async (request, response) => {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1');
    const userId = request.headers['x-user-id'];
    const statePath = parseStatePath(url.pathname);
    const eventPath = parseEventPath(url.pathname);

    try {
      if (request.method === 'GET' && statePath?.revisionOnly) {
        const result = runtime.latestRevision({
          roomId: statePath.roomId,
          stateKey: statePath.stateKey,
          userId
        });
        if (!result.ok) {
          jsonResponse(response, statusForRuntimeError(result.error), result);
          return;
        }
        jsonResponse(response, 200, result);
        return;
      }

      if (request.method === 'GET' && statePath && !statePath.revisionOnly) {
        const result = runtime.readState({
          roomId: statePath.roomId,
          stateKey: statePath.stateKey,
          userId
        });
        if (!result.ok) {
          jsonResponse(response, statusForRuntimeError(result.error), result);
          return;
        }
        jsonResponse(response, 200, result);
        return;
      }

      if (request.method === 'PUT' && statePath && !statePath.revisionOnly) {
        const body = await readBody(request);
        const result = runtime.updateState({
          roomId: statePath.roomId,
          stateKey: statePath.stateKey,
          userId,
          expectedRevision: body.expected_revision,
          body: body.body
        });
        if (!result.ok) {
          jsonResponse(response, statusForRuntimeError(result.error), result);
          return;
        }
        jsonResponse(response, 200, result);
        return;
      }

      if (request.method === 'GET' && eventPath) {
        const subscribeResult = runtime.subscribe({
          roomId: eventPath.roomId,
          userId,
          onEvent: event => {
            if (eventContainsForbiddenSecrets(event)) {
              response.write(`event: sync.error\ndata: ${JSON.stringify({ error: 'forbidden_secret_in_event' })}\n\n`);
              return;
            }
            response.write(`event: ${event.type}\ndata: ${JSON.stringify(event)}\n\n`);
          }
        });
        if (!subscribeResult.ok) {
          jsonResponse(response, statusForRuntimeError(subscribeResult.error), subscribeResult);
          return;
        }

        response.writeHead(200, {
          'content-type': 'text/event-stream; charset=utf-8',
          'cache-control': 'no-store',
          connection: 'keep-alive'
        });
        response.write(`event: sync.ready\ndata: ${JSON.stringify(subscribeResult)}\n\n`);

        request.on('close', () => {
          runtime.unsubscribe({ roomId: eventPath.roomId, userId });
        });
        return;
      }

      jsonResponse(response, 404, { ok: false, error: 'not_found' });
    } catch (error) {
      jsonResponse(response, 500, { ok: false, error: 'server_error', message: error.message });
    }
  });

  return server;
}
