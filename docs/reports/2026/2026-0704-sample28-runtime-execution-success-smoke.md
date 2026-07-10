# Sample28 Runtime Execution Success Smoke

Status: `FIRST_SLICE_DONE`

Date: 2026-07-04

## Summary

sample28 now exercises a successful authenticated no-code runtime execution path for current and custom-alias public runtime previews.

The sample managed operation policy allows the local stub admin principal to execute the generated `update_no_code_ticket` tryout action. The runtime execution path treats `public-runtime` as a valid managed-operation sync origin, then enqueues a pending sync intent instead of directly mutating the business row.

## Changed

- sample28 managed operation seed now uses the local stub admin role and no extra write scope for the tryout action.
- Managed-operation sync endpoint validation now accepts `public-runtime` as an origin.
- The direct endpoint smoke now expects current/alias `execute.json` POSTs to return 200, pass request binding, create a `managed-operation-sync-intent-v0`, and leave the outbox item `pending`.

## Verification

- `php -l mtool/app/managed_operation_sync.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `make sample28-no-code-public-runtime-browser-smoke`

The smoke reports both current and alias execution endpoints with `status: 200`, endpoint `ok: true`, `executed: true`, dispatcher `ok: true`, sync intent `managed-operation-sync-intent-v0`, and outbox status `pending`.

## Notes

`dispatcher_executed` remains `false` for this path because the public runtime endpoint enqueues a sync intent rather than processing the outbox item immediately. That is intentional for this slice.

Push was not performed.

