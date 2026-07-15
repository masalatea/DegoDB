# Standalone no-code completion report

## Status

`DONE_SUPPORTED_CONTRACT_SCOPE`

## Purpose

Close NC-S5: standalone Mtool no-code completion for the agreed supported-contract scope.

This report completes the lane that started by separating Mtool-owned standalone no-code from broader external app framework, mobile/native, offline sync, and realtime/shared-state work.

## Completion basis

Current scope source:

- `docs/no-code-standalone-scope.md`

Evidence inventory:

- `docs/reports/2026/2026-0715-standalone-no-code-evidence-inventory.md`

The evidence inventory found no implementation gap inside the declared standalone no-code supported-contract scope.

## Completion decision

Standalone Mtool no-code is complete for the current supported scope.

This means:

- Mtool-owned metadata is covered;
- `NO-CODE-RUNTIME` Source Output and runtime preview artifacts are covered;
- representative list/detail/form surfaces are covered;
- action intent draft and required/readiness state are covered;
- guarded submit / outbox handoff is covered where execution is explicitly supported;
- publish candidate, approval, current preview, and alias preview are covered;
- server authority remains explicit and is not replaced by browser-only mutation;
- validation gates are named through fast contract tests and representative browser smokes;
- non-required items are parked with reasons.

## Not claimed

This completion does not claim:

- every sample is fully converted to no-code;
- every application can be fully generated without custom code;
- all custom code is replaced;
- full CRUD for every action is automatic;
- offline sync is enabled by default;
- realtime/shared-state sync is part of standalone no-code;
- external app framework or native wrapper output is part of standalone completion.

Those are separate scoped lanes.

## Validation evidence

This completion report did not rerun the full smoke matrix. It closes the plan based on the current evidence inventory and existing gates.

Representative gates named by the inventory:

```sh
make sample-no-code-public-runtime-browser-smoke
make sample28-no-code-runtime-ui-smoke
make sample29-no-code-runtime-ui-smoke
make sample31-no-code-runtime-ui-smoke
make sample18-guarded-transaction-http-smoke
```

Fast/contract evidence includes:

- `tests/Integration/NoCodeRuntimeTest.php`;
- `tests/Integration/NoCodeScreenDefinitionTest.php`;
- `tests/Integration/SharedDataClassContractFoundationTest.php`;
- `tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`;
- `tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`;
- `tests/Integration/NoCodeOperatorSyncInspectionTest.php`.

## Next direction

With standalone no-code complete, the next active direction is external no-code/tool support as optional output:

- keep `mtool_no_code` as the supported baseline;
- do not migrate away from Mtool's runtime now;
- add optional `external_no_code` and `hybrid` outputs where useful;
- preserve the core/generated vs custom/extension boundary;
- keep server authority, CSRF, idempotency, Transaction Full, audit, outbox, and approval/current/alias policy on the Mtool/server side.

Next report:

- `docs/reports/2026/2026-0715-external-framework-optional-output-boundary-matrix.md`
