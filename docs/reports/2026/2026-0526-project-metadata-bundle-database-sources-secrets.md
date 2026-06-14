# 2026-05-26 Project Metadata Bundle Database Sources / Secrets Slice

## 要約

- canonical metadata bundle に `database_sources` の optional sidecar export/import を追加した。
- `database_sources` は project bundle の default scope には自動では入れず、`--database-sources=...` で明示した key だけを bundle に含める。
- bundle 本体には `password` を含めず、`has_password` だけを残す。
- import preview/apply は `--database-source-secrets=PATH` で separate secrets map を受ける。
- separate secrets map は literal password だけでなく env-reference も使える。
- existing source は secret 未指定でも current password を preserve する。new source で `has_password=true` の場合は secret 未指定で fail-closed にする。

## 追加したもの

- `mtool/app/project_metadata_bundle.php`
  - optional `database_sources` section の export / load / validate / preview / apply を追加した
  - export 側は `database_source_keys` option を受け、built-in key (`db` / `config_db` / `lab_db`) を reject する
  - import 側は `database_source_secrets_path` option を受け、JSON secrets map を読み込む
  - schema version を `2026-05-26.project-core.v2` に上げた
- `mtool/scripts/export_project_metadata.php`
  - `--database-sources=key1,key2` を追加した
- `mtool/scripts/import_project_metadata.php`
  - `--database-source-secrets=/path/to/file.json` を追加した
- `tests/Integration/ProjectMetadataBundleContractTest.php`
  - existing source の password preserve を確認する round trip contract を追加した
  - new source の secret-required / fail-closed contract を追加した

## current rule

- default bundle:
  - `project`
  - `memberships`
  - `dbtable` / `dbtablecolumns`
  - `dataclass` / `dataclassfields`
  - `project_db_access_*`
  - `project_source_outputs`
  - `project_db_access_function_source_output_targets`
- optional sidecar:
  - `database_sources`

`database_sources` は shared catalog なので replace-delete しない。import apply は `source_key` 単位の upsert に固定した。

## secrets file format

```json
{
  "database_source_passwords": {
    "reporting_db": "secret-value",
    "analytics_db": "another-secret"
  }
}
```

- top-level object 直下に key -> password を置く形でも読める
- bundle には actual password を残さない
- `has_password=true` かつ target に row が無い source は、secret 未指定だと preview warning / apply error になる
- target に既存 row がある source は、secret 未指定なら current password を preserve する

## current CLI

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php \
  --project-key=MTOOL \
  --database-sources=reporting_db,analytics_db \
  --output-dir=/tmp/mtool-project-metadata-bundle-MTOOL \
  --requested-by=manual

docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=preview \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

## 検証

```bash
php -l mtool/app/project_metadata_bundle.php
php -l mtool/scripts/export_project_metadata.php
php -l mtool/scripts/import_project_metadata.php
php -l tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php --project-key=MTOOL --database-sources=ext_smoke_05250555145828 --output-dir=/tmp/mtool-project-metadata-cli-smoke-20260526-002 --requested-by=codex-smoke
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php --bundle=/tmp/mtool-project-metadata-cli-smoke-20260526-002 --mode=preview --requested-by=codex-smoke
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

## 残タスク

- `database_sources` secrets map の distribution rule を docs / ops 手順としてもう少し固める
- project HTML / custom proxy / language resource file tree など、bundle scope 外 metadata の slice を続ける
