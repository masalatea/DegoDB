<?php

declare(strict_types=1);

require_once __DIR__ . '/no_code_screen_definition.php';

/**
 * @param array<string,mixed> $definition
 * @param list<array<string,mixed>> $rows
 * @param array<string,mixed> $currentItem
 * @return array{ok:bool,render:array<string,mixed>,error:string}
 */
function app_no_code_runtime_render_screen(
    array $definition,
    string $screenKey,
    array $rows = [],
    array $currentItem = [],
): array
{
    $screenLookup = app_no_code_runtime_find_screen($definition, $screenKey);
    if (!$screenLookup['ok']) {
        return app_no_code_runtime_render_error($screenLookup['error']);
    }

    $screen = $screenLookup['screen'];
    $contract = $screenLookup['contract'];
    $screenType = (string) ($screen['screen_type'] ?? '');
    $fields = is_array($screen['fields'] ?? null) ? $screen['fields'] : [];

    return [
        'ok' => true,
        'render' => [
            'runtime_version' => app_no_code_runtime_version(),
            'definition_version' => (string) ($definition['definition_version'] ?? ''),
            'project_key' => (string) ($definition['project_key'] ?? ''),
            'contract_key' => (string) ($contract['contract_key'] ?? ''),
            'screen_key' => $screenKey,
            'screen_type' => $screenType,
            'screen_title' => app_no_code_runtime_screen_title($screenKey, $screenType),
            'screen_subtitle' => app_no_code_runtime_screen_subtitle(
                (string) ($contract['contract_key'] ?? ''),
                $screenType,
            ),
            'fields' => app_no_code_runtime_render_fields($fields),
            'presentation_hint' => is_array($screen['presentation_hint'] ?? null) ? $screen['presentation_hint'] : [],
            'extension_slots' => is_array($screen['extension_slots'] ?? null) ? $screen['extension_slots'] : [],
            'custom_operations' => is_array($contract['custom_operations'] ?? null) ? $contract['custom_operations'] : [],
            'actions' => app_no_code_runtime_render_actions(
                is_array($screen['actions'] ?? null) ? $screen['actions'] : [],
                is_array($contract['actions'] ?? null) ? $contract['actions'] : [],
            ),
            'data' => app_no_code_runtime_render_data($screenType, $fields, $rows, $currentItem),
            'empty_state_message' => app_no_code_runtime_empty_state_message($screenType),
            'sync_status_hint' => (bool) ($screen['sync_status_hint'] ?? false),
            'sync_error_retry_hint' => app_no_code_runtime_sync_error_retry_hint((bool) ($screen['sync_status_hint'] ?? false)),
        ],
        'error' => '',
    ];
}

function app_no_code_runtime_version(): string
{
    return 'no-code-runtime-v0';
}

function app_no_code_runtime_screen_title(string $screenKey, string $screenType): string
{
    $base = $screenKey;
    foreach (['_list', '_detail', '_form'] as $suffix) {
        if (str_ends_with($base, $suffix)) {
            $base = substr($base, 0, -strlen($suffix));
            break;
        }
    }

    $label = app_no_code_runtime_human_label($base !== '' ? $base : $screenKey);
    return match ($screenType) {
        'list' => $label . ' List',
        'detail' => $label . ' Detail',
        'form' => $label . ' Form',
        default => $label,
    };
}

function app_no_code_runtime_screen_subtitle(string $contractKey, string $screenType): string
{
    $contractLabel = app_no_code_runtime_human_label($contractKey);
    $typeLabel = app_no_code_runtime_human_label($screenType);
    if ($contractLabel === '') {
        return $typeLabel;
    }

    return $contractLabel . ' / ' . $typeLabel;
}

function app_no_code_runtime_empty_state_message(string $screenType): string
{
    return match ($screenType) {
        'list' => 'No records to show yet.',
        'detail' => 'No detail data is available yet.',
        'form' => 'No editable data is available yet.',
        default => 'No preview data is available yet.',
    };
}

function app_no_code_runtime_human_label(string $value): string
{
    $normalized = trim(str_replace(['_', '-'], ' ', $value));
    if ($normalized === '') {
        return '';
    }

    return ucwords($normalized);
}

function app_no_code_runtime_sync_error_retry_hint(bool $syncStatusHint): string
{
    if (!$syncStatusHint) {
        return '';
    }

    return 'Failed or retryable sync items are reviewed from the operator sync outbox.';
}

/**
 * @return array{ok:bool,render:array<string,mixed>,error:string}
 */
function app_no_code_runtime_render_error(string $error): array
{
    return [
        'ok' => false,
        'render' => [],
        'error' => $error,
    ];
}

/**
 * @param array<string,mixed> $definition
 * @return array{ok:bool,contract:array<string,mixed>,screen:array<string,mixed>,error:string}
 */
