# 2026-05-26 Troubleshooting Doc Guide

## 要約

- `docs/troubleshooting.md` を追加し、current supported workflow で遭遇しやすい warning / error を恒久文書に集約した。
- `README.md`、`docs/start-here.md`、`docs/README.md`、`docs/choose-your-path.md`、`docs/common-tasks.md`、`docs/current-supported-workflow.md` から新文書へ辿れるようにした。
- `tests/Integration/DocsEntranceContractTest.php` を更新し、入口リンク回帰を guard した。

## 対象にしたもの

- `reference-snapshot-only`
- `config-db` preflight warning / `schema_current=false`
- bundle import preview の missing secret env warning
- invalid `db_source_key` による viewer notice / proxy `422`
- `spec_visibility` と raw `openapi.json` delivery に関する誤解
- external config DB lane に parity target が無い理由

## ねらい

- report や長い task guide に埋まっていた warning/error の意味を、1 枚の恒久文書から辿れるようにする
- 初見の contributor が「失敗した時にまずどこを見ればよいか」を入口導線から見つけやすくする
- archived helper や historical workaround を混ぜず、current mainline の切り分けだけを残す

## verification

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (6 tests, 118 assertions)`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (124 tests, 4482 assertions)`
