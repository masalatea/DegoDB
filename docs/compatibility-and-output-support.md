# Compatibility And Output Support / 対応範囲と出力サポート

English companion:
This page states what DegoDB currently supports, what is only legacy reference evidence, and what remains future work.

DegoDB should be described with clear support boundaries. / DegoDB は、対応範囲を明確にした言葉で説明します。

This page separates current refactored support, verified generated-output coverage, legacy reference evidence, and future compatibility candidates. / このページでは、現行リファクタ版の対応、検証済みの生成出力 coverage、旧実装参照、将来候補を分けます。

## Summary / 要約

Current DegoDB is strongest as a PHP-focused, database-first toolkit with MySQL / MariaDB, SQLite, and PostgreSQL coverage across different layers. Firebird is being evaluated as a local durable DB profile between SQLite and MySQL / MariaDB, not claimed as current production support. / 現行 DegoDB は、PHP 主対象のデータベース起点ツールキットとして、MySQL / MariaDB、SQLite、PostgreSQL を複数 layer で扱うところが主な強みです。Firebird は SQLite と MySQL・MariaDB の間に置く local durable DB profile として評価中であり、現行 production support とは書きません。

Do not claim that every database, every output language, or every legacy feature is currently supported. / すべての DB、すべての出力言語、すべての旧機能が現行対応済みであるとは書きません。

## Support Terms / 用語

| Term | Meaning / 意味 |
| --- | --- |
| `current support` | Implemented and maintained in the refactored repo / 現行リファクタ版で実装・維持している |
| `verified target` | Covered by tests, samples, or contract gates / test、sample、contract gate で確認している |
| `legacy reference` | Old implementation evidence kept for reference, not current support / 旧実装の参照。現行対応ではない |
| `future candidate` | Useful target, but not yet current support / 有用な候補だが、まだ現行対応ではない |
| `parked` | Intentionally deferred / 意図的に保留 |

## Database Support / データベース対応

| Area / 領域 | Current position / 現在の位置づけ |
| --- | --- |
| MySQL / MariaDB | Main default for Mtool config store and generated-output workflows. / Mtool config store と生成出力 workflow の主 default。 |
| SQLite | Supported for lightweight Mtool config store profile and representative generated user DB contract coverage. / 軽量 Mtool config store profile と代表的な生成 user DB contract coverage で対応。 |
| PostgreSQL | Complete for the required scope: opt-in input and output, live schema import for input, generated DBAccess contract comparison for output, with a local compose-backed completion gate. Mtool config store PostgreSQL is intentionally not required. / 必要範囲では完了。opt-in の Input・Output として対応し、Input は live schema import、Output は generated DBAccess contract comparison で確認し、local compose-backed completion gate を持つ。Mtool config store の PostgreSQL 化は意図的に不要。 |
| Firebird | Active feasibility candidate as a local durable RDB profile between SQLite and MySQL/MariaDB. Not current production support yet. / SQLite と MySQL・MariaDB の間に置く local durable RDB profile として active feasibility 候補。まだ現行production supportではない。 |
| SQL Server | Legacy metadata hooks existed, but current support is not claimed. / 旧実装には metadata 導線があったが、現行対応とは書かない。 |
| Oracle | Future enterprise compatibility candidate only. / 将来の enterprise compatibility 候補に留める。 |

## Layer Boundary / layer 境界

Database support must name the layer. / DB 対応は、必ず layer を指定します。

| Layer | Current reading / 現在の読み方 |
| --- | --- |
| Mtool config store | MySQL / MariaDB default, SQLite lightweight profile. PostgreSQL config-store support is intentionally not required. |
| Imported user database | MySQL / MariaDB mainline. SQLite and PostgreSQL have representative coverage by sample / contract lane. |
| Generated DBAccess runtime | PHP-focused generated runtime with MySQL / MariaDB, SQLite, and PostgreSQL representative contract coverage. |
| Generated proxy / OpenAPI | PHP-focused runtime and OpenAPI artifacts. PostgreSQL-specific risk is mostly naming and DBAccess behavior, not a separate proxy language support claim. |
| Firebird local durable profile | Active feasibility lane. Evaluate SQLite -> Firebird and Firebird -> MySQL/MariaDB one-way promotion before claiming support. |
| Examples / docs | Input drafts are allowed. Generated-looking output examples must be actual Mtool output. |

## Output Support / 出力サポート

