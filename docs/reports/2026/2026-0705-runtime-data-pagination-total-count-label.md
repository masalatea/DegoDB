# Runtime Data Pagination Total-Count Label

Date: 2026-07-05

Status: `DONE`

## Summary

#230 replans after the first browser pagination controls slice and chooses compact total-count visibility as the next smallest continuation. #231 implements it.

The fixed `Page size 1` pagination proof is already working. Before adding arbitrary page-size input or filter/search semantics, the generated runtime should make the existing pagination metadata easier to read by showing how many live runtime rows exist in total.

## Implemented

- Added `total_rows` to the generated runtime pagination label: `Page X of Y (N total rows)`.
- Added `data-runtime-pagination-total-rows` to the active pagination control wrapper.
- Extended the public runtime browser smoke to assert that the visible total-row label and DOM attribute match returned `metadata.pagination.total_rows`.
- Preserved the existing pagination request behavior, selected-row behavior, and no-query Refresh behavior.

## Boundary

- In scope: display already-returned pagination metadata in the generated runtime UI.
- In scope: browser-smoke coverage for the label/metadata match.
- Out of scope: arbitrary page-size input, direct page number input, first/last buttons, filter/search query parameters, cursor pagination, and submit/outbox mutation behavior.

## Verification

Passed:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11098 assertions, skipped 1)
