# 2026-0716 Shared-State Sync Runtime Sample Promotion

## Summary

After post-RSS cleanup, the next product scope was selected as Shared-state sync runtime integration.

The selected first slice is a Node.js sample, not production runtime generation.

## Reasoning

AI-assisted artifact execution packets are useful, but users and AI agents can often handle that route from existing task packet documentation.
Shared-state sync runtime is more structural: without a concrete runtime-shaped sample, every app creator has to rediscover room membership, revision conflict, event fanout, latest fetch, and secret-free event boundaries.

## Scope for the first slice

Create `sample38-shared-state-sync-node-runtime` as a dependency-free Node.js reference sample that consumes the existing sample36/server and sample37/client packets.

The first slice should validate:

- authenticated member can subscribe;
- non-member cannot subscribe;
- editor can update state;
- stale revision is rejected;
- accepted update fans out only within the room;
- reconnect/latest-fetch style recovery is available;
- events contain no SSO token, refresh token, raw invite token, or secret.

## Non-goals

The sample does not:

- install npm dependencies;
- initialize a production Node.js project;
- open a public port;
- implement a real WebSocket server;
- own SSO/OIDC provider setup;
- store raw tokens;
- generate a client SDK;
- claim Redis/pubsub, durable event replay, CRDT/OT, or game-loop support.

Those are later scoped decisions.
