# Review Workflow Route Persistence Helper First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#477 adds a route-local persistence helper for `review_source_output_artifact` accepted-plan results.

The helper is guard-first: it skips every deferred, blocked, unauthorized, stale, invalid, or otherwise non-allowed result. When the dispatch preflight result is allowed and `accepted_plan`, it calls the review workflow repository, records a new review request or reuses an open one, and updates audit metadata with `review_request_key`.

Default dogfooding metadata still reports availability as deferred, so normal generated buttons and the current default route path do not execute persistence.

## Added Behavior

- `app_project_source_output_operation_apply_review_request_persistence()`.
- `app_project_source_output_operation_review_request_persistence_status()`.
- Result page review request status: `skipped`, `recorded`, `duplicate`, or `failed`.
- Accepted-plan persistence updates audit result to `accepted` or `duplicate`.
- Persistence failure marks the route result as failed before audit append.

## Coverage

Focused coverage proves:

- Deferred guard results do not persist review requests.
- Accepted-plan results persist review requests.
- Duplicate accepted-plan results reuse the existing open request.
- Audit metadata receives `review_request_key` after persistence.
- Existing route rendering / contract coverage still passes.

## Still Out Of Scope

- Changing custom operation availability to `available`.
- Enabling generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.
- Full browser-driven route mutation against default dogfooding metadata.

## Verification

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit route persistence helper: `OK (2 tests, 16 assertions)`
- Focused PHPUnit source output route contract: `OK (26 tests, 1918 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 359, Assertions: 11445, Skipped: 1.`
- `git diff --check`

## Next Candidate

#478 should close this helper lane and decide whether availability enablement remains parked or whether a separate route-level executable workflow slice is worth promoting.
