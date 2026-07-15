# 2026-0716 Shared-State Chat Domain Sample First Slice

## Summary

Added `sample39-shared-state-chat-demo` as a chat-like domain sample on top of the sample38 shared-state sync runtime reference.

## Why this is separate from sample38

sample38 is the lower-level synchronization reference:

- room membership;
- role-based update authority;
- expected revision / stale revision;
- room-scoped event fanout;
- latest fetch;
- secret-free events;
- HTTP/SSE fallback;
- Mtool artifact linkage.

sample39 is a domain sample that uses those mechanics for chat-like message append behavior.

## Covered behavior

The first slice validates:

- room-local message list state;
- member can list messages;
- non-member cannot list or append;
- empty message is rejected;
- editor can append a message;
- viewer cannot append;
- stale append is rejected;
- append increments revision;
- same-room subscribers receive `state.updated`;
- another room receives no event;
- emitted events contain no SSO token, refresh token, raw invite token, or secret.

## Boundary

This is not a production chat application.

It does not:

- install dependencies;
- open a public port;
- run a production WebSocket server;
- persist chat history to a database;
- implement SSO/OIDC setup;
- generate client UI;
- provide moderation, attachment, notification, or presence features.

## Validation

```bash
node sample/tutorials/sample39-shared-state-chat-demo/scripts/validate-sample.mjs
```

This passed for the first slice.

## Next decision

Choose whether to:

- add chat HTTP/SSE route coverage;
- add real WebSocket transport sample;
- add production-hardening checklist;
- add Mtool combined bundle;
- or checkpoint/PR.
