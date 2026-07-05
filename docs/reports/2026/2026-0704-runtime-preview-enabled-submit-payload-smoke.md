# Runtime Preview Enabled Submit Payload Smoke

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

The sample28 public runtime browser smoke now covers the enabled submit path without mutating the server. Current and alias previews use a fetch-stub probe that temporarily makes one generated action ready in the browser, clicks `Submit to server`, and inspects the outgoing `FormData`.

Artifact-key preview remains static and has no execution binding or submit probe.

## Implementation Notes

- Added `--submit-probe=none|enabled-fetch-stub` to `check_no_code_runtime_preview_ui_smoke.js`.
- The probe preserves the normal blocked-state assertions by snapshotting draft/execute state before forcing the enabled submit path.
- The fetch stub verifies POST method, `same-origin` credentials, CSRF, project key, artifact key, action key, key payload, and edited required input payload.
- Updated `check_sample28_no_code_public_runtime_browser_smoke.sh` so current and alias previews run the submit probe while artifact preview keeps `--execution-binding=none`.

## Verification

- `make sample28-no-code-public-runtime-browser-smoke`: passed.

The run confirmed current preview posts to `/runs/no-code/SAMPLE28/current/execute.json` and alias preview posts to `/runs/no-code/SAMPLE28/alias/stable/execute.json` through the browser-side fetch stub.

Push was not performed for this slice.
