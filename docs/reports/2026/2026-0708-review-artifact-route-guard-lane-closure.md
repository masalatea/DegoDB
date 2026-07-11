# Review Artifact Route Guard Lane Closure

Date: 2026-07-08

Status: `DONE`

## Summary

#471 closes the `review_source_output_artifact` route guard lane.

The lane now has a narrow HTTP route wrapper, dispatch preflight guard, blocked/deferred rendering, audit append, and audit append failure visibility. It still does not enable generated button execution or review workflow mutation.

## Accepted Capability

- `review_source_output_artifact` has a dedicated POST route wrapper.
- The route is registered before the generic Source Output detail route.
- `request_source_output_publish` remains unrouted.
- The wrapper reuses Source Output route bootstrap for admin/config auth, project/source-output loading, and method enforcement.
- Dispatch preflight validates operation key, auth guard, CSRF, Source Output identity, policy, availability, artifact presence, and stale artifact state.
- Current Mtool dogfooding metadata remains `availability: deferred`, so real requests are blocked before plan-only acceptance.
- Result rendering exposes operation result, failure code, operation key, Source Output key, audit event type, audit append state, and no-mutation wording.
- Audit append records blocked/deferred review-artifact outcomes.
- Audit append failure is visible as telemetry state without converting a blocked guard result into mutation.

## Preserved Boundary

- No review workflow is created.
- No generated button execution is enabled.
- No Source Output or artifact state is mutated.
- No build, publish, review-request, approval transition, rollback, or custom component execution is added.
- No publish request route is added.
- No JSON adapter execution route is added.

## Decision

Keep availability enablement parked.

The next route-readiness work should be a separate replan/decision slice. Enabling `review_source_output_artifact` should require an explicit review workflow persistence boundary, idempotency semantics, UI availability change, and end-to-end route tests. `request_source_output_publish` should stay deferred until the review route's mutation boundary is proven.

## Verification

Latest code verification for this lane is #470:

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l tests/Integration/AuditLogRepositorySqliteTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- Focused PHPUnit audit append: `OK (3 tests, 23 assertions)`
- Focused PHPUnit route contract and guard smoke: `OK (26 tests, 1918 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 355, Assertions: 11406, Skipped: 1.`
- `git diff --check`

#471 itself is docs-only and uses `git diff --check`.

## Next Candidate

Run a local commit stack review before any push decision, unless a new priority explicitly promotes review workflow persistence.
