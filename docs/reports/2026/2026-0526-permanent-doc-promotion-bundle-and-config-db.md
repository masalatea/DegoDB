# 2026-05-26 Permanent Doc Promotion For Bundle / Config DB

## 要約

- `docs/project-metadata-bundle.md` を追加し、project-scoped canonical metadata bundle の current rule を恒久文書へ昇格した。
- `docs/config-db-externalization.md` を追加し、`config_db` local overlay / external lane / preflight / migrate / raw compose fallback の current rule を恒久文書へ昇格した。
- `README.md`、`docs/start-here.md`、`docs/README.md`、`docs/choose-your-path.md`、`docs/current-supported-workflow.md`、`docs/common-tasks.md` を更新し、詳細 rule を専用 doc へ寄せた。
- `tests/Integration/DocsEntranceContractTest.php` を更新し、入口リンク回帰を guard した。

## 背景

- export/import と config DB externalization は実装・検証済みだったが、stable rule が主に report と長い task guide に埋まっていた。
- 入口文書を読んだ人が current rule を見つけても、security-sensitive な secret handling や external lane の boundary を専用の正本で読めない状態だった。
- history を読まずに current rule へ到達できる形へ揃える必要があった。

## 実施内容

- `docs/project-metadata-bundle.md`
  - current scope (`project-core`)
  - export / import CLI
  - `database_sources` sidecar
  - `database-source-secrets` separate file
  - `password_env` / `env` / `env_name`
  - existing source preserve / new source fail-closed
  - scope 外 metadata
- `docs/config-db-externalization.md`
  - `config_db` / `db` / `db-lab` の役割
  - base compose と local overlay
  - external lane の supported target
  - preflight / migrate
  - `APP_DB_*` と `APP_CONFIG_DB_*` の warning boundary
  - raw `docker compose -f compose.yaml ...` fallback
  - parity 非採用の boundary
- 既存 doc の該当節は、短い手順とリンクを残す形に薄くした。

## verification

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (6 tests, 115 assertions)`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (124 tests, 4479 assertions)`
