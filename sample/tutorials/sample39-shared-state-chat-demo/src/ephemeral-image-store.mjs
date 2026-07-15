import fs from 'node:fs';
import path from 'node:path';

function sanitizeFileName(fileName) {
  return String(fileName ?? 'image.bin').replace(/[^A-Za-z0-9._-]/g, '_');
}

export class EphemeralImageStore {
  constructor({ rootDir, maxBytes = 1024 * 1024 }) {
    this.rootDir = rootDir;
    this.maxBytes = maxBytes;
    fs.mkdirSync(this.rootDir, { recursive: true });
  }

  storeImage({ attachmentId, fileName, mimeType, bytes, width, height }) {
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
        width,
        height,
        storage_key: storageKey
      },
      absolute_path: absolutePath
    };
  }

  readImage(storageKey) {
    return fs.readFileSync(path.join(this.rootDir, sanitizeFileName(storageKey)));
  }

  removeAll() {
    fs.rmSync(this.rootDir, { recursive: true, force: true });
  }
}
