<?php

declare(strict_types=1);

require_once __DIR__ . '/material_insight.php';
require_once __DIR__ . '/no_code_runtime.php';

/**
 * @param array<string,mixed> $artifact
 * @return array{ok:bool,screen_definition:array<string,mixed>,runtime_preview:array<string,mixed>,error:string}
 */
function app_material_insight_no_code_handoff(array $artifact): array
{
    $validation = app_material_insight_validate($artifact);
    if (!$validation['ok']) {
        return [
            'ok' => false,
            'screen_definition' => [],
            'runtime_preview' => [],
            'error' => 'material_insight_invalid:' . implode(',', $validation['errors']),
        ];
    }

    $definition = app_material_insight_no_code_screen_definition($artifact);
    $runtimePreview = app_material_insight_no_code_runtime_preview($definition, $artifact);

    return [
        'ok' => $runtimePreview['ok'],
        'screen_definition' => $definition,
        'runtime_preview' => $runtimePreview,
        'error' => (string) ($runtimePreview['error'] ?? ''),
    ];
}

/**
 * @param array<string,mixed> $artifact
 * @return array<string,mixed>
 */
function app_material_insight_no_code_screen_definition(array $artifact): array
{
    $contractKey = 'sample19_material_insight';
    $screens = [];
    foreach (is_array($artifact['ui_outline']['screens'] ?? null) ? $artifact['ui_outline']['screens'] : [] as $screen) {
        if (!is_array($screen)) {
            continue;
        }
        $screens[] = [
            'screen_key' => (string) ($screen['screen_key'] ?? ''),
            'screen_type' => 'list',
            'view_variant' => 'review_list',
            'contract_key' => $contractKey,
            'presentation_hint' => [
                'profile_key' => 'sample19_material_insight_review',
                'screen_type' => 'list',
                'density' => 'compact',
                'emphasis' => (string) ($screen['section'] ?? 'review'),
                'primary_fields' => is_array($screen['fields'] ?? null) ? array_values($screen['fields']) : [],
                'secondary_fields' => [],
                'field_groups' => [
                    [
                        'group_key' => (string) ($screen['section'] ?? 'review'),
                        'label' => app_no_code_runtime_human_label((string) ($screen['section'] ?? 'review')),
                        'fields' => is_array($screen['fields'] ?? null) ? array_values($screen['fields']) : [],
                    ],
                ],
            ],
            'extension_slots' => [],
            'fields' => app_material_insight_no_code_fields(
                is_array($screen['fields'] ?? null) ? $screen['fields'] : [],
            ),
            'actions' => [],
            'sync_status_hint' => false,
            'material_insight_traceability' => [
                'section' => (string) ($screen['section'] ?? ''),
                'purpose' => (string) ($screen['purpose'] ?? ''),
                'entity_refs' => is_array($screen['entity_refs'] ?? null) ? array_values($screen['entity_refs']) : [],
                'qa_refs' => is_array($screen['qa_refs'] ?? null) ? array_values($screen['qa_refs']) : [],
            ],
        ];
    }

    return [
        'definition_version' => app_no_code_screen_definition_version(),
        'project_key' => (string) ($artifact['project_key'] ?? 'SAMPLE19'),
        'contracts' => [
            [
                'contract_key' => $contractKey,
                'entity' => [
                    'entity_key' => $contractKey,
                    'label' => 'Sample19 Material Insight',
                ],
                'interface_usage' => [
                    'intent' => 'screen',
                    'source' => 'material_insight:ui_outline',
                    'allowed_intents' => ['screen'],
                    'presentation_layer' => 'view_variant',
                ],
                'view_variant_preference' => [
                    'variant' => 'review_list',
                    'source' => 'material_insight:ui_outline',
                    'allowed_variants' => ['review_list'],
                ],
                'presentation_profile' => [
                    'profile_key' => 'sample19_material_insight_review',
                    'source' => 'material_insight:ui_outline',
                    'density' => 'compact',
                    'emphasis' => 'review',
                    'primary_fields' => [],
                    'secondary_fields' => [],
                    'field_groups' => [],
                ],
                'extension_slots' => [],
                'custom_operations' => [],
                'storage_hint' => [
                    'sync_role' => 'none',
                    'app_persistence_role' => 'preview-only',
                    'sync_status_display' => false,
                ],
                'traceability' => app_material_insight_no_code_traceability($artifact),
                'screens' => $screens,
                'actions' => [],
            ],
        ],
    ];
}

