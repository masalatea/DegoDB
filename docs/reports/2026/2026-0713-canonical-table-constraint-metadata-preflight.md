# Canonical Table Constraint Metadata Preflight

Status: `DONE`

This report completes plan #862A. It selects the canonical evidence boundary required before SSO app-user runtime generation can safely begin.

## Finding

Current table import and canonical metadata preserve:

- table names;
- column names and types;
- null/default/extra attributes;
- one column-level `IsKey` token such as `PRI`.

They do not preserve full key or foreign-key definitions. Existing live import reads `information_schema.COLUMNS` and does not build a constraint snapshot. Therefore neither current canonical metadata nor an existing reusable artifact can prove composite unique keys and foreign keys.

## Decision

Add normalized canonical table key and foreign-key metadata. Do not encode composite constraints in `IsKey`, table memo text, policy JSON, or generated SQL strings.

The selected model is separate from SSO and reusable for every DB-first project.

### Table keys

`project_table_keys`

- project ID;
- canonical table PID;
- stable key name;
- key kind: `primary`, `unique`, or `index`;
- source of truth;
- timestamps.

`project_table_key_columns`

- key ID;
- canonical column PID;
- ordinal position;
- one row per ordered key column.

### Foreign keys

`project_table_foreign_keys`

- project ID;
- source table PID;
- stable constraint name;
- referenced table PID;
- `on_update` and `on_delete` actions;
- source of truth;
- timestamps.

`project_table_foreign_key_columns`

- foreign-key ID;
- source column PID;
- referenced column PID;
- ordinal position;
- one row per ordered column pair.

## Invariants

- Key and FK metadata references canonical table/column PIDs, not free-form physical names after persistence.
- Ordered columns are explicit; composite key order is preserved.
- A table may have at most one primary key definition.
- Key names are unique within a project table.
- FK names are unique within a project table.
- Every key/FK column belongs to its declared table.
- Referenced FK columns belong to the referenced table.
- Empty keys and FKs are invalid.
- Imported/generated/manual source of truth is recorded.
- Existing `IsKey` remains a compatibility/display hint until consumers migrate; it is not authoritative for composite constraints.

## Driver introspection

### MySQL/MariaDB

Use:

- `information_schema.TABLE_CONSTRAINTS`;
- `information_schema.KEY_COLUMN_USAGE`;
- `information_schema.REFERENTIAL_CONSTRAINTS`;
- `information_schema.STATISTICS` for non-constraint indexes where needed.

### PostgreSQL

Use standard `information_schema.table_constraints`, `key_column_usage`, `constraint_column_usage`, plus PostgreSQL catalog data only where ordered referenced-column mapping or action semantics require it.

### SQLite

Use:

- `PRAGMA index_list`;
- `PRAGMA index_info` or `index_xinfo`;
- `PRAGMA foreign_key_list`;
- table-info primary-key ordinals.

Driver-specific readers normalize into one source constraint snapshot before canonical preview/apply.

## Import lifecycle

Constraint import extends the existing table import review flow rather than creating a second command.

1. Read source tables, columns, keys, and FKs.
2. Resolve source physical names to source table/column records.
3. Build a constraint diff against canonical metadata.
4. Preview insert/update/delete/same changes and review risk.
5. Apply table/column and constraint changes in one config-DB transaction.
6. Reject dangling or ambiguous constraint mappings.

Focused table import must include constraints touching the focused table. Destructive key/FK deletion requires review just like destructive column changes.

## Editing and proposals

- First slice provides repository and side-effect-free validation, not an admin editor UI.
- AI/Mtool may create a reviewable constraint proposal.
- Manual application uses the same repository contract after explicit review.
- SSO schema validation consumes canonical constraints only after they are stored and valid.

## Metadata bundle

Add optional project-core sections after repository stabilization:

- `table_keys`;
- `table_foreign_keys`.

Bundles without these sections preserve existing target constraint metadata until an explicit versioned replace decision exists. New bundles include ordered column references using stable table/column physical names at the portable boundary, then resolve them back to canonical PIDs during import.

## Migration and compatibility

- New config tables are additive.
- Existing projects have no canonical constraint rows and continue current generation behavior outside features that explicitly require constraint evidence.
- Do not backfill composite constraints from `IsKey`; the information is insufficient.
- A later live import can populate authoritative rows.
- SSO app-user generation remains blocked until required constraints are imported or explicitly reviewed and stored.
- Existing simple primary-key consumers may continue using `IsKey` during migration, while new constraint-aware validation prefers normalized key metadata.

## First implementation slice

1. Add config DB tables and bootstrap requirements.
2. Add side-effect-free normalized constraint contract validation.
3. Add repository fetch/replace for one project's table keys/FKs in a single transaction.
4. Prove composite unique ordering, composite FK pairing, invalid cross-table references, and absent-metadata compatibility with SQLite config DB tests.
5. Do not change live import or generators in this slice.

## Planned continuation

| Order | Work unit | Exit condition |
| --- | --- | --- |
| 862A | Constraint metadata preflight | Canonical model, driver sources, import/bundle lifecycle, compatibility, and first slice are fixed. |
| 862B | Constraint metadata foundation | Config schema, validator, repository, and focused tests support composite keys and FKs. |
| 862C | Constraint bundle integration | Optional key/FK sections round-trip without changing legacy bundle output. |
| 862D | Live import constraint first slice | One driver imports constraints through preview/apply; broader drivers remain explicit. |
| 862E | SSO schema gate completion | SSO validator consumes canonical unique/FK metadata and can become generation-ready. |

Only after #862E should plan #863 generate the first server runtime slice.
