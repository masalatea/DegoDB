# Examples / 例の索引

This directory is for scenario-oriented examples that explain DegoDB as a database-first and existing-database-first toolkit. / このディレクトリは、DegoDB をデータベース起点・既存データベース起点のツールキットとして説明するための、シナリオ型の例を置く場所です。

These examples are different from `sample/tutorials/`. / ここに置く example は `sample/tutorials/` とは役割が異なります。

- `sample/tutorials/` verifies current features in small runnable tutorial packs / `sample/tutorials/` は現行機能を小さな実行可能チュートリアルパックとして検証する場所
- `examples/` explains realistic business scenarios and modernization stories before they become runnable `sample/tutorials/` packs / `examples/` は、実行可能な `sample/tutorials/` pack になる前の現実的な業務シナリオや現代化ストーリーを説明する場所

## Example Principles / 例の原則

- Use synthetic but executable scenarios / 合成だが実行できるシナリオを使う
- Avoid pretending the examples come from real client systems / 実在の顧客システム由来であるかのように見せない
- Keep baseline behavior checks before generated output / 生成出力の前に、基準となる振る舞い確認を置く
- Put actual generated output under `reference/` after it is produced by Mtool / Mtool が生成した実出力だけを `reference/` に置く
- Keep future output ideas in docs or mtool handoff notes, not in generated-looking directories / 将来の出力案は generated 風 directory ではなく docs や mtool 引き継ぎメモに置く

## Planned Examples / 予定している例

| Example | Status / 状態 | Purpose / 目的 | Planned contents / 予定内容 |
| --- | --- | --- | --- |
| `laravel-mysql-legacy/` | Baseline draft / baseline ドラフト | Legacy DB modernization entry point / レガシーデータベース現代化の入口 | Synthetic MySQL schema, seed data, baseline behavior check, mtool handoff notes / 合成 MySQL スキーマ、初期データ、基準となる振る舞い確認、mtool 引き継ぎメモ |
| `postgresql-existing-schema/` | Actual-output backed input / 実出力裏付け付き入力 | Existing PostgreSQL schema before scenario-specific Mtool output generation / scenario 固有 Mtool 出力生成前の既存 PostgreSQL schema | PostgreSQL schema, seed data, representative query check, mtool import notes, current actual-output backing / PostgreSQL スキーマ、初期データ、代表 query 確認、mtool import メモ、現行実出力の裏付け |
| `sqlite-api-generation/` | Actual-output backed input / 実出力裏付け付き入力 | Small SQLite app before scenario-specific API layer generation / scenario 固有 API 層生成前の小さな SQLite アプリ | SQLite schema, CRUD smoke, mtool import notes, current task-board actual-output backing / SQLite スキーマ、CRUD smoke、mtool import メモ、現行 task-board 実出力の裏付け |

## Parked Examples / 保留している例

| Example | Status / 状態 | Reason / 理由 |
| --- | --- | --- |
| `japanese-invoice-saas/` | Parked / 保留 | Billing, invoice, tax, and compliance examples need domain review before they should be shown as realistic / 請求、インボイス、税務、コンプライアンスの例は、現実的なものとして見せる前に専門家レビューが必要 |

## First Example Structure / 最初の例の構成

The first modernization example should be easy to inspect before any generation happens. / 最初の現代化例は、生成前の状態を簡単に確認できる構成にします。

```text
examples/laravel-mysql-legacy/
  README.md
  scenario.md
  legacy/
    schema.sql
    seed.sql
    checks/
      baseline.sh
  mtool/
    import-notes.md
    generation-plan.md
```

## Related Docs / 関連文書

- [Use Cases / ユースケース](../docs/use-cases.md)
- [Compatibility And Output Support / 対応範囲と出力サポート](../docs/compatibility-and-output-support.md)
- [Existing DB To Output / 既存 DB から出力まで](../docs/existing-db-to-output.md)
- [Sample Tutorial Roadmap / sample 学習導線](../docs/sample-tutorial-roadmap.md)
- [Database-first sales assets plan / データベース起点の営業資産計画](../docs/reports/2026/2026-0621-database-first-sales-assets-plan.md)
