<?php

declare(strict_types=1);

require_once __DIR__ . '/managed_operation_policy.php';
require_once __DIR__ . '/managed_operation_repository_pdo.php';
require_once __DIR__ . '/shared_contract_manifest.php';

function app_no_code_screen_definition_version(): string
{
    return 'no-code-screen-definition-v0';
}

/**
 * @param array<string,mixed>|null $principal
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_screen_definition_from_project(
    array $app,
    string $projectKey,
    ?array $principal = null,
): array
{
    $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
    if (!$manifestResult['ok']) {
        return app_no_code_screen_definition_error($manifestResult['error']);
    }

    $operationSnapshot = app_pdo_fetch_managed_operation_snapshot($app, $projectKey);
    if (!$operationSnapshot['ok']) {
        return app_no_code_screen_definition_error($operationSnapshot['error']);
    }

    return app_no_code_screen_definition_from_snapshots(
        $projectKey,
        $manifestResult['manifest'],
        $operationSnapshot['items'],
        $principal,
    );
}

/**
 * @param array<string,mixed> $manifest
 * @param list<array<string,mixed>> $operations
 * @param array<string,mixed>|null $principal
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_screen_definition_from_snapshots(
    string $projectKey,
    array $manifest,
    array $operations,
    ?array $principal = null,
): array
{
    $contracts = app_no_code_screen_definition_managed_contracts($manifest);
    if ($contracts === []) {
        return app_no_code_screen_definition_error('managed-screen contract がありません。');
    }

    $operationsByContract = app_no_code_screen_definition_operations_by_contract($operations);
    $contractDefinitions = [];
    foreach ($contracts as $contract) {
        $contract = app_no_code_screen_definition_with_project_metadata($projectKey, $contract);
        $contractKey = (string) ($contract['contract_key'] ?? '');
        $contractOperations = $operationsByContract[$contractKey] ?? [];
        $contractDefinitions[] = app_no_code_screen_definition_contract_definition(
            $contract,
            $contractOperations,
            $principal,
        );
    }

    return [
        'ok' => true,
        'definition' => [
            'definition_version' => app_no_code_screen_definition_version(),
            'project_key' => $projectKey,
            'contracts' => $contractDefinitions,
        ],
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $contract
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_with_project_metadata(string $projectKey, array $contract): array
{
    if ($projectKey !== 'SAMPLE18' || (string) ($contract['contract_key'] ?? '') !== 'task_card') {
        return $contract;
    }

    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
    $metadata['custom_operations'] = array_values(array_merge(
        is_array($metadata['custom_operations'] ?? null) ? $metadata['custom_operations'] : [],
        app_no_code_screen_definition_sample18_task_card_custom_operations(),
    ));
    $metadata['extension_slots'] = array_values(array_merge(
        is_array($metadata['extension_slots'] ?? null) ? $metadata['extension_slots'] : [],
        [
            [
                'slot_key' => 'sample18_task_card_dry_run_actions',
                'slot_type' => 'operator_actions_panel',
                'label' => 'Task Actions',
                'placement' => 'footer',
                'renderer' => 'action_panel',
                'target' => 'sample18_task_card_actions',
                'action_items' => app_no_code_screen_definition_sample18_task_card_action_items(),
                'screen_types' => ['detail'],
            ],
        ],
    ));
    $contract['contract_metadata'] = $metadata;

    return $contract;
}

/**
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_sample18_task_card_custom_operations(): array
{
    $definitions = [
        ['create_task_card', 'Create Task', 'Create a task card through the curated sample18 POST route.', ['validation_error']],
        ['update_task_card', 'Update Task', 'Update an existing task card through the curated sample18 POST route.', ['missing_record', 'validation_error']],
        ['complete_task_card', 'Complete Task', 'Mark an existing task card done through the curated sample18 POST route.', ['missing_record']],
        ['reopen_task_card', 'Reopen Task', 'Move an existing task card back to todo through the curated sample18 POST route.', ['missing_record']],
        ['delete_task_card', 'Delete Task', 'Delete an existing task card through the curated sample18 POST route.', ['missing_record']],
    ];

    $operations = [];
    foreach ($definitions as [$operationKey, $label, $intent, $extraFailures]) {
        $operations[] = [
            'operation_key' => $operationKey,
            'label' => $label,
            'category' => 'custom',
            'target' => 'shared_contract',
            'side_effect_class' => 'direct_mutation',
            'availability' => 'disabled',
            'policy_key' => 'project.edit',
            'csrf_required' => true,
            'audit_event' => 'sample18.task_card.dry_run_action',
            'adapter_handoff' => 'sample18_task_card_curated_route',
            'route_boundary' => [
                'method' => 'POST',
                'path' => '/samples/sample18-task-board',
                'response_shape' => 'html_redirect',
                'auth_guard' => 'web_lab_login',
                'idempotency' => 'not_idempotent',
                'failure_modes' => array_values(array_unique(array_merge(
                    ['unavailable', 'unauthorized', 'missing_csrf'],
                    $extraFailures,
                ))),
            ],
            'intent' => $intent,
            'unavailable_reason' => 'Generated no-code runtime records this as a dry-run boundary only; the curated sample18 page still owns mutation.',
        ];
    }

    return $operations;
}

/**
 * @return list<array<string,string>>
 */
