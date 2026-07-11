# Review Workflow Persistence Failure Visibility Coverage

Date: 2026-07-09

Status: `FIRST_SLICE_DONE`

## Summary

#483 adds focused coverage for the route-local review workflow persistence failure path.

This does not enable availability, generated buttons, approval transitions, publish routes, or adapter execution.

## Covered Behavior

- Accepted-plan persistence failure marks the route result as `failed`.
- Status code becomes `500`.
- Failure code becomes `review_request_persistence_failed`.
- Audit event result becomes `invalid`.
- Audit event metadata carries `failure_code: review_request_persistence_failed`.
- Result page renders review request status `failed`.
- Result page keeps the no-mutation message for failed persistence.

## Out Of Scope

- Changing `review_source_output_artifact` availability to `available`.
- Browser route execution against default dogfooding metadata.
- Generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.
- Push.

## Verification

- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit route persistence helper failure visibility: `OK (4 tests, 28 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 361, Assertions: 11457, Skipped: 1.`
- `git diff --check`

## Next Candidate

#484 should close the failure visibility slice and decide whether any further non-executable hardening is useful while availability remains parked.
