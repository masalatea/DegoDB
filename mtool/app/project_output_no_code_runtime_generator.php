<?php

declare(strict_types=1);

require_once __DIR__ . '/no_code_runtime.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_no_code_runtime_strategy_is_supported(string $strategy): bool
{
    return in_array($strategy, ['no-code-runtime-json', 'no-code-react-bridge', 'no-code-json-forms-probe'], true);
}

function app_project_output_no_code_runtime_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_no_code_runtime_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_no_code_react_bridge_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_no_code_react_bridge_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_no_code_json_forms_probe_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_no_code_json_forms_probe_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_no_code_runtime_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('no-code runtime JSON generation failed.');
    }

    return $json . PHP_EOL;
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,string>
 */
function app_project_output_no_code_runtime_build_emitted_files(array $payload): array
{
    $screenDefinition = is_array($payload['screen_definition'] ?? null) ? $payload['screen_definition'] : [];
    $runtimePreview = is_array($payload['runtime_preview'] ?? null) ? $payload['runtime_preview'] : [];

    return [
        'screen-definition.json' => app_project_output_no_code_runtime_json_text($screenDefinition),
        'runtime-preview.json' => app_project_output_no_code_runtime_json_text($runtimePreview),
        'runtime-preview.html' => app_no_code_runtime_render_preview_html($runtimePreview),
        'README.md' => app_project_output_no_code_runtime_readme_text($payload),
    ];
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,string>
 */
function app_project_output_no_code_react_bridge_build_emitted_files(array $payload): array
{
    $bridgeContract = app_project_output_no_code_react_bridge_contract($payload);

    return [
        'bridge-contract.json' => app_project_output_no_code_runtime_json_text($bridgeContract),
        'index.html' => app_project_output_no_code_react_bridge_index_html_text(),
        'package.json' => app_project_output_no_code_react_bridge_package_json_text(),
        'tsconfig.json' => app_project_output_no_code_react_bridge_tsconfig_json_text(),
        'vite.config.ts' => app_project_output_no_code_react_bridge_vite_config_text(),
        'src/App.tsx' => app_project_output_no_code_react_bridge_app_tsx_text(),
        'src/MtoolNoCodeRuntime.tsx' => app_project_output_no_code_react_bridge_runtime_tsx_text(),
        'src/mtoolNoCodeBridge.ts' => app_project_output_no_code_react_bridge_typescript_text(),
        'src/main.tsx' => app_project_output_no_code_react_bridge_main_tsx_text(),
        'CONSUMER-NOTES.md' => app_project_output_no_code_react_bridge_consumer_notes_text($bridgeContract),
        'README.md' => app_project_output_no_code_react_bridge_readme_text($payload),
    ];
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,string>
 */
function app_project_output_no_code_json_forms_probe_build_emitted_files(array $payload): array
{
    $schemaFormContract = app_project_output_no_code_json_forms_probe_contract($payload);

    return [
        'schema-form-contract.json' => app_project_output_no_code_runtime_json_text($schemaFormContract),
        'json-schema.json' => app_project_output_no_code_runtime_json_text(
            is_array($schemaFormContract['json_schema'] ?? null) ? $schemaFormContract['json_schema'] : [],
        ),
        'ui-schema.json' => app_project_output_no_code_runtime_json_text(
            is_array($schemaFormContract['ui_schema'] ?? null) ? $schemaFormContract['ui_schema'] : [],
        ),
        'CONSUMER-NOTES.md' => app_project_output_no_code_json_forms_probe_consumer_notes_text($schemaFormContract),
        'README.md' => app_project_output_no_code_json_forms_probe_readme_text($schemaFormContract),
    ];
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,mixed>
 */
function app_project_output_no_code_react_bridge_contract(array $payload): array
{
    $screenDefinition = is_array($payload['screen_definition'] ?? null) ? $payload['screen_definition'] : [];
    $runtimePreview = is_array($payload['runtime_preview'] ?? null) ? $payload['runtime_preview'] : [];

    return [
        'contract_schema_version' => 'no-code-react-bridge-contract-v0',
        'bridge_version' => 'no-code-react-bridge-v0',
        'framework' => [
            'name' => 'react',
            'language' => 'typescript',
            'adapter_kind' => 'custom-mtool-screen-schema',
            'status' => 'feasibility-first-slice',
        ],
        'mtool_ownership' => [
            'design_metadata',
            'screen_definition',
            'action_intent',
            'validation_hints',
            'sync_error_hints',
        ],
        'frontend_ownership' => [
            'rendering',
            'routing',
            'components',
            'client_state',
        ],
        'source_artifacts' => [
            'screen_definition' => 'screen-definition.json',
            'runtime_preview' => 'runtime-preview.json',
        ],
        'contract_invariants' => [
            'screen_definition_version' => (string) (($payload['screen_definition']['definition_version'] ?? '') ?: 'no-code-screen-definition-v0'),
            'runtime_preview_version' => (string) (($payload['runtime_preview']['runtime_version'] ?? '') ?: 'no-code-runtime-v0'),
            'action_intent_version' => 'no-code-runtime-action-intent-v0',
            'required_files' => [
                'bridge-contract.json',
                'package.json',
                'src/App.tsx',
                'src/MtoolNoCodeRuntime.tsx',
                'src/main.tsx',
                'src/mtoolNoCodeBridge.ts',
                'CONSUMER-NOTES.md',
            ],
            'screen_model_required_keys' => [
                'screen_key',
                'screen_type',
                'screen_title',
                'fields',
                'actions',
                'data',
            ],
            'runtime_cell_shape' => [
                'value',
                'display_value',
            ],
        ],
        'screen_definition' => $screenDefinition,
        'runtime_preview' => $runtimePreview,
        'custom_operation_handoffs' => app_project_output_no_code_react_bridge_custom_operation_handoffs(
            $screenDefinition,
            $runtimePreview,
        ),
        'action_intent_version' => 'no-code-runtime-action-intent-v0',
        'consumer_notes' => [
            'contract_boundary' => 'Mtool owns metadata, screen definition, runtime preview, validation hints, sync hints, and action-intent shape. Frontend consumers own routing, components, rendering, styling, and durable client application state.',
            'generated_scaffold_status' => 'The React scaffold is a verification and adapter proof. It is not a durable Mtool-owned component library.',
            'custom_operation_handoff_boundary' => 'Custom operation handoffs expose metadata, disabled reasons, and adapter handoff keys for React consumers. They do not grant execution rights or wire server routes.',
            'form_state_boundary' => 'Editable form state is local to the generated React bridge preview and is serialized only into no-code-runtime-action-intent-v0.',
            'schema_form_probe_boundary' => 'The sibling no-code-json-forms-probe artifact is comparison-only and does not replace the custom React bridge.',
            'artifact_parity_notes' => [
                'Inspect NO-CODE-REACT-BRIDGE when validating the custom React + TypeScript adapter, generated component wiring, local form state, and action-intent emission.',
                'Inspect NO-CODE-JSON-FORMS-PROBE when comparing the same form metadata against schema-form ecosystems such as JSON Forms or rjsf.',
                'Both artifacts derive from the same screen definition and runtime preview; behavior changes should start from canonical Mtool metadata or generator code, not hand edits.',
                'Custom operation handoffs are metadata-only adapter inputs; execution remains owned by explicit Mtool routes, policies, CSRF, and audit wiring.',
            ],
            'adapter_handoff_checklist' => [
                'Required files: bridge-contract.json, CONSUMER-NOTES.md, src/mtoolNoCodeBridge.ts, src/MtoolNoCodeRuntime.tsx.',
                'Stable markers: contract_schema_version, bridge_version, contract_invariants, action_intent_version.',
                'Smoke commands: make sample28-no-code-react-bridge-build-smoke and make sample28-no-code-react-bridge-browser-smoke.',
                'Custom operation handoffs: inspect bridge-contract.json custom_operation_handoffs before wiring React buttons to external adapters.',
            ],
            'adapter_troubleshooting_notes' => [
                'If the React scaffold does not build, inspect package.json, tsconfig.json, src/mtoolNoCodeBridge.ts, and bridge-contract.json before changing canonical metadata.',
                'If runtime values render incorrectly, compare runtime_preview screen data cells and the displayRuntimeValue helper.',
                'If action intents are missing expected input fields, compare action fields in bridge-contract.json with createActionIntent and editableInputFromItem.',
            ],
            'adapter_doc_index' => [
                'Start with Artifact Parity Notes to choose the React bridge or schema-form probe artifact.',
                'Use Adapter Handoff Checklist to confirm required files, stable markers, and smoke commands.',
                'Use Adapter Troubleshooting Notes when build, rendering, or action-intent handoff checks fail.',
                'Use Stable Markers and Action Intent sections when comparing generated contract versions.',
            ],
            'stable_contract_markers' => [
                'contract_schema_version',
                'bridge_version',
                'contract_invariants',
                'action_intent_version',
            ],
        ],
        'notes' => [
            'runtime-preview.html remains a verification-only preview.',
            'This bridge intentionally avoids making Mtool own React components as durable product code.',
        ],
    ];
}

/**
 * @param array<string,mixed> $screenDefinition
 * @param array<string,mixed> $runtimePreview
 * @return list<array<string,mixed>>
 */
function app_project_output_no_code_react_bridge_custom_operation_handoffs(
    array $screenDefinition,
    array $runtimePreview,
): array {
    $screenKeysByOperation = [];
    foreach (($runtimePreview['screens'] ?? []) as $screen) {
        if (!is_array($screen)) {
            continue;
        }

        $screenKey = trim((string) ($screen['screen_key'] ?? ''));
        if ($screenKey === '') {
            continue;
        }

        foreach (($screen['custom_operations'] ?? []) as $operation) {
            if (!is_array($operation)) {
                continue;
            }

            $operationKey = trim((string) ($operation['operation_key'] ?? ''));
            if ($operationKey === '') {
                continue;
            }

            $screenKeysByOperation[$operationKey] ??= [];
            $screenKeysByOperation[$operationKey][] = $screenKey;
        }
    }

    $handoffs = [];
    foreach (($screenDefinition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $contractKey = trim((string) ($contract['contract_key'] ?? ''));
        foreach (($contract['custom_operations'] ?? []) as $operation) {
            if (!is_array($operation)) {
                continue;
            }

            $operationKey = trim((string) ($operation['operation_key'] ?? ''));
            if ($operationKey === '') {
                continue;
            }

            $handoffs[] = [
                'contract_key' => $contractKey,
                'operation_key' => $operationKey,
                'label' => trim((string) ($operation['label'] ?? '')),
                'category' => trim((string) ($operation['category'] ?? '')),
                'target' => trim((string) ($operation['target'] ?? '')),
                'side_effect_class' => trim((string) ($operation['side_effect_class'] ?? '')),
                'availability' => trim((string) ($operation['availability'] ?? '')),
                'unavailable_reason' => trim((string) ($operation['unavailable_reason'] ?? '')),
                'policy_key' => trim((string) ($operation['policy_key'] ?? '')),
                'csrf_required' => (bool) ($operation['csrf_required'] ?? true),
                'audit_event' => trim((string) ($operation['audit_event'] ?? '')),
                'adapter_handoff' => trim((string) ($operation['adapter_handoff'] ?? '')),
                'route_boundary' => is_array($operation['route_boundary'] ?? null) ? $operation['route_boundary'] : [],
                'screen_keys' => array_values(array_unique($screenKeysByOperation[$operationKey] ?? [])),
            ];
        }
    }

    usort(
        $handoffs,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['operation_key'] ?? ''),
            (string) ($right['operation_key'] ?? ''),
        ),
    );

    return $handoffs;
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,mixed>
 */
function app_project_output_no_code_json_forms_probe_contract(array $payload): array
{
    $runtimePreview = is_array($payload['runtime_preview'] ?? null) ? $payload['runtime_preview'] : [];
    $screenDefinition = is_array($payload['screen_definition'] ?? null) ? $payload['screen_definition'] : [];
    $screens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];
    $formScreen = [];
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'form') {
            $formScreen = $screen;
            break;
        }
    }

    $fields = is_array($formScreen['fields'] ?? null) ? $formScreen['fields'] : [];
    $actions = is_array($formScreen['actions'] ?? null) ? $formScreen['actions'] : [];
    $properties = [];
    $required = [];
    $uiElements = [];
    $fieldMappings = [];
    $actionFields = is_array($actions[0]['fields'] ?? null) ? $actions[0]['fields'] : [];
    $actionFieldsByKey = app_project_output_no_code_json_forms_probe_action_fields_by_key($actionFields);

    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fieldKey = (string) ($field['field_key'] ?? '');
        if ($fieldKey === '') {
            continue;
        }

        $fieldType = (string) ($field['type'] ?? '');
        $isRequired = (bool) ($field['required'] ?? false);
        $isReadonly = (bool) ($field['readonly'] ?? false);
        $label = (string) ($field['label'] ?? $fieldKey);
        $jsonType = app_project_output_no_code_json_forms_probe_json_schema_type($fieldType);
        $jsonFormat = app_project_output_no_code_json_forms_probe_json_schema_format($fieldType);
        $actionField = $actionFieldsByKey[$fieldKey] ?? [];
        $actionFieldRole = (string) ($actionField['role'] ?? '');
        $clientWrite = (bool) ($actionField['client_write'] ?? false);
        $schemaKeywords = ['type', 'title'];

        $property = [
            'type' => $jsonType,
            'title' => $label,
            'description' => app_project_output_no_code_json_forms_probe_field_description(
                $fieldKey,
                $isRequired,
                $isReadonly,
                $actionFieldRole,
                $clientWrite,
            ),
            'x-mtool-field-key' => $fieldKey,
            'x-mtool-field-type' => $fieldType,
            'x-mtool-required' => $isRequired,
            'x-mtool-readonly' => $isReadonly,
            'x-mtool-action-field-role' => $actionFieldRole,
            'x-mtool-client-write' => $clientWrite,
        ];
        $schemaKeywords[] = 'description';
        if ($isReadonly) {
            $property['readOnly'] = true;
            $schemaKeywords[] = 'readOnly';
        }
        if ($jsonFormat !== '') {
            $property['format'] = $jsonFormat;
            $schemaKeywords[] = 'format';
        }
        if ($isRequired) {
            $required[] = $fieldKey;
            if ($jsonType === 'string') {
                $property['minLength'] = 1;
                $property['pattern'] = '\\S';
                $property['x-mtool-blank-is-missing'] = true;
                $schemaKeywords[] = 'minLength';
                $schemaKeywords[] = 'pattern';
                $schemaKeywords[] = 'x-mtool-blank-is-missing';
            }
        }
        $properties[$fieldKey] = $property;

        $uiElement = [
            'type' => 'Control',
            'scope' => '#/properties/' . $fieldKey,
            'label' => $label,
            'options' => [
                'readonly' => $isReadonly,
                'required' => $isRequired,
                'mtoolFieldKey' => $fieldKey,
                'mtoolFieldType' => $fieldType,
                'mtoolActionFieldRole' => $actionFieldRole,
                'mtoolClientWrite' => $clientWrite,
                'mtoolValidationHint' => $isReadonly ? 'read-only' : ($isRequired ? 'required' : 'optional'),
            ],
        ];
        $uiElements[] = $uiElement;

        $fieldMappings[] = [
            'field_key' => $fieldKey,
            'json_schema_pointer' => '#/properties/' . $fieldKey,
            'ui_schema_scope' => '#/properties/' . $fieldKey,
            'json_schema_type' => $jsonType,
            'json_schema_format' => $jsonFormat,
            'required' => $isRequired,
            'readonly' => $isReadonly,
            'action_field_role' => $actionFieldRole,
            'client_write' => $clientWrite,
            'json_schema_keywords' => $schemaKeywords,
        ];
    }

    $screenKey = (string) ($formScreen['screen_key'] ?? '');
    $actionKey = (string) ($actions[0]['action_key'] ?? '');

    $jsonSchema = [
        '$schema' => 'https://json-schema.org/draft/2020-12/schema',
        '$id' => 'mtool://no-code-json-forms-probe/' . (string) ($runtimePreview['project_key'] ?? '') . '/' . $screenKey,
        'title' => (string) ($formScreen['screen_title'] ?? $screenKey),
        'type' => 'object',
        'properties' => $properties,
        'required' => array_values($required),
        'additionalProperties' => false,
    ];

    $uiSchema = [
        'type' => 'VerticalLayout',
        'elements' => $uiElements,
    ];

    return [
        'schema_form_contract_version' => 'no-code-json-forms-probe-contract-v0',
        'probe_version' => 'no-code-json-forms-probe-v0',
        'adapter_kind' => 'schema-form-comparison-probe',
        'schema_form_targets' => [
            'json-forms',
            'rjsf',
        ],
        'source_artifacts' => [
            'screen_definition' => 'screen-definition.json',
            'runtime_preview' => 'runtime-preview.json',
        ],
        'contract_invariants' => [
            'screen_definition_version' => (string) (($screenDefinition['definition_version'] ?? '') ?: 'no-code-screen-definition-v0'),
            'runtime_preview_version' => (string) (($runtimePreview['runtime_version'] ?? '') ?: 'no-code-runtime-v0'),
            'json_schema_dialect' => 'https://json-schema.org/draft/2020-12/schema',
            'required_files' => [
                'schema-form-contract.json',
                'json-schema.json',
                'ui-schema.json',
                'CONSUMER-NOTES.md',
            ],
            'action_intent_version' => 'no-code-runtime-action-intent-v0',
            'mtool_extension_keys' => [
                'x-mtool-field-key',
                'x-mtool-field-type',
                'x-mtool-required',
                'x-mtool-readonly',
                'x-mtool-action-field-role',
                'x-mtool-client-write',
                'x-mtool-blank-is-missing',
            ],
        ],
        'validation_parity' => [
            'required_blank_string_policy' => 'Required string fields use minLength 1 and pattern \\S so blank or whitespace-only values align with generated runtime and React bridge fail-close behavior.',
            'raw_error_code' => 'input.missing:<field_key>',
            'display_message' => 'Required input is missing: <field_key>',
        ],
        'form_screen_key' => $screenKey,
        'action_key' => $actionKey,
        'json_schema' => $jsonSchema,
        'ui_schema' => $uiSchema,
        'field_mappings' => $fieldMappings,
        'consumer_notes' => [
            'probe_boundary' => 'This artifact is a schema-form comparison probe. It does not make JSON Forms or rjsf the product runtime and does not replace the custom React bridge.',
            'mtool_ownership' => 'Mtool owns field metadata, action-field hints, JSON Schema generation, UI Schema ordering metadata, and x-mtool extension keys.',
            'consumer_ownership' => 'Schema-form consumers own renderer selection, widgets, validation presentation, styling, routing, and durable client state.',
            'runtime_smoke_boundary' => 'The focused rjsf runtime smoke verifies consumer viability only; it is not bundled into generated product code.',
            'validation_parity_boundary' => 'Required string fields include JSON Schema minLength 1 and pattern \\S to match Mtool blank required handling. Schema-form consumers still own final validation presentation.',
            'artifact_parity_notes' => [
                'Inspect NO-CODE-JSON-FORMS-PROBE when comparing generated form metadata with JSON Forms or rjsf conventions.',
                'Inspect NO-CODE-REACT-BRIDGE when validating the custom React + TypeScript adapter, local form state, and action-intent emission.',
                'Both artifacts derive from the same screen definition and runtime preview; differences should be treated as adapter-surface differences, not separate source-of-truth metadata.',
            ],
            'adapter_handoff_checklist' => [
                'Required files: schema-form-contract.json, json-schema.json, ui-schema.json, CONSUMER-NOTES.md.',
                'Stable markers: schema_form_contract_version, probe_version, contract_invariants, form_screen_key, action_key.',
                'Smoke command: make sample28-no-code-schema-form-runtime-smoke.',
                'Validation parity: required string fields should reject missing, empty, and whitespace-only values.',
            ],
            'adapter_troubleshooting_notes' => [
                'If the schema-form smoke does not render, inspect schema-form-contract.json before editing json-schema.json or ui-schema.json by hand.',
                'If required or readonly hints differ from React bridge behavior, compare field_mappings and x-mtool extension keys against the form screen fields.',
                'If blank required values pass schema-form validation, inspect required string field minLength, pattern, and x-mtool-blank-is-missing.',
                'If action payload expectations differ, compare action field roles in schema-form-contract.json with the React bridge action-intent contract.',
            ],
            'adapter_doc_index' => [
                'Start with Artifact Parity Notes to choose the schema-form probe or React bridge artifact.',
                'Use Adapter Handoff Checklist to confirm required files, stable markers, and smoke commands.',
                'Use Adapter Troubleshooting Notes when schema render, field mapping, or action-role checks fail.',
                'Use Stable Markers and Generated Files sections when comparing generated schema-form contract versions.',
            ],
            'stable_contract_markers' => [
                'schema_form_contract_version',
                'probe_version',
                'contract_invariants',
                'form_screen_key',
                'action_key',
            ],
        ],
        'notes' => [
            'This artifact is a comparison probe for schema-form ecosystems.',
            'The custom React bridge remains the default first adapter.',
            'No JSON Forms or rjsf runtime UI is bundled in this first slice.',
        ],
    ];
}

