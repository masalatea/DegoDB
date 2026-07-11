# Review Artifact Route Boundary Inventory

Date: 2026-07-08

Status: `DONE`

## Summary

#458 records the route boundary inventory for the existing custom operation key `review_source_output_artifact`.

This remains non-executing. No route implementation, mutation, build, publish, review-request, approval transition, or custom component execution is added.

## Operation

- Operation key: `review_source_output_artifact`
- Label: `Review Artifact`
- Category: `review_request`
- Target: `artifact`
- Side-effect class: `external_handoff`
- Policy key: `source_output.review`
- CSRF required: `true`
- Audit event: `mtool.source_output.artifact_review_requested`
- Adapter handoff: `source_output_artifact_review`

## Proposed Route Boundary

- Method: `POST`
- Path: `/projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact`
- Request origin: Mtool operator/admin UI or generated no-code adapter handoff.
- Response shape: redirect back to Source Output detail for HTML form requests; JSON `ok/error/status` shape only if a future adapter route explicitly requests JSON.
- CSRF: required for browser-origin requests using existing `app_csrf_token()` / `app_verify_csrf_token()` pattern.
- Authorization: require an authenticated Mtool operator/admin principal and the `source_output.review` policy for the project/source-output target.

## Audit Boundary

- Event type: `mtool.source_output.artifact_review_requested`
- Target type: `source_output_artifact`
- Target key: `{project_key}:{source_output_key}:{artifact_key}`
- Result values: `accepted`, `blocked`, `unauthorized`, `invalid`, or `stale`.
- Minimum metadata:
  - `operation_key`
  - `project_key`
  - `source_output_key`
  - `artifact_key`
  - `source_output_dir`
  - `adapter_handoff`
  - `policy_key`

## Idempotency And Failure Boundary

- Duplicate request: safe to repeat while no review workflow is created; future implementation should either reuse an existing open review request or return an already-requested state.
- Missing Source Output: fail closed with `404`.
- Unauthorized principal: fail closed with `403` and audit `unauthorized` only if an authenticated principal is known.
- Missing CSRF: fail closed with `400` or existing form-error handling.
- Missing artifact: fail closed with `blocked`.
- Stale artifact selection: fail closed with `stale`; do not silently review a newer artifact.
- Disabled operation metadata: keep generated buttons disabled and show the existing unavailable reason until implementation changes availability.

## Adapter Boundary

- Generated HTML may carry the operation key, policy key, audit event, adapter handoff, and unavailable reason.
- React bridge may carry the same handoff metadata through `custom_operation_handoffs`.
- Neither generated HTML nor React bridge should invent execution rights.
- A future implementation must update operation availability separately from adding the route.

## Next Candidates

- Attach this inventory as structured metadata to `custom_operations`.
- Add disabled UI wording that specifically names policy/CSRF/audit readiness.
- Implement the POST route only after permission guard, CSRF verification, audit append, and stale-artifact checks are testable.
