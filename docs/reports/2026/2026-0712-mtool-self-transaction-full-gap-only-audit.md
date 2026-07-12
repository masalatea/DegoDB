# Mtool Self Transaction Full Gap-Only Audit

Status: `DONE_NO_GAP`

## Result

No concrete same-database Mtool multi-write path was found that requires a new transaction boundary. The Transaction Full main plan is implementation-complete without rewriting existing PDO repository code.

## Method

The audit scanned `mtool/app/*.php` for:

- SQL write repositories and services;
- `beginTransaction`, `commit`, `rollBack`, and `inTransaction` usage;
- explicit `ownsTransaction = !inTransaction()` ownership helpers;
- functions containing multiple SQL write forms;
- apparent write files without local transaction calls.

The scan found 31 files containing SQL write text and 172 transaction-related call sites. Sixteen write-text files had no local transaction calls and were inspected separately.

## Findings

### Existing composite boundaries

The principal same-database composite paths already own explicit PDO transactions, including:

- project creation and metadata bundle import;
- table import and data-class synchronization;
- DBAccess and Custom Proxy metadata replacement/reordering;
- HTML template and project HTML canonicalization/deletion;
- no-code publish candidate transitions and alias/current selection;
- project membership, identity membership, and page-security replacement;
- source-output reorder and related metadata operations.

### Outer transaction participation

Data-class, HTML-template, and project-HTML helpers include `!inTransaction()` ownership checks. They begin/commit only when they own the boundary and leave an existing outer transaction active, rolling back only their own transaction.

### Files without local boundaries

The remaining candidates did not establish a missing composite atomic unit:

- create/update/delete repositories perform one SQL write per public call;
- SQLite and MariaDB upsert implementations choose one update/insert branch or execute one native upsert statement;
- apparent multiple writes in generator/reference files are SQL text being generated or parsed, not Mtool repository execution;
- Sample18 idempotency/audit config-store writes are intentionally outside the application-DB transaction and use recovery-visible outcomes;
- outbox status transitions are independent state-machine operations rather than one same-request multi-write unit.

## Boundaries retained

- Cross-store application/config persistence is not relabeled as one transaction.
- No transaction is added to read-only or single-write functions.
- Existing public repositories that create their own PDO are not changed merely to make hypothetical nesting possible; no current composite caller requiring that nesting was found.
- Concurrency/idempotency improvements, if needed later, are separate from Transaction Full atomicity.

## Verification basis

The audit relies on the existing full suite last run after the implementation slice:

- 424 tests;
- 13,846 assertions;
- 1 skipped test;
- Sample14 real composite commit/rollback and Sample18 guarded HTTP commit/rollback evidence remain green.

## Decision

Transaction Full can close. Future work should add a transaction only when a concrete same-database composite caller is introduced or a failing partial-write test demonstrates a gap.

## Next

#745 reviews the local semantic commit stack and branch state. It should preserve meaningful commits and only squash an actually over-split unit.
