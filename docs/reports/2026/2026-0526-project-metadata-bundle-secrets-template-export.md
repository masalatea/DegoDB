# 2026-05-26 Project Metadata Bundle Secrets Template Export

## 要約

- `database_sources` を bundle export に含めた時、password を含まない `database-source-secrets.template.json` を自動生成するようにした。
- template は `database_source_passwords.{source_key}=""` の placeholder だけを持ち、actual password は bundle 本体にも template にも出さない。
- import は従来どおり `--database-source-secrets=PATH` で separate secrets map を受ける。template を埋めた local file をそのまま渡せる。

## 追加したもの

- `mtool/app/project_metadata_bundle.php`
  - `database-source-secrets.template.json` の payload builder と export write を追加した
  - `manifest.json` に `supplemental_files.database_source_secrets_template` を追加した
- `tests/Integration/ProjectMetadataBundleContractTest.php`
  - template file が出ること
  - template に actual password が含まれないこと
  - `database_source_passwords` map が empty placeholder になること
    を確認する assert を追加した

## current rule

- bundle export に `--database-sources=...` を付けた時だけ、bundle root に次が増える。

```text
database-source-secrets.template.json
```

- template 例:

```json
{
  "bundle_type": "mtool-project-metadata-database-source-secrets-template",
  "schema_version": "2026-05-26.database-source-secrets-template.v1",
  "source_project_key": "MTOOL",
  "generated_at": "2026-05-26T00:00:00+00:00",
  "instructions": [
    "Fill database_source_passwords locally before import apply.",
    "Do not commit populated secrets files."
  ],
  "database_source_passwords": {
    "reporting_db": ""
  }
}
```

- template は import loader と互換な JSON shape にしている
- 必要なら entry を `{ "password_env": "ENV_NAME" }` に置き換えて env-reference file として使える
- empty string は secret 未指定として扱う
- template を埋めた file を `--database-source-secrets=...` にそのまま渡せる

## current workflow

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php \
  --project-key=MTOOL \
  --database-sources=<source_key> \
  --output-dir=/tmp/mtool-project-metadata-bundle-MTOOL \
  --requested-by=manual

# bundle root に database-source-secrets.template.json が出る
# local で password を埋めた file を別名保存して import 側へ渡す

docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=preview \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

## 検証

```bash
php -l mtool/app/project_metadata_bundle.php
php -l tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php --project-key=MTOOL --database-sources=ext_smoke_05250555145828 --output-dir=/tmp/mtool-project-metadata-cli-smoke-20260526-003 --requested-by=codex-smoke
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```
