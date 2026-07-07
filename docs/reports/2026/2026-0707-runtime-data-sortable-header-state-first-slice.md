# Runtime Data Sortable Header State First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#334 chooses visible sorted-column state as the next smallest continuation after the sortable runtime-data table header lane was closed. #335 adds first-slice generated header state for current/alias runtime-data list tables.

The slice keeps the existing read-only `runtime-data.json` query contract unchanged. It only mirrors the active primary sort field and direction into generated table headers.

## Implemented

- Added initial `aria-sort="none"` to sortable generated list-table header cells.
- Added `data-runtime-sort-state="none"` to sortable generated header buttons.
- Synced the active primary sort field to `aria-sort="ascending"` or `aria-sort="descending"` after runtime-data payloads update controls.
- Synced non-primary sortable headers back to `aria-sort="none"`.
- Added small visual state text for active header sort direction.
- Extended browser smoke coverage to prove header state after header-driven sorting.
- Added focused HTML assertions for initial generated header state.

## Boundary

- No endpoint contract change was made.
- No new sort semantics were added.
- Secondary and tertiary sort rows remain available in the explicit controls, but header state only represents the primary sort.
- Artifact-key previews remain static.

## Verification

Passed in this worktree:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11142 assertions`, `1 skipped`)

## Next Candidates

- Consider a compact icon treatment for active sort state.
- Dynamic add/remove filter and sort rows.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing for stronger filter/sort semantics.
