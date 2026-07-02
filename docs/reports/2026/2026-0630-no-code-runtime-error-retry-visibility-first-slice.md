# No-Code Runtime Error/Retry Visibility First Slice

Date: 2026-06-30
Status: `FIRST_SLICE_DONE`

## Summary

Added read-only failed/retryable sync visibility to generated no-code runtime artifacts.

Sync-aware generated screens now carry a deterministic `sync_error_retry_hint` in `runtime-preview.json` and render a small HTML hint that points failed or retryable sync items back to the operator sync outbox. Retry mutation remains outside generated runtime.

## Implemented

- Added `app_no_code_runtime_sync_error_retry_hint()`.
- Added `sync_error_retry_hint` to generated screen render models when `sync_status_hint` is enabled.
- Rendered a read-only `data-sync-retry-hint="operator-outbox"` badge in `runtime-preview.html`.
- Kept form screens and non-sync screens without the retry hint.
- Extended `NoCodeRuntimeTest` coverage for render model and HTML output.
- Extended sample30 checker coverage for generated runtime JSON and HTML.
- Updated sample30 README.

## Boundary

In scope:

- generated/runtime-visible sync error or retryable state
- read-only hints
- existing sample/runtime smoke
- operator sync outbox handoff wording

Out of scope:

- retry mutation in generated runtime
- scheduler
- transport
- conflict resolution
- retry audit table
- broad dashboard

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `php -l mtool/scripts/lib/sample30_no_code_app_local_sync_demo_check.php`
- focused runtime/sample smoke
- `git diff --check`
- `make test`

## Next

Run a short post-runtime-error/retry-visibility product goal replan before choosing the next implementation slice.
