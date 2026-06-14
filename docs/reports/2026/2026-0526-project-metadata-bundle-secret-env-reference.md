# 2026-05-26 Project Metadata Bundle Secret Env Reference

## 要約

- `database_source_secrets` loader を env-reference 対応にして、raw password file を作らずに import preview/apply できるようにした。
- secrets file の各 entry は literal string に加えて `{ "password_env": "ENV_NAME" }` を受ける。
- env が未設定でも parse error にはせず preview warning に落とし、new secret-backed source だけ apply で fail-closed にする。

## 追加したもの

- `mtool/app/project_metadata_bundle.php`
  - `password_env` / `env` / `env_name` を解決する loader を追加した
  - secrets file summary に literal/env/missing-env count を追加した
  - generated template の instruction に env-reference 形式を明記した
- `tests/Integration/ProjectMetadataBundleContractTest.php`
  - missing env では preview warning になること
  - env を入れると preview/apply が通り password が restore されること
    を確認する contract を追加した

## current secrets file format

literal:

```json
{
  "database_source_passwords": {
    "reporting_db": "secret-value"
  }
}
```

env reference:

```json
{
  "database_source_passwords": {
    "reporting_db": {
      "password_env": "REPORTING_DB_PASSWORD"
    }
  }
}
```

- `password_env` のほか `env` / `env_name` も alias として読める
- env が未設定なら warning になり、resolved password は empty string として扱う
- その結果、existing source は password preserve になり、new source + `has_password=true` は fail-closed になる

## current rule

- shared / team-visible file に secret を置きたくない場合は env-reference file を使う
- local-only で一時運用する場合だけ populated literal file を使う
- generated `database-source-secrets.template.json` は placeholder として保存してよい
- populated literal file は commit しない

## 検証

```bash
php -l mtool/app/project_metadata_bundle.php
php -l tests/Integration/ProjectMetadataBundleContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectMetadataBundleContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```
