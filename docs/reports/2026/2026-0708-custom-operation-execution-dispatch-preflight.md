# Custom Operation Execution Dispatch Preflight

Date: 2026-07-08

Status: `DONE`

## Summary

#465 defines the execution preflight for no-code custom operations.

This does not implement a POST route. It records the shared dispatch boundary that must exist before `review_source_output_artifact` or `request_source_output_publish` can move from disabled metadata to executable behavior.

## Existing Foundations

- Managed operation policy already supports operation status, permission key, required roles, required scopes, required claims, and contract storage checks.
- Managed operation execution already has a plan-only preparation path before mutation.
- Audit log append/fetch already exists and redacts secret-like metadata.
- No-code custom operation metadata already carries policy key, CSRF requirement, audit event, adapter handoff, route boundary, unavailable reason, and disabled UI state.

## Shared Dispatch Boundary

A future custom operation dispatch route should:

- Resolve `project_key`, `source_output_key`, and `operation_key` from the POST route and request body.
- Load the canonical Source Output and the current no-code custom operation metadata.
- Reject unknown operation keys before policy or mutation.
- Require browser-origin CSRF verification when `csrf_required` is true.
- Require an authenticated Mtool operator/admin principal.
- Evaluate the declared policy key against the project/source-output target.
- Confirm the operation is available before executing; `availability: deferred` must stay non-executing.
- Validate artifact identity where the operation targets an artifact.
- Reject stale artifact requests instead of silently moving to a newer artifact.
- Append an audit event for accepted, blocked, unauthorized, invalid, stale, and duplicate outcomes.
- Return an HTML redirect by default, with JSON only for an explicitly separate adapter route.

## First Route Candidate

First candidate: `review_source_output_artifact`.

Reason:

- It is an external handoff side effect, not an approval transition mutation.
- It can begin as a plan-only accepted/blocked route before creating a review workflow.
- Its stale-artifact and missing-artifact checks are easier to reason about than publish approval state.
- It gives the generated disabled UI a concrete path to become enabled without also solving publish promotion.

`request_source_output_publish` should remain deferred until the review route proves the shared dispatch boundary, because publish requests introduce approval-state duplication and transition semantics.

## First Implementation Slice Candidate

The first implementation slice should be narrow:

- Add route matching for `POST /projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact`.
- Add a dispatch helper that accepts operation metadata and request context.
- Keep mutation disabled; return blocked/plan-only until availability is explicitly changed.
- Add tests for unknown operation, missing CSRF, unauthorized principal, deferred availability, missing artifact, stale artifact, and accepted plan-only response.
- Add audit assertions for blocked and accepted plan-only outcomes.

## Out Of Scope

- Creating a review workflow.
- Enabling publish requests.
- Mutating Source Output or artifact state.
- Cross-system adapter execution.
- Custom React component execution.

## Next Candidate

Promote a code-backed first slice for a plan-only `review_source_output_artifact` dispatch helper and route guard tests, while keeping the generated operation button disabled until availability is intentionally changed.