| Output area / 出力領域 | Current support / 現行対応 | Legacy reference / 旧実装参照 | How to describe / 書き方 |
| --- | --- | --- | --- |
| DataClass | PHP-focused current output. / PHP 主対象の現行出力。 | PHP, C#, Java, Objective-C, Swift templates existed. | Current support is PHP-focused; other languages are legacy reference / future candidates. |
| DBAccess | PHP-focused current output. / PHP 主対象の現行出力。 | PHP, C#, Java, Objective-C, Swift templates existed. | Do not claim current non-PHP DBAccess generation. |
| Proxy server | PHP-focused current generated runtime. / PHP 主対象の現行生成 runtime。 | PHP-oriented legacy proxy server templates existed. | Describe current proxy runtime as PHP-focused. |
| Proxy client | Not a current refactored primary output. / 現行リファクタ版の主出力ではない。 | Legacy templates include PHP, C#, Java, Objective-C, Swift proxy clients. | Mention only as legacy reference or future candidate. |
| OpenAPI | Current generated artifact. / 現行生成成果物。 | Not the old center of gravity. | Safe to describe as current. |
| AI context | Current generated `AI-CONTEXT-MD` artifact from canonical metadata. / canonical metadata から生成する現行 `AI-CONTEXT-MD` 成果物。 | Not a legacy feature. | Describe as deterministic generated context for AI / developer handoff, not AI-authored prose. |
| Modernization audit | Current deterministic read-only `MODERNIZATION-AUDIT-MD` artifact from canonical metadata, verified by `sample17`. / canonical metadata から生成する現行の決定的・読み取り専用 `MODERNIZATION-AUDIT-MD` 成果物。`sample17` で検証済み。 | Not a legacy feature. | Describe as generated diagnostic output; it does not modify runtime code or schema. |
| HTML artifact | Current tutorial / sample output exists. / 現行 tutorial / sample 出力がある。 | Legacy HTML output existed. | Keep scope tied to current samples. |
| Project metadata bundle | Current export / preview / apply support. / 現行の export / preview / apply 対応。 | Not a legacy claim. | Safe to describe as current. |

## Safe Public Message / 安全な説明文

Use this wording when a short support statement is needed. / 短い対応範囲説明が必要な時は、この文を使います。

```text
DegoDB currently focuses on PHP-oriented generated artifacts, deterministic AI-context handoff, and read-only modernization audit output around database-backed metadata. MySQL / MariaDB are the default mainline, SQLite is supported for lightweight local configuration and representative user DB contracts, and PostgreSQL is complete for the required opt-in user DB / generated-output contract lane. Mtool config store PostgreSQL is intentionally not required. Firebird is an active feasibility candidate for a local durable DB profile, not current production support. Older implementation references show broader language and database directions, but non-PHP outputs, SQL Server, Oracle, and automatic modernization code / schema changes are future compatibility tracks unless explicitly marked as current in this repository.
```

```text
DegoDB は現在、データベース由来メタデータを中心にした PHP 主対象の生成成果物、決定的に生成される AI context handoff、読み取り専用の現代化診断 output に注力しています。MySQL / MariaDB は主 default、SQLite は軽量 local 設定と代表的 user DB contract、PostgreSQL は必要範囲の opt-in user DB / generated-output contract lane として完了済みです。Mtool config store の PostgreSQL 化は意図的に不要です。Firebird は local durable DB profile の active feasibility 候補であり、現行 production support ではありません。旧実装参照にはより広い言語・DB 方向性が残っていますが、PHP 以外の出力、SQL Server、Oracle、現代化診断結果に基づく code・schema 自動変更は、この repo で明示されるまでは将来対応候補です。
```

## What Not To Say / 言わないこと

- Do not say DegoDB supports all SQL databases. / すべての SQL DB に対応しているとは言わない。
- Do not treat Mtool config store PostgreSQL support as remaining work. It is intentionally not required. / Mtool config store PostgreSQL 対応を残件として扱わない。意図的に不要。
- Do not say current DegoDB generates C# / Java / Objective-C / Swift output. / 現行 DegoDB が C# / Java / Objective-C / Swift を生成できるとは言わない。
- Do not say AI authors `AI-CONTEXT-MD`; it is generated by DegoDB / Mtool code from canonical metadata. / AI が `AI-CONTEXT-MD` を書くとは言わない。canonical metadata から DegoDB / Mtool code が生成する。
- Do not present parked invoice / tax / compliance examples as domain expertise. / 保留中の請求・税務・コンプライアンス example を専門知識として見せない。

## Current Evidence / 現在の根拠

- PostgreSQL user DB output representative set: [2026-0620 PostgreSQL user DB output first slice](reports/2026/2026-0620-postgresql-user-db-output-first-slice.md)
- Generated name migration and physical / logical naming policy: [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md)
- AI context current evidence: all tutorial sample `AI-CONTEXT-MD` definitions, `sample/tutorials/sample17-multi-output-project/reference/AI-CONTEXT-MD/`, and `ZzzAiContextStandardOutputTest` Mtool self-output coverage.
- Modernization audit current evidence: `sample/tutorials/sample17-multi-output-project/reference/MODERNIZATION-AUDIT-MD/` and `Sample17MultiOutputProjectTest`.
- Firebird local durable profile plan: [2026-0713 Firebird local durable profile plan](reports/2026/2026-0713-firebird-local-durable-profile-plan.md)
- Mobile app handoff roadmap: [2026-0713 mobile app handoff wrapper roadmap](reports/2026/2026-0713-mobile-app-handoff-wrapper-roadmap.md)
- Legacy template reference: `mtool/reference/legacy-mtool-templates/`
- Legacy build reference: `mtool/reference/legacy-mtool-build/`
