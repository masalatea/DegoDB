# Generation Plan / 生成計画

Status: `DRAFT`

## First Pass / first pass

1. Import the SQLite schema into canonical metadata.
2. Sync DataClass metadata for `projects` and `tasks`.
3. Sync DBAccess metadata for project list and task CRUD operations.
4. Generate API surface output from the same metadata when supported.
5. Add smoke checks for expected CRUD behavior.
6. Store output under `reference/` only after a concrete generator run is added.

## Output Boundaries / 出力境界

| Output | Scope / 範囲 |
| --- | --- |
| DataClass | One generated class per table. |
| DBAccess | Project list, task list, task create, task status update. |
| OpenAPI / API surface | Actual generated artifact only after generator support is wired. |
| AI context | Out of scope; tracked in `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`. |
| Runtime app | Out of scope for the first input draft. |

## First API Contract / 最初の API contract

The first API contract should expose project and task data without returning `internal_note`. / 最初の API contract は、`internal_note` を返さずに project と task data を公開します。
