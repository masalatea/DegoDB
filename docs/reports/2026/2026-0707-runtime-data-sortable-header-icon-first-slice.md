# Runtime Data Sortable Header Icon First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#340 chooses compact active-sort indicator treatment after the sortable header state lane closed. #341 replaces the verbose generated `asc` / `desc` header suffix with a compact visual indicator while preserving the existing `aria-sort` and `data-runtime-sort-state` behavior.

This slice is display-only. It does not change the read-only runtime-data endpoint, generated query payloads, URL mirroring, or sort semantics.

## Implemented

- Replaced active header text suffixes with compact `^` / `v` indicators.
- Styled the active indicator as a small inline badge so it reads as state rather than another column label.
- Kept `aria-sort="ascending"` / `aria-sort="descending"` as the accessibility source of truth.
- Kept browser smoke coverage focused on the synchronized header state after header-driven sorting.

## Boundary

- No endpoint contract change was made.
- No JS state model change was made beyond smoke inspection.
- No numeric/date-aware semantics were added.
- No dynamic filter/sort row builder was added.

## Verification

Passed in this worktree:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11142 assertions`, `1 skipped`)

## Next Candidates

- Promote the compact icon visual check across sample29 and sample31 if desired.
- Dynamic add/remove filter and sort rows.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing for stronger filter/sort semantics.
