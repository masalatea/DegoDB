# Canonical Table Constraint Metadata Foundation

Status: `DONE`

This report completes plan #862B.

## Implemented foundation

Four additive config metadata tables now represent:

- table keys;
- ordered key columns;
- table foreign keys;
- ordered source/referenced FK column pairs.

The model supports primary, unique, and index keys, composite ordering, FK update/delete actions, project/table/column ownership checks, and source-of-truth metadata.

## Contract and repository

The side-effect-free normalizer rejects:

- tables or columns outside the project;
- columns that do not belong to the declared table;
- referenced columns outside the referenced table;
- empty constraints;
- duplicate key/FK names;
- duplicate columns;
- multiple primary keys for one table;
- unsupported key kinds or FK actions.

The repository replaces one project's complete constraint snapshot in one config-DB transaction. Validation occurs before deletion. A failed replacement preserves the previous snapshot.

No live schema import, generated SQL, user DB, or SSO runtime is changed by this slice.

## Evidence

- Composite unique key column order round-trips.
- FK source/referenced column pairing round-trips.
- FK actions normalize deterministically.
- Invalid cross-table column input fails before replacement.
- Existing snapshot remains after invalid replacement.
- Focused test: `2 tests`, `25 assertions`.
- `make test`: `533 tests`, `14763 assertions`, `1 skipped`; exit code 0.
- PHP lint and `git diff --check` passed.

## Next boundary

Plan #862C should add optional portable bundle sections. Bundle files must use physical table/column names rather than config DB PIDs, resolve those names on import, preserve target constraints when sections are absent, and keep legacy bundle output unchanged.
