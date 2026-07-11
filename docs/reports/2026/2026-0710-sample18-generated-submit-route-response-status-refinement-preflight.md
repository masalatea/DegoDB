# Sample18 Generated-Submit Route Response/Status Refinement Preflight

Date: 2026-07-10
Status: DONE
Plan: #683

## Context

Sample18 generated-submit now has enough execution behavior to need a stable response contract. The route covers invalid requests, blocked/default behavior, duplicate idempotency, config/dependency failures, transaction/DBAccess failures, post-commit recovery-required failures, commit-status-unknown recovery, and executed success.

The no-code runtime UI and availability docs already depend on `result`, `ok`, `failure_code`, recovery metadata, and `executor_config`. Before adding broader browser smoke or expanding generated action availability, the route response semantics should be written down and lightly asserted.

## Response Contract

| Outcome | HTTP | `result` | `ok` | User-facing meaning | Required payload |
| --- | ---: | --- | --- | --- | --- |
| Method not allowed | 405 | `invalid` | `false` | Client used a non-POST method. | `failure_code=method_not_allowed`, `allowed_methods` |
| CSRF missing/invalid | 403 | `invalid` | `false` | Authenticated form guard failed. | `failure_code=missing_csrf` or `invalid_csrf`, `errors` |
| Validation error | 422 | `invalid` | `false` | Operation exists but input is invalid. | `failure_code=validation_error`, `errors`, normalized payload preview |
| Unknown operation | 404 | `invalid` | `false` | Generated action key is not allowlisted. | `failure_code=unknown_operation` |
| Disabled/default blocked | 409 | `blocked` | `false` | No mutation executed; route is inspectable only. | `failure_code=generated_submit_disabled`, dry-run metadata, `executor_config.status=disabled` |
| Duplicate idempotency | 409 | `blocked` | `false` | Request was recognized as duplicate and not executed. | `idempotency.status=duplicate`, duplicate reasons |
| Config failure | 500 | `failed` | `false` | Execution cannot safely start because config/runtime reference is invalid. | `executor_config.status=failed`, `route_execution.recovery_required=false` |
| Dependency failure | 500 | `failed` | `false` | Execution cannot safely start because required callables/classes are missing. | `route_execution.failure_code`, `recovery_required=false` |
| Transaction/DBAccess rollback failure | 500 | `failed` | `false` | Mutation did not commit successfully. | `transaction_result`, `recovery_required=false` unless commit status is unknown |
| Commit-status-unknown failure | 500 | `failed` | `false` | Commit failed/raised after DBAccess; manual recovery may be needed. | `recovery_required=true`, `recovery_reason=commit_status_unknown` |
| Post-commit recording failure | 500 | `failed` | `false` | Mutation committed, but required audit/idempotency recording failed. | `post_commit_recording.recovery_required=true` |
| Executed success | 200 | `executed` | `true` | All required steps succeeded. | `accepted=true`, `route_execution.execution_status=executed`, `transaction_result.transaction_status=committed`, `post_commit_recording.recording_status=recorded` |

## First Slice

#684 should add a compact durable response contract reference and focused assertions for the user-facing status/result/failure/recovery matrix. It should not change route execution behavior.

The first assertions should prefer a table/matrix style over broad browser smoke:

- invalid outcomes keep their HTTP/status/failure semantics;
- blocked and duplicate remain non-executing HTTP 409 outcomes;
- config/dependency failures stay HTTP 500 with `recovery_required=false`;
- recovery-required failures are distinguishable by `recovery_required=true` and stable recovery reason;
- executed success stays HTTP 200 and `result=executed`.

## Next

Promote #684 as the first response contract slice.
