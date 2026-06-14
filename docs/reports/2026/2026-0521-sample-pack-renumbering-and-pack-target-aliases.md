# 2026-05-21 Sample Pack Renumbering And Pack Target Aliases

## 背景

- active sample pack の directory rename 自体は先行で完了していたが、catalog 配列順、README、Makefile の user-facing 導線に historical numbering が残っていた
- user goal は、`sample/patterns/` を simple-to-complex の順で読めるようにし、`sample/legacy-projects/` は別帯として番号を分けて見通しをよくすることだった

## 今回の整理

- `sample/patterns/` は `sample01-15` を current catalog として固定した
- `sample/legacy-projects/` は `sample51-57` を current catalog として固定した
- `mtool/app/sample_pack_catalog.php` の category map / runtime pack list / reference-only sample list / fixture map を新順序へ揃えた
- `tests/Integration/SamplePackCatalogTest.php` に category order guard を追加し、pattern / legacy 両方の並びを static に固定した
- `tests/Integration/LegacyProjectSampleCatalogTest.php` の expected pack order を `sample51 -> sample57` に揃えた
- `sample/README.md`、`sample/patterns/README.md`、`sample/legacy-projects/README.md`、`tests/README.md`、`tests/Integration/README.md` を current numbering 前提へ更新した

## Makefile 方針

- historical な `sample1-output-test` / `sample9-output-test` ... `sample22-output-test` は内部互換として残した
- ただし `sample10-output-test` から `sample15-output-test` は新 numbering と衝突するため、同名を別 meaning へ repurpose しない方針にした
- user-facing には新しく `sample01-pack-output-test` ... `sample15-pack-output-test` を追加した
- sample README では新しい `sampleXX-pack-output-test` を正本にし、PHP check script / PHPUnit class 名は historical な `sample9-22` / `Sample9-22` を互換維持として残した

## 検証

- `php -l mtool/app/sample_pack_catalog.php`
- `php -l tests/Integration/SamplePackCatalogTest.php`
- `php -l tests/Integration/LegacyProjectSampleCatalogTest.php`
- `make help`
- `make test`

`make test` の初回実行では、旧 `mtool-sample-sample1-simple-table-db-lab-1` が `33062` を使用中だったため起動に失敗した。既存 stack は触らず、`sample01` 側だけ `docker compose -f compose.yaml -f sample/patterns/sample01-simple-table-runtime/compose.yaml down -v` で片付けた上で、`ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` を実行し、`68 tests / 1918 assertions` で pass した。
