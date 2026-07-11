# Review Workflow Availability Surface Inventory

Date: 2026-07-09

Status: `DONE`

## Summary

#538 inventories the current review workflow availability surfaces before defining the availability gate matrix. This is a docs-only slice. It does not enable generated button execution, route mutation, publish request execution, or push.

## Current Surfaces

| Surface | Current state | Notes |
| --- | --- | --- |
| Dogfooding custom operation metadata | Present for `review_source_output_artifact` and `request_source_output_publish`. | Both are `availability: deferred`, CSRF-required, admin-guarded, duplicate-safe, and carry route-boundary metadata. |
| Extension slot action items | Present in the Mtool source-output dogfooding screen. | The operator action panel exposes review and publish actions as disabled action items with unavailable reasons. |
| Runtime preview HTML | Present as disabled UI. | `data-extension-slot-action`, `data-extension-slot-operation`, `data-extension-slot-unavailable-reason`, and `data-extension-slot-route-boundary` markers exist for the review action. |
| Screen definition normalization | Present. | Custom operation availability normalizes to `disabled`, `available`, `blocked`, or `deferred`; route boundary normalizes method/path/response/auth/idempotency/failure modes. |
| Dispatch preflight | Present for custom operations. | `review_source_output_artifact` can reach `accepted_plan` only when the operation is manually made `available`; normal metadata remains deferred. |
| Review request persistence | Present after accepted plan. | Route-local persistence records, reuses duplicate/open requests, skips non-allowed guard results, and renders failure status. |
| Audit append | Present for accepted/blocked outcomes. | Dispatch produces audit event metadata; persistence can append accepted/duplicate/failure audit records. |

## Route Boundary Inventory

| Operation | Route boundary | Failure modes currently declared |
| --- | --- | --- |
| `review_source_output_artifact` | `POST /projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact` | `unavailable`, `unauthorized`, `missing_csrf`, `missing_artifact`, `stale_artifact` |
| `request_source_output_publish` | `POST /projects/{project_key}/source-outputs/{source_output_key}/operations/request-source-output-publish` | `unavailable`, `unauthorized`, `missing_csrf`, `missing_artifact`, `stale_artifact`, `duplicate_request` |

Both routes are documented as `html_redirect`, guarded by `mtool_operator_admin`, and `duplicate_safe`. Only the review artifact path has a current plan-only dispatch/persistence slice. Publish request execution should stay behind a later gate.

## Guard Outcomes Already Covered

| Outcome | Evidence |
| --- | --- |
| `deferred_availability` | Dispatch blocks the default deferred review operation before execution. |
| `accepted_plan` | Dispatch can prepare plan-only review handoff when the operation is made available in test setup. |
| `missing_csrf` | Dispatch rejects missing CSRF before persistence. |
| `unauthenticated` / `auth_guard` / `policy_denied` | Dispatch rejects missing or insufficient principal before availability. |
| `missing_source_output` / `source_output_mismatch` / `unknown_operation` | Dispatch fails closed for invalid identity. |
| `missing_artifact` / `stale_artifact` | Artifact-target dispatch requires a current artifact key match. |
| non-allowed persistence skip | Persistence tests assert stale, unauthorized, missing-CSRF, and unknown-operation results do not create review requests. |
| persistence `recorded` / `duplicate` / `failed` | Route-local persistence records accepted plans, reuses open duplicates, and reports persistence failures. |

## Gaps For #539

- Define the public vocabulary for availability states: `available`, `deferred`, `blocked`, `unavailable`, `stale`, `unauthorized`, `missing_csrf`, and persistence failure.
- Decide whether UI should show operation-level `availability` or derived guard-level availability, because metadata currently says `deferred` while dispatch can also produce `blocked`, `stale`, `unauthorized`, or `accepted_plan`.
- Decide whether `request_source_output_publish` belongs in the same gate matrix or remains metadata-only until review request availability is proven.
- Define exact UI copy and stable DOM markers for each unavailable reason.
- Define whether `accepted_plan` should appear as "available", "ready", or "plan-only ready" in no-code surfaces.
- Define audit expectations for every blocked state before enabling any generated button.
- Define the read model shape for #540 so availability can be exposed without executing generated buttons.
- Add fast JSON/DOM contract test expectations for the availability markers before relying on browser smoke.

## Boundary

- Availability remains parked.
- Generated operator action buttons remain disabled.
- The first executable availability slice should be `review_source_output_artifact` only; publish request execution remains separately gated.
- This report does not change PHP behavior, routes, tests, or generated artifacts.
- Push is not performed.

## Verification

- `git diff --check`
