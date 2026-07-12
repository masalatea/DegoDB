# Sample18 Generated Lifecycle Action

Plan item: #792 Sample18 generated lifecycle action

## Summary

Sample18 `complete_task_card` is now qualified as the representative generated lifecycle action.

This slice keeps the existing default-off generated-submit model and does not broaden delete/reopen support. The covered claim is narrower: a keyed generated action can represent a non-create state transition, call an allowlisted DBAccess method, run through the guarded route transaction path, and record the same audit/idempotency outcome shape as the create path.

## Evidence Added

- `complete_task_card` carries explicit lifecycle metadata:
  - kind: `state_transition`
  - state field: `status`
  - from: `todo_or_doing`
  - to: `done`
- The fast Sample18 contract fixture records lifecycle metadata for create, update, and complete.
- The generated-submit route execution test now creates a task and then executes `complete_task_card` against the existing row.
- The route execution verifies:
  - operation key `complete_task_card`
  - curated action `complete`
  - DBAccess method `CompleteTaskCard`
  - committed transaction
  - post-commit recording
  - row state `status=done` with `completed_at` and `updated_at` set.
- The guarded HTTP transaction smoke can now run the same complete action by setting `SAMPLE18_TRANSACTION_SMOKE_OPERATION_KEY=complete_task_card` and `SAMPLE18_TRANSACTION_SMOKE_TASK_ID`.

## Boundary Kept

- Generated UI execution remains default-off and explicit allowlist based.
- Legacy Sample18 UI execution compatibility remains create-only.
- `reopen_task_card` and `delete_task_card` stay outside the initial supported matrix because reopen needs a separate adapter/policy decision and hard delete remains domain-retention-specific.
- This does not claim a generic lifecycle engine. It proves one representative state transition action using the existing generated-submit path.

## Next

#793 should prove explicit generated/custom hybrid ownership with one mixed workflow, then #794 can re-evaluate the capability lane for closure.
