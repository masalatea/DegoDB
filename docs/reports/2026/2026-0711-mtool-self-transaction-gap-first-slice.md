# 2026-0711 Mtool Self Transaction Gap First Slice

Status: `DONE`

## Gap Found

DataClass field create, update, and delete each perform two required writes on the same config database:

1. mutate the `dataclassfields` row;
2. update the parent `dataclass.LastModified` value.

These writes previously had no shared transaction, so a failure in the parent touch could leave the field mutation committed while the parent metadata remained stale.

## Change

The three field mutation paths now use an ownership-aware atomic runner:

- start and own a transaction when none exists;
- commit only after both writes succeed;
- rollback an owned transaction after any exception;
- join an already-open outer transaction without committing or rolling it back.

SQLite coverage proves owned rollback and outer-transaction participation. Existing repository integration coverage continues to exercise field create, update, and delete.

## Audit Notes

The initial static candidates without local transaction calls were mostly false positives:

- separate CRUD functions with one write each;
- upsert branches that execute either INSERT or UPDATE, not both;
- cross-store Sample18 recording that cannot be covered by one local transaction.

## Helper-Aware Audit Closure

The follow-up audit traced mutation helpers that can hide additional writes. The main multi-write families already own or join transactions:

- Page Security policy plus capability replacement;
- Custom Proxy targets, steps, and reordering;
- DBAccess metadata child replacement and reordering;
- project HTML and HTML template metadata;
- no-code publish candidate transitions and alias events;
- project/table/DataClass sync and metadata bundle import.

The remaining apparent candidates execute one branch of an upsert, perform one write per public function, or cross stores. No additional same-database atomicity gap was found. #719 therefore closes with the DataClass field/parent-touch repair as its only required code change.
