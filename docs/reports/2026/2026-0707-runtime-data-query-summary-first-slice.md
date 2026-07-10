# Runtime Data Query Summary First Slice

Date: 2026-07-07

## Summary

#387 adds a generated runtime-data query summary to current/alias public runtime controls.

After live `runtime-data.json` fetches, generated controls now show the active query state in a compact text summary:

- Search query
- Field filters and operators
- Sort fields and directions
- Page size

After Clear, the summary returns to:

`No runtime data query applied.`

## Scope

Changed:

- Generated runtime preview controls now include a query summary element.
- Browser-side runtime-data control syncing updates that summary from the latest query and pagination metadata.
- The public runtime browser smoke now asserts combined search/filter/sort/page-size summary text and reset summary text.

Unchanged:

- `runtime-data.json` contract.
- Current/alias/artifact runtime routes.
- Sample data.
- Query parsing semantics.
- Mutation behavior.
- Sync outbox behavior.

## Verification

Passed:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun because this slice is limited to generated runtime UI state display plus browser smoke assertions, and the full sample28/29/31 public runtime browser matrix passed.

## Status

Done locally. Push was not performed.
