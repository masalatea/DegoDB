import { SharedStateSyncRuntime, eventContainsForbiddenSecrets } from '../../sample38-shared-state-sync-node-runtime/src/shared-state-sync-runtime.mjs';

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function sanitizeMessageText(text) {
  return String(text ?? '').trim();
}

export class SharedStateChatDemo {
  constructor({ serverPacket, clientPacket }) {
    this.runtime = new SharedStateSyncRuntime({ serverPacket, clientPacket });
  }

  createChatRoom({ roomId, members = {}, initialMessages = [] }) {
    this.runtime.createRoom({
      roomId,
      members,
      initialState: {
        messages: {
          messages: initialMessages.map(message => clone(message))
        }
      }
    });
  }

  subscribe({ roomId, userId, onMessageState }) {
    return this.runtime.subscribe({
      roomId,
      userId,
      onEvent: event => {
        if (event.type === 'state.updated' && event.state_key === 'messages') {
          onMessageState(clone(event));
        }
      }
    });
  }

  listMessages({ roomId, userId }) {
    const result = this.runtime.readState({ roomId, stateKey: 'messages', userId });
    if (!result.ok) {
      return result;
    }
    return {
      ok: true,
      revision: result.state.revision,
      messages: result.state.body.messages
    };
  }

  appendMessage({ roomId, userId, expectedRevision, messageId, text }) {
    const cleanText = sanitizeMessageText(text);
    if (cleanText === '') {
      return { ok: false, error: 'message_text_required' };
    }

    const current = this.runtime.readState({ roomId, stateKey: 'messages', userId });
    if (!current.ok) {
      return current;
    }

    const existingMessages = current.state.body.messages ?? [];
    const nextMessages = [
      ...existingMessages,
      {
        id: messageId,
        user_id: userId,
        text: cleanText
      }
    ];

    const update = this.runtime.updateState({
      roomId,
      stateKey: 'messages',
      userId,
      expectedRevision,
      body: {
        messages: nextMessages
      }
    });

    if (!update.ok) {
      return update;
    }

    return {
      ok: true,
      revision: update.state.revision,
      messages: update.state.body.messages,
      event: update.event
    };
  }

  latestRevision({ roomId, userId }) {
    return this.runtime.latestRevision({ roomId, stateKey: 'messages', userId });
  }

  hasForbiddenEventPayloads() {
    return this.runtime.auditEvents.some(event => eventContainsForbiddenSecrets(event));
  }
}
