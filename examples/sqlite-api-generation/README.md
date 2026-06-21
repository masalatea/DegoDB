# SQLite API Generation Example / SQLite API 生成例

Status: `ACTUAL_OUTPUT_BACKED_INPUT`

This example records a small SQLite-backed application shape before it is wired into a Mtool generated-output sample. / この例は、Mtool の生成出力 sample へ接続する前の小さな SQLite アプリの形を記録します。

It is a synthetic example, not a generated runtime output yet. / これは合成例であり、まだ実際の generated runtime output ではありません。

## Purpose / 目的

Show the input schema and CRUD behavior that should later drive Data Classes, DBAccess code, and a small API contract. / 後で Data Class、DBAccess、小さな API contract を生成するための入力 schema と CRUD 振る舞いを示します。

## Scenario / シナリオ

A small task board app stores projects and tasks in SQLite. / 小さな task board app が project と task を SQLite に保存しています。

The team wants to generate:

- Data Classes for `projects` and `tasks`;
- DBAccess helpers for list/detail/create/update;
- a simple API surface for task board screens;
- smoke checks that describe current CRUD behavior.

## Contents / 内容

- [Scenario](scenario.md)
- [SQLite schema](schema/schema.sql)
- [Seed data](seed.sql)
- [CRUD smoke SQL](checks/crud-smoke.sql)
- [CRUD smoke runner](checks/run-crud-smoke.sh)
- [Mtool import notes](mtool/import-notes.md)
- [Generation plan](mtool/generation-plan.md)
- [Actual output backing](mtool/actual-output.md)

## Boundary / 境界

- This is an input-side example backed by an existing actual-output task-board tutorial. / これは既存の実出力 task-board tutorial に裏付けられた入力側 example です。
- Add actual Mtool output under `reference/` only after a concrete generator run exists. / 具体的な generator 実行ができてから、Mtool 実出力だけを `reference/` に追加します。
- It uses SQLite as a user database example, not as the whole product scope. / SQLite は user database example として扱い、製品全体の範囲とはしません。

## Success Criteria / 完了条件

- The schema is small and inspectable.
- CRUD behavior is visible before generation.
- The future API surface can be derived from reviewable source inputs.
- The output boundary avoids generated-looking placeholders.
