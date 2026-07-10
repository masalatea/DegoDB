# Runtime-data filter validation copy polish first slice

Date: 2026-07-07

## Summary

#378 implements the first runtime-data filter validation copy polish slice.

Generated current/alias runtime-data filter validation errors now identify the affected filter row, selected field label, and expected format. This keeps the same local validation behavior from #375 but makes the error actionable before users reach the endpoint contract.

## Changes

- Added a generated runtime helper to read the selected filter field label.
- Updated typed filter validation messages to include expected format copy.
- Updated local fetch-stop status messages to include:
  - filter row label;
  - selected field label;
  - expected format.
- Extended the sample31 public runtime browser smoke to assert the field-aware validation copy.

## Preserved Boundaries

- Endpoint validation remains authoritative and fail-closed.
- URL replay and history replay remain server-validated.
- Direct `runtime-data.json` requests remain server-validated.
- Validation timing before fetch is unchanged.
- Artifact-key preview behavior, mutation, retry, outbox processing, and status polling are unchanged.

## Verification

Completed verification:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #378 because the code change is limited to generated browser validation copy, and the sample31 public runtime browser smoke asserts the touched message.

## Push Status

No push was performed for #378.
