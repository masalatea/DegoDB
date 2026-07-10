# Runtime Data Browser URL Query Mirror First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#268 replans after the runtime-data controls visual density slice and chooses URL query mirroring before richer filter/sort models or broader read-model shape. #269 implements the first small slice.

The goal is to make successful current/alias read-only runtime-data exploration visible in the browser URL, without changing endpoint contracts or replaying query state on initial page load yet.

## Planned / Implemented

- Mirror successful current/alias runtime-data fetch query parameters into the current `runtime-preview.html` URL using `history.replaceState`.
- Mirror selected row, search, field filter, sort, page, and page-size parameters.
- Clear known runtime-data query parameters from the browser URL when the generated `Clear` control fetches no-query runtime data.
- Preserve immutable artifact-key preview behavior because artifact-key pages do not receive a runtime-data binding.
- Preserve request construction, endpoint contracts, query controls, and submit/outbox mutation behavior.
- Extend generated HTML and browser smoke coverage for the URL mirror.

## Boundary

- In scope: browser URL query mirroring after successful current/alias runtime-data fetches.
- Out of scope: initial-load replay from URL parameters, back/forward navigation handling, endpoint contract changes, richer filter/sort models, selected-row semantics changes, mutation behavior, and push.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11122 assertions`, `1 skipped`)
