# Shared state sync schema/API contract

## Status

`RSS_2_DONE`

## Purpose

Define the v1 DB schema and REST API contract for room-based shared state synchronization before generating realtime packets or Node.js server input packets.

## Output

Added:

```text
docs/shared-state-sync-schema-api-contract.md
```

The document defines:

- `sync_room`;
- `sync_room_membership`;
- `sync_room_invite`;
- `sync_shared_state`;
- `sync_state_event`;
- create/list room endpoints;
- invite creation and join endpoints;
- get/update/latest-revision state endpoints;
- authorization rules;
- stale revision conflict behavior;
- stable error code set.

## Boundary

This slice does not implement:

- database migration generation;
- OpenAPI emission;
- WebSocket/SSE event contract;
- Node.js sync server input packet;
- app client input packet;
- reference runtime/sample.

Those remain RSS-3 through RSS-7.

## Next

Proceed to RSS-3 Realtime event contract.
