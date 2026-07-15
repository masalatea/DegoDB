import { EventEmitter } from 'node:events';

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function forbiddenPayloadKeys() {
  return new Set(['sso_token', 'refresh_token', 'raw_invite_token', 'secret']);
}

function assertPacketShape(serverPacket, clientPacket) {
  if (serverPacket?.schema_version !== 'shared_state_sync_server_input.v1') {
    throw new Error('server packet schema_version mismatch');
  }
  if (clientPacket?.schema_version !== 'shared_state_sync_client_input.v1') {
    throw new Error('client packet schema_version mismatch');
  }
  if (serverPacket.server?.runtime !== 'nodejs') {
    throw new Error('server runtime must be nodejs');
  }
  if (serverPacket.server?.production_runtime_generated !== false) {
    throw new Error('sample must not claim production runtime generation');
  }
  if (clientPacket.client?.source_generation !== false || clientPacket.client?.sdk_generation !== false) {
    throw new Error('sample must not claim client source or SDK generation');
  }
  if (!serverPacket.routes?.websocket?.commands?.includes('state.update')) {
    throw new Error('server packet must declare state.update command');
  }
  if (clientPacket.realtime_flow?.primary_transport !== 'websocket') {
    throw new Error('client packet must identify websocket as primary transport');
  }
}

export class SharedStateSyncRuntime {
  constructor({ serverPacket, clientPacket }) {
    assertPacketShape(serverPacket, clientPacket);

    this.serverPacket = serverPacket;
    this.clientPacket = clientPacket;
    this.events = new EventEmitter();
    this.rooms = new Map();
    this.auditEvents = [];
  }

  createRoom({ roomId, initialState = {}, members = {} }) {
    if (this.rooms.has(roomId)) {
      throw new Error(`room already exists: ${roomId}`);
    }

    const state = new Map();
    for (const [stateKey, body] of Object.entries(initialState)) {
      state.set(stateKey, {
        body: clone(body),
        revision: 1
      });
    }

    this.rooms.set(roomId, {
      members: new Map(Object.entries(members)),
      state,
      subscribers: new Map()
    });
  }

  subscribe({ roomId, userId, onEvent }) {
    const room = this.requireRoom(roomId);
    const role = room.members.get(userId);
    if (!role) {
      return { ok: false, error: 'membership_required' };
    }

    const handler = event => {
      if (event.room_id === roomId) {
        onEvent(clone(event));
      }
    };
    const channel = `room:${roomId}`;
    this.events.on(channel, handler);
    room.subscribers.set(userId, { channel, handler });

    return {
      ok: true,
      role,
      latest_revision_summary: this.latestRevisionSummary(room)
    };
  }

  unsubscribe({ roomId, userId }) {
    const room = this.requireRoom(roomId);
    const subscription = room.subscribers.get(userId);
    if (!subscription) {
      return { ok: true, unsubscribed: false };
    }
    this.events.off(subscription.channel, subscription.handler);
    room.subscribers.delete(userId);
    return { ok: true, unsubscribed: true };
  }

  updateState({ roomId, stateKey, userId, expectedRevision, body }) {
    const room = this.requireRoom(roomId);
    const role = room.members.get(userId);
    if (!role) {
      return { ok: false, error: 'membership_required' };
    }
    if (role !== 'editor' && role !== 'owner') {
      return { ok: false, error: 'update_forbidden' };
    }
    if (!Number.isInteger(expectedRevision)) {
      return { ok: false, error: 'expected_revision_required' };
    }

    const current = room.state.get(stateKey) ?? { body: null, revision: 0 };
    if (current.revision !== expectedRevision) {
      return {
        ok: false,
        error: 'stale_revision',
        latest: this.getState({ roomId, stateKey })
      };
    }

    const next = {
      body: clone(body),
      revision: current.revision + 1
    };
    room.state.set(stateKey, next);

    const event = this.buildEvent({
      type: 'state.updated',
      roomId,
      stateKey,
      revision: next.revision,
      body: next.body,
      updatedBy: userId
    });

    this.auditEvents.push(event);
    this.events.emit(`room:${roomId}`, event);

    return { ok: true, state: clone(next), event: clone(event) };
  }

  getMembership({ roomId, userId }) {
    const room = this.requireRoom(roomId);
    return room.members.get(userId) ?? null;
  }

  readState({ roomId, stateKey, userId }) {
    const role = this.getMembership({ roomId, userId });
    if (!role) {
      return { ok: false, error: 'membership_required' };
    }
    return { ok: true, role, state: this.getState({ roomId, stateKey }) };
  }

  getState({ roomId, stateKey }) {
    const room = this.requireRoom(roomId);
    const current = room.state.get(stateKey);
    if (!current) {
      return { body: null, revision: 0 };
    }
    return clone(current);
  }

  latestRevision({ roomId, stateKey, userId = null }) {
    if (userId !== null && !this.getMembership({ roomId, userId })) {
      return { ok: false, error: 'membership_required' };
    }
    const revision = this.getState({ roomId, stateKey }).revision;
    if (userId !== null) {
      return { ok: true, revision };
    }
    return revision;
  }

  reconnectLatestFetch({ roomId, stateKey, knownRevision }) {
    const latest = this.getState({ roomId, stateKey });
    return {
      changed: latest.revision !== knownRevision,
      latest
    };
  }

  requireRoom(roomId) {
    const room = this.rooms.get(roomId);
    if (!room) {
      throw new Error(`unknown room: ${roomId}`);
    }
    return room;
  }

  latestRevisionSummary(room) {
    return [...room.state.entries()].map(([stateKey, state]) => ({
      state_key: stateKey,
      revision: state.revision
    }));
  }

  buildEvent({ type, roomId, stateKey, revision, body, updatedBy }) {
    return {
      envelope: 'shared_state_sync_realtime_event.v1',
      type,
      room_id: roomId,
      state_key: stateKey,
      revision,
      body: clone(body),
      updated_by: updatedBy,
      delivery: 'best_effort_realtime_plus_latest_fetch'
    };
  }
}

export function eventContainsForbiddenSecrets(event) {
  const forbidden = forbiddenPayloadKeys();

  function visit(value) {
    if (!value || typeof value !== 'object') {
      return false;
    }
    for (const [key, nested] of Object.entries(value)) {
      if (forbidden.has(key)) {
        return true;
      }
      if (visit(nested)) {
        return true;
      }
    }
    return false;
  }

  return visit(event);
}
