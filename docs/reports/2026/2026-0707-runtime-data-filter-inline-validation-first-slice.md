# Runtime-data filter inline validation first slice

Date: 2026-07-07

## Summary

#375 implements the first runtime-data filter inline validation slice.

Generated current/alias runtime-data filter controls now validate populated filter rows before fetching `runtime-data.json`. Invalid typed values stop the fetch locally and show a runtime-data error status, while the endpoint remains the authoritative fail-closed boundary.

## Changes

- Added browser-side validation helpers for generated runtime-data filter values.
- Validates populated primary, secondary, and tertiary filter rows.
- Uses native input validity where available.
- Adds explicit contract checks for:
  - integer values;
  - number values;
  - date values;
  - datetime values;
  - time values.
- Applies validation before page, search, filter, and sort fetch paths reuse active filters.
- Extends the sample31 public runtime browser smoke to assert an invalid typed numeric filter is stopped before fetch and reports a local error status.

## Preserved Boundaries

- Endpoint validation remains authoritative and fail-closed.
- URL replay and history replay remain server-validated.
- Direct `runtime-data.json` requests remain server-validated.
- Artifact-key preview behavior remains static.
- Mutation, retry, outbox processing, and status polling are unchanged.
- Empty filter values continue to mean "no filter row".

## Verification

Completed verification:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #375 because the code change is limited to generated browser-side filter validation, and the sample31 public runtime browser smoke covers the touched runtime-data filter path.

## Push Status

No push was performed for #375.
