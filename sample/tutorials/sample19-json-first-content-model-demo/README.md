# sample19-json-first-content-model-demo

English companion:
This tutorial pack is the JSON-first entrance for the ebook CMS demo lane. The user does not need to know database design. They provide a JSON-shaped content idea, AI interprets it as normalized tables and a public read model, and Mtool generates PHP DataClass / DBAccess artifacts from that interpreted metadata.

- project key: `SAMPLE19`
- runtime root: `work/sample-packs/sample19-json-first-content-model-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`

## Original Prompt

```text
DB のことはよく分かりません。
でも、記事や本のデータを JSON で考えることならできます。
この JSON をもとに、管理しやすい DB 構造と API の形を考えてください。
```

## User JSON

```json
{
  "article": {
    "title": "はじめての電子書籍CMS",
    "slug": "first-ebook-cms",
    "status": "published",
    "publishedAt": "2026-06-19T09:00:00+09:00",
    "author": {
      "name": "Sample Editor"
    },
    "category": {
      "name": "Guide"
    },
    "body": "JSONから始めるCMSの例です。"
  }
}
```

## AI Interpreted Data Model

The sample story treats the JSON as the user's input. AI then interprets the nested shape into normalized tables:

- physical table `json_author`, generated surface `JsonAuthor`
  - stores author values from `article.author`
- physical table `json_category`, generated surface `JsonCategory`
  - stores category values from `article.category`
- physical table `article_json_model`, generated surface `ArticleJsonModel`
  - stores the article body and foreign keys to author / category
- physical table `article_public_summary`, generated surface `ArticlePublicSummary`
  - a read model DTO for public published article lists

Implementation note: the seed SQL already contains the interpreted schema. That is intentional for a sample pack. The user-facing story is still JSON first, then AI-interpreted DB / API metadata.

## Generated DB / API Scope

- tables:
  - physical `json_author`, generated `JsonAuthor`
  - physical `json_category`, generated `JsonCategory`
  - physical `article_json_model`, generated `ArticleJsonModel`
  - physical `article_public_summary`, generated `ArticlePublicSummary`
- DBAccess:
  - `ArticleJsonModel.GetPublishedArticlePublicSummaryList`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`

`GetPublishedArticlePublicSummaryList` joins `article_json_model`, `json_author`, and `json_category`, then returns only `status = published` rows.

## 起動

```bash
./sample/tutorials/sample19-json-first-content-model-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample19-json-first-content-model-demo/run.sh apply-seed
```

## 検証

```bash
make sample19-pack-runtime-test
```

SQLite config store profile でも同じ入口 sample を検証する:

```bash
make sample19-pack-runtime-test-sqlite
```

`sample20+` の ebook CMS lane は MySQL / MariaDB 正本に寄せるが、`sample19` は JSON-first tutorial entrance なので SQLite config store profile も維持する。

## Manual Flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE19 --source=live-schema --table=json_author

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE19 --source=live-schema --table=json_category

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE19 --source=live-schema --table=article_json_model

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE19 --source=live-schema --table=article_public_summary

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE19

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE19 --source-output-key=DATACLASS-PHP --requested-by=sample19-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample19-json-first-content-model-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE19 --source-output-key=DBACCESS-PHP --requested-by=sample19-manual --publish
```

## Out of Scope

- storing arbitrary JSON blobs as the runtime model
- editor UI
- OpenAPI / proxy runtime
- article create / update workflow
- roles, audit logs, revision history, file upload, search, notification, or production CMS operations

Those belong to later samples or production notes. This pack only fixes the JSON-first entrance and the first Mtool generation loop.
