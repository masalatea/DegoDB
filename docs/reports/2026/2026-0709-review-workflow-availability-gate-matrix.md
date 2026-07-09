# Review Workflow Availability Gate Matrix

Date: 2026-07-09

Status: `DONE`

## Summary

#539 defines the gate matrix for review workflow availability before any generated button execution. The main decision is to separate operation metadata availability from derived guard/preflight availability. #540 should expose the derived read model without executing the generated button.

## Vocabulary

| Term | Meaning | Source |
| --- | --- | --- |
| `operation_availability` | Static capability declared by no-code custom operation metadata: `disabled`, `deferred`, `blocked`, or `available`. | Screen definition / dogfooding metadata. |
| `preflight_result` | Runtime guard outcome from dispatch preflight: `accepted_plan`, `blocked`, `unauthorized`, `stale`, `invalid`, or `failed`. | Dispatch/read model. |
| `availability_state` | UI/read-model state derived from operation metadata plus preflight context. | #540 metadata-only read model. |
| `execution_mode` | What may happen if the generated action is eventually enabled. First slice is `plan-only`; mutation remains out of scope. | Dispatch plan. |

## Gate Matrix

| Gate | Input condition | availability_state | UI behavior | Audit expectation | Persistence expectation |
| --- | --- | --- | --- | --- | --- |
| Metadata deferred | `operation_availability=deferred` | `deferred` | Button disabled; show deferred reason and route boundary. | No new audit event from read-only display. If submitted through preflight, audit `blocked/deferred_availability`. | No review request row. |
| Metadata disabled | `operation_availability=disabled` | `unavailable` | Button disabled; show disabled/unavailable reason. | No audit from read-only display. If submitted through preflight, audit blocked unavailable. | No review request row. |
| Metadata blocked | `operation_availability=blocked` | `blocked` | Button disabled; show policy/setup blocker. | No audit from read-only display. If submitted through preflight, audit blocked reason. | No review request row. |
| Missing principal | no principal | `unauthorized` | Button disabled; show login/admin requirement. | If submitted, audit unauthorized with `unauthenticated`. | No review request row. |
| Auth/policy denied | guard or policy rejects | `unauthorized` | Button disabled; show admin/policy requirement without exposing sensitive detail. | If submitted, audit unauthorized with `auth_guard` or `policy_denied`. | No review request row. |
| Missing CSRF | CSRF required but unavailable | `missing_csrf` | Button disabled in generated UI; route forms must not be rendered as executable without CSRF. | If submitted, audit blocked with `missing_csrf`. | No review request row. |
| Missing source output | source output context missing/mismatched | `unavailable` | Button disabled; show source output unavailable. | If submitted, audit invalid with missing/mismatch code. | No review request row. |
| Missing artifact | artifact-target operation lacks current artifact | `missing_artifact` | Button disabled; show artifact unavailable. | If submitted, audit blocked with `missing_artifact`. | No review request row. |
| Stale artifact | submitted artifact does not match current artifact | `stale` | Button disabled or fail closed; show refresh/review latest artifact. | If submitted, audit stale with `stale_artifact`. | No review request row. |
| Plan-only ready | metadata available and all guards pass | `plan_only_ready` | For #540/#541, still render as non-executable preview; label as plan-only ready. Generated button remains disabled until #542. | If submitted through explicit route wrapper, audit `accepted_plan`. | Create or reuse review request row. |
| Persistence duplicate | accepted plan matches open review request | `plan_only_duplicate` | Show existing/open review request state after route result, not as a fresh UI default. | Audit duplicate with review request key. | Reuse existing row. |
| Persistence failure | accepted plan cannot persist | `persistence_failed` | Result page shows failure and states no mutation executed. | Audit invalid/failed with `review_request_persistence_failed`. | No successful row; report failed status. |

## UI Marker Contract For #540/#541

The metadata-only read model should provide stable values that HTML/DOM contract tests can assert:

- `data-availability-state`
- `data-operation-availability`
- `data-preflight-result`
- `data-availability-reason`
- `data-route-boundary`
- `data-execution-mode`
- `data-review-request-status` only after route result pages, not default preview

Default generated preview buttons remain disabled until #542 explicitly enables a narrow route path. `plan_only_ready` is a readiness state, not permission to render an enabled generated button.

## Operation Scope

| Operation | Gate policy |
| --- | --- |
| `review_source_output_artifact` | First availability target. It may progress from deferred metadata to metadata-only read model, UI preview, and then plan-only route enablement. |
| `request_source_output_publish` | Keep metadata-only until review request availability is proven. Include in read model as deferred/unavailable, but do not promote to executable availability in #542. |

## Test Expectations

- #540 should add or refine JSON-level tests for derived `availability_state`.
- #541 should add fast DOM contract tests for disabled UI markers and copy.
- #542 should run focused route/persistence tests before any generated button can become executable.
- Headless Chrome remains optional for representative smoke; it is not the first test layer for this matrix.

## Boundary

- This is a planning/docs slice only.
- No PHP behavior changes are made here.
- Generated operator buttons remain disabled.
- No publish request route is enabled.
- Push is not performed.

## Verification

- `git diff --check`
