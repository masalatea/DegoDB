# Runtime Submit Operator Outbox Detail Path Feedback

Status: `FIRST_SLICE_DONE`

Date: 2026-07-04

## Summary

Generated no-code runtime submit feedback now shows the existing operator sync outbox detail path when the execution endpoint returns an outbox `dedupe_key`.

The slice turns the prior outbox trace into an actionable inspection handoff while keeping generated runtime behavior enqueue-first. It does not process the item, retry it, or refresh business data.

## Changed

- Added generated runtime path formatting for `/projects/{project_key}/sync-outbox/{dedupe_key}`.
- Added the detail path to successful submit status and action feedback.
- Extended sample28 real-submit smoke capture and assertions for the returned `dedupe_key` and rendered detail path.
- Extended the direct endpoint smoke to require a non-empty outbox `dedupe_key`.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`

Push was not performed.