function app_project_output_no_code_json_forms_probe_json_schema_type(string $fieldType): string
{
    return match (strtolower(trim($fieldType))) {
        'int', 'integer', 'bigint', 'smallint' => 'integer',
        'float', 'double', 'decimal', 'number' => 'number',
        'bool', 'boolean' => 'boolean',
        default => 'string',
    };
}

function app_project_output_no_code_json_forms_probe_json_schema_format(string $fieldType): string
{
    return match (strtolower(trim($fieldType))) {
        'date' => 'date',
        'datetime', 'datetime-local', 'timestamp' => 'date-time',
        'email' => 'email',
        'url', 'uri' => 'uri',
        default => '',
    };
}

/**
 * @param list<array<string,mixed>> $actionFields
 * @return array<string,array<string,mixed>>
 */
function app_project_output_no_code_json_forms_probe_action_fields_by_key(array $actionFields): array
{
    $items = [];
    foreach ($actionFields as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fieldKey = (string) ($field['field_key'] ?? '');
        if ($fieldKey === '') {
            continue;
        }

        $items[$fieldKey] = $field;
    }

    return $items;
}

function app_project_output_no_code_json_forms_probe_field_description(
    string $fieldKey,
    bool $required,
    bool $readonly,
    string $actionFieldRole,
    bool $clientWrite,
): string {
    $parts = [
        'Mtool field `' . $fieldKey . '`.',
        $required ? 'Required.' : 'Optional.',
        $readonly ? 'Read-only.' : 'Editable in the generated form model.',
    ];

    if ($actionFieldRole !== '') {
        $parts[] = 'Action field role: `' . $actionFieldRole . '`.';
        $parts[] = $clientWrite ? 'Client write is allowed.' : 'Client write is not allowed.';
    }

    return implode(' ', $parts);
}

