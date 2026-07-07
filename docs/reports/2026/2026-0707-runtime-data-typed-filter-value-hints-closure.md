# Runtime-data typed filter value hints closure

Date: 2026-07-07

## Summary

#370 closes the runtime-data typed filter value hints lane.

Generated current/alias runtime-data filter value inputs now show field-type-aware placeholder/title guidance for text, integer, number, date, datetime, and time fields. This gives users a visible format cue while preserving the strict endpoint validation and fail-closed semantics.

## Accepted Capability

- Primary, secondary, and tertiary generated filter value inputs update their placeholder/title from the selected field type.
- Text fields show `Text value`.
- Integer fields show `Integer value`.
- Number fields show `Numeric value`.
- Date fields show `YYYY-MM-DD`.
- Datetime fields show `YYYY-MM-DDTHH:MM:SS`.
- Time fields show `HH:MM:SS`.
- Existing type-driven ordered operator gating remains unchanged.

## Verification Baseline

The implementation baseline is #369:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #369 because the change was limited to generated browser value hints and the public runtime browser smoke matrix covered the touched behavior across sample28, sample29, and sample31.

## Remaining Candidates

These are useful but separate future contract/UI changes:

- native typed filter controls such as `input[type=date]`, `input[type=time]`, `input[type=datetime-local]`, or numeric inputs;
- inline validation before fetch;
- localized or generated-contract-driven format copy;
- per-field examples from sample/read-model metadata.

## Push Status

No push was performed for #370.
