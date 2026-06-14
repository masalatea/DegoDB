<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_language_resource_catalog_loader.php';

use PHPUnit\Framework\TestCase;

final class LegacyProjectSampleCatalogTest extends TestCase
{
    public function testLegacyProjectRuntimePackContractsStayAligned(): void
    {
        $expectedSourceOutputKeysByPack = [
            'sample51-runtime-sql-server' => [
                'DBACCESS-CS-SQLSERVER',
            ],
            'sample53-runtime-whiteboard' => [
                'DBACCESS-PHP',
            ],
            'sample56-runtime-misc-proxy' => [
                'DBACCESS-PHP',
                'PROXY-SERVER-LOCK-PHP',
                'PROXY-CLIENT-LOCK-CS',
            ],
        ];
        $resourcePackNames = [];

        $legacyPackNames = app_sample_pack_category_map()['legacy-projects'] ?? [];
        self::assertSame(array_keys($expectedSourceOutputKeysByPack), $legacyPackNames);

        foreach ($legacyPackNames as $packName) {
            $projectKey = app_sample_pack_project_key($packName);
            self::assertMatchesRegularExpression('/^SAMPLE[2-8]$/', $projectKey);

            $packRoot = app_sample_pack_absolute_path($packName);
            self::assertNotSame('', $packRoot, 'pack root missing: ' . $packName);

            $readmePath = $packRoot . '/README.md';
            $readme = file_get_contents($readmePath);
            self::assertIsString($readme, 'failed to read: ' . $readmePath);
            self::assertStringContainsString('- canonical project key: `' . $projectKey . '`', $readme);
            self::assertStringContainsString(
                '- disposable runtime root: `work/sample-packs/' . $packName . '/`',
                $readme,
            );

            $projectSeedPath = $this->assertUniqueSeedPath($packRoot, '900_010_*_project_seed.sql');
            $projectRows = $this->parseInsertRows($projectSeedPath, 'projects');
            self::assertCount(1, $projectRows, 'unexpected project seed row count: ' . $packName);
            self::assertSame($projectKey, $this->sqlStringValue($projectRows[0], 'project_key'));
            self::assertSame($packName, $this->sqlStringValue($projectRows[0], 'slug'));
            self::assertSame('admin', $this->sqlStringValue($projectRows[0], 'owner_login_id'));

            $projectSeedText = file_get_contents($projectSeedPath);
            self::assertIsString($projectSeedText, 'failed to read: ' . $projectSeedPath);
            self::assertStringContainsString("WHERE p.project_key = '" . $projectKey . "'", $projectSeedText);

            $sourceOutputSeedPath = $this->assertUniqueSeedPath($packRoot, '900_020_*_source_output_seed.sql');
            $sourceOutputRows = $this->parseInsertRows($sourceOutputSeedPath, 'project_source_outputs');
            self::assertSame(
                $expectedSourceOutputKeysByPack[$packName],
                array_map(
                    fn (array $row): string => $this->sqlStringValue($row, 'source_output_key'),
                    $sourceOutputRows,
                ),
                'source output keys changed: ' . $packName,
            );
            self::assertSame(
                range(10, count($sourceOutputRows) * 10, 10),
                array_map(
                    fn (array $row): int => $this->sqlIntValue($row, 'source_output_list_order'),
                    $sourceOutputRows,
                ),
                'source output list order changed: ' . $packName,
            );

            foreach ($sourceOutputRows as $row) {
                $sourceOutputKey = $this->sqlStringValue($row, 'source_output_key');
                $classType = $this->sqlStringValue($row, 'class_type');

                self::assertSame(
                    'work/source-outputs/' . $projectKey . '/' . $sourceOutputKey,
                    $this->sqlStringValue($row, 'source_output_dir'),
                );
                self::assertSame(
                    'work/staging/source-outputs/' . $projectKey . '/' . $sourceOutputKey,
                    $this->sqlStringValue($row, 'source_temp_output_dir'),
                );
                self::assertSame('metadata-only', $this->sqlStringValue($row, 'artifact_strategy'));
                self::assertSame(
                    in_array($classType, ['DBaaSProxyServer', 'DBaaSProxyClient'], true)
                        ? 'proxy-metadata-only'
                        : 'metadata-only',
                    $this->sqlStringValue($row, 'target_binding_type'),
                );
            }

            self::assertSame(
                in_array($packName, $resourcePackNames, true),
                is_dir(app_sample_pack_resource_root($packName)),
                'resource root expectation changed: ' . $packName,
            );
        }
    }

