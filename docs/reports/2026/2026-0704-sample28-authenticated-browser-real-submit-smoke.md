# Sample28 Authenticated Browser Real-Submit Smoke

Status: `FIRST_SLICE_DONE`

Date: 2026-07-04

## Summary

sample28 public runtime browser smoke now covers the real authenticated submit path for current and custom-alias previews.

The Playwright smoke can run in `enabled-real-fetch` mode. In that mode it logs in as the local stub admin, opens the runtime preview, prepares the generated action draft, clicks `Submit to server`, and records the real fetch response. The smoke verifies that the UI reaches the accepted state and the response contains a pending managed-operation sync intent.

## Changed

- Added `--submit-probe=enabled-real-fetch` to `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`.
- Added stub admin login support to the Playwright smoke for real-submit probes.
- Captured real fetch response status, `ok`, sync intent version, and outbox status.
- Switched sample28 current/alias public runtime browser smoke from fetch-stub submit probing to real authenticated submit probing.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample28-no-code-public-runtime-browser-smoke`

The sample28 smoke verifies both current and alias previews with response status `200`, response `ok: true`, sync intent `managed-operation-sync-intent-v0`, and outbox status `pending`.

Push was not performed.

