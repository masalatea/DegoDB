# Runtime-data native typed filter controls first slice

Date: 2026-07-07

## Summary

#372 implements the first native typed runtime-data filter controls slice.

Generated current/alias runtime-data filter value inputs now switch their browser-native input type from the selected field type, while keeping the strict endpoint contract and the existing placeholder/title hints from #369.

## Changes

- Added runtime helper logic that maps selected filter field type to native input type.
- Primary, secondary, and tertiary filter value inputs all sync:
  - `type`
  - `step`
  - `inputmode`
  - placeholder/title hints
- Extended the sample31 public runtime browser smoke to assert:
  - string fields keep `type="text"`;
  - the typed `date` field uses `type="date"`.

## Input Type Contract

- `integer`: `type="number"`, `step="1"`, decimal input mode.
- `number`: `type="number"`, `step="any"`, decimal input mode.
- `date`: `type="date"`.
- `datetime`: `type="datetime-local"`, `step="1"`.
- `time`: `type="time"`, `step="1"`.
- other fields: `type="text"`.

## Unchanged

- Endpoint contracts are unchanged.
- URL replay/history replay behavior is unchanged.
- Artifact-key preview behavior is unchanged.
- Mutation, retry, outbox processing, and status polling are unchanged.
- Date/time accepted formats and fail-closed behavior are unchanged.

## Verification

Completed verification:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #372 because the code change is limited to generated browser filter input type syncing, and sample31 covers the touched text, number, and date runtime-data filter control behavior.

## Push Status

No push was performed for #372.
