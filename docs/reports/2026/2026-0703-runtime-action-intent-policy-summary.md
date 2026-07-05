# 2026-0703 Runtime Action Intent Policy Summary

Status: `FIRST_SLICE_DONE`

## Summary

Extends the generated no-code runtime `Action Intent Draft` summary so blocked previews show policy failed checks as well as draft-level blockers.

The previous slice made the summary readable without opening JSON. This slice closes the next small clarity gap: when an action is disabled by policy, the summary now includes reasons such as `policy: principal.missing` next to draft blockers such as `action.disabled` or `key.missing:id`.

## Changes

- Adds `policy_failed_checks` to the compact draft summary.
- Treats a draft with policy failed checks as blocked even if no draft-level missing-field checks are present.
- Keeps the detailed JSON visible for inspection.
- Keeps the non-mutating preview boundary unchanged: disabled actions remain disabled and no server update is executed from the generated preview page.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- Focused `NoCodeRuntimeTest`: `8 tests, 122 assertions`
- `make sample28-no-code-runtime-ui-smoke`: passed and confirmed `draftSummaryAfterEdit` includes `Blocked draft: action.disabled, key.missing:id; policy: principal.missing`
- Full `make test`: `327 tests, 10819 assertions, skipped 1`
- `git diff --check`: passed

Push was not performed for this slice.
