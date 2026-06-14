# 2026-05-21 Resume Prompt

最新版のコピペ用再開 prompt。これは 2026-05-21 までの `Phase 2` 完了境界整理、host-only helper lane 分類、runtime reference promote 運用整理、`bootstrap_dbclasses.sh` archive、`autoload_mtool.php` の generated classmap lazy load 化、active sample pack の current numbering 整理までを反映した派生文書であり、背景と判断根拠の正本は各 report 側にある。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。

今日の到達点:
- `original-codes/` は host-side reference only のまま維持し、Docker runtime / artifact bundle / current runtime input には戻さない
- `web-admin` / `web-lab` から `/var/www/original-codes` は見えない
- `Phase 2. self-host / runtime 置換完了` は historical contract の literal `100%` ではなく、current supported runtime scope を self-host / authoritative runtime として閉じる `bounded full replacement` を完了境界として読む
- runtime dbclasses 本体は `bootstrap_data_class_count=0`、`fallback_dbaccess_count=0`、`legacy_delegate_function_count=0`
- current rollout inventory は `direct-replacement=63`、`sample-test=36`、`unclassified_non_plain_items=0`
- complex lane の current inventory は `sample/patterns/sample02-15-*` と `LegacyTopLevelDeclarationMigrationTest.php` で representative gate が一巡済みであり、個別の PHPUnit class 名は互換のため historical な `Sample9-22` を維持している
- `autoload_mtool.php` は basename compatibility を保ったまま eager include list をやめ、top-level function file preload + generated classmap lazy load の hybrid loader へ切り替えた
- active sample pack は `sample/patterns/` を `sample01-15`、`sample/legacy-projects/` を `sample51-57` の current numbering に揃えた
- `mtool/app/sample_pack_catalog.php` の category map / runtime pack list / fixture map は current numbering 順に揃え、`tests/Integration/SamplePackCatalogTest.php` に order guard を追加した
- Makefile の user-facing target は `sample01-pack-output-test` ... `sample15-pack-output-test` を正本にし、historical な `sample1-output-test` / `sample9-output-test` ... `sample22-output-test` は内部互換として残している
- `sample/README.md`、`sample/patterns/README.md`、`sample/legacy-projects/README.md`、`tests/README.md`、`tests/Integration/README.md` は current numbering 前提へ更新済み
- `ApacheHostSetting` / `ApacheHostSettingTemplate` は runtime/self-loop scope からの explicit exclusion のまま維持
- `file/blob` contract は current live metadata に無いため optional unsupported track として別扱い
- host-only helper inventory は `explicit export` / `last-resort staging` / `provenance metadata` の 3 lane に分類済み
- `export_legacy_*_reference.php` 群、`export_legacy_table_schema_reference.php`、`export_mtool_db_access_seed.php` は current durable input refresh helper として残し、archive 候補から外す
- `bootstrap_dbclasses.sh` は `mtool/old/archived-bootstrap-dbclasses/bootstrap_dbclasses.sh` へ archive 済みであり、`mtool/scripts/` と current Makefile 主系からは外した
- `make bootstrap-dbclasses` / `make bootstrap-dbclasses-runtime-reference` は archive と snapshot restore を案内して fail fast する
- host-only helper inventory の current live lane は `explicit export` / `provenance metadata` であり、`last-resort staging` は archive 済み historical lane として扱う
- runtime reference repair / rollback 主系は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...`
- `make test` / `make mtool-self-loop-check` のような verification run と、`make promote-runtime-reference` / `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` の promote candidate run は分けて扱う
- `status=stale-reference` は `latest artifact` 未採用の運用状態であり、単独では self-loop failure を意味しない
- latest artifact `20260521-023351-d52e8c8b` は promote 済みで、`php mtool/scripts/show_runtime_reference_status.php --require-current` は `up-to-date`、`needs_promote=false`、`durable_recovery_ready=true`
- promoted runtime reference と durable snapshot は artifact `20260521-023351-d52e8c8b` に一致している
- sample renumbering 後の `make test` は local の旧 `mtool-sample-sample1-simple-table-*` stack が default port `33062` を掴んでいたため、`ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` で rerun し、`68 tests / 1918 assertions` で pass
- `mtool/reference/dbclasses/_support/` 直下の file は `runtime-generation-manifest.json` のみ
- 実 tool/runtime 側では `original-codes/` direct load は current zero を維持
- dbclass/runtime output と self-output artifact 側では copied output も current mainline から外れている
- `mtool` 実処理コードの historical copy は current zero-copy goal の対象外として扱う
- sample / migration test 側の `tests/fixtures/legacy-dbclasses/` は migration gate 用 input fixture として別枠に置き、self-output artifact の zero-copy 判定とは分けて扱う

重要な前提:
- `original-codes/` を Docker runtime / artifact bundle / current runtime input に戻さない
- file-based sample / migration test の入力追加が必要なら `tests/fixtures/legacy-dbclasses/` に必要最小限だけコピーする
- runtime reference repair は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を主系にする
- legacy dump が必要な export は host-side 明示実行だけで扱う
- `source_dump_path` は provenance metadata であり、runtime open path として扱わない
- `bootstrap-reference` は historical label であり、current runtime dependency を意味しない
- simple form は direct replacement、complex/new form は sample gate を通したものだけ promote する
- current inventory の未分類は `0` なので、新しい複雑系対応は `manual-classification` が出た時だけ追加する

次の優先タスク:
1. `Phase 2 core done` の shorthand は `docs/reports/2026/2026-0521-phase2-core-done-checklist.md` を基準に読む
2. sample/test 拡充を続けるなら、sample numbering / README / Make target 整理は終わっているので、その上で new sample 追加か実 project 由来 sample の棚卸しへ進む
3. historical な `Sample9-22` / `check_sample*` 名は current compat layer として残しており、rename を続けるなら target 衝突回避方針から先に決める
4. dbclass/runtime output と self-output artifact の zero-copy 判定を 1 行で誤解なく書けるようにする
5. `source_dump_path` / `bootstrap-reference` rename は DB row / manifest migration 前提の別タスクとして扱い続ける

最初に読むべき文書:
- docs/reports/2026/2026-0521-phase2-core-done-checklist.md
- docs/reports/2026/2026-0521-end-of-day-status.md
- docs/reports/2026/2026-0521-post-archive-verification-and-promotion.md
- docs/reports/2026/2026-0521-runtime-autoload-classmap-lazy-load.md
- docs/reports/2026/2026-0521-sample-pack-renumbering-and-pack-target-aliases.md
- docs/reports/2026/2026-0521-bootstrap-dbclasses-archived.md
- docs/reports/2026/2026-0521-host-only-helper-lane-classification.md
- docs/reports/2026/2026-0521-phase2-completion-boundary-refresh.md
- docs/reports/2026/2026-0521-runtime-reference-promote-operation-split.md
- docs/reports/2026/2026-0520-runtime-replacement-two-stage-rollout.md
- docs/reports/2026/2026-0520-runtime-replacement-rollout-inventory.md
- docs/internal/generated-code-strategy.md
- docs/internal/runtime-architecture.md
- docs/internal/mtool-admin-roadmap.md
- tests/README.md

最初に確認するコマンド:
- php mtool/scripts/show_runtime_reference_status.php --require-current
- php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
- docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'
- docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'
- find mtool/reference/dbclasses/_support -maxdepth 1 -type f | sort
- make help | sed -n '1,80p'
- make test
- make mtool-self-loop-check

必要なら再実行する host-side export:
- php mtool/scripts/export_mtool_db_access_seed.php --host-side --project-key=MTOOL --host=127.0.0.1 --port=33061 --db-password=config_root_local_2026 --output-dir=mtool/docker/mariadb/config-seed --dbclasses-root=mtool/reference/dbclasses --sql-dump=original-codes/mtool.sql
```
