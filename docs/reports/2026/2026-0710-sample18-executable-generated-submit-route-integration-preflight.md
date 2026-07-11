# Sample18 Executable Generated Submit Route Integration Preflight

Date: 2026-07-10
Plan: #645
Status: DONE

## Summary

#645 defines how sample18 generated-submit route execution should compose the existing route-unwired helpers before real execution is enabled.

This is a preflight only. It does not enable generated-submit route execution, real `TaskCardDBAccess` invocation, real transaction, real audit recording, or real idempotency outcome update.

## Composition Boundary

Future route execution must compose these steps in order:

1. Existing request method / CSRF / validation / operation checks.
2. Request audit append.
3. Idempotency admission.
4. Mutation gate.
5. DBAccess execution plan.
6. Transaction plan.
7. Execution update plan.
8. Execution guard.
9. Executor coordination plan.
10. Transaction adapter with DBAccess invocation.
11. Post-commit execution recording.
12. Route response assembly.

The first implementation slice should remain route-unwired and use fake transaction, fake DBAccess, fake execution audit, and fake idempotency recording callables.

## Feature Flag Boundary

Real execution must require an explicit executor feature flag separate from the existing metadata-only mutation gate.

Required behavior:

- default route behavior remains HTTP 409 `generated_submit_disabled`;
- generated button execution remains fail-closed unless the explicit executor flag is enabled;
- even with executor flag enabled, every required step must pass before success;
- duplicate idempotency state must fail closed before execution.

## Response Shape

Future executable responses should expose:

- `ok`;
- `accepted`;
- `result`;
- `success`;
- `failure_code`;
- `execution_status`;
- `execution_guard`;
- `executor_coordination_plan`;
- `transaction_result`;
- `post_commit_recording`;
- `recovery_required`;
- `dedupe_key`;
- `request_audit_event_key`.

Route result categories:

- `blocked`: pre-execution guard/gate/idempotency/feature flag prevents execution;
- `failed`: an execution or required recording step failed;
- `executed`: every required step succeeded;
- `recovery_required`: failure after app DB commit needs internal recovery, but still user-facing failure.

## First Helper Slice

#646 should add a route-unwired execution plan/helper that composes:

- existing guard/coordinator metadata;
- `app_lab_sample18_task_board_generated_submit_transaction_adapter`;
- `app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter`;
- fake transaction / DBAccess / recording callables;
- all-success-or-failure response metadata.

Required coverage:

- default feature flag disabled returns blocked and does not call fake transaction/DBAccess/recording;
- guard blocked / duplicate idempotency fails before transaction;
- transaction success + recording success returns executed/success;
- DBAccess failure returns failed with rollback;
- commit failure returns failed/recovery-required;
- post-commit recording failure returns failed/recovery-required;
- no generated-submit route execution is wired yet.

## Verification

- `git diff --check`

## Next

#646 should implement the route-unwired executable route execution plan/helper with fake callables and focused tests.
