# Runtime-data filter inline validation closure

Date: 2026-07-07

## Summary

#376 closes the runtime-data filter inline validation lane.

Generated current/alias runtime-data filter controls now stop obvious invalid typed filter values before fetch. This is a browser-side usability layer only; endpoint validation remains the authoritative fail-closed contract.

## Accepted Capability

- Populated generated filter rows are validated before page/search/filter/sort fetch paths reuse active filters.
- Validation uses native input validity where available.
- Validation includes explicit contract checks for:
  - integer;
  - number;
  - date;
  - datetime;
  - time.
- Invalid values show a local runtime-data error status and do not fetch.

## Verification Baseline

The implementation baseline is #375:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #375 because the code change was limited to generated browser-side filter validation and the sample31 public runtime browser smoke covered the touched runtime-data filter path.

## Remaining Candidates

These are useful but separate future work:

- localized or generated-contract-driven validation copy;
- broader datetime/time native sample coverage;
- cross-profile public runtime browser smoke promotion;
- local stack review before the next push boundary.

## Push Status

No push was performed for #376.