function app_project_output_no_code_react_bridge_package_json_text(): string
{
    return app_project_output_no_code_runtime_json_text([
        'name' => 'mtool-no-code-react-bridge-preview',
        'private' => true,
        'version' => '0.0.0',
        'type' => 'module',
        'scripts' => [
            'dev' => 'vite',
            'build' => 'tsc -b && vite build',
            'preview' => 'vite preview',
        ],
        'dependencies' => [
            'react' => '^19.0.0',
            'react-dom' => '^19.0.0',
        ],
        'devDependencies' => [
            '@vitejs/plugin-react' => '^5.0.0',
            '@types/react' => '^19.0.0',
            '@types/react-dom' => '^19.0.0',
            'typescript' => '^5.0.0',
            'vite' => '^7.0.0',
        ],
    ]);
}

function app_project_output_no_code_react_bridge_tsconfig_json_text(): string
{
    return app_project_output_no_code_runtime_json_text([
        'compilerOptions' => [
            'target' => 'ES2022',
            'useDefineForClassFields' => true,
            'lib' => ['ES2022', 'DOM', 'DOM.Iterable'],
            'allowJs' => false,
            'skipLibCheck' => true,
            'esModuleInterop' => true,
            'allowSyntheticDefaultImports' => true,
            'strict' => true,
            'forceConsistentCasingInFileNames' => true,
            'module' => 'ESNext',
            'moduleResolution' => 'Node',
            'resolveJsonModule' => true,
            'isolatedModules' => true,
            'noEmit' => true,
            'jsx' => 'react-jsx',
        ],
        'include' => ['src', 'bridge-contract.json'],
    ]);
}

