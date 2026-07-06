# Runtime Data Selected-Key Query First Slice

Status: DONE
Date: 2026-07-05

## Summary

This slice adds the first query-driven read-model behavior to `runtime-data.json`.

Current/alias runtime data routes now accept an optional `selected_key` query parameter. Without it, behavior remains unchanged: detail and form screens render from the first returned row. With it, list rows remain intact, while detail and form screens render from the row whose generated action key field matches the selected key.

## Implemented

- Added optional `selected_key` query parsing for current/alias `runtime-data.json`.
- Added selected-row lookup using the generated action key field from the screen contract.
- Preserved existing list rows, `data`, `source`, and metadata shapes.
- Added `query.selected_key` to successful `runtime-data.json` responses.
- Missing selected keys fail closed as JSON 422.
- Invalid selected-key strings fail closed before rendering.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
  - proves normal current/alias runtime data still uses first row
  - proves `selected_key=1002` selects a non-first sample28 row
  - proves missing selected key fails closed with 422
- `make test` (337 tests, 11091 assertions, skipped 1)

## Remaining Candidates

- Promote selected-key query smoke across sample29/sample31, even though they currently have single-row seeded data.
- Query-driven pagination and page-size controls.
- Filter parameters derived from generated screen/operation metadata.
- Form default behavior for create/update screens.
- Browser UI affordance for selecting list rows and refreshing detail/form via `selected_key`.

Push was not performed.
