# Sample29 Runtime Outbox Processing Smoke

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Sample29 now has the same processing confidence shape as sample28 for the public no-code runtime smoke.

The existing outbox processing smoke is profile-aware for `sample28` and `sample29`. Sample29 is enabled by default through `make sample29-no-code-public-runtime-browser-smoke`, so the smoke now proves:

- browser current / alias submit reaches the real endpoint;
- direct current / alias endpoint POSTs enqueue pending work for `update_support_case`;
- the existing managed-operation sync outbox processor claims that pending work;
- the generated server DBAccess handler processes it against an isolated SQLite `support_case` table;
- the row's `next_action` becomes `Generated sample29 direct endpoint smoke payload`.

Sample28 remains covered by the same smoke path and still verifies `no_code_ticket.body`.

## Boundary

This does not change runtime endpoint behavior. Public runtime submit remains outbox-based and async. The smoke proves the existing processor path can consume queued work for the second no-code domain.

## Verification

- `php -l mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php`
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh mtool/scripts/check_sample29_no_code_public_runtime_browser_smoke.sh`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `334 tests`, `10967 assertions`, `skipped 1`
