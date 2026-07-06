# Multi-Profile Runtime Data Row Selection Fixtures

Status: DONE
Date: 2026-07-05

## Summary

This slice promotes the #223 browser row-selection affordance across the other product-facing no-code sample profiles.

sample29 and sample31 now have second seeded rows, so their public runtime browser smokes can verify that selecting a non-first list row fetches `runtime-data.json?selected_key=...`, highlights the selected row, and refreshes detail/form data from that row. This keeps the row-selection proof from being sample28-only.

## Implemented

- Added a second support-case seed row to sample29 with key `2002`.
- Added a second inventory-request seed row to sample31 with key `3102`.
- Updated the shared browser smoke profile expectations so sample29 selects `2002` and sample31 selects `3102`.
- Updated the direct runtime execution endpoint smoke selected-key expectations for sample29 and sample31.

## Verified Behavior

- sample29 current/alias live runtime previews render two rows.
- sample29 row selection fetches `runtime-data.json?selected_key=2002`.
- sample29 selected detail/form data and hidden key value use `2002`.
- sample31 current/alias live runtime previews render two rows.
- sample31 row selection fetches `runtime-data.json?selected_key=3102`.
- sample31 selected detail/form data and hidden key value use `3102`.
- Missing selected keys still fail closed with JSON 422.
- Existing submit enqueue and generated server DBAccess outbox processing proof still pass.

## Verification

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample29-no-code-public-runtime-browser-smoke`
  - `runtimeDataRowSelection.selectedKey`: `2002`
  - `row_count_metadata`: `2` for current/alias runtime-data reads
  - selected detail/form body shows the Contoso support case row
- `make sample31-no-code-public-runtime-browser-smoke`
  - `runtimeDataRowSelection.selectedKey`: `3102`
  - `row_count_metadata`: `2` for current/alias runtime-data reads
  - selected detail/form body shows the Contoso inventory request row
- `make test` (337 tests, 11093 assertions, skipped 1)

## Remaining Candidates

- Query-driven pagination and page-size controls.
- Filter parameters derived from generated metadata.
- Form default behavior for create/update screens.
- Operator/admin wording for live runtime data selection boundaries.

Push was not performed.
