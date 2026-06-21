# Actual Output Backing / 実出力の裏付け

Status: `ACTUAL_OUTPUT_BACKED`

This example does not store generated output under its own `reference/` directory yet. / この example は、まだ自身の `reference/` directory には生成出力を保存していません。

Instead, it is backed by current actual-output and PostgreSQL import gates that already exist in the repository. / 代わりに、repo 内に既にある current な実出力 gate と PostgreSQL import gate に接続します。

## Current Actual Output / 現在の実出力

Use `sample12-external-db-source-import` as the current actual generated-output backing for existing schema import to DataClass output. / 既存 schema import から DataClass output までの current な実生成出力の裏付けとして、`sample12-external-db-source-import` を使います。

Actual generated reference files:

- `sample/tutorials/sample12-external-db-source-import/reference/DATACLASS-PHP/data-ExternalArticle.php`
- `sample/tutorials/sample12-external-db-source-import/reference/DATACLASS-PHP/base/data-ExternalArticleBase.php`

Verification command:

```bash
make sample12-pack-runtime-test
```

## PostgreSQL Import Gate / PostgreSQL import gate

PostgreSQL live schema import is covered by:

- `tests/Integration/Sample12PostgresqlLiveSchemaImportTest.php`

This gate verifies live PostgreSQL schema import naming behavior for an external schema. / この gate は、外部 schema の live PostgreSQL schema import naming 挙動を検証します。

Optional PostgreSQL contract command when a local PostgreSQL DSN is available:

```bash
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=15432;dbname=lab_app' \
MTOOL_RUNTIME_PGSQL_USER=lab_app \
MTOOL_RUNTIME_PGSQL_PASSWORD=lab_app_password \
make user-db-contract-test-pgsql USER_DB_CONTRACT_SAMPLE=sample12-external-db-source-import
```

## Boundary / 境界

- The files above are actual Mtool generated output from `sample12`. / 上記 file は `sample12` の Mtool 実生成出力です。
- This example's PostgreSQL-specific `accounts` / `subscriptions` / `usage_events` schema is still an input draft. / この example の PostgreSQL 固有 `accounts` / `subscriptions` / `usage_events` schema はまだ入力ドラフトです。
- Do not copy unrelated generated output into this example's `reference/` directory. / 無関係な生成出力をこの example の `reference/` directory へコピーしません。
- Add `examples/postgresql-existing-schema/reference/` only after this exact scenario is wired into Mtool and generated. / この scenario 自体を Mtool に接続して生成した後だけ、`examples/postgresql-existing-schema/reference/` を追加します。
