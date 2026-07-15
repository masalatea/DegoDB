# 2026-0716 Shared-State Chat Image Attachment First Slice

## Summary

Extended `sample39-shared-state-chat-demo` with sample-only image attachment metadata.

The image bytes are stored in an ephemeral local directory. Chat shared-state events carry metadata only.

## Why this shape

For a sample, Docker/container-local temporary storage is enough. It keeps the sample concrete without introducing production storage, signed URLs, durable object storage, virus scanning, CDN, or mobile upload flows.

The sync layer should not fan out image binaries.

## Covered behavior

- Store image bytes in an ephemeral local store.
- Append a chat message with image attachment metadata.
- Include metadata fields such as `attachment_id`, `mime_type`, `size_bytes`, `width`, `height`, and `storage_key`.
- Keep raw image bytes out of the shared-state event payload.
- Reject unsupported image MIME types.
- Reject oversized image bytes.
- Remove the temporary image store after validation.

## Boundary

This is not production media handling.

It does not:

- persist images after container/sample exit;
- upload to object storage;
- create signed URLs;
- implement image transformation;
- implement virus scanning;
- sync image binary payloads through WebSocket/SSE events;
- implement moderation, attachments UI, notification, or presence.

## Validation

```bash
node sample/tutorials/sample39-shared-state-chat-demo/scripts/validate-sample.mjs
```

This passed for the first slice.

## Next decision

Choose whether to:

- add chat HTTP/SSE route coverage;
- add real WebSocket transport sample;
- add a production-hardening checklist;
- add a Mtool combined bundle;
- or checkpoint/PR before widening scope.
