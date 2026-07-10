# Runtime-data native typed filter controls plan

Date: 2026-07-07

## Summary

#371 chooses native typed runtime-data filter controls as the next small product-facing slice after typed filter value hints.

The previous lane exposed the accepted value format through placeholder/title text. The next slice should also set the browser-native input type from the selected field type, without changing endpoint validation or query contracts.

## Selected First Slice

Update generated current/alias runtime-data filter value inputs dynamically:

- `integer`: `type="number"` with integer step.
- `number`: `type="number"`.
- `date`: `type="date"`.
- `datetime`: `type="datetime-local"` with seconds enabled.
- `time`: `type="time"` with seconds enabled.
- other fields: `type="text"`.

The behavior should apply to primary, secondary, and tertiary filter rows.

## Out Of Scope

- Endpoint contract changes.
- New accepted date/time formats.
- Timezone normalization.
- Client-side validation before fetch.
- URL replay/history replay behavior changes.
- Artifact-key preview behavior changes.
- Mutation, retry, outbox processing, or status polling changes.

## Verification Plan

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

## Push Status

No push was performed for #371.