function app_no_code_runtime_find_screen(array $definition, string $screenKey): array
{
    foreach (($definition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        foreach (($contract['screens'] ?? []) as $screen) {
            if (!is_array($screen)) {
                continue;
            }

            if ((string) ($screen['screen_key'] ?? '') === $screenKey) {
                return [
                    'ok' => true,
                    'contract' => $contract,
                    'screen' => $screen,
                    'error' => '',
                ];
            }
        }
    }

    return [
        'ok' => false,
        'contract' => [],
        'screen' => [],
        'error' => 'screen が見つかりません: ' . $screenKey,
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @return list<array<string,mixed>>
 */
function app_no_code_runtime_render_fields(array $fields): array
{
    $renderFields = [];
    foreach ($fields as $field) {
        $renderFields[] = [
            'field_key' => (string) ($field['field_key'] ?? ''),
            'label' => (string) ($field['label'] ?? $field['field_key'] ?? ''),
            'type' => (string) ($field['type'] ?? 'string'),
            'required' => (bool) ($field['required'] ?? false),
            'readonly' => (bool) ($field['readonly'] ?? false),
            'visibility' => (string) ($field['visibility'] ?? 'visible'),
        ];
    }

    return $renderFields;
}

/**
 * @param list<array<string,mixed>> $screenActions
 * @param list<array<string,mixed>> $contractActions
 * @return list<array<string,mixed>>
 */
function app_no_code_runtime_render_actions(array $screenActions, array $contractActions): array
{
    $contractActionsByKey = app_no_code_runtime_actions_by_key($contractActions);
    $renderActions = [];
    foreach ($screenActions as $screenAction) {
        $actionKey = (string) ($screenAction['action_key'] ?? '');
        $contractAction = $contractActionsByKey[$actionKey] ?? [];
        $availability = (string) ($screenAction['availability'] ?? $contractAction['availability'] ?? 'disabled');
        $renderActions[] = [
            'action_key' => $actionKey,
            'label' => (string) ($contractAction['label'] ?? $actionKey),
            'operation_key' => (string) ($screenAction['operation_key'] ?? $contractAction['operation_key'] ?? ''),
            'operation_type' => (string) ($screenAction['operation_type'] ?? $contractAction['operation_type'] ?? ''),
            'enabled' => $availability === 'enabled',
            'availability' => $availability,
            'submit_route' => (string) ($screenAction['submit_route'] ?? $contractAction['submit_route'] ?? ''),
            'submit_binding_gate' => is_array($screenAction['submit_binding_gate'] ?? null)
                ? $screenAction['submit_binding_gate']
                : (is_array($contractAction['submit_binding_gate'] ?? null) ? $contractAction['submit_binding_gate'] : []),
            'fields' => is_array($contractAction['fields'] ?? null) ? array_values($contractAction['fields']) : [],
            'failed_checks' => is_array($contractAction['policy']['failed_checks'] ?? null)
                ? $contractAction['policy']['failed_checks']
                : [],
        ];
    }

    return $renderActions;
}

/**
 * @param list<array<string,mixed>> $actions
 * @return array<string,array<string,mixed>>
 */
function app_no_code_runtime_actions_by_key(array $actions): array
{
    $indexed = [];
    foreach ($actions as $action) {
        $actionKey = (string) ($action['action_key'] ?? '');
        if ($actionKey !== '') {
            $indexed[$actionKey] = $action;
        }
    }

    return $indexed;
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,mixed> $policyDefinition
 * @return array<string,mixed>
 */
function app_no_code_runtime_definition_with_action_policy_overlay(array $definition, array $policyDefinition): array
{
    $policyActionsByKey = [];
    foreach (($policyDefinition['contracts'] ?? []) as $policyContract) {
        if (!is_array($policyContract)) {
            continue;
        }

        foreach (($policyContract['actions'] ?? []) as $policyAction) {
            if (!is_array($policyAction)) {
                continue;
            }
            $actionKey = (string) ($policyAction['action_key'] ?? '');
            if ($actionKey !== '') {
                $policyActionsByKey[$actionKey] = $policyAction;
            }
        }
    }

    if ($policyActionsByKey === []) {
        return $definition;
    }

    $overlaid = $definition;
    foreach (($overlaid['contracts'] ?? []) as $contractIndex => $contract) {
        if (!is_array($contract)) {
            continue;
        }

        foreach (($contract['actions'] ?? []) as $actionIndex => $action) {
            if (!is_array($action)) {
                continue;
            }
            $actionKey = (string) ($action['action_key'] ?? '');
            $policyAction = $policyActionsByKey[$actionKey] ?? null;
            if (!is_array($policyAction)) {
                continue;
            }

            $overlaid['contracts'][$contractIndex]['actions'][$actionIndex]['availability'] = (string) ($policyAction['availability'] ?? 'disabled');
            $overlaid['contracts'][$contractIndex]['actions'][$actionIndex]['policy'] = is_array($policyAction['policy'] ?? null)
                ? $policyAction['policy']
                : [];
        }

        foreach (($contract['screens'] ?? []) as $screenIndex => $screen) {
            if (!is_array($screen)) {
                continue;
            }
            foreach (($screen['actions'] ?? []) as $actionIndex => $action) {
                if (!is_array($action)) {
                    continue;
                }
                $actionKey = (string) ($action['action_key'] ?? '');
                $policyAction = $policyActionsByKey[$actionKey] ?? null;
                if (!is_array($policyAction)) {
                    continue;
                }

                $overlaid['contracts'][$contractIndex]['screens'][$screenIndex]['actions'][$actionIndex]['availability'] = (string) ($policyAction['availability'] ?? 'disabled');
            }
        }
    }

    return $overlaid;
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $rows
 * @param array<string,mixed> $currentItem
 * @return array<string,mixed>
 */
function app_no_code_runtime_render_data(string $screenType, array $fields, array $rows, array $currentItem): array
{
    if ($screenType === 'list') {
        return [
            'rows' => app_no_code_runtime_render_rows($fields, $rows),
        ];
    }

    return [
        'item' => app_no_code_runtime_render_item($fields, $currentItem),
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $rows
 * @return list<array<string,mixed>>
 */
function app_no_code_runtime_render_rows(array $fields, array $rows): array
{
    $renderRows = [];
    foreach ($rows as $row) {
        $renderRows[] = app_no_code_runtime_render_item($fields, $row);
    }

    return $renderRows;
}

/**
 * @param list<array<string,mixed>> $fields
 * @param array<string,mixed> $item
 * @return array<string,mixed>
 */
function app_no_code_runtime_render_item(array $fields, array $item): array
{
    $values = [];
    foreach ($fields as $field) {
        $fieldKey = (string) ($field['field_key'] ?? '');
        if ($fieldKey === '') {
            continue;
        }

        $generatedName = (string) ($field['generated_name'] ?? '');
        $value = null;
        if (array_key_exists($fieldKey, $item)) {
            $value = $item[$fieldKey];
        } elseif ($generatedName !== '' && array_key_exists($generatedName, $item)) {
            $value = $item[$generatedName];
        }

        $values[$fieldKey] = [
            'value' => $value,
            'display_value' => app_no_code_runtime_display_value($value),
        ];
    }

    return $values;
}

function app_no_code_runtime_display_value(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_scalar($value)) {
        return (string) $value;
    }

    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return is_string($json) ? $json : '';
}

/**
 * @param array<string,mixed> $runtimePreview
 */
function app_no_code_runtime_render_preview_html(array $runtimePreview): string
{
    $sections = [];
    foreach (($runtimePreview['screens'] ?? []) as $screenRender) {
        if (!is_array($screenRender)) {
            continue;
        }

        $sections[] = app_no_code_runtime_render_screen_html($screenRender);
    }

    $previewOk = (bool) ($runtimePreview['ok'] ?? true);
    $previewState = $previewOk ? 'ready' : 'error';
    $previewError = (string) ($runtimePreview['error'] ?? '');
    $statusText = $previewOk ? 'Preview ready' : ($previewError !== '' ? $previewError : 'Preview could not be prepared.');

    return implode("\n", [
        '<!doctype html>',
        '<html lang="en">',
        '<head>',
        '<meta charset="utf-8">',
        '<meta name="viewport" content="width=device-width, initial-scale=1">',
        '<title>No-Code Runtime Preview</title>',
        '<style>',
        app_no_code_runtime_preview_css(),
        '</style>',
        '</head>',
        '<body>',
        '<main class="no-code-preview" aria-labelledby="no-code-preview-title" data-runtime-version="' . app_no_code_runtime_html_escape((string) ($runtimePreview['runtime_version'] ?? '')) . '" data-runtime-state="' . app_no_code_runtime_html_escape($previewState) . '">',
        '<header class="no-code-preview-header">',
        '<div>',
        '<h1 id="no-code-preview-title">' . app_no_code_runtime_html_escape((string) ($runtimePreview['project_key'] ?? 'No-code runtime')) . '</h1>',
        '<p>' . app_no_code_runtime_html_escape((string) ($runtimePreview['definition_version'] ?? '')) . '</p>',
        '</div>',
        '<span class="no-code-state-badge" data-state="' . app_no_code_runtime_html_escape($previewState) . '">' . app_no_code_runtime_html_escape($statusText) . '</span>',
        '</header>',
        implode("\n", $sections),
        '</main>',
        '<script type="application/json" id="no-code-runtime-preview-data">' . app_no_code_runtime_json_script_text($runtimePreview) . '</script>',
        '<script>',
        app_no_code_runtime_preview_js(),
        '</script>',
        '</body>',
        '</html>',
        '',
    ]);
}

/**
 * @param array<string,mixed> $render
 */
function app_no_code_runtime_render_screen_html(array $render): string
{
    $screenType = (string) ($render['screen_type'] ?? '');
    $screenKey = (string) ($render['screen_key'] ?? '');
    $screenTitle = (string) ($render['screen_title'] ?? app_no_code_runtime_screen_title($screenKey, $screenType));
    $screenSubtitle = (string) ($render['screen_subtitle'] ?? '');
    $fields = is_array($render['fields'] ?? null) ? $render['fields'] : [];
    $extensionSlots = is_array($render['extension_slots'] ?? null) ? $render['extension_slots'] : [];
    $actions = is_array($render['actions'] ?? null) ? $render['actions'] : [];
    $data = is_array($render['data'] ?? null) ? $render['data'] : [];
    $emptyStateMessage = (string) ($render['empty_state_message'] ?? app_no_code_runtime_empty_state_message($screenType));
    $screenState = app_no_code_runtime_screen_state($screenType, $data);
    $screenStatusMessage = app_no_code_runtime_screen_status_message($screenState, $screenType);
    $syncStatusHint = (bool) ($render['sync_status_hint'] ?? false);
    $syncErrorRetryHint = (string) ($render['sync_error_retry_hint'] ?? app_no_code_runtime_sync_error_retry_hint($syncStatusHint));

    $screenTitleId = app_no_code_runtime_dom_id('no-code-screen-title-' . $screenKey);
    $body = $screenType === 'list'
        ? app_no_code_runtime_render_list_html($fields, is_array($data['rows'] ?? null) ? $data['rows'] : [], $emptyStateMessage, $screenTitle . ' records')
        : app_no_code_runtime_render_item_screen_html(
            $screenType,
            $fields,
            is_array($data['item'] ?? null) ? $data['item'] : [],
            $emptyStateMessage,
    );

    return implode("\n", [
        '<section class="no-code-screen no-code-screen-' . app_no_code_runtime_html_escape($screenType) . '" role="region" aria-labelledby="' . app_no_code_runtime_html_escape($screenTitleId) . '" data-screen-key="' . app_no_code_runtime_html_escape($screenKey) . '" data-screen-type="' . app_no_code_runtime_html_escape($screenType) . '" data-screen-state="' . app_no_code_runtime_html_escape($screenState) . '">',
        '<header class="no-code-screen-header">',
        '<div>',
        '<h2 id="' . app_no_code_runtime_html_escape($screenTitleId) . '">' . app_no_code_runtime_html_escape($screenTitle) . '</h2>',
        '<p>' . app_no_code_runtime_html_escape($screenSubtitle) . '</p>',
        '</div>',
        '<span class="no-code-state-badge" data-state="' . app_no_code_runtime_html_escape($screenState) . '">' . app_no_code_runtime_html_escape($screenStatusMessage) . '</span>',
        app_no_code_runtime_render_sync_status_hint_html($syncStatusHint),
        app_no_code_runtime_render_sync_error_retry_hint_html($syncErrorRetryHint),
        app_no_code_runtime_render_actions_html($actions, $screenTitle, $screenKey),
        '</header>',
        app_no_code_runtime_render_screen_summary_html($screenKey, $fields, $actions),
        app_no_code_runtime_render_extension_slots_html($screenKey, $extensionSlots),
        '<div class="no-code-action-feedback" role="status" aria-live="polite" data-state="idle">Select an enabled action to preview its intent.</div>',
        app_no_code_runtime_render_action_intent_draft_html($actions),
        '<div class="no-code-screen-body" data-screen-body="' . app_no_code_runtime_html_escape($screenKey) . '">',
        $body,
        '</div>',
        '</section>',
    ]);
}

/**
 * @param list<array<string,mixed>> $extensionSlots
 */
function app_no_code_runtime_render_extension_slots_html(string $screenKey, array $extensionSlots): string
{
    if ($extensionSlots === []) {
        return '';
    }

    $items = [];
    foreach ($extensionSlots as $slot) {
        if (!is_array($slot)) {
            continue;
        }

        $slotKey = (string) ($slot['slot_key'] ?? '');
        $slotType = (string) ($slot['slot_type'] ?? '');
        if ($slotKey === '' || $slotType === '') {
            continue;
        }

        $label = (string) ($slot['label'] ?? $slotKey);
        $renderer = (string) ($slot['renderer'] ?? 'placeholder');
        $target = (string) ($slot['target'] ?? '');
        $placement = (string) ($slot['placement'] ?? 'aside');
        $metaParts = array_values(array_filter([$slotType, $target, $renderer]));
        $slotBody = app_no_code_runtime_render_extension_slot_body_html(
            $slotType,
            $renderer,
            is_array($slot['links'] ?? null) ? $slot['links'] : [],
            is_array($slot['status_items'] ?? null) ? $slot['status_items'] : [],
            is_array($slot['action_items'] ?? null) ? $slot['action_items'] : [],
            $metaParts,
        );
        $items[] = implode("\n", [
            '<article class="no-code-extension-slot" data-extension-slot="' . app_no_code_runtime_html_escape($slotKey) . '" data-extension-slot-type="' . app_no_code_runtime_html_escape($slotType) . '" data-extension-slot-placement="' . app_no_code_runtime_html_escape($placement) . '" data-extension-slot-renderer="' . app_no_code_runtime_html_escape($renderer) . '">',
            '<strong>' . app_no_code_runtime_html_escape($label) . '</strong>',
            $slotBody,
            '</article>',
        ]);
    }

    if ($items === []) {
        return '';
    }

    return implode("\n", [
        '<section class="no-code-extension-slots" data-extension-slots-for="' . app_no_code_runtime_html_escape($screenKey) . '" aria-label="Declared extension slots">',
        implode("\n", $items),
        '</section>',
    ]);
}

/**
 * @param list<array<string,mixed>> $links
 * @param list<array<string,mixed>> $statusItems
 * @param list<array<string,mixed>> $actionItems
 * @param list<string> $metaParts
 */
function app_no_code_runtime_render_extension_slot_body_html(
    string $slotType,
    string $renderer,
    array $links,
    array $statusItems,
    array $actionItems,
    array $metaParts,
): string {
    if ($slotType === 'related_settings_panel' && $renderer === 'link_list') {
        $items = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $label = trim((string) ($link['label'] ?? ''));
            $href = trim((string) ($link['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }

            $target = trim((string) ($link['target'] ?? ''));
            $items[] = '<li><a href="' . app_no_code_runtime_html_escape($href) . '" data-extension-slot-link="' . app_no_code_runtime_html_escape($target) . '">' . app_no_code_runtime_html_escape($label) . '</a></li>';
        }

        if ($items !== []) {
            return implode("\n", [
                '<ul class="no-code-extension-slot-links">',
                implode("\n", $items),
                '</ul>',
            ]);
        }
    }

    if ($slotType === 'artifact_status_panel' && $renderer === 'status_card') {
        $items = [];
        foreach ($statusItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            $value = trim((string) ($item['value'] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }

            $state = trim((string) ($item['state'] ?? 'info'));
            $items[] = implode('', [
                '<div class="no-code-extension-slot-status-item" data-extension-slot-status-item="' . app_no_code_runtime_html_escape($label) . '" data-extension-slot-status-state="' . app_no_code_runtime_html_escape($state) . '">',
                '<dt>' . app_no_code_runtime_html_escape($label) . '</dt>',
                '<dd>' . app_no_code_runtime_html_escape($value) . '</dd>',
                '</div>',
            ]);
        }

        if ($items !== []) {
            return implode("\n", [
                '<dl class="no-code-extension-slot-status-card">',
                implode("\n", $items),
                '</dl>',
            ]);
        }
    }

    if ($slotType === 'operator_actions_panel' && $renderer === 'action_panel') {
        $items = [];
        foreach ($actionItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            $actionKey = trim((string) ($item['action_key'] ?? ''));
            if ($label === '' || $actionKey === '') {
                continue;
            }

            $intent = trim((string) ($item['intent'] ?? ''));
            $state = trim((string) ($item['state'] ?? 'deferred'));
            $operationKey = trim((string) ($item['operation_key'] ?? $actionKey));
            $unavailableReason = trim((string) ($item['unavailable_reason'] ?? ''));
            $availabilityReadModel = is_array($item['availability_read_model'] ?? null) ? $item['availability_read_model'] : [];
            $operationAvailability = trim((string) ($availabilityReadModel['operation_availability'] ?? ''));
            $availabilityState = trim((string) ($availabilityReadModel['availability_state'] ?? ''));
            $preflightResult = trim((string) ($availabilityReadModel['preflight_result'] ?? ''));
            $executionMode = trim((string) ($availabilityReadModel['execution_mode'] ?? ''));
            $availabilityReason = trim((string) ($availabilityReadModel['availability_reason'] ?? ''));
            $generatedButtonEnabled = (bool) ($availabilityReadModel['generated_button_enabled'] ?? false);
            $routeBoundary = is_array($item['route_boundary'] ?? null) ? $item['route_boundary'] : [];
            $routeMethod = trim((string) ($routeBoundary['method'] ?? ''));
            $routePath = trim((string) ($routeBoundary['path'] ?? ''));
            $authGuard = trim((string) ($routeBoundary['auth_guard'] ?? ''));
            $availabilityText = '';
            if ($availabilityState !== '' || $operationAvailability !== '' || $executionMode !== '') {
                $availabilityParts = [];
                if ($availabilityState !== '') {
                    $availabilityParts[] = 'state ' . $availabilityState;
                }
                if ($operationAvailability !== '') {
                    $availabilityParts[] = 'operation ' . $operationAvailability;
                }
                if ($executionMode !== '') {
                    $availabilityParts[] = 'execution ' . $executionMode;
                }
                if ($preflightResult !== '') {
                    $availabilityParts[] = 'preflight ' . $preflightResult;
                }
                $availabilityText = 'Availability preview: ' . implode('; ', $availabilityParts) . '.';
            }
            $routeBoundaryText = '';
            if ($routeMethod !== '' || $routePath !== '' || $authGuard !== '') {
                $routeParts = [];
                if ($routeMethod !== '' || $routePath !== '') {
                    $routeParts[] = 'route ' . trim($routeMethod . ' ' . $routePath);
                }
                if ($authGuard !== '') {
                    $routeParts[] = 'auth ' . $authGuard;
                }
                $routeBoundaryText = 'Route boundary declared, execution still disabled: ' . implode('; ', $routeParts) . '.';
            }
            $items[] = implode("\n", [
                '<div class="no-code-extension-slot-action-item" data-extension-slot-action-item="' . app_no_code_runtime_html_escape($actionKey) . '" data-extension-slot-operation="' . app_no_code_runtime_html_escape($operationKey) . '" data-extension-slot-action-state="' . app_no_code_runtime_html_escape($state) . '" data-availability-state="' . app_no_code_runtime_html_escape($availabilityState) . '" data-operation-availability="' . app_no_code_runtime_html_escape($operationAvailability) . '" data-preflight-result="' . app_no_code_runtime_html_escape($preflightResult) . '" data-execution-mode="' . app_no_code_runtime_html_escape($executionMode) . '" data-generated-button-enabled="' . ($generatedButtonEnabled ? 'true' : 'false') . '">',
                '<button type="button" data-extension-slot-action="' . app_no_code_runtime_html_escape($actionKey) . '" data-extension-slot-operation-key="' . app_no_code_runtime_html_escape($operationKey) . '" data-availability-state="' . app_no_code_runtime_html_escape($availabilityState) . '" data-operation-availability="' . app_no_code_runtime_html_escape($operationAvailability) . '" data-preflight-result="' . app_no_code_runtime_html_escape($preflightResult) . '" data-execution-mode="' . app_no_code_runtime_html_escape($executionMode) . '" data-generated-button-enabled="' . ($generatedButtonEnabled ? 'true' : 'false') . '" disabled>' . app_no_code_runtime_html_escape($label) . '</button>',
                '<span>' . app_no_code_runtime_html_escape($intent !== '' ? $intent : 'Operator action is declared but not executable in this generated preview.') . '</span>',
                $availabilityText !== '' ? '<small data-extension-slot-availability="' . app_no_code_runtime_html_escape($operationKey) . '" data-availability-reason="' . app_no_code_runtime_html_escape($availabilityReason) . '">' . app_no_code_runtime_html_escape($availabilityText) . '</small>' : '',
                $unavailableReason !== '' ? '<small data-extension-slot-unavailable-reason="' . app_no_code_runtime_html_escape($operationKey) . '">' . app_no_code_runtime_html_escape($unavailableReason) . '</small>' : '',
                $routeBoundaryText !== '' ? '<small data-extension-slot-route-boundary="' . app_no_code_runtime_html_escape($operationKey) . '">' . app_no_code_runtime_html_escape($routeBoundaryText) . '</small>' : '',
                '</div>',
            ]);
        }

        if ($items !== []) {
            return implode("\n", [
                '<div class="no-code-extension-slot-action-panel">',
                implode("\n", $items),
                '</div>',
            ]);
        }
    }

    return '<span>' . app_no_code_runtime_html_escape(implode(' / ', $metaParts)) . '</span>';
}

/**
 * @param list<array<string,mixed>> $actions
 */
function app_no_code_runtime_render_action_intent_draft_html(array $actions): string
{
    if ($actions === []) {
        return '';
    }

    return implode("\n", [
        '<div class="no-code-intent-draft" data-intent-draft-state="idle">',
        '<div class="no-code-intent-draft-heading">',
        '<strong>Action Intent Draft</strong>',
        '<span class="no-code-intent-draft-state-badge" data-intent-draft-state-badge>Idle</span>',
        '</div>',
        '<p>Editing this screen updates a local action-intent preview. It does not execute a server update.</p>',
        '<p class="no-code-intent-draft-summary" data-intent-draft-summary>Draft summary will update when this screen is ready.</p>',
        '<p class="no-code-intent-draft-meta" data-intent-draft-meta>Action metadata will update with the draft.</p>',
        '<p class="no-code-intent-draft-fields" data-intent-draft-fields>Field summary will update with the draft.</p>',
        '<p class="no-code-intent-draft-payload" data-intent-draft-payload>Payload summary will update with the draft.</p>',
        '<div class="no-code-intent-draft-toolbar">',
        '<button type="button" data-intent-draft-copy>Copy draft JSON</button>',
        '<span class="no-code-intent-draft-copy-status" role="status" aria-live="polite" data-intent-draft-copy-status>Draft JSON copy will be available when this screen is ready.</span>',
        '<button type="button" data-runtime-execute disabled data-runtime-execute-state="unavailable">Submit to server</button>',
        '<span class="no-code-runtime-execute-status" role="status" aria-live="polite" data-runtime-execute-status>Server execution is available from an authenticated current or alias preview.</span>',
        '<button type="button" data-runtime-result-refresh disabled>Refresh preview</button>',
        '<span class="no-code-runtime-result-refresh-status" role="status" aria-live="polite" data-runtime-result-refresh-status>Refresh preview is available after server submit. Artifact-key previews stay static; current or alias previews can fetch live runtime data when available.</span>',
        '<button type="button" data-runtime-outbox-detail-copy disabled>Copy outbox path</button>',
        '<a class="no-code-runtime-outbox-detail-link" data-runtime-outbox-detail-link hidden href="">Open outbox detail</a>',
        '<span class="no-code-runtime-outbox-detail-copy-status" role="status" aria-live="polite" data-runtime-outbox-detail-copy-status>Outbox detail path will be available after server submit.</span>',
        '</div>',
        '<div class="no-code-runtime-flow" data-runtime-flow-state="waiting">',
        '<strong>Runtime flow</strong>',
        '<span data-runtime-flow-step="submit" data-state="waiting">Submit waits for a ready draft.</span>',
        '<span data-runtime-flow-step="track" data-state="waiting">Outbox tracking appears after submit.</span>',
        '<span data-runtime-flow-step="refresh" data-state="waiting">Refresh appears after submit.</span>',
        '</div>',
        '<details class="no-code-intent-draft-json" data-intent-draft-json-details>',
        '<summary>Draft JSON</summary>',
        '<pre data-intent-draft-output>Change editable fields to preview the generated action intent draft.</pre>',
        '</details>',
        '</div>',
    ]);
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 */
function app_no_code_runtime_render_screen_summary_html(string $screenKey, array $fields, array $actions): string
{
    $fieldCount = count($fields);
    $actionCount = count($actions);
    $fieldLabel = $fieldCount === 1 ? '1 field' : $fieldCount . ' fields';
    $actionLabel = $actionCount === 1 ? '1 action' : $actionCount . ' actions';

    return implode('', [
        '<div class="no-code-screen-summary" data-screen-summary="' . app_no_code_runtime_html_escape($screenKey) . '"',
        ' data-field-count="' . $fieldCount . '"',
        ' data-action-count="' . $actionCount . '">',
        '<span>' . app_no_code_runtime_html_escape($fieldLabel) . '</span>',
        '<span>' . app_no_code_runtime_html_escape($actionLabel) . '</span>',
        '<code>' . app_no_code_runtime_html_escape($screenKey) . '</code>',
        '</div>',
    ]);
}

function app_no_code_runtime_render_sync_status_hint_html(bool $syncStatusHint): string
{
    if (!$syncStatusHint) {
        return '';
    }

    return '<span class="no-code-sync-status-hint" data-sync-status-hint="visible">Sync status tracked</span>';
}

function app_no_code_runtime_render_sync_error_retry_hint_html(string $hint): string
{
    $hint = trim($hint);
    if ($hint === '') {
        return '';
    }

    return '<span class="no-code-sync-retry-hint" data-sync-retry-hint="operator-outbox">' . app_no_code_runtime_html_escape($hint) . '</span>';
}

/**
 * @param array<string,mixed> $data
 */
function app_no_code_runtime_screen_state(string $screenType, array $data): string
{
    if ($screenType === 'list') {
        $rows = is_array($data['rows'] ?? null) ? $data['rows'] : [];
        return $rows === [] ? 'empty' : 'ready';
    }

    $item = is_array($data['item'] ?? null) ? $data['item'] : [];
    return $item === [] ? 'empty' : 'ready';
}

function app_no_code_runtime_screen_status_message(string $screenState, string $screenType): string
{
    if ($screenState === 'empty') {
        return match ($screenType) {
            'list' => 'Empty',
            'detail' => 'No detail',
            'form' => 'No data',
            default => 'Empty',
        };
    }

    return 'Ready';
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $rows
 */
function app_no_code_runtime_render_list_html(array $fields, array $rows, string $emptyStateMessage = 'No records to show yet.', string $caption = 'Records'): string
{
    $headerCells = [];
    foreach ($fields as $field) {
        $headerCells[] = '<th scope="col">' . app_no_code_runtime_html_escape((string) ($field['label'] ?? $field['field_key'] ?? '')) . '</th>';
    }

    $bodyRows = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $cells = [];
        foreach ($fields as $field) {
            $fieldKey = (string) ($field['field_key'] ?? '');
            $cell = is_array($row[$fieldKey] ?? null) ? $row[$fieldKey] : [];
            $cells[] = '<td>' . app_no_code_runtime_html_escape((string) ($cell['display_value'] ?? '')) . '</td>';
        }

        $bodyRows[] = '<tr>' . implode('', $cells) . '</tr>';
    }

    if ($bodyRows === []) {
        $bodyRows[] = '<tr class="no-code-empty-row"><td colspan="' . max(1, count($fields)) . '">'
            . '<span class="no-code-empty-state">' . app_no_code_runtime_html_escape($emptyStateMessage) . '</span>'
            . '</td></tr>';
    }

    return implode("\n", [
        '<div class="no-code-table-wrap">',
        '<table>',
        '<caption class="no-code-table-caption">' . app_no_code_runtime_html_escape($caption) . '</caption>',
        '<thead><tr>' . implode('', $headerCells) . '</tr></thead>',
        '<tbody>',
        implode("\n", $bodyRows),
        '</tbody>',
        '</table>',
        '</div>',
    ]);
}

/**
 * @param list<array<string,mixed>> $fields
 * @param array<string,mixed> $item
 */
function app_no_code_runtime_render_item_screen_html(string $screenType, array $fields, array $item, string $emptyStateMessage = 'No preview data is available yet.'): string
{
    if ($screenType === 'form') {
        return app_no_code_runtime_render_form_html($fields, $item, $emptyStateMessage);
    }

    $pairs = [];
    foreach ($fields as $field) {
        $fieldKey = (string) ($field['field_key'] ?? '');
        $value = is_array($item[$fieldKey] ?? null) ? $item[$fieldKey] : [];
        $pairs[] = '<dt>' . app_no_code_runtime_html_escape((string) ($field['label'] ?? $fieldKey)) . '</dt>'
            . '<dd>' . app_no_code_runtime_html_escape((string) ($value['display_value'] ?? '')) . '</dd>';
    }

    if ($pairs === []) {
        return '<p class="no-code-empty-state">' . app_no_code_runtime_html_escape($emptyStateMessage) . '</p>';
    }

    return '<dl class="no-code-detail">' . implode('', $pairs) . '</dl>';
}

/**
 * @param list<array<string,mixed>> $fields
 * @param array<string,mixed> $item
 */
function app_no_code_runtime_render_form_html(array $fields, array $item, string $emptyStateMessage = 'No editable data is available yet.'): string
{
    $controls = [];
    foreach ($fields as $field) {
        $fieldKey = (string) ($field['field_key'] ?? '');
        $type = (string) ($field['type'] ?? 'string');
        $value = is_array($item[$fieldKey] ?? null) ? $item[$fieldKey] : [];
        $displayValue = (string) ($value['display_value'] ?? '');
        $readonly = (bool) ($field['readonly'] ?? false);
        $required = (bool) ($field['required'] ?? false);
        $label = app_no_code_runtime_html_escape((string) ($field['label'] ?? $fieldKey));
        $fieldHintId = 'field-hint-' . app_no_code_runtime_html_escape($fieldKey);
        $attrs = ' name="' . app_no_code_runtime_html_escape($fieldKey) . '"'
            . ' id="field-' . app_no_code_runtime_html_escape($fieldKey) . '"'
            . ($readonly ? ' readonly' : '')
            . ($required ? ' required aria-describedby="' . $fieldHintId . '"' : '');

        if ($type === 'text') {
            $control = '<textarea' . $attrs . '>' . app_no_code_runtime_html_escape($displayValue) . '</textarea>';
        } else {
            $inputType = match ($type) {
                'integer', 'decimal' => 'number',
                'boolean' => 'checkbox',
                'date' => 'date',
                'datetime' => 'datetime-local',
                'time' => 'time',
                default => 'text',
            };
            $valueAttr = $inputType === 'checkbox'
                ? ((bool) ($value['value'] ?? false) ? ' checked' : '')
                : ' value="' . app_no_code_runtime_html_escape($displayValue) . '"';
            $control = '<input type="' . $inputType . '"' . $attrs . $valueAttr . '>';
        }

        $labelText = '<span class="no-code-form-label-text">' . $label
            . ($required ? '<span class="no-code-required-badge">Required</span>' : '')
            . '</span>';
        $hint = $required
            ? '<span id="' . $fieldHintId . '" class="no-code-required-hint" data-required-field="' . app_no_code_runtime_html_escape($fieldKey) . '" data-required-label="' . $label . '" data-required-state="pending">Required for the generated action intent.</span>'
            : '';
        $controls[] = '<label for="field-' . app_no_code_runtime_html_escape($fieldKey) . '">' . $labelText . $control . $hint . '</label>';
    }

    if ($controls === []) {
        return '<p class="no-code-empty-state">' . app_no_code_runtime_html_escape($emptyStateMessage) . '</p>';
    }

    return '<form class="no-code-form" method="post">' . implode("\n", $controls) . '</form>';
}

/**
 * @param list<array<string,mixed>> $actions
 */
function app_no_code_runtime_render_actions_html(array $actions, string $screenTitle = '', string $screenKey = ''): string
{
    $buttons = [];
    foreach ($actions as $action) {
        if (!is_array($action)) {
            continue;
        }

        $enabled = (bool) ($action['enabled'] ?? false);
        $actionState = $enabled ? 'ready' : 'disabled';
        $actionKey = (string) ($action['action_key'] ?? '');
        $operationType = (string) ($action['operation_type'] ?? '');
        $submitRoute = (string) ($action['submit_route'] ?? '');
        $submitBindingGate = is_array($action['submit_binding_gate'] ?? null) ? $action['submit_binding_gate'] : [];
        $bindingState = (string) ($submitBindingGate['binding_state'] ?? '');
        $csrfSource = (string) ($submitBindingGate['csrf_source'] ?? '');
        $csrfTokenField = (string) ($submitBindingGate['csrf_token_field'] ?? '');
        $csrfSourceSelector = (string) ($submitBindingGate['csrf_source_selector'] ?? '');
        $csrfTransport = (string) ($submitBindingGate['csrf_transport'] ?? '');
        $clickBindingState = (string) ($submitBindingGate['click_binding_state'] ?? '');
        $submitTrigger = (string) ($submitBindingGate['submit_trigger'] ?? '');
        $networkSubmitEnabled = (bool) ($submitBindingGate['network_submit_enabled'] ?? false);
        $guardedClickInventoryState = (string) ($submitBindingGate['guarded_click_inventory_state'] ?? '');
        $enablementGateSet = (string) ($submitBindingGate['enablement_gate_set'] ?? '');
        $payloadAssembly = (string) ($submitBindingGate['payload_assembly'] ?? '');
        $blockedResponseHandling = (string) ($submitBindingGate['blocked_response_handling'] ?? '');
        $failureDisplayTarget = (string) ($submitBindingGate['failure_display_target'] ?? '');
        $failClosedResult = (string) ($submitBindingGate['fail_closed_result'] ?? '');
        $hintId = app_no_code_runtime_dom_id('no-code-action-hint-' . $screenKey . '-' . $actionKey);
        $disabledReason = $enabled ? '' : 'policy-not-enabled';
        $buttons[] = '<span class="no-code-action-control" data-action-control="' . app_no_code_runtime_html_escape($actionKey) . '">'
            . '<button type="button" data-action-key="' . app_no_code_runtime_html_escape($actionKey) . '"'
            . ' data-operation-key="' . app_no_code_runtime_html_escape((string) ($action['operation_key'] ?? '')) . '"'
            . ' data-operation-type="' . app_no_code_runtime_html_escape($operationType) . '"'
            . ' data-action-enabled="' . ($enabled ? 'true' : 'false') . '"'
            . ' data-action-state="' . $actionState . '"'
            . ' data-action-affordance="keyboard-intent-preview"'
            . ' data-keyboard-activation="enter-space"'
            . ($submitRoute !== '' ? ' data-action-submit-url="' . app_no_code_runtime_html_escape($submitRoute) . '"' : '')
            . ($bindingState !== '' ? ' data-action-binding-state="' . app_no_code_runtime_html_escape($bindingState) . '"' : '')
            . ($csrfSource !== '' ? ' data-action-csrf-source="' . app_no_code_runtime_html_escape($csrfSource) . '"' : '')
            . ($csrfTokenField !== '' ? ' data-action-csrf-token-field="' . app_no_code_runtime_html_escape($csrfTokenField) . '"' : '')
            . ($csrfSourceSelector !== '' ? ' data-action-csrf-source-selector="' . app_no_code_runtime_html_escape($csrfSourceSelector) . '"' : '')
            . ($csrfTransport !== '' ? ' data-action-csrf-transport="' . app_no_code_runtime_html_escape($csrfTransport) . '"' : '')
            . ($clickBindingState !== '' ? ' data-action-click-binding-state="' . app_no_code_runtime_html_escape($clickBindingState) . '"' : '')
            . ($submitTrigger !== '' ? ' data-action-submit-trigger="' . app_no_code_runtime_html_escape($submitTrigger) . '"' : '')
            . ' data-action-network-submit-enabled="' . ($networkSubmitEnabled ? 'true' : 'false') . '"'
            . ($guardedClickInventoryState !== '' ? ' data-action-guarded-click-inventory-state="' . app_no_code_runtime_html_escape($guardedClickInventoryState) . '"' : '')
            . ($enablementGateSet !== '' ? ' data-action-enable-gate-set="' . app_no_code_runtime_html_escape($enablementGateSet) . '"' : '')
            . ($payloadAssembly !== '' ? ' data-action-payload-assembly="' . app_no_code_runtime_html_escape($payloadAssembly) . '"' : '')
            . ($blockedResponseHandling !== '' ? ' data-action-blocked-response-handling="' . app_no_code_runtime_html_escape($blockedResponseHandling) . '"' : '')
            . ($failureDisplayTarget !== '' ? ' data-action-failure-display-target="' . app_no_code_runtime_html_escape($failureDisplayTarget) . '"' : '')
            . ($failClosedResult !== '' ? ' data-action-fail-closed-result="' . app_no_code_runtime_html_escape($failClosedResult) . '"' : '')
            . ($disabledReason !== '' ? ' data-action-disabled-reason="' . app_no_code_runtime_html_escape($disabledReason) . '"' : '')
            . ' aria-describedby="' . app_no_code_runtime_html_escape($hintId) . '"'
            . ' aria-disabled="' . ($enabled ? 'false' : 'true') . '"'
            . ($enabled ? '' : ' disabled')
            . '>' . app_no_code_runtime_html_escape((string) ($action['label'] ?? $actionKey)) . '</button>'
            . '<span id="' . app_no_code_runtime_html_escape($hintId) . '" class="no-code-action-hint" data-action-hint="' . app_no_code_runtime_html_escape($actionKey) . '" data-action-state-hint="' . app_no_code_runtime_html_escape($actionState) . '">'
            . app_no_code_runtime_html_escape(app_no_code_runtime_action_affordance_hint($enabled, $operationType))
            . '</span>'
            . '</span>';
    }

    $label = trim($screenTitle) !== '' ? $screenTitle . ' actions' : 'Screen actions';
    return '<nav class="no-code-actions" aria-label="' . app_no_code_runtime_html_escape($label) . '">' . implode('', $buttons) . '</nav>';
}

function app_no_code_runtime_action_affordance_hint(bool $enabled, string $operationType): string
{
    if (!$enabled) {
        return 'Disabled in this preview: policy checks did not enable this action.';
    }

    $operationLabel = app_no_code_runtime_human_label($operationType);
    $intentLabel = $operationLabel !== '' ? strtolower($operationLabel) . ' intent' : 'action intent';
    return 'Keyboard: Tab to this action, then press Enter or Space to preview the ' . $intentLabel . '.';
}

function app_no_code_runtime_dom_id(string $value): string
{
    $id = preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($value));
    $id = is_string($id) ? trim($id, '-') : '';
    return $id !== '' ? $id : 'no-code-runtime-section';
}

function app_no_code_runtime_html_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_no_code_runtime_preview_css(): string
{
    return implode("\n", [
        ':root { color-scheme: light; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #1f2933; background: #f7f8fa; }',
        'body { margin: 0; }',
        '.no-code-preview { max-width: 1120px; margin: 0 auto; padding: 24px; }',
        '.no-code-preview-header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 24px; }',
        '.no-code-preview-header h1 { margin: 0; font-size: 28px; line-height: 1.2; }',
        '.no-code-preview-header p, .no-code-screen-header p { margin: 6px 0 0; color: #5f6b7a; font-size: 13px; }',
        '.no-code-screen { background: #ffffff; border: 1px solid #d8dee8; border-radius: 8px; padding: 18px; margin-bottom: 18px; }',
        '.no-code-screen-header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px; }',
        '.no-code-screen h2 { margin: 0; font-size: 18px; line-height: 1.25; }',
        '.no-code-screen-summary { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: -6px 0 14px; color: #52606d; font-size: 12px; }',
        '.no-code-screen-summary span, .no-code-screen-summary code { border: 1px solid #d8dee8; border-radius: 999px; padding: 3px 8px; background: #f7f9fb; }',
        '.no-code-screen-summary code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }',
        '.no-code-extension-slots { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; margin: -4px 0 14px; }',
        '.no-code-extension-slot { border: 1px dashed #9fb3c8; border-radius: 6px; background: #f8fafc; padding: 8px 10px; }',
        '.no-code-extension-slot strong { display: block; color: #334e68; font-size: 13px; line-height: 1.35; }',
        '.no-code-extension-slot span { display: block; margin-top: 3px; color: #62748a; font-size: 12px; line-height: 1.35; overflow-wrap: anywhere; }',
        '.no-code-extension-slot-links { display: flex; flex-wrap: wrap; gap: 6px; list-style: none; margin: 7px 0 0; padding: 0; }',
        '.no-code-extension-slot-links a { display: inline-flex; align-items: center; min-height: 28px; border: 1px solid #9fb3c8; border-radius: 6px; background: #ffffff; color: #102a43; padding: 0 9px; font-size: 12px; text-decoration: none; }',
        '.no-code-extension-slot-links a:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '.no-code-extension-slot-status-card { display: grid; gap: 6px; margin: 7px 0 0; }',
        '.no-code-extension-slot-status-item { display: grid; grid-template-columns: minmax(88px, .85fr) minmax(0, 1fr); gap: 6px; align-items: center; border: 1px solid #d8dee8; border-radius: 6px; background: #ffffff; padding: 6px 8px; }',
        '.no-code-extension-slot-status-item dt { color: #62748a; font-size: 11px; line-height: 1.35; }',
        '.no-code-extension-slot-status-item dd { margin: 0; color: #243b53; font-size: 12px; line-height: 1.35; overflow-wrap: anywhere; }',
        '.no-code-extension-slot-status-item[data-extension-slot-status-state="ok"] { border-color: #badbcc; background: #f0f9f4; }',
        '.no-code-extension-slot-status-item[data-extension-slot-status-state="warning"] { border-color: #f0d58c; background: #fff8e5; }',
        '.no-code-extension-slot-status-item[data-extension-slot-status-state="blocked"] { border-color: #f1b0b7; background: #fff5f5; }',
        '.no-code-extension-slot-action-panel { display: grid; gap: 7px; margin: 7px 0 0; }',
        '.no-code-extension-slot-action-item { display: grid; gap: 4px; border: 1px solid #d8dee8; border-radius: 6px; background: #ffffff; padding: 7px 8px; }',
        '.no-code-extension-slot-action-item button { justify-self: flex-start; min-height: 28px; border: 1px solid #c8d1dc; border-radius: 6px; background: #eef1f4; color: #7b8794; padding: 0 9px; font: inherit; font-size: 12px; }',
        '.no-code-extension-slot-action-item span { margin: 0; color: #62748a; font-size: 12px; line-height: 1.35; }',
        '.no-code-extension-slot-action-item[data-extension-slot-action-state="blocked"] { border-color: #f1b0b7; background: #fff5f5; }',
        '.no-code-actions { display: flex; flex-wrap: wrap; gap: 8px; }',
        '.no-code-action-control { display: inline-flex; flex-direction: column; gap: 4px; align-items: flex-start; max-width: 260px; }',
        '.no-code-actions button { min-height: 34px; border: 1px solid #9fb3c8; background: #eef4fb; color: #102a43; border-radius: 6px; padding: 0 12px; font: inherit; }',
        '.no-code-actions button:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '.no-code-actions button:disabled { border-color: #c8d1dc; background: #eef1f4; color: #7b8794; }',
        '.no-code-action-hint { color: #62748a; font-size: 11px; line-height: 1.3; }',
        '.no-code-action-hint[data-action-state-hint="disabled"] { color: #7b8794; }',
        '.no-code-action-feedback { min-height: 20px; margin: -4px 0 12px; color: #486581; font-size: 13px; }',
        '.no-code-action-feedback[data-state="idle"] { color: #62748a; }',
        '.no-code-action-feedback[data-state="working"] { color: #334e68; }',
        '.no-code-action-feedback[data-state="success"] { color: #0f5132; }',
        '.no-code-action-feedback[data-state="error"] { color: #842029; }',
        '.no-code-intent-draft { border: 1px solid #d8dee8; border-radius: 6px; background: #f7f9fb; padding: 10px 12px; margin: -4px 0 14px; }',
        '.no-code-intent-draft-heading { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 4px; }',
        '.no-code-intent-draft strong { display: block; font-size: 13px; color: #334e68; }',
        '.no-code-intent-draft-state-badge { border: 1px solid #c8d1dc; border-radius: 999px; padding: 2px 8px; color: #52606d; background: #f4f6f8; font-size: 11px; line-height: 1.4; }',
        '.no-code-intent-draft-state-badge[data-state="ready"] { border-color: #badbcc; color: #0f5132; background: #f0f9f4; }',
        '.no-code-intent-draft-state-badge[data-state="blocked"] { border-color: #f1b0b7; color: #842029; background: #fff5f5; }',
        '.no-code-intent-draft-state-badge[data-state="empty"] { border-color: #c8d1dc; color: #52606d; background: #f4f6f8; }',
        '.no-code-intent-draft p { margin: 0 0 8px; color: #62748a; font-size: 12px; }',
        '.no-code-intent-draft-summary { font-weight: 600; }',
        '.no-code-intent-draft-meta { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }',
        '.no-code-intent-draft-fields { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }',
        '.no-code-intent-draft-payload { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }',
        '.no-code-intent-draft[data-intent-draft-state="ready"] .no-code-intent-draft-summary { color: #0f5132; }',
        '.no-code-intent-draft[data-intent-draft-state="blocked"] .no-code-intent-draft-summary { color: #842029; }',
        '.no-code-intent-draft-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 0 0 8px; }',
        '.no-code-intent-draft-toolbar button { min-height: 30px; border: 1px solid #9fb3c8; border-radius: 6px; background: #ffffff; color: #102a43; padding: 0 10px; font: inherit; font-size: 12px; }',
        '.no-code-intent-draft-toolbar a { min-height: 28px; display: inline-flex; align-items: center; border: 1px solid #9fb3c8; border-radius: 6px; background: #ffffff; color: #102a43; padding: 0 10px; font: inherit; font-size: 12px; text-decoration: none; }',
        '.no-code-intent-draft-toolbar a[hidden] { display: none; }',
        '.no-code-intent-draft-toolbar button:disabled { border-color: #c8d1dc; background: #eef1f4; color: #7b8794; }',
        '.no-code-intent-draft-toolbar button:focus-visible, .no-code-intent-draft-toolbar a:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '.no-code-intent-draft-copy-status { color: #62748a; font-size: 12px; }',
        '.no-code-runtime-execute-status { color: #62748a; font-size: 12px; }',
        '.no-code-runtime-execute-status[data-state="ready"], .no-code-runtime-execute-status[data-state="success"] { color: #0f5132; }',
        '.no-code-runtime-execute-status[data-state="blocked"], .no-code-runtime-execute-status[data-state="error"] { color: #842029; }',
        '.no-code-runtime-result-refresh-status { color: #62748a; font-size: 12px; }',
        '.no-code-runtime-outbox-detail-copy-status { color: #62748a; font-size: 12px; }',
        '.no-code-runtime-flow { display: grid; grid-template-columns: minmax(120px, 0.7fr) repeat(3, minmax(0, 1fr)); gap: 6px; align-items: stretch; margin: 0 0 8px; }',
        '.no-code-runtime-flow strong, .no-code-runtime-flow span { border: 1px solid #d8dee8; border-radius: 6px; background: #ffffff; color: #52606d; padding: 7px 8px; font-size: 12px; line-height: 1.35; }',
        '.no-code-runtime-flow strong { color: #334e68; background: #f4f6f8; }',
        '.no-code-runtime-flow span[data-state="ready"], .no-code-runtime-flow span[data-state="done"] { border-color: #badbcc; color: #0f5132; background: #f0f9f4; }',
        '.no-code-runtime-flow span[data-state="working"] { border-color: #b6d4fe; color: #084298; background: #eef6ff; }',
        '.no-code-runtime-flow span[data-state="blocked"], .no-code-runtime-flow span[data-state="error"] { border-color: #f1b0b7; color: #842029; background: #fff5f5; }',
        '@media (max-width: 720px) { .no-code-runtime-flow { grid-template-columns: 1fr; } }',
        '.no-code-intent-draft-json { margin: 0; }',
        '.no-code-intent-draft-json summary { cursor: pointer; color: #334e68; font-size: 12px; font-weight: 600; margin-bottom: 6px; }',
        '.no-code-intent-draft-json summary:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '.no-code-intent-draft pre { margin: 0; max-height: 220px; overflow: auto; border: 1px solid #e4e8ef; border-radius: 4px; background: #ffffff; padding: 8px; color: #243b53; font: 12px/1.45 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; white-space: pre-wrap; overflow-wrap: anywhere; }',
        '.no-code-state-badge { align-self: flex-start; border: 1px solid #c8d1dc; border-radius: 999px; padding: 4px 9px; color: #486581; background: #f4f6f8; font-size: 12px; white-space: nowrap; }',
        '.no-code-state-badge[data-state="ready"], .no-code-state-badge[data-state="success"] { border-color: #badbcc; color: #0f5132; background: #f0f9f4; }',
        '.no-code-state-badge[data-state="empty"], .no-code-state-badge[data-state="idle"] { border-color: #c8d1dc; color: #52606d; background: #f4f6f8; }',
        '.no-code-state-badge[data-state="error"] { border-color: #f5c2c7; color: #842029; background: #fff5f5; }',
        '.no-code-sync-status-hint { align-self: flex-start; border: 1px solid #b6d4fe; border-radius: 999px; padding: 4px 9px; color: #084298; background: #eef6ff; font-size: 12px; white-space: nowrap; }',
        '.no-code-sync-retry-hint { align-self: flex-start; border: 1px solid #f0d58c; border-radius: 999px; padding: 4px 9px; color: #7a4d00; background: #fff8e5; font-size: 12px; white-space: nowrap; }',
        '.no-code-table-wrap { overflow-x: auto; }',
        '.no-code-table-caption { color: #52606d; font-size: 12px; margin-bottom: 6px; text-align: left; }',
        '.no-code-row-select-cell { width: 1%; white-space: nowrap; }',
        '.no-code-row-select-button { min-height: 30px; border: 1px solid #9fb3c8; border-radius: 6px; background: #ffffff; color: #102a43; padding: 0 10px; font: inherit; font-size: 12px; }',
        '.no-code-row-select-button[aria-pressed="true"] { border-color: #15803d; background: #dcfce7; color: #166534; }',
        '.no-code-row-select-button:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '.no-code-sort-header { width: 100%; min-height: 28px; border: 0; background: transparent; color: inherit; padding: 0; text-align: inherit; text-transform: inherit; font: inherit; cursor: pointer; }',
        '.no-code-sort-header[data-runtime-sort-state="ascending"], .no-code-sort-header[data-runtime-sort-state="descending"] { color: #0f5132; font-weight: 700; }',
        '.no-code-sort-header[data-runtime-sort-state="ascending"]::after, .no-code-sort-header[data-runtime-sort-state="descending"]::after { display: inline-flex; align-items: center; justify-content: center; width: 1.2em; height: 1.2em; margin-left: 4px; border: 1px solid #badbcc; border-radius: 999px; background: #f0f9f4; color: #0f5132; font-size: 10px; line-height: 1; }',
        '.no-code-sort-header[data-runtime-sort-state="ascending"]::after { content: "^"; }',
        '.no-code-sort-header[data-runtime-sort-state="descending"]::after { content: "v"; }',
        '.no-code-sort-header:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '.no-code-row-selected { background: #f0fdf4; }',
        '.no-code-pagination { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: 10px; color: #52606d; font-size: 12px; }',
        '.no-code-runtime-data-controls { padding: 6px; border: 1px solid #d8dee8; border-radius: 6px; background: #f8fafc; }',
        '.no-code-runtime-data-row-group { display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center; }',
        '.no-code-runtime-data-label { color: #334e68; font-weight: 600; margin-right: 2px; }',
        '.no-code-runtime-data-query-summary { display: inline-flex; flex-wrap: wrap; gap: 4px; align-items: center; color: #465a69; font-size: 0.9em; flex-basis: 100%; }',
        '.no-code-runtime-data-query-summary-label { color: #334e68; font-weight: 600; }',
        '.no-code-runtime-data-query-token { border: 1px solid #d0d7de; border-radius: 999px; background: #ffffff; color: #334e68; padding: 1px 7px; line-height: 1.6; }',
        '.no-code-pagination label { display: inline-flex; gap: 5px; align-items: center; white-space: nowrap; }',
        '.no-code-pagination input, .no-code-pagination select { box-sizing: border-box; width: 72px; min-height: 30px; border: 1px solid #bcccdc; border-radius: 6px; padding: 0 8px; font: inherit; font-size: 12px; background: #ffffff; }',
        '.no-code-pagination input[type="search"], .no-code-pagination input[type="text"] { width: min(160px, 42vw); }',
        '.no-code-pagination select { width: min(132px, 36vw); }',
        '.no-code-pagination button { min-height: 30px; border: 1px solid #9fb3c8; border-radius: 6px; background: #ffffff; color: #102a43; padding: 0 9px; font: inherit; font-size: 12px; }',
        '.no-code-pagination button:disabled { border-color: #c8d1dc; background: #eef1f4; color: #7b8794; }',
        '.no-code-pagination button:focus-visible { outline: 3px solid #b6d4fe; outline-offset: 2px; }',
        '@media (max-width: 640px) { .no-code-runtime-data-controls { align-items: stretch; } .no-code-runtime-data-label, .no-code-runtime-data-query-summary { flex-basis: 100%; } .no-code-runtime-data-row-group { display: flex; flex: 1 1 100%; align-items: stretch; } .no-code-runtime-data-controls label, .no-code-runtime-data-controls button { flex: 1 1 130px; } .no-code-runtime-data-row-group label, .no-code-runtime-data-row-group button { flex: 1 1 120px; } .no-code-runtime-data-controls input[type="search"], .no-code-runtime-data-controls input[type="text"], .no-code-runtime-data-controls input[type="number"], .no-code-runtime-data-controls select { width: 100%; } .no-code-runtime-data-query-token { max-width: 100%; overflow-wrap: anywhere; } }',
        'table { width: 100%; border-collapse: collapse; table-layout: fixed; }',
        'th, td { border-bottom: 1px solid #e4e8ef; padding: 10px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }',
        'th { font-size: 12px; text-transform: uppercase; color: #52606d; background: #f4f6f8; }',
        '.no-code-detail { display: grid; grid-template-columns: minmax(120px, 220px) 1fr; gap: 0; margin: 0; }',
        '.no-code-detail dt, .no-code-detail dd { border-bottom: 1px solid #e4e8ef; padding: 10px; margin: 0; overflow-wrap: anywhere; }',
        '.no-code-detail dt { color: #52606d; background: #f4f6f8; }',
        '.no-code-form { display: grid; gap: 12px; max-width: 720px; }',
        '.no-code-form label { display: grid; gap: 6px; font-size: 13px; color: #52606d; }',
        '.no-code-form-label-text { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }',
        '.no-code-required-badge { border: 1px solid #f0d58c; border-radius: 999px; padding: 1px 6px; color: #7a4d00; background: #fff8e5; font-size: 11px; line-height: 1.4; }',
        '.no-code-required-hint { color: #62748a; font-size: 12px; }',
        '.no-code-required-hint[data-required-state="missing"] { color: #842029; }',
        '.no-code-required-hint[data-required-state="ok"] { color: #0f5132; }',
        '.no-code-form input, .no-code-form textarea { box-sizing: border-box; width: 100%; min-height: 36px; border: 1px solid #bcccdc; border-radius: 6px; padding: 8px 10px; font: inherit; color: #1f2933; background: #ffffff; }',
        '.no-code-form textarea { min-height: 96px; resize: vertical; }',
        '.no-code-empty-state { display: block; padding: 12px 10px; color: #62748a; font-size: 14px; }',
        '@media (max-width: 680px) { .no-code-preview { padding: 14px; } .no-code-preview-header, .no-code-screen-header { display: grid; } .no-code-detail { grid-template-columns: 1fr; } .no-code-detail dt { border-bottom: 0; } }',
    ]);
}

/**
 * @param array<string,mixed> $value
 */
function app_no_code_runtime_json_script_text(array $value): string
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        return '{}';
    }

    return str_replace('</script', '<\/script', $json);
}

function app_no_code_runtime_preview_js(): string
{
    return <<<'JS'
(function () {
  var dataElement = document.getElementById('no-code-runtime-preview-data');
  var preview = {};
  try {
    preview = dataElement ? JSON.parse(dataElement.textContent || '{}') : {};
  } catch (error) {
    preview = {};
  }
  var executionBindingElement = document.getElementById('no-code-runtime-execution-binding');
  var executionBinding = {};
  try {
    executionBinding = executionBindingElement ? JSON.parse(executionBindingElement.textContent || '{}') : {};
  } catch (error) {
    executionBinding = {};
  }

  function hasExecutionBinding() {
    return !!(executionBinding && executionBinding.execution_url && executionBinding.csrf_token);
  }

  function runtimeDataBindingUrl() {
    return executionBinding && executionBinding.runtime_data_url ? String(executionBinding.runtime_data_url) : '';
  }

  function hasRuntimeDataBinding() {
    return runtimeDataBindingUrl() !== '';
  }

  function runtimeDataUrlWithSelectedKey(selectedKey) {
    return runtimeDataUrlWithQuery({
      selected_key: selectedKey || ''
    });
  }

  function runtimeDataUrlWithPagination(page, pageSize) {
    return runtimeDataUrlWithQuery({
      page: page || '',
      page_size: pageSize || ''
    });
  }

  function runtimeDataUrlWithSearch(searchQuery, page, pageSize) {
    return runtimeDataUrlWithQuery({
      q: searchQuery || '',
      page: page || '',
      page_size: pageSize || ''
    });
  }

  function runtimeDataUrlWithFieldFilter(fieldKey, fieldValue, operator, page, pageSize) {
    var params = {
      page: page || '',
      page_size: pageSize || ''
    };
    if (fieldKey && fieldValue) {
      params['filter[' + fieldKey + ']'] = fieldValue;
      if (operator) {
        params['filter_op[' + fieldKey + ']'] = operator;
      }
    }
    return runtimeDataUrlWithQuery(params);
  }

  function runtimeDataUrlWithSort(fieldKey, direction, page, pageSize) {
    var params = {
      page: page || '',
      page_size: pageSize || ''
    };
    if (fieldKey && direction) {
      params['sort[' + fieldKey + ']'] = direction;
    }
    return runtimeDataUrlWithQuery(params);
  }

  function runtimeDataUrlWithCombinedQuery(query) {
    var params = {
      q: query && query.q ? query.q : '',
      page: query && query.page ? query.page : '',
      page_size: query && query.pageSize ? query.pageSize : ''
    };
    if (query && Array.isArray(query.filters)) {
      query.filters.forEach(function (filter) {
        if (filter && filter.field && filter.value) {
          params['filter[' + filter.field + ']'] = filter.value;
          if (filter.operator) {
            params['filter_op[' + filter.field + ']'] = filter.operator;
          }
        }
      });
    }
    if (query && query.filterField && query.filterValue) {
      params['filter[' + query.filterField + ']'] = query.filterValue;
      if (query.filterOperator) {
        params['filter_op[' + query.filterField + ']'] = query.filterOperator;
      }
    }
    if (query && Array.isArray(query.sorts) && query.sorts.length > 0) {
      query.sorts.forEach(function (sort) {
        if (sort && sort.field && sort.direction) {
          params['sort[' + sort.field + ']'] = sort.direction;
        }
      });
    } else if (query && query.sortField && query.sortDirection) {
      params['sort[' + query.sortField + ']'] = query.sortDirection;
    }
    return runtimeDataUrlWithQuery(params);
  }

  function runtimeDataUrlWithQuery(params) {
    var baseUrl = runtimeDataBindingUrl();
    if (!baseUrl) {
      return baseUrl;
    }
    try {
      var url = new URL(baseUrl, window.location.href);
      Object.keys(params || {}).forEach(function (key) {
        var value = params[key];
        if (value !== null && value !== undefined && String(value) !== '') {
          url.searchParams.set(key, String(value));
        }
      });
      return url.pathname + url.search + url.hash;
    } catch (error) {
      var query = Object.keys(params || {}).filter(function (key) {
        return params[key] !== null && params[key] !== undefined && String(params[key]) !== '';
      }).map(function (key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(String(params[key]));
      }).join('&');
      return query ? baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + query : baseUrl;
    }
  }

  function mirrorRuntimeDataQueryInBrowserUrl(requestUrl, mode) {
    if (!window.history || typeof window.history.replaceState !== 'function') {
      return;
    }
    if (mode === 'none') {
      return;
    }
    try {
      var dataUrl = new URL(requestUrl, window.location.href);
      var pageUrl = new URL(window.location.href);
      var keysToDelete = [];
      pageUrl.searchParams.forEach(function (_value, key) {
        if (key === 'selected_key' || key === 'q' || key === 'page' || key === 'page_size' || key.indexOf('filter[') === 0 || key.indexOf('filter_op[') === 0 || key.indexOf('sort[') === 0) {
          keysToDelete.push(key);
        }
      });
      keysToDelete.forEach(function (key) {
        pageUrl.searchParams.delete(key);
      });
      dataUrl.searchParams.forEach(function (value, key) {
        if (key === 'selected_key' || key === 'q' || key === 'page' || key === 'page_size' || key.indexOf('filter[') === 0 || key.indexOf('filter_op[') === 0 || key.indexOf('sort[') === 0) {
          pageUrl.searchParams.set(key, value);
        }
      });
      var nextUrl = pageUrl.pathname + pageUrl.search + pageUrl.hash;
      if (mode === 'push' && typeof window.history.pushState === 'function' && nextUrl !== window.location.pathname + window.location.search + window.location.hash) {
        window.history.pushState(window.history.state, '', nextUrl);
        return;
      }
      window.history.replaceState(window.history.state, '', nextUrl);
    } catch (error) {
      return;
    }
  }

  function runtimeDataQueryFromBrowserUrl() {
    var query = {
      selectedKey: '',
      q: '',
      filterField: '',
      filterValue: '',
      filterOperator: 'contains',
      secondFilterField: '',
      secondFilterValue: '',
      secondFilterOperator: 'contains',
      thirdFilterField: '',
      thirdFilterValue: '',
      thirdFilterOperator: 'contains',
      filters: [],
      filterOperators: {},
      sortField: '',
      sortDirection: '',
      secondSortField: '',
      secondSortDirection: '',
      thirdSortField: '',
      thirdSortDirection: '',
      sorts: [],
      page: '',
      pageSize: ''
    };
    try {
      var pageUrl = new URL(window.location.href);
      query.selectedKey = pageUrl.searchParams.get('selected_key') || '';
      query.q = pageUrl.searchParams.get('q') || '';
      query.page = pageUrl.searchParams.get('page') || '';
      query.pageSize = pageUrl.searchParams.get('page_size') || '';
      pageUrl.searchParams.forEach(function (value, key) {
        var operatorMatch = /^filter_op\[(.+)\]$/.exec(key);
        if (operatorMatch) {
          query.filterOperators[operatorMatch[1] || ''] = value || 'contains';
        }
      });
      pageUrl.searchParams.forEach(function (value, key) {
        var filterMatch = /^filter\[(.+)\]$/.exec(key);
        if (filterMatch) {
          var filterField = filterMatch[1] || '';
          var filterValue = value || '';
          var filterOperator = query.filterOperators[filterField] || 'contains';
          if (filterField && filterValue) {
            query.filters.push({ field: filterField, value: filterValue, operator: filterOperator });
          }
          if (!query.filterField) {
            query.filterField = filterField;
            query.filterValue = filterValue;
            query.filterOperator = filterOperator;
          } else if (!query.secondFilterField && filterField !== query.filterField) {
            query.secondFilterField = filterField;
            query.secondFilterValue = filterValue;
            query.secondFilterOperator = filterOperator;
          } else if (!query.thirdFilterField && filterField !== query.filterField && filterField !== query.secondFilterField) {
            query.thirdFilterField = filterField;
            query.thirdFilterValue = filterValue;
            query.thirdFilterOperator = filterOperator;
          }
        }
        var sortMatch = /^sort\[(.+)\]$/.exec(key);
        if (sortMatch) {
          var sortField = sortMatch[1] || '';
          var sortDirection = value || '';
          if (sortField && sortDirection) {
            query.sorts.push({ field: sortField, direction: sortDirection });
          }
          if (!query.sortField) {
            query.sortField = sortField;
            query.sortDirection = sortDirection;
          } else if (!query.secondSortField && sortField !== query.sortField) {
            query.secondSortField = sortField;
            query.secondSortDirection = sortDirection;
          } else if (!query.thirdSortField && sortField !== query.sortField && sortField !== query.secondSortField) {
            query.thirdSortField = sortField;
            query.thirdSortDirection = sortDirection;
          }
        }
      });
    } catch (error) {
      return query;
    }
    return query;
  }

  function runtimeDataBrowserUrlQueryIsPresent(query) {
    return !!(query && (query.selectedKey || query.q || (Array.isArray(query.filters) && query.filters.length > 0) || (query.filterField && query.filterValue) || (Array.isArray(query.sorts) && query.sorts.length > 0) || (query.sortField && query.sortDirection) || query.page || query.pageSize));
  }

  function htmlEscape(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function displayCellValue(cell) {
    if (cell && typeof cell === 'object' && Object.prototype.hasOwnProperty.call(cell, 'display_value')) {
      return cell.display_value;
    }
    if (cell === null || cell === undefined || typeof cell === 'object') {
      return '';
    }
    return cell;
  }

  function firstRuntimeFieldValue(render, item, fieldKey) {
    if (item && Object.prototype.hasOwnProperty.call(item, fieldKey)) {
      var itemValue = displayCellValue(item[fieldKey]);
      if (itemValue !== '') {
        return itemValue;
      }
    }
    var contractKey = render && render.contract_key ? String(render.contract_key) : '';
    var selectedKey = runtimeSelectedKeyFromRender(render);
    var screens = Array.isArray(preview.screens) ? preview.screens : [];
    if (selectedKey !== '') {
      for (var selectedScreenIndex = 0; selectedScreenIndex < screens.length; selectedScreenIndex += 1) {
        var selectedScreen = screens[selectedScreenIndex] || {};
        if (contractKey && selectedScreen.contract_key && String(selectedScreen.contract_key) !== contractKey) {
          continue;
        }
        var selectedData = selectedScreen.data && typeof selectedScreen.data === 'object' ? selectedScreen.data : {};
        var selectedRows = Array.isArray(selectedData.rows) ? selectedData.rows : [];
        for (var selectedRowIndex = 0; selectedRowIndex < selectedRows.length; selectedRowIndex += 1) {
          var selectedRow = selectedRows[selectedRowIndex] && typeof selectedRows[selectedRowIndex] === 'object' ? selectedRows[selectedRowIndex] : {};
          var selectedRowValue = displayCellValue(selectedRow[fieldKey]);
          if (selectedRowValue !== '' && selectedRowValue === selectedKey) {
            return selectedRowValue;
          }
        }
      }
    }
    for (var screenIndex = 0; screenIndex < screens.length; screenIndex += 1) {
      var screen = screens[screenIndex] || {};
      if (contractKey && screen.contract_key && String(screen.contract_key) !== contractKey) {
        continue;
      }
      var data = screen.data && typeof screen.data === 'object' ? screen.data : {};
      var candidateItem = data.item && typeof data.item === 'object' ? data.item : {};
      if (Object.prototype.hasOwnProperty.call(candidateItem, fieldKey)) {
        var candidateItemValue = displayCellValue(candidateItem[fieldKey]);
        if (candidateItemValue !== '') {
          return candidateItemValue;
        }
      }
      var rows = Array.isArray(data.rows) ? data.rows : [];
      for (var rowIndex = 0; rowIndex < rows.length; rowIndex += 1) {
        var row = rows[rowIndex] && typeof rows[rowIndex] === 'object' ? rows[rowIndex] : {};
        if (Object.prototype.hasOwnProperty.call(row, fieldKey)) {
          var rowValue = displayCellValue(row[fieldKey]);
          if (rowValue !== '') {
            return rowValue;
          }
        }
      }
    }
    return '';
  }

  function runtimeActionKeyField(render) {
    var actions = Array.isArray(render && render.actions) ? render.actions : [];
    for (var actionIndex = 0; actionIndex < actions.length; actionIndex += 1) {
      var fields = Array.isArray(actions[actionIndex] && actions[actionIndex].fields) ? actions[actionIndex].fields : [];
      for (var fieldIndex = 0; fieldIndex < fields.length; fieldIndex += 1) {
        if (fields[fieldIndex] && fields[fieldIndex].role === 'key' && fields[fieldIndex].field_key) {
          return String(fields[fieldIndex].field_key);
        }
      }
    }
    return '';
  }

  function runtimeSelectedKeyFromRender(render) {
    var metadata = render && render.metadata && typeof render.metadata === 'object' ? render.metadata : {};
    var selected = metadata.selected_key && typeof metadata.selected_key === 'object' ? metadata.selected_key : {};
    if (selected.display_value === null || selected.display_value === undefined) {
      return '';
    }
    return String(selected.display_value);
  }

  function findAction(actionKey) {
    var screens = Array.isArray(preview.screens) ? preview.screens : [];
    for (var screenIndex = 0; screenIndex < screens.length; screenIndex += 1) {
      var actions = Array.isArray(screens[screenIndex].actions) ? screens[screenIndex].actions : [];
      for (var actionIndex = 0; actionIndex < actions.length; actionIndex += 1) {
        if (actions[actionIndex] && actions[actionIndex].action_key === actionKey) {
          return actions[actionIndex];
        }
      }
    }

    return null;
  }

  function validationMessage(error) {
    var parts = String(error || '').split(',').map(function (part) {
      return part.trim();
    }).filter(Boolean);

    if (parts.length === 0) {
      return 'Action intent could not be prepared.';
    }

    var messages = parts.map(function (part) {
      var missingPrefix = 'input.missing:';
      var readonlyPrefix = 'input.readonly:';
      if (part.indexOf(missingPrefix) === 0) {
        return 'Required input is missing: ' + part.slice(missingPrefix.length);
      }
      if (part.indexOf(readonlyPrefix) === 0) {
        return 'Input is read-only: ' + part.slice(readonlyPrefix.length);
      }

      return part;
    });

    return Array.from(new Set(messages)).join(', ');
  }

  function actionError(error, intent) {
    return {
      ok: false,
      executed: false,
      intent: intent || null,
      error: error,
      message: validationMessage(error)
    };
  }

  function isEmptyRequiredValue(value) {
    return value === null || (typeof value === 'string' && value.trim() === '');
  }

  function buildActionIntent(action, input) {
    var intent = {
      intent_version: 'no-code-runtime-action-intent-v0',
      runtime_version: preview.runtime_version || '',
      project_key: preview.project_key || '',
      operation_key: action.operation_key || '',
      operation_type: action.operation_type || '',
      payload: {
        key: {},
        input: {},
        filter: {}
      }
    };
    var failed = [];
    var fields = Array.isArray(action.fields) ? action.fields : [];

    fields.forEach(function (field) {
      var fieldKey = field && field.field_key ? field.field_key : '';
      if (!fieldKey) {
        return;
      }

      var present = Object.prototype.hasOwnProperty.call(input, fieldKey);
      if ((!present || isEmptyRequiredValue(input[fieldKey])) && field.required) {
        failed.push('input.missing:' + fieldKey);
        return;
      }
      if (!present) {
        return;
      }

      if (field.role === 'key') {
        intent.payload.key[fieldKey] = input[fieldKey];
      } else if (field.role === 'filter') {
        intent.payload.filter[fieldKey] = input[fieldKey];
      } else if (field.role === 'input') {
        if (!field.client_write) {
          failed.push('input.readonly:' + fieldKey);
          return;
        }
        intent.payload.input[fieldKey] = input[fieldKey];
      }
    });

    if (failed.length > 0) {
      return actionError(Array.from(new Set(failed)).join(', '), intent);
    }

    return {
      ok: true,
      executed: true,
      intent: intent,
      error: '',
      message: ''
    };
  }

  function collectScreenInput(button) {
    var screen = button.closest('.no-code-screen');
    return collectScreenInputFromScreen(screen);
  }

  function collectScreenInputFromScreen(screen) {
    var input = {};
    if (!screen) {
      return input;
    }

    screen.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (control) {
      if (control.type === 'checkbox') {
        input[control.name] = control.checked;
      } else {
        input[control.name] = control.value;
      }
    });

    return input;
  }

  function firstScreenAction(screen) {
    if (!screen) {
      return null;
    }
    var actionButton = screen.querySelector('.no-code-actions button[data-action-key]');
    if (!actionButton) {
      return null;
    }

    return findAction(actionButton.getAttribute('data-action-key') || '');
  }

  function findNamedControl(screen, fieldKey) {
    if (!screen) {
      return null;
    }
    var controls = screen.querySelectorAll('input[name], textarea[name], select[name]');
    for (var index = 0; index < controls.length; index += 1) {
      if (controls[index].name === fieldKey) {
        return controls[index];
      }
    }
    return null;
  }

  function findActionField(action, fieldKey) {
    var fields = action && Array.isArray(action.fields) ? action.fields : [];
    for (var index = 0; index < fields.length; index += 1) {
      if (fields[index] && fields[index].field_key === fieldKey) {
        return fields[index];
      }
    }
    return null;
  }

  function requiredFieldRoleLabel(field) {
    var role = field && field.role ? field.role : 'field';
    if (role === 'key') {
      return 'key value';
    }
    if (role === 'filter') {
      return 'filter value';
    }
    if (role === 'input') {
      return 'input value';
    }
    return 'field value';
  }

  function requiredFieldDisplayLabel(hint, fieldKey) {
    var label = hint.getAttribute('data-required-label') || '';
    return label || fieldKey || 'field';
  }

  function writeRequiredFieldHints(screen, draft, action) {
    if (!screen) {
      return;
    }
    var checks = draft && Array.isArray(draft.draft_checks) ? draft.draft_checks : [];
    screen.querySelectorAll('[data-required-field]').forEach(function (hint) {
      var fieldKey = hint.getAttribute('data-required-field') || '';
      var actionField = findActionField(action, fieldKey);
      var roleLabel = requiredFieldRoleLabel(actionField);
      var displayLabel = requiredFieldDisplayLabel(hint, fieldKey);
      var control = findNamedControl(screen, fieldKey);
      var missing = checks.indexOf('key.missing:' + fieldKey) !== -1
        || checks.indexOf('input.missing:' + fieldKey) !== -1
        || checks.indexOf('filter.missing:' + fieldKey) !== -1;
      if (missing) {
        hint.textContent = 'Missing required ' + roleLabel + ' for generated action intent: ' + displayLabel + '.';
        hint.setAttribute('data-required-state', 'missing');
        return;
      }
      if (control && !isEmptyRequiredValue(control.type === 'checkbox' ? control.checked : control.value)) {
        hint.textContent = 'Required ' + roleLabel + ' is present for generated action intent: ' + displayLabel + '.';
        hint.setAttribute('data-required-state', 'ok');
        return;
      }
      hint.textContent = 'Required ' + roleLabel + ' for generated action intent: ' + displayLabel + '.';
      hint.setAttribute('data-required-state', 'pending');
    });
  }

  function buildActionIntentDraft(action, input) {
    var draft = {
      intent_version: 'no-code-runtime-action-intent-v0',
      runtime_version: preview.runtime_version || '',
      project_key: preview.project_key || '',
      action_key: action.action_key || '',
      operation_key: action.operation_key || '',
      operation_type: action.operation_type || '',
      availability: action.availability || (action.enabled ? 'enabled' : 'disabled'),
      executable: !!action.enabled,
      draft_checks: [],
      policy_failed_checks: Array.isArray(action.failed_checks) ? action.failed_checks : [],
      payload: {
        key: {},
        input: {},
        filter: {}
      }
    };
    if (!action.enabled) {
      draft.draft_checks.push('action.disabled');
    }
    var fields = Array.isArray(action.fields) ? action.fields : [];
    fields.forEach(function (field) {
      var fieldKey = field && field.field_key ? field.field_key : '';
      if (!fieldKey) {
        return;
      }

      var present = Object.prototype.hasOwnProperty.call(input, fieldKey);
      if ((!present || isEmptyRequiredValue(input[fieldKey])) && field.required) {
        draft.draft_checks.push((field.role || 'input') + '.missing:' + fieldKey);
        return;
      }
      if (!present) {
        return;
      }

      if (field.role === 'key') {
        draft.payload.key[fieldKey] = input[fieldKey];
      } else if (field.role === 'filter') {
        draft.payload.filter[fieldKey] = input[fieldKey];
      } else if (field.role === 'input') {
        if (!field.client_write) {
          draft.draft_checks.push('input.readonly:' + fieldKey);
          return;
        }
        draft.payload.input[fieldKey] = input[fieldKey];
      }
    });
    draft.draft_checks = Array.from(new Set(draft.draft_checks));

    return draft;
  }

  function actionFieldSummary(action) {
    var fields = Array.isArray(action.fields) ? action.fields : [];
    var buckets = {
      key: [],
      input: [],
      filter: []
    };
    fields.forEach(function (field) {
      var fieldKey = field && field.field_key ? field.field_key : '';
      if (!fieldKey) {
        return;
      }
      var role = field.role === 'key' || field.role === 'filter' ? field.role : 'input';
      buckets[role].push(fieldKey);
    });
    function list(values) {
      return values.length > 0 ? values.join(', ') : '(none)';
    }
    return 'Fields: key=' + list(buckets.key)
      + ' | input=' + list(buckets.input)
      + ' | filter=' + list(buckets.filter);
  }

  function writeIntentDraft(screen) {
    var draftOutput = screen ? screen.querySelector('[data-intent-draft-output]') : null;
    var draftSummary = screen ? screen.querySelector('[data-intent-draft-summary]') : null;
    var draftMeta = screen ? screen.querySelector('[data-intent-draft-meta]') : null;
    var draftFields = screen ? screen.querySelector('[data-intent-draft-fields]') : null;
    var draftPayload = screen ? screen.querySelector('[data-intent-draft-payload]') : null;
    var copyStatus = screen ? screen.querySelector('[data-intent-draft-copy-status]') : null;
    var stateBadge = screen ? screen.querySelector('[data-intent-draft-state-badge]') : null;
    var draftRoot = screen ? screen.querySelector('.no-code-intent-draft') : null;
    if (!draftOutput || !draftRoot) {
      return;
    }

    var action = firstScreenAction(screen);
    if (!action) {
      draftOutput.textContent = 'No action metadata is available for this screen.';
      if (draftSummary) {
        draftSummary.textContent = 'No action metadata is available for this screen.';
      }
      if (draftMeta) {
        draftMeta.textContent = 'No action metadata is available for this screen.';
      }
      if (draftFields) {
        draftFields.textContent = 'No action fields are available for this screen.';
      }
      if (draftPayload) {
        draftPayload.textContent = 'No payload is available for this screen.';
      }
      if (copyStatus) {
        copyStatus.textContent = 'No draft JSON is available to copy.';
      }
      if (stateBadge) {
        stateBadge.textContent = 'Empty';
        stateBadge.setAttribute('data-state', 'empty');
      }
      draftRoot.setAttribute('data-intent-draft-state', 'empty');
      return;
    }

    var draft = buildActionIntentDraft(action, collectScreenInputFromScreen(screen));
    var draftChecks = Array.isArray(draft.draft_checks) ? draft.draft_checks : [];
    var policyChecks = Array.isArray(draft.policy_failed_checks) ? draft.policy_failed_checks : [];
    var hasBlockingChecks = draftChecks.length > 0 || policyChecks.length > 0;
    writeRequiredFieldHints(screen, draft, action);
    writeRuntimeExecuteAvailability(screen, draft, hasBlockingChecks);
    draftOutput.textContent = JSON.stringify(draft, null, 2);
    if (draftSummary) {
      var summaryChecks = [];
      if (draftChecks.length > 0) {
        summaryChecks.push(draftChecks.join(', '));
      }
      if (policyChecks.length > 0) {
        summaryChecks.push('policy: ' + policyChecks.join(', '));
      }
      draftSummary.textContent = !hasBlockingChecks
        ? 'Ready draft: no blocking checks found.'
        : 'Blocked draft: ' + summaryChecks.join('; ');
    }
    if (draftMeta) {
      draftMeta.textContent = 'Action: ' + (draft.action_key || '(unknown)')
        + ' | Operation: ' + (draft.operation_key || '(unknown)')
        + ' | Type: ' + (draft.operation_type || '(unknown)');
    }
    if (draftFields) {
      draftFields.textContent = actionFieldSummary(action);
    }
    if (draftPayload) {
      var payload = draft.payload || {};
      var keyCount = Object.keys(payload.key || {}).length;
      var inputCount = Object.keys(payload.input || {}).length;
      var filterCount = Object.keys(payload.filter || {}).length;
      draftPayload.textContent = 'Payload: ' + keyCount + ' key fields'
        + ' | ' + inputCount + ' input fields'
        + ' | ' + filterCount + ' filter fields';
    }
    if (copyStatus) {
      copyStatus.textContent = 'Draft JSON is ready to copy.';
    }
    var draftState = hasBlockingChecks ? 'blocked' : 'ready';
    if (stateBadge) {
      stateBadge.textContent = draftState.charAt(0).toUpperCase() + draftState.slice(1);
      stateBadge.setAttribute('data-state', draftState);
    }
    draftRoot.setAttribute('data-intent-draft-state', draftState);
  }

  function copyIntentDraft(button) {
    var draftRoot = button.closest('.no-code-intent-draft');
    var draftOutput = draftRoot ? draftRoot.querySelector('[data-intent-draft-output]') : null;
    var copyStatus = draftRoot ? draftRoot.querySelector('[data-intent-draft-copy-status]') : null;
    var draftText = draftOutput ? draftOutput.textContent || '' : '';
    if (!draftText) {
      if (copyStatus) {
        copyStatus.textContent = 'No draft JSON is available to copy.';
      }
      return;
    }
    if (!navigator.clipboard || typeof navigator.clipboard.writeText !== 'function') {
      if (copyStatus) {
        copyStatus.textContent = 'Clipboard is unavailable; select the JSON manually.';
      }
      return;
    }
    navigator.clipboard.writeText(draftText).then(function () {
      if (copyStatus) {
        copyStatus.textContent = 'Draft JSON copied.';
      }
    }).catch(function () {
      if (copyStatus) {
        copyStatus.textContent = 'Copy failed; select the JSON manually.';
      }
    });
  }

  function writeRuntimeOutboxDetailCopy(screen, detailPath) {
    var copyButton = screen ? screen.querySelector('[data-runtime-outbox-detail-copy]') : null;
    var detailLink = screen ? screen.querySelector('[data-runtime-outbox-detail-link]') : null;
    var copyStatus = screen ? screen.querySelector('[data-runtime-outbox-detail-copy-status]') : null;
    if (copyButton) {
      copyButton.disabled = !detailPath;
      if (detailPath) {
        copyButton.setAttribute('data-runtime-outbox-detail-path', detailPath);
      } else {
        copyButton.removeAttribute('data-runtime-outbox-detail-path');
      }
    }
    if (detailLink) {
      if (detailPath) {
        detailLink.hidden = false;
        detailLink.setAttribute('href', detailPath);
        detailLink.setAttribute('data-runtime-outbox-detail-path', detailPath);
      } else {
        detailLink.hidden = true;
        detailLink.setAttribute('href', '');
        detailLink.removeAttribute('data-runtime-outbox-detail-path');
      }
    }
    if (copyStatus) {
      copyStatus.textContent = detailPath
        ? 'Outbox detail path is ready to copy.'
        : 'Outbox detail path will be available after server submit.';
    }
  }

  function writeRuntimeFlow(screen, state, detailPath) {
    var flow = screen ? screen.querySelector('[data-runtime-flow-state]') : null;
    if (!flow) {
      return;
    }
    var submit = flow.querySelector('[data-runtime-flow-step="submit"]');
    var track = flow.querySelector('[data-runtime-flow-step="track"]');
    var refresh = flow.querySelector('[data-runtime-flow-step="refresh"]');
    flow.setAttribute('data-runtime-flow-state', state);
    var setStep = function (element, stepState, text) {
      if (!element) {
        return;
      }
      element.setAttribute('data-state', stepState);
      element.textContent = text;
    };
    if (state === 'ready') {
      setStep(submit, 'ready', 'Submit is ready for this draft.');
      setStep(track, 'waiting', 'Outbox tracking appears after submit.');
      setStep(refresh, 'waiting', 'Refresh appears after submit.');
      return;
    }
    if (state === 'working') {
      setStep(submit, 'working', 'Submitting this draft to the server.');
      setStep(track, 'waiting', 'Waiting for outbox acceptance.');
      setStep(refresh, 'waiting', 'Refresh appears after submit.');
      return;
    }
    if (state === 'accepted') {
      setStep(submit, 'done', 'Submit accepted.');
      setStep(track, detailPath ? 'ready' : 'waiting', detailPath ? 'Open or copy the outbox detail.' : 'Outbox item accepted; detail path unavailable.');
      setStep(refresh, 'ready', 'Process the item, then refresh this screen.');
      return;
    }
    if (state === 'tracking') {
      setStep(submit, 'done', 'Submit accepted.');
      setStep(track, 'working', 'Checking sync outbox status.');
      setStep(refresh, 'ready', 'Refresh remains available after status checks.');
      return;
    }
    if (state === 'timeout') {
      setStep(submit, 'done', 'Submit accepted.');
      setStep(track, 'waiting', 'Status is still queued after bounded checks.');
      setStep(refresh, 'ready', 'Refresh this screen or open the outbox detail.');
      return;
    }
    if (state === 'complete') {
      setStep(submit, 'done', 'Submit accepted.');
      setStep(track, 'done', 'Sync outbox item is done.');
      setStep(refresh, 'ready', 'Refresh this screen to load the latest data.');
      return;
    }
    if (state === 'needs_review') {
      setStep(submit, 'done', 'Submit accepted.');
      setStep(track, 'error', 'Sync outbox item needs operator review.');
      setStep(refresh, 'ready', 'Refresh remains available after review.');
      return;
    }
    if (state === 'blocked') {
      setStep(submit, 'blocked', 'Resolve draft blockers before submit.');
      setStep(track, 'waiting', 'Outbox tracking appears after submit.');
      setStep(refresh, 'waiting', 'Refresh appears after submit.');
      return;
    }
    if (state === 'error') {
      setStep(submit, 'error', 'Submit did not complete.');
      setStep(track, 'waiting', 'Outbox tracking is unavailable.');
      setStep(refresh, 'waiting', 'Refresh appears after a successful submit.');
      return;
    }
    setStep(submit, 'waiting', 'Submit waits for a ready draft.');
    setStep(track, 'waiting', 'Outbox tracking appears after submit.');
    setStep(refresh, 'waiting', 'Refresh appears after submit.');
  }

  function writeRuntimeResultRefresh(screen, enabled) {
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    var refreshStatus = screen ? screen.querySelector('[data-runtime-result-refresh-status]') : null;
    if (!refreshButton) {
      return;
    }
    refreshButton.disabled = !enabled;
    refreshButton.setAttribute('data-runtime-result-refresh-state', enabled ? 'ready' : 'waiting');
    if (refreshStatus) {
      if (enabled && hasRuntimeDataBinding()) {
        refreshStatus.textContent = 'Refresh preview fetches read-only live runtime data for this current or alias selection.';
      } else {
        refreshStatus.textContent = enabled
          ? 'Refresh preview reloads this generated preview artifact; artifact-key previews do not fetch live runtime data.'
          : 'Refresh preview is available after server submit. Artifact-key previews stay static; current or alias previews can fetch live runtime data when available.';
      }
    }
  }

  function captureRuntimeSubmitState(screen) {
    if (!screen) {
      return null;
    }
    var executeButton = screen.querySelector('[data-runtime-execute]');
    var executeStatus = screen.querySelector('[data-runtime-execute-status]');
    var feedback = screen.querySelector('.no-code-action-feedback');
    var copyButton = screen.querySelector('[data-runtime-outbox-detail-copy]');
    var detailLink = screen.querySelector('[data-runtime-outbox-detail-link]');
    var copyStatus = screen.querySelector('[data-runtime-outbox-detail-copy-status]');
    var flow = screen.querySelector('[data-runtime-flow-state]');
    var state = {
      executeState: executeButton ? executeButton.getAttribute('data-runtime-execute-state') || '' : '',
      executeDisabled: executeButton ? executeButton.disabled : true,
      statusText: executeStatus ? executeStatus.textContent || '' : '',
      statusState: executeStatus ? executeStatus.getAttribute('data-state') || '' : '',
      statusOutboxDetailPath: executeStatus ? executeStatus.getAttribute('data-runtime-outbox-detail-path') || '' : '',
      statusOutboxPath: executeStatus ? executeStatus.getAttribute('data-runtime-outbox-status-path') || '' : '',
      statusPollState: executeStatus ? executeStatus.getAttribute('data-runtime-outbox-status-poll-state') || '' : '',
      statusPollCount: executeStatus ? executeStatus.getAttribute('data-runtime-outbox-status-poll-count') || '' : '',
      feedbackText: feedback ? feedback.textContent || '' : '',
      feedbackState: feedback ? feedback.getAttribute('data-state') || '' : '',
      feedbackOutboxDetailPath: feedback ? feedback.getAttribute('data-runtime-outbox-detail-path') || '' : '',
      copyDisabled: copyButton ? copyButton.disabled : true,
      copyPath: copyButton ? copyButton.getAttribute('data-runtime-outbox-detail-path') || '' : '',
      detailLinkHidden: detailLink ? detailLink.hidden : true,
      detailLinkHref: detailLink ? detailLink.getAttribute('href') || '' : '',
      detailLinkPath: detailLink ? detailLink.getAttribute('data-runtime-outbox-detail-path') || '' : '',
      copyStatusText: copyStatus ? copyStatus.textContent || '' : '',
      flowState: flow ? flow.getAttribute('data-runtime-flow-state') || '' : '',
      flowSteps: []
    };
    if (flow) {
      flow.querySelectorAll('[data-runtime-flow-step]').forEach(function (step) {
        state.flowSteps.push({
          key: step.getAttribute('data-runtime-flow-step') || '',
          state: step.getAttribute('data-state') || '',
          text: step.textContent || ''
        });
      });
    }
    return state.statusPollState || state.statusOutboxDetailPath || state.feedbackOutboxDetailPath || state.executeState === 'success'
      ? state
      : null;
  }

  function restoreRuntimeSubmitState(screen, state) {
    if (!screen || !state) {
      return;
    }
    var executeButton = screen.querySelector('[data-runtime-execute]');
    var executeStatus = screen.querySelector('[data-runtime-execute-status]');
    var feedback = screen.querySelector('.no-code-action-feedback');
    var copyButton = screen.querySelector('[data-runtime-outbox-detail-copy]');
    var detailLink = screen.querySelector('[data-runtime-outbox-detail-link]');
    var copyStatus = screen.querySelector('[data-runtime-outbox-detail-copy-status]');
    var flow = screen.querySelector('[data-runtime-flow-state]');
    if (executeButton && state.executeState) {
      executeButton.disabled = state.executeDisabled;
      executeButton.setAttribute('data-runtime-execute-state', state.executeState);
    }
    if (executeStatus && state.statusText) {
      executeStatus.textContent = state.statusText;
      if (state.statusState) {
        executeStatus.setAttribute('data-state', state.statusState);
      }
      if (state.statusOutboxDetailPath) {
        executeStatus.setAttribute('data-runtime-outbox-detail-path', state.statusOutboxDetailPath);
      }
      if (state.statusOutboxPath) {
        executeStatus.setAttribute('data-runtime-outbox-status-path', state.statusOutboxPath);
      }
      if (state.statusPollState) {
        executeStatus.setAttribute('data-runtime-outbox-status-poll-state', state.statusPollState);
      }
      if (state.statusPollCount) {
        executeStatus.setAttribute('data-runtime-outbox-status-poll-count', state.statusPollCount);
      }
    }
    if (feedback && state.feedbackText) {
      feedback.textContent = state.feedbackText;
      if (state.feedbackState) {
        feedback.setAttribute('data-state', state.feedbackState);
      }
      if (state.feedbackOutboxDetailPath) {
        feedback.setAttribute('data-runtime-outbox-detail-path', state.feedbackOutboxDetailPath);
      }
    }
    if (copyButton) {
      copyButton.disabled = state.copyDisabled;
      if (state.copyPath) {
        copyButton.setAttribute('data-runtime-outbox-detail-path', state.copyPath);
      }
    }
    if (detailLink) {
      detailLink.hidden = state.detailLinkHidden;
      detailLink.setAttribute('href', state.detailLinkHref || '');
      if (state.detailLinkPath) {
        detailLink.setAttribute('data-runtime-outbox-detail-path', state.detailLinkPath);
      }
    }
    if (copyStatus && state.copyStatusText) {
      copyStatus.textContent = state.copyStatusText;
    }
    if (flow && state.flowState) {
      flow.setAttribute('data-runtime-flow-state', state.flowState);
      state.flowSteps.forEach(function (snapshot) {
        var step = flow.querySelector('[data-runtime-flow-step="' + snapshot.key + '"]');
        if (!step) {
          return;
        }
        step.setAttribute('data-state', snapshot.state);
        step.textContent = snapshot.text;
      });
    }
  }

  function runtimeRefreshStorageKey(screen) {
    var screenKey = screen ? screen.getAttribute('data-screen-key') || 'screen' : 'screen';
    return 'no-code-runtime-refresh:' + window.location.pathname + ':' + screenKey;
  }

  function runtimeSessionStorage() {
    try {
      return window.sessionStorage || null;
    } catch (_error) {
      return null;
    }
  }

  function saveRuntimeRefreshState(screen) {
    var storage = runtimeSessionStorage();
    if (!storage || !screen) {
      return;
    }
    var values = {};
    screen.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (control) {
      if (control.type === 'checkbox') {
        values[control.name] = control.checked ? '1' : '0';
        return;
      }
      values[control.name] = control.value;
    });
    try {
      storage.setItem(runtimeRefreshStorageKey(screen), JSON.stringify(values));
    } catch (_error) {
      // Ignore storage failures; the refresh action should still work.
    }
  }

  function restoreRuntimeRefreshState(screen) {
    var storage = runtimeSessionStorage();
    if (!storage || !screen) {
      return;
    }
    var storageKey = runtimeRefreshStorageKey(screen);
    var raw = storage.getItem(storageKey);
    if (!raw) {
      return;
    }
    storage.removeItem(storageKey);
    var values;
    try {
      values = JSON.parse(raw);
    } catch (_error) {
      return;
    }
    if (!values || typeof values !== 'object') {
      return;
    }
    screen.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (control) {
      if (!Object.prototype.hasOwnProperty.call(values, control.name)) {
        return;
      }
      if (control.type === 'checkbox') {
        control.checked = values[control.name] === '1';
        return;
      }
      control.value = values[control.name];
    });
  }

  function screenStateFromRender(render) {
    var screenType = render && render.screen_type ? String(render.screen_type) : '';
    var data = render && render.data && typeof render.data === 'object' ? render.data : {};
    if (screenType === 'list') {
      return Array.isArray(data.rows) && data.rows.length > 0 ? 'ready' : 'empty';
    }
    return data.item && typeof data.item === 'object' && Object.keys(data.item).length > 0 ? 'ready' : 'empty';
  }

  function screenStatusMessage(state, screenType) {
    if (state !== 'empty') {
      return 'Ready';
    }
    if (screenType === 'detail') {
      return 'No detail';
    }
    if (screenType === 'form') {
      return 'No data';
    }
    return 'Empty';
  }

  function renderRuntimeListBody(render) {
    var fields = Array.isArray(render.fields) ? render.fields : [];
    var data = render.data && typeof render.data === 'object' ? render.data : {};
    var metadata = render.metadata && typeof render.metadata === 'object' ? render.metadata : {};
    var pagination = metadata.pagination && typeof metadata.pagination === 'object' ? metadata.pagination : null;
    var rows = Array.isArray(data.rows) ? data.rows : [];
    var caption = (render.screen_title || 'Records') + ' records';
    var keyField = runtimeActionKeyField(render);
    var selectedKey = runtimeSelectedKeyFromRender(render);
    var canSelectRows = hasRuntimeDataBinding() && keyField && rows.length > 0;
    var header = fields.map(function (field) {
      var fieldKey = field.field_key || '';
      var label = field.label || fieldKey || '';
      if (hasRuntimeDataBinding() && fieldKey) {
        return '<th scope="col" aria-sort="none"><button type="button" class="no-code-sort-header" data-runtime-sort-header data-runtime-sort-field-key="' + htmlEscape(fieldKey) + '" data-runtime-sort-state="none" aria-label="Sort by ' + htmlEscape(label) + '">' + htmlEscape(label) + '</button></th>';
      }
      return '<th scope="col">' + htmlEscape(label) + '</th>';
    }).join('');
    if (canSelectRows) {
      header = '<th scope="col" class="no-code-row-select-cell">Select</th>' + header;
    }
    var bodyRows = rows.map(function (row) {
      var rowKey = displayCellValue(row ? row[keyField] : null);
      var isSelected = rowKey !== '' && rowKey === selectedKey;
      var selectCell = canSelectRows
        ? '<td class="no-code-row-select-cell"><button type="button" class="no-code-row-select-button" data-runtime-row-select data-runtime-selected-key="' + htmlEscape(rowKey) + '" aria-pressed="' + (isSelected ? 'true' : 'false') + '"' + (rowKey === '' ? ' disabled' : '') + '>' + (isSelected ? 'Selected' : 'Select') + '</button></td>'
        : '';
      return '<tr' + (isSelected ? ' class="no-code-row-selected"' : '') + (rowKey !== '' ? ' data-runtime-row-key="' + htmlEscape(rowKey) + '"' : '') + '>' + selectCell + fields.map(function (field) {
        var fieldKey = field.field_key || '';
        return '<td>' + htmlEscape(displayCellValue(row ? row[fieldKey] : null)) + '</td>';
      }).join('') + '</tr>';
    });
    if (bodyRows.length === 0) {
      bodyRows.push('<tr class="no-code-empty-row"><td colspan="' + Math.max(1, fields.length + (canSelectRows ? 1 : 0)) + '"><span class="no-code-empty-state">' + htmlEscape(render.empty_state_message || 'No records to show yet.') + '</span></td></tr>');
    }
    return '<div class="no-code-table-wrap"><table><caption class="no-code-table-caption">' + htmlEscape(caption) + '</caption><thead><tr>' + header + '</tr></thead><tbody>' + bodyRows.join('') + '</tbody></table></div>' + renderRuntimePaginationControls(pagination, fields);
  }

  function renderRuntimeFilterControls(fields) {
    var fieldOptions = (Array.isArray(fields) ? fields : []).filter(function (field) {
      return field && field.field_key;
    }).map(function (field) {
      var fieldKey = field.field_key || '';
      var fieldType = field.type || 'string';
      return '<option value="' + htmlEscape(fieldKey) + '" data-runtime-field-type="' + htmlEscape(fieldType) + '">' + htmlEscape(field.label || fieldKey) + '</option>';
    }).join('');
    if (!fieldOptions) {
      return '';
    }
    var operatorOptions = '<option value="contains">Contains</option><option value="eq">Equals</option><option value="gt" data-runtime-filter-ordered>Greater than</option><option value="gte" data-runtime-filter-ordered>Greater or equal</option><option value="lt" data-runtime-filter-ordered>Less than</option><option value="lte" data-runtime-filter-ordered>Less or equal</option>';
    return '<span class="no-code-runtime-data-row-group" data-runtime-filter-primary><label>Filter <select data-runtime-filter-field>' + fieldOptions + '</select></label><label>Op <select data-runtime-filter-operator>' + operatorOptions + '</select></label><label>Value <input type="text" maxlength="128" data-runtime-filter-value></label></span><span class="no-code-runtime-data-row-group" data-runtime-filter-extra="secondary" hidden><label>Filter 2 <select data-runtime-filter-field-secondary><option value="">None</option>' + fieldOptions + '</select></label><label>Op 2 <select data-runtime-filter-operator-secondary>' + operatorOptions + '</select></label><label>Value 2 <input type="text" maxlength="128" data-runtime-filter-value-secondary></label><button type="button" data-runtime-filter-remove="secondary">Remove filter 2</button></span><span class="no-code-runtime-data-row-group" data-runtime-filter-extra="tertiary" hidden><label>Filter 3 <select data-runtime-filter-field-tertiary><option value="">None</option>' + fieldOptions + '</select></label><label>Op 3 <select data-runtime-filter-operator-tertiary>' + operatorOptions + '</select></label><label>Value 3 <input type="text" maxlength="128" data-runtime-filter-value-tertiary></label><button type="button" data-runtime-filter-remove="tertiary">Remove filter 3</button></span><button type="button" data-runtime-filter-add>Add filter</button><button type="button" data-runtime-filter-submit>Filter</button>';
  }

  function renderRuntimeSortControls(fields) {
    var fieldOptions = (Array.isArray(fields) ? fields : []).filter(function (field) {
      return field && field.field_key;
    }).map(function (field) {
      var fieldKey = field.field_key || '';
      return '<option value="' + htmlEscape(fieldKey) + '">' + htmlEscape(field.label || fieldKey) + '</option>';
    }).join('');
    if (!fieldOptions) {
      return '';
    }
    return '<span class="no-code-runtime-data-row-group" data-runtime-sort-primary><label>Sort <select data-runtime-sort-field>' + fieldOptions + '</select></label><label>Direction <select data-runtime-sort-direction><option value="asc">Asc</option><option value="desc">Desc</option></select></label></span><span class="no-code-runtime-data-row-group" data-runtime-sort-extra="secondary" hidden><label>Sort 2 <select data-runtime-sort-field-secondary><option value="">None</option>' + fieldOptions + '</select></label><label>Direction 2 <select data-runtime-sort-direction-secondary><option value="asc">Asc</option><option value="desc">Desc</option></select></label><button type="button" data-runtime-sort-remove="secondary">Remove sort 2</button></span><span class="no-code-runtime-data-row-group" data-runtime-sort-extra="tertiary" hidden><label>Sort 3 <select data-runtime-sort-field-tertiary><option value="">None</option>' + fieldOptions + '</select></label><label>Direction 3 <select data-runtime-sort-direction-tertiary><option value="asc">Asc</option><option value="desc">Desc</option></select></label><button type="button" data-runtime-sort-remove="tertiary">Remove sort 3</button></span><button type="button" data-runtime-sort-add>Add sort</button><button type="button" data-runtime-sort-submit>Sort</button>';
  }

  function renderRuntimePaginationControls(pagination, fields) {
    if (!hasRuntimeDataBinding()) {
      return '';
    }
    var filterControls = renderRuntimeFilterControls(fields);
    var sortControls = renderRuntimeSortControls(fields);
    var controlsAttributes = 'class="no-code-pagination no-code-runtime-data-controls" role="group" aria-label="Runtime data controls" data-runtime-data-controls';
    if (!pagination) {
      return '<div ' + controlsAttributes + ' data-runtime-pagination-state="entry"><span class="no-code-runtime-data-label">Runtime data</span><span class="no-code-runtime-data-query-summary" data-runtime-query-summary aria-live="polite">No runtime data query applied.</span><label>Search <input type="search" maxlength="128" data-runtime-search-input></label><button type="button" data-runtime-search-submit>Search</button>' + filterControls + sortControls + '<label>Page size <input type="number" min="1" max="100" value="1" data-runtime-page-size-input></label><button type="button" data-runtime-page-size-submit>Apply</button><button type="button" data-runtime-query-reset>Clear</button></div>';
    }
    var page = Number(pagination.page || 1);
    var pageSize = Number(pagination.page_size || 1);
    var totalRows = Number(pagination.total_rows || 0);
    var pageCount = Number(pagination.page_count || 1);
    var hasPrevious = !!pagination.has_previous_page;
    var hasNext = !!pagination.has_next_page;
    return '<div ' + controlsAttributes + ' data-runtime-pagination-state="active" data-runtime-pagination-page="' + htmlEscape(page) + '" data-runtime-pagination-page-size="' + htmlEscape(pageSize) + '" data-runtime-pagination-page-count="' + htmlEscape(pageCount) + '" data-runtime-pagination-total-rows="' + htmlEscape(totalRows) + '">'
      + '<button type="button" data-runtime-page="' + htmlEscape(Math.max(1, page - 1)) + '" data-runtime-page-size="' + htmlEscape(pageSize) + '"' + (hasPrevious ? '' : ' disabled') + '>Previous</button>'
      + '<span class="no-code-runtime-data-label">Page ' + htmlEscape(page) + ' of ' + htmlEscape(pageCount) + ' (' + htmlEscape(totalRows) + ' total rows)</span>'
      + '<span class="no-code-runtime-data-query-summary" data-runtime-query-summary aria-live="polite">No runtime data query applied.</span>'
      + '<label>Page <input type="number" min="1" max="' + htmlEscape(pageCount) + '" value="' + htmlEscape(page) + '" data-runtime-page-input></label><button type="button" data-runtime-page-submit>Go</button>'
      + '<button type="button" data-runtime-page="' + htmlEscape(page + 1) + '" data-runtime-page-size="' + htmlEscape(pageSize) + '"' + (hasNext ? '' : ' disabled') + '>Next</button>'
      + '<label>Search <input type="search" maxlength="128" data-runtime-search-input></label><button type="button" data-runtime-search-submit>Search</button>'
      + filterControls
      + sortControls
      + '<label>Page size <input type="number" min="1" max="100" value="' + htmlEscape(pageSize) + '" data-runtime-page-size-input></label><button type="button" data-runtime-page-size-submit>Apply</button>'
      + '<button type="button" data-runtime-query-reset>Clear</button>'
      + '</div>';
  }

  function renderRuntimeDetailBody(render) {
    var fields = Array.isArray(render.fields) ? render.fields : [];
    var data = render.data && typeof render.data === 'object' ? render.data : {};
    var item = data.item && typeof data.item === 'object' ? data.item : {};
    var pairs = fields.map(function (field) {
      var fieldKey = field.field_key || '';
      return '<dt>' + htmlEscape(field.label || fieldKey) + '</dt><dd>' + htmlEscape(displayCellValue(item[fieldKey])) + '</dd>';
    });
    if (pairs.length === 0) {
      return '<p class="no-code-empty-state">' + htmlEscape(render.empty_state_message || 'No preview data is available yet.') + '</p>';
    }
    return '<dl class="no-code-detail">' + pairs.join('') + '</dl>';
  }

  function renderRuntimeFormBody(render) {
    var fields = Array.isArray(render.fields) ? render.fields : [];
    var data = render.data && typeof render.data === 'object' ? render.data : {};
    var item = data.item && typeof data.item === 'object' ? data.item : {};
    var visibleFieldKeys = {};
    var controls = fields.map(function (field) {
      var fieldKey = field.field_key || '';
      if (fieldKey) {
        visibleFieldKeys[fieldKey] = true;
      }
      var type = field.type || 'string';
      var value = item[fieldKey] && typeof item[fieldKey] === 'object' ? item[fieldKey] : {};
      var displayValue = displayCellValue(value);
      var readonly = !!field.readonly;
      var required = !!field.required;
      var label = field.label || fieldKey;
      var fieldHintId = 'field-hint-' + fieldKey;
      var attrs = ' name="' + htmlEscape(fieldKey) + '" id="field-' + htmlEscape(fieldKey) + '"' + (readonly ? ' readonly' : '') + (required ? ' required aria-describedby="' + htmlEscape(fieldHintId) + '"' : '');
      var control;
      if (type === 'text') {
        control = '<textarea' + attrs + '>' + htmlEscape(displayValue) + '</textarea>';
      } else {
        var inputType = type === 'integer' || type === 'decimal' ? 'number' : (type === 'boolean' ? 'checkbox' : (type === 'date' ? 'date' : (type === 'datetime' ? 'datetime-local' : (type === 'time' ? 'time' : 'text'))));
        var valueAttr = inputType === 'checkbox' ? (value.value ? ' checked' : '') : ' value="' + htmlEscape(displayValue) + '"';
        control = '<input type="' + inputType + '"' + attrs + valueAttr + '>';
      }
      var labelText = '<span class="no-code-form-label-text">' + htmlEscape(label) + (required ? '<span class="no-code-required-badge">Required</span>' : '') + '</span>';
      var hint = required ? '<span id="' + htmlEscape(fieldHintId) + '" class="no-code-required-hint" data-required-field="' + htmlEscape(fieldKey) + '" data-required-label="' + htmlEscape(label) + '" data-required-state="pending">Required for the generated action intent.</span>' : '';
      return '<label for="field-' + htmlEscape(fieldKey) + '">' + labelText + control + hint + '</label>';
    });
    var hiddenKeyControls = hiddenActionKeyControls(render, item, visibleFieldKeys);
    controls = controls.concat(hiddenKeyControls);
    if (controls.length === 0) {
      return '<p class="no-code-empty-state">' + htmlEscape(render.empty_state_message || 'No editable data is available yet.') + '</p>';
    }
    return '<form class="no-code-form" method="post">' + controls.join('\n') + '</form>';
  }

  function hiddenActionKeyControls(render, item, visibleFieldKeys) {
    var actions = Array.isArray(render.actions) ? render.actions : [];
    var hidden = [];
    var seen = {};
    actions.forEach(function (action) {
      var actionFields = Array.isArray(action && action.fields) ? action.fields : [];
      actionFields.forEach(function (field) {
        var fieldKey = field && field.field_key ? String(field.field_key) : '';
        if (!fieldKey || field.role !== 'key' || visibleFieldKeys[fieldKey] || seen[fieldKey]) {
          return;
        }
        var value = firstRuntimeFieldValue(render, item, fieldKey);
        if (value === '') {
          return;
        }
        seen[fieldKey] = true;
        hidden.push('<input type="hidden" name="' + htmlEscape(fieldKey) + '" value="' + htmlEscape(value) + '" data-runtime-hidden-action-key="' + htmlEscape(fieldKey) + '">');
      });
    });
    return hidden;
  }

  function renderRuntimeScreenBody(render) {
    if (!render || render.screen_type === 'list') {
      return renderRuntimeListBody(render || {});
    }
    if (render.screen_type === 'form') {
      return renderRuntimeFormBody(render);
    }
    return renderRuntimeDetailBody(render);
  }

  function replacePreviewScreenRender(render) {
    var screenKey = render && render.screen_key ? String(render.screen_key) : '';
    if (!screenKey || !Array.isArray(preview.screens)) {
      return;
    }
    for (var index = 0; index < preview.screens.length; index += 1) {
      if (preview.screens[index] && preview.screens[index].screen_key === screenKey) {
        preview.screens[index] = render;
        return;
      }
    }
  }

  function existingPreviewScreenRender(screenKey) {
    if (!screenKey || !Array.isArray(preview.screens)) {
      return null;
    }
    for (var index = 0; index < preview.screens.length; index += 1) {
      if (preview.screens[index] && preview.screens[index].screen_key === screenKey) {
        return preview.screens[index];
      }
    }
    return null;
  }

  function runtimeRenderFromDataScreen(dataScreen) {
    var screenKey = dataScreen && dataScreen.screen_key ? String(dataScreen.screen_key) : '';
    var existing = existingPreviewScreenRender(screenKey) || {};
    var render = {};
    Object.keys(existing).forEach(function (key) {
      render[key] = existing[key];
    });
    Object.keys(dataScreen || {}).forEach(function (key) {
      render[key] = dataScreen[key];
    });
    render.fields = Array.isArray(existing.fields) ? existing.fields : [];
    render.actions = Array.isArray(existing.actions) ? existing.actions : [];
    render.screen_title = existing.screen_title || render.screen_title || screenKey;
    render.screen_subtitle = existing.screen_subtitle || render.screen_subtitle || '';
    render.empty_state_message = existing.empty_state_message || render.empty_state_message || '';
    return render;
  }

  function applyRuntimeDataScreen(render) {
    var screenKey = render && render.screen_key ? String(render.screen_key) : '';
    if (!screenKey) {
      return;
    }
    var screen = null;
    document.querySelectorAll('.no-code-screen[data-screen-key]').forEach(function (candidate) {
      if (!screen && candidate.getAttribute('data-screen-key') === screenKey) {
        screen = candidate;
      }
    });
    var body = screen ? screen.querySelector('[data-screen-body]') : null;
    if (!screen || !body) {
      return;
    }
    var screenType = render.screen_type || screen.getAttribute('data-screen-type') || '';
    var state = screenStateFromRender(render);
    var badge = screen.querySelector('.no-code-screen-header .no-code-state-badge');
    screen.setAttribute('data-screen-state', state);
    screen.setAttribute('data-screen-type', screenType);
    if (badge) {
      badge.setAttribute('data-state', state);
      badge.textContent = screenStatusMessage(state, screenType);
    }
    body.innerHTML = renderRuntimeScreenBody(render);
    replacePreviewScreenRender(render);
    bindScreenControls(screen);
    bindRuntimeListSelection(screen);
    bindRuntimeSortHeaders(screen);
    bindRuntimePaginationControls(screen);
    syncRuntimeSortHeadersFromControls(screen);
    writeIntentDraft(screen);
  }

  function applyRuntimeDataPayload(payload) {
    if (!payload || payload.contract_version !== 'no-code-runtime-data-v0') {
      throw new Error('runtime data contract mismatch');
    }
    var screens = Array.isArray(payload.screens) ? payload.screens : [];
    screens.forEach(function (dataScreen) {
      applyRuntimeDataScreen(runtimeRenderFromDataScreen(dataScreen));
    });
    syncRuntimeDataControlsFromPayload(payload);
    window.__noCodeRuntimeLastDataPayload = payload;
  }

  function firstRuntimeQueryEntry(values) {
    var entries = runtimeQueryEntries(values);
    return entries.length > 0 ? entries[0] : {
      key: '',
      value: ''
    };
  }

  function runtimeQueryEntries(values) {
    if (!values || typeof values !== 'object') {
      return [];
    }
    return Object.keys(values).map(function (key) {
      return {
        key: key,
        value: String(values[key] || '')
      };
    });
  }

  function runtimeDataPayloadPagination(payload) {
    if (payload && payload.pagination && typeof payload.pagination === 'object') {
      return payload.pagination;
    }
    var screens = payload && Array.isArray(payload.screens) ? payload.screens : [];
    for (var index = 0; index < screens.length; index += 1) {
      var metadata = screens[index] && screens[index].metadata && typeof screens[index].metadata === 'object' ? screens[index].metadata : {};
      if (metadata.pagination && typeof metadata.pagination === 'object') {
        return metadata.pagination;
      }
    }
    return {};
  }

  function runtimeDataQueryControlLabels(controls) {
    var labels = {
      fields: {},
      filterOperators: {},
      sortDirections: {}
    };
    if (!controls) {
      return labels;
    }
    controls.querySelectorAll('select[data-runtime-filter-field] option, select[data-runtime-sort-field] option').forEach(function (option) {
      var value = String(option.value || '');
      if (value && !labels.fields[value]) {
        labels.fields[value] = String(option.textContent || value).trim() || value;
      }
    });
    controls.querySelectorAll('select[data-runtime-filter-operator] option').forEach(function (option) {
      var value = String(option.value || '');
      if (value && !labels.filterOperators[value]) {
        labels.filterOperators[value] = String(option.textContent || value).trim() || value;
      }
    });
    controls.querySelectorAll('select[data-runtime-sort-direction] option').forEach(function (option) {
      var value = String(option.value || '');
      if (value && !labels.sortDirections[value]) {
        labels.sortDirections[value] = String(option.textContent || value).trim() || value;
      }
    });
    return labels;
  }

  function runtimeDataQueryLabel(labels, group, key) {
    labels = labels && typeof labels === 'object' ? labels : {};
    group = labels[group] && typeof labels[group] === 'object' ? labels[group] : {};
    key = String(key || '');
    return group[key] || key;
  }

  function runtimeDataQueryFieldLabel(labels, fieldKey) {
    var key = String(fieldKey || '');
    return runtimeDataQueryLabel(labels, 'fields', key);
  }

  function runtimeDataQuerySummaryParts(query, pagination, labels) {
    query = query && typeof query === 'object' ? query : {};
    pagination = pagination && typeof pagination === 'object' ? pagination : {};
    labels = labels && typeof labels === 'object' ? labels : {};
    var parts = [];
    var searchQuery = String(query.q || '').trim();
    if (searchQuery) {
      parts.push('Search: ' + searchQuery);
    }
    var filters = runtimeQueryEntries(query.filter);
    var filterOperators = query.filter_op && typeof query.filter_op === 'object' ? query.filter_op : {};
    if (filters.length > 0) {
      parts.push('Filters: ' + filters.map(function (entry) {
        return runtimeDataQueryFieldLabel(labels, entry.key) + ' ' + runtimeDataQueryLabel(labels, 'filterOperators', filterOperators[entry.key] || 'contains') + ' ' + entry.value;
      }).join(', '));
    }
    var sorts = runtimeQueryEntries(query.sort);
    if (sorts.length > 0) {
      parts.push('Sort: ' + sorts.map(function (entry) {
        return runtimeDataQueryFieldLabel(labels, entry.key) + ' ' + runtimeDataQueryLabel(labels, 'sortDirections', entry.value);
      }).join(', '));
    }
    var pageSize = pagination.page_size || query.page_size || '';
    if (pageSize) {
      parts.push('Page size: ' + pageSize);
    }
    if (parts.length > 0 && pagination.total_rows !== undefined && pagination.total_rows !== null && pagination.total_rows !== '') {
      parts.push('Rows: ' + String(pagination.total_rows));
    }
    return parts;
  }

  function runtimeDataQuerySummaryText(query, pagination, labels) {
    var parts = runtimeDataQuerySummaryParts(query, pagination, labels);
    return parts.length > 0 ? 'Active query: ' + parts.join(' | ') : 'No runtime data query applied.';
  }

  function runtimeDataQuerySummaryHtml(query, pagination, labels) {
    var parts = runtimeDataQuerySummaryParts(query, pagination, labels);
    if (parts.length === 0) {
      return htmlEscape('No runtime data query applied.');
    }
    return '<span class="no-code-runtime-data-query-summary-label">Active query:</span> ' + parts.map(function (part) {
      return '<span class="no-code-runtime-data-query-token">' + htmlEscape(part) + '</span>';
    }).join(' ');
  }

  function setRuntimeSelectValue(control, value) {
    if (!control) {
      return;
    }
    var normalized = String(value || '');
    var hasOption = false;
    Array.prototype.forEach.call(control.options || [], function (option) {
      if (String(option.value || '') === normalized) {
        hasOption = true;
      }
    });
    if (hasOption || normalized === '') {
      control.value = normalized;
    }
  }

  function syncRuntimeSortHeaders(screen, sortField, sortDirection) {
    if (!screen) {
      return;
    }
    var activeField = String(sortField || '');
    var activeDirection = String(sortDirection || '').toLowerCase() === 'desc' ? 'descending' : (activeField ? 'ascending' : 'none');
    screen.querySelectorAll('[data-runtime-sort-header]').forEach(function (button) {
      var fieldKey = String(button.getAttribute('data-runtime-sort-field-key') || '');
      var state = fieldKey && fieldKey === activeField ? activeDirection : 'none';
      var header = button.closest('th');
      button.setAttribute('data-runtime-sort-state', state);
      if (header) {
        header.setAttribute('aria-sort', state);
      }
    });
  }

  function syncRuntimeSortHeadersFromControls(root) {
    var scope = root || document;
    scope.querySelectorAll('[data-runtime-data-controls]').forEach(function (controls) {
      var screen = controls.closest('.no-code-screen');
      var sortField = controls.querySelector('[data-runtime-sort-field]');
      var sortDirection = controls.querySelector('[data-runtime-sort-direction]');
      syncRuntimeSortHeaders(screen, sortField ? sortField.value : '', sortDirection ? sortDirection.value : '');
    });
  }

  function runtimeDataControlValue(controls, selector) {
    var control = controls ? controls.querySelector(selector) : null;
    return control ? String(control.value || '').trim() : '';
  }

  function runtimeDataFilterFieldType(fieldSelect) {
    if (!fieldSelect) {
      return '';
    }
    var selected = fieldSelect.options ? fieldSelect.options[fieldSelect.selectedIndex] : null;
    return selected ? String(selected.getAttribute('data-runtime-field-type') || 'string').toLowerCase() : '';
  }

  function runtimeDataFilterTypeSupportsOrdered(fieldType) {
    return ['integer', 'number', 'date', 'datetime', 'time'].indexOf(String(fieldType || '').toLowerCase()) !== -1;
  }

  function runtimeDataFilterValueHint(fieldType) {
    var normalized = String(fieldType || '').toLowerCase();
    if (normalized === 'integer') {
      return 'Integer value';
    }
    if (normalized === 'number') {
      return 'Numeric value';
    }
    if (normalized === 'date') {
      return 'YYYY-MM-DD';
    }
    if (normalized === 'datetime') {
      return 'YYYY-MM-DDTHH:MM:SS';
    }
    if (normalized === 'time') {
      return 'HH:MM:SS';
    }
    return 'Text value';
  }

  function runtimeDataFilterValueInputType(fieldType) {
    var normalized = String(fieldType || '').toLowerCase();
    if (normalized === 'integer' || normalized === 'number') {
      return 'number';
    }
    if (normalized === 'date') {
      return 'date';
    }
    if (normalized === 'datetime') {
      return 'datetime-local';
    }
    if (normalized === 'time') {
      return 'time';
    }
    return 'text';
  }

  function runtimeDataFilterFieldLabel(fieldSelect) {
    if (!fieldSelect) {
      return 'selected field';
    }
    var selected = fieldSelect.options ? fieldSelect.options[fieldSelect.selectedIndex] : null;
    var label = selected ? String(selected.textContent || '').trim() : '';
    return label || String(fieldSelect.value || 'selected field');
  }

  function runtimeDataFilterValueValidationMessage(fieldType, value) {
    var normalized = String(fieldType || '').toLowerCase();
    var candidate = String(value || '').trim();
    var hint = runtimeDataFilterValueHint(normalized);
    var dateParts;
    var maxDay;
    if (candidate === '') {
      return '';
    }
    if (normalized === 'integer') {
      return /^-?[0-9]+$/.test(candidate) ? '' : 'Expected format: ' + hint + '. Use whole digits only.';
    }
    if (normalized === 'number') {
      return /^-?[0-9]+(?:\.[0-9]+)?$/.test(candidate) ? '' : 'Expected format: ' + hint + '. Use digits with an optional decimal point.';
    }
    if (normalized === 'date') {
      dateParts = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(candidate);
      if (!dateParts) {
        return 'Expected format: ' + hint + '.';
      }
      maxDay = new Date(Number(dateParts[1]), Number(dateParts[2]), 0).getDate();
      return Number(dateParts[2]) >= 1 && Number(dateParts[2]) <= 12 && Number(dateParts[3]) >= 1 && Number(dateParts[3]) <= maxDay ? '' : 'Expected format: ' + hint + '. Use a valid calendar date.';
    }
    if (normalized === 'time') {
      dateParts = /^([0-9]{2}):([0-9]{2}):([0-9]{2})$/.exec(candidate);
      return dateParts && Number(dateParts[1]) <= 23 && Number(dateParts[2]) <= 59 && Number(dateParts[3]) <= 59 ? '' : 'Expected format: ' + hint + '. Use a valid 24-hour time.';
    }
    if (normalized === 'datetime') {
      dateParts = /^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})$/.exec(candidate);
      if (!dateParts) {
        return 'Expected format: ' + hint + '.';
      }
      maxDay = new Date(Number(dateParts[1]), Number(dateParts[2]), 0).getDate();
      if (Number(dateParts[2]) < 1 || Number(dateParts[2]) > 12 || Number(dateParts[3]) < 1 || Number(dateParts[3]) > maxDay) {
        return 'Expected format: ' + hint + '. Use a valid calendar date.';
      }
      return Number(dateParts[4]) <= 23 && Number(dateParts[5]) <= 59 && Number(dateParts[6]) <= 59 ? '' : 'Expected format: ' + hint + '. Use a valid 24-hour time.';
    }
    return '';
  }

  function runtimeDataFilterRowValidationMessage(fieldSelect, valueInput, label) {
    var fieldKey = fieldSelect ? String(fieldSelect.value || '').trim() : '';
    var value = valueInput ? String(valueInput.value || '').trim() : '';
    var fieldType = runtimeDataFilterFieldType(fieldSelect);
    var message = runtimeDataFilterValueValidationMessage(fieldType, value);
    if (!fieldKey || !value) {
      return '';
    }
    if (valueInput && typeof valueInput.checkValidity === 'function' && !valueInput.checkValidity()) {
      message = message || 'Use a valid filter value.';
    }
    if (message) {
      return label + ' for ' + runtimeDataFilterFieldLabel(fieldSelect) + ': ' + message;
    }
    return '';
  }

  function runtimeDataFilterValidationMessage(paginationRoot) {
    var controls = paginationRoot && paginationRoot.matches && paginationRoot.matches('[data-runtime-data-controls]')
      ? paginationRoot
      : (paginationRoot ? paginationRoot.querySelector('[data-runtime-data-controls]') : null);
    if (!controls) {
      return '';
    }
    return runtimeDataFilterRowValidationMessage(controls.querySelector('[data-runtime-filter-field]'), controls.querySelector('[data-runtime-filter-value]'), 'Filter')
      || runtimeDataFilterRowValidationMessage(controls.querySelector('[data-runtime-filter-field-secondary]'), controls.querySelector('[data-runtime-filter-value-secondary]'), 'Filter 2')
      || runtimeDataFilterRowValidationMessage(controls.querySelector('[data-runtime-filter-field-tertiary]'), controls.querySelector('[data-runtime-filter-value-tertiary]'), 'Filter 3');
  }

  function stopRuntimeDataFetchForFilterValidation(button, paginationRoot) {
    var message = runtimeDataFilterValidationMessage(paginationRoot);
    if (!message) {
      return false;
    }
    setRuntimeRefreshStatus(button, 'error', 'Runtime data filter was not fetched: ' + message);
    return true;
  }

  function syncRuntimeDataFilterValueHint(fieldSelect, valueInput) {
    if (!valueInput) {
      return;
    }
    var fieldType = runtimeDataFilterFieldType(fieldSelect);
    var hint = runtimeDataFilterValueHint(fieldType);
    var inputType = runtimeDataFilterValueInputType(fieldType);
    valueInput.setAttribute('type', inputType);
    valueInput.setAttribute('placeholder', hint);
    valueInput.setAttribute('title', 'Runtime data filter value format: ' + hint);
    if (inputType === 'number') {
      valueInput.setAttribute('inputmode', 'decimal');
      valueInput.setAttribute('step', String(fieldType).toLowerCase() === 'integer' ? '1' : 'any');
    } else if (inputType === 'datetime-local' || inputType === 'time') {
      valueInput.removeAttribute('inputmode');
      valueInput.setAttribute('step', '1');
    } else {
      valueInput.removeAttribute('inputmode');
      valueInput.removeAttribute('step');
    }
  }

  function syncRuntimeDataFilterOperatorOptions(fieldSelect, operatorSelect, valueInput) {
    syncRuntimeDataFilterValueHint(fieldSelect, valueInput);
    if (!operatorSelect) {
      return;
    }
    var supportsOrdered = runtimeDataFilterTypeSupportsOrdered(runtimeDataFilterFieldType(fieldSelect));
    operatorSelect.querySelectorAll('[data-runtime-filter-ordered]').forEach(function (option) {
      option.hidden = !supportsOrdered;
      option.disabled = !supportsOrdered;
    });
    operatorSelect.setAttribute('data-runtime-filter-ordered-enabled', supportsOrdered ? 'true' : 'false');
    var selected = operatorSelect.options ? operatorSelect.options[operatorSelect.selectedIndex] : null;
    if (selected && selected.hasAttribute('data-runtime-filter-ordered') && !supportsOrdered) {
      setRuntimeSelectValue(operatorSelect, 'contains');
    }
  }

  function syncRuntimeDataFilterOperatorChoices(controls) {
    if (!controls) {
      return;
    }
    syncRuntimeDataFilterOperatorOptions(
      controls.querySelector('[data-runtime-filter-field]'),
      controls.querySelector('[data-runtime-filter-operator]'),
      controls.querySelector('[data-runtime-filter-value]')
    );
    syncRuntimeDataFilterOperatorOptions(
      controls.querySelector('[data-runtime-filter-field-secondary]'),
      controls.querySelector('[data-runtime-filter-operator-secondary]'),
      controls.querySelector('[data-runtime-filter-value-secondary]')
    );
    syncRuntimeDataFilterOperatorOptions(
      controls.querySelector('[data-runtime-filter-field-tertiary]'),
      controls.querySelector('[data-runtime-filter-operator-tertiary]'),
      controls.querySelector('[data-runtime-filter-value-tertiary]')
    );
  }

  function runtimeDataFilterExtraHasValue(controls, name) {
    var suffix = name === 'tertiary' ? 'tertiary' : 'secondary';
    return runtimeDataControlValue(controls, '[data-runtime-filter-field-' + suffix + ']') !== ''
      || runtimeDataControlValue(controls, '[data-runtime-filter-value-' + suffix + ']') !== '';
  }

  function runtimeDataSortExtraHasValue(controls, name) {
    var suffix = name === 'tertiary' ? 'tertiary' : 'secondary';
    return runtimeDataControlValue(controls, '[data-runtime-sort-field-' + suffix + ']') !== '';
  }

  function setRuntimeDataExtraVisibility(controls, kind, name, visible) {
    var selector = kind === 'sort'
      ? '[data-runtime-sort-extra="' + name + '"]'
      : '[data-runtime-filter-extra="' + name + '"]';
    var row = controls ? controls.querySelector(selector) : null;
    if (!row) {
      return;
    }
    row.hidden = !visible;
  }

  function syncRuntimeDataRowVisibility(controls) {
    if (!controls) {
      return;
    }
    var secondFilterRow = controls.querySelector('[data-runtime-filter-extra="secondary"]');
    var thirdFilterRow = controls.querySelector('[data-runtime-filter-extra="tertiary"]');
    var secondSortRow = controls.querySelector('[data-runtime-sort-extra="secondary"]');
    var thirdSortRow = controls.querySelector('[data-runtime-sort-extra="tertiary"]');
    var showSecondFilter = runtimeDataFilterExtraHasValue(controls, 'secondary') || (secondFilterRow ? !secondFilterRow.hidden : false);
    var showThirdFilter = showSecondFilter && (runtimeDataFilterExtraHasValue(controls, 'tertiary') || (thirdFilterRow ? !thirdFilterRow.hidden : false));
    var showSecondSort = runtimeDataSortExtraHasValue(controls, 'secondary') || (secondSortRow ? !secondSortRow.hidden : false);
    var showThirdSort = showSecondSort && (runtimeDataSortExtraHasValue(controls, 'tertiary') || (thirdSortRow ? !thirdSortRow.hidden : false));
    setRuntimeDataExtraVisibility(controls, 'filter', 'secondary', showSecondFilter);
    setRuntimeDataExtraVisibility(controls, 'filter', 'tertiary', showThirdFilter);
    setRuntimeDataExtraVisibility(controls, 'sort', 'secondary', showSecondSort);
    setRuntimeDataExtraVisibility(controls, 'sort', 'tertiary', showThirdSort);
    var addFilter = controls.querySelector('[data-runtime-filter-add]');
    var addSort = controls.querySelector('[data-runtime-sort-add]');
    if (addFilter) {
      addFilter.disabled = showSecondFilter && showThirdFilter;
    }
    if (addSort) {
      addSort.disabled = showSecondSort && showThirdSort;
    }
  }

  function clearRuntimeDataFilterExtra(controls, name) {
    var suffix = name === 'tertiary' ? 'tertiary' : 'secondary';
    setRuntimeSelectValue(controls.querySelector('[data-runtime-filter-field-' + suffix + ']'), '');
    setRuntimeSelectValue(controls.querySelector('[data-runtime-filter-operator-' + suffix + ']'), 'contains');
    syncRuntimeDataFilterOperatorChoices(controls);
    var value = controls.querySelector('[data-runtime-filter-value-' + suffix + ']');
    if (value) {
      value.value = '';
    }
  }

  function clearRuntimeDataSortExtra(controls, name) {
    var suffix = name === 'tertiary' ? 'tertiary' : 'secondary';
    setRuntimeSelectValue(controls.querySelector('[data-runtime-sort-field-' + suffix + ']'), '');
    setRuntimeSelectValue(controls.querySelector('[data-runtime-sort-direction-' + suffix + ']'), 'asc');
  }

  function revealRuntimeDataExtraRow(button, kind) {
    var controls = button ? button.closest('[data-runtime-data-controls]') : null;
    if (!controls) {
      return;
    }
    if (kind === 'sort') {
      var secondSortRow = controls.querySelector('[data-runtime-sort-extra="secondary"]');
      if (!runtimeDataSortExtraHasValue(controls, 'secondary') && secondSortRow && secondSortRow.hidden) {
        setRuntimeDataExtraVisibility(controls, 'sort', 'secondary', true);
      } else {
        setRuntimeDataExtraVisibility(controls, 'sort', 'tertiary', true);
      }
    } else {
      var secondFilterRow = controls.querySelector('[data-runtime-filter-extra="secondary"]');
      if (!runtimeDataFilterExtraHasValue(controls, 'secondary') && secondFilterRow && secondFilterRow.hidden) {
        setRuntimeDataExtraVisibility(controls, 'filter', 'secondary', true);
      } else {
        setRuntimeDataExtraVisibility(controls, 'filter', 'tertiary', true);
      }
    }
    syncRuntimeDataRowVisibility(controls);
  }

  function removeRuntimeDataExtraRow(button, kind) {
    var controls = button ? button.closest('[data-runtime-data-controls]') : null;
    var name = button ? String(button.getAttribute(kind === 'sort' ? 'data-runtime-sort-remove' : 'data-runtime-filter-remove') || '') : '';
    if (!controls || (name !== 'secondary' && name !== 'tertiary')) {
      return;
    }
    if (kind === 'sort') {
      clearRuntimeDataSortExtra(controls, name);
      setRuntimeDataExtraVisibility(controls, 'sort', name, false);
      if (name === 'secondary') {
        clearRuntimeDataSortExtra(controls, 'tertiary');
        setRuntimeDataExtraVisibility(controls, 'sort', 'tertiary', false);
      }
      syncRuntimeSortHeaders(controls.closest('.no-code-screen'), runtimeDataControlValue(controls, '[data-runtime-sort-field]'), runtimeDataControlValue(controls, '[data-runtime-sort-direction]'));
    } else {
      clearRuntimeDataFilterExtra(controls, name);
      setRuntimeDataExtraVisibility(controls, 'filter', name, false);
      if (name === 'secondary') {
        clearRuntimeDataFilterExtra(controls, 'tertiary');
        setRuntimeDataExtraVisibility(controls, 'filter', 'tertiary', false);
      }
    }
    syncRuntimeDataRowVisibility(controls);
  }

  function syncRuntimeDataControlsFromPayload(payload) {
    var query = payload && payload.query && typeof payload.query === 'object' ? payload.query : {};
    var pagination = runtimeDataPayloadPagination(payload);
    var filters = runtimeQueryEntries(query.filter);
    var filterOperators = query.filter_op && typeof query.filter_op === 'object' ? query.filter_op : {};
    var filter = filters.length > 0 ? filters[0] : { key: '', value: '' };
    var secondFilter = filters.length > 1 ? filters[1] : { key: '', value: '' };
    var thirdFilter = filters.length > 2 ? filters[2] : { key: '', value: '' };
    var sorts = runtimeQueryEntries(query.sort);
    var sort = sorts.length > 0 ? sorts[0] : { key: '', value: '' };
    var secondSort = sorts.length > 1 ? sorts[1] : { key: '', value: '' };
    var thirdSort = sorts.length > 2 ? sorts[2] : { key: '', value: '' };
    var pageSize = pagination.page_size || query.page_size || '';
    document.querySelectorAll('[data-runtime-data-controls]').forEach(function (controls) {
      var searchInput = controls.querySelector('[data-runtime-search-input]');
      var filterField = controls.querySelector('[data-runtime-filter-field]');
      var filterOperator = controls.querySelector('[data-runtime-filter-operator]');
      var filterValue = controls.querySelector('[data-runtime-filter-value]');
      var secondFilterField = controls.querySelector('[data-runtime-filter-field-secondary]');
      var secondFilterOperator = controls.querySelector('[data-runtime-filter-operator-secondary]');
      var secondFilterValue = controls.querySelector('[data-runtime-filter-value-secondary]');
      var thirdFilterField = controls.querySelector('[data-runtime-filter-field-tertiary]');
      var thirdFilterOperator = controls.querySelector('[data-runtime-filter-operator-tertiary]');
      var thirdFilterValue = controls.querySelector('[data-runtime-filter-value-tertiary]');
      var sortField = controls.querySelector('[data-runtime-sort-field]');
      var sortDirection = controls.querySelector('[data-runtime-sort-direction]');
      var secondSortField = controls.querySelector('[data-runtime-sort-field-secondary]');
      var secondSortDirection = controls.querySelector('[data-runtime-sort-direction-secondary]');
      var thirdSortField = controls.querySelector('[data-runtime-sort-field-tertiary]');
      var thirdSortDirection = controls.querySelector('[data-runtime-sort-direction-tertiary]');
      var pageSizeInput = controls.querySelector('[data-runtime-page-size-input]');
      var querySummary = controls.querySelector('[data-runtime-query-summary]');
      if (searchInput) {
        searchInput.value = String(query.q || '');
      }
      setRuntimeSelectValue(filterField, filter.key);
      syncRuntimeDataFilterOperatorChoices(controls);
      setRuntimeSelectValue(filterOperator, filterOperators[filter.key] || 'contains');
      syncRuntimeDataFilterOperatorChoices(controls);
      if (filterValue) {
        filterValue.value = filter.value;
      }
      setRuntimeSelectValue(secondFilterField, secondFilter.key);
      syncRuntimeDataFilterOperatorChoices(controls);
      setRuntimeSelectValue(secondFilterOperator, secondFilter.key ? (filterOperators[secondFilter.key] || 'contains') : 'contains');
      syncRuntimeDataFilterOperatorChoices(controls);
      if (secondFilterValue) {
        secondFilterValue.value = secondFilter.value;
      }
      setRuntimeSelectValue(thirdFilterField, thirdFilter.key);
      syncRuntimeDataFilterOperatorChoices(controls);
      setRuntimeSelectValue(thirdFilterOperator, thirdFilter.key ? (filterOperators[thirdFilter.key] || 'contains') : 'contains');
      syncRuntimeDataFilterOperatorChoices(controls);
      if (thirdFilterValue) {
        thirdFilterValue.value = thirdFilter.value;
      }
      setRuntimeSelectValue(sortField, sort.key);
      setRuntimeSelectValue(sortDirection, sort.value);
      setRuntimeSelectValue(secondSortField, secondSort.key);
      setRuntimeSelectValue(secondSortDirection, secondSort.key ? secondSort.value : 'asc');
      setRuntimeSelectValue(thirdSortField, thirdSort.key);
      setRuntimeSelectValue(thirdSortDirection, thirdSort.key ? thirdSort.value : 'asc');
      if (pageSizeInput && pageSize) {
        pageSizeInput.value = String(pageSize);
      }
      if (querySummary) {
        var querySummaryLabels = runtimeDataQueryControlLabels(controls);
        var querySummaryText = runtimeDataQuerySummaryText(query, pagination, querySummaryLabels);
        querySummary.innerHTML = runtimeDataQuerySummaryHtml(query, pagination, querySummaryLabels);
        querySummary.setAttribute('aria-label', querySummaryText);
      }
      syncRuntimeSortHeaders(controls.closest('.no-code-screen'), sort.key, sort.value);
      syncRuntimeDataRowVisibility(controls);
    });
  }

  function setRuntimeRefreshStatus(button, state, message) {
    var screen = button.closest('.no-code-screen');
    var status = screen ? screen.querySelector('[data-runtime-result-refresh-status]') : null;
    button.setAttribute('data-runtime-result-refresh-state', state);
    if (status) {
      status.textContent = message;
      status.setAttribute('data-state', state);
    }
  }

  function refreshRuntimeDataForScreen(screen, button, workingMessage, selectedKey, page, pageSize, searchQuery, filterField, filterValue, sortField, sortDirection, filters, browserHistoryMode, sorts) {
    if (!button || !hasRuntimeDataBinding()) {
      return;
    }
    var submitState = captureRuntimeSubmitState(screen);
    var requestUrl = selectedKey
      ? runtimeDataUrlWithSelectedKey(selectedKey)
      : runtimeDataUrlWithCombinedQuery({
        q: searchQuery || '',
        filters: Array.isArray(filters) ? filters : [],
        filterField: filterField || '',
        filterValue: filterValue || '',
        sortField: sortField || '',
        sortDirection: sortDirection || '',
        sorts: Array.isArray(sorts) ? sorts : [],
        page: page || '',
        pageSize: pageSize || ''
      });
    button.disabled = true;
    setRuntimeRefreshStatus(button, 'working', workingMessage || 'Fetching read-only live runtime data...');
    fetch(requestUrl, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json'
      }
    }).then(function (response) {
      return response.json().catch(function () {
        return {
          ok: false,
          error: 'invalid_json_response'
        };
      });
    }).then(function (payload) {
      if (!payload || payload.ok === false) {
        throw new Error(payload && payload.error ? payload.error : 'runtime data fetch failed');
      }
      applyRuntimeDataPayload(payload);
      restoreRuntimeSubmitState(screen, submitState);
      button = screen ? screen.querySelector('[data-runtime-result-refresh]') || button : button;
      button.disabled = false;
      mirrorRuntimeDataQueryInBrowserUrl(requestUrl, browserHistoryMode || 'replace');
      setRuntimeRefreshStatus(button, 'success', 'Fresh runtime data loaded from ' + requestUrl + ' (read-only current/alias data).');
    }).catch(function (error) {
      button.disabled = false;
      setRuntimeRefreshStatus(button, 'error', 'Fresh runtime data could not be loaded from the read-only runtime-data endpoint: ' + (error && error.message ? error.message : 'request failed') + '. Current preview data was left unchanged.');
    });
  }

  function refreshRuntimeDataForSelectedRow(button) {
    var selectedKey = button.getAttribute('data-runtime-selected-key') || '';
    if (!selectedKey || !hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!refreshButton) {
      return;
    }
    refreshRuntimeDataForScreen(screen, refreshButton, 'Fetching selected row from read-only live runtime data...', selectedKey, '', '', '', '', '', '', '', [], 'push');
  }

  function runtimeDataQueryFromControls(paginationRoot) {
    var searchInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-search-input]') : null;
    var filterFieldInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-field]') : null;
    var filterOperatorInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-operator]') : null;
    var filterValueInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-value]') : null;
    var secondFilterFieldInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-field-secondary]') : null;
    var secondFilterOperatorInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-operator-secondary]') : null;
    var secondFilterValueInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-value-secondary]') : null;
    var thirdFilterFieldInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-field-tertiary]') : null;
    var thirdFilterOperatorInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-operator-tertiary]') : null;
    var thirdFilterValueInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-filter-value-tertiary]') : null;
    var sortFieldInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-sort-field]') : null;
    var sortDirectionInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-sort-direction]') : null;
    var secondSortFieldInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-sort-field-secondary]') : null;
    var secondSortDirectionInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-sort-direction-secondary]') : null;
    var thirdSortFieldInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-sort-field-tertiary]') : null;
    var thirdSortDirectionInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-sort-direction-tertiary]') : null;
    var pageSizeInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-page-size-input]') : null;
    var filterField = filterFieldInput ? String(filterFieldInput.value || '').trim() : '';
    var filterOperator = filterOperatorInput ? String(filterOperatorInput.value || 'contains').trim() : 'contains';
    var filterValue = filterValueInput ? String(filterValueInput.value || '').trim() : '';
    var secondFilterField = secondFilterFieldInput ? String(secondFilterFieldInput.value || '').trim() : '';
    var secondFilterOperator = secondFilterOperatorInput ? String(secondFilterOperatorInput.value || 'contains').trim() : 'contains';
    var secondFilterValue = secondFilterValueInput ? String(secondFilterValueInput.value || '').trim() : '';
    var thirdFilterField = thirdFilterFieldInput ? String(thirdFilterFieldInput.value || '').trim() : '';
    var thirdFilterOperator = thirdFilterOperatorInput ? String(thirdFilterOperatorInput.value || 'contains').trim() : 'contains';
    var thirdFilterValue = thirdFilterValueInput ? String(thirdFilterValueInput.value || '').trim() : '';
    var filters = [];
    if (filterField && filterValue) {
      filters.push({ field: filterField, value: filterValue, operator: filterOperator || 'contains' });
    }
    if (secondFilterField && secondFilterValue && secondFilterField !== filterField) {
      filters.push({ field: secondFilterField, value: secondFilterValue, operator: secondFilterOperator || 'contains' });
    }
    if (thirdFilterField && thirdFilterValue && thirdFilterField !== filterField && thirdFilterField !== secondFilterField) {
      filters.push({ field: thirdFilterField, value: thirdFilterValue, operator: thirdFilterOperator || 'contains' });
    }
    var sortField = sortFieldInput ? String(sortFieldInput.value || '').trim() : '';
    var sortDirection = sortDirectionInput ? String(sortDirectionInput.value || '').trim().toLowerCase() : '';
    var secondSortField = secondSortFieldInput ? String(secondSortFieldInput.value || '').trim() : '';
    var secondSortDirection = secondSortDirectionInput ? String(secondSortDirectionInput.value || '').trim().toLowerCase() : '';
    var thirdSortField = thirdSortFieldInput ? String(thirdSortFieldInput.value || '').trim() : '';
    var thirdSortDirection = thirdSortDirectionInput ? String(thirdSortDirectionInput.value || '').trim().toLowerCase() : '';
    var sorts = [];
    if (sortField && sortDirection) {
      sorts.push({ field: sortField, direction: sortDirection });
    }
    if (secondSortField && secondSortDirection && secondSortField !== sortField) {
      sorts.push({ field: secondSortField, direction: secondSortDirection });
    }
    if (thirdSortField && thirdSortDirection && thirdSortField !== sortField && thirdSortField !== secondSortField) {
      sorts.push({ field: thirdSortField, direction: thirdSortDirection });
    }
    return {
      q: searchInput ? String(searchInput.value || '').trim() : '',
      filterField: filterField,
      filterValue: filterValue,
      filterOperator: filterOperator || 'contains',
      secondFilterField: secondFilterField,
      secondFilterValue: secondFilterValue,
      secondFilterOperator: secondFilterOperator || 'contains',
      thirdFilterField: thirdFilterField,
      thirdFilterValue: thirdFilterValue,
      thirdFilterOperator: thirdFilterOperator || 'contains',
      filters: filters,
      sortField: sortField,
      sortDirection: sortDirection,
      secondSortField: secondSortField,
      secondSortDirection: secondSortDirection,
      thirdSortField: thirdSortField,
      thirdSortDirection: thirdSortDirection,
      sorts: sorts,
      pageSize: pageSizeInput ? String(Math.min(100, Math.max(1, Number(pageSizeInput.value || 1) || 1))) : ''
    };
  }

  function refreshRuntimeDataForPage(button) {
    var page = button.hasAttribute('data-runtime-page-size-submit') ? '1' : (button.getAttribute('data-runtime-page') || '1');
    var pageSize = button.getAttribute('data-runtime-page-size') || '';
    var paginationRoot = button.closest('.no-code-pagination');
    var query = runtimeDataQueryFromControls(paginationRoot);
    if (button.hasAttribute('data-runtime-page-size-submit') || button.hasAttribute('data-runtime-page-submit')) {
      if (button.hasAttribute('data-runtime-page-submit')) {
        var pageInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-page-input]') : null;
        var pageCount = paginationRoot ? Number(paginationRoot.getAttribute('data-runtime-pagination-page-count') || 1) : 1;
        page = String(Math.min(Math.max(1, pageCount || 1), Math.max(1, Number(pageInput ? pageInput.value : page) || 1)));
      }
      var pageSizeInput = paginationRoot ? paginationRoot.querySelector('[data-runtime-page-size-input]') : null;
      pageSize = pageSizeInput ? pageSizeInput.value : pageSize;
    }
    pageSize = String(Math.min(100, Math.max(1, Number(pageSize || 1) || 1)));
    if (!pageSize || !hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!refreshButton) {
      return;
    }
    if (stopRuntimeDataFetchForFilterValidation(refreshButton, paginationRoot)) {
      return;
    }
    refreshRuntimeDataForScreen(screen, refreshButton, 'Fetching paginated read-only live runtime data...', '', page, pageSize, query.q, query.filterField, query.filterValue, query.sortField, query.sortDirection, query.filters, 'push', query.sorts);
  }

  function refreshRuntimeDataForSearch(button) {
    var paginationRoot = button.closest('.no-code-pagination');
    var query = runtimeDataQueryFromControls(paginationRoot);
    if (!query.q || !hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!refreshButton) {
      return;
    }
    if (stopRuntimeDataFetchForFilterValidation(refreshButton, paginationRoot)) {
      return;
    }
    refreshRuntimeDataForScreen(screen, refreshButton, 'Searching read-only live runtime data...', '', query.pageSize ? '1' : '', query.pageSize, query.q, query.filterField, query.filterValue, query.sortField, query.sortDirection, query.filters, 'push', query.sorts);
  }

  function refreshRuntimeDataForFieldFilter(button) {
    var paginationRoot = button.closest('.no-code-pagination');
    var query = runtimeDataQueryFromControls(paginationRoot);
    if (query.filters.length < 1 || !hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!refreshButton) {
      return;
    }
    if (stopRuntimeDataFetchForFilterValidation(refreshButton, paginationRoot)) {
      return;
    }
    refreshRuntimeDataForScreen(screen, refreshButton, 'Filtering read-only live runtime data...', '', query.pageSize ? '1' : '', query.pageSize, query.q, query.filterField, query.filterValue, query.sortField, query.sortDirection, query.filters, 'push', query.sorts);
  }

  function refreshRuntimeDataForSort(button) {
    var paginationRoot = button.closest('.no-code-pagination');
    var query = runtimeDataQueryFromControls(paginationRoot);
    if (!query.sortField || !query.sortDirection || !hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!refreshButton) {
      return;
    }
    if (stopRuntimeDataFetchForFilterValidation(refreshButton, paginationRoot)) {
      return;
    }
    refreshRuntimeDataForScreen(screen, refreshButton, 'Sorting read-only live runtime data...', '', query.pageSize ? '1' : '', query.pageSize, query.q, query.filterField, query.filterValue, query.sortField, query.sortDirection, query.filters, 'push', query.sorts);
  }

  function refreshRuntimeDataForSortHeader(button) {
    var fieldKey = button ? String(button.getAttribute('data-runtime-sort-field-key') || '').trim() : '';
    if (!fieldKey || !hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var controls = screen ? screen.querySelector('[data-runtime-data-controls]') : null;
    var sortButton = controls ? controls.querySelector('[data-runtime-sort-submit]') : null;
    var sortField = controls ? controls.querySelector('[data-runtime-sort-field]') : null;
    var sortDirection = controls ? controls.querySelector('[data-runtime-sort-direction]') : null;
    if (!sortButton || !sortField || !sortDirection) {
      return;
    }
    var currentField = String(sortField.value || '');
    var currentDirection = String(sortDirection.value || 'asc').toLowerCase();
    setRuntimeSelectValue(sortField, fieldKey);
    setRuntimeSelectValue(sortDirection, currentField === fieldKey && currentDirection === 'asc' ? 'desc' : 'asc');
    setRuntimeSelectValue(controls.querySelector('[data-runtime-sort-field-secondary]'), '');
    setRuntimeSelectValue(controls.querySelector('[data-runtime-sort-direction-secondary]'), 'asc');
    setRuntimeSelectValue(controls.querySelector('[data-runtime-sort-field-tertiary]'), '');
    setRuntimeSelectValue(controls.querySelector('[data-runtime-sort-direction-tertiary]'), 'asc');
    setRuntimeDataExtraVisibility(controls, 'sort', 'secondary', false);
    setRuntimeDataExtraVisibility(controls, 'sort', 'tertiary', false);
    syncRuntimeDataRowVisibility(controls);
    syncRuntimeSortHeaders(screen, fieldKey, sortDirection.value);
    refreshRuntimeDataForSort(sortButton);
  }

  function refreshRuntimeDataForQueryReset(button) {
    if (!hasRuntimeDataBinding()) {
      return;
    }
    var screen = button.closest('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!refreshButton) {
      return;
    }
    refreshRuntimeDataForScreen(screen, refreshButton, 'Clearing read-only runtime data query controls...', '', '', '', '', '', '', '', '', [], 'replace');
  }

  function refreshRuntimeDataFromBrowserUrl(forceReplay, browserHistoryMode) {
    if (!hasRuntimeDataBinding()) {
      return;
    }
    var query = runtimeDataQueryFromBrowserUrl();
    if (!runtimeDataBrowserUrlQueryIsPresent(query) && !forceReplay) {
      return;
    }
    var screen = document.querySelector('.no-code-screen[data-screen-type="list"]') || document.querySelector('.no-code-screen');
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    if (!screen || !refreshButton) {
      return;
    }
    refreshRuntimeDataForScreen(
      screen,
      refreshButton,
      'Loading read-only live runtime data from the browser URL...',
      query.selectedKey,
      query.page,
      query.pageSize,
      query.q,
      query.filterField,
      query.filterValue,
      query.sortField,
      query.sortDirection,
      query.filters,
      browserHistoryMode || 'replace',
      query.sorts
    );
  }

  function refreshRuntimePreview(button) {
    var screen = button.closest('.no-code-screen');
    if (hasRuntimeDataBinding()) {
      refreshRuntimeDataForScreen(screen, button, 'Fetching read-only live runtime data...');
      return;
    }
    saveRuntimeRefreshState(screen);
    window.location.reload();
  }

  function copyRuntimeOutboxDetailPath(button) {
    var draftRoot = button.closest('.no-code-intent-draft');
    var copyStatus = draftRoot ? draftRoot.querySelector('[data-runtime-outbox-detail-copy-status]') : null;
    var detailPath = button.getAttribute('data-runtime-outbox-detail-path') || '';
    if (!detailPath) {
      if (copyStatus) {
        copyStatus.textContent = 'No outbox detail path is available to copy.';
      }
      return;
    }
    if (!navigator.clipboard || typeof navigator.clipboard.writeText !== 'function') {
      if (copyStatus) {
        copyStatus.textContent = 'Clipboard is unavailable; select the outbox path manually.';
      }
      return;
    }
    navigator.clipboard.writeText(detailPath).then(function () {
      if (copyStatus) {
        copyStatus.textContent = 'Outbox detail path copied.';
      }
    }).catch(function () {
      if (copyStatus) {
        copyStatus.textContent = 'Copy failed; select the outbox path manually.';
      }
    });
  }

  function setRuntimeExecuteStatus(screen, state, message, outboxDetailPath) {
    var status = screen ? screen.querySelector('[data-runtime-execute-status]') : null;
    if (!status) {
      return;
    }
    status.textContent = message;
    status.setAttribute('data-state', state);
    if (outboxDetailPath) {
      status.setAttribute('data-runtime-outbox-detail-path', outboxDetailPath);
    } else {
      status.removeAttribute('data-runtime-outbox-detail-path');
    }
  }

  function writeRuntimeExecuteAvailability(screen, draft, hasBlockingChecks) {
    var executeButton = screen ? screen.querySelector('[data-runtime-execute]') : null;
    if (!executeButton) {
      return;
    }
    writeRuntimeOutboxDetailCopy(screen, '');
    writeRuntimeResultRefresh(screen, false);
    if (!hasExecutionBinding()) {
      executeButton.disabled = true;
      executeButton.setAttribute('data-runtime-execute-state', 'unavailable');
      setRuntimeExecuteStatus(screen, 'unavailable', 'Server execution is available from an authenticated current or alias preview.');
      writeRuntimeFlow(screen, 'waiting', '');
      return;
    }
    if (hasBlockingChecks) {
      executeButton.disabled = true;
      executeButton.setAttribute('data-runtime-execute-state', 'blocked');
      setRuntimeExecuteStatus(screen, 'blocked', 'Resolve draft blockers before server submission.');
      writeRuntimeFlow(screen, 'blocked', '');
      return;
    }
    executeButton.disabled = false;
    executeButton.setAttribute('data-runtime-execute-state', 'ready');
    executeButton.setAttribute('data-runtime-execute-action', draft.action_key || '');
    setRuntimeExecuteStatus(screen, 'ready', 'Server execution endpoint is ready: ' + executionBinding.execution_url);
    writeRuntimeFlow(screen, 'ready', '');
  }

  function writeActionFeedback(button, result) {
    var screen = button.closest('.no-code-screen');
    var feedback = screen ? screen.querySelector('.no-code-action-feedback') : null;
    if (!feedback) {
      return;
    }
    writeRuntimeOutboxDetailCopy(screen, '');
    writeRuntimeResultRefresh(screen, false);
    writeRuntimeFlow(screen, 'waiting', '');

    if (result && result.ok) {
      feedback.textContent = 'Action intent is ready: ' + (result.intent && result.intent.operation_key ? result.intent.operation_key : 'operation');
      feedback.setAttribute('data-state', 'success');
      feedback.removeAttribute('data-runtime-outbox-detail-path');
      return;
    }

    feedback.textContent = result && result.message ? result.message : 'Action intent could not be prepared.';
    feedback.setAttribute('data-state', 'error');
    feedback.removeAttribute('data-runtime-outbox-detail-path');
  }

  function runtimeExecutionSyncStatus(payload) {
    var status = payload
      && payload.result
      && payload.result.executor_result
      && payload.result.executor_result.item
      ? payload.result.executor_result.item.status
      : '';
    return typeof status === 'string' && status ? status : '';
  }

  function runtimeExecutionOutboxItem(payload) {
    return payload
      && payload.result
      && payload.result.executor_result
      && payload.result.executor_result.item
      ? payload.result.executor_result.item
      : null;
  }

  function runtimeExecutionOutboxDetailPath(item) {
    if (!item || !item.dedupe_key) {
      return '';
    }
    var projectKey = item.project_key || executionBinding.project_key || preview.project_key || '';
    if (!projectKey) {
      return '';
    }
    return '/projects/' + encodeURIComponent(projectKey) + '/sync-outbox/' + encodeURIComponent(item.dedupe_key);
  }

  function runtimeExecutionOutboxStatusPath(detailPath) {
    return detailPath ? detailPath + '.json' : '';
  }

  function runtimeOutboxStatusMessage(payload) {
    if (!payload || !payload.ok) {
      return 'Live outbox check did not return a usable status.';
    }
    var status = payload.status || '';
    var handoff = payload.handoff && payload.handoff.label ? payload.handoff.label : '';
    var nextStep = payload.handoff && payload.handoff.next_step ? payload.handoff.next_step : '';
    var message = status ? 'Live outbox check: ' + status + '.' : 'Live outbox check completed.';
    if (handoff) {
      message += ' ' + handoff;
    }
    if (nextStep) {
      message += ' Next step: ' + nextStep;
    }
    return message;
  }

  function runtimeOutboxTimeoutMessage(maxAttempts) {
    return 'Live outbox check stopped after ' + maxAttempts + ' attempts. The item is still queued or processing; use Refresh preview later or open the outbox detail.';
  }

  function runtimeOutboxFlowState(payload) {
    var handoffState = payload && payload.handoff && payload.handoff.state ? payload.handoff.state : '';
    if (handoffState === 'complete') {
      return 'complete';
    }
    if (handoffState === 'needs_review') {
      return 'needs_review';
    }
    return 'tracking';
  }

  function runtimeOutboxStatusShouldContinue(payload) {
    var handoffState = payload && payload.handoff && payload.handoff.state ? payload.handoff.state : '';
    return handoffState === 'queued' || handoffState === 'processing';
  }

  function runtimeTextBase(element, attributeName) {
    if (!element) {
      return '';
    }
    var existing = element.getAttribute(attributeName) || '';
    if (existing) {
      return existing;
    }
    existing = element.textContent || '';
    element.setAttribute(attributeName, existing);
    return existing;
  }

  function pollRuntimeOutboxStatus(screen, detailPath, feedback, attempt) {
    var statusPath = runtimeExecutionOutboxStatusPath(detailPath);
    if (!statusPath) {
      return;
    }
    var currentAttempt = attempt || 1;
    var maxAttempts = 3;
    var status = screen ? screen.querySelector('[data-runtime-execute-status]') : null;
    var statusBaseText = runtimeTextBase(status, 'data-runtime-outbox-status-base-text');
    var feedbackBaseText = runtimeTextBase(feedback, 'data-runtime-outbox-feedback-base-text');
    if (status) {
      status.setAttribute('data-runtime-outbox-status-path', statusPath);
      status.setAttribute('data-runtime-outbox-status-poll-state', 'checking');
      status.setAttribute('data-runtime-outbox-status-poll-count', String(currentAttempt));
    }
    writeRuntimeFlow(screen, 'tracking', detailPath);
    fetch(statusPath, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json'
      }
    }).then(function (response) {
      return response.json().catch(function () {
        return {
          ok: false,
          error: 'invalid_json_response'
        };
      });
    }).then(function (payload) {
      var message = runtimeOutboxStatusMessage(payload);
      var flowState = runtimeOutboxFlowState(payload);
      if (status) {
        status.textContent = statusBaseText + ' ' + message;
        status.setAttribute('data-runtime-outbox-status-poll-state', payload && payload.ok ? 'checked' : 'error');
      }
      if (feedback) {
        feedback.textContent = feedbackBaseText + ' ' + message;
      }
      writeRuntimeFlow(screen, flowState, detailPath);
      if (payload && payload.ok && runtimeOutboxStatusShouldContinue(payload) && currentAttempt < maxAttempts) {
        if (status) {
          status.setAttribute('data-runtime-outbox-status-poll-state', 'waiting');
        }
        window.setTimeout(function () {
          pollRuntimeOutboxStatus(screen, detailPath, feedback, currentAttempt + 1);
        }, 250);
        return;
      }
      if (payload && payload.ok && runtimeOutboxStatusShouldContinue(payload) && currentAttempt >= maxAttempts && status) {
        var timeoutMessage = runtimeOutboxTimeoutMessage(maxAttempts);
        status.setAttribute('data-runtime-outbox-status-poll-state', 'timeout');
        status.textContent = statusBaseText + ' ' + message + ' ' + timeoutMessage;
        if (feedback) {
          feedback.textContent = feedbackBaseText + ' ' + message + ' ' + timeoutMessage;
        }
        writeRuntimeFlow(screen, 'timeout', detailPath);
      }
      if (payload && payload.ok && flowState === 'complete' && hasRuntimeDataBinding()) {
        refreshRuntimeDataForScreen(
          screen,
          screen ? screen.querySelector('[data-runtime-result-refresh]') : null,
          'Sync outbox is done. Fetching read-only live runtime data...'
        );
      }
    }).catch(function () {
      if (status) {
        status.setAttribute('data-runtime-outbox-status-poll-state', 'error');
      }
    });
  }

  function runtimeExecutionAcceptedMessage(payload) {
    var message = 'Server execution accepted.';
    var item = runtimeExecutionOutboxItem(payload);
    var syncStatus = item && typeof item.status === 'string' ? item.status : runtimeExecutionSyncStatus(payload);
    var detailPath = runtimeExecutionAcceptedDetailPath(payload);
    var demoProcessing = payload && payload.demo_processing ? payload.demo_processing : null;
    if (syncStatus) {
      message += ' Sync outbox status: ' + syncStatus + '.';
    }
    if (item && item.id) {
      message += ' Sync outbox item: #' + item.id + '.';
    }
    if (item && item.operation_key) {
      message += ' Operation: ' + item.operation_key + '.';
    }
    if (detailPath) {
      message += ' Review sync outbox: ' + detailPath + '.';
    }
    if (syncStatus === 'pending' || syncStatus === 'running') {
      message += ' Next result check: process the sync outbox item, then reload this generated preview artifact or open the outbox detail.';
    }
    if (demoProcessing && demoProcessing.processed && demoProcessing.outcome === 'done') {
      message += ' Demo processing completed this item; reload this generated preview artifact to re-read the current preview.';
    } else if (demoProcessing && demoProcessing.error) {
      message += ' Demo processing did not run: ' + demoProcessing.error + '.';
    }
    return message;
  }

  function runtimeExecutionAcceptedDetailPath(payload) {
    return runtimeExecutionOutboxDetailPath(runtimeExecutionOutboxItem(payload));
  }

  function submitRuntimeAction(button) {
    var screen = button.closest('.no-code-screen');
    var feedback = screen ? screen.querySelector('.no-code-action-feedback') : null;
    if (!hasExecutionBinding()) {
      setRuntimeExecuteStatus(screen, 'error', 'Server execution binding is not available for this preview.');
      return;
    }

    var action = firstScreenAction(screen);
    if (!action) {
      setRuntimeExecuteStatus(screen, 'error', 'No action metadata is available for server execution.');
      return;
    }

    var input = collectScreenInputFromScreen(screen);
    var localResult = buildActionIntent(action, input);
    if (!localResult.ok) {
      setRuntimeExecuteStatus(screen, 'error', localResult.message || 'Action intent could not be prepared.');
      return;
    }

    var formData = new FormData();
    formData.append('_csrf', executionBinding.csrf_token || '');
    formData.append('project_key', executionBinding.project_key || preview.project_key || '');
    formData.append('artifact_key', executionBinding.artifact_key || '');
    formData.append('action_key', action.action_key || '');
    if (executionBinding.demo_processing === 'available') {
      formData.append('runtime_demo_process', '1');
    }
    Object.keys(input).forEach(function (fieldKey) {
      formData.append('input[' + fieldKey + ']', input[fieldKey]);
    });

    button.disabled = true;
    button.setAttribute('data-runtime-execute-state', 'working');
    setRuntimeExecuteStatus(screen, 'working', 'Submitting action to server...');
    writeRuntimeOutboxDetailCopy(screen, '');
    writeRuntimeResultRefresh(screen, false);
    writeRuntimeFlow(screen, 'working', '');
    if (feedback) {
      feedback.textContent = 'Submitting action to server...';
      feedback.setAttribute('data-state', 'working');
      feedback.removeAttribute('data-runtime-outbox-detail-path');
    }

    fetch(executionBinding.execution_url, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json'
      }
    }).then(function (response) {
      return response.json().catch(function () {
        return {
          ok: false,
          message: 'Server execution response was not JSON.',
          error: 'invalid_json_response'
        };
      });
    }).then(function (payload) {
      if (payload && payload.ok) {
        var acceptedMessage = runtimeExecutionAcceptedMessage(payload);
        var acceptedDetailPath = runtimeExecutionAcceptedDetailPath(payload);
        button.setAttribute('data-runtime-execute-state', 'success');
        setRuntimeExecuteStatus(screen, 'success', acceptedMessage, acceptedDetailPath);
        if (feedback) {
          feedback.textContent = acceptedMessage;
          feedback.setAttribute('data-state', 'success');
          if (acceptedDetailPath) {
            feedback.setAttribute('data-runtime-outbox-detail-path', acceptedDetailPath);
          } else {
            feedback.removeAttribute('data-runtime-outbox-detail-path');
          }
        }
        writeRuntimeOutboxDetailCopy(screen, acceptedDetailPath);
        writeRuntimeResultRefresh(screen, true);
        writeRuntimeFlow(screen, 'accepted', acceptedDetailPath);
        pollRuntimeOutboxStatus(screen, acceptedDetailPath, feedback);
        return;
      }

      button.disabled = false;
      button.setAttribute('data-runtime-execute-state', 'error');
      var message = payload && (payload.message || payload.error) ? (payload.message || payload.error) : 'Server execution failed.';
      setRuntimeExecuteStatus(screen, 'error', message);
      writeRuntimeOutboxDetailCopy(screen, '');
      writeRuntimeResultRefresh(screen, false);
      writeRuntimeFlow(screen, 'error', '');
      if (feedback) {
        feedback.textContent = message;
        feedback.setAttribute('data-state', 'error');
        feedback.removeAttribute('data-runtime-outbox-detail-path');
      }
    }).catch(function () {
      button.disabled = false;
      button.setAttribute('data-runtime-execute-state', 'error');
      setRuntimeExecuteStatus(screen, 'error', 'Server execution request failed.');
      writeRuntimeOutboxDetailCopy(screen, '');
      writeRuntimeResultRefresh(screen, false);
      writeRuntimeFlow(screen, 'error', '');
      if (feedback) {
        feedback.textContent = 'Server execution request failed.';
        feedback.setAttribute('data-state', 'error');
        feedback.removeAttribute('data-runtime-outbox-detail-path');
      }
    });
  }

  window.__noCodeRuntimePreview = preview;
  window.__noCodeRuntimeExecutionBinding = executionBinding;
  window.__noCodeRuntimeDispatches = [];
  window.noCodeRuntimeDispatchAction = function (actionKey, input) {
    var action = findAction(actionKey);
    var result;
    if (!action) {
      result = actionError('action was not found: ' + actionKey);
    } else if (!action.enabled) {
      result = actionError('action is not enabled: ' + actionKey);
    } else {
      result = buildActionIntent(action, input || {});
    }
    window.__noCodeRuntimeDispatches.push(result);
    return result;
  };

  function bindScreenControls(screen) {
    if (!screen) {
      return;
    }
    screen.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (control) {
      if (control.getAttribute('data-runtime-input-bound') === 'true') {
        return;
      }
      control.setAttribute('data-runtime-input-bound', 'true');
      control.addEventListener('input', function () {
        writeIntentDraft(screen);
      });
      control.addEventListener('change', function () {
        writeIntentDraft(screen);
      });
    });
  }

  function bindRuntimeListSelection(root) {
    if (!root) {
      return;
    }
    root.querySelectorAll('[data-runtime-row-select]').forEach(function (button) {
      if (button.getAttribute('data-runtime-row-select-bound') === 'true') {
        return;
      }
      button.setAttribute('data-runtime-row-select-bound', 'true');
      button.addEventListener('click', function () {
        refreshRuntimeDataForSelectedRow(button);
      });
    });
  }

  function bindRuntimeSortHeaders(root) {
    if (!root) {
      return;
    }
    root.querySelectorAll('[data-runtime-sort-header]').forEach(function (button) {
      if (button.getAttribute('data-runtime-sort-header-bound') === 'true') {
        return;
      }
      button.setAttribute('data-runtime-sort-header-bound', 'true');
      button.addEventListener('click', function () {
        refreshRuntimeDataForSortHeader(button);
      });
    });
  }

  function bindRuntimePaginationControls(root) {
    if (!root) {
      return;
    }
    root.querySelectorAll('[data-runtime-data-controls]').forEach(function (controls) {
      syncRuntimeDataFilterOperatorChoices(controls);
      syncRuntimeDataRowVisibility(controls);
    });
    root.querySelectorAll('[data-runtime-filter-field], [data-runtime-filter-field-secondary], [data-runtime-filter-field-tertiary]').forEach(function (select) {
      if (select.getAttribute('data-runtime-filter-field-bound') === 'true') {
        return;
      }
      select.setAttribute('data-runtime-filter-field-bound', 'true');
      select.addEventListener('change', function () {
        syncRuntimeDataFilterOperatorChoices(select.closest('[data-runtime-data-controls]'));
      });
    });
    root.querySelectorAll('[data-runtime-page], [data-runtime-page-size], [data-runtime-page-size-submit], [data-runtime-page-submit], [data-runtime-search-submit], [data-runtime-filter-submit], [data-runtime-sort-submit], [data-runtime-query-reset]').forEach(function (button) {
      if (button.getAttribute('data-runtime-pagination-bound') === 'true') {
        return;
      }
      button.setAttribute('data-runtime-pagination-bound', 'true');
      button.addEventListener('click', function () {
        if (button.hasAttribute('data-runtime-search-submit')) {
          refreshRuntimeDataForSearch(button);
          return;
        }
        if (button.hasAttribute('data-runtime-filter-submit')) {
          refreshRuntimeDataForFieldFilter(button);
          return;
        }
        if (button.hasAttribute('data-runtime-sort-submit')) {
          refreshRuntimeDataForSort(button);
          return;
        }
        if (button.hasAttribute('data-runtime-query-reset')) {
          refreshRuntimeDataForQueryReset(button);
          return;
        }
        refreshRuntimeDataForPage(button);
      });
    });
    root.querySelectorAll('[data-runtime-filter-add], [data-runtime-sort-add], [data-runtime-filter-remove], [data-runtime-sort-remove]').forEach(function (button) {
      if (button.getAttribute('data-runtime-row-builder-bound') === 'true') {
        return;
      }
      button.setAttribute('data-runtime-row-builder-bound', 'true');
      button.addEventListener('click', function () {
        if (button.hasAttribute('data-runtime-filter-add')) {
          revealRuntimeDataExtraRow(button, 'filter');
          return;
        }
        if (button.hasAttribute('data-runtime-sort-add')) {
          revealRuntimeDataExtraRow(button, 'sort');
          return;
        }
        if (button.hasAttribute('data-runtime-filter-remove')) {
          removeRuntimeDataExtraRow(button, 'filter');
          return;
        }
        removeRuntimeDataExtraRow(button, 'sort');
      });
    });
  }

  document.querySelectorAll('.no-code-actions button[data-action-key]').forEach(function (button) {
    button.addEventListener('click', function () {
      button.setAttribute('data-action-state', 'working');
      var screen = button.closest('.no-code-screen');
      var feedback = screen ? screen.querySelector('.no-code-action-feedback') : null;
      if (feedback) {
        feedback.textContent = 'Preparing action intent...';
        feedback.setAttribute('data-state', 'working');
      }
      var result = window.noCodeRuntimeDispatchAction(button.getAttribute('data-action-key') || '', collectScreenInput(button));
      button.setAttribute('data-action-state', result && result.ok ? 'success' : 'error');
      writeActionFeedback(button, result);
      if (screen) {
        writeIntentDraft(screen);
      }
    });
  });

  document.querySelectorAll('[data-intent-draft-copy]').forEach(function (button) {
    button.addEventListener('click', function () {
      copyIntentDraft(button);
    });
  });

  document.querySelectorAll('[data-runtime-execute]').forEach(function (button) {
    button.addEventListener('click', function () {
      submitRuntimeAction(button);
    });
  });

  document.querySelectorAll('[data-runtime-outbox-detail-copy]').forEach(function (button) {
    button.addEventListener('click', function () {
      copyRuntimeOutboxDetailPath(button);
    });
  });

  document.querySelectorAll('[data-runtime-result-refresh]').forEach(function (button) {
    button.addEventListener('click', function () {
      refreshRuntimePreview(button);
    });
  });

  document.querySelectorAll('.no-code-screen').forEach(function (screen) {
    restoreRuntimeRefreshState(screen);
    writeIntentDraft(screen);
    bindScreenControls(screen);
    bindRuntimeListSelection(screen);
    bindRuntimeSortHeaders(screen);
    bindRuntimePaginationControls(screen);
    if (hasRuntimeDataBinding() && screen.getAttribute('data-screen-type') === 'list') {
      var render = existingPreviewScreenRender(screen.getAttribute('data-screen-key') || '');
      if (render) {
        applyRuntimeDataScreen(render);
      }
    }
  });
  if (window.addEventListener) {
    window.addEventListener('popstate', function () {
      refreshRuntimeDataFromBrowserUrl(true, 'none');
    });
  }
  refreshRuntimeDataFromBrowserUrl();
}());
JS;
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,mixed> $input
 * @param callable(array<string,mixed>):array<string,mixed> $dispatcher
 * @return array{ok:bool,executed:bool,intent:array<string,mixed>,result:array<string,mixed>|null,error:string,message:string}
 */