function app_project_output_no_code_react_bridge_index_html_text(): string
{
    return <<<'HTML'
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mtool No-code React Bridge</title>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
HTML;
}

function app_project_output_no_code_react_bridge_vite_config_text(): string
{
    return <<<'TS'
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
});
TS;
}

function app_project_output_no_code_react_bridge_main_tsx_text(): string
{
    return <<<'TSX'
import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

createRoot(document.getElementById('root') as HTMLElement).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
);
TSX;
}

function app_project_output_no_code_react_bridge_app_tsx_text(): string
{
    return <<<'TSX'
import { useEffect, useState } from 'react';
import bridgeContract from '../bridge-contract.json';
import { MtoolNoCodeRuntime } from './MtoolNoCodeRuntime';
import { createActionIntentResult, currentItem, editableInputFromItem } from './mtoolNoCodeBridge';
import type { MtoolBridgeContract, MtoolNoCodeActionIntent, MtoolNoCodeActionIntentResult } from './mtoolNoCodeBridge';

const contract = bridgeContract as unknown as MtoolBridgeContract;

declare global {
  interface Window {
    __mtoolNoCodeReactBridgeContract?: MtoolBridgeContract;
    __mtoolNoCodeReactBridgeLastIntent?: unknown;
    __mtoolNoCodeReactBridgeLastActionResult?: MtoolNoCodeActionIntentResult;
    __mtoolNoCodeReactBridgeCreateActionIntent?: (screenKey: string, actionKey: string) => MtoolNoCodeActionIntentResult;
    __mtoolNoCodeReactBridgeFormState?: Record<string, Record<string, unknown>>;
  }
}

window.__mtoolNoCodeReactBridgeContract = contract;
window.__mtoolNoCodeReactBridgeFormState = {};
window.__mtoolNoCodeReactBridgeCreateActionIntent = (screenKey: string, actionKey: string) => {
  const screen = contract.runtime_preview.screens.find((candidate) => candidate.screen_key === screenKey);
  if (!screen) {
    throw new Error(`Unknown screen: ${screenKey}`);
  }
  const action = screen.actions.find((candidate) => candidate.action_key === actionKey);
  if (!action) {
    throw new Error(`Unknown action: ${actionKey}`);
  }

  const input = window.__mtoolNoCodeReactBridgeFormState?.[screenKey] ?? editableInputFromItem(screen, currentItem(screen));
  const result = createActionIntentResult(screen, action, input);
  window.__mtoolNoCodeReactBridgeLastActionResult = result;
  if (result.ok) {
    window.__mtoolNoCodeReactBridgeLastIntent = result.intent;
    window.dispatchEvent(new CustomEvent('mtool-no-code-react-bridge-intent', { detail: result.intent }));
  }
  return result;
};

export default function App() {
  const [lastIntent, setLastIntent] = useState<MtoolNoCodeActionIntent | null>(null);
  const [actionError, setActionError] = useState('');

  useEffect(() => {
    const listener = (event: Event) => {
      setLastIntent((event as CustomEvent<MtoolNoCodeActionIntent>).detail);
    };
    window.addEventListener('mtool-no-code-react-bridge-intent', listener);

    return () => window.removeEventListener('mtool-no-code-react-bridge-intent', listener);
  }, []);

  return (
    <>
      <MtoolNoCodeRuntime
        contract={contract}
        onAction={(screen, action, input) => {
          const result = createActionIntentResult(screen, action, input);
          window.__mtoolNoCodeReactBridgeLastActionResult = result;
          if (!result.ok) {
            setActionError(result.message);
            console.warn('Mtool no-code action intent blocked', result.error);
            return;
          }
          window.__mtoolNoCodeReactBridgeLastIntent = result.intent;
          setLastIntent(result.intent);
          setActionError('');
          console.log('Mtool no-code action intent', result.intent);
        }}
      />
      <section
        data-mtool-react-bridge-action-feedback
        data-state={actionError ? 'error' : lastIntent ? 'success' : 'idle'}
        data-action-key={lastIntent?.action_key ?? ''}
      >
        <h2>Action Feedback</h2>
        {actionError ? (
          <p>{actionError}</p>
        ) : lastIntent ? (
          <p>
            Last intent: {lastIntent.action_key} on {lastIntent.screen_key}
          </p>
        ) : (
          <p>No action intent emitted yet.</p>
        )}
      </section>
    </>
  );
}
TSX;
}

