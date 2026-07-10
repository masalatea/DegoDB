# Runtime-data empty-result query summary smoke

Date: 2026-07-07

## Summary

#401 adds browser-smoke coverage for generated runtime-data query summaries when a read-only query returns zero rows.

## What changed

The shared no-code runtime preview browser smoke now performs a deterministic no-match search after the normal successful search:

- Query: `__no_runtime_data_match__`
- Expected response: `200`
- Expected rendered rows: `0`
- Expected empty rows: at least `1`
- Expected query summary: active summary remains visible and includes the search term plus `Rows: 0`
- Expected accessibility summary: `aria-label` also includes `Rows: 0`

This locks the current generated UX behavior without changing runtime behavior.

## Preserved boundary

- Runtime-data endpoint contracts are unchanged.
- URL/query behavior is unchanged.
- Sample seed data is unchanged.
- Mutation, submit, sync outbox, and artifact-key preview behavior are unchanged.

## Verification

Passed:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The umbrella public runtime smoke passed through sample28, sample29, and sample31 `ok: true` outputs. The sample31 alias output included `runtimeDataEmptySearch` with `renderedRowCount: 0`, `emptyRowCount: 1`, and `Rows: 0` in both summary text and `aria-label`.

Full `make test` was not rerun because the change is limited to browser-smoke coverage and the cross-profile public runtime smoke matrix passed.

## Push / history

Push was not performed. History was not rewritten.
