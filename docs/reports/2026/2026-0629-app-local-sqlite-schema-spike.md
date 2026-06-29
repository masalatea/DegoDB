# 2026-06-29 App Local SQLite Schema Spike

Status: `GATE0_FS_COMPLETED`

## Purpose

Gate 0 の 2 本目として、shared contract manifest から App-local SQLite schema を安全に生成して apply できるか確認した。

前段の Shared Contract Manifest Spike では DataClass metadata 単体だと nullable / default / key が不足することが分かった。したがって、この spike では明示的な nullable / default / key を含む minimal manifest を入力にした。

## Implemented

- Added standalone script:
  - `mtool/scripts/experimental/app_local_sqlite_schema_spike.php`
- Input:
  - built-in explicit `task-default` contract, or `--input-manifest=...`
- Output:
  - `work/feasibility/app-local-sqlite-schema-sample02/schema.sql`
  - `work/feasibility/app-local-sqlite-schema-sample02/summary.json`
  - `work/feasibility/app-local-sqlite-schema-sample02/app-local.sqlite`

The `work/` output is intentionally disposable and is not committed.

## Run

```bash
php mtool/scripts/experimental/app_local_sqlite_schema_spike.php \
  --sample=task-default \
  --output-dir=work/feasibility/app-local-sqlite-schema-sample02
```

## Result

Summary:

- table count: `1`
- table: `task`
- core fields:
  - `id`
  - `title`
  - `status`
  - `sort_order`
  - `is_pinned`
  - `published_at`
  - `note`
- key field:
  - `id`
- local metadata columns:
  - `local_updated_at`
  - `last_synced_at`
  - `sync_status`
  - `dirty`
  - `tombstone`
- SQLite apply: success

Generated DDL shape:

```sql
CREATE TABLE IF NOT EXISTS "task" (
    "id" INTEGER NOT NULL,
    "title" TEXT NOT NULL,
    "status" TEXT NOT NULL DEFAULT 'draft',
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    "is_pinned" INTEGER NOT NULL DEFAULT 0,
    "published_at" TEXT,
    "note" TEXT,
    "local_updated_at" TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "last_synced_at" TEXT NULL,
    "sync_status" TEXT NOT NULL DEFAULT 'clean',
    "dirty" INTEGER NOT NULL DEFAULT 0,
    "tombstone" INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY ("id")
);
```

## Decision

App-local SQLite schema generation is feasible when the contract manifest includes explicit nullable / default / key semantics.

This confirms the same boundary from the opposite direction: once the shared contract carries persistence semantics explicitly, App-local schema generation becomes straightforward. The schema spike did not need PHP DataClass output as input; it needed a language-neutral contract with field identity, type, nullability, default, and key policy.

The local metadata column policy also looks workable:

- append reserved local / sync columns after core contract fields;
- reject collisions with business fields;
- keep metadata columns visibly separate from core fields in summary/docs.

Together with the previous spike, this reinforces the original design assumption:

- keep DataClass as an implementation-facing generated type surface;
- add shared contract metadata as the language-neutral source for persistence / sync / no-code semantics;
- generate App-local schema, TypeScript DTO, sync payloads, and no-code operation definitions from the shared contract rather than from generated PHP DataClass output.

The roadmap should therefore add a real shared contract metadata layer before making App-local persistence a product feature.

## Remaining Risks

- Type mapping is intentionally minimal:
  - `bool` -> SQLite `INTEGER`
  - `datetime` -> SQLite `TEXT`
  - string/text -> SQLite `TEXT`
- Auto-increment and server id mapping are not decided.
- Conflict / revision policy is not covered.
- Composite keys need a separate check.
- Generated TypeScript DTO and DBAccess are not covered yet.

## Next

Recommended next Gate 0 step:

1. DTO Save/Read Spike using the generated SQLite schema.
2. Insert a DTO-shaped row, read it back, and compare the payload shape.
3. If that passes, promote Shared DataClass contract foundation as the next implementation work unit, with explicit contract metadata as the first real schema/design addition.

## Verification

- `php -l mtool/scripts/experimental/app_local_sqlite_schema_spike.php`
- `php mtool/scripts/experimental/app_local_sqlite_schema_spike.php --help`
- `php mtool/scripts/experimental/app_local_sqlite_schema_spike.php --sample=task-default --output-dir=work/feasibility/app-local-sqlite-schema-sample02`
