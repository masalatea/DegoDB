# Runtime Data Browser Third Sort Row First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

This slice completes the visible browser side of the existing max-3 runtime-data sort contract.

The current/alias read-only `runtime-data.json` endpoint already accepts up to three ordered `sort[field]=asc|desc` entries. The generated runtime browser previously exposed two visible sort rows, so the third endpoint slot was available only through URL/API-style use. This slice exposes the third row directly in generated controls without introducing a dynamic row builder.

## Implemented

- Added `Sort 3` and `Direction 3` generated controls to current/alias runtime-data browser output.
- Carried the third sort through generated query capture, payload sync, initial URL replay, URL mirror, and browser smoke probes.
- Extended sample28, sample29, and sample31 smoke expectations with profile-appropriate third sort fields.
- Added generated HTML assertions for the tertiary sort field and direction controls.
- Made third-filter URL replay assignment explicit so the existing three visible filter rows restore from URL state consistently.

## Boundary

- The endpoint max remains three ordered sort fields.
- No dynamic sort-row builder was added.
- No sortable table header behavior was added.
- No numeric/date-aware sort semantics were added.
- No read-model shape expansion was added.

## Verification

Passed in this worktree:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11138 assertions, 1 skipped)

## Next Candidates

- Closure report for the visible three-sort-row lane.
- Local commit stack review before push cleanup.
- Dynamic sort/filter row builders, only after layout density and wrapping are deliberately designed.
- Sortable column headers.
- Numeric/date-aware sort semantics.
