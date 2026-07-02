<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE28_NO_CODE_PROJECT_KEY = 'SAMPLE28';
const APP_SAMPLE28_NO_CODE_TABLE_NAME = 'no_code_ticket';
const APP_SAMPLE28_NO_CODE_SOURCE_OUTPUT_KEY = 'NO-CODE-RUNTIME';
const APP_SAMPLE28_NO_CODE_REACT_BRIDGE_SOURCE_OUTPUT_KEY = 'NO-CODE-REACT-BRIDGE';
const APP_SAMPLE28_NO_CODE_JSON_FORMS_PROBE_SOURCE_OUTPUT_KEY = 'NO-CODE-JSON-FORMS-PROBE';

/**
 * @param list<string> $errors
 */
function app_sample28_no_code_data_app_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @return array{ok:bool,data:array<string,mixed>,error:string}
 */
function app_sample28_no_code_data_app_read_json_file(string $path): array
{
    if (!is_file($path)) {
        return ['ok' => false, 'data' => [], 'error' => 'file was not found: ' . $path];
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'data' => [], 'error' => 'failed to decode JSON file: ' . $path];
    }

    return ['ok' => true, 'data' => $decoded, 'error' => ''];
}

/**
 * @param list<array<string,mixed>> $items
 * @return list<string>
 */
