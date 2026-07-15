# 2026-0716 Shared-State Sync Mtool Artifact Linkage First Slice

## Summary

Added a sample38 validator that links Mtool-emitted shared-state sync packets to the Node.js reference runtime.

This proves sample38 can consume generated artifacts, not only the checked-in sample36/sample37 fixtures.

## What changed

- Added `sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-mtool-artifact-linkage.mjs`.
- Updated sample docs and current plan.

## Behavior

The validator:

1. creates a temporary directory;
2. runs `mtool/scripts/create_shared_state_sync_server_input.php`;
3. runs `mtool/scripts/create_shared_state_sync_client_input.php`;
4. reads the emitted `sync-server-input.json` and `sync-client-input.json`;
5. feeds those packets into the sample38 runtime;
6. validates subscribe/update/event behavior;
7. creates and closes the loopback HTTP server to prove the generated packets are acceptable to that adapter;
8. removes the temporary directory.

## Boundary

The validator does not:

- install dependencies;
- initialize a Node.js project;
- open a public port;
- start a production server;
- implement SSO/OIDC setup;
- choose token storage;
- generate SDK or app source.

## Validation

```bash
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-mtool-artifact-linkage.mjs
```

This passed for the first slice.

## Next decision

Choose whether to:

- add a real WebSocket transport sample;
- add a production-hardening checklist;
- add a combined Mtool bundle artifact;
- or checkpoint/PR before widening scope.