function app_project_output_no_code_react_bridge_runtime_tsx_text(): string
{
    return <<<'TSX'
import { useEffect, useMemo, useState } from 'react';
import type { MtoolBridgeContract, MtoolRuntimeAction, MtoolRuntimeScreen } from './mtoolNoCodeBridge';
import { currentItem, displayRuntimeValue, editableInputFromItem } from './mtoolNoCodeBridge';

type Props = {
  contract: MtoolBridgeContract;
  onAction: (screen: MtoolRuntimeScreen, action: MtoolRuntimeAction, input: Record<string, unknown>) => void;
};

declare global {
  interface Window {
    __mtoolNoCodeReactBridgeFormState?: Record<string, Record<string, unknown>>;
  }
}

export function MtoolNoCodeRuntime({ contract, onAction }: Props) {
  const screens = contract.runtime_preview.screens;

  return (
    <main data-mtool-react-bridge={contract.bridge_version} data-runtime-version={contract.runtime_preview.runtime_version}>
      <header>
        <h1>{contract.runtime_preview.project_key} No-code React Bridge</h1>
        <p>{contract.framework.adapter_kind}</p>
      </header>
      {screens.map((screen) => {
        const item = currentItem(screen);

        return (
          <section key={screen.screen_key} data-screen-key={screen.screen_key} data-screen-type={screen.screen_type}>
            <h2>{screen.screen_title}</h2>
            <p>{screen.screen_subtitle}</p>
            {screen.sync_error_retry_hint ? <p>{screen.sync_error_retry_hint}</p> : null}
            {screen.screen_type === 'form' ? (
              <MtoolNoCodeFormScreen screen={screen} item={item} onAction={onAction} />
            ) : (
              <>
                <dl>
                  {screen.fields.map((field) => (
                    <div key={field.field_key} data-field-key={field.field_key}>
                      <dt>{field.label}</dt>
                      <dd data-display-value={displayRuntimeValue(item[field.field_key])}>
                        {displayRuntimeValue(item[field.field_key])}
                      </dd>
                    </div>
                  ))}
                </dl>
                <MtoolNoCodeActions screen={screen} input={item} onAction={onAction} />
              </>
            )}
          </section>
        );
      })}
    </main>
  );
}

type FormScreenProps = {
  screen: MtoolRuntimeScreen;
  item: Record<string, unknown>;
  onAction: Props['onAction'];
};

function MtoolNoCodeFormScreen({ screen, item, onAction }: FormScreenProps) {
  const initialInput = useMemo(() => editableInputFromItem(screen, item), [screen, item]);
  const [input, setInput] = useState<Record<string, unknown>>(initialInput);

  useEffect(() => {
    setInput(initialInput);
  }, [initialInput]);

  useEffect(() => {
    window.__mtoolNoCodeReactBridgeFormState = {
      ...(window.__mtoolNoCodeReactBridgeFormState ?? {}),
      [screen.screen_key]: input,
    };
  }, [input, screen.screen_key]);

  return (
    <>
      <form onSubmit={(event) => event.preventDefault()}>
        {screen.fields.map((field) => (
          <label
            key={field.field_key}
            data-field-key={field.field_key}
            data-field-required={field.required ? 'true' : 'false'}
            data-field-readonly={field.readonly ? 'true' : 'false'}
          >
            <span>{field.label}</span>
            <input
              data-field-key={field.field_key}
              data-display-value={displayRuntimeValue(item[field.field_key])}
              aria-required={field.required ? 'true' : 'false'}
              required={field.required}
              readOnly={field.readonly}
              value={String(input[field.field_key] ?? '')}
              onChange={(event) => {
                setInput((current) => ({
                  ...current,
                  [field.field_key]: event.target.value,
                }));
              }}
            />
            <small data-field-hint={field.field_key}>
              {field.readonly ? 'Read only' : field.required ? 'Required' : 'Optional'}
            </small>
          </label>
        ))}
      </form>
      <MtoolNoCodeActions screen={screen} input={input} onAction={onAction} />
    </>
  );
}

type ActionsProps = {
  screen: MtoolRuntimeScreen;
  input: Record<string, unknown>;
  onAction: Props['onAction'];
};

function MtoolNoCodeActions({ screen, input, onAction }: ActionsProps) {
  return (
    <>
      {screen.actions.map((action) => (
        <button
          key={action.action_key}
          type="button"
          data-action-key={action.action_key}
          data-operation-key={action.operation_key}
          data-operation-type={action.operation_type}
          data-action-enabled={action.enabled ? 'true' : 'false'}
          disabled={!action.enabled}
          onClick={() => onAction(screen, action, input)}
        >
          {action.label}
        </button>
      ))}
    </>
  );
}
TSX;
}

