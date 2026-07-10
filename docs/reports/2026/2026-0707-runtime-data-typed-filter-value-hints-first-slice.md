# Runtime-data typed filter value hints first slice

Date: 2026-07-07

## Summary

#369 implements the first runtime-data typed filter value hints slice.

Generated current/alias runtime-data filter value inputs now expose field-type-aware placeholder/title text so users can see the expected value shape before submitting a strict read-only filter.

## Changes

- Added runtime helper logic that maps selected filter field type to value hints.
- Primary, secondary, and tertiary filter rows all sync their value input placeholder/title.
- Existing type-driven ordered operator sync now also updates the value hint.
- Extended the sample31 public runtime browser smoke to assert:
  - string fields keep `Text value`;
  - the typed `date` field exposes `YYYY-MM-DD` in placeholder/title.

## Hint Contract

- `integer`: `Integer value`
- `number`: `Numeric value`
- `date`: `YYYY-MM-DD`
- `datetime`: `YYYY-MM-DDTHH:MM:SS`
- `time`: `HH:MM:SS`
- other fields: `Text value`

## Unchanged

- Endpoint contracts are unchanged.
- URL replay/history replay behavior is unchanged.
- Artifact-key preview behavior is unchanged.
- Mutation, retry, outbox processing, and status polling are unchanged.
- Date/time accepted formats are unchanged.

## Verification

Completed verification:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #369 because the code change is limited to generated browser value hints and the shared public runtime browser smoke matrix covers the touched behavior across sample28, sample29, and sample31.

## Push Status

No push was performed for #369.