function app_no_code_screen_definition_sample18_task_card_action_items(): array
{
    return [
        [
            'label' => 'Create Task',
            'action_key' => 'create_task_card',
            'operation_key' => 'create_task_card',
            'intent' => 'Dry-run create action metadata for the existing task board route.',
            'state' => 'blocked',
            'unavailable_reason' => 'Generated no-code execution is disabled until sample18 mutation handoff is designed.',
        ],
        [
            'label' => 'Update Task',
            'action_key' => 'update_task_card',
            'operation_key' => 'update_task_card',
            'intent' => 'Dry-run update action metadata for the existing task board route.',
            'state' => 'blocked',
            'unavailable_reason' => 'Generated no-code execution is disabled until sample18 mutation handoff is designed.',
        ],
        [
            'label' => 'Complete Task',
            'action_key' => 'complete_task_card',
            'operation_key' => 'complete_task_card',
            'intent' => 'Dry-run complete action metadata for the existing task board route.',
            'state' => 'blocked',
            'unavailable_reason' => 'Generated no-code execution is disabled until sample18 mutation handoff is designed.',
        ],
        [
            'label' => 'Reopen Task',
            'action_key' => 'reopen_task_card',
            'operation_key' => 'reopen_task_card',
            'intent' => 'Dry-run reopen action metadata for the existing task board route.',
            'state' => 'blocked',
            'unavailable_reason' => 'Generated no-code execution is disabled until sample18 mutation handoff is designed.',
        ],
        [
            'label' => 'Delete Task',
            'action_key' => 'delete_task_card',
            'operation_key' => 'delete_task_card',
            'intent' => 'Dry-run delete action metadata for the existing task board route.',
            'state' => 'blocked',
            'unavailable_reason' => 'Generated no-code execution is disabled until sample18 mutation handoff is designed.',
        ],
    ];
}

/**
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_screen_definition_error(string $error): array
{
    return [
        'ok' => false,
        'definition' => [],
        'error' => $error,
    ];
}

/**
 * @param array<string,mixed> $manifest
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_managed_contracts(array $manifest): array
{
    $contracts = [];
    foreach (($manifest['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
        if ((string) ($metadata['status'] ?? 'active') !== 'active') {
            continue;
        }
        if ((string) ($metadata['no_code_role'] ?? '') !== 'managed-screen') {
            continue;
        }

        $contracts[] = $contract;
    }

    return $contracts;
}

/**
 * @param list<array<string,mixed>> $operations
 * @return array<string,list<array<string,mixed>>>
 */
function app_no_code_screen_definition_operations_by_contract(array $operations): array
{
    $grouped = [];
    foreach ($operations as $operation) {
        $contractKey = (string) ($operation['contract_key'] ?? '');
        if ($contractKey === '') {
            continue;
        }

        $grouped[$contractKey] ??= [];
        $grouped[$contractKey][] = $operation;
    }

    foreach ($grouped as $contractKey => $items) {
        usort(
            $items,
            static fn (array $left, array $right): int => strcmp(
                (string) ($left['operation_key'] ?? ''),
                (string) ($right['operation_key'] ?? ''),
            ),
        );
        $grouped[$contractKey] = $items;
    }

    return $grouped;
}

/**
 * @param array<string,mixed> $contract
 * @param list<array<string,mixed>> $operations
 * @param array<string,mixed>|null $principal
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_contract_definition(
    array $contract,
    array $operations,
    ?array $principal,
): array {
    $contractKey = (string) ($contract['contract_key'] ?? '');
    $fields = app_no_code_screen_definition_fields($contract);
    $actions = app_no_code_screen_definition_actions($contract, $operations, $principal);
    $storageHint = app_no_code_screen_definition_storage_hint($contract);
    $syncStatusDisplay = (bool) ($storageHint['sync_status_display'] ?? false);
    $customOperations = app_no_code_screen_definition_custom_operations($contract);
    $presentationProfile = app_no_code_screen_definition_presentation_profile($contract, $fields);
    $extensionSlots = app_no_code_screen_definition_extension_slots($contract, $customOperations);

    return [
        'contract_key' => $contractKey,
        'entity' => $contract['entity'] ?? [],
        'interface_usage' => app_no_code_screen_definition_interface_usage($contract),
        'view_variant_preference' => app_no_code_screen_definition_view_variant_preference($contract),
        'presentation_profile' => $presentationProfile,
        'extension_slots' => $extensionSlots,
        'custom_operations' => $customOperations,
        'storage_hint' => $storageHint,
        'traceability' => app_no_code_screen_definition_traceability($contractKey, $fields, $actions),
        'screens' => [
            app_no_code_screen_definition_list_screen($contractKey, $fields, $actions, $syncStatusDisplay, $presentationProfile, $extensionSlots),
            app_no_code_screen_definition_detail_screen($contractKey, $fields, $actions, $syncStatusDisplay, $presentationProfile, $extensionSlots),
            app_no_code_screen_definition_form_screen($contractKey, $fields, $actions, $presentationProfile, $extensionSlots),
        ],
        'actions' => $actions,
    ];
}

/**
 * @param array<string,mixed> $contract
 * @param list<array<string,mixed>> $fields
 * @return array{
 *     profile_key:string,
 *     source:string,
 *     density:string,
 *     emphasis:string,
 *     primary_fields:list<string>,
 *     secondary_fields:list<string>,
 *     field_groups:list<array{group_key:string,label:string,fields:list<string>}>
 * }
 */