function app_project_output_no_code_react_bridge_typescript_text(): string
{
    return <<<'TS'
export type MtoolBridgeContract = {
  contract_schema_version: 'no-code-react-bridge-contract-v0';
  bridge_version: 'no-code-react-bridge-v0';
  framework: {
    name: 'react';
    language: 'typescript';
    adapter_kind: 'custom-mtool-screen-schema';
    status: string;
  };
  contract_invariants: MtoolBridgeContractInvariants;
  runtime_preview: MtoolRuntimePreview;
  custom_operation_handoffs: MtoolCustomOperationHandoff[];
  action_intent_version: 'no-code-runtime-action-intent-v0';
};

export type MtoolBridgeContractInvariants = {
  screen_definition_version: 'no-code-screen-definition-v0';
  runtime_preview_version: 'no-code-runtime-v0';
  action_intent_version: 'no-code-runtime-action-intent-v0';
  required_files: string[];
  screen_model_required_keys: string[];
  runtime_cell_shape: Array<'value' | 'display_value'>;
};

export type MtoolRuntimePreview = {
  ok: boolean;
  runtime_version: string;
  project_key: string;
  screens: MtoolRuntimeScreen[];
};

export type MtoolCustomOperationHandoff = {
  contract_key: string;
  operation_key: string;
  label: string;
  category: string;
  target: string;
  side_effect_class: string;
  availability: string;
  unavailable_reason: string;
  policy_key: string;
  csrf_required: boolean;
  audit_event: string;
  adapter_handoff: string;
  route_boundary: MtoolCustomOperationRouteBoundary;
  screen_keys: string[];
};

export type MtoolCustomOperationRouteBoundary = {
  method: string;
  path: string;
  response_shape: string;
  auth_guard: string;
  idempotency: string;
  failure_modes: string[];
};

export type MtoolRuntimeScreen = {
  screen_key: string;
  screen_type: string;
  screen_title: string;
  screen_subtitle: string;
  fields: MtoolRuntimeField[];
  actions: MtoolRuntimeAction[];
  data: {
    rows?: Array<Record<string, unknown>>;
    item?: Record<string, unknown>;
  };
  sync_error_retry_hint?: string;
};

export type MtoolRuntimeCell = {
  value?: unknown;
  display_value?: unknown;
};

export type MtoolRuntimeField = {
  field_key: string;
  label: string;
  type: string;
  readonly: boolean;
  required: boolean;
  visibility: string;
};

export type MtoolRuntimeAction = {
  action_key: string;
  label: string;
  operation_key: string;
  operation_type: string;
  enabled: boolean;
  availability: string;
  fields?: MtoolRuntimeActionField[];
};

export type MtoolRuntimeActionField = {
  field_key: string;
  role: string;
  required: boolean;
};

export type MtoolNoCodeActionIntent = {
  intent_version: 'no-code-runtime-action-intent-v0';
  screen_key: string;
  action_key: string;
  input: Record<string, unknown>;
};

export type MtoolNoCodeActionIntentResult = {
  ok: boolean;
  intent: MtoolNoCodeActionIntent | null;
  error: string;
  message: string;
};

export function currentItem(screen: MtoolRuntimeScreen): Record<string, unknown> {
  if (screen.data.item && typeof screen.data.item === 'object') {
    return screen.data.item;
  }
  if ((screen.data.rows ?? []).length > 0) {
    return screen.data.rows?.[0] ?? {};
  }

  return {};
}

export function displayRuntimeValue(value: unknown): string {
  if (isRuntimeCell(value)) {
    const displayValue = value.display_value;
    if (displayValue !== undefined && displayValue !== null) {
      return String(displayValue);
    }

    return value.value === undefined || value.value === null ? '' : String(value.value);
  }

  return value === undefined || value === null ? '' : String(value);
}

export function runtimeInputValue(value: unknown): unknown {
  if (isRuntimeCell(value)) {
    return value.value ?? null;
  }

  return value;
}

export function editableInputFromItem(
  screen: MtoolRuntimeScreen,
  item: Record<string, unknown>,
): Record<string, unknown> {
  return Object.fromEntries(screen.fields.map((field) => [
    field.field_key,
    runtimeInputValue(item[field.field_key]) ?? '',
  ]));
}

export function createActionIntent(
  screen: MtoolRuntimeScreen,
  action: MtoolRuntimeAction,
  input: Record<string, unknown>,
): MtoolNoCodeActionIntent {
  const result = createActionIntentResult(screen, action, input);
  if (!result.ok || result.intent === null) {
    throw new Error(result.error || 'Action intent could not be prepared.');
  }

  return result.intent;
}

export function createActionIntentResult(
  screen: MtoolRuntimeScreen,
  action: MtoolRuntimeAction,
  input: Record<string, unknown>,
): MtoolNoCodeActionIntentResult {
  const failed = (action.fields ?? [])
    .filter((field) => field.role === 'input' && field.required && requiredValueIsEmpty(input[field.field_key]))
    .map((field) => `input.missing:${field.field_key}`);

  const intent: MtoolNoCodeActionIntent = {
    intent_version: 'no-code-runtime-action-intent-v0',
    screen_key: screen.screen_key,
    action_key: action.action_key,
    input: Object.fromEntries(Object.entries(input).map(([key, value]) => [key, runtimeInputValue(value)])),
  };

  if (failed.length > 0) {
    const error = Array.from(new Set(failed)).join(', ');
    return {
      ok: false,
      intent,
      error,
      message: validationMessage(error),
    };
  }

  return {
    ok: true,
    intent,
    error: '',
    message: '',
  };
}

function isRuntimeCell(value: unknown): value is MtoolRuntimeCell {
  return typeof value === 'object'
    && value !== null
    && ('value' in value || 'display_value' in value);
}

function requiredValueIsEmpty(value: unknown): boolean {
  const runtimeValue = runtimeInputValue(value);
  return runtimeValue === undefined
    || runtimeValue === null
    || (typeof runtimeValue === 'string' && runtimeValue.trim() === '');
}

export function validationMessage(error: string): string {
  const parts = error.split(',').map((part) => part.trim()).filter(Boolean);
  if (parts.length === 0) {
    return 'Action intent could not be prepared.';
  }

  const messages = parts.map((part) => {
    if (part.startsWith('input.missing:')) {
      return `Required input is missing: ${part.slice('input.missing:'.length)}`;
    }
    if (part.startsWith('input.readonly:')) {
      return `Input is read-only: ${part.slice('input.readonly:'.length)}`;
    }

    return part;
  });

  return Array.from(new Set(messages)).join(', ');
}
TS;
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_no_code_react_bridge_readme_text(array $payload): string
{
    $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];

    return implode("\n", [
        '# No-Code React Bridge Artifact',
        '',
        'Generated React + TypeScript bridge scaffold from canonical Mtool no-code metadata.',
        '',
        '- `bridge-contract.json` embeds the framework-facing bridge contract and source runtime preview.',
        '- `CONSUMER-NOTES.md` explains the generated bridge boundary for frontend and schema-form consumers.',
        '- `src/mtoolNoCodeBridge.ts` defines the minimal TypeScript contract and action intent helper.',
        '- `src/MtoolNoCodeRuntime.tsx` is a small custom adapter proof, not durable Mtool-owned UI product code.',
        '- `runtime-preview.html` remains the verification-only HTML preview in the sibling no-code runtime artifact.',
        '- Do not hand-edit generated files; update canonical Mtool metadata or the bridge generator instead.',
        '',
        'Contract count: ' . (int) ($summary['contract_count'] ?? 0),
        'Screen count: ' . (int) ($summary['screen_count'] ?? 0),
        'Preview count: ' . (int) ($summary['preview_count'] ?? 0),
        'Status: ' . ((bool) ($payload['ok'] ?? false) ? 'ok' : 'failed'),
        '',
    ]);
}

/**
 * @param array<string,mixed> $contract
 */
function app_project_output_no_code_react_bridge_consumer_notes_text(array $contract): string
{
    $consumerNotes = is_array($contract['consumer_notes'] ?? null) ? $contract['consumer_notes'] : [];
    $artifactParityNotes = is_array($consumerNotes['artifact_parity_notes'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['artifact_parity_notes'])))
        : [];
    $handoffChecklist = is_array($consumerNotes['adapter_handoff_checklist'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['adapter_handoff_checklist'])))
        : [];
    $troubleshootingNotes = is_array($consumerNotes['adapter_troubleshooting_notes'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['adapter_troubleshooting_notes'])))
        : [];
    $docIndex = is_array($consumerNotes['adapter_doc_index'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['adapter_doc_index'])))
        : [];
    $stableMarkers = is_array($consumerNotes['stable_contract_markers'] ?? null)
        ? array_values(array_map('strval', $consumerNotes['stable_contract_markers']))
        : [];

    return implode("\n", [
        '# No-Code React Bridge Consumer Notes',
        '',
        'This generated artifact is a consumer-facing adapter proof for Mtool no-code metadata.',
        '',
        '## Adapter Documentation Index',
        '',
        implode("\n", array_map(static fn (string $item): string => '- ' . $item, $docIndex)),
        '',
        '## Contract Boundary',
        '',
        (string) ($consumerNotes['contract_boundary'] ?? ''),
        '',
        '## Generated Scaffold Status',
        '',
        (string) ($consumerNotes['generated_scaffold_status'] ?? ''),
        '',
        '## Form State Boundary',
        '',
        (string) ($consumerNotes['form_state_boundary'] ?? ''),
        '',
        '## Custom Operation Handoff Boundary',
        '',
        (string) ($consumerNotes['custom_operation_handoff_boundary'] ?? ''),
        '',
        '## Schema-Form Probe Boundary',
        '',
        (string) ($consumerNotes['schema_form_probe_boundary'] ?? ''),
        '',
        '## Artifact Parity Notes',
        '',
        implode("\n", array_map(static fn (string $note): string => '- ' . $note, $artifactParityNotes)),
        '',
        '## Adapter Handoff Checklist',
        '',
        implode("\n", array_map(static fn (string $item): string => '- ' . $item, $handoffChecklist)),
        '',
        '## Adapter Troubleshooting Notes',
        '',
        implode("\n", array_map(static fn (string $note): string => '- ' . $note, $troubleshootingNotes)),
        '',
        '## Stable Markers',
        '',
        implode("\n", array_map(static fn (string $marker): string => '- `' . $marker . '`', $stableMarkers)),
        '',
        '## Action Intent',
        '',
        'Generated actions emit `no-code-runtime-action-intent-v0`. Consumers may observe or translate that intent, but this scaffold does not execute server mutations, persistence, transport, retry scheduling, or validation rules.',
        '',
        '## Editing Guidance',
        '',
        'Do not hand-edit generated bridge files. Change canonical Mtool metadata or the generator, then regenerate the artifact.',
        '',
        'Contract schema version: ' . (string) ($contract['contract_schema_version'] ?? ''),
        'Bridge version: ' . (string) ($contract['bridge_version'] ?? ''),
        'Action intent version: ' . (string) ($contract['action_intent_version'] ?? ''),
        '',
    ]);
}

