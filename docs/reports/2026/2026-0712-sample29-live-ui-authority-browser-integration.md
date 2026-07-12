# Sample29 Live UI Authority Browser Integration

Status: `DONE`

## Result

Sample29 now proves the reusable managed-operation-outbox UI authority handoff without test code manufacturing an enabled action.

The dedicated Sample29 smoke enables the global generated-UI execution switch, allowlists only `SAMPLE29:update_support_case`, and supplies `managed_outbox_v1`. The runtime then combines that server configuration with authenticated live availability before enabling generic server submission.

## Browser evidence

- The current public runtime route receives live `enabled` availability for `update_support_case` and submits one real request.
- The custom alias route resolves the same project/action authority and submits through the same managed-outbox contract.
- The real endpoint returns a durable `pending` outbox item for `update_support_case`.
- Static artifact preview receives no server authority and remains unable to submit.
- Stubbed `done` and `failed` outcomes still cover success and operator-review recovery presentation.

The managed-authority browser mode does not rewrite preview action metadata, availability diagnostics, failed checks, dispatch functions, or execute-button disabled state. Legacy smoke modes retain their existing isolated setup.

## Runtime boundary

Generic submission is enabled only when all of these agree:

- generated UI binding is globally enabled;
- the normalized project/action pair is allowlisted;
- authenticated live availability reports the action enabled;
- the live execution model is `managed_operation_outbox`.

The same predicate is checked again immediately before POST. Stored artifact draft JSON is not rewritten; only its effective presentation checks may be relaxed after live authority is established.

## Verification

- `make sample29-no-code-public-runtime-browser-smoke`: passed.
- PHP, Node, and shell syntax checks: passed.
- `git diff --check`: passed.
- Full suite: 424 tests, 13,879 assertions, 1 skipped.

## Boundary

- Authority is enabled only by the Sample29 dedicated smoke environment; product defaults remain off.
- Availability remains authenticated, GET-only, and zero-dispatch.
- Static generated artifacts do not carry execution authority.
- This slice proves enqueue authority and durable pending response, not successful downstream outbox processing as one browser transaction.

## Next

#754 compares this evidence with the reusable L1 checklist and closes Sample29 qualification, unless one concrete boundary gap remains.
