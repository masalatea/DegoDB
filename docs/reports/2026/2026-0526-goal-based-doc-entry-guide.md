# 2026-05-26 Goal-Based Doc Entry Guide

## 要約

- `docs/choose-your-path.md` を追加し、初見の contributor が「何をしたいか」から current doc と最初のコマンドを逆引きできるようにした。
- `README.md`、`docs/start-here.md`、`docs/README.md` から新ガイドへリンクを追加した。
- `tests/Integration/DocsEntranceContractTest.php` に新しい入口リンクを追加し、入口導線の回帰を guard した。

## ねらい

- 既存の入口文書は「読む順番」や「文書の役割分担」は整理できていたが、初見の人は自分の目的に対してどの文書へ入るべきかを都度判断する必要があった。
- `common-tasks.md` は task 手順、`current-supported-workflow.md` は mainline 定義に寄っているため、その手前で目的別に分岐できる薄い入口が必要だった。
- history ではなく date-less doc を source of truth として読む順番を、より短い判断で選べる状態にしたかった。

## 実施内容

- 新しい恒久文書 `docs/choose-your-path.md` を追加した。
- goal table で次を 1 画面にまとめた。
  - repo 全体像
  - local default stack
  - external config DB
  - `MTOOL` canonical import / sync
  - canonical metadata bundle export / import preview
  - tutorial sample
  - green-state verification
  - external named source -> Lab Swagger
  - runtime reference status
- 典型的な 3 ルートとして次をコード付きで整理した。
  - new contributor local route
  - shared / external config DB route
  - green-state verification route
- `README.md`、`docs/start-here.md`、`docs/README.md` から goal-based guide へ辿れるようにした。

## 非目標

- runtime behavior や compose behavior は変えていない。
- `docs/reports/` を current spec の入口に格上げしていない。
- external lane の `start/stop/reset/shell` parity や OpenAPI public raw route policy は今回の doc 追加では再変更していない。

## verification

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (6 tests, 109 assertions)`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (124 tests, 4473 assertions)`
