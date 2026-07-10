# Runtime Data Controls Visual Density First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#266 replans after the query reset affordance slice and chooses visual density polish before URL/history persistence, richer filter/sort models, or broader read-model shape. #267 implements the first small slice.

The runtime-data control row is now useful but dense: pagination, search, field filter, sort, page-size, and Clear all share one generated group. This slice keeps behavior unchanged while making the generated controls easier to scan and wrap.

## Planned / Implemented

- Add a stable visual label class for the runtime-data control group.
- Tighten generated control spacing and button/input padding.
- Slightly reduce search/text/select widths so the row wraps less aggressively.
- Add a small-screen rule that lets labels and buttons stretch cleanly instead of squeezing controls.
- Keep the existing `data-runtime-data-controls`, `role="group"`, and `aria-label="Runtime data controls"` semantics.
- Preserve request construction, query retention, Clear, selected-row requests, immutable artifact-key previews, and submit/outbox mutation behavior.

## Boundary

- In scope: generated CSS/markup polish and generated HTML contract coverage.
- Out of scope: endpoint query contracts, URL/history persistence, multi-filter/multi-sort behavior, selected-row behavior changes, mutation behavior, and broad visual redesign.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11121 assertions`, `1 skipped`)
