# Consulting Intake / 相談前チェックリスト

English companion:
Use this checklist before a consulting, implementation-support, or overseas task-intake conversation. It keeps the scope grounded in current DegoDB support and separates technical discovery from future or domain-specific claims.

日本語:
この文書は、コンサルティング、導入支援、海外案件相談の前に使う checklist です。current support、future candidate、専門領域レビューが必要な論点を分けます。

## Purpose / 目的

English:
The intake step prevents over-promising. It records the source database, application context, expected deliverables, data-handling rules, and unsupported scope before implementation work starts.

日本語:
intake の目的は、過剰な約束を避けることです。実装作業の前に、source database、application context、期待する成果物、data handling rule、scope 外を記録します。

## Project Context / project 文脈

| Question / 確認項目 | English | 日本語 |
| --- | --- | --- |
| Goal / 目的 | What should DegoDB help with? | DegoDB で何を支援するか。 |
| Engagement type / 関わり方 | OSS evaluation, paid consulting, implementation support, or internal trial. | OSS 評価、有償 consulting、導入支援、社内 trial のどれか。 |
| Reviewer / 確認者 | Developer, migration lead, security reviewer, or client stakeholder. | developer、migration lead、security reviewer、client stakeholder の誰が見るか。 |
| Publication / 公開可否 | Use synthetic material unless sanitized publication is explicitly approved. | 明示承認がない限り synthetic material を使う。 |

## Database Intake / DB 受付

| Question / 確認項目 | English | 日本語 |
| --- | --- | --- |
| DB engine | MySQL / MariaDB, SQLite, PostgreSQL, Firebird feasibility candidate, or future candidate. | MySQL / MariaDB、SQLite、PostgreSQL、Firebird feasibility 候補、将来候補のどれか。 |
| Layer | Config store, imported user DB, generated DBAccess runtime, or docs/example. | config store、imported user DB、generated DBAccess runtime、docs/example のどの layer か。 |
| Access method | Local Docker, managed DB, dump, schema-only export, or read-only account. | local Docker、managed DB、dump、schema-only export、read-only account のどれか。 |
| Scope | Table count, column count, and representative slice. | table 数、column 数、代表 slice の有無。 |
| Sensitive names | Check whether table / column names reveal business details. | table / column 名が業務情報を漏らすか確認する。 |
| Data policy | Prefer schema-only and synthetic seed data. | schema-only と synthetic seed を優先する。 |

## Application And Output Target / app と出力対象

| Question / 確認項目 | English | 日本語 |
| --- | --- | --- |
| Existing app | Laravel, custom PHP, non-PHP framework, internal tool, or unknown. | Laravel、custom PHP、non-PHP framework、internal tool、unknown のどれか。 |
| Desired output | DataClass, DBAccess, OpenAPI, HTML, proxy runtime, metadata bundle, modernization audit, or handoff packet. | DataClass、DBAccess、OpenAPI、HTML、proxy runtime、metadata bundle、modernization audit、handoff packet のどれが必要か。 |
| Runtime expectation | Current generated output is PHP-focused. | 現行生成 output は PHP 主対象。 |
| API sharing | Current share lanes are authenticated viewer and admin artifact download. | current share lane は authenticated viewer と admin artifact download。 |
| Custom logic | Complex transactions and domain services are extension work outside the generator core. | complex transaction や domain service は generator core 外の extension work。 |

## Deliverables / 成果物

English:
Choose deliverables from [Deliverables / 成果物 catalog](deliverables.md). A first engagement should usually stay small and verifiable.

日本語:
成果物は [Deliverables / 成果物 catalog](deliverables.md) から選びます。最初の相談では、小さく検証可能な package に絞るのが安全です。

Typical first package / 最初の典型 package:

1. Source DB intake notes / DB 受付メモ。
2. Schema inventory / schema 棚卸し。
3. Canonical metadata import result / canonical metadata 取り込み結果。
4. Generated DataClass / DBAccess artifacts / 生成 DataClass / DBAccess。
5. OpenAPI / proxy / metadata bundle artifacts where applicable / 必要に応じた OpenAPI / proxy / metadata bundle。
6. Verification summary / 検証 summary。
7. Handoff notes and unsupported scope / 引き継ぎメモと scope 外。

## Scope Boundary / scope 境界

| Item / 項目 | Current reading / 現在の読み方 |
| --- | --- |
| MySQL / MariaDB user DB | Mainline / 主線。 |
| SQLite | Lightweight config store and representative user DB contracts / 軽量 config store と代表 user DB contract。 |
| PostgreSQL | Complete for the required opt-in user DB / generated-output lane. Mtool config store PostgreSQL is intentionally not required. / 必要範囲の opt-in user DB / generated-output lane は完了。Mtool config store の PostgreSQL 化は意図的に不要。 |
| Firebird | Active feasibility candidate for a local durable DB profile; not current production support / local durable DB profile の active feasibility 候補。現行 production support ではない。 |
| SQL Server / Oracle | Future candidate unless separate scope is decided / 別途 scope 決定があるまで将来候補。 |
| Non-PHP generated output | Legacy reference / future candidate / 旧実装参照または将来候補。 |
| AI context generation | Current `AI-CONTEXT-MD` generated output from canonical metadata / canonical metadata から生成する現行 `AI-CONTEXT-MD` output。 |
| Modernization audit generation | Current read-only `MODERNIZATION-AUDIT-MD` generated output; automatic code / schema changes are not included / 現行の読み取り専用 `MODERNIZATION-AUDIT-MD` 生成 output。code・schema 自動変更は含まない。 |
| Mobile app handoff | Next-after-Firebird roadmap candidate; first deliverable is a spec output, not native app generation / Firebird 後の roadmap 候補。最初の成果物は spec output であり、native app 生成ではない。 |
| Legal / tax / billing / compliance | Domain review required / 専門領域レビューが必要。 |

## Minimum Handoff Payload / 最小 handoff

English:
Capture these items when implementation support moves to another person or environment.

日本語:
導入支援を別の人や環境へ引き継ぐ時は、次を残します。

- project key and source key / project key と source key;
- config store profile / config store profile;
- source DB layer and access policy / source DB layer と access policy;
- import preview / apply status / import preview・apply 状態;
- generated artifact keys / generated artifact key;
- metadata bundle path / metadata bundle path;
- secret sidecar policy / secret sidecar policy;
- verification commands / 検証 command;
- assumptions and review-needed items / 仮定と review 必須項目。

## Related Docs / 関連文書

- [Adoption Guide / 採用ガイド](adoption-guide.md)
- [Deliverables / 成果物 catalog](deliverables.md)
- [Existing DB To Output / 既存 DB から出力まで](existing-db-to-output.md)
- [Security And Data Handling / security と data handling](security-and-data-handling.md)
