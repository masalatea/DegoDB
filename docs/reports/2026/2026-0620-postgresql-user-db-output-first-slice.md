# 2026-06-20 PostgreSQL User DB Output First Slice

## Status

- status: `REPRESENTATIVE_AND_EBOOK_SET_READY / OPT_IN_RUNTIME_GATE`
- scope: user DB / generated DBAccess output contract lane
- target samples: `sample06-dbaccess-filter-sort-page`, `sample08-dbaccess-join-read-model`, `sample09-dbaccess-aggregate-report`, `sample10-dbaccess-mini-crud-flow`, `sample12-external-db-source-import`, `sample13-openapi-api-surface`, `sample14-custom-proxy-runtime`, `sample16-authenticated-proxy`, `sample18-mini-task-board-demo`, `sample19-json-first-content-model-demo`, `sample21-ebook-catalog-api-demo`, `sample22-ebook-chapter-workflow-demo`, `sample23-ebook-media-metadata-demo`, `sample24-ebook-public-reader-site-demo`, `sample25-ebook-editor-auth-cms-demo`, `sample26-ebook-headless-cms-capstone`
- non-goal: Mtool config store PostgreSQL support

## Summary

PostgreSQL support is started as an opt-in user DB contract lane, not as a new Mtool config store profile.

This first slice keeps the existing canonical DBAccess output surface and adds PostgreSQL fixture / runtime contract coverage around it. The purpose is to prove that generated DBAccess classes can be exercised against PostgreSQL fixtures through the same normalized user DB contract framework used by MySQL / MariaDB and SQLite.

Current live PostgreSQL coverage:

- `sample10-dbaccess-mini-crud-flow`: CRUD list / detail / insert / update / delete.
- `sample06-dbaccess-filter-sort-page`: filter / sort / limit behavior.
- `sample08-dbaccess-join-read-model`: join read model behavior.
- `sample09-dbaccess-aggregate-report`: aggregate / group by / having behavior.
- `sample12-external-db-source-import`: PostgreSQL live schema import from `external_article`, including `external_article -> ExternalArticle` and `published_at -> publishedAt` name mapping.
- `sample18-mini-task-board-demo`: task-board DataClass + DBAccess runtime contract.
- `sample19-json-first-content-model-demo`: JSON-first public article summary DBAccess runtime contract.
- `sample21-ebook-catalog-api-demo`: ebook catalog list / detail behavior.
- `sample22-ebook-chapter-workflow-demo`: published chapter list / detail plus draft update, ordering, and publish behavior.
- `sample23-ebook-media-metadata-demo`: media delivery list / detail plus asset insert and metadata update behavior.
- `sample24-ebook-public-reader-site-demo`: public reader book, chapter, and media delivery behavior.
- `sample25-ebook-editor-auth-cms-demo`: editor chapter detail, draft update, and publish behavior.
- `sample26-ebook-headless-cms-capstone`: public CMS book / chapter / EPUB delivery plus editor draft update and publish behavior.
- schema introspection first slice: current PostgreSQL schema table / column import helper.

Representative output coverage that does not require a live PostgreSQL connection:

- `sample13-openapi-api-surface`: OpenAPI output representative.
- `sample14-custom-proxy-runtime`: custom proxy runtime representative.
- `sample16-authenticated-proxy`: authenticated generated proxy representative.

These are covered by focused PostgreSQL naming policy output contracts because their PostgreSQL-specific risk is the generated name surface created from snake_case physical metadata, not a live PostgreSQL query path.

Ebook samples were handled as an all-or-none PostgreSQL migration:

- `sample21` through `sample26` form an accumulated ebook series.
- Do not partially migrate only one ebook sample as a PostgreSQL representative.
- Either migrate the ebook series from `sample21` through `sample26` in order, or leave the whole ebook series outside this PostgreSQL representative slice.
- Current decision:
  - Representative coverage through `sample19` stayed light and stable.
  - The ebook series was then migrated in order from `sample21` through `sample26`.
  - All ebook live PostgreSQL user DB contract gates now pass.

## Changes

- `mtool/scripts/lib/user_db_contract_runtime.php`
  - Adds `pgsql` fixture SQL for the existing contract samples.
  - Adds `app_user_db_contract_runtime_prepare_pgsql_fixture()`.
- `mtool/scripts/user_db_contract_runtime_smoke.php`
  - Accepts `--dialect=pgsql`.
  - Uses `MTOOL_RUNTIME_PGSQL_*` env vars and forwards the resolved PDO DSN to generated runtime code.
