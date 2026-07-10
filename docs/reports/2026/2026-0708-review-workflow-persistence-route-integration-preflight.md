# Review Workflow Persistence Route Integration Preflight

Date: 2026-07-08

Status: `DONE`

## Summary

#476 defines how the existing `review_source_output_artifact` POST route may connect to the review workflow repository added in #475.

This is a decision slice only. It does not call the repository from the route, does not change operation availability, and does not enable generated buttons.

## Route Integration Rule

The route must remain guard-first.

Allowed persistence path:

1. POST route resolves project/source output/principal.
2. Dispatch preflight validates operation identity, auth guard, CSRF, source output, policy, availability, and artifact freshness.
3. Repository persistence may run only when the preflight result is allowed and has result `accepted_plan`.
4. The repository creates a review request or reuses an existing open request.
5. Audit append records `accepted` or `duplicate` after persistence result is known.

Blocked path:

1. Deferred, unauthorized, missing CSRF, missing artifact, stale artifact, unknown operation, or policy failure never call the repository.
2. These outcomes continue to append the guard audit event only.
3. The rendered result must continue to state that no mutation was executed.

## Next Implementation Shape

The next code-backed slice should add a route-local helper that receives an already allowed preflight result and calls `app_no_code_review_workflow_create_or_reuse_request()`.

Focused coverage should prove:

- Deferred availability still does not persist a review request.
- Accepted-plan input persists a review request.
- Duplicate accepted-plan input returns duplicate/reused request.
- Audit metadata includes `review_request_key` after persistence.
- Generated buttons and dogfooding metadata remain disabled/deferred by default.

## Out Of Scope

- Changing custom operation availability to `available`.
- Calling persistence from the default dogfooding route path while availability is still deferred.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.
- Generated HTML / React bridge execution.

## Verification

#476 is docs-only.

- `git diff --check`

## Next Candidate

#477 should implement the route-local persistence helper and focused tests under an accepted-plan fixture, while keeping the default dogfooding operation availability deferred.
