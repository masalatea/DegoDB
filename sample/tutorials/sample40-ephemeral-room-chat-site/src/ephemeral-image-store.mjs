import fs from 'node:fs';
import path from 'node:path';

const SUPPORTED_IMAGE_MIME_TYPES = new Set([
  'image/png',
  'image/jpeg',
  'image/webp',
  'image/gif'
]);

function sanitizeFileName(fileName) {
  return String(fileName ?? 'image.bin').replace(/[^A-Za-z0-9._-]/g, '_');
}

function isSupportedImageMimeType(mimeType) {
  return SUPPORTED_IMAGE_MIME_TYPES.has(String(mimeType));
}

export class EphemeralImageStore {
  constructor({ rootDir, maxBytes = 1024 * 1024 }) {
    this.rootDir = rootDir;
    this.maxBytes = maxBytes;
    fs.mkdirSync(this.rootDir, { recursive: true });
  }

  storeImage({ attachmentId, fileName, mimeType, bytes }) {
    if (!isSupportedImageMimeType(mimeType)) {
      return { ok: false, error: 'unsupported_image_mime_type' };
    }

    const buffer = Buffer.from(bytes);
    if (buffer.length === 0) {
      return { ok: false, error: 'image_bytes_required' };
    }
    if (buffer.length > this.maxBytes) {
      return { ok: false, error: 'image_too_large', max_bytes: this.maxBytes };
    }

    const safeAttachmentId = sanitizeFileName(attachmentId);
    const safeFileName = sanitizeFileName(fileName);
    const storageKey = `${safeAttachmentId}-${safeFileName}`;
    const absolutePath = path.join(this.rootDir, storageKey);
    fs.writeFileSync(absolutePath, buffer);

    return {
      ok: true,
      attachment: {
        attachment_id: attachmentId,
        type: 'image',
        file_name: fileName,
        mime_type: mimeType,
        size_bytes: buffer.length,
        storage_key: storageKey
      },
      absolute_path: absolutePath
    };
  }

  readImage(storageKey) {
    return fs.readFileSync(path.join(this.rootDir, sanitizeFileName(storageKey)));
  }

  imagePath(storageKey) {
    return path.join(this.rootDir, sanitizeFileName(storageKey));
  }

  removeImage(storageKey) {
    fs.rmSync(this.imagePath(storageKey), { force: true });
  }

  removeMessageAttachments(message) {
    for (const attachment of message.attachments ?? []) {
      if (attachment.type === 'image' && attachment.storage_key) {
        this.removeImage(attachment.storage_key);
      }
    }
  }
}

export { isSupportedImageMimeType };