- `mtool/scripts/run_user_db_contract_capture.sh`
  - Accepts `--lane=pgsql`.
  - Captures generated output and runs the PostgreSQL runtime smoke locally.
- `Makefile`
  - Adds `user-db-contract-capture-pgsql`.
  - Adds `user-db-contract-compare-pgsql`.
  - Adds `user-db-contract-test-pgsql`.
- `tests/Integration/UserDbContractManifestTest.php`
  - Fixes expected PostgreSQL fixture coverage for sample06 / sample08 / sample09 / sample10.
  - Adds expected PostgreSQL fixture / runtime definition coverage for ebook sample21 through sample26.
- `mtool/scripts/lib/user_db_contract_runtime.php`
  - Adds ebook sample21 through sample26 runtime fixtures and runners.
  - Normalizes volatile publish timestamp observations for sample25 / sample26 contract comparison while keeping before-update fixture timestamps visible.
- `mtool/app/sql_dialect.php`
  - Detects `pgsql:` DSNs and PDO `pgsql` drivers.
  - Adds PostgreSQL datetime select expression, unquoted identifier policy, information_schema table / column existence checks, server version, and current database helpers.
- `mtool/app/project_table_import_source.php`
  - Adds PostgreSQL live schema introspection through `pg_catalog`.
  - Maps PostgreSQL rows into the existing table import source shape using lowercase unquoted aliases such as `table_name` and `column_name`.
  - Keeps the shared row reader compatible with MySQL-style uppercase `information_schema` aliases such as `TABLE_NAME` and `COLUMN_NAME`.
  - Uses `current_schema()` as the PostgreSQL source schema name.
- `tests/Integration/SqlDialectTest.php`
  - Adds PostgreSQL dialect helper expectations that do not require a live PostgreSQL server.
- `tests/Integration/ProjectTableImportReviewContractTest.php`
  - Adds uppercase information_schema row conversion and lowercase PostgreSQL alias contracts.
- `tests/Integration/Sample12PostgresqlLiveSchemaImportTest.php`
  - Adds an opt-in live PostgreSQL schema import contract for sample12-style external source import.
  - Skips under plain `make test` unless `MTOOL_RUNTIME_PGSQL_DSN` is provided.
- `tests/Integration/PostgresqlRepresentativeOutputNamingTest.php`
  - Adds sample13-style OpenAPI path / schema / property contracts from snake_case physical names.
  - Adds sample14-style custom proxy contract that keeps user-defined endpoint names while carrying DBAccess step metadata.
  - Adds sample16-style authenticated proxy endpoint / handler / auth policy contracts from snake_case physical names.

## PostgreSQL Introspection Notes

PostgreSQL folds unquoted identifiers to lower case. A table created as `CREATE TABLE SupportTicket (...)` is introspected as `supportticket`, with columns like `id`, `title`, and `updatedat`.

Mtool's PostgreSQL lane intentionally avoids double-quoted identifier SQL. The first slice follows PostgreSQL's actual unquoted catalog names rather than inventing Mtool-side case restoration. Mixed-case PostgreSQL identifiers are not a target for this lane.

## Cross-DB Naming Decision

Do not implement PostgreSQL-only naming exceptions.

The next naming work should introduce a cross-DB physical / logical naming layer:

- `physical_name`: the exact table / column name observed in the source DB catalog.
- `logical_name`: the Mtool canonical name used for DataClass / field / DBAccess concepts.
- `generated_name`: the language or API-facing name derived from the logical name.

The rule should be based on the name shape, not the DB dialect. For example:

- `support_ticket` -> `SupportTicket`
- `support_ticket_id` -> `SupportTicketId`
- `updated_at` -> `UpdatedAt`
- `SupportTicket` -> `SupportTicket`
- `UpdatedAt` -> `UpdatedAt`

Generated SQL must continue to use `physical_name`. Generated PHP / OpenAPI surfaces may use `logical_name` / `generated_name`.

This keeps PostgreSQL's lower-case unquoted catalog behavior from becoming a special case and also gives MySQL / MariaDB / SQLite projects the same path for snake_case schemas.

2026-06-20 update: PostgreSQL continuation is paused until the generated name migration audit lane has a reproducible first slice. Mtool is self-hosted by generated code, so physical / logical / generated naming changes must first be treated as before / after generated artifact migration with file / symbol / docs keyword diff reports. See `docs/reports/2026/2026-0620-generated-name-migration-plan.md`.

First implementation should be a small helper / contract layer, not a repository-wide metadata migration:

