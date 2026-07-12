# Execution-Model-Aware Action Availability

Status: `FIRST_SLICE_DONE`

## Result

Authenticated action availability now evaluates two explicit execution models and reports the capability required by each action.

## `direct_guarded_route`

This preserves the Sample18 contract. Availability requires:

- overlay enabled;
- principal policy allowed;
- route-compatible `candidate_ready` readiness with no failure reasons;
- guarded click submit route/binding;
- mutation gate still disabled at the presentation layer;
- `transaction_full_v1` capability.

Existing failure codes remain stable, including `transaction_full_gate_missing`.

## `managed_operation_outbox`

Actions without a dedicated guarded submit route use the generic selector execution endpoint and are classified as managed outbox actions. Availability requires:

- overlay enabled;
- principal policy allowed;
- non-empty managed operation key;
- `managed_outbox_v1` capability.

This model does not require Sample18 readiness metadata, a guarded route, or Transaction Full merely to enqueue one durable outbox item. Missing capability reports `managed_outbox_gate_missing`, not `transaction_full_gate_missing`.

## Response diagnostics

Each flattened action diagnostic now carries:

- `execution_model`;
- `required_capability`;
- `capability_satisfied`;
- the existing availability, authorization, readiness, and failed-check fields.

The top-level response also reports the supplied managed-outbox capability gate. The endpoint remains authenticated, GET-only, selector-bound, and zero-dispatch with `mutation_enabled=false`.

## Configuration

The admin Compose service passes through `MTOOL_NO_CODE_MANAGED_OUTBOX_GATE`. Absence remains fail-closed.

## Verification

- Direct guarded policy remains enabled only with Transaction Full and retains prior failure behavior.
- Managed outbox policy enables with operation identity plus `managed_outbox_v1`.
- Missing managed capability disables the action and does not emit a Transaction Full failure.
- Missing managed operation identity fails closed.
- PHP syntax and `git diff --check` passed.
- Full suite: 424 tests, 13,878 assertions, 1 skipped.

## Boundary

- No Sample29 environment enables UI execution in this slice.
- No POST or dispatcher call is added to availability.
- No outbox handler receives an implicit Transaction Full claim.
- Static artifact previews remain non-authoritative.

## Next

#753 enables the reusable policy and managed-outbox capability only in the dedicated Sample29 smoke, removes in-page test authority mutation, and proves current/alias live authority with a real pending-outbox response plus blocked-path zero-POST behavior.
