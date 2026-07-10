# Runtime Data Dynamic Row Builder Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#346 chooses closure after the dynamic row-builder first slice landed. #347 closes the lane before richer row-builder behavior, numeric/date-aware semantics, broader read-model field typing, grouped/mobile layout, local stack cleanup, or push.

The accepted capability is intentionally bounded: generated current/alias runtime-data controls now reduce default density by showing one filter row and one sort row, while preserving the existing generated DOM hooks and read-only query contracts for all three visible rows when needed.

## Accepted Boundary

- The browser shows only primary filter and sort rows by default.
- Secondary and tertiary filter/sort rows stay generated but hidden until needed.
- Add controls reveal secondary/tertiary rows.
- Remove controls clear and hide extra rows, including downstream tertiary stale values when secondary is removed.
- Payload sync and URL replay reveal rows that already have secondary/tertiary query values.
- Browser history replay continues to preserve three-row filter/sort state.
- Sortable table headers remain a primary-sort shortcut and collapse secondary/tertiary sort rows.
- Endpoint contracts did not change.
- Artifact-key previews remain static.

## Verification Baseline

The implementation slice was verified before this closure with:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

This closure is documentation-only.

## Remaining Candidates

- True arbitrary filter-row builder up to the endpoint max-8 boundary.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing for stronger query semantics.
- More compact grouped/mobile-specific query-control layout.
- Local commit stack review before push or another large behavior lane.

## Push

Push was not performed.