function app_no_code_screen_definition_presentation_profile(array $contract, array $fields): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
    $profile = is_array($metadata['presentation_profile'] ?? null) ? $metadata['presentation_profile'] : [];
    $fieldKeys = app_no_code_screen_definition_field_keys($fields);
    $contractKey = (string) ($contract['contract_key'] ?? 'contract');
    $profileKey = trim((string) ($profile['profile_key'] ?? ''));
    $density = app_no_code_screen_definition_normalize_presentation_density((string) ($profile['density'] ?? ''));
    $emphasis = app_no_code_screen_definition_normalize_presentation_emphasis((string) ($profile['emphasis'] ?? ''));

    return [
        'profile_key' => $profileKey !== '' ? $profileKey : $contractKey . '_auto',
        'source' => $profile === [] ? 'derived:default' : 'presentation_profile:explicit',
        'density' => $density !== '' ? $density : 'standard',
        'emphasis' => $emphasis !== '' ? $emphasis : 'balanced',
        'primary_fields' => app_no_code_screen_definition_normalize_field_key_list($profile['primary_fields'] ?? [], $fieldKeys),
        'secondary_fields' => app_no_code_screen_definition_normalize_field_key_list($profile['secondary_fields'] ?? [], $fieldKeys),
        'field_groups' => app_no_code_screen_definition_normalize_field_groups($profile['field_groups'] ?? [], $fieldKeys),
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @return list<string>
 */
function app_no_code_screen_definition_field_keys(array $fields): array
{
    $fieldKeys = [];
    foreach ($fields as $field) {
        $fieldKey = (string) ($field['field_key'] ?? '');
        if ($fieldKey !== '') {
            $fieldKeys[] = $fieldKey;
        }
    }

    return $fieldKeys;
}

function app_no_code_screen_definition_normalize_presentation_density(string $density): string
{
    $normalized = strtolower(trim($density));
    return in_array($normalized, ['compact', 'standard', 'comfortable'], true) ? $normalized : '';
}

function app_no_code_screen_definition_normalize_presentation_emphasis(string $emphasis): string
{
    $normalized = strtolower(trim($emphasis));
    return in_array($normalized, ['balanced', 'review', 'data_entry'], true) ? $normalized : '';
}

/**
 * @param mixed $value
 * @param list<string> $allowedFieldKeys
 * @return list<string>
 */
function app_no_code_screen_definition_normalize_field_key_list(mixed $value, array $allowedFieldKeys): array
{
    if (!is_array($value)) {
        return [];
    }

    $normalized = [];
    foreach ($value as $fieldKey) {
        $fieldKey = (string) $fieldKey;
        if ($fieldKey !== '' && in_array($fieldKey, $allowedFieldKeys, true) && !in_array($fieldKey, $normalized, true)) {
            $normalized[] = $fieldKey;
        }
    }

    return $normalized;
}

/**
 * @param mixed $value
 * @param list<string> $allowedFieldKeys
 * @return list<array{group_key:string,label:string,fields:list<string>}>
 */
function app_no_code_screen_definition_normalize_field_groups(mixed $value, array $allowedFieldKeys): array
{
    if (!is_array($value)) {
        return [];
    }

    $groups = [];
    foreach ($value as $group) {
        if (!is_array($group)) {
            continue;
        }

        $groupKey = trim((string) ($group['group_key'] ?? ''));
        $fields = app_no_code_screen_definition_normalize_field_key_list($group['fields'] ?? [], $allowedFieldKeys);
        if ($groupKey === '' || $fields === []) {
            continue;
        }

        $groups[] = [
            'group_key' => $groupKey,
            'label' => trim((string) ($group['label'] ?? '')) !== '' ? trim((string) ($group['label'] ?? '')) : $groupKey,
            'fields' => $fields,
        ];
    }

    return $groups;
}

/**
 * @param array<string,mixed> $contract
 * @return list<array{
 *     slot_key:string,
 *     slot_type:string,
 *     label:string,
 *     placement:string,
 *     renderer:string,
 *     target:string,
 *     links:list<array{label:string,target:string,href:string}>,
 *     status_items:list<array{label:string,value:string,state:string}>,
 *     action_items:list<array{label:string,action_key:string,operation_key:string,intent:string,state:string,unavailable_reason:string,availability_read_model:array<string,mixed>}>,
 *     screen_types:list<string>,
 *     source:string
 * }>
 */
function app_no_code_screen_definition_extension_slots(array $contract, array $customOperations = []): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
    $slots = is_array($metadata['extension_slots'] ?? null) ? $metadata['extension_slots'] : [];
    $customOperationsByKey = [];
    foreach ($customOperations as $operation) {
        if (!is_array($operation)) {
            continue;
        }

        $operationKey = trim((string) ($operation['operation_key'] ?? ''));
        if ($operationKey !== '') {
            $customOperationsByKey[$operationKey] = $operation;
        }
    }

    $normalized = [];
    foreach ($slots as $slot) {
        if (!is_array($slot)) {
            continue;
        }

        $slotKey = trim((string) ($slot['slot_key'] ?? ''));
        $slotType = app_no_code_screen_definition_normalize_extension_slot_type((string) ($slot['slot_type'] ?? ''));
        $screenTypes = app_no_code_screen_definition_normalize_screen_types($slot['screen_types'] ?? []);
        if ($slotKey === '' || $slotType === '' || $screenTypes === []) {
            continue;
        }

        $normalized[] = [
            'slot_key' => $slotKey,
            'slot_type' => $slotType,
            'label' => trim((string) ($slot['label'] ?? '')) !== '' ? trim((string) ($slot['label'] ?? '')) : $slotKey,
            'placement' => app_no_code_screen_definition_normalize_extension_slot_placement((string) ($slot['placement'] ?? '')),
            'renderer' => app_no_code_screen_definition_normalize_extension_slot_renderer((string) ($slot['renderer'] ?? '')),
            'target' => trim((string) ($slot['target'] ?? '')),
            'links' => app_no_code_screen_definition_normalize_extension_slot_links($slot['links'] ?? []),
            'status_items' => app_no_code_screen_definition_normalize_extension_slot_status_items($slot['status_items'] ?? []),
            'action_items' => app_no_code_screen_definition_normalize_extension_slot_action_items($slot['action_items'] ?? [], $customOperationsByKey),
            'screen_types' => $screenTypes,
            'source' => 'extension_slots:explicit',
        ];
    }

    return $normalized;
}

