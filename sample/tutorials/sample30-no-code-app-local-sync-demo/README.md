# Sample30 No-Code App-local Sync Demo

- Role: first sync-backed no-code demonstration.
- Path: canonical table metadata -> shared contract -> managed operation -> `NO-CODE-RUNTIME` action intent -> managed operation sync outbox -> App-local SQLite handler.
- Current first-slice scope: one generated no-code update action becomes a managed operation sync intent, is enqueued, and is processed by the App-local handler to update a local SQLite row.

Run:

```bash
./sample/tutorials/sample30-no-code-app-local-sync-demo/run.sh up
./sample/tutorials/sample30-no-code-app-local-sync-demo/run.sh apply-seed
make sample30-pack-runtime-test
bash mtool/scripts/check_sample_pack_compose_smoke.sh --pack=sample30-no-code-app-local-sync-demo
bash mtool/scripts/check_sample_pack_runtime_smoke.sh --pack=sample30-no-code-app-local-sync-demo
```

Generated artifact targets:

```text
work/source-outputs/SAMPLE30/APP-LOCAL-PERSISTENCE/schema.sql
work/source-outputs/SAMPLE30/APP-LOCAL-PERSISTENCE/AppLocalPersistence.php
work/source-outputs/SAMPLE30/NO-CODE-RUNTIME/runtime-preview.html
work/source-outputs/SAMPLE30/NO-CODE-RUNTIME/runtime-preview.json
work/source-outputs/SAMPLE30/NO-CODE-RUNTIME/screen-definition.json
```

The pack checker verifies:

- `sync_task` shared contract manifest and App-local schema generation
- App-local source output artifact generation
- no-code runtime artifact generation
- authorized `update_sync_task` dispatch builds a managed operation sync intent
- the sync intent is enqueued in the managed operation outbox
- the App-local outbox handler processes the intent and updates the local SQLite DTO
