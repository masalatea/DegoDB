<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/lab_sample18_task_board_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

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

    public function testMiniTaskBoardGeneratedSubmitRequestContractPreflight(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        self::assertFalse($submitContract['generated_route_added'] ?? true);
        self::assertFalse($submitContract['mutation_enabled'] ?? true);

        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        self::assertNotSame('', $timestamp);

        $contracts = app_lab_sample18_task_board_generated_submit_contracts();
        $operations = $submitContract['operations'] ?? [];
        self::assertIsArray($operations);
        self::assertSame(['create_task_card', 'update_task_card', 'complete_task_card'], array_keys($operations));

        $inventoryOperations = $checklist['action_input_mapping_inventory']['operations'] ?? [];
        self::assertIsArray($inventoryOperations);
        $inventoryByKey = [];
        foreach ($inventoryOperations as $operation) {
            self::assertIsArray($operation);
            $inventoryByKey[(string) ($operation['operation_key'] ?? '')] = $operation;
        }

        foreach ($operations as $operationKey => $expectation) {
            self::assertIsArray($expectation);
            self::assertArrayHasKey($operationKey, $contracts);
            self::assertSame($expectation['curated_route_action'] ?? '', $contracts[$operationKey]['curated_route_action'] ?? '');
            self::assertSame($expectation['db_access_function'] ?? '', $contracts[$operationKey]['db_access_function'] ?? '');
            self::assertSame(
                $inventoryByKey[$operationKey]['curated_route_action'] ?? '',
                $contracts[$operationKey]['curated_route_action'] ?? '',
            );
            self::assertSame(
                $inventoryByKey[$operationKey]['db_access_function'] ?? '',
                $contracts[$operationKey]['db_access_function'] ?? '',
            );

            $valid = app_lab_sample18_task_board_normalize_generated_submit_request(
                (string) $operationKey,
                is_array($expectation['valid_input'] ?? null) ? $expectation['valid_input'] : [],
                $timestamp,
            );
            self::assertTrue($valid['ok'], (string) $operationKey);
            self::assertSame('', $valid['failure_code']);
            self::assertSame($expectation['expected_payload'] ?? [], $valid['payload']);
            self::assertSame($expectation['ignored_input_fields'] ?? [], $valid['ignored_input_fields']);

            $invalid = app_lab_sample18_task_board_normalize_generated_submit_request(
                (string) $operationKey,
                is_array($expectation['invalid_input'] ?? null) ? $expectation['invalid_input'] : [],
                $timestamp,
            );
            self::assertFalse($invalid['ok'], (string) $operationKey);
            self::assertSame('validation_error', $invalid['failure_code']);
            self::assertSame($expectation['expected_errors'] ?? [], $invalid['errors']);
        }

        $unknown = app_lab_sample18_task_board_normalize_generated_submit_request(
            'delete_task_card',
            ['id' => '1801'],
            $timestamp,
        );
        self::assertFalse($unknown['ok']);
        self::assertSame('unknown_operation', $unknown['failure_code']);
        self::assertSame(['operation.unknown'], $unknown['errors']);
    }

    public function testMiniTaskBoardGeneratedSubmitRouteBlockedWrapper(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        $createExpectation = $submitContract['operations']['create_task_card'] ?? [];
        self::assertIsArray($createExpectation);

        $route = app_route_match([
            'path' => app_lab_sample18_task_board_generated_submit_path(),
        ]);
        self::assertSame('lab_sample18_task_board_generated_submit', $route['name']);
        self::assertTrue(app_route_requires_auth('lab_sample18_task_board_generated_submit'));

        $notPost = app_lab_sample18_task_board_generated_submit_blocked_response('GET', [], $timestamp);
        self::assertSame(405, $notPost['status_code']);
        self::assertSame('method_not_allowed', $notPost['payload']['failure_code'] ?? '');
        self::assertFalse($notPost['payload']['mutation_enabled'] ?? true);

        $validPost = array_merge(
            ['operation_key' => 'create_task_card', '_csrf_token' => 'client-token'],
            is_array($createExpectation['valid_input'] ?? null) ? $createExpectation['valid_input'] : [],
        );
        $blocked = app_lab_sample18_task_board_generated_submit_blocked_response('POST', $validPost, $timestamp);
        self::assertSame(409, $blocked['status_code']);
        self::assertFalse($blocked['payload']['ok'] ?? true);
        self::assertFalse($blocked['payload']['accepted'] ?? true);
        self::assertSame('blocked', $blocked['payload']['result'] ?? '');
        self::assertSame('generated_submit_disabled', $blocked['payload']['failure_code'] ?? '');
        self::assertSame('create_task_card', $blocked['payload']['operation_key'] ?? '');
        self::assertSame('create', $blocked['payload']['curated_route_action'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['db_access_function'] ?? '');
        self::assertSame($createExpectation['expected_payload'] ?? [], $blocked['payload']['normalized_payload'] ?? []);
        self::assertSame($createExpectation['ignored_input_fields'] ?? [], $blocked['payload']['ignored_input_fields'] ?? []);
        self::assertFalse($blocked['payload']['mutation_enabled'] ?? true);

        $missingCsrf = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'create_task_card'],
            $timestamp,
            'missing',
        );
        self::assertSame(403, $missingCsrf['status_code']);
        self::assertSame('missing_csrf', $missingCsrf['payload']['failure_code'] ?? '');
        self::assertSame(['csrf.missing'], $missingCsrf['payload']['errors'] ?? []);
        self::assertFalse($missingCsrf['payload']['mutation_enabled'] ?? true);

        $invalidCsrf = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'create_task_card', '_csrf_token' => 'wrong-token'],
            $timestamp,
            'invalid',
        );
        self::assertSame(403, $invalidCsrf['status_code']);
        self::assertSame('invalid_csrf', $invalidCsrf['payload']['failure_code'] ?? '');
        self::assertSame(['csrf.invalid'], $invalidCsrf['payload']['errors'] ?? []);
        self::assertFalse($invalidCsrf['payload']['mutation_enabled'] ?? true);

        $invalid = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'update_task_card', 'id' => '0', 'title' => ''],
            $timestamp,
        );
        self::assertSame(422, $invalid['status_code']);
        self::assertSame('validation_error', $invalid['payload']['failure_code'] ?? '');
        self::assertSame(['id.invalid', 'title.required'], $invalid['payload']['errors'] ?? []);

        $unknown = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'delete_task_card', 'id' => '1801'],
            $timestamp,
        );
        self::assertSame(404, $unknown['status_code']);
        self::assertSame('unknown_operation', $unknown['payload']['failure_code'] ?? '');
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
        $bindingGate = $checklist['submit_route_binding_gate'] ?? [];
        self::assertIsArray($bindingGate);
        foreach ($contractActions as $action) {
            self::assertIsArray($action);
            $actionKey = (string) ($action['action_key'] ?? '');
            $fieldCountsByAction[$actionKey] = count($action['fields'] ?? []);
            self::assertSame('disabled', (string) ($action['availability'] ?? ''));
            self::assertSame(
                $htmlDomContract['managed_action_submit_url'] ?? '',
                (string) ($action['submit_route'] ?? ''),
            );
            if (in_array($actionKey, $bindingGate['managed_action_keys'] ?? [], true)) {
                self::assertSame(
                    [
                        'binding_state' => $bindingGate['state'] ?? '',
                        'submit_route' => $bindingGate['submit_route'] ?? '',
                        'csrf_source' => $bindingGate['csrf_source'] ?? '',
                        'csrf_token_field' => $bindingGate['csrf_token_field'] ?? '',
                        'csrf_source_selector' => $bindingGate['csrf_source_selector'] ?? '',
                        'csrf_transport' => $bindingGate['csrf_transport'] ?? '',
                        'csrf_submit_field' => $bindingGate['csrf_submit_field'] ?? '',
                        'required_button_state' => $bindingGate['required_button_state'] ?? '',
                        'click_binding_state' => $bindingGate['click_binding_state'] ?? '',
                        'submit_trigger' => $bindingGate['submit_trigger'] ?? '',
                        'network_submit_enabled' => $bindingGate['network_submit_enabled'] ?? null,
                        'guarded_click_inventory_state' => $bindingGate['guarded_click_inventory_state'] ?? '',
                        'enablement_gate_set' => $bindingGate['enablement_gate_set'] ?? '',
                        'enablement_gates' => $bindingGate['enablement_gates'] ?? [],
                        'payload_assembly' => $bindingGate['payload_assembly'] ?? '',
                        'blocked_response_handling' => $bindingGate['blocked_response_handling'] ?? '',
                        'failure_display_target' => $bindingGate['failure_display_target'] ?? '',
                        'runtime_click_binding' => $bindingGate['runtime_click_binding'] ?? null,
                        'mutation_enabled' => $bindingGate['mutation_enabled'] ?? null,
                        'fail_closed_result' => $bindingGate['fail_closed_result'] ?? '',
                        'http_smoke_command' => $bindingGate['http_smoke_command'] ?? '',
                    ],
                    $action['submit_binding_gate'] ?? [],
                );
            }
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
        self::assertStringContainsString(
            'data-action-submit-url="' . ($htmlDomContract['managed_action_submit_url'] ?? '') . '"',
            $runtimePreviewHtml,
        );
        foreach (($bindingGate['required_dom_attributes'] ?? []) as $attribute => $value) {
            self::assertStringContainsString($attribute . '="' . $value . '"', $runtimePreviewHtml);
        }
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
