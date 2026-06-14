# 2026-05-20 Resume Prompt

最新版のコピペ用再開 prompt。これは 2026-05-20 までの `original-codes/` host-only boundary 整理と provenance wording cleanup を反映した派生文書であり、背景と確認ログの正本は各 report 側にある。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。

今日の到達点:
- `ApacheHostSetting` / `ApacheHostSettingTemplate` は runtime/self-loop scope から明示除外済み
- legacy blob/file contract は `legacy-delegate` 固定で、UI / repository / seed export guard も追加済み
- `original-codes/` は host-side reference only に固定した
- root `compose.yaml` から `original-codes/` mount は削除済みで、`web-admin` / `web-lab` container から `/var/www/original-codes` は見えない
- migration sample / top-level declaration test は `tests/fixtures/legacy-dbclasses/` の curated copy を入力に使う
- `mtool/scripts/bootstrap_dbclasses.sh` は stage-only helper に固定済みで、default target は `work/legacy-recovery/dbclasses`。`--apply-to-runtime-reference` は retired し、`--target-dir` でも `mtool/reference/` 配下は拒否する。さらに実行には `--last-resort` を必須化した
- `make bootstrap-dbclasses-runtime-reference` は retired alias に変更済みで、実行すると `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を案内して fail fast する
- `make bootstrap-dbclasses` も casual path にはせず、`ACKNOWLEDGE_LAST_RESORT=1` を明示した時だけ host-side staged legacy copy を作る
- `export_legacy_*_reference.php` 群は host-side helper に整理済みで、`dataclass` / `dbtable` / `db_access` / `html` / `language_resource` は dump path helper、`table_schema` は temporary schema helper として切り分け済み。いずれも `--host-side` を付けた明示実行だけを許す
- `app_runtime_storage_legacy_dbclasses_*()` は live code / test から未使用と確認できたため削除済み
- `source_dump_path=original-codes/mtool.sql` と `bootstrap-reference` は historical / provenance metadata として残っているが、current runtime input ではない
- runtime 置換は `simple form direct replacement` と `complex/new form の Sample gate` の 2 段 rollout に整理済みで、current gate / coverage は `Sample1` / `Sample9-22` / top-level declaration test
- `sample14-buildsourcefunccache-companion-declarations` を追加し、`BuildSourceFuncCache` の 3-class + multi-helper companion declarations variant を fixture-based sample として固定した
- `sample15-buildlog-companion-declarations` を追加し、`BuildLog` の no-top-level companion declarations variant を fixture-based sample として固定した
- `sample16-livecheckresult-companion-declarations` を追加し、`LiveCheckResult` の 3-class no-top-level companion declarations variant を fixture-based sample として固定した
- `sample17-speccontent-top-level-declaration` を追加し、`SpecContent` の 1-class top-level declaration variant を fixture-based sample として固定した
- `sample18-projectuser-top-level-declaration` を追加し、`ProjectUser` の 3-class top-level declaration variant を fixture-based sample として固定した
- `sample19-htmltemplate-top-level-declaration` を追加し、`htmlTemplate` の 4-class top-level declaration variant を fixture-based sample として固定した
- `sample20-dacustomproxy-method-and-enum` を追加し、`daCustomProxy` の no-top-level method-and-enum variant を fixture-based sample として固定した
- `sample21-project-method-and-enum` を追加し、`Project` の multi-method + top-level helper method-and-enum variant を fixture-based sample として固定した
- `sample22-projectsourceoutput-method-and-enum` を追加し、`ProjectSourceOutput` の heavy multi-method + top-level helper method-and-enum variant を fixture-based sample として固定した
- top-level-declaration lane の representative coverage は `SpecContent` / `ProjectUser` / `htmlTemplate` + `LegacyTopLevelDeclarationMigrationTest.php` まで揃った
- method-and-enum lane の representative coverage は `Req` / `daCustomProxy` / `Project` / `ProjectSourceOutput` で top-level helper あり / なし + middle/heavy multi-method representative を持てる状態になった
- `export_legacy_table_schema_reference.php` の help example は host-side DSN に補正済みで、`127.0.0.1:33061` を使う temporary schema helper として読める
- `export_mtool_db_access_seed.php` を含む host-side export helper は `--host-side` を付けない限り fail fast する
- `php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php` は pass
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only` で current non-plain `data-*` 36 件を lane / sample gate ごとに棚卸しでき、`unclassified_non_plain_items=0` を確認済み
- self-generated runtime bundle と promoted reference の `_support/runtime-generation-manifest.json` は `artifact_key` provenance を持ち、promote 時には `mtool/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/{artifact_key}/` に durable snapshot も保存する
- `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` または `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` で `work/` 消失後も promoted artifact を runtime reference へ戻せる
- `php mtool/scripts/show_runtime_reference_status.php --require-current` または `make mtool-runtime-reference-status REQUIRE_CURRENT=1` で latest artifact と promoted reference の同期状態に加えて `durable_recovery_ready` も確認できる
- post-sample22 rerun の `make test` は `54 tests / 1156 assertions` で pass
- `make mtool-self-loop-check` は pass
- current latest promoted artifact は `20260520-073256-1bc9b18f`
- `php mtool/scripts/show_runtime_reference_status.php --require-current` は `up-to-date`

