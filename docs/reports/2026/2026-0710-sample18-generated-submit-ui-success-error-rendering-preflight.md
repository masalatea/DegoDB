# Sample18 Generated-Submit UI Success/Error Rendering Preflight

Date: 2026-07-10
Status: DONE
Plan: #673

## Context

The generated-submit route contract now covers:

- default blocked/non-mutating behavior;
- explicit executor success;
- duplicate non-execution;
- DBAccess rollback failure;
- post-commit recording recovery;
- commit-unknown recovery;
- default runtime binding failure.

The no-code runtime guarded generated action UI still treats network-submit responses mostly as blocked or error. It needs a small result renderer that exposes distinct UI states and stable data attributes before broader UI polish.

## Rendering Contract

The guarded generated action submit UI should map route responses as follows:

- `result=executed` and `ok=true`
  - UI state: `success`
  - Message: execution succeeded, with operation/result context.
  - Button attributes include `data-action-last-submit-result=executed` and blank failure code.
- `result=blocked`
  - UI state: `blocked`
  - Message: generated submit blocked, preserving `failure_code`.
  - Duplicate replay remains blocked/non-executing and should be distinguishable through `idempotency.status=duplicate` when present.
- `result=failed` with `route_execution.recovery_required=true`, `transaction_result.recovery_required=true`, or `post_commit_recording.recovery_required=true`
  - UI state: `recovery-required`
  - Message: generated submit requires recovery, preserving `recovery_reason` and `failure_code`.
  - Button/feedback attributes expose `data-action-recovery-required=true` and `data-action-recovery-reason`.
- Other failed/invalid responses
  - UI state: `error`
  - Message: generated submit rejected/failed with `failure_code` or `error`.

The runtime dispatch record should keep `network_submit=true`, set `ok` from the route payload, set `executed=true` only for `result=executed`, and store the raw result payload.

## First Slice

#674 should update `submitGuardedGeneratedAction()` and its helper message/state functions in `mtool/app/no_code_runtime.php`.

Test boundary:

- A synthetic executed payload renders `success` and dispatches `executed=true`.
- A duplicate/blocked payload renders `blocked` and preserves failure code.
- A recovery-required payload renders `recovery-required` and exposes recovery attributes.
- A malformed/error payload remains `error`.

No route execution behavior should change in this slice.

## Next

Promote #674 as the first runtime UI result rendering slice.
