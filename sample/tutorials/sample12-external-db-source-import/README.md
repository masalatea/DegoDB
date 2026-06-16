# sample12-external-db-source-import

- canonical project key: `SAMPLE12`
- 役割: external named DB source を登録し、`named-live-schema:sample12_lab` から table import -> DataClass sync -> Source Output publish まで確認する tutorial sample pack
- config seed は `SAMPLE12` project、`database_sources` row、`DATACLASS-PHP` source output definition を作る
- lab seed は external DB 相当の物理 `ExternalArticle` table と seed row を `db-lab` に作る
- canonical `dbtable` / `dataclass` metadata は seed しない。external source import と data class sync で current metadata を作る前提
- durable actual output sample: `reference/DATACLASS-PHP/data-ExternalArticle.php`, `reference/DATACLASS-PHP/base/data-ExternalArticleBase.php`
- disposable runtime root: `work/sample-packs/sample12-external-db-source-import/`

## 読み方

まず次だけ実行します。

```bash
make sample12-pack-runtime-test
```

この sample の主題は、`db-lab` を外部 DB 相当として扱い、`named-live-schema:sample12_lab` から table metadata を import することです。`dbtable` / `dataclass` は seed せず、import / sync で作られる点を見ます。

## 起動

```bash
./sample/tutorials/sample12-external-db-source-import/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample12-external-db-source-import/run.sh apply-seed
```

## 検証

```bash
make sample12-pack-runtime-test
```

`sample12-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample12ExternalDbSourceImportOutputTest.php` を実行します。

## Seed 内容

- `database_sources`
  - `source_key=sample12_lab`
  - `host=db-lab`
  - `database_name=lab_app`
  - `supports_live_schema_import=1`
  - `supports_proxy_runtime_read=0`
- `ExternalArticle`
  - `Status=published`, `Title=External source first row`
  - `Status=draft`, `Title=Draft from external DB`

## 手動 flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample12-external-db-source-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE12 --source=named-live-schema:sample12_lab --table=ExternalArticle

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample12-external-db-source-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE12

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample12-external-db-source-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE12 --source-output-key=DATACLASS-PHP --requested-by=sample12-pack --publish
```

## 生成物

```text
work/source-outputs/SAMPLE12/DATACLASS-PHP/data-ExternalArticle.php
work/source-outputs/SAMPLE12/DATACLASS-PHP/base/data-ExternalArticleBase.php
```

次は `sample13-openapi-api-surface` で DBAccess function から OpenAPI artifact を publish します。