重要な前提:
- `original-codes/` を Docker runtime / artifact bundle / current runtime input に戻さない
- legacy dump が必要な export は host-side 明示実行で扱う
- file-based sample / migration test の入力追加が必要なら `tests/fixtures/legacy-dbclasses/` に必要最小限だけコピーする
- `source_dump_path` は provenance metadata であり、runtime open path として扱わない
- `bootstrap-reference` は historical label として残っているが、current runtime dependency を意味しない
- simple form は manifest/self-loop を崩さなければ direct replacement で進め、complex/new form は Sample 緑化後にだけ `MTOOL` へ promote する
- `mtool/reference/dbclasses/_support/` に snapshot metadata file を残さない。durable snapshot metadata は `mtool/reference/runtime-reference-snapshots/.../_support/runtime-reference-snapshot.json` だけに置く

次のタスク候補:
1. stage-only 化した `bootstrap_dbclasses.sh` 自体を archive へ退避できる条件を、残る host-only helper inventory と合わせて整理する
2. `bootstrap-reference` / `source_dump_path` を本当に rename する必要があるか、DB row / manifest migration を伴う変更として切り出すか判断する
3. simple form の未適用残件があれば direct replacement で広げ、complex/new form は Sample 追加 -> green -> promote の順に広げる
4. self-generated snapshot restore が代替できる host-only helper 導線をさらに減らす
5. 必要なら host-side export を再実行して、seed / reference JSON / manifest の current canonical state を更新する
6. `reference-snapshot-only` を運用上どう扱うか、status / UI / docs の意味づけを整理する

最初に読むべき文書:
- docs/reports/2026/2026-0520-end-of-day-status.md
- docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md
- docs/reports/2026/2026-0520-bootstrap-dbclasses-runtime-reference-isolation.md
- docs/reports/2026/2026-0520-bootstrap-dbclasses-archive-readiness.md
- docs/reports/2026/2026-0520-bootstrap-dbclasses-last-resort-gate.md
- docs/reports/2026/2026-0520-host-side-export-helper-explicit-gate.md
- docs/reports/2026/2026-0520-original-codes-helper-inventory.md
- docs/reports/2026/2026-0520-legacy-table-schema-helper-classification.md
- docs/reports/2026/2026-0520-unused-legacy-dbclasses-helper-removal.md
- docs/reports/2026/2026-0520-provenance-metadata-wording-cleanup.md
- docs/reports/2026/2026-0520-runtime-replacement-two-stage-rollout.md
- docs/reports/2026/2026-0520-runtime-replacement-rollout-inventory.md
- docs/reports/2026/2026-0520-runtime-manifest-artifact-provenance.md
- docs/reports/2026/2026-0520-runtime-reference-status-check.md
- docs/reports/2026/2026-0520-runtime-reference-durable-snapshot-recovery.md
- docs/reports/2026/2026-0520-bootstrap-dbclasses-runtime-reference-retirement.md
- docs/reports/2026/2026-0520-buildsourcefunccache-companion-declarations-sample.md
- docs/reports/2026/2026-0520-buildlog-companion-declarations-sample.md
- docs/reports/2026/2026-0520-livecheckresult-companion-declarations-sample.md
- docs/reports/2026/2026-0520-speccontent-top-level-declaration-sample.md
- docs/reports/2026/2026-0520-projectuser-top-level-declaration-sample.md
- docs/reports/2026/2026-0520-htmltemplate-top-level-declaration-sample.md
- docs/reports/2026/2026-0520-dacustomproxy-method-and-enum-sample.md
- docs/reports/2026/2026-0520-project-method-and-enum-sample.md
- docs/reports/2026/2026-0520-projectsourceoutput-method-and-enum-sample.md
- docs/reports/2026/2026-0520-post-sample22-verification-and-table-schema-usage-correction.md
- docs/internal/generated-code-strategy.md
- docs/internal/runtime-architecture.md
- tests/README.md

最初に確認するコマンド:
- rg -n "original-codes|bootstrap_dbclasses|sql-dump|source_dump_path|bootstrap-reference" mtool tests docs sample compose.yaml
- php mtool/scripts/show_runtime_reference_status.php --require-current
- find mtool/reference/dbclasses/_support -maxdepth 1 -type f | sort
- php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
- docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'
- docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'
- php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php
- make test
- make mtool-self-loop-check

必要なら再実行する host-side export:
- php mtool/scripts/export_mtool_db_access_seed.php --host-side --project-key=MTOOL --host=127.0.0.1 --port=33061 --db-password=config_root_local_2026 --output-dir=mtool/docker/mariadb/config-seed --dbclasses-root=mtool/reference/dbclasses --sql-dump=original-codes/mtool.sql
```
