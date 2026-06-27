# sample21-ebook-catalog-api-demo

English companion:
This tutorial pack moves the JSON-first ebook lane from generic content publishing into an ebook catalog API. It keeps the implementation deliberately sample-sized: book, author, series, genre, many-to-many links, one public catalog list function, one public detail function, and generated DataClass / DBAccess / OpenAPI outputs.

- project key: `SAMPLE21`
- runtime root: `work/sample-packs/sample21-ebook-catalog-api-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON`

## Original Prompt

```text
電子書籍ストアの本棚データを管理したいです。
本、著者、シリーズ、ジャンルを登録し、公開中の本をジャンルや著者で絞り込める API が欲しいです。
```

## Interpreted Scope

`sample21` は production ebook store ではなく、Mtool sample として ebook catalog API の最小公開面を見せる。

- physical tables / generated data classes:
  - `ebook_series` / `EbookSeries`
  - `ebook_author` / `EbookAuthor`
  - `ebook_genre` / `EbookGenre`
  - `ebook_book` / `EbookBook`
  - `ebook_book_author` / `EbookBookAuthor`
  - `ebook_book_genre` / `EbookBookGenre`
  - `ebook_catalog_item` / `EbookCatalogItem`
- DBAccess:
  - `EbookCatalogItem.GetPublicEbookCatalogList`
  - `EbookCatalogItem.GetPublicEbookBook`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `OPENAPI-JSON`

`ebook_catalog_item` / `EbookCatalogItem` is a materialized public read model for the sample. `draft` books are kept in the fixture data but excluded from public DBAccess functions by the `status = published` condition. `epub_status` / `EpubStatus` and `primary_epub_url` / `PrimaryEpubUrl` are metadata fields only; this sample does not generate, import, or validate EPUB files.

## Generated / Curated Boundary

- `DATACLASS-PHP` and `DBACCESS-PHP` are generated from imported table metadata and DBAccess metadata.
- `OPENAPI-JSON` is generated from single-function proxy target metadata.
- `reference/` stores actual generated output only.

## 起動

```bash
./sample/tutorials/sample21-ebook-catalog-api-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample21-ebook-catalog-api-demo/run.sh apply-seed
```

## 検証

```bash
make sample21-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Manual Flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_series

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_author

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_genre

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_book

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_book_author

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_book_genre

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE21 --source=live-schema --table=ebook_catalog_item

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE21

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE21 --source-output-key=DATACLASS-PHP --requested-by=sample21-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE21 --source-output-key=DBACCESS-PHP --requested-by=sample21-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE21 --source-output-key=OPENAPI-JSON --requested-by=sample21-manual --publish
```

## Out of Scope

- shopping cart, payment, subscription, DRM, or purchase history
- full editor UI
- role / permission management
- revision history
- file upload
- EPUB generation or EPUB parsing
- full text search engine

EPUB appears here only as delivery metadata that later samples can display. The sample does not create EPUB content.
