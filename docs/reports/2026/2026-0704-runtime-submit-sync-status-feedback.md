# Runtime Submit Sync-Status Feedback

Status: `FIRST_SLICE_DONE`

Date: 2026-07-04

## Summary

Generated no-code runtime previews now surface the sync outbox status returned by a successful real server submit.

When the public runtime execution endpoint accepts a generated action and enqueues a managed-operation sync intent, the runtime execute status and action feedback include `Sync outbox status: pending`. This keeps the UI honest about the current boundary: the action has been accepted and queued, while outbox processing and business-row refresh remain separate later work.

## Changed

- Added generated runtime JavaScript helpers for extracting sync outbox status from the endpoint response.
- Updated successful submit messaging to append the sync outbox status when it exists.
- Extended the real-submit Playwright smoke assertion to require pending sync status in both submit status and action feedback.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample28-no-code-public-runtime-browser-smoke`

Push was not performed.

