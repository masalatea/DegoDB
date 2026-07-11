<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SamplePhysicalLogicalNamingContractTest extends TestCase
{
    public function testMigratedTutorialOutputTestsOptIntoPhysicalLogicalNamePolicy(): void
    {
        $missing = [];

        foreach ($this->migratedTutorialPolicyEntrypointFiles() as $file) {
            $contents = file_get_contents(dirname(__DIR__, 2) . '/' . $file);
            self::assertIsString($contents, 'test file should be readable: ' . $file);

            if (!str_contains($contents, 'MTOOL_GENERATED_NAME_POLICY=physical-logical-v1')) {
                $missing[] = $file;
            }
        }

        self::assertSame([], $missing);
    }

    public function testSampleRunEntrypointsAreClassifiedForPhysicalLogicalPolicy(): void
    {
        $expected = array_values(array_unique(array_merge(
            $this->migratedTutorialPolicyEntrypointFiles(),
            $this->physicalLogicalPolicyExcludedEntrypointFiles(),
        )));
        sort($expected, SORT_STRING);

        self::assertSame($expected, $this->sampleRunEntrypointFiles());
    }

    public function testGeneratedReferenceDbAccessSqlDoesNotUseMixedCasePhysicalNames(): void
    {
        $violations = [];

        foreach ($this->tutorialReferencePhpFiles() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents, 'reference PHP file should be readable: ' . $file);

            foreach ($this->lastSqlCommandStrings($contents) as $sql) {
                foreach ($this->mixedCaseIdentifiers($sql) as $identifier) {
                    $violations[] = $this->relativePath($file) . ':' . $identifier . ':' . $sql;
                }
            }
        }

        self::assertSame([], $violations);
    }

    public function testSampleCheckerPhysicalNameConstantsDoNotUseMixedCaseNames(): void
    {
        $violations = [];

        foreach ($this->sampleCheckerFiles() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents, 'sample checker file should be readable: ' . $file);

            foreach ($this->checkerPhysicalNameConstantStringValues($contents) as $constantName => $values) {
                foreach ($values as $value) {
                    if ($value !== '' && $this->containsMixedCaseIdentifier($value)) {
                        $violations[] = $this->relativePath($file) . ':' . $constantName . ':' . $value;
                    }
                }
            }
        }

        self::assertSame([], $violations);
    }

    public function testTutorialSeedPhysicalNameColumnsDoNotUseMixedCaseNames(): void
    {
        $violations = [];

        foreach ($this->tutorialSeedSqlFiles() as $file) {
            $sql = file_get_contents($file);
            self::assertIsString($sql, 'seed sql should be readable: ' . $file);

            foreach ($this->insertStatements($sql) as $statement) {
                $columns = array_map(
                    static fn (string $column): string => trim($column, " \t\n\r`"),
                    $this->splitSqlCsv($statement['columns']),
                );

                foreach ($this->tupleStrings($statement['values']) as $tuple) {
                    $values = $this->splitSqlCsv($tuple);

                    foreach ($columns as $index => $column) {
                        if (!$this->isPhysicalNameColumn($column) || !array_key_exists($index, $values)) {
                            continue;
                        }

                        $value = $this->unquoteSqlValue($values[$index]);
                        if ($value === '' || strtoupper($value) === 'NULL') {
                            continue;
                        }

                        if ($this->containsMixedCaseIdentifier($value)) {
                            $violations[] = $this->relativePath($file)
                                . ':' . $statement['table']
                                . ':' . $column
                                . ':' . $value;
                        }
                    }
                }
            }
        }

        self::assertSame([], $violations);
    }

    public function testTutorialSeedSqlSchemaIdentifiersDoNotUseMixedCaseNames(): void
    {
        $violations = [];

        foreach ($this->tutorialSeedSqlFiles() as $file) {
            $sql = file_get_contents($file);
            self::assertIsString($sql, 'seed sql should be readable: ' . $file);
            $relativePath = $this->relativePath($file);

            foreach ($this->schemaIdentifierMatches($sql) as $match) {
                if ($this->containsMixedCaseIdentifier($match['identifier'])) {
                    $violations[] = $relativePath . ':' . $match['context'] . ':' . $match['identifier'];
                }
            }
        }

        self::assertSame([], $violations);
    }

    public function testTutorialReferenceJsonPhysicalNameFieldsDoNotUseMixedCaseNames(): void
    {
        $violations = [];

        foreach ($this->tutorialReferenceJsonFiles() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents, 'reference json should be readable: ' . $file);

            $payload = json_decode($contents, true);
            self::assertTrue(json_last_error() === JSON_ERROR_NONE, 'reference json should parse: ' . $file);

            $this->collectMixedCasePhysicalJsonFields(
                $payload,
                $this->relativePath($file),
                $violations,
            );
        }

        self::assertSame([], $violations);
    }

    public function testTutorialReferenceTextDoesNotDescribeMixedCasePhysicalNames(): void
    {
        $violations = [];

        foreach ($this->tutorialReferenceTextFiles() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents, 'reference text file should be readable: ' . $file);

            foreach ($this->mixedCasePhysicalTextMentions($contents) as $mention) {
                $violations[] = $this->relativePath($file) . ':' . $mention;
            }
        }

        self::assertSame([], $violations);
    }

    public function testTutorialDocumentationDoesNotDescribeMixedCasePhysicalNames(): void
    {
        $violations = [];

        foreach ($this->tutorialDocumentationTextFiles() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents, 'tutorial documentation file should be readable: ' . $file);

            foreach ($this->mixedCasePhysicalTextMentions($contents) as $mention) {
                $violations[] = $this->relativePath($file) . ':' . $mention;
            }
        }

        self::assertSame([], $violations);
    }

    /**
     * @return list<string>
     */
    private function migratedTutorialPolicyEntrypointFiles(): array
    {
        return [
            'mtool/scripts/check_sample1_simple_table_outputs.php',
            'mtool/scripts/check_sample2_dataclass_nullable_default_status_outputs.php',
            'mtool/scripts/check_sample3_dataclass_lookup_helper_outputs.php',
            'mtool/scripts/check_sample4_dataclass_parent_child_basic_outputs.php',
            'mtool/scripts/check_sample5_dbaccess_select_basic_outputs.php',
            'mtool/scripts/check_sample6_dbaccess_filter_sort_page_outputs.php',
            'mtool/scripts/check_sample7_dbaccess_crud_basic_outputs.php',
            'mtool/scripts/check_sample8_dbaccess_join_read_model_outputs.php',
            'mtool/scripts/check_sample09_dbaccess_aggregate_report_outputs.php',
            'mtool/scripts/check_sample10_dbaccess_mini_crud_flow_outputs.php',
            'mtool/scripts/check_sample12_external_db_source_import_outputs.php',
            'mtool/scripts/check_sample13_openapi_api_surface_outputs.php',
            'mtool/scripts/check_sample14_custom_proxy_runtime_outputs.php',
            'mtool/scripts/check_sample15_project_metadata_export_import_outputs.php',
            'mtool/scripts/check_sample16_authenticated_proxy_outputs.php',
            'mtool/scripts/check_sample17_multi_output_project_outputs.php',
            'mtool/scripts/check_sample18_mini_task_board_demo_outputs.php',
            'mtool/scripts/check_sample19_json_first_content_model_outputs.php',
            'tests/Integration/Sample1SimpleTableOutputTest.php',
            'tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php',
            'tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php',
            'tests/Integration/Sample4DataclassParentChildBasicOutputTest.php',
            'tests/Integration/Sample5DbAccessSelectBasicOutputTest.php',
            'tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php',
            'tests/Integration/Sample7DbAccessCrudBasicOutputTest.php',
            'tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php',
            'tests/Integration/Sample09DbAccessAggregateReportOutputTest.php',
            'tests/Integration/Sample10DbAccessMiniCrudFlowOutputTest.php',
            'tests/Integration/Sample12ExternalDbSourceImportOutputTest.php',
            'tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php',
            'tests/Integration/Sample14CustomProxyRuntimeOutputTest.php',
            'tests/Integration/Sample15ProjectMetadataExportImportTest.php',
            'tests/Integration/Sample16AuthenticatedProxyTest.php',
            'tests/Integration/Sample17MultiOutputProjectTest.php',
            'tests/Integration/Sample18MiniTaskBoardDemoTest.php',
            'tests/Integration/Sample19JsonFirstContentModelOutputTest.php',
            'tests/Integration/Sample20ContentPublishingDemoTest.php',
            'tests/Integration/Sample21EbookCatalogApiDemoTest.php',
            'tests/Integration/Sample22EbookChapterWorkflowDemoTest.php',
            'tests/Integration/Sample23EbookMediaMetadataDemoTest.php',
            'tests/Integration/Sample24EbookPublicReaderSiteDemoTest.php',
            'tests/Integration/Sample25EbookEditorAuthCmsDemoTest.php',
            'tests/Integration/Sample26EbookHeadlessCmsCapstoneTest.php',
            'tests/Integration/Sample27AppLocalPersistenceDemoTest.php',
            'tests/Integration/Sample28NoCodeDataAppMvpTest.php',
            'tests/Integration/Sample29NoCodeSupportCaseDemoTest.php',
            'tests/Integration/Sample30NoCodeAppLocalSyncDemoTest.php',
            'tests/Integration/Sample31NoCodeInventoryRequestDemoTest.php',
            'tests/Integration/Sample32NoCodeUiTestLabTest.php',
        ];
    }

    /**
     * @return list<string>
     */
    private function physicalLogicalPolicyExcludedEntrypointFiles(): array
    {
        return [
            'mtool/scripts/check_sample10_compare_output_companion_declarations_outputs.php',
            'mtool/scripts/check_sample11_da_dataclass_method_only_outputs.php',
            'mtool/scripts/check_sample11_html_template_output_outputs.php',
            'mtool/scripts/check_sample12_dbtablecolumns_wrapper_property_outputs.php',
            'mtool/scripts/check_sample13_req_method_and_enum_outputs.php',
            'mtool/scripts/check_sample14_buildsourcefunccache_companion_declarations_outputs.php',
            'mtool/scripts/check_sample15_buildlog_companion_declarations_outputs.php',
            'mtool/scripts/check_sample16_livecheckresult_companion_declarations_outputs.php',
            'mtool/scripts/check_sample17_speccontent_top_level_declaration_outputs.php',
            'mtool/scripts/check_sample18_projectuser_top_level_declaration_outputs.php',
            'mtool/scripts/check_sample19_htmltemplate_top_level_declaration_outputs.php',
            'mtool/scripts/check_sample20_dacustomproxy_method_and_enum_outputs.php',
            'mtool/scripts/check_sample21_project_method_and_enum_outputs.php',
            'mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php',
            'mtool/scripts/check_sample9_testpattern_default_property_outputs.php',
            'tests/Integration/Sample10CompareOutputCompanionDeclarationsOutputTest.php',
            'tests/Integration/Sample11DaDataclassMethodOnlyOutputTest.php',
            'tests/Integration/Sample11HtmlTemplateOutputTest.php',
            'tests/Integration/Sample12DbtablecolumnsWrapperPropertyOutputTest.php',
            'tests/Integration/Sample13ReqMethodAndEnumOutputTest.php',
            'tests/Integration/Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php',
            'tests/Integration/Sample15BuildLogCompanionDeclarationsOutputTest.php',
            'tests/Integration/Sample16LiveCheckResultCompanionDeclarationsOutputTest.php',
            'tests/Integration/Sample17SpecContentTopLevelDeclarationOutputTest.php',
            'tests/Integration/Sample18ProjectUserTopLevelDeclarationOutputTest.php',
            'tests/Integration/Sample19HtmlTemplateTopLevelDeclarationOutputTest.php',
            'tests/Integration/Sample20DaCustomProxyMethodAndEnumOutputTest.php',
            'tests/Integration/Sample21ProjectMethodAndEnumOutputTest.php',
            'tests/Integration/Sample22ProjectSourceOutputMethodAndEnumOutputTest.php',
            'tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php',
        ];
    }

    /**
     * @return list<string>
     */
    private function sampleRunEntrypointFiles(): array
    {
        $root = dirname(__DIR__, 2);
        $files = [];

        foreach ([
            $root . '/mtool/scripts',
            $root . '/tests/Integration',
        ] as $directory) {
            foreach (glob($directory . '/*.php') ?: [] as $file) {
                $contents = file_get_contents($file);
                if (!is_string($contents) || preg_match('/app_sample[0-9]+.*_run\(/', $contents) !== 1) {
                    continue;
                }

                $files[] = $this->relativePath($file);
            }
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function sampleCheckerFiles(): array
    {
        $files = glob(dirname(__DIR__, 2) . '/mtool/scripts/lib/sample*_*.php') ?: [];
        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function tutorialSeedSqlFiles(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials';
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (str_ends_with($path, '.sql') && str_contains($path, '/seed/')) {
                $files[] = $path;
            }
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function tutorialReferencePhpFiles(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials';
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (str_ends_with($path, '.php') && str_contains($path, '/reference/')) {
                $files[] = $path;
            }
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function tutorialReferenceJsonFiles(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials';
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (str_ends_with($path, '.json') && str_contains($path, '/reference/')) {
                $files[] = $path;
            }
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function tutorialReferenceTextFiles(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials';
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (
                str_contains($path, '/reference/')
                && preg_match('/\.(html|md|txt)$/', $path) === 1
            ) {
                $files[] = $path;
            }
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function tutorialDocumentationTextFiles(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials';
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (
                !str_contains($path, '/reference/')
                && preg_match('/\.(md|txt)$/', $path) === 1
            ) {
                $files[] = $path;
            }
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @return list<array{table: string, columns: string, values: string}>
     */
    private function insertStatements(string $sql): array
    {
        $matches = [];
        preg_match_all(
            '/INSERT\s+INTO\s+([a-zA-Z0-9_]+)\s*\((.*?)\)\s*VALUES\s*(.*?);/is',
            $sql,
            $matches,
            PREG_SET_ORDER,
        );

        $statements = [];
        foreach ($matches as $match) {
            $statements[] = [
                'table' => $match[1],
                'columns' => $match[2],
                'values' => $match[3],
            ];
        }

        return $statements;
    }

    /**
     * @return list<array{context: string, identifier: string}>
     */
    private function schemaIdentifierMatches(string $sql): array
    {
        $matches = [];

        $patterns = [
            'create-table' => '/\bCREATE\s+TABLE\s+`?([A-Za-z][A-Za-z0-9_]*)`?/i',
            'drop-table' => '/\bDROP\s+TABLE\s+IF\s+EXISTS\s+`?([A-Za-z][A-Za-z0-9_]*)`?/i',
            'insert-into' => '/\bINSERT\s+INTO\s+`?([A-Za-z][A-Za-z0-9_]*)`?/i',
            'references-table' => '/\bREFERENCES\s+`?([A-Za-z][A-Za-z0-9_]*)`?\s*\(/i',
        ];

        foreach ($patterns as $context => $pattern) {
            $patternMatches = [];
            preg_match_all($pattern, $sql, $patternMatches, PREG_SET_ORDER);
            foreach ($patternMatches as $match) {
                $matches[] = [
                    'context' => $context,
                    'identifier' => $match[1],
                ];
            }
        }

        foreach ($this->createTableBodies($sql) as $tableName => $body) {
            foreach ($this->createTableColumnIdentifiers($body) as $columnName) {
                $matches[] = [
                    'context' => 'create-table-column:' . $tableName,
                    'identifier' => $columnName,
                ];
            }
        }

        return $matches;
    }

    /**
     * @return array<string,string>
     */
    private function createTableBodies(string $sql): array
    {
        $bodies = [];
        $matches = [];
        preg_match_all('/\bCREATE\s+TABLE\s+`?([A-Za-z][A-Za-z0-9_]*)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tableName = $match[1][0];
            $openParenOffset = $match[0][1] + strlen($match[0][0]) - 1;
            $closeParenOffset = $this->matchingParenOffset($sql, $openParenOffset);
            if ($closeParenOffset === null) {
                continue;
            }

            $bodies[$tableName] = substr($sql, $openParenOffset + 1, $closeParenOffset - $openParenOffset - 1);
        }

        return $bodies;
    }

    private function matchingParenOffset(string $sql, int $openParenOffset): ?int
    {
        $inQuote = false;
        $depth = 0;
        $length = strlen($sql);

        for ($index = $openParenOffset; $index < $length; $index++) {
            $char = $sql[$index];

            if ($inQuote) {
                if ($char === "'") {
                    if ($index + 1 < $length && $sql[$index + 1] === "'") {
                        $index++;
                    } else {
                        $inQuote = false;
                    }
                }
                continue;
            }

            if ($char === "'") {
                $inQuote = true;
                continue;
            }

            if ($char === '(') {
                $depth++;
                continue;
            }

            if ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function createTableColumnIdentifiers(string $body): array
    {
        $columns = [];

        foreach ($this->splitSqlCsv($body) as $definition) {
            $definition = trim($definition);
            if ($definition === '' || preg_match('/^(PRIMARY|KEY|UNIQUE|INDEX|CONSTRAINT|FOREIGN|CHECK)\b/i', $definition) === 1) {
                continue;
            }

            $matches = [];
            if (preg_match('/^`?([A-Za-z][A-Za-z0-9_]*)`?\b/', $definition, $matches) === 1) {
                $columns[] = $matches[1];
            }
        }

        return $columns;
    }

    /**
     * @return list<string>
     */
    private function splitSqlCsv(string $sql): array
    {
        $items = [];
        $current = '';
        $inQuote = false;
        $parenDepth = 0;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];

            if ($inQuote) {
                $current .= $char;
                if ($char === "'") {
                    if ($index + 1 < $length && $sql[$index + 1] === "'") {
                        $current .= $sql[++$index];
                    } else {
                        $inQuote = false;
                    }
                }
                continue;
            }

            if ($char === "'") {
                $inQuote = true;
                $current .= $char;
                continue;
            }

            if ($char === '(') {
                $parenDepth++;
                $current .= $char;
                continue;
            }

            if ($char === ')') {
                $parenDepth--;
                $current .= $char;
                continue;
            }

            if ($char === ',' && $parenDepth === 0) {
                $items[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $items[] = trim($current);
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    private function tupleStrings(string $sql): array
    {
        $tuples = [];
        $inQuote = false;
        $parenDepth = 0;
        $tupleStart = null;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];

            if ($inQuote) {
                if ($char === "'") {
                    if ($index + 1 < $length && $sql[$index + 1] === "'") {
                        $index++;
                    } else {
                        $inQuote = false;
                    }
                }
                continue;
            }

            if ($char === "'") {
                $inQuote = true;
                continue;
            }

            if ($char === '(') {
                if ($parenDepth === 0) {
                    $tupleStart = $index + 1;
                }
                $parenDepth++;
                continue;
            }

            if ($char === ')') {
                $parenDepth--;
                if ($parenDepth === 0 && $tupleStart !== null) {
                    $tuples[] = substr($sql, $tupleStart, $index - $tupleStart);
                    $tupleStart = null;
                }
            }
        }

        return $tuples;
    }

    private function isPhysicalNameColumn(string $column): bool
    {
        return in_array($column, [
            'another_field_name',
            'another_table_alias_name',
            'another_table_name',
            'column_name',
            'db_access_source_name',
            'foreign_key_column_name',
            'foreign_key_table_name',
            'sort_order_columns',
            'source_column_name',
            'source_name',
            'source_table_name',
            'table_name',
            'target_table_alias_name',
            'target_table_column_name',
            'target_table_name',
        ], true);
    }

    /**
     * @return array<string,list<string>>
     */
    private function checkerPhysicalNameConstantStringValues(string $contents): array
    {
        $matches = [];
        preg_match_all(
            '/const\s+([A-Z0-9_]*(?:TABLE_NAME|TABLE_NAMES|SOURCE_NAME|SOURCE_NAMES|COLUMN_NAME|COLUMN_NAMES)[A-Z0-9_]*)\s*=\s*([^;]+);/m',
            $contents,
            $matches,
            PREG_SET_ORDER,
        );

        $valuesByConstant = [];
        foreach ($matches as $match) {
            $constantName = $match[1];
            if ($this->isNonPhysicalCheckerConstant($constantName)) {
                continue;
            }

            $stringMatches = [];
            preg_match_all("/'((?:[^']|'')*)'/", $match[2], $stringMatches);
            $values = array_map(
                static fn (string $value): string => str_replace("''", "'", $value),
                $stringMatches[1] ?? [],
            );

            if ($values !== []) {
                $valuesByConstant[$constantName] = $values;
            }
        }

        return $valuesByConstant;
    }

    private function isNonPhysicalCheckerConstant(string $constantName): bool
    {
        return str_contains($constantName, 'DATA_CLASS')
            || str_contains($constantName, 'SOURCE_OUTPUT')
            || str_contains($constantName, 'REFERENCE_SOURCE')
            || str_contains($constantName, 'TRAILING_CLASS')
            || str_contains($constantName, 'PROPERTY')
            || str_contains($constantName, 'METHOD');
    }

    /**
     * @param mixed $value
     * @param list<string> $violations
     */
    private function collectMixedCasePhysicalJsonFields(mixed $value, string $path, array &$violations): void
    {
        if (!is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            $childPath = $path . '/' . (string) $key;

            if (is_string($key) && $this->isPhysicalNameColumn($key) && is_string($child)) {
                if ($child !== '' && $this->containsMixedCaseIdentifier($child)) {
                    $violations[] = $childPath . ':' . $child;
                }
            }

            $this->collectMixedCasePhysicalJsonFields($child, $childPath, $violations);
        }
    }

    private function unquoteSqlValue(string $value): string
    {
        $value = trim($value);
        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            return str_replace("''", "'", substr($value, 1, -1));
        }

        return $value;
    }

    private function containsMixedCaseIdentifier(string $value): bool
    {
        return preg_match('/[a-z][A-Z]|[A-Z][a-z]+[A-Z]/', $value) === 1;
    }

    /**
     * @return list<string>
     */
    private function lastSqlCommandStrings(string $contents): array
    {
        $matches = [];
        preg_match_all(
            "/\\\$last_sql_command_for_mtooldb\\s*=\\s*'((?:[^']|'')*)'/",
            $contents,
            $matches,
        );

        return array_map(
            static fn (string $value): string => str_replace("''", "'", $value),
            $matches[1] ?? [],
        );
    }

    /**
     * @return list<string>
     */
    private function mixedCaseIdentifiers(string $sql): array
    {
        $matches = [];
        preg_match_all('/\\b[A-Za-z_][A-Za-z0-9_]*\\b/', $sql, $matches);

        $identifiers = [];
        foreach ($matches[0] ?? [] as $identifier) {
            if ($this->containsMixedCaseIdentifier($identifier)) {
                $identifiers[] = $identifier;
            }
        }

        return array_values(array_unique($identifiers));
    }

    /**
     * @return list<string>
     */
    private function mixedCasePhysicalTextMentions(string $contents): array
    {
        $patterns = [
            '/\bphysical(?:_name)?\s*[`"\']([A-Za-z][A-Za-z0-9_]*)[`"\']/',
            '/物理\s*[`"\']([A-Za-z][A-Za-z0-9_]*)[`"\']/',
            '/\btable\s*[`"\']([A-Za-z][A-Za-z0-9_]*)[`"\']/',
            '/\bCREATE\s+TABLE\s+`?([A-Za-z][A-Za-z0-9_]*)`?/i',
            '/--table=([A-Za-z][A-Za-z0-9_]*)/',
        ];

        $mentions = [];
        foreach ($patterns as $pattern) {
            $matches = [];
            preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $identifier = (string) ($match[1] ?? '');
                if ($identifier !== '' && $this->containsMixedCaseIdentifier($identifier)) {
                    $mentions[] = $match[0];
                }
            }
        }

        return array_values(array_unique($mentions));
    }

    private function relativePath(string $path): string
    {
        $root = dirname(__DIR__, 2) . '/';
        if (str_starts_with($path, $root)) {
            return substr($path, strlen($root));
        }

        return $path;
    }
}
