# Runtime Data Browser History Replay First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#292 chooses the first browser history replay implementation slice after the boundary plan. #293 implements it.

## Planned / Implemented

- Use `pushState` after successful explicit user runtime-data query operations.
- Keep initial URL replay and Clear behavior on `replaceState`.
- Add a `popstate` replay path that parses the current browser URL and reuses the existing read-only `runtime-data.json` refresh path.
- Avoid creating extra history entries while handling `popstate`.
- Extend the public runtime browser smoke to prove back/forward replay for search plus two-filter query state.

## Boundary

- In scope: generated current/alias browser history replay for read-only runtime-data queries.
- Out of scope: endpoint contract changes, typed filter operators, more visible filter rows, multi-column sort, broader read-model shape, mutation behavior, artifact-key preview changes, and push.

## Verification

- Passed in this working copy:
  - `php -l mtool/app/no_code_runtime.php`
  - `php -l tests/Integration/NoCodeRuntimeTest.php`
  - `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
  - `git diff --check`
  - `make sample28-no-code-public-runtime-browser-smoke`
  - `make test` (`337 tests`, `11129 assertions`, `1 skipped`)

## Notes

- The first `make test` run after implementation exposed a fixed integration-test string that still expected the pre-history-replay JavaScript function signatures.
- The test expectation was updated to cover the new `browserHistoryMode` argument, `pushState` / `replaceState`, and `popstate` replay hook.
