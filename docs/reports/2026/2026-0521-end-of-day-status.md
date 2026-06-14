# 2026-05-21 End Of Day Status

## 結論

- 2026-05-21 の停止点として、`Phase 2` の完了境界、host-only helper inventory、runtime reference promote 運用の読み方、`autoload_mtool.php` の generated classmap lazy load 化に加えて、active sample pack の current numbering と Makefile / README 導線を揃えた。
- `RUNTIME-DBCLASSES` の current latest promoted artifact は `20260521-023351-d52e8c8b` であり、`php mtool/scripts/show_runtime_reference_status.php --require-current` は `up-to-date`、`needs_promote=false`、`durable_recovery_ready=true`。
- runtime replacement 本体は `simple=63` / `complex=36` / `unclassified_non_plain_items=0` の current inventory を超えて新規残件を掘る段階ではなく、`bounded full replacement` の done 判定と tail cleanup を締める段階に入っている。

## 2026-05-21 の最終状態

- `original-codes/` は host-side reference only のまま維持
- `web-admin` / `web-lab` から `/var/www/original-codes` は見えない前提を維持
- `Phase 2. self-host / runtime 置換完了` は `historical contract の literal 100%` ではなく、`bounded full replacement` を完了境界として読む
- runtime dbclasses 本体は `bootstrap_data_class_count=0`、`fallback_dbaccess_count=0`、`legacy_delegate_function_count=0`
- current rollout inventory は `direct-replacement=63`、`sample-test=36`、`unclassified_non_plain_items=0`
- complex lane の current inventory は `sample/patterns/sample02-15-*` と `LegacyTopLevelDeclarationMigrationTest.php` で representative gate が一巡済みであり、個別の PHPUnit class 名は互換のため historical な `Sample9-22` を維持している
- `ApacheHostSetting` / `ApacheHostSettingTemplate` は runtime bundle scope からの explicit exclusion のまま維持
- `file/blob` contract は current live metadata に無いため optional unsupported track のまま維持
- `autoload_mtool.php` は basename compatibility を保ったまま eager include list をやめ、top-level function file preload + generated classmap lazy load の hybrid loader へ切り替えた
- active sample pack は `sample/patterns/` を `sample01-15`、`sample/legacy-projects/` を `sample51-57` の current numbering に揃えた
- `mtool/app/sample_pack_catalog.php` は pattern / legacy とも simple-to-complex order に揃え、`tests/Integration/SamplePackCatalogTest.php` で order guard を追加した
- Makefile の user-facing target は `sample01-pack-output-test` ... `sample15-pack-output-test` を正本にし、historical な `sample1-output-test` / `sample9-output-test` ... `sample22-output-test` は内部互換として残した
- `sample/README.md`、`sample/patterns/README.md`、`sample/legacy-projects/README.md`、`tests/README.md`、`tests/Integration/README.md` を current numbering 前提へ更新した
- `bootstrap_dbclasses.sh` は `mtool/old/archived-bootstrap-dbclasses/` へ archive 済み
  - `mtool/scripts/` と current Makefile 主系からは外した
  - `make bootstrap-dbclasses` / `make bootstrap-dbclasses-runtime-reference` は archive と snapshot restore を案内して fail fast する
- 実 tool/runtime 側では `original-codes/` direct load は current zero を維持
- dbclass/runtime output と self-output artifact 側では copied output も current mainline から外れている
- `mtool` 実処理コードの historical copy は current zero-copy goal の対象外として扱う
- sample / migration test 側の `tests/fixtures/legacy-dbclasses/` は migration gate 用 input fixture として別枠に置き、self-output artifact の zero-copy 判定とは分けて扱う
- `source_dump_path` / `bootstrap-reference` は rename ではなく provenance metadata migration の別タスクとして扱う

## 2026-05-21 に確定した運用整理

- host-only helper inventory は historical には `explicit export` / `last-resort staging` / `provenance metadata` の 3 lane で整理した
- current live lane として残すのは `explicit export` / `provenance metadata` だけで、`last-resort staging` は `mtool/old/archived-bootstrap-dbclasses/` へ archive した
- `export_legacy_*_reference.php` 群、`export_legacy_table_schema_reference.php`、`export_mtool_db_access_seed.php` は current durable input refresh helper として残す
- `make test` / `make mtool-self-loop-check` のような verification run と、`make promote-runtime-reference` / `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` の promote candidate run は分けて扱う
- `status=stale-reference` は `latest artifact` 未採用の運用状態であり、単独では self-loop failure を意味しない
- sample pack renumbering 後の `make test` は、local で旧 `mtool-sample-sample1-simple-table-*` stack が default port `33062` を掴んでいたため、そのままでは bind conflict で失敗した
- 既存 stack は落とさず、`sample01` 側だけ `docker compose -f compose.yaml -f sample/patterns/sample01-simple-table-runtime/compose.yaml down -v` で cleanup した上で、`ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` を実行し、`68 tests / 1918 assertions` で pass した
- runtime artifact は今回触っていないため、latest promoted baseline は引き続き `20260521-023351-d52e8c8b` と読む
- archived `bootstrap_dbclasses.sh` をもし戻すなら、理由は `diff / inspection` ではなく、`host-side quarantined full-tree emergency preparation` に限る

## 次回最初に見るべきこと

1. `Phase 2 core done` の shorthand は `docs/reports/2026/2026-0521-phase2-core-done-checklist.md` を基準に読む
2. sample/test 拡充を続けるなら、sample numbering / README / Make target の整理は完了済みなので、その上で new sample 追加か実 project 由来 sample の棚卸しに入る
3. historical な `Sample9-22` / `check_sample*` 名は current compat layer として残しており、rename を続けるなら衝突回避方針から先に決める
4. dbclass/runtime output と self-output artifact の zero-copy 判定を 1 行で誤解なく書けるようにする
5. `source_dump_path` / `bootstrap-reference` rename は current mainline から外し、DB row / manifest migration 前提の別トラックとして扱い続ける

## 参照

- `docs/reports/2026/2026-0521-host-only-helper-lane-classification.md`
- `docs/reports/2026/2026-0521-bootstrap-dbclasses-staged-copy-use-inventory.md`
- `docs/reports/2026/2026-0521-bootstrap-dbclasses-archived.md`
- `docs/reports/2026/2026-0521-post-archive-verification-and-promotion.md`
- `docs/reports/2026/2026-0521-runtime-autoload-classmap-lazy-load.md`
- `docs/reports/2026/2026-0521-sample-pack-renumbering-and-pack-target-aliases.md`
- `docs/reports/2026/2026-0521-phase2-core-done-checklist.md`
- `docs/reports/2026/2026-0521-phase2-completion-boundary-refresh.md`
- `docs/reports/2026/2026-0521-runtime-reference-promote-operation-split.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
