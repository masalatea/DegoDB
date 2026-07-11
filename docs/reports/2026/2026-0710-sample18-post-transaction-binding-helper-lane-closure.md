# Sample18 Post Transaction Binding Helper Lane Closure

Date: 2026-07-10
Plan: #653
Status: DONE

## Summary

#653 closes the route-unwired transaction binding helper lane.

#652 is accepted as the current callable binding boundary before generated-submit route execution is enabled.

## Accepted Capability From #652

- A transaction-capable runtime object can be adapted into begin / commit / rollback / DBAccess callables.
- The binding helper fail-closes non-sample18 DB targets before transaction begin.
- The DBAccess callable requires an active transaction context.
- Successful DBAccess execution commits through the existing transaction adapter.
- DBAccess failure rolls back through the existing transaction adapter.
- The generated-submit route remains disabled.

## Decision

Promote generated runtime transaction support preflight next.

Reason:

- The binding helper now assumes a runtime object with transaction methods.
- The generated DBAccess runtime support currently preserves `$mtooldb->execute(...)` but does not expose begin / commit / rollback / inTransaction.
- Route feature-flag integration should wait until the generated runtime itself can provide the transaction surface used by the binding helper.

## Next

#654 should define the smallest generated runtime transaction support addition.

Required boundaries:

- no generated-submit route wiring yet;
- no default-on executor behavior;
- keep legacy `$mtooldb` query / execute behavior compatible;
- add or wrap begin / commit / rollback / inTransaction for PDO first;
- define mysqli behavior explicitly or fail closed if unsupported in the first slice;
- prove that generated DBAccess calls use the same runtime object inside an active transaction.

## Verification

- `git diff --check`
