# Runtime Data Browser Pagination Controls First Slice

Date: 2026-07-05

Status: `DONE`

## Summary

#229 implements the first browser-facing pagination controls for current/alias live `runtime-data.json` consumption.

The slice keeps the existing no-query `Refresh preview` behavior as the default full-list reload. Pagination only starts after the user explicitly clicks the page-size entry control. Once paginated runtime data is returned, the preview renders Previous/Next controls from the additive `metadata.pagination` contract.

## Implemented

- Added a current/alias-only list pagination entry control: `Page size 1`.
- Added `runtime-data.json?page=...&page_size=...` request construction in the generated runtime browser JS.
- Rendered Previous/Next controls from returned list-screen `metadata.pagination`.
- Preserved `Refresh preview` as the no-query full-list fetch.
- Preserved selected-row detail/form behavior through `runtime-data.json?selected_key=...`.
- Added focused browser-smoke assertions that prove pagination entry, next-page fetch, one-row rendering, pagination metadata, and row-selection compatibility.
- Added minimal generated-runtime CSS for pagination controls and disabled states.

## Accepted Boundary

- In scope: current/alias live runtime data pagination controls for the generated runtime preview.
- In scope: additive pagination UI that appears only when live runtime data binding exists.
- In scope: browser smoke proof across sample28, sample29, and sample31.
- Out of scope: arbitrary user-entered page sizes, filter/search controls, artifact-key runtime data mutation, infinite scrolling, and server-side cursor pagination.
- Out of scope: changing submit/outbox mutation semantics.

## Verification

Passed:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11096 assertions, skipped 1)

## Remaining Candidates

- Add a user-entered page-size control after the fixed-size proof is stable.
- Add direct page number input or first/last controls if larger list navigation becomes a practical need.
- Add filter/search query support after pagination semantics remain stable across more samples.
- Consider a compact count label that uses `total_rows` for operator clarity.
