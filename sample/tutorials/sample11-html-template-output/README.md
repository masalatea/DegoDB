# sample11-html-template-output

- canonical project key: `SAMPLE11`
- 役割: HTML template / HTML Source Output を `html-module-catalog` strategy で publish する最小 tutorial sample pack
- seed は `SAMPLE11` project、HTML module 用 `project_source_outputs` row、HTML source binding、HTML definition / parameter、global HTML template / parameter metadata を作る
- source module の正本は `mtool/reference/html-modules/sample11/HTML-PAGE/current/`
- `original-codes/` は runtime input にしない
- durable actual output sample: `reference/HTML-PAGE/`
- disposable runtime root: `work/sample-packs/sample11-html-template-output/`

## 読み方

Quickstart と `sample01` から `sample10` を終えた後は、まず次だけ実行します。

```bash
make sample11-pack-runtime-test
```

この sample の主題は HTML authoring UI ではなく、curated HTML module tree を `HTML-PAGE` Source Output として publish することです。manual flow は test が通った後に、publish command の中身を分解して追うために使います。

## 起動

```bash
./sample/tutorials/sample11-html-template-output/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample11-html-template-output/run.sh apply-seed
```

## 検証

```bash
make sample11-pack-runtime-test
```

`sample11-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample11HtmlTemplateOutputTest.php` を実行します。

SQLite config store profile で同じ tutorial を検証:

```bash
make sample11-pack-runtime-test-sqlite
```

手元で軽く動かす場合は、DegoDB 自身の設計メタデータを `APP_CONFIG_STORE_DIR` 配下の SQLite file に保存できます。これは tutorial の user / Lab DB とは別です。

```bash
APP_CONFIG_STORE_DIR=work/config-store-sample11-sqlite \
  ./sample/tutorials/sample11-html-template-output/run-sqlite-config.sh up
```

## Seed 内容

- `project_source_outputs`
  - `source_output_key=HTML-PAGE`
  - `class_type=html`
  - `artifact_strategy=html-module-catalog`
  - `source_template_dir=catalog://html-module/SAMPLE11/HTML-PAGE`
- `html_templates`
  - `legacy_html_template_pid=110100`
  - `target_type=html`
  - `file_name=page.php`
- `project_html_definitions`
  - `html_key=SAMPLE11-PAGE`
  - `legacy_project_source_output_pid=110030`
  - `legacy_html_template_pid=110100`

## 手動 flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample11-html-template-output/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE11 --source-output-key=HTML-PAGE --requested-by=sample11-pack --publish
```

## 生成物

```text
work/source-outputs/SAMPLE11/HTML-PAGE/README.md
work/source-outputs/SAMPLE11/HTML-PAGE/page.php
```

次は `sample12-external-db-source-import` で external named source から table metadata を取り込む flow を見ます。
