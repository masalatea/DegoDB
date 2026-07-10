# Runtime Data Query Control State Persistence First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#256 replans after the runtime-data controls accessibility slice and chooses persisted query-control state before combined-query behavior. #257 implements the first small slice.

The goal is to keep the generated runtime-data controls understandable after each live `runtime-data.json` fetch: when a search, field filter, sort, or page-size request re-renders the generated screens, the controls should still show the active query state returned by the endpoint.

## Planned / Implemented

- Restore generated search input value from `payload.query.q`.
- Restore generated field-filter select/value controls from `payload.query.filter`.
- Restore generated sort select/direction controls from `payload.query.sort`.
- Restore generated page-size input from `payload.pagination.page_size` or `payload.query.page_size`.
- Preserve current request URL construction and precedence.
- Preserve no-query Refresh, selected-key row selection, immutable artifact-key previews, and submit/outbox mutation separation.
- Extend browser smoke coverage to assert retained search/filter/sort control state after live data fetches.

## Boundary

- In scope: browser-local control state restoration after successful `runtime-data.json` payload application.
- Out of scope: combined search+filter+sort query behavior, URL/history persistence, new reset controls, endpoint contract changes, visual redesign, and mutation behavior.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11115 assertions`, `1 skipped`)
