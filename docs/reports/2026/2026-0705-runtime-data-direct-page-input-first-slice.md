# Runtime Data Direct Page Input First Slice

Date: 2026-07-05

Status: `DONE`

## Summary

#234 replans after the page-size input slice and chooses direct page navigation as the next small continuation. #235 implements the first slice.

The endpoint already accepts bounded `page` and `page_size` values for current/alias `runtime-data.json`. This slice adds a browser control for explicit page jumps without introducing filter/search semantics or changing the read-only runtime-data boundary.

## Implemented

- Added an active pagination `Page` numeric input and `Go` control.
- Clamped browser direct-page input to the returned `page_count`.
- Kept page-size `Apply` anchored to page 1 for the selected page size.
- Kept Previous/Next controls driven by returned pagination metadata.
- Extended browser smoke coverage to exercise direct page navigation from page 2 back to page 1.

## Boundary

- In scope: current/alias generated runtime direct page navigation after pagination has been opted into.
- In scope: preserving no-query Refresh, selected-row refresh, page-size Apply, and metadata-driven Previous/Next.
- Out of scope: filter/search query parameters, first/last buttons, cursor pagination, artifact-key previews, and submit/outbox mutation behavior.

## Verification

Passed before commit:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11102 assertions`, `1 skipped`)

Note: Docker-backed checks were run with normal Docker permissions because buildx writes activity metadata under `~/.docker`.
