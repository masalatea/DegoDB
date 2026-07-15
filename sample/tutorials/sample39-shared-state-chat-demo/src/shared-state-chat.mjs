import { SharedStateSyncRuntime, eventContainsForbiddenSecrets } from '../../sample38-shared-state-sync-node-runtime/src/shared-state-sync-runtime.mjs';

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function sanitizeMessageText(text) {
  return String(text ?? '').trim();
}

function isAllowedImageMimeType(mimeType) {
  return ['image/png', 'image/jpeg', 'image/webp', 'image/gif'].includes(String(mimeType));
}

function normalizeAttachments(attachments) {
  return attachments.map(attachment => ({
    attachment_id: attachment.attachment_id,
    type: 'image',
    file_name: attachment.file_name,
    mime_type: attachment.mime_type,
    size_bytes: attachment.size_bytes,
    width: attachment.width,
    height: attachment.height,
    storage_key: attachment.storage_key
  }));
}

export class SharedStateChatDemo {
  constructor({ serverPacket, clientPacket, imageStore = null }) {
    this.runtime = new SharedStateSyncRuntime({ serverPacket, clientPacket });
    this.imageStore = imageStore;
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

  appendMessage({ roomId, userId, expectedRevision, messageId, text, attachments = [] }) {
    const cleanText = sanitizeMessageText(text);
    if (cleanText === '' && attachments.length === 0) {
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
        text: cleanText,
        attachments: normalizeAttachments(attachments)
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

  storeImageAttachment({ attachmentId, fileName, mimeType, bytes, width, height }) {
    if (!this.imageStore) {
      return { ok: false, error: 'image_store_not_configured' };
    }
    if (!isAllowedImageMimeType(mimeType)) {
      return { ok: false, error: 'unsupported_image_mime_type' };
    }
    return this.imageStore.storeImage({
      attachmentId,
      fileName,
      mimeType,
      bytes,
      width,
      height
    });
  }

  hasForbiddenEventPayloads() {
    return this.runtime.auditEvents.some(event => eventContainsForbiddenSecrets(event));
  }
}