function app_no_code_screen_definition_normalize_extension_slot_type(string $slotType): string
{
    $normalized = strtolower(trim($slotType));
    return in_array(
        $normalized,
        ['related_settings_panel', 'artifact_status_panel', 'operator_actions_panel'],
        true,
    ) ? $normalized : '';
}

function app_no_code_screen_definition_normalize_extension_slot_placement(string $placement): string
{
    $normalized = strtolower(trim($placement));
    return in_array($normalized, ['aside', 'header', 'footer', 'inline'], true) ? $normalized : 'aside';
}

function app_no_code_screen_definition_normalize_extension_slot_renderer(string $renderer): string
{
    $normalized = strtolower(trim($renderer));
    return in_array($normalized, ['placeholder', 'link_list', 'status_card', 'action_panel'], true) ? $normalized : 'placeholder';
}

/**
 * @param mixed $links
 * @return list<array{label:string,target:string,href:string}>
 */
function app_no_code_screen_definition_normalize_extension_slot_links(mixed $links): array
{
    if (!is_array($links)) {
        return [];
    }

    $normalized = [];
    foreach ($links as $link) {
        if (!is_array($link)) {
            continue;
        }

        $label = trim((string) ($link['label'] ?? ''));
        $target = trim((string) ($link['target'] ?? ''));
        $href = trim((string) ($link['href'] ?? ''));
        if ($label === '' || $href === '') {
            continue;
        }

        $normalized[] = [
            'label' => $label,
            'target' => $target,
            'href' => $href,
        ];
    }

    return $normalized;
}

/**
 * @param mixed $items
 * @return list<array{label:string,value:string,state:string}>
 */
function app_no_code_screen_definition_normalize_extension_slot_status_items(mixed $items): array
{
    if (!is_array($items)) {
        return [];
    }

    $normalized = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $label = trim((string) ($item['label'] ?? ''));
        $value = trim((string) ($item['value'] ?? ''));
        if ($label === '' || $value === '') {
            continue;
        }

        $normalized[] = [
            'label' => $label,
            'value' => $value,
            'state' => app_no_code_screen_definition_normalize_extension_slot_status_state((string) ($item['state'] ?? 'info')),
        ];
    }

    return $normalized;
}

function app_no_code_screen_definition_normalize_extension_slot_status_state(string $state): string
{
    $normalized = strtolower(trim($state));
    return in_array($normalized, ['ok', 'info', 'warning', 'blocked'], true) ? $normalized : 'info';
}

/**
 * @param mixed $items
 * @param array<string,array<string,mixed>> $customOperationsByKey
 * @return list<array{label:string,action_key:string,operation_key:string,intent:string,state:string,unavailable_reason:string,availability_read_model:array<string,mixed>,route_boundary:array{method:string,path:string,response_shape:string,auth_guard:string,idempotency:string,failure_modes:list<string>}}>
 */
function app_no_code_screen_definition_normalize_extension_slot_action_items(mixed $items, array $customOperationsByKey = []): array
{
    if (!is_array($items)) {
        return [];
    }

    $normalized = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $label = trim((string) ($item['label'] ?? ''));
        $actionKey = trim((string) ($item['action_key'] ?? ''));
        $operationKey = trim((string) ($item['operation_key'] ?? $actionKey));
        if ($label === '' || $actionKey === '') {
            continue;
        }

        $effectiveOperationKey = $operationKey !== '' ? $operationKey : $actionKey;
        $operation = $customOperationsByKey[$effectiveOperationKey] ?? [];
        $routeBoundary = is_array($operation)
            ? app_no_code_screen_definition_custom_operation_route_boundary($operation['route_boundary'] ?? null)
            : app_no_code_screen_definition_custom_operation_route_boundary(null);
        $availabilityReadModel = is_array($operation)
            ? app_no_code_screen_definition_custom_operation_availability_read_model($operation)
            : app_no_code_screen_definition_custom_operation_availability_read_model([
                'operation_key' => $effectiveOperationKey,
                'availability' => 'deferred',
                'unavailable_reason' => trim((string) ($item['unavailable_reason'] ?? '')),
            ]);

        $normalized[] = [
            'label' => $label,
            'action_key' => $actionKey,
            'operation_key' => $effectiveOperationKey,
            'intent' => trim((string) ($item['intent'] ?? '')),
            'state' => app_no_code_screen_definition_normalize_extension_slot_action_state((string) ($item['state'] ?? 'deferred')),
            'unavailable_reason' => trim((string) ($item['unavailable_reason'] ?? '')),
            'availability_read_model' => $availabilityReadModel,
            'route_boundary' => $routeBoundary,
        ];
    }

    return $normalized;
}

function app_no_code_screen_definition_normalize_extension_slot_action_state(string $state): string
{
    $normalized = strtolower(trim($state));
    return in_array($normalized, ['available', 'deferred', 'blocked'], true) ? $normalized : 'deferred';
}

/**
 * @param array<string,mixed> $contract
 * @return list<array{
 *     operation_key:string,
 *     label:string,
 *     category:string,
 *     target:string,
 *     side_effect_class:string,
 *     availability:string,
 *     policy_key:string,
 *     csrf_required:bool,
 *     audit_event:string,
 *     adapter_handoff:string,
 *     route_boundary:array{method:string,path:string,response_shape:string,auth_guard:string,idempotency:string,failure_modes:list<string>},
 *     availability_read_model:array<string,mixed>,
 *     intent:string,
 *     unavailable_reason:string
 * }>
 */
