# Runtime Data List Row Selection First Slice

Status: DONE
Date: 2026-07-05

## Summary

This slice turns the selected-key runtime-data endpoint into a small browser-visible affordance.

Current/alias runtime previews now add a `Select` control to live list rows when a `runtime_data_url` binding exists. Selecting a row fetches `runtime-data.json?selected_key=...`, keeps list rows intact, highlights the selected row, and refreshes detail/form screens from the selected row.

Artifact-key previews remain static and do not fetch live runtime data.

## Implemented

- Added selected-key URL construction for runtime-data fetches.
- Added list-row `Select` buttons only when live runtime data binding exists.
- Added selected-row highlight and pressed state.
- Reused the existing read-only runtime-data refresh path.
- Preserved hidden action key generation for selected rows by preferring the selected-key metadata before falling back to first-row key lookup.
- Updated the browser smoke fetch probe to recognize query-string runtime-data URLs.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
  - current preview fetches `/runtime-data.json?selected_key=1002`
  - alias preview fetches `/runtime-data.json?selected_key=1002`
  - selected row reports key `1002`
  - selected detail/form hidden action key reports `1002`
  - selected draft has no `key.missing:id`
- `make test` (337 tests, 11093 assertions, skipped 1)

## Remaining Candidates

- Promote row-selection smoke assertions across sample29/sample31 after adding multi-row seeded fixtures.
- Add pagination and page-size query support.
- Add filter parameters derived from generated metadata.
- Clarify whether form-default semantics should follow selected row or action mode.

Push was not performed.
