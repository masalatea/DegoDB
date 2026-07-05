# Runtime Required Field Validation Wording First Slice

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

Generated no-code runtime required hints now include more specific wording during live feedback. When a required field is present or missing, the hint names the action field role, such as input value, and the rendered field label, such as Body.

This remains browser-local preview behavior. The generated runtime still builds and explains an action-intent draft without performing server mutation.

## Accepted Capability

- Required hints carry `data-required-label` from the rendered form label.
- Browser-local required hint updates use the selected action field role.
- Missing state now reads like `Missing required input value for generated action intent: Body.`
- Present state now reads like `Required input value is present for generated action intent: Body.`
- Existing required badge, `aria-describedby`, draft summary, copy, JSON disclosure, and fail-closed dispatch behavior remain unchanged.

## Verification

- Container PHP lint: `php -l /var/www/mtool/app/no_code_runtime.php` passed.
- Focused `NoCodeRuntimeTest`: `8 tests, 148 assertions`.
- sample28 pack/runtime PHPUnit portion: `1 test, 8 assertions`.
- sample28 runtime UI smoke passed via bundled Node after local PATH lacked `node`.
- Full `make test`: `327 tests, 10845 assertions, skipped 1`.

## Notes

The first `make sample28-no-code-runtime-ui-smoke` attempt completed the sample28 pack/runtime PHPUnit portion, then stopped because `node` was not on the shell PATH. The generated runtime browser smoke was rerun directly with the Codex bundled Node runtime and passed against `work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.html`.

Push was not performed for this slice.