function app_no_code_screen_definition_custom_operations(array $contract): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
    $operations = is_array($metadata['custom_operations'] ?? null) ? $metadata['custom_operations'] : [];
    $normalized = [];
    foreach ($operations as $operation) {
        if (!is_array($operation)) {
            continue;
        }

        $operationKey = trim((string) ($operation['operation_key'] ?? ''));
        $label = trim((string) ($operation['label'] ?? ''));
        if ($operationKey === '' || $label === '') {
            continue;
        }

        $normalizedOperation = [
            'operation_key' => $operationKey,
            'label' => $label,
            'category' => app_no_code_screen_definition_normalize_custom_operation_category((string) ($operation['category'] ?? 'custom')),
            'target' => app_no_code_screen_definition_normalize_custom_operation_target((string) ($operation['target'] ?? '')),
            'side_effect_class' => app_no_code_screen_definition_normalize_custom_operation_side_effect_class((string) ($operation['side_effect_class'] ?? 'external_handoff')),
            'availability' => app_no_code_screen_definition_normalize_custom_operation_availability((string) ($operation['availability'] ?? 'deferred')),
            'policy_key' => trim((string) ($operation['policy_key'] ?? '')),
            'csrf_required' => (bool) ($operation['csrf_required'] ?? true),
            'audit_event' => trim((string) ($operation['audit_event'] ?? '')),
            'adapter_handoff' => trim((string) ($operation['adapter_handoff'] ?? '')),
            'route_boundary' => app_no_code_screen_definition_custom_operation_route_boundary($operation['route_boundary'] ?? null),
            'intent' => trim((string) ($operation['intent'] ?? '')),
            'unavailable_reason' => trim((string) ($operation['unavailable_reason'] ?? '')),
        ];
        $normalizedOperation['availability_read_model'] = app_no_code_screen_definition_custom_operation_availability_read_model($normalizedOperation);
        $normalized[] = $normalizedOperation;
    }

    return $normalized;
}

/**
 * @param array<string,mixed> $operation
 * @return array{
 *     operation_key:string,
 *     operation_availability:string,
 *     availability_state:string,
 *     preflight_result:string,
 *     availability_reason:string,
 *     execution_mode:string,
 *     route_boundary:array{method:string,path:string,response_shape:string,auth_guard:string,idempotency:string,failure_modes:list<string>},
 *     generated_button_enabled:bool
 * }
 */
function app_no_code_screen_definition_custom_operation_availability_read_model(array $operation): array
{
    $operationAvailability = app_no_code_screen_definition_normalize_custom_operation_availability(
        (string) ($operation['availability'] ?? 'deferred'),
    );
    $availabilityState = app_no_code_screen_definition_operation_availability_state($operationAvailability);
    $preflightResult = $operationAvailability === 'available' ? 'not_evaluated' : 'blocked';
    $executionMode = $operationAvailability === 'available' ? 'plan-only' : 'metadata-only';
    $reason = trim((string) ($operation['unavailable_reason'] ?? ''));
    if ($reason === '') {
        $reason = match ($availabilityState) {
            'plan_only_ready' => 'Operation metadata is available, but generated button execution remains separately gated.',
            'blocked' => 'Operation is blocked by metadata.',
            'unavailable' => 'Operation is unavailable by metadata.',
            default => 'Operation availability is deferred.',
        };
    }

    return [
        'operation_key' => trim((string) ($operation['operation_key'] ?? '')),
        'operation_availability' => $operationAvailability,
        'availability_state' => $availabilityState,
        'preflight_result' => $preflightResult,
        'availability_reason' => $reason,
        'execution_mode' => $executionMode,
        'route_boundary' => app_no_code_screen_definition_custom_operation_route_boundary($operation['route_boundary'] ?? null),
        'generated_button_enabled' => false,
    ];
}

function app_no_code_screen_definition_operation_availability_state(string $operationAvailability): string
{
    return match (app_no_code_screen_definition_normalize_custom_operation_availability($operationAvailability)) {
        'available' => 'plan_only_ready',
        'blocked' => 'blocked',
        'disabled' => 'unavailable',
        default => 'deferred',
    };
}

/**
 * @param mixed $boundary
 * @return array{method:string,path:string,response_shape:string,auth_guard:string,idempotency:string,failure_modes:list<string>}
 */
function app_no_code_screen_definition_custom_operation_route_boundary($boundary): array
{
    if (!is_array($boundary)) {
        return [
            'method' => '',
            'path' => '',
            'response_shape' => '',
            'auth_guard' => '',
            'idempotency' => '',
            'failure_modes' => [],
        ];
    }

    $failureModes = [];
    foreach (($boundary['failure_modes'] ?? []) as $mode) {
        $normalized = strtolower(trim((string) $mode));
        if ($normalized !== '') {
            $failureModes[] = $normalized;
        }
    }

    return [
        'method' => strtoupper(trim((string) ($boundary['method'] ?? ''))),
        'path' => trim((string) ($boundary['path'] ?? '')),
        'response_shape' => trim((string) ($boundary['response_shape'] ?? '')),
        'auth_guard' => trim((string) ($boundary['auth_guard'] ?? '')),
        'idempotency' => trim((string) ($boundary['idempotency'] ?? '')),
        'failure_modes' => array_values(array_unique($failureModes)),
    ];
}

function app_no_code_screen_definition_normalize_custom_operation_category(string $category): string
{
    $normalized = strtolower(trim($category));
    return in_array(
        $normalized,
        ['build', 'publish', 'review_request', 'approval', 'rollback', 'navigation', 'custom'],
        true,
    ) ? $normalized : 'custom';
}

function app_no_code_screen_definition_normalize_custom_operation_target(string $target): string
{
    $normalized = strtolower(trim($target));
    return in_array(
        $normalized,
        ['source_output', 'publish_candidate', 'artifact', 'shared_contract', 'project'],
        true,
    ) ? $normalized : '';
}

