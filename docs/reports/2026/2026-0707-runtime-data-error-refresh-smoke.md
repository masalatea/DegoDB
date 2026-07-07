# Runtime Data Error Refresh Smoke

Date: 2026-07-07

## Summary

#404 adds browser-smoke coverage for the existing runtime-data refresh failure path.

The generated public runtime UI already reports a read-only `runtime-data.json` refresh failure as non-mutating: the current preview data is left unchanged. This slice makes that behavior explicit in the shared public runtime browser smoke by forcing one current/alias runtime-data response to fail and asserting both the status wording and preserved rendered rows.

## Scope

- Change area: `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- Coverage target: current/alias generated runtime-data controls
- Behavior target: forced read-only `runtime-data.json` failure after a successful current/alias refresh

## Accepted Coverage

- The smoke can force one runtime-data fetch to return `ok:false` with `forced_runtime_data_error`.
- The generated status text includes:
  - `Fresh runtime data could not be loaded from the read-only runtime-data endpoint`
  - `forced_runtime_data_error`
  - `Current preview data was left unchanged.`
- The refresh status state is `error`.
- The rendered list row count before and after the forced failure is unchanged.
- The forced failure runs after query reset, so the failure probe is isolated from active search/filter/sort state.

## Preserved Boundaries

- No endpoint behavior changed.
- No generated runtime production UI code changed.
- No sample data changed.
- No `runtime-data.json` contract changed.
- Mutation, sync outbox polling, URL/query behavior, and immutable artifact-key preview behavior remain unchanged.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The public runtime smoke passed through sample28, sample29, and sample31 `ok: true` outputs. Full `make test` was not rerun because this change is limited to shared browser-smoke coverage and the cross-profile public runtime smoke matrix passed.

## Notes

This is a good separate commit from #401 because #401 proves active query summaries survive empty successful reads, while #404 proves failed read-only refreshes keep already-rendered data in place.
