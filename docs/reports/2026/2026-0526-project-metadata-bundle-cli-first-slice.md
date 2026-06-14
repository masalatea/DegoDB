# 2026-05-26 Project Metadata Bundle CLI First Slice

## 要約

- canonical metadata の project-scoped export / import CLI first slice を追加した。
- first slice の scope は `project-core` に固定し、`preview -> apply` の 2 段で import する。
- secret は first slice では一切 bundle に含めず、`secrets_policy=exclude-all` を `manifest.json` に明記した。
- local compose での current 実行経路は host PHP ではなく `docker compose exec -T web-admin php ...` とした。

## 追加したもの

- `mtool/app/project_metadata_bundle.php`
  - `project-core` bundle の export / load / validate / import preview / import apply を追加した
  - `manifest.json` に `bundle_type` / `schema_version` / `scope` / checksum / excluded section / secrets policy を残すようにした
  - import apply は config DB に対して core scope の replace を direct SQL で行う
- `mtool/scripts/export_project_metadata.php`
  - project-scoped export CLI を追加した
- `mtool/scripts/import_project_metadata.php`
  - bundle preview / apply CLI を追加した
- `tests/Integration/ProjectMetadataBundleContractTest.php`
  - export -> mutate -> preview -> apply -> re-export round trip の contract を追加した
  - scope 外 row として `compare_output` が preserve されることを確認した

## first slice の scope

bundle に含めるもの:

- `project`
- `memberships`
- `dbtable` / `dbtablecolumns`
- `dataclass` / `dataclassfields`
- `project_db_access_*` core metadata
- `project_source_outputs`
- `project_db_access_function_source_output_targets`

bundle から外すもの:

- `database_sources`
- page security / host assignments
- compare outputs / compare assets
- custom proxies
- project HTML / HTML source bindings / global HTML templates
- language resource file tree

## 実装上の注意

- bundle repository access は `db=config_db` を明示して canonical config DB へ固定した
  - 既存 repository の一部がまだ `db` を見るため、bundle service 側で明示上書きしている
- CLI import の default target は `bundle source_project_key` で、空文字 override を渡しても fallback が潰れないようにした
  - first implementation では wrapper が `target_project_key=''` を渡すため preview が失敗していた
  - service 側で空文字を `source_project_key` へ正規化し、contract test に回帰 guard を入れた
- local compose の `.env` は `db-config` / `db-lab` の service name を前提にしているため、host 側 `php mtool/scripts/...` では name 解決できないことがある
  - current supported workflow では container 内から CLI を実行する

## current CLI

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php \
  --project-key=MTOOL \
  --output-dir=/tmp/mtool-project-metadata-bundle-MTOOL \
  --requested-by=manual

docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=preview \
  --requested-by=manual
```

- `--mode=apply` は core scope を replace するので、first slice では preview を先に必須とする
- `--target-project-key=...` を省略した時は bundle の `source_project_key` を使う
- `database_sources` や secret は含まれないため、external DB source の接続情報移送にはまだ使わない

## 検証

```bash
php -l mtool/app/project_metadata_bundle.php
php -l mtool/scripts/export_project_metadata.php
php -l mtool/scripts/import_project_metadata.php
php -l tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php --project-key=MTOOL --output-dir=/tmp/mtool-project-metadata-cli-smoke-20260526-001 --requested-by=codex-smoke
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php --bundle=/tmp/mtool-project-metadata-cli-smoke-20260526-001 --mode=preview --requested-by=codex-smoke
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `ProjectMetadataBundleContractTest`: `OK (1 test, 52 assertions)`
- `OpenApiSourceOutputContractTest`: `OK (15 tests, 1627 assertions)`
- full suite: `OK (113 tests, 4214 assertions)`
- CLI smoke:
  - `MTOOL` export bundle を生成できること
  - `--target-project-key` 省略時でも preview が `target_project_key=MTOOL` で通ること
  - preview summary に excluded row count と preserve warning が出ること

## 次の段

- bundle から secret をどう分離するかの rule を決める
- `database_sources` をどう export/import 対象へ入れるかを設計する
- config DB externalization の bootstrap / preflight を `APP_CONFIG_DB_*` 前提で設計する
