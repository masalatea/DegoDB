# 2026-0703 Runtime Action Intent Draft Summary

Status: `FIRST_SLICE_DONE`

## Summary

Adds a compact human-readable summary line to generated no-code runtime `Action Intent Draft` panels.

This follows the readiness checks slice. The JSON detail remains available, but a first-time user can now see whether the local draft is ready or blocked without parsing the JSON payload.

## Changes

- Adds `data-intent-draft-summary` above the local intent draft JSON.
- Shows `Ready draft: no blocking checks found.` when the local draft has no blocking checks.
- Shows `Blocked draft: ...` with the relevant `draft_checks`, such as `action.disabled` or `key.missing:id`, when the local draft is blocked.
- Keeps the boundary unchanged: the preview is browser-side only and does not execute a server update or bypass disabled action policy.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- Focused `NoCodeRuntimeTest`: `8 tests, 121 assertions`
- `make sample28-no-code-runtime-ui-smoke`: passed and confirmed `draftSummaryAfterEdit` includes `Blocked draft: action.disabled, key.missing:id`
- Full `make test`: `327 tests, 10818 assertions, skipped 1`
- `git diff --check`: passed

Push was not performed for this slice.
