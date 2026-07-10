# Runtime Data Terminal Done Auto Refresh

Status: DONE
Date: 2026-07-05

## Summary

This slice builds on the explicit `runtime-data.json` Refresh path and key preservation from #212/#213.

When live outbox polling reaches terminal `done` on a current/alias public runtime preview, the runtime now automatically fetches fresh read-only runtime data. This keeps the user on the same generated preview while updating the visible list/detail/form data from the current data endpoint.

## Implemented

- Reused the same `runtime-data.json` fetch/apply path for manual Refresh and terminal-done auto refresh.
- Limited auto refresh to terminal `done` status with an available current/alias `runtime_data_url`.
- Left `pending` timeout, `failed` / `needs_review`, and immutable artifact-key preview behavior unchanged.
- Preserved the submit/outbox status, copy path, detail link, and runtime-flow state after auto refresh re-renders the current screen body.
- Extended the browser smoke so the terminal `done` branch waits for and verifies automatic runtime-data fetch.
- Kept refreshed form key preservation checks from #213 active after the auto refresh.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11091 assertions, skipped 1)

## Remaining Candidates

- Clearer UI wording around static artifact-key preview vs current/alias live data refresh.
- A larger read-model shape for pagination, filters, and detail selection.
- More explicit post-refresh data-change proof after synchronous demo processing mutates a row in the same request path.

Push was not performed.
