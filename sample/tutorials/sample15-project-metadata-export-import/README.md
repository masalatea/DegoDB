# sample15-project-metadata-export-import

## 役割

- 役割: project-scoped canonical metadata bundle を export し、preview / apply で同じ project へ import する tutorial sample pack
- project key: `SAMPLE15`
- runtime root: `work/sample-packs/sample15-project-metadata-export-import/`
- reference output: `reference/PROJECT-METADATA-BUNDLE/`

`sample11` から `sample14` は Source Output / API / proxy の generated artifact が主題です。`sample15` は generated code ではなく、設計 metadata を bundle として持ち運ぶ flow に絞ります。

## 読み方

まず `make sample15-pack-runtime-test` を実行します。manual flow は、table import / DataClass sync の後に bundle export、import preview、import apply を分けて確認するためのものです。

## 起動

```sh
./sample/tutorials/sample15-project-metadata-export-import/run.sh up
```

seed を再適用する場合:

```sh
./sample/tutorials/sample15-project-metadata-export-import/run.sh apply-seed
```

## 検証

```sh
make sample15-pack-runtime-test
```

`sample15-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample15ProjectMetadataExportImportTest.php` を実行します。

SQLite config store profile で同じ tutorial を検証:

```sh
make sample15-pack-runtime-test-sqlite
```

SQLite profile では live schema の型名が MySQL / MariaDB と異なるため、bundle section の型名完全一致ではなく、manifest / preview / apply / target summary の往復を確認します。

```sh
APP_CONFIG_STORE_DIR=work/config-store-sample15-sqlite \
  ./sample/tutorials/sample15-project-metadata-export-import/run-sqlite-config.sh up
```

## Seed 内容

- `projects`
  - `project_key=SAMPLE15`
- physical table
  - `BundleNote`
  - `Id`, `Title`, `Body`, `UpdatedAt`
- Source Output
  - `DATACLASS-PHP`
  - `artifact_strategy=canonical-dataclass-php`
  - `target_binding_type=runtime`

## 手動 flow

```sh
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample15-project-metadata-export-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE15 --source=live-schema --table=BundleNote

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample15-project-metadata-export-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE15

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample15-project-metadata-export-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/export_project_metadata.php --project-key=SAMPLE15 --output-dir=/tmp/sample15-project-metadata-bundle --requested-by=sample15-manual

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample15-project-metadata-export-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_metadata.php --bundle=/tmp/sample15-project-metadata-bundle --mode=preview --requested-by=sample15-manual

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample15-project-metadata-export-import/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_metadata.php --bundle=/tmp/sample15-project-metadata-bundle --mode=apply --requested-by=sample15-manual
```

## Scope

- `sample15` は current `project-core` bundle scope に絞る。
- `database_sources` sidecar / secret file は扱わない。
- generated code publish は扱わない。設計 metadata の export / import 再現性だけを確認する。
- `--target-project-key` による別 project import は slug uniqueness や rename policy を含むため、この sample では扱わない。

次は `sample16-authenticated-proxy` で ProjectToken auth の fail-closed behavior を確認します。
