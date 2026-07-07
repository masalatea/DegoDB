# Runtime-data filter validation copy polish closure

Date: 2026-07-07

## Summary

#379 closes the runtime-data filter validation copy polish lane.

Generated current/alias runtime-data filter validation errors now name the affected row, field label, and expected format before fetch. This is accepted as the first field-aware local validation copy layer.

## Accepted Capability

- Local validation messages identify the filter row.
- Local validation messages identify the selected field label.
- Local validation messages include expected format copy.
- Endpoint validation remains authoritative and fail-closed.

## Verification Baseline

The implementation baseline is #378:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #378 because the code change was limited to generated browser validation copy and the sample31 public runtime browser smoke asserted the touched message.

## Remaining Candidates

These are useful but separate future work:

- broader datetime/time native sample coverage;
- cross-profile public runtime browser smoke promotion;
- local stack review before the next push boundary.

## Push Status

No push was performed for #379.
