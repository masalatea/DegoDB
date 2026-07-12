# 2026-0711 Generated DBAccess Transaction Full Foundation

Status: `FIRST_SLICE_DONE`

## Decision

Generated DBAccess classes remain transaction-unaware. A composite caller opens one transaction on the shared global `$mtooldb` runtime connection, calls any required generated DBAccess instances, rolls back after any required failure, and commits only after all required calls succeed.

Transaction behavior belongs to the shared runtime connection wrapper, not to each generated DBAccess class, a DBAccess parent class, or a PHP trait.

## Implementation

- Added mysqli delegation for `beginTransaction()`, `commit()`, `rollBack()`, and `inTransaction()`.
- Added wrapper-owned mysqli transaction-state tracking because mysqli has no direct `inTransaction()` equivalent.
- Preserved PDO delegation.
- Normalized thrown mysqli query/prepare/execute errors back into the established `false`, `errno`, and `error` runtime contract.
- Regenerated all 17 checked sample DBAccess runtime support references from the shared template.

## Proof

Focused integration coverage uses two ordinary generated-style DBAccess instances sharing `$mtooldb`.

For PDO/SQLite and mysqli/MariaDB it verifies:

- transaction start succeeds;
- two successful updates commit together;
- the second transaction performs one successful update followed by a required duplicate-key failure;
- the caller observes the failure and rolls back;
- the first update from the failed transaction is absent afterward;
- transaction state is active after mysqli begin and inactive after commit/rollback.

## Next

#714 inventories actual sample and Mtool composite mutation call sites. It should rank same-database multi-write flows for caller-owned transaction boundaries and explicitly exclude cross-database work from local atomicity claims.
