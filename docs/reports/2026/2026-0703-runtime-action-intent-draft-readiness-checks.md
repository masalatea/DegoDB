# 2026-0703 Runtime Action Intent Draft Readiness Checks

Status: `FIRST_SLICE_DONE`

## Summary

Follow-up to the runtime action intent draft first slice.

The generated preview already shows local action-intent JSON as users edit fields. This slice makes the draft more self-explanatory by adding readiness checks to the local draft JSON.

## Changes

- Adds `draft_checks` to the browser-side intent draft.
- Adds `policy_failed_checks` passthrough from generated action metadata.
- Marks disabled actions as `action.disabled`.
- Marks missing required key/input/filter fields with role-specific checks such as `key.missing:id`.
- Keeps the preview non-mutating: no server update is executed and disabled actions remain disabled.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- Focused `NoCodeRuntimeTest`
  - `8 tests, 117 assertions`
- `make sample28-no-code-runtime-ui-smoke`
  - verified `intentDraftStates` includes `blocked`
  - verified `draftAfterEditChecks` includes `action.disabled`
  - verified `draftAfterEditChecks` includes `key.missing:id`
- Full `make test`
  - `327 tests, 10814 assertions, skipped 1`

No push was performed.
