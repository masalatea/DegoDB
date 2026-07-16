# Sample 39: Shared-State Chat Demo

This sample shows a small chat-like domain on top of the sample38 shared-state sync reference runtime.

sample38 is the lower-level synchronization reference.
sample39 is a domain sample that uses the same room / membership / revision / event model for chat messages.

## What this sample does

- Loads the sample36 server packet and sample37 client packet fixtures.
- Creates a chat room backed by the sample38 in-memory reference runtime.
- Stores messages as room-scoped shared state.
- Appends messages with `expected_revision`.
- Stores image bytes in an ephemeral local store and syncs only attachment metadata.
- Emits `state.updated` to subscribers in the same room.
- Rejects non-member messages.
- Rejects stale message appends.
- Verifies another room does not receive chat events.
- Verifies emitted chat events contain no SSO token, refresh token, raw invite token, or secret.

## What this sample intentionally does not do

This sample does not install dependencies, open a public port, run a production WebSocket server, persist chat history to a database, implement SSO/OIDC setup, generate client UI, or provide moderation / notification features.

Image attachments are sample-only:

- bytes are written to a temporary local directory;
- the temporary directory is removed after validation;
- sync events contain metadata such as `storage_key`, `mime_type`, `size_bytes`, `width`, and `height`;
- sync events do not contain raw image bytes.

It is a domain-level tutorial showing how a chat-like feature can sit on top of the shared-state sync contract.

## Validate

```bash
node sample/tutorials/sample39-shared-state-chat-demo/scripts/validate-sample.mjs
```
