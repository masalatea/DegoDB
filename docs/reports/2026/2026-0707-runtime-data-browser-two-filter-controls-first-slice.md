# Runtime Data Browser Two Filter Controls First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#280 replans after the endpoint multi-filter contract closure and chooses generated browser two-filter controls as the next small UI slice. #281 implements the first slice.

The endpoint can already handle bounded multi-field filters. This slice exposes a small part of that capability in generated current/alias runtime previews by adding one secondary filter field/value pair to the existing runtime-data control row.

## Planned / Implemented

- Add `Filter 2` and `Value 2` generated controls next to the existing single-filter controls.
- Send up to two distinct `filter[field]=value` clauses through the existing read-only `runtime-data.json` refresh path.
- Preserve the primary `filterField` / `filterValue` behavior for existing single-filter requests.
- Restore primary and secondary filter controls from returned `query.filter` metadata after screen re-render.
- Extend generated HTML coverage and browser smoke coverage for secondary filter controls.

## Boundary

- In scope: generated browser controls for up to two filters.
- Out of scope: endpoint contract changes, URL initial-replay multi-filter expansion, `pushState` / `popstate`, typed filter operators, more than two generated filter rows, multi-column sort, mutation behavior, artifact-key preview changes, and push.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
  - Confirms generated current/alias browser controls can submit a two-field runtime-data filter:
    `filter[status]=triage&filter[priority]=20`.
- `make test`
  - `337 tests`, `11126 assertions`, `1 skipped`.
