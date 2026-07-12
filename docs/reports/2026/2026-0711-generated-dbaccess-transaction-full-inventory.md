# 2026-0711 Generated DBAccess Transaction Full Inventory

Status: `DONE`

## Purpose

Reframe the former Sample18-oriented transaction gate as a general Mtool generated DBAccess capability investigation.

The target behavior is:

- multiple generated DBAccess SQL updates can run on one transaction;
- one required operation failure makes the whole processing unit fail and roll back;
- success means every required update committed;
- sample-specific adoption starts only after the common runtime/generator contract is understood;
- Mtool itself should adopt the same Transaction Full contract where feasible.

## Agreed Minimal Design

- Generated DBAccess classes remain transaction-unaware.
- The composite caller owns `beginTransaction()`, all required DBAccess calls, failure detection, `rollBack()`, and `commit()`.
- All participating generated DBAccess classes use the same global `$mtooldb` runtime connection.
- The custom runtime wrapper delegates transaction operations to its internal standard PDO or mysqli connection.
- No transaction argument, DBAccess parent-class transaction API, or PHP trait is required for the first design.
- A transaction helper callback may be considered later, but it is not required to prove the basic contract.

## Initial Findings

### Shared connection makes a single transaction possible in principle

Generated DBAccess methods use the global `$mtooldb` runtime handle and call `connect_mtooldb_if_not_yet()` before executing SQL. The current bootstrap preserves an existing object, so multiple generated DBAccess class calls can use the same runtime connection when the caller opens a transaction around them.

### Transaction support is PDO-only

`MtoolGeneratedDbAccessRuntimeDb` currently exposes `beginTransaction()`, `commit()`, `rollBack()`, and `inTransaction()` only for PDO connections. The default mysqli path reports transaction operations as unsupported even though mysqli itself has transaction APIs.

All 17 checked sample DBAccess runtime support files contain the PDO transaction methods because they are generated from the shared runtime template.

### Generated DBAccess methods do not provide a composite failure contract

Generated methods execute SQL through `$mtooldb`, log an error when `errno != 0`, and return the raw result. They do not throw a stable DBAccess exception, mark a shared transaction as rollback-only, or provide a common transaction runner.

Therefore a caller can currently forget to inspect one failed result and still commit later successful work. The runtime API alone does not guarantee all-success-or-all-failure processing.

### Existing proof is Sample18-specific

Sample18 has PDO/SQLite transaction binding and rollback coverage plus route-specific adapters. This proves that generated DBAccess calls can participate in one PDO transaction, but it does not establish a generator-wide orchestration and failure contract.

Only `Sample18MiniTaskBoardDemoTest` directly exercises the generated runtime transaction API in the integration suite found by this initial scan.

### Mtool has local transaction implementations but no shared Transaction Full contract

Several Mtool repositories and services use PDO transactions directly, sometimes with transaction ownership checks. These paths must be inventoried separately to determine which can adopt a common contract and which cross-store flows cannot be physically atomic.

## Questions for the Full Inventory

1. Can every generated DBAccess class for one source output reliably share the same connection and transaction?
2. What happens across multiple source outputs, runtime roots, databases, or drivers?
3. Should failed generated DBAccess calls throw, return a typed result, or mark the transaction rollback-only?
4. How are nested transaction owners and already-open transactions handled?
5. What compatibility behavior is required for legacy callers that inspect raw return values and `$mtooldb->errno`?
6. Which databases and drivers must support Transaction Full in the first release?
7. Which sample supplies the smallest representative multi-update success/rollback proof?
8. Which Mtool self-use flows are single-database and eligible for the same atomic contract?

## Replanned Work Units

- #711 full current-state inventory and repair-boundary decision
- #712 shared runtime PDO/mysqli transaction parity
- #713 generated DBAccess composite commit/rollback proof on PDO and mysqli
- #714 sample and Mtool composite mutation call-site inventory
- #715 sample-by-sample caller-owned transaction rollout
- #716 Mtool self-use caller-owned transaction rollout
- #717 post-implementation commit checkpoint
- #718 server-generated availability overlay, parked
- #719 real guarded execution smoke, parked

## Current Estimate Position

Do not estimate the whole lane as 1-2 days. Only the initial inventory is currently estimated at 1-2 days. Implementation and rollout estimates must be produced after #711 identifies driver, compatibility, generator, sample, and Mtool self-use scope.

## Outcome

The repair boundary is smaller than the initial broad architecture hypothesis:

- keep generated DBAccess classes unchanged;
- complete transaction delegation in the shared runtime wrapper;
- let each composite caller define its own transaction boundary;
- inventory and migrate only real multi-write call sites.

The first foundation implementation and proof are recorded in `2026-0711-generated-dbaccess-transaction-full-foundation.md`.
