# 2026-0715 Shared State Sync Server Input Docs Dry Run

## Status

`RSS_8_DONE`

## Purpose

Harden user-facing documentation and record a dry-run for the emitted shared-state sync server input artifact.

## Documentation update

Updated:

- `docs/shared-state-sync-node-server-input-packet.md`

Added:

- CLI command;
- generated file list;
- overwrite/target-dir safety note;
- explicit non-actions;
- focused validation commands.

## Dry-run

Command:

```bash
php mtool/scripts/create_shared_state_sync_server_input.php --project-key=SAMPLE36 --backend-base-url-env=SAMPLE_BACKEND_URL --target-dir=/tmp/mtool-sync-server-input-rss8-doc-check
```

Result:

```json
{
    "ok": true,
    "error": "",
    "target_dir": "/tmp/mtool-sync-server-input-rss8-doc-check",
    "files": [
        "SYNC-SERVER-INPUT.md",
        "sync-server-input.json"
    ],
    "contract_errors": [],
    "project_key": "SAMPLE36",
    "backend_base_url_env": "SAMPLE_BACKEND_URL",
    "artifact": "shared_state_sync_server_input"
}
```

## Boundary confirmed

The dry-run emitted only:

- `SYNC-SERVER-INPUT.md`;
- `sync-server-input.json`.

It did not:

- install dependencies;
- initialize a Node.js project;
- start a server;
- open ports;
- write `package.json`;
- write Node.js server source;
- create `node_modules`.

## Next

RSS-9 should define the app client input packet for consumers that join rooms, subscribe to realtime state, update state, and apply reconnect/latest-fetch behavior.
