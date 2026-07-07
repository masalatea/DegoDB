# Runtime-data native typed filter controls closure

Date: 2026-07-07

## Summary

#373 closes the native typed runtime-data filter controls lane.

Generated current/alias runtime-data filter value controls now use native browser input types from the selected field type. This completes the UI guidance slice that started with placeholder/title hints and keeps endpoint validation as the final contract boundary.

## Accepted Capability

- Text fields keep `type="text"`.
- Integer fields use `type="number"` with `step="1"` and decimal input mode.
- Number fields use `type="number"` with `step="any"` and decimal input mode.
- Date fields use `type="date"`.
- Datetime fields use `type="datetime-local"` with `step="1"`.
- Time fields use `type="time"` with `step="1"`.
- Placeholder/title hints from #369 remain in place.
- Type-driven ordered operator gating remains in place.

## Preserved Boundaries

- Endpoint validation remains strict and fail-closed.
- URL replay and history replay behavior are unchanged.
- Artifact-key preview behavior remains static.
- Mutation, retry, outbox processing, and status polling are unchanged.
- Date/time accepted formats remain governed by the endpoint contract.

## Verification Baseline

The implementation baseline is #372:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #372 because the code change was limited to generated browser filter input type syncing, and sample31 covered the touched text, number, and date runtime-data filter control behavior.

## Remaining Candidates

These are useful but separate future contract/UI changes:

- inline validation before fetch;
- localized or generated-contract-driven format copy;
- broader datetime/time native sample coverage;
- cross-profile public runtime browser smoke promotion;
- local stack review before the next push boundary.

## Push Status

No push was performed for #373.
