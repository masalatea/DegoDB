<?php

declare(strict_types=1);

require_once __DIR__ . '/no_code_screen_definition.php';
require_once __DIR__ . '/project_output_no_code_runtime_generator.php';

/**
 * @return array<string,mixed>
 */
function app_no_code_mtool_dogfooding_probe_manifest(): array
{
    return [
        'project_key' => 'MTOOL',
        'contracts' => [
            [
                'contract_key' => 'mtool_source_output_review',
                'entity' => [
                    'physical_name' => 'project_source_outputs',
                    'generated_name' => 'ProjectSourceOutput',
                    'label' => 'Mtool Source Output Review',
                ],
                'contract_metadata' => [
                    'status' => 'active',
                    'usage_intent' => 'internal',
                    'view_variant_preference' => 'review_list',
                    'sync_role' => 'server-copy',
                    'no_code_role' => 'managed-screen',
                    'app_persistence_role' => 'server-managed-copy',
                    'source_of_truth' => 'mtool-no-code-dogfooding-probe',
                    'presentation_profile' => [
                        'profile_key' => 'mtool_source_output_review_compact',
                        'density' => 'compact',
                        'emphasis' => 'review',
                        'primary_fields' => ['source_output_key', 'name', 'class_type'],
                        'secondary_fields' => ['artifact_strategy', 'target_binding_type', 'spec_visibility', 'source_output_dir'],
                        'field_groups' => [
                            [
                                'group_key' => 'identity',
                                'label' => 'Identity',
                                'fields' => ['source_output_key', 'name', 'class_type'],
                            ],
                            [
                                'group_key' => 'artifact',
                                'label' => 'Artifact',
                                'fields' => ['artifact_strategy', 'target_binding_type', 'spec_visibility', 'source_output_dir'],
                            ],
                        ],
                    ],
                    'custom_operations' => [
                        [
                            'operation_key' => 'review_source_output_artifact',
                            'label' => 'Review Artifact',
                            'category' => 'review_request',
                            'target' => 'artifact',
                            'side_effect_class' => 'external_handoff',
                            'availability' => 'available',
                            'policy_key' => 'source_output.review',
                            'csrf_required' => true,
                            'audit_event' => 'mtool.source_output.artifact_review_requested',
                            'adapter_handoff' => 'source_output_artifact_review',
                            'route_boundary' => [
                                'method' => 'POST',
                                'path' => '/projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact',
                                'response_shape' => 'html_redirect',
                                'auth_guard' => 'mtool_operator_admin',
                                'idempotency' => 'duplicate_safe',
                                'failure_modes' => ['unavailable', 'unauthorized', 'missing_csrf', 'missing_artifact', 'stale_artifact'],
                            ],
                            'intent' => 'Open the generated artifact review workflow.',
                            'unavailable_reason' => 'Review request route is available as plan-only; generated button execution remains separately gated.',
                        ],
                        [
                            'operation_key' => 'request_source_output_publish',
                            'label' => 'Request Publish',
                            'category' => 'publish',
                            'target' => 'source_output',
                            'side_effect_class' => 'approval_transition',
                            'availability' => 'deferred',
                            'policy_key' => 'source_output.publish_request',
                            'csrf_required' => true,
                            'audit_event' => 'mtool.source_output.publish_requested',
                            'adapter_handoff' => 'source_output_publish_request',
                            'route_boundary' => [
                                'method' => 'POST',
                                'path' => '/projects/{project_key}/source-outputs/{source_output_key}/operations/request-source-output-publish',
                                'response_shape' => 'html_redirect',
                                'auth_guard' => 'mtool_operator_admin',
                                'idempotency' => 'duplicate_safe',
                                'failure_modes' => ['unavailable', 'unauthorized', 'missing_csrf', 'missing_artifact', 'stale_artifact', 'duplicate_request'],
                            ],
                            'intent' => 'Prepare an approval request for the current generated artifact.',
                            'unavailable_reason' => 'Publish request execution is deferred until approval transition policy, CSRF, and audit boundaries are wired.',
                        ],
                    ],
                    'extension_slots' => [
                        [
                            'slot_key' => 'mtool_source_output_related_settings',
                            'slot_type' => 'related_settings_panel',
                            'label' => 'Related Settings',
                            'placement' => 'aside',
                            'renderer' => 'link_list',
                            'target' => 'shared_contract_settings',
                            'links' => [
                                [
                                    'label' => 'Shared Contracts',
                                    'target' => 'shared_contracts',
                                    'href' => '/projects/MTOOL/shared-contracts',
                                ],
                                [
                                    'label' => 'Source Outputs',
                                    'target' => 'source_outputs',
                                    'href' => '/projects/MTOOL/source-outputs',
                                ],
                            ],
                            'screen_types' => ['list', 'detail'],
                        ],
                        [
                            'slot_key' => 'mtool_source_output_artifact_status',
                            'slot_type' => 'artifact_status_panel',
                            'label' => 'Artifact Status',
                            'placement' => 'aside',
                            'renderer' => 'status_card',
                            'target' => 'source_output_artifact_status',
                            'status_items' => [
                                [
                                    'label' => 'Artifact Strategy',
                                    'value' => 'no-code-runtime',
                                    'state' => 'ok',
                                ],
                                [
                                    'label' => 'Target Binding',
                                    'value' => 'managed-screen',
                                    'state' => 'info',
                                ],
                                [
                                    'label' => 'Spec Visibility',
                                    'value' => 'internal-review',
                                    'state' => 'warning',
                                ],
                            ],
                            'screen_types' => ['list', 'detail'],
                        ],
                        [
                            'slot_key' => 'mtool_source_output_operator_actions',
                            'slot_type' => 'operator_actions_panel',
                            'label' => 'Operator Actions',
                            'placement' => 'footer',
                            'renderer' => 'action_panel',
                            'target' => 'source_output_operator_actions',
                            'action_items' => [
                                [
                                    'label' => 'Review Artifact',
                                    'action_key' => 'review_source_output_artifact',
                                    'operation_key' => 'review_source_output_artifact',
                                    'intent' => 'Open the generated artifact review workflow.',
                                    'state' => 'available',
                                    'unavailable_reason' => 'Review request route is available as plan-only; generated button execution remains separately gated.',
                                ],
                                [
                                    'label' => 'Request Publish',
                                    'action_key' => 'request_source_output_publish',
                                    'operation_key' => 'request_source_output_publish',
                                    'intent' => 'Prepare an approval request for the current generated artifact.',
                                    'state' => 'deferred',
                                    'unavailable_reason' => 'Publish request execution is deferred until approval transition policy, CSRF, and audit boundaries are wired.',
                                ],
                            ],
                            'screen_types' => ['detail'],
                        ],
                    ],
                ],
                'fields' => app_no_code_mtool_dogfooding_probe_source_output_fields(),
            ],
        ],
    ];
}

