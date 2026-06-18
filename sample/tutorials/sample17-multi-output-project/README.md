# sample17-multi-output-project

English companion:
This tutorial pack is the final capstone of the current tutorial lane. It keeps one small project and publishes multiple Source Outputs from it.

- project key: `SAMPLE17`
- runtime root: `work/sample-packs/sample17-multi-output-project/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`

`sample11` から `sample16` は Source Output の種類や auth 境界を個別に確認します。`sample17` はそれらを 1 project にまとめ、同じ canonical metadata から複数 artifact を publish する capstone です。

SQLite config store profile では、DegoDB の設計メタデータだけを folder-backed SQLite file に保存します。runtime 側の user database / lab database は別物として維持されるため、軽量な設定ストアと実アプリの DB を混同せずに確認できます。

## 読み方

Quickstart と `sample01` から `sample10` を終えた後は、まず次だけ実行します。

```bash
make sample17-pack-runtime-test
```

この test は import / sync 後に `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON` の 4 output を publish し、actual reference と比較します。manual flow は、この 4 output publish を 1 command ずつ分解して追うための optional path です。

## 起動

```bash
./sample/tutorials/sample17-multi-output-project/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample17-multi-output-project/run.sh apply-seed
```

## 検証

```bash
make sample17-pack-runtime-test
```

SQLite config store profile で同じ gate を見る場合:

```bash
make sample17-pack-runtime-test-sqlite
```

`sample17-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample17MultiOutputProjectTest.php` を実行します。

## Seed 内容

- project:
  - `project_key=SAMPLE17`
- live table:
  - `CapstoneTask`
- DBAccess:
  - `CapstoneTask.GetCapstoneTaskList`
  - `CapstoneTask.GetCapstoneTask`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`
- HTML module source:
  - `mtool/reference/html-modules/sample17/HTML-PAGE/current/`

## 手動 flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample17-multi-output-project/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE17 --source=live-schema --table=CapstoneTask

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample17-multi-output-project/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE17

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample17-multi-output-project/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE17 --source-output-key=DATACLASS-PHP --requested-by=sample17-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample17-multi-output-project/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE17 --source-output-key=DBACCESS-PHP --requested-by=sample17-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample17-multi-output-project/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE17 --source-output-key=HTML-PAGE --requested-by=sample17-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample17-multi-output-project/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE17 --source-output-key=OPENAPI-JSON --requested-by=sample17-manual --publish
```

## Scope

- `sample17` は current tutorial lane の final capstone として、multi-output publish の最小構成に絞る。
- Project metadata bundle export / import は `sample15` に分ける。
- ProjectToken auth は `sample16` に分ける。
- `LanguageResource` / i18n は tool scope 外なので扱わない。
