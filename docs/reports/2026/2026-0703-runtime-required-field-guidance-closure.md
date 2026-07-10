# Runtime Required Field Guidance Closure

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

The runtime required-field guidance lane is closed for the current no-code first slice. The generated runtime form now shows the required input contract directly beside required labels, before a user needs to inspect the `Action Intent Draft` summary or detailed JSON.

This is a UI clarity and accessibility slice. It does not change the non-mutating preview boundary and does not enable server-backed action execution.

## Accepted Capability

- Required generated form fields show a compact `Required` badge.
- Required generated form fields include a short hint: `Required for the generated action intent.`
- Required controls are linked to their hint through `aria-describedby`.
- The sample28 browser smoke verifies that rendered required controls have matching badges, hints, and descriptions.
- The existing draft summary, copy control, JSON disclosure, and disabled-policy behavior remain unchanged.

## Verification Baseline

- `php -l mtool/app/no_code_runtime.php`: passed.
- Focused `NoCodeRuntimeTest`: `8 tests, 145 assertions`.
- sample28 artifact PHPUnit: `1 test, 8 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed, with `requiredBadgeCount: 2`, `requiredHintCount: 2`, and `requiredControlsWithDescriptions: 2`.
- Full Integration PHPUnit on a clean buildless sample01 stack: `327 tests, 10842 assertions, skipped 1`.

## Remaining Candidates

- Highlight missing required fields dynamically as a user edits the form.
- Add richer per-field validation wording that mirrors draft checks.
- Decide when to cross from local non-mutating intent draft to server-backed action execution.
- Add another no-code scenario or sample after the current sample28 path is stable.
- Review local commit grouping and push timing after the current closure is accepted.

Push was not performed for this slice.
