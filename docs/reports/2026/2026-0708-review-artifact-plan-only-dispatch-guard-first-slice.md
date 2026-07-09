# Review Artifact Plan-Only Dispatch Guard First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#466 adds the first code-backed custom operation dispatch guard slice for `review_source_output_artifact`.

This still does not add an HTTP route, generated button execution, review workflow creation, approval transition, publish mutation, or custom component execution.

## Implemented

- Added `mtool/app/no_code_custom_operation_dispatch.php`.
- Added a pure preflight helper for custom operation dispatch requests.
- Added guard handling for:
  - invalid request shape
  - unknown operation key
  - unauthenticated principal
  - unsupported auth guard
  - missing CSRF
  - missing Source Output
  - Source Output key mismatch
  - project policy denial/error
  - deferred operation availability
  - missing artifact
  - stale artifact
  - accepted plan-only dispatch
- Added audit event input generation without writing audit records.
- Registered `source_output.review` and `source_output.publish_request` as project permission capabilities.
- Added focused integration coverage in `NoCodeCustomOperationDispatchTest`.

## Boundary

- Current Mtool dogfooding metadata remains `availability: deferred`, so real metadata still blocks execution with `deferred_availability`.
- Tests can clone the operation as `availability: available` to prove the plan-only accepted path without enabling generated UI execution.
- The helper returns a plan-only response and audit event input; it does not append audit records yet.
- No router entry or page/controller wrapper is added.
- No mutation is added.

## Verification

- `php -l mtool/app/no_code_custom_operation_dispatch.php`
- `php -l mtool/app/project_permission.php`
- `php -l tests/Integration/NoCodeCustomOperationDispatchTest.php`
- Focused PHPUnit: `OK (6 tests, 54 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 351, Assertions: 11378, Skipped: 1.`
- `git diff --check`

## Next Candidate

Add a narrow HTTP route guard wrapper for `review_source_output_artifact` that calls the preflight helper, keeps mutation disabled, returns blocked/plan-only responses, and starts auditing blocked outcomes only after audit append semantics are covered.
