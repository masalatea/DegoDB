# 2026-0715 Shared State Sync Server Input Artifact Emission

## Status

`RSS_7_DONE`

## Purpose

Implement Mtool artifact emission for the shared-state sync Node.js server input packet after the RSS-6 static fixture proved the packet shape.

## Output

Added:

- `mtool/app/shared_state_sync_server_input.php`
- `mtool/scripts/create_shared_state_sync_server_input.php`
- `tests/Integration/SharedStateSyncServerInputTest.php`

The CLI emits:

- `sync-server-input.json`
- `SYNC-SERVER-INPUT.md`

## Boundary

The implementation does not:

- install Node.js dependencies;
- initialize a Node.js project;
- generate production server source;
- start a server;
- open ports;
- implement SSO/OIDC provider verification;
- claim Redis/pubsub, queue, guaranteed replay, CRDT/OT, or game-loop support.

## Validation

Focused validation:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncServerInputTest.php
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
git diff --check
```

## Next

RSS-8 should harden user-facing documentation and dry-run guidance:

- add the CLI route to `docs/shared-state-sync-node-server-input-packet.md`;
- optionally add `make` target after deciding naming;
- record a sample dry-run path and emitted summary.
