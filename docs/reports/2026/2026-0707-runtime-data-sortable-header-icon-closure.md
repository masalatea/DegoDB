# Runtime Data Sortable Header Icon Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#342 chooses a closure report after the compact active-sort indicator first slice landed. #343 closes the lane before dynamic row builders, richer sort semantics, broader read-model field typing, or push cleanup.

The accepted capability is deliberately small: generated current/alias runtime-data list headers keep the synchronized sorted-column state from the previous lane, but the visible active-sort marker is now compact enough to read as state instead of another label.

## Accepted Boundary

- Active primary sort headers use compact `^` / `v` visual indicators.
- `aria-sort` remains the accessibility source of truth.
- `data-runtime-sort-state` remains the generated browser state hook.
- Header state still represents only the primary sort.
- Explicit secondary and tertiary sort rows remain in the runtime-data controls.
- Artifact-key previews remain static.
- The read-only `runtime-data.json` endpoint contract did not change.

## Verification Baseline

The implementation slice was verified before this closure with:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11142 assertions`, `1 skipped`)

This closure is documentation-only.

## Remaining Candidates

- Promote visual/manual check coverage across sample29 and sample31 if needed.
- Dynamic add/remove filter and sort rows.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing for stronger filter/sort semantics.
- Grouped or mobile-specific query-control layout.

## Push

Push was not performed.