/**
 * @param list<mixed> $fieldKeys
 * @return list<array<string,mixed>>
 */
function app_material_insight_no_code_fields(array $fieldKeys): array
{
    $fields = [];
    foreach ($fieldKeys as $fieldKey) {
        $key = (string) $fieldKey;
        if ($key === '') {
            continue;
        }
        $fields[] = [
            'field_key' => $key,
            'label' => app_no_code_runtime_human_label($key),
            'type' => $key === 'field_count' || $key === 'key_count' ? 'integer' : 'string',
            'is_key' => $key === 'entity_key' || $key === 'question_key',
            'required' => false,
            'readonly' => true,
            'visibility' => 'visible',
        ];
    }

    return $fields;
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,mixed> $artifact
 * @return array<string,mixed>
 */
function app_material_insight_no_code_runtime_preview(array $definition, array $artifact): array
{
    $screens = [];
    $errors = [];
    foreach (($definition['contracts'][0]['screens'] ?? []) as $screen) {
        if (!is_array($screen)) {
            continue;
        }
        $screenKey = (string) ($screen['screen_key'] ?? '');
        $rows = app_material_insight_no_code_rows_for_screen($artifact, $screenKey);
        $renderResult = app_no_code_runtime_render_screen(
            $definition,
            $screenKey,
            $rows,
            $rows[0] ?? [],
        );
        if (!$renderResult['ok']) {
            $errors[] = $renderResult['error'];
            continue;
        }
        $render = $renderResult['render'];
        $render['material_insight_traceability'] = is_array($screen['material_insight_traceability'] ?? null)
            ? $screen['material_insight_traceability']
            : [];
        $screens[] = $render;
    }

    return [
        'ok' => $errors === [],
        'runtime_version' => app_no_code_runtime_version(),
        'definition_version' => (string) ($definition['definition_version'] ?? ''),
        'project_key' => (string) ($definition['project_key'] ?? ''),
        'screens' => $screens,
        'material_insight_traceability' => app_material_insight_no_code_traceability($artifact),
        'error' => implode('; ', $errors),
    ];
}

/**
 * @param array<string,mixed> $artifact
 * @return list<array<string,mixed>>
 */
function app_material_insight_no_code_rows_for_screen(array $artifact, string $screenKey): array
{
    if ($screenKey === 'material_entity_list') {
        return array_map(
            static fn (array $entity): array => [
                'entity_key' => (string) ($entity['entity_key'] ?? ''),
                'purpose' => (string) ($entity['purpose'] ?? ''),
                'field_count' => (int) ($entity['field_count'] ?? 0),
                'relationship_keys' => implode(', ', is_array($entity['relationship_keys'] ?? null) ? $entity['relationship_keys'] : []),
            ],
            is_array($artifact['entities'] ?? null) ? $artifact['entities'] : [],
        );
    }

    if ($screenKey === 'material_qa_cards') {
        return array_map(
            static fn (array $card): array => [
                'question' => (string) ($card['question'] ?? ''),
                'answer' => (string) ($card['answer'] ?? ''),
                'evidence_refs' => implode(', ', is_array($card['evidence_refs'] ?? null) ? $card['evidence_refs'] : []),
            ],
            is_array($artifact['qa_cards'] ?? null) ? $artifact['qa_cards'] : [],
        );
    }

    return [];
}

/**
 * @param array<string,mixed> $artifact
 * @return array<string,mixed>
 */
function app_material_insight_no_code_traceability(array $artifact): array
{
    return [
        'source_version' => (string) ($artifact['version'] ?? ''),
        'project_key' => (string) ($artifact['project_key'] ?? ''),
        'source_sha256' => (string) ($artifact['source']['sha256'] ?? ''),
        'basis_kind' => (string) ($artifact['basis']['kind'] ?? ''),
        'proposal_id' => (string) ($artifact['basis']['proposal_id'] ?? ''),
        'canonical_snapshot_sha256' => (string) ($artifact['basis']['canonical_snapshot_sha256'] ?? ''),
        'prohibited_actions' => is_array($artifact['prohibited_actions'] ?? null) ? array_values($artifact['prohibited_actions']) : [],
    ];
}