1. Add a shared physical-to-logical name helper.
2. Add tests for snake_case, lower-case, PascalCase, camelCase, and simple separators.
3. Surface both physical and logical names in import review / manifest shape.
4. Do not rewrite existing canonical metadata tables until the review surface proves stable.

## Runtime Configuration

The PostgreSQL lane is opt-in and expects an existing reachable PostgreSQL database. It does not add a PostgreSQL service to every sample compose stack.

Supported environment variables:

- `MTOOL_RUNTIME_PGSQL_DSN`
- `MTOOL_RUNTIME_PGSQL_HOST`
- `MTOOL_RUNTIME_PGSQL_PORT`
- `MTOOL_RUNTIME_PGSQL_DB`
- `MTOOL_RUNTIME_PGSQL_USER`
- `MTOOL_RUNTIME_PGSQL_PASSWORD`

Example:

```bash
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' \
MTOOL_RUNTIME_PGSQL_USER=lab_app \
MTOOL_RUNTIME_PGSQL_PASSWORD=lab_app_password \
make user-db-contract-test-pgsql \
  USER_DB_CONTRACT_RUN_ID=pgsql-sample10 \
  USER_DB_CONTRACT_SAMPLE=sample10-dbaccess-mini-crud-flow
```

## Verification

Passed locally:

```bash
php -l mtool/scripts/lib/user_db_contract_runtime.php
php -l mtool/scripts/user_db_contract_runtime_smoke.php
php -l mtool/app/sql_dialect.php
php -l mtool/app/project_table_import_source.php
php -l tests/Integration/SqlDialectTest.php
php -l tests/Integration/ProjectTableImportReviewContractTest.php
bash -n mtool/scripts/run_user_db_contract_capture.sh
php -r 'require "mtool/app/sql_dialect.php"; $checks=[app_sql_dialect_from_dsn("pgsql:host=127.0.0.1;dbname=x")==="pgsql", app_sql_datetime_select_expr("pgsql","p.updated_at","updated_at")==="to_char(p.updated_at, '\''YYYY-MM-DD HH24:MI:SS'\'') AS updated_at", app_sql_identifier("pgsql", "project_source_output")==="project_source_output"]; if (in_array(false,$checks,true)) { var_export($checks); exit(1); } echo "pgsql sql dialect helper OK\n";'
php -r 'require "mtool/app/project_table_import_source.php"; $tables=app_project_table_import_source_tables_from_information_schema_rows([["TABLE_NAME"=>"SupportTicket","COLUMN_NAME"=>"Id","COLUMN_TYPE"=>"bigint","IS_NULLABLE"=>"NO","COLUMN_KEY"=>"PRI","COLUMN_DEFAULT"=>null,"EXTRA"=>"auto_increment","ORDINAL_POSITION"=>1]]); var_export([$tables[0]["name"]??null,$tables[0]["columns_by_name"]["Id"]["is_key"]??null]); echo PHP_EOL;'
php mtool/scripts/user_db_contract.php manifest --root=sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/DBACCESS-PHP --dialect=pgsql --sample=sample10-dbaccess-mini-crud-flow --output=/tmp/dego-user-db-contract-pgsql-manifest.json --pretty
php mtool/scripts/user_db_contract.php compare --left=/tmp/dego-user-db-contract-pgsql-manifest.json --right=/tmp/dego-user-db-contract-pgsql-manifest.json --output=/tmp/dego-user-db-contract-pgsql-compare.json --pretty
make -n user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-first-slice USER_DB_CONTRACT_SAMPLE=sample10-dbaccess-mini-crud-flow
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml --run-script=./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh --phpunit-target=/var/www/tests/Integration/UserDbContractManifestTest.php
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml --run-script=./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh --phpunit-target=/var/www/tests/Integration/SqlDialectTest.php
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml --run-script=./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh --phpunit-target=/var/www/tests/Integration/ProjectTableImportReviewContractTest.php
make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-pgsql-regression USER_DB_CONTRACT_SAMPLE=sample10-dbaccess-mini-crud-flow
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=15432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD=lab_app_password make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-live USER_DB_CONTRACT_SAMPLE=sample10-dbaccess-mini-crud-flow
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=15432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD=lab_app_password make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample06 USER_DB_CONTRACT_SAMPLE=sample06-dbaccess-filter-sort-page
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=15432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD=lab_app_password make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample08 USER_DB_CONTRACT_SAMPLE=sample08-dbaccess-join-read-model
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=15432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD=lab_app_password make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample09 USER_DB_CONTRACT_SAMPLE=sample09-dbaccess-aggregate-report
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample18 USER_DB_CONTRACT_SAMPLE=sample18-mini-task-board-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample19 USER_DB_CONTRACT_SAMPLE=sample19-json-first-content-model-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample21 USER_DB_CONTRACT_SAMPLE=sample21-ebook-catalog-api-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample22 USER_DB_CONTRACT_SAMPLE=sample22-ebook-chapter-workflow-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample23 USER_DB_CONTRACT_SAMPLE=sample23-ebook-media-metadata-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample24 USER_DB_CONTRACT_SAMPLE=sample24-ebook-public-reader-site-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample25 USER_DB_CONTRACT_SAMPLE=sample25-ebook-editor-auth-cms-demo
MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=5432;dbname=lab_app' MTOOL_RUNTIME_PGSQL_USER=lab_app MTOOL_RUNTIME_PGSQL_PASSWORD='' make user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=codex-pgsql-sample26 USER_DB_CONTRACT_SAMPLE=sample26-ebook-headless-cms-capstone
php -r 'require "mtool/app/project_table_import_source.php"; $pdo=new PDO("pgsql:host=127.0.0.1;port=15432;dbname=lab_app","lab_app","lab_app_password",[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); $pdo->exec("CREATE TABLE SupportTicket (Id BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY, Title VARCHAR(255) NOT NULL, Status VARCHAR(32) NOT NULL DEFAULT '\''open'\'', Body TEXT NULL, UpdatedAt TIMESTAMP NOT NULL)"); $tables=app_project_table_import_source_tables_from_pgsql($pdo); $schema=app_project_table_import_source_pgsql_current_schema($pdo); $cols=$tables[0]["columns_by_name"]??[]; var_export([$schema, array_column($tables,"name"), $cols["id"]["is_key"]??null, $cols["id"]["extra"]??null, $cols["title"]["datatype"]??null, $cols["status"]["is_default"]??null]); echo PHP_EOL;'
php -l tests/Integration/Sample12PostgresqlLiveSchemaImportTest.php
php -r 'require "mtool/app/database.php"; require "mtool/app/project_table_import_source.php"; $db=["host"=>"127.0.0.1","port"=>"5432","name"=>"lab_app","user"=>"lab_app","password"=>"","dsn"=>"pgsql:host=127.0.0.1;port=5432;dbname=lab_app"]; $pdo=app_create_pdo_from_db_config($db); $pdo->exec("DROP TABLE IF EXISTS external_article"); $pdo->exec("CREATE TABLE external_article (id BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY, title VARCHAR(255) NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, status VARCHAR(32) NOT NULL DEFAULT '\''draft'\'', published_at TIMESTAMP NULL, body TEXT NOT NULL)"); $result=app_project_table_import_source_named_live_schema(["config_db"=>["name"=>"__not_pg__"],"database_sources"=>["sample12_pgsql_lab"=>["key"=>"sample12_pgsql_lab","label"=>"Sample12 PostgreSQL lab DB","description"=>"Sample12 PostgreSQL live schema import contract source.","source_of_truth"=>"test","db_config_key"=>"sample12_pgsql_lab","supports_live_schema_import"=>true,"supports_proxy_runtime_read"=>false,"proxy_runtime_priority"=>500,"is_canonical_store"=>false,"host"=>$db["host"],"port"=>$db["port"],"name"=>$db["name"],"user"=>$db["user"],"password"=>$db["password"],"dsn"=>$db["dsn"]]]],"SAMPLE12",["key"=>"named-live-source:sample12_pgsql_lab","label"=>"Sample12 PostgreSQL lab DB","description"=>"Sample12 PostgreSQL live schema import contract source.","database_source_key"=>"sample12_pgsql_lab","apply_supported"=>true]); if (!$result["ok"]) { fwrite(STDERR, $result["error"].PHP_EOL); exit(1); }'
```

Live PostgreSQL verification used temporary `postgres:16-alpine` containers on host ports `15432` and `5432`.

Latest full regression after the generated naming follow-up:

- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 245, Assertions: 8043, Skipped: 1`.
  - The skipped test is the opt-in live PostgreSQL sample12 contract when `MTOOL_RUNTIME_PGSQL_DSN` is not provided.

## Next

1. Decide whether to add a reusable PostgreSQL compose profile for contract gates instead of temporary ad hoc containers.
2. Keep PostgreSQL output work separate from Mtool config store portability.
3. Expand live PostgreSQL user DB contract coverage only when a new sample adds a genuinely new DBAccess behavior surface.
