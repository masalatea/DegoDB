# Firebird Mtool Profile First Slice / Firebird Mtool profile 初回スライス

Status: `F100_4_FIRST_SLICE_DONE_CONFIG_DIALECT_ENTRY`

This report records the first implementation slice for adapting Mtool itself to an opt-in Firebird profile. It is intentionally not a full Firebird completion claim. The slice creates the configuration and SQL dialect entry points needed before config schema/bootstrap and repository SQL coverage can be completed.

この report は、Mtool 自身を opt-in Firebird profile に対応させる F100-4 の初回実装スライスを記録します。これは Firebird 完了宣言ではありません。config schema/bootstrap と repository SQL coverage に進む前に必要な、configuration と SQL dialect の入口を作った段階です。

## What changed / 変更点

- `APP_CONFIG_STORE_DRIVER=firebird` and `APP_CONFIG_STORE_DRIVER=fb` are now normalized to the `firebird` config-store driver.
- Firebird config-store DSN construction was added, including:
  - default Firebird config DB port `3050`
  - default charset `APP_CONFIG_FIREBIRD_CHARSET=UTF8`
  - override support through `APP_CONFIG_FIREBIRD_DSN`
- When the app DB is not explicitly overridden, the default admin DB now follows a Firebird config-store profile, matching the existing SQLite-following behavior.
- SQL dialect helpers now recognize `firebird:` DSNs and PDO driver name `firebird`.
- SQL helper coverage was extended for Firebird-shaped:
  - datetime select expressions
  - quoted identifiers
  - table existence checks through `RDB$RELATIONS`
  - column existence checks through `RDB$RELATION_FIELDS`
  - server version lookup through `RDB$GET_CONTEXT('SYSTEM', 'ENGINE_VERSION')`
  - current database name lookup through `RDB$GET_CONTEXT('SYSTEM', 'DB_NAME')`

## Non-goals for this slice / このスライスで完了していないこと

- Full Firebird config schema/bootstrap support is not complete yet.
- Config-store repository SQL has not been fully audited for Firebird-specific behavior yet.
- SQLite -> Firebird migration path is not implemented in this slice.
- Firebird -> MySQL/MariaDB migration path is not implemented in this slice.
- Normal `make test` remains Firebird-server independent; Firebird server behavior is checked through focused Docker smoke tests.

## Verification / 検証

- `php -l mtool/app/config.php`
- `php -l mtool/app/sql_dialect.php`
- `php -l tests/Integration/ConfigStoreProfileTest.php`
- `php -l tests/Integration/SqlDialectTest.php`
- `make test`
  - result: passed
  - summary: `Tests: 637, Assertions: 15410, Skipped: 5.`
- `make firebird-connection-smoke-docker`
  - result: passed
  - observed PDO driver: `firebird`
  - observed engine version: `5.0.4`
  - observed smoke table: `MTOOL_FIREBIRD_SMOKE`

During verification, the first `make test` run exposed that `APP_CONFIG_DB_PORT=3306` can be present in the test environment. The Firebird admin-default test was corrected to clear that inherited port so the Firebird default port `3050` is verified deterministically.

## Next / 次

F100-4 remains active after this slice. The next implementation work should focus on:

1. Firebird config schema/bootstrap materialization.
2. Firebird config preflight/initdb behavior.
3. Repository SQL audit for Mtool config-store paths.
4. Only after those pass, move to F100-5 SQLite -> Firebird migration path.

