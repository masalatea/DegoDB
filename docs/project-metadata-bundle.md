# Project Metadata Bundle

English companion:
This document explains the current project-scoped export and import bundle format for canonical metadata. It defines which scope is included today, how database-source secrets are separated, and how preview versus apply should be used safely.

この文書は、canonical metadata の project-scoped export / import bundle についての current rule をまとめた恒久文書です。  
実装メモや履歴ではなく、今どの scope を移せて、何を secret file に分離し、どう preview/apply を使うかの正本として扱います。

existing DB から output までの end-to-end 手順は [existing-db-to-output.md](existing-db-to-output.md) を正本にします。  
この文書は stage を繰り返さず、bundle transport と secret rule の detail だけを残します。

<a id="b0-when-to-read"></a>
## この文書を使う場面

- rerun path や handoff を固めたい時
  - [existing-db-to-output.md#e10-capture-rerun-path](existing-db-to-output.md#e10-capture-rerun-path)
- named source を別環境へ sidecar として持ち込みたい時
  - [existing-db-to-output.md#e3-register-source](existing-db-to-output.md#e3-register-source)
- bundle preview/apply の warning を切り分けたい時
  - [troubleshooting.md#t5-missing-secret-env-in-bundle-preview](troubleshooting.md#t5-missing-secret-env-in-bundle-preview)

この文書だけで topology や mainline を再構成しないで、先に golden path の stage を確定してから detail として使います。

## 目的

- project 単位で canonical metadata を別環境へ持ち運ぶ
- import 前に preview で差分と warning を確認する
- source 本体から secret を分離し、bundle 自体は team-visible artifact として扱いやすくする

<a id="b1-current-scope"></a>
## current scope

current first slice の scope は `project-core` です。bundle 本体には次を含みます。

- `project`
- `memberships`
- `dbtable` / `dbtablecolumns`
- `dataclass` / `dataclassfields`
- `project_db_access_*`
- `project_source_outputs`
- `project_db_access_function_source_output_targets`

次はまだ scope 外です。

- page security / host assignments
- compare outputs
- custom proxies
- project HTML
- language resource file tree

<a id="b2-export"></a>
## export

local compose では host 側 PHP ではなく container 内 CLI を使います。

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php \
  --project-key=MTOOL \
  --database-sources=reporting_db,analytics_db \
  --output-dir=/tmp/mtool-project-metadata-bundle-MTOOL \
  --requested-by=manual
```

- `--database-sources=...` を省略した時は `database_sources` sidecar を export しない
- built-in key の `db` / `config_db` / `lab_db` は `--database-sources` に入れない
- `database_sources` を含めた export では bundle root に `database-source-secrets.template.json` を自動生成する

<a id="b3-import"></a>
## import

import は `preview -> apply` の 2 段で使います。

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=preview \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=apply \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

- `--mode=apply` は current scope の core metadata を置き換えるので、先に `preview` を確認する
- `--target-project-key` を省略した時は bundle の `source_project_key` を使う
- `database_sources` sidecar は project core と違い replace-delete ではなく `source_key` 単位の upsert に固定する

<a id="b4-secret-handling"></a>
## secret の扱い

bundle の `secrets_policy` は `exclude-all` です。bundle 本体には actual password を入れません。

- `database_sources` row には `password` ではなく `has_password` だけを残す
- import preview/apply では `--database-source-secrets=PATH` の separate JSON map を使う
- populated literal secret file は commit しない
- team-visible file を残す時は env reference を優先する
- generated `database-source-secrets.template.json` は placeholder として保存してよい

### secrets file format

literal password:

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

- top-level object 直下の `key -> password` 形式でも読める
- env reference は `password_env` のほか `env` / `env_name` も alias として受ける
- env が未設定でも parse error にはせず warning に落とす

<a id="b5-database-source-rules"></a>
## database source import rule

- existing source は secret 未指定でも current password を preserve する
- new source で `has_password=true` の場合は secret 未指定で fail-closed にする
- env reference が未解決の時は preview warning になり、resolved password は empty string として扱う
- その結果、existing source は password preserve になり、new source + `has_password=true` は apply error になる

## 推奨フロー

1. export する
2. 必要なら `database-source-secrets.template.json` を元に secret file を作る
3. `preview` で warning と apply plan を確認する
4. 問題がなければ `apply` を流す

## 関連文書

- [existing-db-to-output.md](existing-db-to-output.md)
  - end-to-end の primary journey
- [current-supported-workflow.md](current-supported-workflow.md)
  - current mainline の中でこの bundle をどこで使うか
- [common-tasks.md](common-tasks.md)
  - 手元でよく使う短い実行手順
- [config-db-externalization.md](config-db-externalization.md)
  - canonical metadata の保存先としての `config_db` 運用