function app_no_code_runtime_dispatch_action(
    array $definition,
    string $actionKey,
    array $input,
    callable $dispatcher,
): array
{
    $actionLookup = app_no_code_runtime_find_action($definition, $actionKey);
    if (!$actionLookup['ok']) {
        return app_no_code_runtime_dispatch_error($actionLookup['error']);
    }

    $action = $actionLookup['action'];
    if ((string) ($action['availability'] ?? 'disabled') !== 'enabled') {
        return app_no_code_runtime_dispatch_error('action is not enabled: ' . $actionKey);
    }

    $intentResult = app_no_code_runtime_action_intent($definition, $action, $input);
    if (!$intentResult['ok']) {
        return app_no_code_runtime_dispatch_error($intentResult['error'], $intentResult['intent']);
    }

    $result = $dispatcher($intentResult['intent']);

    return [
        'ok' => true,
        'executed' => true,
        'intent' => $intentResult['intent'],
        'result' => $result,
        'error' => '',
        'message' => '',
    ];
}

/**
 * @return array{ok:bool,executed:bool,intent:array<string,mixed>,result:array<string,mixed>|null,error:string,message:string}
 */
function app_no_code_runtime_dispatch_error(string $error, array $intent = []): array
{
    return [
        'ok' => false,
        'executed' => false,
        'intent' => $intent,
        'result' => null,
        'error' => $error,
        'message' => app_no_code_runtime_validation_message($error),
    ];
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,mixed> $post
 * @param array<string,mixed> $expectedBinding
 * @param callable(array<string,mixed>):array<string,mixed> $dispatcher
 * @return array{ok:bool,executed:bool,request:array<string,mixed>,intent:array<string,mixed>,result:array<string,mixed>|null,error:string,message:string}
 */
function app_no_code_runtime_execute_request_from_post(
    array $definition,
    string $requestMethod,
    array $post,
    array $expectedBinding,
    callable $dispatcher,
): array
{
    $request = app_no_code_runtime_execution_request_from_post($requestMethod, $post, $expectedBinding);
    if (!$request['ok']) {
        return app_no_code_runtime_execution_response_error($request['error'], $request);
    }

    $dispatch = app_no_code_runtime_dispatch_action(
        $definition,
        $request['action_key'],
        $request['input'],
        $dispatcher,
    );

    return [
        'ok' => $dispatch['ok'],
        'executed' => $dispatch['executed'],
        'request' => $request,
        'intent' => $dispatch['intent'],
        'result' => $dispatch['result'],
        'error' => $dispatch['error'],
        'message' => $dispatch['message'],
    ];
}

/**
 * @param array<string,mixed> $request
 * @return array{ok:bool,executed:bool,request:array<string,mixed>,intent:array<string,mixed>,result:array<string,mixed>|null,error:string,message:string}
 */
function app_no_code_runtime_execution_response_error(string $error, array $request = []): array
{
    return [
        'ok' => false,
        'executed' => false,
        'request' => $request,
        'intent' => [],
        'result' => null,
        'error' => $error,
        'message' => app_no_code_runtime_validation_message($error),
    ];
}

/**
 * @param array<string,mixed> $execution
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_runtime_execution_endpoint_response(array $execution): array
{
    $ok = (bool) ($execution['ok'] ?? false);
    $error = app_no_code_runtime_string_value($execution['error'] ?? '');
    $message = app_no_code_runtime_string_value($execution['message'] ?? '');
    $payload = [
        'ok' => $ok,
        'executed' => (bool) ($execution['executed'] ?? false),
        'error' => $error,
        'message' => $message,
        'request' => is_array($execution['request'] ?? null) ? $execution['request'] : [],
        'intent' => is_array($execution['intent'] ?? null) ? $execution['intent'] : [],
        'result' => is_array($execution['result'] ?? null) ? $execution['result'] : null,
    ];

    return [
        'status_code' => app_no_code_runtime_execution_status_code($ok, $error),
        'payload' => $payload,
    ];
}

function app_no_code_runtime_execution_status_code(bool $ok, string $error): int
{
    if ($ok) {
        return 200;
    }

    if ($error === 'runtime execution requires POST' || $error === 'runtime execution action key is missing' || $error === 'runtime execution input must be an object') {
        return 400;
    }

    if ($error === 'runtime execution csrf token is invalid') {
        return 403;
    }

    if ($error === 'runtime execution project binding does not match' || $error === 'runtime execution artifact binding does not match') {
        return 409;
    }

    return 422;
}

/**
 * @param array<string,mixed> $post
 * @param array<string,mixed> $expectedBinding
 * @return array{ok:bool,action_key:string,input:array<string,mixed>,binding:array<string,string>,error:string,message:string}
 */
function app_no_code_runtime_execution_request_from_post(
    string $requestMethod,
    array $post,
    array $expectedBinding,
): array
{
    if (strtoupper($requestMethod) !== 'POST') {
        return app_no_code_runtime_execution_request_error('runtime execution requires POST');
    }

    $expectedCsrfToken = app_no_code_runtime_string_value($expectedBinding['csrf_token'] ?? '');
    $submittedCsrfToken = app_no_code_runtime_string_value($post['_csrf'] ?? $post['csrf_token'] ?? '');
    if ($expectedCsrfToken === '' || !hash_equals($expectedCsrfToken, $submittedCsrfToken)) {
        return app_no_code_runtime_execution_request_error('runtime execution csrf token is invalid');
    }

    $projectKey = app_no_code_runtime_string_value($expectedBinding['project_key'] ?? '');
    $submittedProjectKey = app_no_code_runtime_string_value($post['project_key'] ?? '');
    if ($projectKey === '' || $submittedProjectKey !== $projectKey) {
        return app_no_code_runtime_execution_request_error('runtime execution project binding does not match');
    }

    $artifactKey = app_no_code_runtime_string_value($expectedBinding['artifact_key'] ?? '');
    $submittedArtifactKey = app_no_code_runtime_string_value($post['artifact_key'] ?? '');
    if ($artifactKey === '' || $submittedArtifactKey !== $artifactKey) {
        return app_no_code_runtime_execution_request_error('runtime execution artifact binding does not match');
    }

    $actionKey = app_no_code_runtime_string_value($post['action_key'] ?? '');
    if ($actionKey === '') {
        return app_no_code_runtime_execution_request_error('runtime execution action key is missing');
    }

    $rawInput = $post['input'] ?? [];
    if (!is_array($rawInput)) {
        return app_no_code_runtime_execution_request_error('runtime execution input must be an object');
    }

    $input = [];
    foreach ($rawInput as $fieldKey => $value) {
        $normalizedFieldKey = app_no_code_runtime_string_value($fieldKey);
        if ($normalizedFieldKey === '' || is_array($value) || is_object($value)) {
            continue;
        }
        $input[$normalizedFieldKey] = $value;
    }

    $binding = [
        'project_key' => $projectKey,
        'artifact_key' => $artifactKey,
    ];

    foreach (['source_output_key', 'revision_id'] as $optionalKey) {
        $value = app_no_code_runtime_string_value($expectedBinding[$optionalKey] ?? '');
        if ($value !== '') {
            $binding[$optionalKey] = $value;
        }
    }

    return [
        'ok' => true,
        'action_key' => $actionKey,
        'input' => $input,
        'binding' => $binding,
        'error' => '',
        'message' => '',
    ];
}

/**
 * @return array{ok:bool,action_key:string,input:array<string,mixed>,binding:array<string,string>,error:string,message:string}
 */
function app_no_code_runtime_execution_request_error(string $error): array
{
    return [
        'ok' => false,
        'action_key' => '',
        'input' => [],
        'binding' => [],
        'error' => $error,
        'message' => app_no_code_runtime_validation_message($error),
    ];
}

/**
 * @param mixed $value
 */
function app_no_code_runtime_string_value($value): string
{
    if (is_string($value) || is_int($value) || is_float($value)) {
        return trim((string) $value);
    }

    return '';
}

function app_no_code_runtime_validation_message(string $error): string
{
    $parts = array_values(array_filter(array_map('trim', explode(',', $error)), static fn (string $part): bool => $part !== ''));
    if ($parts === []) {
        return 'Action intent could not be prepared.';
    }

    $messages = [];
    foreach ($parts as $part) {
        if (str_starts_with($part, 'input.missing:')) {
            $messages[] = 'Required input is missing: ' . substr($part, strlen('input.missing:'));
            continue;
        }
        if (str_starts_with($part, 'input.readonly:')) {
            $messages[] = 'Input is read-only: ' . substr($part, strlen('input.readonly:'));
            continue;
        }

        $messages[] = $part;
    }

    return implode(', ', array_values(array_unique($messages)));
}

/**
 * @param array<string,mixed> $definition
 * @return array{ok:bool,contract:array<string,mixed>,action:array<string,mixed>,error:string}
 */
function app_no_code_runtime_find_action(array $definition, string $actionKey): array
{
    foreach (($definition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        foreach (($contract['actions'] ?? []) as $action) {
            if (!is_array($action)) {
                continue;
            }

            if ((string) ($action['action_key'] ?? '') === $actionKey) {
                return [
                    'ok' => true,
                    'contract' => $contract,
                    'action' => $action,
                    'error' => '',
                ];
            }
        }
    }

    return [
        'ok' => false,
        'contract' => [],
        'action' => [],
        'error' => 'action が見つかりません: ' . $actionKey,
    ];
}

/**
 * @param mixed $value
 */
function app_no_code_runtime_required_value_is_empty($value): bool
{
    return $value === null || (is_string($value) && trim($value) === '');
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,mixed> $action
 * @param array<string,mixed> $input
 * @return array{ok:bool,intent:array<string,mixed>,error:string}
 */
function app_no_code_runtime_action_intent(array $definition, array $action, array $input): array
{
    $intent = [
        'intent_version' => 'no-code-runtime-action-intent-v0',
        'runtime_version' => app_no_code_runtime_version(),
        'project_key' => (string) ($definition['project_key'] ?? ''),
        'operation_key' => (string) ($action['operation_key'] ?? ''),
        'operation_type' => (string) ($action['operation_type'] ?? ''),
        'payload' => [
            'key' => [],
            'input' => [],
            'filter' => [],
        ],
    ];
    $failed = [];

    foreach (($action['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fieldKey = (string) ($field['field_key'] ?? '');
        if ($fieldKey === '') {
            continue;
        }

        $present = array_key_exists($fieldKey, $input);
        if ((!$present || app_no_code_runtime_required_value_is_empty($input[$fieldKey] ?? null)) && (bool) ($field['required'] ?? false)) {
            $failed[] = 'input.missing:' . $fieldKey;
            continue;
        }
        if (!$present) {
            continue;
        }

        $role = (string) ($field['role'] ?? '');
        if ($role === 'key') {
            $intent['payload']['key'][$fieldKey] = $input[$fieldKey];
        } elseif ($role === 'filter') {
            $intent['payload']['filter'][$fieldKey] = $input[$fieldKey];
        } elseif ($role === 'input') {
            if (!((bool) ($field['client_write'] ?? false))) {
                $failed[] = 'input.readonly:' . $fieldKey;
                continue;
            }
            $intent['payload']['input'][$fieldKey] = $input[$fieldKey];
        }
    }

    if ($failed !== []) {
        return [
            'ok' => false,
            'intent' => $intent,
            'error' => implode(', ', array_values(array_unique($failed))),
        ];
    }

    return [
        'ok' => true,
        'intent' => $intent,
        'error' => '',
    ];
}
