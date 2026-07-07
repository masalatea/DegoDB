# Runtime Data Query Summary Operator Label Polish

Date: 2026-07-07

## Summary

#389 polishes the generated runtime-data query summary so filter operators and sort directions use rendered control labels.

Examples:

- `Status Contains review`
- `Status Desc`
- `QuantityNeeded Asc`

The underlying query values remain field-key/operator tokens such as `status`, `contains`, `asc`, and `desc`.

## Scope

Changed:

- Runtime query summary label extraction now collects:
  - field labels
  - filter operator labels
  - sort direction labels
- Summary rendering uses those labels for display.
- Browser smoke expectations assert the rendered operator/direction labels.

Unchanged:

- URL query values.
- Runtime-data endpoint parsing.
- `runtime-data.json` contract.
- Sample data.
- Mutation behavior.
- Sync outbox behavior.

## Verification

Passed / completed with ok outputs:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun because this slice only changes generated runtime UI display wording and browser smoke expectations.

## Status

Done locally. Push was not performed.