function app_no_code_screen_definition_normalize_custom_operation_side_effect_class(string $sideEffectClass): string
{
    $normalized = strtolower(trim($sideEffectClass));
    return in_array(
        $normalized,
        ['read_only', 'queued_mutation', 'direct_mutation', 'approval_transition', 'external_handoff'],
        true,
    ) ? $normalized : 'external_handoff';
}

function app_no_code_screen_definition_normalize_custom_operation_availability(string $availability): string
{
    $normalized = strtolower(trim($availability));
    return in_array($normalized, ['disabled', 'available', 'blocked', 'deferred'], true) ? $normalized : 'deferred';
}

/**
 * @param mixed $value
 * @return list<string>
 */
function app_no_code_screen_definition_normalize_screen_types(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $normalized = [];
    foreach ($value as $screenType) {
        $screenType = strtolower(trim((string) $screenType));
        if (in_array($screenType, ['list', 'detail', 'form'], true) && !in_array($screenType, $normalized, true)) {
            $normalized[] = $screenType;
        }
    }

    return $normalized;
}

/**
 * @param array<string,mixed> $contract
 * @return array{variant:string,source:string,allowed_variants:list<string>}
 */
function app_no_code_screen_definition_view_variant_preference(array $contract): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
    $explicitVariant = app_no_code_screen_definition_normalize_view_variant(
        (string) ($metadata['view_variant_preference'] ?? ''),
    );

    if ($explicitVariant !== '') {
        return [
            'variant' => $explicitVariant,
            'source' => 'view_variant_preference:explicit',
            'allowed_variants' => app_no_code_screen_definition_allowed_view_variants(),
        ];
    }

    return [
        'variant' => 'auto',
        'source' => 'derived:screen_type',
        'allowed_variants' => app_no_code_screen_definition_allowed_view_variants(),
    ];
}

/**
 * @return list<string>
 */
function app_no_code_screen_definition_allowed_view_variants(): array
{
    return ['auto', 'standard_table', 'detail_record', 'edit_form', 'review_list'];
}

function app_no_code_screen_definition_normalize_view_variant(string $viewVariant): string
{
    $normalized = strtolower(trim($viewVariant));
    return in_array(
        $normalized,
        ['standard_table', 'detail_record', 'edit_form', 'review_list'],
        true,
    ) ? $normalized : '';
}

/**
 * @param array<string,mixed> $contract
 * @return array{intent:string,source:string,allowed_intents:list<string>,presentation_layer:string}
 */
function app_no_code_screen_definition_interface_usage(array $contract): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
    $explicitUsageIntent = app_no_code_screen_definition_normalize_usage_intent(
        (string) ($metadata['usage_intent'] ?? ''),
    );
    $noCodeRole = (string) ($metadata['no_code_role'] ?? '');
    $syncRole = (string) ($metadata['sync_role'] ?? '');
    $appPersistenceRole = (string) ($metadata['app_persistence_role'] ?? '');
    $intent = 'internal';
    $source = 'derived:internal';

    if ($explicitUsageIntent !== '') {
        $intent = $explicitUsageIntent;
        $source = 'usage_intent:explicit';
    } elseif ($noCodeRole === 'managed-screen') {
        $intent = 'screen';
        $source = 'no_code_role:managed-screen';
    } elseif (in_array($syncRole, ['local-copy', 'server-copy', 'app-source', 'bidirectional-sync'], true)
        || in_array($appPersistenceRole, ['local-copy', 'server-managed-copy'], true)
    ) {
        $intent = 'sync';
        $source = 'sync_or_app_persistence_role';
    }

    return [
        'intent' => $intent,
        'source' => $source,
        'allowed_intents' => ['screen', 'external_integration', 'sync', 'reporting', 'workflow', 'internal'],
        'presentation_layer' => 'view_variant',
    ];
}