/**
 * @param array<string,mixed> $contract
 */
function app_project_output_no_code_json_forms_probe_readme_text(array $contract): string
{
    $fieldMappings = is_array($contract['field_mappings'] ?? null) ? $contract['field_mappings'] : [];

    return implode("\n", [
        '# No-Code JSON Forms Probe Artifact',
        '',
        'Generated schema-form comparison artifact from canonical Mtool no-code metadata.',
        '',
        '- `schema-form-contract.json` keeps the probe metadata, invariants, JSON Schema, UI Schema, and field mapping together.',
        '- `json-schema.json` is a JSON Schema style object schema for the selected form screen.',
        '- `ui-schema.json` is a JSON Forms style UI Schema layout that rjsf consumers can also inspect as field-order metadata.',
        '- `CONSUMER-NOTES.md` explains the schema-form probe boundary for JSON Forms / rjsf consumers.',
        '- This first slice does not bundle JSON Forms or rjsf runtime UI and does not replace the custom React bridge.',
        '- Do not hand-edit generated files; update canonical Mtool metadata or the probe generator instead.',
        '',
        'Form screen: ' . (string) ($contract['form_screen_key'] ?? ''),
        'Action: ' . (string) ($contract['action_key'] ?? ''),
        'Field count: ' . count($fieldMappings),
        'Status: comparison-probe',
        '',
    ]);
}

/**
 * @param array<string,mixed> $contract
 */
function app_project_output_no_code_json_forms_probe_consumer_notes_text(array $contract): string
{
    $consumerNotes = is_array($contract['consumer_notes'] ?? null) ? $contract['consumer_notes'] : [];
    $artifactParityNotes = is_array($consumerNotes['artifact_parity_notes'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['artifact_parity_notes'])))
        : [];
    $handoffChecklist = is_array($consumerNotes['adapter_handoff_checklist'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['adapter_handoff_checklist'])))
        : [];
    $troubleshootingNotes = is_array($consumerNotes['adapter_troubleshooting_notes'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['adapter_troubleshooting_notes'])))
        : [];
    $docIndex = is_array($consumerNotes['adapter_doc_index'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['adapter_doc_index'])))
        : [];
    $validationParityBoundary = (string) ($consumerNotes['validation_parity_boundary'] ?? '');
    $stableMarkers = is_array($consumerNotes['stable_contract_markers'] ?? null)
        ? array_values(array_filter(array_map('strval', $consumerNotes['stable_contract_markers'])))
        : [];

    return implode("\n", [
        '# No-Code JSON Forms Probe Consumer Notes',
        '',
        '## Adapter Documentation Index',
        '',
        implode("\n", array_map(static fn (string $item): string => '- ' . $item, $docIndex)),
        '',
        '## Probe Boundary',
        '',
        (string) ($consumerNotes['probe_boundary'] ?? ''),
        '',
        '## Ownership',
        '',
        '- ' . (string) ($consumerNotes['mtool_ownership'] ?? ''),
        '- ' . (string) ($consumerNotes['consumer_ownership'] ?? ''),
        '',
        '## Runtime Smoke Boundary',
        '',
        (string) ($consumerNotes['runtime_smoke_boundary'] ?? ''),
        '',
        '## Validation Parity Boundary',
        '',
        $validationParityBoundary,
        '',
        '## Artifact Parity Notes',
        '',
        implode("\n", array_map(static fn (string $note): string => '- ' . $note, $artifactParityNotes)),
        '',
        '## Adapter Handoff Checklist',
        '',
        implode("\n", array_map(static fn (string $item): string => '- ' . $item, $handoffChecklist)),
        '',
        '## Adapter Troubleshooting Notes',
        '',
        implode("\n", array_map(static fn (string $note): string => '- ' . $note, $troubleshootingNotes)),
        '',
        '## Stable Markers',
        '',
        implode("\n", array_map(static fn (string $marker): string => '- `' . $marker . '`', $stableMarkers)),
        '',
        '## Generated Files',
        '',
        '- `schema-form-contract.json` keeps metadata, invariants, JSON Schema, UI Schema, and field mappings together.',
        '- `json-schema.json` is the consumer-facing JSON Schema draft 2020-12 artifact.',
        '- `ui-schema.json` is JSON Forms style ordering/control metadata that rjsf consumers may also inspect.',
        '',
        '## Editing Guidance',
        '',
        'Do not hand-edit generated schema-form probe files. Change canonical Mtool metadata or the generator, then regenerate the artifact.',
        '',
        'Contract version: ' . (string) ($contract['schema_form_contract_version'] ?? ''),
        'Probe version: ' . (string) ($contract['probe_version'] ?? ''),
        'Form screen: ' . (string) ($contract['form_screen_key'] ?? ''),
        'Action: ' . (string) ($contract['action_key'] ?? ''),
        '',
    ]);
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_no_code_runtime_readme_text(array $payload): string
{
    $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];

    return implode("\n", [
        '# No-Code Runtime Artifact',
        '',
        'Generated no-code screen definition and runtime preview from canonical Mtool metadata.',
        '',
        '- `screen-definition.json` is the machine-readable no-code screen definition.',
        '- `runtime-preview.json` is a fail-closed render preview for generated screens.',
        '- `runtime-preview.html` is a minimal HTML preview rendered from the same runtime model.',
        '- Do not hand-edit generated files; update canonical Mtool metadata instead.',
        '',
        'Contract count: ' . (int) ($summary['contract_count'] ?? 0),
        'Screen count: ' . (int) ($summary['screen_count'] ?? 0),
        'Preview count: ' . (int) ($summary['preview_count'] ?? 0),
        'Status: ' . ((bool) ($payload['ok'] ?? false) ? 'ok' : 'failed'),
        '',
    ]);
}

/**
 * @param array<string,mixed> $definitionResult
 * @return array<string,mixed>
 */
function app_project_output_no_code_runtime_payload(string $projectKey, array $definitionResult): array
{
    $screenDefinition = is_array($definitionResult['definition'] ?? null)
        ? $definitionResult['definition']
        : [];
    $preview = app_project_output_no_code_runtime_preview($screenDefinition, $projectKey);
    $screenCount = 0;
    foreach (($screenDefinition['contracts'] ?? []) as $contract) {
        if (is_array($contract) && is_array($contract['screens'] ?? null)) {
            $screenCount += count($contract['screens']);
        }
    }

    return [
        'ok' => (bool) ($definitionResult['ok'] ?? false) && (bool) ($preview['ok'] ?? false),
        'artifact_type' => 'no-code-runtime-json',
        'project_key' => app_normalize_project_key($projectKey),
        'manifest_version' => 'no-code-runtime-artifact-v0',
        'screen_definition' => $screenDefinition,
        'runtime_preview' => $preview,
        'summary' => [
            'contract_count' => count(is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : []),
            'screen_count' => $screenCount,
            'preview_count' => count(is_array($preview['screens'] ?? null) ? $preview['screens'] : []),
        ],
        'errors' => array_values(array_filter([
            (string) ($definitionResult['error'] ?? ''),
            (string) ($preview['error'] ?? ''),
        ])),
    ];
}

