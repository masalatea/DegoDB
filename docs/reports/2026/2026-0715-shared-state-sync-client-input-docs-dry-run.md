# 2026-0715 Shared State Sync Client Input Docs Dry Run

## Status

`RSS_13_DONE`

## Purpose

Harden user-facing documentation and record a dry-run for the emitted shared-state sync app client input artifact.

## Documentation update

Updated:

- `docs/shared-state-sync-app-client-input-packet.md`

Added:

- CLI command;
- generated file list;
- overwrite/target-dir safety note;
- explicit non-actions;
- focused validation commands.

## Dry-run

Command:

```bash
php mtool/scripts/create_shared_state_sync_client_input.php --project-key=SAMPLE37 --api-base-url-env=SAMPLE_BACKEND_URL --target-dir=/tmp/mtool-sync-client-input-rss13-doc-check
```

Result:

```json
{
    "ok": true,
    "error": "",
    "target_dir": "/tmp/mtool-sync-client-input-rss13-doc-check",
    "files": [
        "SYNC-CLIENT-INPUT.md",
        "sync-client-input.json"
    ],
    "contract_errors": [],
    "project_key": "SAMPLE37",
    "api_base_url_env": "SAMPLE_BACKEND_URL",
    "artifact": "shared_state_sync_client_input"
}
```

## Boundary confirmed

The dry-run emitted only:

- `SYNC-CLIENT-INPUT.md`;
- `sync-client-input.json`.

It did not:

- generate SDKs;
- generate app source;
- install dependencies;
- choose token storage;
- implement SSO/OIDC provider setup;
- start realtime runtime;
- implement offline sync.

## Next

RSS-14 should review the shared-state sync packet stack as a whole and decide whether to add a combined bundle/manifest, validation checklist, or open a PR checkpoint.
