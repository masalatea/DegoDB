# 2026-05-22 Sample03 Dataclass Lookup Helper Pack

## 結論

- `sample/tutorials/sample03-dataclass-lookup-and-helper/` を追加し、tutorial lane の 3 本目を current 化した。
- project key は `SAMPLE03` とし、legacy runtime pack の `SAMPLE3` と衝突しないようにした。
- sample03 は `TaskStatus` / `TaskPriority` の 2 lookup table と `DATACLASS-PHP` 1 output に絞り、複数 Data Class の同期と naming を確認する tutorial として固定した。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample03-dataclass-lookup-and-helper/README.md`
  - `sample/tutorials/sample03-dataclass-lookup-and-helper/compose.yaml`
  - `sample/tutorials/sample03-dataclass-lookup-and-helper/run.sh`
  - `sample/tutorials/sample03-dataclass-lookup-and-helper/seed/`
  - `sample/tutorials/sample03-dataclass-lookup-and-helper/reference/DATACLASS-PHP/`
- checker / test
  - `mtool/scripts/check_sample3_dataclass_lookup_helper_outputs.php`
  - `mtool/scripts/lib/sample3_dataclass_lookup_helper_output_check.php`
  - `tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php`
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
CREATE TABLE TaskStatus (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    StatusKey VARCHAR(40) NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Caption VARCHAR(100) NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    IsClosed TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_taskstatus_statuskey (StatusKey)
);

CREATE TABLE TaskPriority (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    PriorityKey VARCHAR(40) NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Caption VARCHAR(100) NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    Weight INT NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_taskpriority_prioritykey (PriorityKey)
);
```

この sample の `helper` は「generated Data Class に独自メソッドを足す」意味ではなく、lookup / caption を後段の formatter / service / custom layer へ逃がす前提を共有するための呼び名とした。出力対象は `DATACLASS-PHP` のみである。

## verification

- focused runtime test
  - `make sample03-pack-runtime-test`
- full suite
  - `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`

## 補足

- `TaskStatus` / `TaskPriority` の seed には最小の lookup row (`draft`, `ready`, `done`, `low`, `normal`, `high`) も入れたが、tutorial の main point は live schema import と canonical dataclass sync / output である。
- `sample03-pack-runtime-test` を canonical target とし、historical な `sample03-pack-output-test` alias は internal pattern compat layer のまま触っていない。