/**
 * @param array<string,mixed> $screenDefinition
 * @return array<string,mixed>
 */
function app_project_output_no_code_runtime_preview(array $screenDefinition, string $projectKey = ''): array
{
    $screens = [];
    $errors = [];
    $normalizedProjectKey = app_normalize_project_key($projectKey !== '' ? $projectKey : (string) ($screenDefinition['project_key'] ?? ''));
    foreach (($screenDefinition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $previewData = app_project_output_no_code_runtime_preview_data(
            $normalizedProjectKey,
            (string) ($contract['contract_key'] ?? ''),
        );

        foreach (($contract['screens'] ?? []) as $screen) {
            if (!is_array($screen)) {
                continue;
            }

            $screenKey = (string) ($screen['screen_key'] ?? '');
            if ($screenKey === '') {
                continue;
            }

            $renderResult = app_no_code_runtime_render_screen(
                $screenDefinition,
                $screenKey,
                $previewData['rows'],
                $previewData['current_item'],
            );
            if (!$renderResult['ok']) {
                $errors[] = $renderResult['error'];
                continue;
            }

            $screens[] = $renderResult['render'];
        }
    }

    return [
        'ok' => $errors === [],
        'runtime_version' => app_no_code_runtime_version(),
        'definition_version' => (string) ($screenDefinition['definition_version'] ?? ''),
        'project_key' => (string) ($screenDefinition['project_key'] ?? ''),
        'screens' => $screens,
        'error' => implode('; ', $errors),
    ];
}

/**
 * @return array{rows:list<array<string,mixed>>,current_item:array<string,mixed>}
 */
function app_project_output_no_code_runtime_preview_data(string $projectKey, string $contractKey): array
{
    if ($projectKey === 'SAMPLE28' && $contractKey === 'no_code_ticket') {
        $rows = [
            [
                'id' => 1001,
                'title' => 'First no-code app ticket',
                'status' => 'open',
                'priority' => 10,
                'body' => 'This row is the first sample28 data-first no-code app fixture.',
            ],
            [
                'id' => 1002,
                'title' => 'Review generated customer fields',
                'status' => 'triage',
                'priority' => 20,
                'body' => 'Confirm imported fields before exposing the generated no-code preview to operators.',
            ],
            [
                'id' => 1003,
                'title' => 'Prepare approval handoff',
                'status' => 'ready',
                'priority' => 30,
                'body' => 'Use the publish candidate workflow to review and approve the generated runtime.',
            ],
        ];

        return [
            'rows' => $rows,
            'current_item' => $rows[0],
        ];
    }

    if ($projectKey === 'SAMPLE18' && $contractKey === 'task_card') {
        $rows = [
            [
                'id' => 1801,
                'title' => 'Define first demo prompt',
                'body' => 'Turn the raw idea into a readable sample prompt and scope.',
                'status' => 'doing',
                'assigned_to' => 'Alice',
                'priority' => 30,
                'due_date' => '2026-06-19',
                'completed_at' => null,
                'updated_at' => '2026-06-19 09:00:00',
            ],
            [
                'id' => 1802,
                'title' => 'Create TaskCard metadata',
                'body' => 'Seed table, DataClass, DBAccess, HTML, and OpenAPI output definitions.',
                'status' => 'todo',
                'assigned_to' => 'Bob',
                'priority' => 20,
                'due_date' => '2026-06-20',
                'completed_at' => null,
                'updated_at' => '2026-06-19 09:30:00',
            ],
            [
                'id' => 1803,
                'title' => 'Publish reference outputs',
                'body' => 'Run the sample pack and capture actual generated outputs.',
                'status' => 'todo',
                'assigned_to' => 'Chris',
                'priority' => 10,
                'due_date' => '2026-06-21',
                'completed_at' => null,
                'updated_at' => '2026-06-19 10:00:00',
            ],
            [
                'id' => 1804,
                'title' => 'Review demo feedback notes',
                'body' => 'Record any runtime or generator gaps discovered while making the demo.',
                'status' => 'done',
                'assigned_to' => 'Dana',
                'priority' => 40,
                'due_date' => '2026-06-18',
                'completed_at' => '2026-06-19 08:30:00',
                'updated_at' => '2026-06-19 08:30:00',
            ],
        ];

        return [
            'rows' => $rows,
            'current_item' => $rows[0],
        ];
    }

    if ($projectKey === 'SAMPLE32' && $contractKey === 'no_code_lab_card') {
        $rows = [
            [
                'id' => 3201,
                'title' => 'Fixture list card',
                'status' => 'draft',
                'owner_name' => 'No Code Lab',
                'priority' => 10,
                'due_on' => '2026-07-20',
                'notes' => 'First fixture row for fast no-code UI contract checks.',
            ],
            [
                'id' => 3202,
                'title' => 'Fixture detail card',
                'status' => 'ready',
                'owner_name' => 'Contract Runner',
                'priority' => 20,
                'due_on' => '2026-07-21',
                'notes' => 'Second fixture row used by detail and form preview assertions.',
            ],
        ];

        return [
            'rows' => $rows,
            'current_item' => $rows[0],
        ];
    }

    return [
        'rows' => [],
        'current_item' => [],
    ];
}

/**
 * @param array{
 *     source_output_key:string,
 *     program_language:string,
 *     artifact_strategy:string,
 *     runtime_source_relative_path:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{relative_path:string,size:int}>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_no_code_runtime_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_no_code_runtime_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Unsupported no-code runtime artifact strategy.',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    $allowedProgramLanguages = $strategy === 'no-code-react-bridge'
        ? ['typescript', 'ts', 'json']
        : ['json'];
    if ($programLanguage !== '' && !in_array($programLanguage, $allowedProgramLanguages, true)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'no-code runtime artifact program_language is not supported.',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = match ($strategy) {
            'no-code-react-bridge' => app_project_output_no_code_react_bridge_default_runtime_source_relative_path(
                $projectKey,
                (string) ($definition['source_output_key'] ?? ''),
            ),
            'no-code-json-forms-probe' => app_project_output_no_code_json_forms_probe_default_runtime_source_relative_path(
                $projectKey,
                (string) ($definition['source_output_key'] ?? ''),
            ),
            default => app_project_output_no_code_runtime_default_runtime_source_relative_path(
                $projectKey,
                (string) ($definition['source_output_key'] ?? ''),
            ),
        };
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $definitionResult = app_no_code_screen_definition_from_project($app, $projectKey);
    if (!$definitionResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $definitionResult['error'],
        ];
    }

    $payload = app_project_output_no_code_runtime_payload($projectKey, $definitionResult);
    if (!$payload['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => implode(', ', $payload['errors']),
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $files = match ($strategy) {
        'no-code-react-bridge' => app_project_output_no_code_react_bridge_build_emitted_files($payload),
        'no-code-json-forms-probe' => app_project_output_no_code_json_forms_probe_build_emitted_files($payload),
        default => app_project_output_no_code_runtime_build_emitted_files($payload),
    };

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($files as $relativePath => $contents) {
            app_project_output_write_text_file($runtimeSourceRoot . '/' . $relativePath, $contents);
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'no-code runtime staging tree creation failed: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}
