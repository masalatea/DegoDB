# Runtime Data Page-Size Input First Slice

Date: 2026-07-05

Status: `DONE`

## Summary

#232 replans after the pagination total-count label and chooses user-entered page-size controls as the next small continuation. #233 implements the first slice.

The endpoint already accepts bounded `page_size` values for current/alias `runtime-data.json`. This slice uses that existing contract from the generated runtime UI without adding filter/search or direct page navigation semantics.

## Implemented

- Replaced the fixed `Page size 1` entry button with a numeric `Page size` input and `Apply` control.
- Kept `Apply` behavior anchored to page 1 for the selected page size.
- Kept Previous/Next controls driven by returned pagination metadata.
- Clamped browser page-size input to the existing endpoint boundary of 1..100.
- Extended browser smoke coverage to exercise the input-backed Apply control.

## Boundary

- In scope: current/alias generated runtime page-size input for live runtime data pagination.
- In scope: preserving no-query Refresh, selected-row refresh, and metadata-driven Previous/Next.
- Out of scope: filter/search query parameters, direct page number input, first/last buttons, cursor pagination, artifact-key previews, and submit/outbox mutation behavior.

## Verification

Passed before commit:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11100 assertions`, `1 skipped`)

Note: Docker-backed checks were rerun with normal Docker permissions after sandboxed runs failed on buildx activity-file writes under `~/.docker`.