function app_no_code_screen_definition_normalize_usage_intent(string $usageIntent): string
{
    $normalized = strtolower(trim($usageIntent));
    return in_array(
        $normalized,
        ['screen', 'external_integration', 'sync', 'reporting', 'workflow', 'internal'],
        true,
    ) ? $normalized : '';
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array{
 *     source_contract:array{target:string,contract_key:string,label:string},
 *     canonical_fields:list<array{target:string,field_key:string,label:string}>,
 *     managed_operations:list<array{target:string,operation_key:string,operation_type:string,label:string}>,
 *     source_output:array{target:string,source_output_key:string,label:string},
 *     publish_candidate:array{target:string,label:string},
 *     current_revision:array{target:string,label:string},
 *     alias:array{target:string,label:string},
 *     outbox_review:array{target:string,label:string}
 * }
 */
function app_no_code_screen_definition_traceability(string $contractKey, array $fields, array $actions): array
{
    $canonicalFields = [];
    foreach ($fields as $field) {
        $fieldKey = (string) ($field['field_key'] ?? '');
        if ($fieldKey === '') {
            continue;
        }

        $canonicalFields[] = [
            'target' => 'canonical_field',
            'field_key' => $fieldKey,
            'label' => (string) ($field['label'] ?? $fieldKey),
        ];
    }

    $managedOperations = [];
    foreach ($actions as $action) {
        $operationKey = (string) ($action['operation_key'] ?? $action['action_key'] ?? '');
        if ($operationKey === '') {
            continue;
        }

        $managedOperations[] = [
            'target' => 'managed_operation',
            'operation_key' => $operationKey,
            'operation_type' => (string) ($action['operation_type'] ?? ''),
            'label' => (string) ($action['label'] ?? $operationKey),
        ];
    }

    return [
        'source_contract' => [
            'target' => 'shared_contract',
            'contract_key' => $contractKey,
            'label' => 'Shared contract',
        ],
        'canonical_fields' => $canonicalFields,
        'managed_operations' => $managedOperations,
        'source_output' => [
            'target' => 'source_output',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'label' => 'NO-CODE-RUNTIME Source Output',
        ],
        'publish_candidate' => [
            'target' => 'publish_candidate',
            'label' => 'Publish candidate review',
        ],
        'current_revision' => [
            'target' => 'current_public_revision',
            'label' => 'Current public revision',
        ],
        'alias' => [
            'target' => 'public_alias',
            'label' => 'Public runtime alias',
        ],
        'outbox_review' => [
            'target' => 'sync_outbox_review',
            'label' => 'Sync outbox review',
        ],
    ];
}

/**
 * @param array<string,mixed> $contract
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_fields(array $contract): array
{
    $fields = [];
    foreach (($contract['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $metadata = is_array($field['contract_metadata'] ?? null) ? $field['contract_metadata'] : [];
        $isKey = (bool) ($field['is_key'] ?? false);
        $operationRole = (string) ($metadata['operation_role'] ?? '');
        $fields[] = [
            'field_key' => (string) ($field['physical_name'] ?? ''),
            'generated_name' => (string) ($field['generated_name'] ?? ''),
            'label' => app_no_code_screen_definition_field_label($field),
            'type' => (string) ($field['type'] ?? 'string'),
            'is_key' => $isKey,
            'nullable' => (bool) ($field['nullable'] ?? false),
            'required' => !$isKey && !(bool) ($field['nullable'] ?? false) && ($field['default'] ?? null) === null,
            'readonly' => $isKey || $operationRole !== 'editable',
            'visibility' => 'visible',
        ];
    }

    return $fields;
}

/**
 * @param array<string,mixed> $field
 */
function app_no_code_screen_definition_field_label(array $field): string
{
    $logicalName = trim((string) ($field['logical_name'] ?? ''));
    if ($logicalName !== '') {
        return $logicalName;
    }

    return (string) ($field['generated_name'] ?? $field['physical_name'] ?? '');
}

/**
 * @param array<string,mixed> $contract
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_storage_hint(array $contract): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];

    return [
        'sync_role' => (string) ($metadata['sync_role'] ?? 'unknown'),
        'app_persistence_role' => (string) ($metadata['app_persistence_role'] ?? 'unknown'),
        'sync_status_display' => in_array(
            (string) ($metadata['sync_role'] ?? ''),
            ['local-copy', 'server-copy', 'app-source', 'bidirectional-sync'],
            true,
        ),
    ];
}

/**
 * @param array<string,mixed> $contract
 * @param list<array<string,mixed>> $operations
 * @param array<string,mixed>|null $principal
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_actions(array $contract, array $operations, ?array $principal): array
{
    $actions = [];
    foreach ($operations as $operation) {
        if ((string) ($operation['status'] ?? '') !== 'active') {
            continue;
        }

        $policy = app_no_code_screen_definition_policy($contract, $operation, $principal);
        $actions[] = [
            'action_key' => (string) ($operation['operation_key'] ?? ''),
            'label' => (string) ($operation['name'] ?? $operation['operation_key'] ?? ''),
            'operation_key' => (string) ($operation['operation_key'] ?? ''),
            'operation_type' => (string) ($operation['operation_type'] ?? ''),
            'permission_key' => (string) ($operation['permission_key'] ?? ''),
            'availability' => $policy['allowed'] ? 'enabled' : 'disabled',
            'policy' => $policy,
            'submit_route' => app_no_code_screen_definition_managed_action_submit_route((string) ($operation['operation_key'] ?? '')),
            'submit_binding_gate' => app_no_code_screen_definition_managed_action_submit_binding_gate((string) ($operation['operation_key'] ?? '')),
            'fields' => app_no_code_screen_definition_action_fields($operation),
        ];
    }

    return $actions;
}

function app_no_code_screen_definition_managed_action_submit_route(string $operationKey): string
{
    return in_array($operationKey, ['create_task_card', 'update_task_card', 'complete_task_card'], true)
        ? '/samples/sample18-task-board/no-code/generated-submit'
        : '';
}

/**
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_managed_action_submit_binding_gate(string $operationKey): array
{
    if (!in_array($operationKey, ['create_task_card', 'update_task_card', 'complete_task_card'], true)) {
        return [];
    }

    return [
        'binding_state' => 'blocked_preflight',
        'submit_route' => app_no_code_screen_definition_managed_action_submit_route($operationKey),
        'csrf_source' => 'sample18_task_board_form_token',
        'csrf_token_field' => '_csrf_token',
        'csrf_source_selector' => 'input[name=_csrf_token]',
        'csrf_transport' => 'form_field',
        'csrf_submit_field' => '_csrf_token',
        'required_button_state' => 'disabled',
        'click_binding_state' => 'disabled_preflight',
        'submit_trigger' => 'none',
        'network_submit_enabled' => false,
        'runtime_click_binding' => false,
        'mutation_enabled' => false,
        'fail_closed_result' => 'generated_submit_disabled',
        'http_smoke_command' => 'make sample18-http-runtime-smoke',
    ];
}

/**
 * @param array<string,mixed> $contract
 * @param array<string,mixed> $operation
 * @param array<string,mixed>|null $principal
 * @return array{evaluated:bool,allowed:bool,failed_checks:list<string>}
 */
function app_no_code_screen_definition_policy(array $contract, array $operation, ?array $principal): array
{
    if ($principal === null) {
        return [
            'evaluated' => false,
            'allowed' => false,
            'failed_checks' => ['principal.missing'],
        ];
    }

    $decision = app_managed_operation_policy_evaluate($principal, $operation, $contract);

    return [
        'evaluated' => true,
        'allowed' => (bool) ($decision['allowed'] ?? false),
        'failed_checks' => is_array($decision['failed_checks'] ?? null) ? $decision['failed_checks'] : [],
    ];
}

/**
 * @param array<string,mixed> $operation
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_action_fields(array $operation): array
{
    $fields = [];
    foreach (($operation['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fields[] = [
            'field_key' => (string) ($field['field_physical_name'] ?? ''),
            'role' => (string) ($field['field_role'] ?? ''),
            'required' => (bool) ($field['is_required'] ?? false),
            'client_write' => (bool) ($field['allow_client_write'] ?? false),
        ];
    }

    return $fields;
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_list_screen(
    string $contractKey,
    array $fields,
    array $actions,
    bool $syncStatusDisplay,
    array $presentationProfile,
    array $extensionSlots,
): array {
    return [
        'screen_key' => $contractKey . '_list',
        'screen_type' => 'list',
        'view_variant' => 'standard_table',
        'contract_key' => $contractKey,
        'presentation_hint' => app_no_code_screen_definition_screen_presentation_hint($presentationProfile, 'list'),
        'extension_slots' => app_no_code_screen_definition_screen_extension_slots($extensionSlots, 'list'),
        'fields' => app_no_code_screen_definition_screen_fields($fields, 'list'),
        'actions' => app_no_code_screen_definition_screen_actions($actions, ['list', 'read', 'create', 'update', 'delete']),
        'sync_status_hint' => $syncStatusDisplay,
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_detail_screen(
    string $contractKey,
    array $fields,
    array $actions,
    bool $syncStatusDisplay,
    array $presentationProfile,
    array $extensionSlots,
): array {
    return [
        'screen_key' => $contractKey . '_detail',
        'screen_type' => 'detail',
        'view_variant' => 'detail_record',
        'contract_key' => $contractKey,
        'presentation_hint' => app_no_code_screen_definition_screen_presentation_hint($presentationProfile, 'detail'),
        'extension_slots' => app_no_code_screen_definition_screen_extension_slots($extensionSlots, 'detail'),
        'fields' => app_no_code_screen_definition_screen_fields($fields, 'detail'),
        'actions' => app_no_code_screen_definition_screen_actions($actions, ['read', 'update', 'delete']),
        'sync_status_hint' => $syncStatusDisplay,
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_form_screen(
    string $contractKey,
    array $fields,
    array $actions,
    array $presentationProfile,
    array $extensionSlots,
): array
{
    return [
        'screen_key' => $contractKey . '_form',
        'screen_type' => 'form',
        'view_variant' => 'edit_form',
        'contract_key' => $contractKey,
        'presentation_hint' => app_no_code_screen_definition_screen_presentation_hint($presentationProfile, 'form'),
        'extension_slots' => app_no_code_screen_definition_screen_extension_slots($extensionSlots, 'form'),
        'fields' => app_no_code_screen_definition_screen_fields($fields, 'form'),
        'actions' => app_no_code_screen_definition_screen_actions($actions, ['create', 'update']),
        'sync_status_hint' => false,
    ];
}

/**
 * @param array<string,mixed> $presentationProfile
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_screen_presentation_hint(array $presentationProfile, string $screenType): array
{
    return [
        'profile_key' => (string) ($presentationProfile['profile_key'] ?? ''),
        'screen_type' => $screenType,
        'density' => (string) ($presentationProfile['density'] ?? 'standard'),
        'emphasis' => (string) ($presentationProfile['emphasis'] ?? 'balanced'),
        'primary_fields' => is_array($presentationProfile['primary_fields'] ?? null) ? $presentationProfile['primary_fields'] : [],
        'secondary_fields' => is_array($presentationProfile['secondary_fields'] ?? null) ? $presentationProfile['secondary_fields'] : [],
        'field_groups' => is_array($presentationProfile['field_groups'] ?? null) ? $presentationProfile['field_groups'] : [],
    ];
}

/**
 * @param list<array<string,mixed>> $extensionSlots
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_screen_extension_slots(array $extensionSlots, string $screenType): array
{
    $slots = [];
    foreach ($extensionSlots as $slot) {
        $screenTypes = is_array($slot['screen_types'] ?? null) ? $slot['screen_types'] : [];
        if (in_array($screenType, $screenTypes, true)) {
            $slots[] = $slot;
        }
    }

    return $slots;
}

/**
 * @param list<array<string,mixed>> $fields
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_screen_fields(array $fields, string $screenType): array
{
    if ($screenType === 'form') {
        return array_values(array_filter($fields, static fn (array $field): bool => !(bool) ($field['is_key'] ?? false)));
    }

    return $fields;
}

/**
 * @param list<array<string,mixed>> $actions
 * @param list<string> $operationTypes
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_screen_actions(array $actions, array $operationTypes): array
{
    $screenActions = [];
    foreach ($actions as $action) {
        if (in_array((string) ($action['operation_type'] ?? ''), $operationTypes, true)) {
            $screenActions[] = [
                'action_key' => (string) ($action['action_key'] ?? ''),
                'operation_key' => (string) ($action['operation_key'] ?? ''),
                'operation_type' => (string) ($action['operation_type'] ?? ''),
                'availability' => (string) ($action['availability'] ?? 'disabled'),
                'submit_route' => (string) ($action['submit_route'] ?? ''),
                'submit_binding_gate' => is_array($action['submit_binding_gate'] ?? null) ? $action['submit_binding_gate'] : [],
            ];
        }
    }

    return $screenActions;
}
