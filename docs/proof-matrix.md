# Proof Matrix / 根拠 matrix

English companion:
This matrix summarizes what the current samples, docs, and tests demonstrate. It is not a marketing claim that every adjacent database, framework, or output language is supported.

日本語:
この文書は、現行 sample、docs、test が何を実証しているかを外部向けに整理する matrix です。近い領域のすべてを対応済みと言うための文書ではありません。

## Purpose / 目的

English:
The proof matrix converts existing verification work into a readable evidence map. It should be used together with the support boundary in [Compatibility And Output Support](compatibility-and-output-support.md).

日本語:
proof matrix は、既存の検証作業を読みやすい evidence map に変換します。[Compatibility And Output Support](compatibility-and-output-support.md) の対応範囲と一緒に使います。

## Support Labels / support label

| Label | English | 日本語 |
| --- | --- | --- |
| `current` | Implemented and maintained in the refactored repo. | refactored repo で実装・維持している。 |
| `verified` | Covered by sample, test, smoke, or contract gate. | sample、test、smoke、contract gate で確認している。 |
| `opt-in` | Available through an explicit lane or environment, not the default path. | default ではなく明示的な lane / env で確認する。 |
| `legacy-reference` | Evidence exists in old references, but it is not current support. | 旧実装参照はあるが current support ではない。 |
| `planned` | Designed or listed in current plans, but not generated today. | 計画済みだが現行生成 output ではない。 |
| `parked` | Deferred until a concrete need or support-scope decision appears. | concrete need / scope decision まで保留。 |

## Generated Artifact Matrix / 生成 artifact matrix

| Artifact | Status | Evidence / 根拠 |
| --- | --- | --- |
| `DATACLASS-PHP` | `current / verified` | `sample01-10`, `sample12`, `sample17-26` |
| `DBACCESS-PHP` | `current / verified` | `sample01`, `sample05-10`, `sample17-26`, user DB contract gates |
| `HTML-PAGE` | `current / verified` | `sample11`, `sample17`, `sample18`, `sample20`, `sample24`, `sample26` |
| `OPENAPI-JSON` | `current / verified` | `sample13`, `sample17`, `sample18`, `sample20-26` |
| `CUSTOM-PROXY-SERVER` | `current / verified` | `sample14`, custom proxy metadata bundle coverage |
| `AUTH-PROXY-SERVER` | `current / verified` | `sample16`, `sample25`, `sample26`, generated runtime security baseline |
| `PROJECT-METADATA-BUNDLE` | `current / verified` | `sample15`, `sample26`, project metadata bundle docs |
| `AI-CONTEXT-MD` | `current / verified` | `sample01-26`, Mtool self-output, `ZzzAiContextStandardOutputTest` |
| `AUDIT-MD` | `planned` | current plan only / 現在は計画のみ |

## Database Layer Matrix / DB layer matrix

English:
Database support must always name the layer. PostgreSQL Input / Output support does not mean PostgreSQL config-store support.

日本語:
DB 対応は必ず layer を指定します。PostgreSQL Input / Output 対応は、PostgreSQL config-store 対応を意味しません。

| Layer | MySQL / MariaDB | SQLite | PostgreSQL | SQL Server / Oracle |
| --- | --- | --- | --- | --- |
| Mtool config store | `current default` | `current lightweight profile` | Not current support / 現行対応ではない | Not current support / 現行対応ではない |
| Imported user database | `current mainline` | `verified representative lane` | `opt-in verified lane` | `parked / future candidate` |
| Generated DBAccess runtime | `current / verified` | `verified representative contracts` | `opt-in representative contracts` | `parked / future candidate` |
| Scenario examples | `current input examples` | `current input examples` | `current input examples` | Future only / 将来候補 |

## Tutorial Coverage / tutorial coverage

| Range | English | 日本語 |
| --- | --- | --- |
| `sample01-04` | DataClass basics: simple table, nullable/default/status, lookup, parent-child. | DataClass 基礎: simple table、nullable/default/status、lookup、parent-child。 |
| `sample05-10` | DBAccess basics: select, filter/sort/page, CRUD, join, aggregate, mini CRUD flow. | DBAccess 基礎: select、filter/sort/page、CRUD、join、aggregate、mini CRUD flow。 |
| `sample11-17` | Source Output and bundle: HTML, external DB import, OpenAPI, custom proxy, metadata bundle, auth proxy, capstone. | Source Output と bundle: HTML、external DB import、OpenAPI、custom proxy、metadata bundle、auth proxy、capstone。 |
| `sample18` | Instruction-driven task board demo with HTTP smoke. | instruction-driven task board demo と HTTP smoke。 |
| `sample19-26` | JSON-first / ebook CMS lane through headless CMS capstone. | JSON-first / ebook CMS lane から headless CMS capstone まで。 |

## Verification Commands / 検証 command

English:
Use these commands as evidence anchors when writing a verification summary.

日本語:
検証 summary を書く時は、次の command を evidence anchor として使います。

```bash
make sample01-pack-runtime-test
make sample10-pack-runtime-test
make sample17-pack-runtime-test
make test
make sample18-pack-runtime-test
make sample26-pack-runtime-test
make user-db-contract-test
make postgresql-user-db-test-local
```

Full suite baseline / full suite 基準:

```bash
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

English:
PostgreSQL live lanes are opt-in. Use `make postgresql-user-db-test-local` for the local compose-backed completion gate, or set explicit `MTOOL_RUNTIME_PGSQL_*` values for an existing PostgreSQL database.

日本語:
PostgreSQL live lane は opt-in です。local compose-backed completion gate には `make postgresql-user-db-test-local` を使い、既存 PostgreSQL database を使う場合は明示的な `MTOOL_RUNTIME_PGSQL_*` を設定します。

## Not Proven / この matrix が証明しないこと

- Full production application readiness / production application としての完全な readiness。
- Enterprise procurement readiness / enterprise procurement readiness。
- Legal, tax, billing, HIPAA, SOC 2, or compliance expertise / 法務、税務、請求、HIPAA、SOC 2、compliance の専門性。
- Non-PHP generated output support / PHP 以外の生成 output 対応。
- SQL Server / Oracle current support / SQL Server・Oracle の現行対応。
- Modernization audit generated output / modernization audit の生成 output。

## Related Docs / 関連文書

- [Compatibility And Output Support / 対応範囲と出力サポート](compatibility-and-output-support.md)
- [Sample Tutorial Roadmap / sample 学習導線](sample-tutorial-roadmap.md)
- [Test Guide / テストガイド](../tests/README.md)
- [Deliverables / 成果物 catalog](deliverables.md)
