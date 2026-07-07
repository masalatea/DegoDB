# Runtime Data Query Summary Label Polish

Date: 2026-07-07

## Summary

#388 polishes the generated runtime-data query summary added in #387.

The query summary now uses rendered field labels from the generated controls for filter and sort entries. For example, it displays `Status contains triage` instead of `status contains triage`.

## Scope

Changed:

- Build a runtime field-label map from generated filter/sort select options.
- Render filter and sort summary entries with field labels.
- Keep smoke expectations explicit that summary display uses labels.

Unchanged:

- URL query parameters still use field keys.
- Runtime-data endpoint parsing remains field-key based.
- `runtime-data.json` contract is unchanged.
- Sample data is unchanged.
- Mutation and sync outbox behavior are unchanged.

## Verification

Passed:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun because this slice only changes generated runtime UI display wording and browser smoke expectations, and the full sample28/29/31 public runtime browser matrix passed.

## Status

Done locally. Push was not performed.