/**
 * @return list<array<string,mixed>>
 */
function app_no_code_mtool_dogfooding_probe_operations(): array
{
    return [
        [
            'project_key' => 'MTOOL',
            'operation_key' => 'review_mtool_source_output_profile',
            'contract_key' => 'mtool_source_output_review',
            'name' => 'Review Mtool Source Output Profile',
            'operation_type' => 'read',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.read',
            'required_roles' => ['viewer'],
            'required_scopes' => [],
            'required_claims' => [],
            'fields' => [
                [
                    'field_physical_name' => 'source_output_key',
                    'field_role' => 'key',
                    'is_required' => true,
                    'allow_client_write' => false,
                ],
            ],
        ],
    ];
}

/**
 * @param array<string,mixed>|null $principal
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_mtool_dogfooding_probe_screen_definition(?array $principal = null): array
{
    return app_no_code_screen_definition_from_snapshots(
        'MTOOL',
        app_no_code_mtool_dogfooding_probe_manifest(),
        app_no_code_mtool_dogfooding_probe_operations(),
        $principal,
    );
}

/**
 * @param array<string,mixed>|null $principal
 * @return array<string,mixed>
 */
function app_no_code_mtool_dogfooding_probe_inspection_summary(?array $principal = null): array
{
    $definitionResult = app_no_code_mtool_dogfooding_probe_screen_definition($principal);
    $payload = app_project_output_no_code_runtime_payload('MTOOL', $definitionResult);
    $files = app_project_output_no_code_runtime_build_emitted_files($payload);
    $screenDefinition = json_decode((string) ($files['screen-definition.json'] ?? ''), true);
    $runtimePreview = json_decode((string) ($files['runtime-preview.json'] ?? ''), true);

    if (!$payload['ok'] || !is_array($screenDefinition) || !is_array($runtimePreview)) {
        return [
            'ok' => false,
            'errors' => array_values(array_filter([
                (string) ($definitionResult['error'] ?? ''),
                implode(', ', is_array($payload['errors'] ?? null) ? $payload['errors'] : []),
                is_array($screenDefinition) ? '' : 'screen-definition.json decode failed',
                is_array($runtimePreview) ? '' : 'runtime-preview.json decode failed',
            ])),
        ];
    }

    $contract = is_array($screenDefinition['contracts'][0] ?? null) ? $screenDefinition['contracts'][0] : [];
    $customOperations = is_array($contract['custom_operations'] ?? null) ? $contract['custom_operations'] : [];
    $screens = [];
    foreach (($runtimePreview['screens'] ?? []) as $screen) {
        if (!is_array($screen)) {
            continue;
        }

        $screens[] = [
            'screen_key' => (string) ($screen['screen_key'] ?? ''),
            'screen_type' => (string) ($screen['screen_type'] ?? ''),
            'presentation_density' => (string) ($screen['presentation_hint']['density'] ?? ''),
            'extension_slot_types' => array_column(
                is_array($screen['extension_slots'] ?? null) ? $screen['extension_slots'] : [],
                'slot_type',
            ),
            'custom_operation_keys' => array_column(
                is_array($screen['custom_operations'] ?? null) ? $screen['custom_operations'] : [],
                'operation_key',
            ),
        ];
    }

    return [
        'ok' => true,
        'artifact_files' => array_keys($files),
        'contract_key' => (string) ($contract['contract_key'] ?? ''),
        'interface_usage_intent' => (string) ($contract['interface_usage']['intent'] ?? ''),
        'view_variant_preference' => (string) ($contract['view_variant_preference']['variant'] ?? ''),
        'presentation_profile_key' => (string) ($contract['presentation_profile']['profile_key'] ?? ''),
        'presentation_density' => (string) ($contract['presentation_profile']['density'] ?? ''),
        'custom_operation_keys' => array_column($customOperations, 'operation_key'),
        'custom_operation_categories' => array_values(array_unique(array_map(
            static fn (array $operation): string => (string) ($operation['category'] ?? ''),
            $customOperations,
        ))),
        'custom_operation_side_effect_classes' => array_values(array_unique(array_map(
            static fn (array $operation): string => (string) ($operation['side_effect_class'] ?? ''),
            $customOperations,
        ))),
        'custom_operation_availability' => array_values(array_unique(array_map(
            static fn (array $operation): string => (string) ($operation['availability'] ?? ''),
            $customOperations,
        ))),
        'custom_operation_availability_states' => array_values(array_unique(array_map(
            static fn (array $operation): string => (string) ($operation['availability_read_model']['availability_state'] ?? ''),
            $customOperations,
        ))),
        'custom_operation_execution_modes' => array_values(array_unique(array_map(
            static fn (array $operation): string => (string) ($operation['availability_read_model']['execution_mode'] ?? ''),
            $customOperations,
        ))),
        'custom_operation_unavailable_reasons' => array_column($customOperations, 'unavailable_reason'),
        'custom_operation_adapter_handoffs' => array_column($customOperations, 'adapter_handoff'),
        'custom_operation_route_boundaries' => array_column($customOperations, 'route_boundary'),
        'extension_slot_types' => array_column(
            is_array($contract['extension_slots'] ?? null) ? $contract['extension_slots'] : [],
            'slot_type',
        ),
        'runtime_version' => (string) ($runtimePreview['runtime_version'] ?? ''),
        'screens' => $screens,
        'html_boundary' => app_no_code_mtool_dogfooding_probe_html_boundary((string) ($files['runtime-preview.html'] ?? '')),
        'errors' => [],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_no_code_mtool_dogfooding_probe_html_boundary(string $html): array
{
    return [
        'custom_slot_rendering' => str_contains($html, 'data-extension-slot') ? 'visible_placeholder' : 'metadata_only',
        'contains_runtime_preview_json' => str_contains($html, 'no-code-runtime-preview-data'),
        'contains_slot_region_markup' => str_contains($html, 'data-extension-slot'),
        'contains_related_settings_slot' => str_contains($html, 'data-extension-slot="mtool_source_output_related_settings"'),
        'contains_related_settings_link_list' => str_contains($html, 'data-extension-slot-link="shared_contracts"'),
        'contains_artifact_status_slot' => str_contains($html, 'data-extension-slot="mtool_source_output_artifact_status"'),
        'contains_artifact_status_card' => str_contains($html, 'data-extension-slot-status-item="Artifact Strategy"'),
        'contains_operator_actions_slot' => str_contains($html, 'data-extension-slot="mtool_source_output_operator_actions"'),
        'contains_operator_action_panel' => str_contains($html, 'data-extension-slot-action="review_source_output_artifact"'),
        'contains_custom_operation_binding' => str_contains($html, 'data-extension-slot-operation="review_source_output_artifact"'),
        'contains_custom_operation_unavailable_reason' => str_contains($html, 'data-extension-slot-unavailable-reason="review_source_output_artifact"'),
        'contains_custom_operation_route_boundary' => str_contains($html, 'data-extension-slot-route-boundary="review_source_output_artifact"'),
    ];
}

/**
 * @return list<array<string,mixed>>
 */
function app_no_code_mtool_dogfooding_probe_source_output_fields(): array
{
    return [
        app_no_code_mtool_dogfooding_probe_field('source_output_key', 'Source Output Key', 'string', true, false),
        app_no_code_mtool_dogfooding_probe_field('name', 'Name', 'string', false, false),
        app_no_code_mtool_dogfooding_probe_field('class_type', 'Class Type', 'string', false, false),
        app_no_code_mtool_dogfooding_probe_field('artifact_strategy', 'Artifact Strategy', 'string', false, false),
        app_no_code_mtool_dogfooding_probe_field('target_binding_type', 'Target Binding Type', 'string', false, true),
        app_no_code_mtool_dogfooding_probe_field('spec_visibility', 'Spec Visibility', 'string', false, false),
        app_no_code_mtool_dogfooding_probe_field('source_output_dir', 'Source Output Directory', 'string', false, false),
    ];
}

/**
 * @return array<string,mixed>
 */
function app_no_code_mtool_dogfooding_probe_field(
    string $physicalName,
    string $label,
    string $type,
    bool $isKey,
    bool $nullable,
): array {
    return [
        'physical_name' => $physicalName,
        'generated_name' => app_no_code_mtool_dogfooding_probe_generated_name($physicalName),
        'label' => $label,
        'type' => $type,
        'is_key' => $isKey,
        'nullable' => $nullable,
        'default' => null,
        'storage_role' => 'business',
        'contract_metadata' => [
            'operation_role' => $isKey ? 'key' : 'read-only',
            'no_code_role' => $isKey ? 'identifier' : 'field',
            'source_of_truth' => 'mtool-no-code-dogfooding-probe',
        ],
    ];
}

function app_no_code_mtool_dogfooding_probe_generated_name(string $physicalName): string
{
    $parts = array_filter(explode('_', $physicalName), static fn (string $part): bool => $part !== '');
    $generated = '';
    foreach ($parts as $part) {
        $generated .= ucfirst($part);
    }

    return $generated;
}
