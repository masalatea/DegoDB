# Runtime-data datetime/time native filter smoke closure

Date: 2026-07-07

## Summary

#382 closes the runtime-data datetime/time native filter smoke lane.

Sample31 public runtime browser smoke now covers generated filter-control metadata for text, numeric, date, datetime, and time field types without changing sample data or endpoint contracts.

## Accepted Coverage

- Text filter metadata remains covered.
- Numeric filter metadata remains covered.
- Date filter metadata remains covered.
- Datetime filter metadata is covered for native input type and format copy.
- Time filter metadata is covered for native input type and format copy.

## Verification Baseline

The implementation baseline is #381:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #381 because the change was limited to smoke coverage.

## Remaining Candidates

These are useful but separate future work:

- cross-profile public runtime browser smoke promotion;
- local stack review before the next push boundary.

## Push Status

No push was performed for #382.
