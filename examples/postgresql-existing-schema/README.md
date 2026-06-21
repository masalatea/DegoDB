# PostgreSQL Existing Schema Example / PostgreSQL 既存スキーマ例

Status: `ACTUAL_OUTPUT_BACKED_INPUT`

This example records an existing PostgreSQL schema scenario before it is wired into a Mtool generated-output sample. / この例は、Mtool の生成出力 sample へ接続する前の既存 PostgreSQL schema シナリオを記録します。

It is a synthetic schema, not from a real client system. / 実在の顧客システム由来ではない合成 schema です。

## Purpose / 目的

Show the PostgreSQL user-database lane without implying PostgreSQL support for the Mtool config store. / Mtool config store の PostgreSQL 対応を主張せずに、PostgreSQL user database lane を示します。

## Scenario / シナリオ

A small operations team stores product subscriptions, customers, and usage events in PostgreSQL. / 小さな運用チームが、顧客、契約、利用イベントを PostgreSQL に保存しています。

The database already exists before DegoDB is introduced. / DegoDB を導入する前から DB は存在しています。

The team wants to:

- inspect the schema before changing application code;
- prepare Data Class and DB Access generation;
- document relationships and risky columns;
- keep the source schema and representative read model reviewable.

## Contents / 内容

- [Scenario](scenario.md)
- [PostgreSQL schema](schema/schema.sql)
- [Seed data](seed.sql)
- [Representative query](checks/representative-query.sql)
- [Representative query runner](checks/run-representative-query.sh)
- [Mtool import notes](mtool/import-notes.md)
- [Generation plan](mtool/generation-plan.md)
- [Actual output backing](mtool/actual-output.md)

## Boundary / 境界

- This is an input-side example backed by existing actual-output gates. / これは既存の実出力 gate に裏付けられた入力側 example です。
- It does not claim PostgreSQL support for the Mtool config store. / Mtool config store の PostgreSQL 対応を主張しません。
- Add actual Mtool output under `reference/` only after a concrete generator run exists. / 具体的な generator 実行ができてから、Mtool 実出力だけを `reference/` に追加します。

## Success Criteria / 完了条件

- The schema is small enough to inspect.
- PostgreSQL-specific shapes such as `uuid`, `timestamptz`, and `jsonb` are visible.
- The representative query shows a useful read model.
- The mtool handoff explains import, generation, and review boundaries.
