# Security And Data Handling / security と data handling

English companion:
This document gives a practical security and data-handling brief for OSS evaluation, consulting intake, and implementation support. It is not a certification statement.

日本語:
この文書は、OSS 評価、コンサルティング相談、導入支援で使う security / data handling の短い説明です。認証・監査・法務上の certification statement ではありません。

## Purpose / 目的

English:
This page explains the operational boundaries for metadata, user databases, generated artifacts, bundles, and secrets. It helps external users understand what DegoDB stores and what must be handled outside the repository.

日本語:
このページは、metadata、user database、generated artifact、bundle、secret の運用境界を説明します。DegoDB が何を保存し、何を repo 外で扱うべきかを外部利用者が理解するための文書です。

## Core Boundary / 基本境界

| Area / 領域 | English | 日本語 |
| --- | --- | --- |
| DegoDB design metadata | Stored in `config_db` or the SQLite file store profile. | `config_db` または SQLite file store profile に保存する。 |
| User / business database | Used as import source or runtime source, not as the canonical metadata store. | import source または runtime source として使う。canonical metadata store ではない。 |
| Generated artifacts | Written under `work/artifacts/...` and optionally `work/source-outputs/...`. | `work/artifacts/...` と、必要に応じて `work/source-outputs/...` に出る。 |
| Metadata bundles | Team-visible transport artifacts with actual secrets excluded. | actual secret を含めない team-visible transport artifact。 |
| Secret sidecars | Separate files or env references; populated secret files are not committed. | separate file または env reference。secret 入り file は commit しない。 |
| Legacy references | Curated reference only, not runtime input. | curated reference only。runtime input ではない。 |

## Data Intake Policy / data intake policy

English:
Consulting and implementation support should prefer schema-only intake and synthetic data. Production personal data requires an explicit policy and approval before it is handled.

日本語:
コンサルティングや導入支援では、schema-only intake と synthetic data を優先します。production personal data を扱うには、明示的な policy と承認が必要です。

- Prefer schema-only intake for discovery. / discovery では schema-only を優先する。
- Use synthetic seed data for examples and public demos. / example と public demo では synthetic seed を使う。
- Do not import production personal data without explicit policy and approval. / 明示 policy と承認なしに production personal data を import しない。
- Redact table / column names when they reveal sensitive business details. / table / column 名が機密を示す場合は redaction を検討する。
- Label inferred relationships separately from schema-backed facts. / inferred relationship は schema-backed fact と分けて書く。

## Secret Handling / secret handling

English:
Project metadata bundles use `secrets_policy=exclude-all`. Secrets move through sidecar files or environment references, not through the bundle itself.

日本語:
project metadata bundle は `secrets_policy=exclude-all` を使います。secret は bundle 本体ではなく、sidecar file または environment reference で扱います。

- Do not put actual database passwords in bundle files. / bundle に actual password を入れない。
- Use generated `database-source-secrets.template.json` as a placeholder. / placeholder として `database-source-secrets.template.json` を使う。
- Prefer environment references such as `{ "password_env": "REPORTING_DB_PASSWORD" }`. / `{ "password_env": "REPORTING_DB_PASSWORD" }` のような env reference を優先する。
- Do not commit populated secret files. / secret 入り file は commit しない。
- Run preview before apply. / apply 前に preview を確認する。

## Access And Sharing / access と共有

| Category / 区分 | English | 日本語 |
| --- | --- | --- |
| Supported share lane | Authenticated viewer, admin artifact download, metadata bundle with secret sidecar separation. | authenticated viewer、admin artifact download、secret sidecar 分離付き metadata bundle。 |
| Non-goal | Public raw `openapi.json` hosting as a default path. | public raw `openapi.json` hosting を default path にしない。 |
| Non-goal | Procurement-ready enterprise controls without separate review. | 別レビューなしに procurement-ready enterprise controls を主張しない。 |
| Non-goal | Treating sample auth as complete production IAM. | sample auth を完全な production IAM として扱わない。 |

## Generated Runtime Security / generated runtime security

English:
Current generated runtime security evidence exists, but it does not replace deployment-specific review.

日本語:
現行 generated runtime security の実装根拠はありますが、deployment 固有の security review の代替ではありません。

- Static bearer authenticated proxy behavior in tutorial samples / tutorial sample の static bearer authenticated proxy。
- Fail-closed behavior for missing / malformed / wrong / missing-env bearer tokens / token missing、malformed、wrong、env missing の fail-closed behavior。
- Generated OIDC JWT bearer runtime verification / generated OIDC JWT bearer runtime verification。
- Custom proxy auth policy reference validation in metadata bundle coverage / metadata bundle coverage 内の custom proxy auth policy reference validation。

## Client Schema Handling / 顧客 schema の扱い

English:
Before accepting a real schema, decide whether names may be stored as-is, whether schema-only is enough, and where generated artifacts may be stored.

日本語:
実 schema を受け取る前に、名前をそのまま保存できるか、schema-only で足りるか、生成 artifact をどこに保存できるかを決めます。

1. Decide whether names can be stored as-is or need redaction. / 名前をそのまま保存できるか、redaction が必要かを決める。
2. Decide whether schema-only is enough. / schema-only で足りるかを決める。
3. Decide whether seed data is synthetic, sampled, or prohibited. / seed data が synthetic、sampled、prohibited のどれかを決める。
4. Decide where generated artifacts and metadata bundles may be stored. / generated artifact と metadata bundle の保存先を決める。
5. Record who can review the output. / 誰が output を review できるかを記録する。

## What Not To Claim / 言わないこと

- HIPAA, SOC 2, tax, billing, or legal compliance readiness without domain review / domain review なしの HIPAA、SOC 2、税務、請求、法務 compliance readiness。
- Sample auth flows as complete production access control / sample auth flow を完全な production access control として扱うこと。
- SQL Server / Oracle or non-PHP output support as current support / SQL Server・Oracle または PHP 以外の output を current support と言うこと。
- AI context or modernization audit output until their generators exist / generator ができる前に AI context / modernization audit output と言うこと。

## Related Docs / 関連文書

- [Storage And State Model / 保存先と状態モデル](storage-and-state-model.md)
- [Project Metadata Bundle / プロジェクトメタデータ bundle](project-metadata-bundle.md)
- [Consulting Intake / 相談前チェックリスト](consulting-intake.md)
- [Compatibility And Output Support / 対応範囲と出力サポート](compatibility-and-output-support.md)
