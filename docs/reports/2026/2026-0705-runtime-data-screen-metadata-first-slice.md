# Runtime Data Screen Metadata First Slice

Status: DONE
Date: 2026-07-05

## Summary

This slice adds the first additive read-model metadata to `runtime-data.json`.

The response remains current/alias scoped, read-only, `GET` only, no-store, and versioned as `no-code-runtime-data-v0`. Existing `data` and `source` screen payloads are preserved so the browser merge path remains compatible.

## Implemented

- Added `metadata.row_count` to each runtime-data screen.
- Added `metadata.selected_key` from the generated action key field when the current row contains that key.
- Added `metadata.freshness` with `live-read` for the current generated DBAccess read path.
- Kept immutable artifact-key previews unchanged.
- Kept pagination, filters, detail selection parameters, and form-default semantics out of this slice.

## Smoke Coverage

- Endpoint smoke now asserts list `row_count` metadata and detail `selected_key` metadata.
- Outbox processing smoke now asserts list `row_count` metadata and detail `selected_key` metadata after processing.
- Smoke summaries now report `row_count_metadata` and `selected_key`.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `php -l mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11091 assertions, skipped 1)

## Remaining Candidates

- Promote metadata smoke assertions across sample29 and sample31.
- Query-driven pagination and page-size controls.
- Filter parameters derived from generated screen/operation metadata.
- Detail selection by key instead of always first row.
- Form default behavior for create/update screens.

Push was not performed.
