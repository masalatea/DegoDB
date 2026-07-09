# Request Publish Route Boundary Inventory

Date: 2026-07-08

Status: `DONE`

## Summary

#462 records the route boundary inventory for the existing custom operation key `request_source_output_publish`.

This remains non-executing. No route implementation, mutation, build, publish, review-request, approval transition, or custom component execution is added.

## Operation

- Operation key: `request_source_output_publish`
- Label: `Request Publish`
- Category: `publish`
- Target: `source_output`
- Side-effect class: `approval_transition`
- Policy key: `source_output.publish_request`
- CSRF required: `true`
- Audit event: `mtool.source_output.publish_requested`
- Adapter handoff: `source_output_publish_request`

## Proposed Route Boundary

- Method: `POST`
- Path: `/projects/{project_key}/source-outputs/{source_output_key}/operations/request-source-output-publish`
- Request origin: Mtool operator/admin UI or generated no-code adapter handoff.
- Response shape: redirect back to Source Output detail for HTML form requests; JSON `ok/error/status` shape only if a future adapter route explicitly requests JSON.
- CSRF: required for browser-origin requests using existing `app_csrf_token()` / `app_verify_csrf_token()` pattern.
- Authorization: require an authenticated Mtool operator/admin principal and the `source_output.publish_request` policy for the project/source-output target.

## Audit Boundary

- Event type: `mtool.source_output.publish_requested`
- Target type: `source_output`
- Target key: `{project_key}:{source_output_key}`
- Result values: `accepted`, `blocked`, `unauthorized`, `invalid`, `stale`, or `duplicate`.
- Minimum metadata:
  - `operation_key`
  - `project_key`
  - `source_output_key`
  - `artifact_key`
  - `source_output_dir`
  - `adapter_handoff`
  - `policy_key`
  - `requested_transition`

## Idempotency And Failure Boundary

- Duplicate request: reuse an existing open publish request or return an already-requested state; do not create parallel approval transitions for the same artifact.
- Missing Source Output: fail closed with `404`.
- Unauthorized principal: fail closed with `403` and audit `unauthorized` only if an authenticated principal is known.
- Missing CSRF: fail closed with `400` or existing form-error handling.
- Missing current artifact: fail closed with `blocked`.
- Stale artifact selection: fail closed with `stale`; do not silently request publish for a newer artifact.
- Disabled operation metadata: keep generated buttons disabled and show the existing unavailable reason until implementation changes availability.

## Adapter Boundary

- Generated HTML may carry the operation key, policy key, audit event, adapter handoff, route boundary, and unavailable reason.
- React bridge may carry the same handoff metadata through `custom_operation_handoffs`.
- Neither generated HTML nor React bridge should invent execution rights.
- A future implementation must update operation availability separately from adding the route.

## Out Of Scope

- POST route implementation.
- Approval transition mutation.
- Publish candidate creation or promotion.
- Build output publishing.
- Custom React component execution.
- Cross-system publish dispatch.

## Next Candidates

- Attach this inventory as structured route-boundary metadata to `custom_operations`.
- Add disabled UI wording that specifically names publish-request route readiness.
- Implement the POST route only after permission guard, CSRF verification, audit append, duplicate handling, and stale-artifact checks are testable.
