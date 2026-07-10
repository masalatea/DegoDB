# Review Artifact Route Boundary Metadata Carry-Through

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#459 carries the `review_source_output_artifact` route boundary inventory into custom operation metadata and generated adapter handoffs.

This remains metadata-only. No execution route, dispatch, mutation, build, publish, review-request, approval transition, or custom component execution is added.

## Implemented

- Added `route_boundary` metadata to the Mtool dogfooding `review_source_output_artifact` custom operation.
- Normalized `route_boundary` through no-code screen definitions.
- Carried the boundary into runtime preview JSON via the existing custom operation path.
- Added `route_boundary` to React bridge `custom_operation_handoffs`.
- Added generated TypeScript type `MtoolCustomOperationRouteBoundary`.
- Added route boundary data to Mtool dogfooding inspection summary.
- Extended `NoCodeScreenDefinitionTest` assertions for screen-definition and React bridge handoff metadata.

## Route Boundary Shape

- `method`
- `path`
- `response_shape`
- `auth_guard`
- `idempotency`
- `failure_modes`

## Preserved Boundary

- Generated operator action buttons remain disabled.
- Generated HTML and React bridge handoffs do not gain execution rights.
- No router entry is added.
- No CSRF verification, permission guard, audit append, or stale-artifact check is implemented yet.
- `request_source_output_publish` remains without route boundary metadata until its own inventory is selected.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 155 assertions)`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11309, Skipped: 1.`
- `git diff --check`

## Next Candidates

- Add disabled UI wording that specifically names route-boundary readiness.
- Add route boundary metadata for `request_source_output_publish`.
- Implement the POST route only after permission guard, CSRF verification, audit append, and stale-artifact checks are testable.