function app_sample28_no_code_data_app_extract_names(array $items, string $key): array
{
    return array_values(array_map(
        static fn (array $item): string => (string) ($item[$key] ?? ''),
        $items,
    ));
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,mixed>
 */
function app_sample28_no_code_data_app_find_by_value(array $items, string $key, string $value): array
{
    foreach ($items as $item) {
        if ((string) ($item[$key] ?? '') === $value) {
            return $item;
        }
    }

    return [];
}

/**
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     table_name:string,
 *     requested_by:string,
 *     steps:array<string,mixed>,
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample28_no_code_data_app_run(array $app, string $requestedBy): array
{
    $projectKey = APP_SAMPLE28_NO_CODE_PROJECT_KEY;
    $tableName = APP_SAMPLE28_NO_CODE_TABLE_NAME;
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'source_output' => null,
        'artifact' => null,
        'published' => null,
        'screen_definition' => null,
        'runtime_preview' => null,
        'react_bridge' => null,
        'json_forms_probe' => null,
    ];
    $assertionErrors = [];

    try {
        $tableImport = app_project_table_import_apply($app, $projectKey, 'live-schema', $tableName);
        $steps['table_import'] = [
            'ok' => $tableImport['ok'],
            'summary' => $tableImport['summary'],
            'errors' => $tableImport['errors'],
            'error' => $tableImport['error'],
        ];
        if (!$tableImport['ok']) {
            throw new RuntimeException('sample28 table import failed.');
        }

        $dataClassSync = app_project_data_class_sync_apply($app, $projectKey);
        $steps['data_class_sync'] = [
            'ok' => $dataClassSync['ok'],
            'summary' => $dataClassSync['summary'],
            'errors' => $dataClassSync['errors'],
            'error' => $dataClassSync['error'],
        ];
        if (!$dataClassSync['ok']) {
            throw new RuntimeException('sample28 data class sync failed.');
        }

        $sourceOutputResult = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE28_NO_CODE_SOURCE_OUTPUT_KEY,
        );
        if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
            throw new RuntimeException(
                $sourceOutputResult['error'] !== ''
                    ? $sourceOutputResult['error']
                    : 'sample28 no-code source output definition was not found.',
            );
        }
        $steps['source_output'] = $sourceOutputResult['item'];

        $artifactResult = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $sourceOutputResult['item'],
            $requestedBy,
        );
        if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
            throw new RuntimeException('sample28 no-code artifact generation failed: ' . $artifactResult['error']);
        }
        $steps['artifact'] = $artifactResult['artifact'];

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $sourceOutputResult['item'],
        );
        if (!$publishResult['ok'] || $publishResult['published'] === null) {
            throw new RuntimeException('sample28 no-code artifact publish failed: ' . $publishResult['error']);
        }
        $steps['published'] = $publishResult['published'];

        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        $screenDefinitionJson = app_sample28_no_code_data_app_read_json_file($publishedRoot . '/screen-definition.json');
        $runtimePreviewJson = app_sample28_no_code_data_app_read_json_file($publishedRoot . '/runtime-preview.json');
        if (!$screenDefinitionJson['ok'] || !$runtimePreviewJson['ok']) {
            throw new RuntimeException(
                !$screenDefinitionJson['ok']
                    ? $screenDefinitionJson['error']
                    : $runtimePreviewJson['error'],
            );
        }

        $screenDefinition = $screenDefinitionJson['data'];
        $runtimePreview = $runtimePreviewJson['data'];
        $contracts = is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : [];
        $contract = is_array($contracts[0] ?? null) ? $contracts[0] : [];
        $screens = is_array($contract['screens'] ?? null) ? $contract['screens'] : [];
        $actions = is_array($contract['actions'] ?? null) ? $contract['actions'] : [];
        $listScreen = is_array($screens[0] ?? null) ? $screens[0] : [];
        $fields = is_array($listScreen['fields'] ?? null) ? $listScreen['fields'] : [];
        $runtimeScreens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];

        app_sample28_no_code_data_app_assert_same('no-code-screen-definition-v0', $screenDefinition['definition_version'] ?? '', 'definition_version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same($projectKey, $screenDefinition['project_key'] ?? '', 'project_key', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(1, count($contracts), 'contract count', $assertionErrors);
        app_sample28_no_code_data_app_assert_same($tableName, $contract['contract_key'] ?? '', 'contract_key', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(['list', 'detail', 'form'], app_sample28_no_code_data_app_extract_names($screens, 'screen_type'), 'screen types', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(['id', 'title', 'status', 'priority', 'body'], app_sample28_no_code_data_app_extract_names($fields, 'field_key'), 'field keys', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(1, count($actions), 'action count', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('update_no_code_ticket', $actions[0]['action_key'] ?? '', 'action key', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('update', $actions[0]['operation_type'] ?? '', 'action operation_type', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('no-code-runtime-v0', $runtimePreview['runtime_version'] ?? '', 'runtime_version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(3, count($runtimeScreens), 'runtime screen count', $assertionErrors);

        $runtimePreviewHtml = is_file($publishedRoot . '/runtime-preview.html')
            ? (string) file_get_contents($publishedRoot . '/runtime-preview.html')
            : '';
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains($runtimePreviewHtml, 'no_code_ticket_list')
                && str_contains($runtimePreviewHtml, 'window.noCodeRuntimeDispatchAction')
                && str_contains($runtimePreviewHtml, 'class="no-code-screen-summary"')
                && str_contains($runtimePreviewHtml, 'data-screen-summary="no_code_ticket_form"')
                && str_contains($runtimePreviewHtml, 'role="region" aria-labelledby="no-code-screen-title-no_code_ticket_list"')
                && str_contains($runtimePreviewHtml, '<caption class="no-code-table-caption">No Code Ticket List records</caption>')
                && str_contains($runtimePreviewHtml, 'data-action-affordance="keyboard-intent-preview"')
                && str_contains($runtimePreviewHtml, 'data-keyboard-activation="enter-space"')
                && str_contains($runtimePreviewHtml, 'data-action-disabled-reason="policy-not-enabled"')
                && str_contains($runtimePreviewHtml, 'Disabled in this preview: policy checks did not enable this action.'),
            'runtime preview html',
            $assertionErrors,
        );

        $steps['screen_definition'] = [
            'definition_version' => $screenDefinition['definition_version'] ?? '',
            'project_key' => $screenDefinition['project_key'] ?? '',
            'contract_key' => $contract['contract_key'] ?? '',
            'screen_types' => app_sample28_no_code_data_app_extract_names($screens, 'screen_type'),
            'field_keys' => app_sample28_no_code_data_app_extract_names($fields, 'field_key'),
            'action_key' => $actions[0]['action_key'] ?? '',
        ];
        $steps['runtime_preview'] = [
            'runtime_version' => $runtimePreview['runtime_version'] ?? '',
            'screen_count' => count($runtimeScreens),
            'published_root' => $publishedRoot,
        ];

        $reactBridgeSourceOutputResult = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE28_NO_CODE_REACT_BRIDGE_SOURCE_OUTPUT_KEY,
        );
        if (!$reactBridgeSourceOutputResult['ok'] || $reactBridgeSourceOutputResult['item'] === null) {
            throw new RuntimeException(
                $reactBridgeSourceOutputResult['error'] !== ''
                    ? $reactBridgeSourceOutputResult['error']
                    : 'sample28 no-code React bridge source output definition was not found.',
            );
        }
        $reactBridgeArtifactResult = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $reactBridgeSourceOutputResult['item'],
            $requestedBy,
        );
        if (!$reactBridgeArtifactResult['ok'] || $reactBridgeArtifactResult['artifact'] === null) {
            throw new RuntimeException('sample28 no-code React bridge artifact generation failed: ' . $reactBridgeArtifactResult['error']);
        }
        $reactBridgePublishResult = app_project_output_publish_artifact(
            $app,
            $reactBridgeArtifactResult['artifact'],
            $reactBridgeSourceOutputResult['item'],
        );
        if (!$reactBridgePublishResult['ok'] || $reactBridgePublishResult['published'] === null) {
            throw new RuntimeException('sample28 no-code React bridge artifact publish failed: ' . $reactBridgePublishResult['error']);
        }

        $reactBridgePublishedRoot = (string) ($reactBridgePublishResult['published']['published_root'] ?? '');
        $bridgeContractJson = app_sample28_no_code_data_app_read_json_file($reactBridgePublishedRoot . '/bridge-contract.json');
        if (!$bridgeContractJson['ok']) {
            throw new RuntimeException($bridgeContractJson['error']);
        }
        $bridgeContract = $bridgeContractJson['data'];
        $bridgeTypescript = is_file($reactBridgePublishedRoot . '/src/mtoolNoCodeBridge.ts')
            ? (string) file_get_contents($reactBridgePublishedRoot . '/src/mtoolNoCodeBridge.ts')
            : '';
        $bridgeConsumerNotes = is_file($reactBridgePublishedRoot . '/CONSUMER-NOTES.md')
            ? (string) file_get_contents($reactBridgePublishedRoot . '/CONSUMER-NOTES.md')
            : '';

        app_sample28_no_code_data_app_assert_same('no-code-react-bridge-contract-v0', $bridgeContract['contract_schema_version'] ?? '', 'react bridge contract_schema_version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('no-code-react-bridge-v0', $bridgeContract['bridge_version'] ?? '', 'react bridge_version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('react', $bridgeContract['framework']['name'] ?? '', 'react bridge framework', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('typescript', $bridgeContract['framework']['language'] ?? '', 'react bridge language', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('no-code-runtime-action-intent-v0', $bridgeContract['action_intent_version'] ?? '', 'react bridge action_intent_version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('no-code-runtime-v0', $bridgeContract['contract_invariants']['runtime_preview_version'] ?? '', 'react bridge runtime_preview invariant', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(
            true,
            in_array('src/mtoolNoCodeBridge.ts', is_array($bridgeContract['contract_invariants']['required_files'] ?? null) ? $bridgeContract['contract_invariants']['required_files'] : [], true),
            'react bridge required_files invariant',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            in_array('CONSUMER-NOTES.md', is_array($bridgeContract['contract_invariants']['required_files'] ?? null) ? $bridgeContract['contract_invariants']['required_files'] : [], true),
            'react bridge consumer notes required_files invariant',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) ($bridgeContract['consumer_notes']['contract_boundary'] ?? ''), 'Mtool owns metadata'),
            'react bridge consumer notes contract boundary',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($bridgeContract['consumer_notes']['artifact_parity_notes'][1] ?? '')), 'NO-CODE-JSON-FORMS-PROBE'),
            'react bridge artifact parity notes',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($bridgeContract['consumer_notes']['adapter_handoff_checklist'][2] ?? '')), 'sample28-no-code-react-bridge-build-smoke'),
            'react bridge adapter handoff checklist',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($bridgeContract['consumer_notes']['adapter_troubleshooting_notes'][1] ?? '')), 'displayRuntimeValue'),
            'react bridge adapter troubleshooting notes',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($bridgeContract['consumer_notes']['adapter_doc_index'][0] ?? '')), 'Artifact Parity Notes'),
            'react bridge adapter doc index',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeTypescript, 'createActionIntent'), 'react bridge typescript helper', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeTypescript, 'displayRuntimeValue'), 'react bridge display helper', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeTypescript, 'editableInputFromItem'), 'react bridge editable input helper', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeConsumerNotes, 'Contract Boundary'), 'react bridge consumer notes file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeConsumerNotes, 'Artifact Parity Notes'), 'react bridge parity notes file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeConsumerNotes, 'Adapter Handoff Checklist'), 'react bridge handoff checklist file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeConsumerNotes, 'Adapter Troubleshooting Notes'), 'react bridge troubleshooting notes file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($bridgeConsumerNotes, 'Adapter Documentation Index'), 'react bridge doc index file', $assertionErrors);

        $steps['react_bridge'] = [
            'contract_schema_version' => $bridgeContract['contract_schema_version'] ?? '',
            'bridge_version' => $bridgeContract['bridge_version'] ?? '',
            'framework' => $bridgeContract['framework']['name'] ?? '',
            'language' => $bridgeContract['framework']['language'] ?? '',
            'runtime_preview_invariant' => $bridgeContract['contract_invariants']['runtime_preview_version'] ?? '',
            'consumer_notes_file' => $bridgeConsumerNotes !== '',
            'published_root' => $reactBridgePublishedRoot,
        ];

        $jsonFormsProbeSourceOutputResult = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE28_NO_CODE_JSON_FORMS_PROBE_SOURCE_OUTPUT_KEY,
        );
        if (!$jsonFormsProbeSourceOutputResult['ok'] || $jsonFormsProbeSourceOutputResult['item'] === null) {
            throw new RuntimeException(
                $jsonFormsProbeSourceOutputResult['error'] !== ''
                    ? $jsonFormsProbeSourceOutputResult['error']
                    : 'sample28 no-code JSON Forms probe source output definition was not found.',
            );
        }
        $jsonFormsProbeArtifactResult = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $jsonFormsProbeSourceOutputResult['item'],
            $requestedBy,
        );
        if (!$jsonFormsProbeArtifactResult['ok'] || $jsonFormsProbeArtifactResult['artifact'] === null) {
            throw new RuntimeException('sample28 no-code JSON Forms probe artifact generation failed: ' . $jsonFormsProbeArtifactResult['error']);
        }
        $jsonFormsProbePublishResult = app_project_output_publish_artifact(
            $app,
            $jsonFormsProbeArtifactResult['artifact'],
            $jsonFormsProbeSourceOutputResult['item'],
        );
        if (!$jsonFormsProbePublishResult['ok'] || $jsonFormsProbePublishResult['published'] === null) {
            throw new RuntimeException('sample28 no-code JSON Forms probe artifact publish failed: ' . $jsonFormsProbePublishResult['error']);
        }

        $jsonFormsProbePublishedRoot = (string) ($jsonFormsProbePublishResult['published']['published_root'] ?? '');
        $schemaFormContractJson = app_sample28_no_code_data_app_read_json_file($jsonFormsProbePublishedRoot . '/schema-form-contract.json');
        $jsonSchemaJson = app_sample28_no_code_data_app_read_json_file($jsonFormsProbePublishedRoot . '/json-schema.json');
        $uiSchemaJson = app_sample28_no_code_data_app_read_json_file($jsonFormsProbePublishedRoot . '/ui-schema.json');
        $schemaFormConsumerNotes = is_file($jsonFormsProbePublishedRoot . '/CONSUMER-NOTES.md')
            ? (string) file_get_contents($jsonFormsProbePublishedRoot . '/CONSUMER-NOTES.md')
            : '';
        if (!$schemaFormContractJson['ok'] || !$jsonSchemaJson['ok'] || !$uiSchemaJson['ok']) {
            throw new RuntimeException(
                !$schemaFormContractJson['ok']
                    ? $schemaFormContractJson['error']
                    : (!$jsonSchemaJson['ok'] ? $jsonSchemaJson['error'] : $uiSchemaJson['error']),
            );
        }
        $schemaFormContract = $schemaFormContractJson['data'];
        $jsonSchema = $jsonSchemaJson['data'];
        $uiSchema = $uiSchemaJson['data'];

        app_sample28_no_code_data_app_assert_same('no-code-json-forms-probe-contract-v0', $schemaFormContract['schema_form_contract_version'] ?? '', 'json forms probe contract version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('no-code-json-forms-probe-v0', $schemaFormContract['probe_version'] ?? '', 'json forms probe version', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('no_code_ticket_form', $schemaFormContract['form_screen_key'] ?? '', 'json forms probe form screen', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('update_no_code_ticket', $schemaFormContract['action_key'] ?? '', 'json forms probe action key', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('https://json-schema.org/draft/2020-12/schema', $jsonSchema['$schema'] ?? '', 'json forms probe schema dialect', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('object', $jsonSchema['type'] ?? '', 'json forms probe schema type', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(['title', 'body'], $jsonSchema['required'] ?? [], 'json forms probe required fields', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('string', $jsonSchema['properties']['body']['type'] ?? '', 'json forms probe body schema type', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('body', $jsonSchema['properties']['body']['x-mtool-field-key'] ?? '', 'json forms probe body mtool field key', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, $jsonSchema['properties']['body']['x-mtool-required'] ?? false, 'json forms probe body mtool required', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('input', $jsonSchema['properties']['body']['x-mtool-action-field-role'] ?? '', 'json forms probe body action role', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, $jsonSchema['properties']['body']['x-mtool-client-write'] ?? false, 'json forms probe body client write', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(
            true,
            in_array('x-mtool-field-key', is_array($schemaFormContract['contract_invariants']['mtool_extension_keys'] ?? null) ? $schemaFormContract['contract_invariants']['mtool_extension_keys'] : [], true),
            'json forms probe mtool extension invariant',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            in_array('CONSUMER-NOTES.md', is_array($schemaFormContract['contract_invariants']['required_files'] ?? null) ? $schemaFormContract['contract_invariants']['required_files'] : [], true),
            'json forms probe consumer notes required_files invariant',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) ($schemaFormContract['consumer_notes']['probe_boundary'] ?? ''), 'comparison probe'),
            'json forms probe consumer notes boundary',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($schemaFormContract['consumer_notes']['artifact_parity_notes'][1] ?? '')), 'NO-CODE-REACT-BRIDGE'),
            'json forms probe artifact parity notes',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($schemaFormContract['consumer_notes']['adapter_handoff_checklist'][2] ?? '')), 'sample28-no-code-schema-form-runtime-smoke'),
            'json forms probe adapter handoff checklist',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($schemaFormContract['consumer_notes']['adapter_troubleshooting_notes'][1] ?? '')), 'field_mappings'),
            'json forms probe adapter troubleshooting notes',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains((string) (($schemaFormContract['consumer_notes']['adapter_doc_index'][3] ?? '')), 'Generated Files'),
            'json forms probe adapter doc index',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(
            true,
            str_contains($schemaFormConsumerNotes, '# No-Code JSON Forms Probe Consumer Notes'),
            'json forms probe consumer notes file',
            $assertionErrors,
        );
        app_sample28_no_code_data_app_assert_same(true, str_contains($schemaFormConsumerNotes, 'Artifact Parity Notes'), 'json forms probe parity notes file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($schemaFormConsumerNotes, 'Adapter Handoff Checklist'), 'json forms probe handoff checklist file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($schemaFormConsumerNotes, 'Adapter Troubleshooting Notes'), 'json forms probe troubleshooting notes file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, str_contains($schemaFormConsumerNotes, 'Adapter Documentation Index'), 'json forms probe doc index file', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('VerticalLayout', $uiSchema['type'] ?? '', 'json forms probe ui schema type', $assertionErrors);
        $uiElements = is_array($uiSchema['elements'] ?? null) ? $uiSchema['elements'] : [];
        $bodyUiElement = app_sample28_no_code_data_app_find_by_value($uiElements, 'scope', '#/properties/body');
        app_sample28_no_code_data_app_assert_same('#/properties/body', $bodyUiElement['scope'] ?? '', 'json forms probe body ui scope', $assertionErrors);
        app_sample28_no_code_data_app_assert_same(true, $bodyUiElement['options']['mtoolClientWrite'] ?? false, 'json forms probe body ui client write', $assertionErrors);
        app_sample28_no_code_data_app_assert_same('required', $bodyUiElement['options']['mtoolValidationHint'] ?? '', 'json forms probe body ui validation hint', $assertionErrors);

        $steps['json_forms_probe'] = [
            'schema_form_contract_version' => $schemaFormContract['schema_form_contract_version'] ?? '',
            'probe_version' => $schemaFormContract['probe_version'] ?? '',
            'form_screen_key' => $schemaFormContract['form_screen_key'] ?? '',
            'action_key' => $schemaFormContract['action_key'] ?? '',
            'required_fields' => $jsonSchema['required'] ?? [],
            'mtool_extension_keys' => $schemaFormContract['contract_invariants']['mtool_extension_keys'] ?? [],
            'consumer_notes_file' => $schemaFormConsumerNotes !== '',
            'published_root' => $jsonFormsProbePublishedRoot,
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => $assertionErrors === [],
        'project_key' => $projectKey,
        'table_name' => $tableName,
        'requested_by' => $requestedBy,
        'steps' => $steps,
        'assertion_errors' => $assertionErrors,
        'error' => $assertionErrors === []
            ? ''
            : 'sample28 no-code data app assertions failed.',
    ];
}