    public function testLegacyProjectLanguageResourceCatalogsStayAlignedWithSeeds(): void
    {
        $resourceProjectMap = app_language_resource_file_catalog_sample_pack_name_map();
        $resourceFreePackNames = [
            'sample51-runtime-sql-server',
            'sample53-runtime-whiteboard',
            'sample56-runtime-misc-proxy',
        ];

        self::assertSame([], $resourceProjectMap);

        foreach ($resourceProjectMap as $projectKey => $packName) {
            $catalog = app_project_language_resource_load_file_catalog($projectKey);
            self::assertTrue(
                $catalog['exists'],
                'language resource file catalog missing: ' . $projectKey,
            );
            self::assertTrue(
                $catalog['ok'],
                $this->jsonFailureMessage($catalog),
            );
            self::assertSame([], $catalog['warnings'] ?? [], 'unexpected warnings: ' . $projectKey);
            self::assertSame([], $catalog['errors'] ?? [], 'unexpected errors: ' . $projectKey);

            $manifest = is_array($catalog['manifest'] ?? null) ? $catalog['manifest'] : [];
            self::assertSame($projectKey, (string) ($manifest['project_key'] ?? ''));
            self::assertSame('file-canonical', (string) ($manifest['catalog_source'] ?? ''));
            self::assertSame('bootstrap-reference', (string) ($manifest['origin']['type'] ?? ''));

            $sourceOutputSeedPath = $this->assertUniqueSeedPath(
                app_sample_pack_absolute_path($packName),
                '900_020_*_source_output_seed.sql',
            );
            $sourceOutputRows = $this->parseInsertRows($sourceOutputSeedPath, 'project_source_outputs');
            self::assertSame(
                array_map(
                    fn (array $row): string => $this->sqlStringValue($row, 'source_output_key'),
                    $sourceOutputRows,
                ),
                is_array($manifest['enabled_source_output_keys'] ?? null)
                    ? $manifest['enabled_source_output_keys']
                    : [],
                'manifest enabled source outputs changed: ' . $projectKey,
            );
        }

        foreach ($resourceFreePackNames as $packName) {
            self::assertDirectoryDoesNotExist(
                app_sample_pack_resource_root($packName),
                'resource-free pack should not grow resources/: ' . $packName,
            );
        }
    }

    private function assertUniqueSeedPath(string $packRoot, string $pattern): string
    {
        $paths = glob($packRoot . '/seed/' . $pattern) ?: [];
        sort($paths, SORT_STRING);

        self::assertCount(1, $paths, 'unexpected seed file match: ' . $packRoot . '/seed/' . $pattern);

        return $paths[0];
    }

    /**
     * @return list<array<string,string>>
     */
    private function parseInsertRows(string $seedPath, string $tableName): array
    {
        $contents = file_get_contents($seedPath);
        self::assertIsString($contents, 'failed to read: ' . $seedPath);

        $pattern = '/INSERT(?:\s+IGNORE)?\s+INTO\s+'
            . preg_quote($tableName, '/')
            . '\s*\((?<columns>.*?)\)\s*VALUES\s*(?<values>.*?)(?=ON\s+DUPLICATE\s+KEY\s+UPDATE\b|;)/is';
        self::assertSame(
            1,
            preg_match($pattern, $contents, $matches),
            'failed to parse INSERT rows: ' . $seedPath . ' [' . $tableName . ']',
        );

        $columns = preg_split('/\s*,\s*/', trim((string) ($matches['columns'] ?? '')));
        self::assertIsArray($columns, 'failed to parse columns: ' . $seedPath);

        $normalizedColumns = [];
        foreach ($columns as $column) {
            $normalizedColumns[] = trim((string) $column, " \t\n\r\0\x0B`");
        }

        $rows = [];
        foreach (app_language_resource_file_catalog_parse_sql_value_tuples((string) ($matches['values'] ?? '')) as $tuple) {
            $row = [];
            foreach ($normalizedColumns as $index => $columnName) {
                $row[$columnName] = (string) ($tuple[$index] ?? '');
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<string,string> $row
     */
    private function sqlStringValue(array $row, string $column): string
    {
        return app_language_resource_file_catalog_sql_unquote_string((string) ($row[$column] ?? ''));
    }

    /**
     * @param array<string,string> $row
     */
    private function sqlIntValue(array $row, string $column): int
    {
        return app_language_resource_file_catalog_sql_int_value((string) ($row[$column] ?? ''));
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function jsonFailureMessage(array $payload): string
    {
        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'failed to encode diagnostic payload';
    }
}
