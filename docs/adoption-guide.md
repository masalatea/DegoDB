# Adoption Guide / 採用ガイド

English companion:
This guide is the external adoption entry point for DegoDB. It explains what DegoDB is useful for today, what remains future work, and which documents to read before trying it in an OSS, consulting, or implementation-support context.

日本語:
この文書は、DegoDB を OSS として試す人、導入を検討する人、コンサルティングや実装支援の相談をする人のための入口です。現時点で安全に説明できることと、まだ計画または将来候補に留めることを分けます。

## What DegoDB Is / DegoDB とは

English:
DegoDB is a database-first and existing-database-first development toolkit. It turns database schemas into canonical metadata, PHP-focused generated artifacts, API surfaces, and repeatable verification materials.

日本語:
DegoDB は、database-first / existing-database-first の開発ツールキットです。データベーススキーマを canonical metadata、PHP 主対象の生成成果物、API 面、再現可能な検証材料へ変換します。

## Good Fit / 向いている用途

| Use / 用途 | English | 日本語 |
| --- | --- | --- |
| OSS local trial / OSS として手元で試す | Run the local stack and inspect one tutorial sample. | local stack を起動し、tutorial sample を 1 本通して生成物を確認する。 |
| Existing DB exploration / 既存 DB 調査 | Import an existing schema and inspect generated artifacts. | 既存 schema を import し、生成物と metadata を確認する。 |
| Consulting intake / コンサル相談 | Clarify DB, app, deliverables, and unsupported scope before work starts. | 作業前に DB、app、成果物、未対応範囲を確認する。 |
| Implementation handoff / 導入引き継ぎ | Keep metadata bundles, secret sidecars, and verification results together. | metadata bundle、secret sidecar、検証結果をまとめて残す。 |
| Modernization preparation / 現代化準備 | Understand schema structure before replacing fragile access code. | 壊れやすい access code を置き換える前に schema 構造を把握する。 |

## Current Support / 現行対応

English:
DegoDB currently supports schema import into canonical metadata, PHP-focused DataClass / DBAccess generation, current sample-backed Source Output artifacts, project metadata bundles, and repeatable verification through samples and contract gates.

日本語:
DegoDB は現在、schema の canonical metadata への import、PHP 主対象の DataClass / DBAccess 生成、現行 sample に裏付けられた Source Output 成果物、project metadata bundle、sample / contract gate による再現可能な検証を扱います。

| Area / 領域 | Current reading / 現在の読み方 |
| --- | --- |
| MySQL / MariaDB | Default mainline for Mtool config store and generated-output workflows. / Mtool config store と生成 output workflow の default 主線。 |
| SQLite | Lightweight config store profile and representative user DB contract coverage. / 軽量 config store profile と代表的 user DB contract coverage。 |
| PostgreSQL | Complete for the required opt-in user DB / generated-output contract lane. Mtool config store PostgreSQL is intentionally not required. / 必要範囲の opt-in user DB / generated-output contract lane は完了。Mtool config store の PostgreSQL 化は意図的に不要。 |
| Firebird | Active feasibility candidate for a local durable DB profile between SQLite and MySQL / MariaDB. Not current production support. / SQLite と MySQL・MariaDB の間に置く local durable DB profile の active feasibility 候補。現行 production support ではない。 |
| Output language / 出力言語 | Current generated output is PHP-focused. / 現行生成 output は PHP 主対象。 |
| Modernization audit / 現代化診断 | Current deterministic read-only `MODERNIZATION-AUDIT-MD` output verified by `sample17`. It does not modify code or schema. / `sample17` で検証済みの現行の決定的・読み取り専用 `MODERNIZATION-AUDIT-MD` output。code や schema は変更しない。 |

## Not Current Support / 現行対応ではないこと

English:
Do not describe DegoDB as supporting every SQL database, every output language, or every enterprise / compliance requirement.

日本語:
DegoDB を「すべての SQL DB」「すべての出力言語」「すべての enterprise / compliance 要件」に対応済みとは説明しません。

| Not current support / 現行対応ではないもの | Reading / 読み方 |
| --- | --- |
| Non-PHP generated output / PHP 以外の生成 output | Legacy reference or future candidate. / 旧実装参照または将来候補。 |
| SQL Server / Oracle | Future enterprise candidates. / 将来の enterprise 候補。 |
| Automatic modernization implementation / 自動現代化実装 | Read-only audit output is current; automatic code / schema changes from audit findings are future implementation work. / 読み取り専用 audit output は現行対応。audit 結果に基づく code・schema 自動変更は将来の実装作業。 |
| Legal / tax / billing / compliance | Domain review required. / 専門領域レビューが必要。 |

## Safe Public Message / 安全な説明文

English:

```text
DegoDB currently focuses on PHP-oriented generated artifacts, deterministic AI-context handoff, and read-only modernization audit output around database-backed metadata. MySQL / MariaDB are the default mainline, SQLite is supported for lightweight local configuration and representative user DB contracts, and PostgreSQL is complete for the required opt-in user DB / generated-output contract lane. Mtool config store PostgreSQL is intentionally not required. Firebird is an active feasibility candidate for a local durable DB profile, not current production support. Older implementation references show broader language and database directions, but non-PHP outputs, SQL Server, Oracle, and automatic modernization code / schema changes are future compatibility tracks unless explicitly marked as current in this repository.
```

日本語:

```text
DegoDB は現在、データベース由来メタデータを中心にした PHP 主対象の生成成果物、決定的に生成される AI context handoff、読み取り専用の現代化診断 output に注力しています。MySQL / MariaDB は主 default、SQLite は軽量 local 設定と代表的 user DB contract、PostgreSQL は必要範囲の opt-in user DB / generated-output contract lane として完了済みです。Mtool config store の PostgreSQL 化は意図的に不要です。Firebird は local durable DB profile の active feasibility 候補であり、現行 production support ではありません。旧実装参照にはより広い言語・DB 方向性が残っていますが、PHP 以外の出力、SQL Server、Oracle、現代化診断結果に基づく code・schema 自動変更は、この repo で明示されるまでは将来対応候補です。
```

## First Evaluation Path / 最初の評価手順

English:
Start with the local quickstart, then check the support boundary and proof matrix before discussing consulting or implementation support.

日本語:
最初に local quickstart を通し、その後で対応範囲と proof matrix を確認してから、コンサルティングや導入支援の相談に進みます。

1. [Quickstart / まず動かしてみる](quickstart.md)
2. [Compatibility And Output Support / 対応範囲と出力サポート](compatibility-and-output-support.md)
3. [Proof Matrix / 根拠 matrix](proof-matrix.md)
4. [Consulting Intake / 相談前チェックリスト](consulting-intake.md)

## Related Docs / 関連文書

- [Use Cases / ユースケース](use-cases.md)
- [Consulting Intake / 相談前チェックリスト](consulting-intake.md)
- [Deliverables / 成果物 catalog](deliverables.md)
- [Proof Matrix / 根拠 matrix](proof-matrix.md)
- [Security And Data Handling / security と data handling](security-and-data-handling.md)
