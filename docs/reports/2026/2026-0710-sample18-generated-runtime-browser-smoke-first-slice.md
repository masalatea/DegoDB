# Sample18 Generated Runtime Browser Smoke First Slice

Date: 2026-07-10

Status: `FIRST_SLICE_DONE`

## Summary

#693 adds the first narrow browser smoke assertion for the generated sample18 no-code runtime preview. The smoke now verifies that rendered list rows expose row key markers and that the guarded generated-submit surface still reports disabled/default-safe execution plus blocked feedback.

This keeps mutation and broader generated availability disabled while giving an outer browser-level confirmation after the fast metadata, DOM, payload, and selected-key handoff contracts.

## Changes

- Extended `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js` so the disabled action surface probe checks generated list row identity:
  - `data-runtime-row-key` rows exist in the list screen;
  - the first row key matches the selected fixture key;
  - row keys are returned in the smoke result for easier diagnosis.
- Kept existing browser-smoke assertions for managed action keys, route-compatible submit URL, CSRF handoff, guarded click inventory, payload assembly, blocked response handling, disabled/default execution state, and generated-submit blocked feedback.
- Updated `docs/no-code-ui-testing.md` to record this browser smoke as the outer confirmation layer after fast contracts.
- Updated `docs/current-plans.md` so #693 is `FIRST_SLICE_DONE` and #694 lane closure is the next active step.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
  - PHPUnit fixture phase: `OK (28 tests, 1663 assertions)`
  - Browser probe: `rowKeyCount=4`, first row key `1801`, guarded generated submit result `blocked`, failure code `generated_submit_disabled`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 412, Assertions: 13518, Skipped: 1.`

## Decision

Accept the first generated runtime browser-smoke slice as a narrow confidence check. Do not enable mutation or broad generated availability from this slice. Promote lane closure next to decide whether the following work is availability expansion, another browser edge, or sample conversion closure.
