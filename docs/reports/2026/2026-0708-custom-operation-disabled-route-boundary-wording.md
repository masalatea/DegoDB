# Custom Operation Disabled Route Boundary Wording

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#461 clarifies disabled custom operation UI wording now that route-boundary metadata exists.

Generated operator action panels can now show that a route boundary is declared while execution is still disabled. This keeps the metadata/execution separation visible to operators and adapter reviewers.

## Implemented

- Enriched extension-slot action items with matched custom operation `route_boundary` metadata.
- Rendered route-boundary readiness text under disabled operator action buttons when method/path/auth guard metadata exists.
- Added stable `data-extension-slot-route-boundary` marker.
- Added Mtool dogfooding inspection coverage for the rendered route-boundary marker.
- Extended `NoCodeScreenDefinitionTest` for runtime JSON action item metadata and generated HTML wording.

## Preserved Boundary

- Operator action buttons remain disabled.
- No custom operation execution route is added.
- No build, publish, review-request, approval, rollback, mutation, dispatch, or custom component execution is added.
- Route-boundary wording is descriptive only; it does not grant execution rights.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 160 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11314, Skipped: 1.`
- `git diff --check`

## Next Candidate

Define the policy/auth/CSRF/audit/idempotency route boundary for `request_source_output_publish` before any publish or approval execution path is enabled.
