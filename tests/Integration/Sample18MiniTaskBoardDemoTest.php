<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample18MiniTaskBoardDemoTest extends TestCase
{
    public function testMiniTaskBoardNoCodeGoldenFixtureMatchesSeedAndRouteContract(): void
    {
        $fixture = $this->sample18NoCodeGoldenFixture();
        $checklist = $this->sample18FastContractChecklist();
        $root = dirname(__DIR__, 2);
        $seedSql = (string) file_get_contents(
            $root . '/sample/tutorials/sample18-mini-task-board-demo/seed/900_020_sample18_table_seed.sql',
        );
        $routeSource = (string) file_get_contents($root . '/mtool/app/lab_sample18_task_board_page.php');
        $dbAccessSeed = (string) file_get_contents(
            $root . '/sample/tutorials/sample18-mini-task-board-demo/seed/900_025_sample18_db_access_seed.sql',
        );

        self::assertSame('sample18-no-code-ui-golden-v1', $fixture['fixture_version'] ?? '');
        self::assertSame('SAMPLE18', $fixture['project_key'] ?? '');
        self::assertSame('sample18-no-code-fast-contract-checklist-v1', $checklist['checklist_version'] ?? '');
        self::assertSame($fixture['project_key'] ?? '', $checklist['project_key'] ?? '');
        self::assertSame($fixture['source_table'] ?? '', $checklist['source_table'] ?? '');
        self::assertSame('/samples/sample18-task-board', $fixture['route_path'] ?? '');
        self::assertSame('task_card', $fixture['source_table'] ?? '');
        self::assertFalse($fixture['no_code_conversion_boundary']['generated_route_replacement'] ?? true);
        self::assertFalse($fixture['no_code_conversion_boundary']['generated_button_execution'] ?? true);
        self::assertSame(
            $fixture['no_code_conversion_boundary']['generated_route_replacement'] ?? null,
            $checklist['conversion_boundary']['generated_route_replacement'] ?? null,
        );
        self::assertSame(
            $fixture['no_code_conversion_boundary']['generated_button_execution'] ?? null,
            $checklist['conversion_boundary']['generated_button_execution'] ?? null,
        );

        foreach (($fixture['seed_rows'] ?? []) as $row) {
            self::assertIsArray($row);
            self::assertStringContainsString((string) ($row['title'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['status'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['assigned_to'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['due_date'] ?? ''), $seedSql);
        }

        $contract = $fixture['dom_contract'] ?? [];
        $statusFilterContract = $checklist['status_filter_contract'] ?? [];
        self::assertIsArray($contract);
        self::assertSame($contract['status_filter_values'] ?? [], $statusFilterContract['curated_route_values'] ?? []);
        self::assertStringContainsString((string) ($contract['title'] ?? ''), $routeSource);
        foreach (($statusFilterContract['curated_route_values'] ?? []) as $value) {
            self::assertStringContainsString('value="' . $value . '"', $routeSource);
        }
        foreach (($contract['form_fields'] ?? []) as $fieldName) {
            self::assertStringContainsString('name="' . $fieldName . '"', $routeSource);
        }
        foreach (($contract['table_columns'] ?? []) as $columnLabel) {
            self::assertStringContainsString('>' . $columnLabel . '<', $routeSource);
        }
        foreach (($contract['actions'] ?? []) as $action) {
            $needle = in_array($action, ['create', 'update'], true)
                ? "action === '" . $action . "'"
                : 'value="' . $action . '"';
            self::assertStringContainsString($needle, $routeSource);
        }
        $actionInputInventory = $checklist['action_input_mapping_inventory'] ?? [];
        self::assertFalse($actionInputInventory['generated_button_execution'] ?? true);
        self::assertFalse($actionInputInventory['route_replacement'] ?? true);
        foreach (($actionInputInventory['operations'] ?? []) as $operation) {
            self::assertIsArray($operation);
            $routeAction = (string) ($operation['curated_route_action'] ?? '');
            $routeNeedle = in_array($routeAction, ['create', 'update'], true)
                ? "action === '" . $routeAction . "'"
                : 'value="' . $routeAction . '"';
            self::assertStringContainsString($routeNeedle, $routeSource);
            foreach (array_merge(
                $operation['key_fields'] ?? [],
                $operation['required_client_fields'] ?? [],
                $operation['optional_client_fields'] ?? [],
            ) as $fieldName) {
                self::assertStringContainsString('name="' . $fieldName . '"', $routeSource);
            }
            $dbAccessFunction = (string) ($operation['db_access_function'] ?? '');
            if ($dbAccessFunction !== '') {
                self::assertStringContainsString("'" . $dbAccessFunction . "'", $dbAccessSeed);
            }
        }
        self::assertSame(
            $checklist['html_dom_contract']['disabled_extension_action_keys'] ?? [],
            $fixture['no_code_action_keys'] ?? [],
        );
        self::assertSame(
            $checklist['html_dom_contract']['managed_action_keys'] ?? [],
            $fixture['no_code_managed_action_keys'] ?? [],
        );
    }

    public function testMiniTaskBoardDemoReferenceOutputs(): void
    {
        $fixture = $this->sample18NoCodeGoldenFixture();
        $checklist = $this->sample18FastContractChecklist();
        $metadataContract = $checklist['metadata_contract'] ?? [];
        $htmlDomContract = $checklist['html_dom_contract'] ?? [];
        $app = app_bootstrap();
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
        try {
            $result = app_sample18_mini_task_board_demo_run(
                $app,
                'phpunit-sample18',
                app_sample18_mini_task_board_demo_default_reference_root(),
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        if (!$result['ok']) {
            fwrite(
                STDERR,
                json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            );
        }

        self::assertTrue(
            $result['ok'],
            is_string($result['error'] ?? null) && $result['error'] !== ''
                ? $result['error']
                : 'sample18 mini task board verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(4, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('HTML-PAGE', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
        self::assertSame($metadataContract['definition_version'] ?? '', $result['steps']['no_code_metadata']['definition_version'] ?? '');
        self::assertSame($metadataContract['runtime_version'] ?? '', $result['steps']['no_code_metadata']['runtime_version'] ?? '');
        self::assertSame($metadataContract['contract_key'] ?? '', $result['steps']['no_code_metadata']['contract_key'] ?? '');
        self::assertSame($metadataContract['screen_types'] ?? [], $result['steps']['no_code_metadata']['screen_types'] ?? []);
        self::assertSame($metadataContract['field_keys'] ?? [], $result['steps']['no_code_metadata']['field_keys'] ?? []);
        self::assertSame(
            $htmlDomContract['disabled_extension_action_keys'] ?? [],
            $result['steps']['no_code_metadata']['custom_operation_keys'] ?? [],
        );
        self::assertSame(
            $htmlDomContract['disabled_extension_action_keys'] ?? [],
            $result['steps']['no_code_metadata']['runtime_action_keys'] ?? [],
        );
        self::assertSame(
            $htmlDomContract['managed_action_keys'] ?? [],
            $result['steps']['no_code_metadata']['managed_action_keys'] ?? [],
        );
        self::assertSame(count($fixture['seed_rows'] ?? []), $result['steps']['no_code_metadata']['runtime_row_count'] ?? null);
        self::assertSame(4, $result['steps']['no_code_metadata']['golden_row_count'] ?? null);

        $publishedRoot = (string) ($result['steps']['no_code_metadata']['published_root'] ?? '');
        self::assertDirectoryExists($publishedRoot);
        $screenDefinition = NoCodeUiContractAssertions::readJsonFile($this, $publishedRoot . '/screen-definition.json');
        $contractActions = $screenDefinition['contracts'][0]['actions'] ?? [];
        self::assertIsArray($contractActions);
        $fieldCountsByAction = [];
        foreach ($contractActions as $action) {
            self::assertIsArray($action);
            $fieldCountsByAction[(string) ($action['action_key'] ?? '')] = count($action['fields'] ?? []);
            self::assertSame('disabled', (string) ($action['availability'] ?? ''));
        }
        self::assertSame($htmlDomContract['managed_action_field_counts'] ?? [], $fieldCountsByAction);
        $runtimePreview = NoCodeUiContractAssertions::readJsonFile($this, $publishedRoot . '/runtime-preview.json');
        NoCodeUiContractAssertions::assertRuntimePreviewScreenKeys(
            $this,
            $runtimePreview,
            $metadataContract['screen_keys'] ?? [],
        );
        NoCodeUiContractAssertions::assertRuntimePreviewScreenField(
            $this,
            $runtimePreview,
            (string) ($checklist['status_filter_contract']['screen_key'] ?? ''),
            $checklist['status_filter_contract']['field'] ?? [],
        );
        $runtimePreviewHtml = (string) file_get_contents($publishedRoot . '/runtime-preview.html');
        NoCodeUiContractAssertions::assertPreviewHtmlScreens(
            $this,
            $runtimePreviewHtml,
            $metadataContract['screen_types_by_key'] ?? [],
        );
        NoCodeUiContractAssertions::assertPreviewHtmlFormFields(
            $this,
            $runtimePreviewHtml,
            $htmlDomContract['form_fields'] ?? [],
        );
        NoCodeUiContractAssertions::assertPreviewHtmlDisabledExtensionActions(
            $this,
            $runtimePreviewHtml,
            $htmlDomContract['disabled_extension_action_keys'] ?? [],
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function sample18NoCodeGoldenFixture(): array
    {
        $path = dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-ui-golden.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    /**
     * @return array<string,mixed>
     */
    private function sample18FastContractChecklist(): array
    {
        $path = dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
