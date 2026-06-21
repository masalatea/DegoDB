# Laravel + MySQL Legacy Example / Laravel + MySQL レガシー例

Status: `BASELINE_DRAFT`

This example defines a synthetic Laravel-style legacy system for DegoDB modernization documentation. / この例は、DegoDB の現代化説明用に作る合成 Laravel 風レガシーシステムです。

It is not from a real client system. / 実在の顧客システム由来ではありません。

## Purpose / 目的

Show how DegoDB can help inspect an existing MySQL-backed application, preserve baseline behavior, and prepare Mtool handoff material. / DegoDB が既存 MySQL アプリケーションを調査し、基準となる振る舞いを保ち、Mtool 引き継ぎ資料を準備する流れを示します。

## Scenario / シナリオ

The system is a small support desk application. / 対象は小さなサポート窓口アプリです。

- customers submit support tickets;
- staff add public replies and internal notes;
- customer-facing views must not expose internal comments;
- the database schema is the starting point for modernization.

## Contents / 内容

- [Scenario](scenario.md)
- [Legacy schema](legacy/schema.sql)
- [Legacy seed](legacy/seed.sql)
- [Baseline check](legacy/checks/baseline.sh)
- [Mtool import notes](mtool/import-notes.md)
- [Mtool generation plan](mtool/generation-plan.md)

## Current Boundary / 現在の境界

This is a documentation-first baseline. / これは文書先行の baseline です。

The schema, seed, and baseline check describe the intended executable shape, but the example does not yet include a full Laravel app. / schema、seed、baseline check は実行可能にしたい形を示しますが、まだ完全な Laravel app は含みません。

## Success Criteria / 完了条件

- The synthetic scenario is clear and believable.
- The schema and seed are small enough to inspect.
- The baseline check expresses the behavior DegoDB must preserve.
- The mtool handoff explains what should be imported, generated, documented, and audited.
