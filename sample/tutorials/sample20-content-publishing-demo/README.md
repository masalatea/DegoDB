# sample20-content-publishing-demo

English companion:
This tutorial pack turns the JSON-first entrance from `sample19` into a small content publishing demo. It keeps the implementation deliberately thin: one article table, public list/detail DBAccess functions, and generated DataClass / DBAccess / HTML / OpenAPI outputs.

- project key: `SAMPLE20`
- runtime root: `work/sample-packs/sample20-content-publishing-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`

## Original Prompt

```text
小さなメディアサイトの記事管理を作りたいです。
記事、カテゴリ、公開状態、公開日時を管理して、
公開済み記事の一覧と詳細を HTML と API で見られるようにしてください。
```

## Interpreted Scope

`sample20` は production CMS ではなく、Mtool sample として content publishing の最小公開面を見せる。

- table:
  - `ContentArticle`
- DBAccess:
  - `ContentArticle.GetPublishedContentArticleList`
  - `ContentArticle.GetPublishedContentArticle`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`

`draft` articles are kept in the fixture data but excluded from public DBAccess functions.

## Generated / Curated Boundary

- `DATACLASS-PHP` and `DBACCESS-PHP` are generated from imported table metadata and DBAccess metadata.
- `OPENAPI-JSON` is generated from single-function proxy target metadata.
- `HTML-PAGE` publishes the curated module under `mtool/reference/html-modules/sample20/HTML-PAGE/current/`.
- `reference/` stores actual generated output only.

## 起動

```bash
./sample/tutorials/sample20-content-publishing-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample20-content-publishing-demo/run.sh apply-seed
```

## 検証

```bash
make sample20-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Manual Flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample20-content-publishing-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE20 --source=live-schema --table=ContentArticle

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample20-content-publishing-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE20

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample20-content-publishing-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE20 --source-output-key=DATACLASS-PHP --requested-by=sample20-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample20-content-publishing-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE20 --source-output-key=DBACCESS-PHP --requested-by=sample20-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample20-content-publishing-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE20 --source-output-key=HTML-PAGE --requested-by=sample20-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample20-content-publishing-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE20 --source-output-key=OPENAPI-JSON --requested-by=sample20-manual --publish
```

## Out of Scope

- full editor UI
- write API
- role / permission management
- revision history
- file upload
- EPUB display or download

EPUB enters later in the ebook lane as an existing asset displayed by the site, not as a generated artifact.
