# 2026-05-20 Post-Sample22 Verification And Table Schema Usage Correction

## 結論

- `export_legacy_table_schema_reference.php` の usage 例を、container 内 DSN ではなく host-side 実行前提の `127.0.0.1:33061` に補正した。
- Docker 復旧後に sample22 追加後の full verification をやり直し、`make test` は `54 tests / 1156 assertions`、`make mtool-self-loop-check` は pass を確認した。
- self-loop で生成された latest artifact `20260520-073256-1bc9b18f` を promote し、`php mtool/scripts/show_runtime_reference_status.php --require-current` は再び `up-to-date` に戻した。

## 背景

- `2026-0520-legacy-table-schema-helper-classification.md` で、`export_legacy_table_schema_reference.php` は dump-path helper ではなく temporary schema helper と整理した。
- ただし usage の DSN 例だけは `mysql:host=db-config;port=3306;...` のままで、host-side helper だと分かっていても container 内向けの例に見えた。
- さらに Docker crash で止まっていた post-sample22 verification を、Docker 復旧後に最新状態で取り直す必要があった。

## 実施内容

- `mtool/scripts/export_legacy_table_schema_reference.php`
  - usage の DSN 例を `mysql:host=127.0.0.1;port=33061;dbname=legacy_seed_tmp;charset=utf8mb4` に更新した。
  - notes に `CONFIG_DB_HOST_PORT=33061` を使う host-side port 例を追記した。
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - Docker crash 中だった古い status を外し、latest verification 結果と promoted artifact key を反映した。
- `docs/reports/2026/README.md`
  - 本 report を index に追加した。

## 検証

- `php -l mtool/scripts/export_legacy_table_schema_reference.php`
- `php mtool/scripts/export_legacy_table_schema_reference.php --help`
- `php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php`
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
- `make test`
  - `54 tests / 1156 assertions`
- `make mtool-self-loop-check`
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - `up-to-date`
- `find mtool/reference/dbclasses/_support -maxdepth 1 -type f | sort`
  - `runtime-generation-manifest.json` だけ

## 次

1. simple lane の未適用残件があれば direct replacement で広げる
2. complex/new form は引き続き sample 追加 -> green -> promote で広げる
3. host-only helper のうち snapshot restore で代替できる導線をさらに減らす
