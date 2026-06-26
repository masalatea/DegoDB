# Deliverables / 成果物 catalog

English companion:
This catalog describes which artifacts can be prepared for OSS evaluation, consulting, and implementation support. It separates current deliverables from planned modernization-audit outputs.

日本語:
この文書は、OSS 評価、コンサルティング、導入支援で渡せる成果物を整理する catalog です。現行機能で作れるものと、計画中のものを分けます。

## Purpose / 目的

English:
The deliverable catalog turns DegoDB's technical capabilities into concrete handoff material. It should help an outside evaluator understand what can be produced today and what must remain a plan.

日本語:
成果物 catalog は、DegoDB の技術的な機能を、外部の人に渡せる具体的な material へ変換するための文書です。現時点で作れるものと、まだ計画に留めるものを判断しやすくします。

## Current Deliverables / 現行成果物

| Deliverable / 成果物 | English | 日本語 |
| --- | --- | --- |
| Schema intake notes | Records DB engine, layer, access method, scope, and assumptions. | DB engine、layer、access method、scope、assumption を記録する。 |
| Schema inventory | Lists tables, columns, type facts, and physical names from import preview or metadata. | import preview / metadata から table、column、type、physical name を整理する。 |
| Relationship notes | Separates schema-backed foreign keys from inferred relationships. | schema-backed FK と inferred relationship を分けて書く。 |
| Canonical metadata import result | Shows the durable design state in `config_db`. | `config_db` に残る durable design state を示す。 |
| DataClass output | Provides current PHP-focused `DATACLASS-PHP` output. | 現行 PHP 主対象の `DATACLASS-PHP` output。 |
| DBAccess output | Provides current PHP-focused `DBACCESS-PHP` output. | 現行 PHP 主対象の `DBACCESS-PHP` output。 |
| OpenAPI artifact | Provides `OPENAPI-JSON`, usually through authenticated viewer or artifact download. | `OPENAPI-JSON`。通常は authenticated viewer または artifact download で共有する。 |
| AI context package | Provides standard generated `AI-CONTEXT-MD` Markdown and JSON context from canonical metadata, verified across tutorial samples and Mtool self-output. | canonical metadata から生成した標準 `AI-CONTEXT-MD` Markdown / JSON context。tutorial sample 全体と Mtool self-output で検証済み。 |
| Modernization audit report | Provides deterministic read-only `MODERNIZATION-AUDIT-MD` Markdown and JSON audit output from canonical metadata, currently verified by `sample17`. | canonical metadata から生成する決定的・読み取り専用の `MODERNIZATION-AUDIT-MD` Markdown / JSON audit output。現在は `sample17` で検証済み。 |
| HTML artifact | Provides sample-backed HTML Source Output when applicable. | applicable な場合に sample-backed HTML Source Output を渡す。 |
| Proxy server artifact | Provides generated PHP-focused proxy runtime artifacts. | PHP 主対象の generated proxy runtime artifact。 |
| Project metadata bundle | Exports project metadata with secret separation. | secret 分離付きで project metadata を export する。 |
| Verification summary | Records commands, pass/fail result, skipped opt-in gates, and environment notes. | command、pass/fail、skip した opt-in gate、環境メモを記録する。 |
| Handoff notes | Records resume checkpoints, bundle paths, secret policy, and unsupported scope. | resume checkpoint、bundle path、secret policy、scope 外を記録する。 |

## Planned Deliverables / 計画中成果物

| Deliverable / 成果物 | English | 日本語 |
| --- | --- | --- |
| Non-PHP generated output | Legacy reference or future candidate, not current support. | 旧実装参照または将来候補。現行対応ではない。 |
| SQL Server / Oracle output | Future enterprise candidate requiring a support-scope decision. | support-scope decision が必要な将来 enterprise 候補。 |

## Typical Packages / 典型 package

### OSS Evaluation Package / OSS 評価 package

English:
Use this when someone wants to understand DegoDB quickly without starting a consulting engagement.

日本語:
コンサルティング契約へ入る前に、DegoDB を短時間で評価したい時の package です。

- adoption guide / 採用ガイド;
- proof matrix / 根拠 matrix;
- quickstart result / quickstart 結果;
- tutorial sample verification result / tutorial sample 検証結果;
- generated artifact pointers / 生成 artifact への導線;
- current support boundary / 現行対応範囲。

### Existing DB Exploration Package / 既存 DB 調査 package

English:
Use this when the user has an existing schema and wants to know what DegoDB can generate or document from it.

日本語:
既存 schema から DegoDB が何を生成・文書化できるかを確認する package です。

- consulting intake answers / consulting intake の回答;
- schema inventory / schema 棚卸し;
- canonical metadata import notes / canonical metadata import メモ;
- generated DataClass / DBAccess output where applicable / 必要に応じた DataClass / DBAccess output;
- OpenAPI / proxy output where applicable / 必要に応じた OpenAPI / proxy output;
- verification summary / 検証 summary;
- assumptions and review-needed items / 仮定と review 必須項目。

### Implementation Handoff Package / 導入引き継ぎ package

English:
Use this when work should be reproducible by another operator or in another environment.

日本語:
別の operator や別環境で作業を再現する必要がある時の package です。

- project metadata bundle / project metadata bundle;
- database source secret template / database source secret template;
- generated artifacts / generated artifacts;
- rerun commands / 再実行 command;
- storage / state checkpoint / storage と state の checkpoint;
- security and data handling notes / security と data handling のメモ;
- unsupported scope and next decisions / scope 外と次の判断。

## Delivery Rules / 提供ルール

English:
Deliverables should be honest about their source. Generated-looking directories must contain real generated output, and client-specific material must stay private unless publication is approved.

日本語:
成果物は、何を根拠に作られたかを明確にします。generated に見える directory には実際の generated output だけを置き、顧客固有 material は公開承認がない限り非公開にします。

- Generated-looking `reference/` directories contain actual Mtool output only. / generated に見える `reference/` は actual Mtool output だけにする。
- Future output ideas stay in docs, reports, or handoff notes. / 将来案は docs、reports、handoff notes に置く。
- Populated secret files are never committed. / secret 入り file は commit しない。
- Client-specific examples are synthetic unless publication is approved. / 顧客固有例は、承認がなければ synthetic にする。
- Verification summaries name exact commands. / 検証 summary には command を明記する。

## Related Docs / 関連文書

- [Consulting Intake / 相談前チェックリスト](consulting-intake.md)
- [Project Metadata Bundle / プロジェクトメタデータ bundle](project-metadata-bundle.md)
- [Proof Matrix / 根拠 matrix](proof-matrix.md)
- [Security And Data Handling / security と data handling](security-and-data-handling.md)
