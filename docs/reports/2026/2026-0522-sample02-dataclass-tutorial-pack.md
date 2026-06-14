# 2026-05-22 Sample02 Dataclass Tutorial Pack

## 結論

- `sample/tutorials/sample02-dataclass-nullable-default-status/` を追加し、tutorial lane の 2 本目を current 化した。
- project key は `SAMPLE02` とし、legacy runtime pack の `SAMPLE2` と衝突しないようにした。
- sample02 は `Task` 1 table と `DATACLASS-PHP` 1 output に絞り、`DB Access` へ進む前の Data Class tutorial として固定した。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample02-dataclass-nullable-default-status/README.md`
  - `sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml`
  - `sample/tutorials/sample02-dataclass-nullable-default-status/run.sh`
  - `sample/tutorials/sample02-dataclass-nullable-default-status/seed/`
  - `sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP/`
- checker / test
  - `mtool/scripts/check_sample2_dataclass_nullable_default_status_outputs.php`
  - `mtool/scripts/lib/sample2_dataclass_nullable_default_status_output_check.php`
  - `tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php`
- catalog / docs / target
  - `mtool/app/sample_pack_catalog.php`
  - `tests/Integration/SamplePackCatalogTest.php`
  - `tests/bootstrap.php`
  - `Makefile`
  - `sample/README.md`
  - `sample/tutorials/README.md`
  - `tests/README.md`
  - `tests/Integration/README.md`
  - `docs/sample-tutorial-roadmap.md`

## schema

```sql
CREATE TABLE Task (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    SortOrder INT NOT NULL DEFAULT 0,
    IsPinned TINYINT(1) NOT NULL DEFAULT 0,
    PublishedAt DATETIME NULL,
    Note TEXT NULL,
    PRIMARY KEY (Id)
);
```

この sample は nullable / default / bool / status-like column を 1 table に集めているが、出力対象は `DATACLASS-PHP` のみとした。

## verification

- `bash mtool/scripts/check_sample_pack_compose_smoke.sh --pack=sample02-dataclass-nullable-default-status`
  - pass
- `docker compose -f compose.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php`
  - pass (`1 test / 7 assertions`)
- `docker compose -f compose.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SamplePackCatalogTest.php`
  - pass (`11 tests / 510 assertions`)
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - pass (`69 tests / 1954 assertions`)

## 補足

- generated `DATACLASS-PHP` は wrapper/base の 2 file (`data-Task.php`, `base/data-TaskBase.php`) で、default 値や nullable の振る舞いは主に import/sync metadata 側の tutorial として読む。
- `sample02-pack-runtime-test` を canonical target とし、historical な `sample02-pack-output-test` alias は internal pattern compat layer のまま触っていない。
