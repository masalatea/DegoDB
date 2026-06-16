# Integration Tests

`tests/Integration/` は、DB や Docker 上の runtime を使って current 実装を検証する PHPUnit テストの置き場です。

主な検証 lane は次です。

- `Sample1SimpleTableOutputTest.php`
  - `sample/tutorials/sample01-simple-table-runtime` を使い、`live schema import -> data class sync -> output generate/publish -> reference 比較` を一括で検証する
- `Sample2DataclassNullableDefaultStatusOutputTest.php`
  - `sample/tutorials/sample02-dataclass-nullable-default-status` を使い、nullable / default / status-like column を含む Data Class output の tutorial lane を検証する
- `Sample3DataclassLookupAndHelperOutputTest.php`
  - `sample/tutorials/sample03-dataclass-lookup-and-helper` を使い、複数 lookup table を Data Class として同期する tutorial lane を検証する
- `Sample4DataclassParentChildBasicOutputTest.php`
  - `sample/tutorials/sample04-dataclass-parent-child-basic` を使い、親 table と child table を含む schema の Data Class tutorial lane を検証する
- `Sample5DbAccessSelectBasicOutputTest.php`
  - `sample/tutorials/sample05-dbaccess-select-basic` を使い、1 table + 1 db access class + 1 selectlist function の最小 DB Access tutorial lane を検証する
- `Sample6DbAccessFilterSortPageOutputTest.php`
  - `sample/tutorials/sample06-dbaccess-filter-sort-page` を使い、1 argument filter + fixed sort + limit pagination の list DB Access tutorial lane を検証する
- `Sample7DbAccessCrudBasicOutputTest.php`
  - `sample/tutorials/sample07-dbaccess-crud-basic` を使い、1 table + insert/update/delete write metadata の DB Access tutorial lane を検証する
- `Sample8DbAccessJoinReadModelOutputTest.php`
  - `sample/tutorials/sample08-dbaccess-join-read-model` を使い、2 live table + 1 read model table + joined select metadata の DB Access tutorial lane を検証する
- `Sample09DbAccessAggregateReportOutputTest.php`
  - `sample/tutorials/sample09-dbaccess-aggregate-report` を使い、2 live table + 1 report model table + grouped select / having metadata の DB Access tutorial lane を検証する
- `Sample10DbAccessMiniCrudFlowOutputTest.php`
  - `sample/tutorials/sample10-dbaccess-mini-crud-flow` を使い、1 table + list/detail/create/update/delete metadata の DB Access tutorial lane を検証する
- `Sample11HtmlTemplateOutputTest.php`
  - `sample/tutorials/sample11-html-template-output` を使い、HTML template metadata と `html-module-catalog` Source Output tutorial lane を検証する
- `Sample12ExternalDbSourceImportOutputTest.php`
  - `sample/tutorials/sample12-external-db-source-import` を使い、external named source import から DataClass output publish までの tutorial lane を検証する
- `Sample13OpenApiApiSurfaceOutputTest.php`
  - `sample/tutorials/sample13-openapi-api-surface` を使い、single-function proxy target metadata から OpenAPI JSON artifact publish までの tutorial lane を検証する
- `Sample14CustomProxyRuntimeOutputTest.php`
  - `sample/tutorials/sample14-custom-proxy-runtime` を使い、custom proxy metadata から PHP proxy server artifact publish までの tutorial lane を検証する
- `Sample15ProjectMetadataExportImportTest.php`
  - `sample/tutorials/sample15-project-metadata-export-import` を使い、project metadata bundle export / preview / apply までの tutorial lane を検証する
- `Sample16AuthenticatedProxyTest.php`
  - `sample/tutorials/sample16-authenticated-proxy` を使い、ProjectToken authenticated proxy と fail-closed behavior の tutorial lane を検証する
- `Sample17MultiOutputProjectTest.php`
  - `sample/tutorials/sample17-multi-output-project` を使い、複数 Source Output を同じ project から publish する capstone tutorial lane を検証する
- historical な `Sample9...OutputTest.php` から `Sample22...OutputTest.php`
  - `sample/internal-patterns/pattern01-default-property-split` から `pattern14-method-and-enum-heavy-multimethod` までの file-based migration sample を対象に、`tests/fixtures/legacy-dbclasses/` の curated fixture から wrapper/base 出力を再生成し、`reference/` と比較する
- `LegacyTopLevelDeclarationMigrationTest.php`
  - top-level helper / declaration lane の migration split contract を unit-like に補助検証する
- `SamplePackCatalogTest.php`
  - sample pack の category、pack order、category guide README、共通 runner、file-based sample の fixture 入力、`pattern01-14` の dedicated output test coverage が current layout と一致していることを検証する
- `DocsEntranceContractTest.php`
  - `README.md` と `docs/` の入口文書が current 導線、boundary wording、sample lane path、既知の full suite command を維持していることを検証する
- `LegacyProjectSampleCatalogTest.php`
  - remaining `sample/legacy-projects/` runtime packs の canonical project key、project/source output seed、source output order/path/binding、resource manifest presence が current layout と一致していることを検証する
- `RuntimeReferencePromotionTest.php` / `RuntimeReferenceStatusTest.php` / `RuntimeReplacementRolloutLaneTest.php`
  - self-generated runtime reference の promotion / status / rollout metadata を検証する
- `RuntimeSqlGeneratorTest.php` / `BlobContractGuardTest.php`
  - runtime SQL generator と blob/file contract の guard を検証する

PHPUnit 実行例:

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh up
./sample/tutorials/sample01-simple-table-runtime/run.sh apply-seed
docker compose -f compose.yaml -f sample/tutorials/sample01-simple-table-runtime/compose.yaml exec -T web-admin \
  phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/Sample1SimpleTableOutputTest.php
```
