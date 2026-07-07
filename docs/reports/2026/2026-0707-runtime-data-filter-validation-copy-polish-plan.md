# Runtime-data filter validation copy polish plan

Date: 2026-07-07

## Summary

#377 chooses validation copy polish for generated runtime-data filter errors.

The previous lane added browser-side local validation before fetch. The next slice should keep that behavior unchanged but make the local error message more actionable by naming the filter row, selected field label, and expected format.

## Planned Scope

- Keep endpoint validation authoritative and fail-closed.
- Keep URL replay, history replay, and direct endpoint requests server-validated.
- Keep the same generated filter validation timing before fetch.
- Update local validation copy to include:
  - filter row label;
  - selected field label;
  - expected format.

## Verification Target

The first implementation should run:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` can remain deferred if the code change is limited to generated browser validation copy and the sample31 public runtime browser smoke asserts the touched message.

## Push Status

No push was performed for #377.
