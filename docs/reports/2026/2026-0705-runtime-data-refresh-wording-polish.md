# Runtime Data Refresh Wording Polish

Status: DONE
Date: 2026-07-05

## Summary

This slice follows #214 terminal-done auto refresh with a small product-surface wording polish.

The behavior is unchanged: immutable artifact-key previews remain static, while current/alias previews can use the injected `runtime_data_url` to fetch read-only live runtime data.

## Implemented

- Clarified the disabled Refresh status so users see that artifact-key previews stay static and current/alias previews can fetch live runtime data when available.
- Clarified enabled current/alias Refresh copy as a read-only live runtime data fetch.
- Clarified enabled artifact-key Refresh copy as generated artifact reload, not live data fetch.
- Clarified runtime-data failure copy so fetch errors say the read-only endpoint failed and the current preview data was left unchanged.
- Updated PHPUnit and browser-smoke expectations for the new boundary wording.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11091 assertions, skipped 1)

## Remaining Candidates

- Broader `runtime-data.json` read-model shape for pagination, filters, detail selection, and form defaults.
- More explicit data-change proof after synchronous demo processing mutates a row in the same request path.
- Optional operator/admin copy for stale artifacts versus current/alias live reads.

Push was not performed.
