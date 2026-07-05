# Runtime Required Field Validation Wording Closure

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

The runtime required-field validation wording lane is closed for the current no-code first slice. Required hints now tell the user whether the required value is present or missing, and the message names both the action field role and rendered field label.

This improves form-editing feedback without changing the preview's non-mutating action-intent boundary.

## Accepted Capability

- Required hints carry the rendered form label through `data-required-label`.
- Browser-local required hint updates include the selected action field role.
- Missing state can read like `Missing required input value for generated action intent: Body.`
- Present state can read like `Required input value is present for generated action intent: Body.`
- Existing required badges, `aria-describedby` linkage, draft summary, copy control, JSON disclosure, and fail-closed dispatch behavior remain unchanged.

## Verification Baseline

- Container PHP lint: `php -l /var/www/mtool/app/no_code_runtime.php` passed.
- Focused `NoCodeRuntimeTest`: `8 tests, 148 assertions`.
- sample28 pack/runtime PHPUnit portion: `1 test, 8 assertions`.
- sample28 runtime UI smoke passed via bundled Node after local PATH lacked `node`.
- Full `make test`: `327 tests, 10845 assertions, skipped 1`.

## Remaining Candidates

- Decide whether the next product-facing lane should be server-backed action execution or another no-code scenario.
- Design broader validation wording beyond required present/missing state.
- Review local commit grouping before the next push.
- Push only after explicit approval.

Push was not performed for this slice.
