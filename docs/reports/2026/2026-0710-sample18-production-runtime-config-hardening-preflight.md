# Sample18 Production Runtime Config Hardening Preflight

Date: 2026-07-10
Status: DONE
Plan: #676

## Context

Generated-submit execution can now run through the sample18 route when the mutation gate, executor flag, transaction/default runtime binding, DBAccess invocation, transaction commit, execution audit append, and idempotency outcome update all succeed.

The route currently reads enablement from:

- app config `sample18_generated_submit_mutation_enabled`;
- env fallback `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED=1`;
- app config `sample18_generated_submit_executor_enabled`;
- env fallback `MTOOL_SAMPLE18_GENERATED_SUBMIT_EXECUTOR_ENABLED=1`;
- app config `sample18_generated_submit_runtime_reference_dir`, falling back to the sample18 reference output path.

Those pieces are sufficient for tests, but production-facing enablement should have a single fail-closed config boundary before generated-submit execution is broadened.

## Hardening Contract

Add a generated-submit executor config resolver before route execution. It should:

- normalize app/env mutation and executor flags with explicit precedence: app config overrides env fallback;
- keep the default state disabled unless both mutation and executor enablement resolve to true;
- expose metadata such as `mutation_enabled`, `executor_enabled`, `source`, `runtime_reference_dir`, `ready`, and `reasons`;
- validate that the default runtime reference directory is non-empty, points to readable files, and does not silently proceed on missing files;
- preserve test injection of `sample18_generated_submit_transaction_callables` as the highest-priority dependency source;
- fail closed with stable failure codes before opening a transaction or calling DBAccess when config is inconsistent;
- keep user-facing route responses aligned with the all-success-or-failure policy: success only after every required step succeeds.

## Test Boundary

The first slice should add focused coverage for:

- default app/env config remains disabled and returns planned/blocked metadata only;
- env flags can enable resolver readiness when app flags are absent;
- app-level false overrides env true;
- missing or invalid runtime reference dir fails closed with stable metadata;
- injected transaction callables bypass default runtime reference path validation only when all required callables are present.

No broad browser smoke is required in this slice. The existing UI renderer can already display success, blocked, error, and recovery-required outcomes.

## Next

Promote #677 as the first resolver/coverage slice.
