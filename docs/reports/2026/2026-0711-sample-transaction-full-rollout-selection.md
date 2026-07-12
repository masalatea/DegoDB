# 2026-0711 Sample Transaction Full Rollout Selection

Status: `DONE`

## Decision

No additional tutorial sample currently qualifies for caller-owned Transaction Full rollout after Sample14.

The qualifying conditions are all required:

1. one processing unit calls multiple generated DBAccess mutations;
2. every mutation is required for the unit to succeed;
3. all mutations use the same transactional database connection.

Sample14 `TRANSACTION-PAIR` is currently the only sample that meets all three conditions. It already proves all-commit success and all-rollback failure through the generated endpoint against MariaDB.

## Excluded Samples

- Read-only and single-mutation samples do not gain Transaction Full semantics from an outer transaction.
- Sample18 performs one application DB mutation per route execution. Its audit and idempotency writes use the config store, so the full route crosses stores and cannot be physically atomic under one local PDO/mysqli transaction.
- Sample25 and Sample26 expose multiple single-function proxy endpoints, but each endpoint executes one mutation. They are not one composite mutation unit.
- No other sample seeds define a multi-step Custom Proxy.

## Outcome

#718 closes without adding speculative transaction wrappers. Future samples should opt in when their metadata declares a real multi-step same-database mutation unit. Cross-store workflows require recovery/idempotency design rather than a false local atomicity claim.

The next lane is #719: a gap-only audit of Mtool's own multi-write paths, preserving existing transaction ownership and changing only genuine uncovered same-database units.
