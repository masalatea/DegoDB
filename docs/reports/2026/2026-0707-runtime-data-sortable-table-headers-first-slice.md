# Runtime Data Sortable Table Headers First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

This slice makes current/alias runtime-data list tables easier to explore by adding sortable table headers.

The feature deliberately reuses the existing read-only `runtime-data.json` query path. A header click sets the clicked field as the primary sort, toggles direction when the same field is already primary, clears secondary and tertiary sort rows, and then runs the same runtime-data fetch and URL mirror flow as the visible sort controls.

## Implemented

- Added generated header sort buttons for current/alias runtime-data list tables.
- Added browser binding that maps a header click into the existing primary sort controls.
- Preserved existing search, filter, and page-size controls when header sorting.
- Reset secondary and tertiary sort rows for header-driven sorting to keep the first slice simple.
- Added smoke coverage proving header sort requests, URL mirroring, and retained controls.
- Added focused HTML assertions for the generated header sort attributes.

## Boundary

- Header sorting is a browser affordance over the existing read-only query contract.
- No endpoint contract change was made.
- No dynamic sort-row builder was added.
- No numeric/date-aware semantics were added.
- No explicit `aria-sort` state management was added in this first slice.

## Verification

Passed in this worktree:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11140 assertions`, `1 skipped`)

## Next Candidates

- Add visible sorted-column state such as `aria-sort`.
- Dynamic add/remove filter and sort rows.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing for stronger filter/sort semantics.
- Grouped or mobile-specific query-control layout.
