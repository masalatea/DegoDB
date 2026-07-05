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
        '<section class="no-code-screen no-code-screen-' . app_no_code_runtime_html_escape($screenType) . '" role="region" aria-labelledby="' . app_no_code_runtime_html_escape($screenTitleId) . '" data-screen-key="' . app_no_code_runtime_html_escape($screenKey) . '" data-screen-state="' . app_no_code_runtime_html_escape($screenState) . '">',
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
        '<div class="no-code-action-feedback" role="status" aria-live="polite" data-state="idle">Select an enabled action to preview its intent.</div>',
        app_no_code_runtime_render_action_intent_draft_html($actions),
        $body,
        '</section>',
    ]);
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
        '<span class="no-code-runtime-result-refresh-status" role="status" aria-live="polite" data-runtime-result-refresh-status>Refresh preview is available after server submit.</span>',
        '<button type="button" data-runtime-outbox-detail-copy disabled>Copy outbox path</button>',
        '<a class="no-code-runtime-outbox-detail-link" data-runtime-outbox-detail-link hidden href="">Open outbox detail</a>',
        '<span class="no-code-runtime-outbox-detail-copy-status" role="status" aria-live="polite" data-runtime-outbox-detail-copy-status>Outbox detail path will be available after server submit.</span>',
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
                'datetime' => 'datetime-local',
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

  function writeRuntimeResultRefresh(screen, enabled) {
    var refreshButton = screen ? screen.querySelector('[data-runtime-result-refresh]') : null;
    var refreshStatus = screen ? screen.querySelector('[data-runtime-result-refresh-status]') : null;
    if (!refreshButton) {
      return;
    }
    refreshButton.disabled = !enabled;
    refreshButton.setAttribute('data-runtime-result-refresh-state', enabled ? 'ready' : 'waiting');
    if (refreshStatus) {
      refreshStatus.textContent = enabled
        ? 'Process the sync outbox item, then use Refresh preview to reload this screen.'
        : 'Refresh preview is available after server submit.';
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

  function refreshRuntimePreview(button) {
    var screen = button.closest('.no-code-screen');
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
      return;
    }
    if (hasBlockingChecks) {
      executeButton.disabled = true;
      executeButton.setAttribute('data-runtime-execute-state', 'blocked');
      setRuntimeExecuteStatus(screen, 'blocked', 'Resolve draft blockers before server submission.');
      return;
    }
    executeButton.disabled = false;
    executeButton.setAttribute('data-runtime-execute-state', 'ready');
    executeButton.setAttribute('data-runtime-execute-action', draft.action_key || '');
    setRuntimeExecuteStatus(screen, 'ready', 'Server execution endpoint is ready: ' + executionBinding.execution_url);
  }

  function writeActionFeedback(button, result) {
    var screen = button.closest('.no-code-screen');
    var feedback = screen ? screen.querySelector('.no-code-action-feedback') : null;
    if (!feedback) {
      return;
    }
    writeRuntimeOutboxDetailCopy(screen, '');
    writeRuntimeResultRefresh(screen, false);

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

  function runtimeExecutionAcceptedMessage(payload) {
    var message = 'Server execution accepted.';
    var item = runtimeExecutionOutboxItem(payload);
    var syncStatus = item && typeof item.status === 'string' ? item.status : runtimeExecutionSyncStatus(payload);
    var detailPath = runtimeExecutionAcceptedDetailPath(payload);
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
      message += ' Next result check: process the sync outbox item, then use Refresh preview to reload this screen.';
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
    Object.keys(input).forEach(function (fieldKey) {
      formData.append('input[' + fieldKey + ']', input[fieldKey]);
    });

    button.disabled = true;
    button.setAttribute('data-runtime-execute-state', 'working');
    setRuntimeExecuteStatus(screen, 'working', 'Submitting action to server...');
    writeRuntimeOutboxDetailCopy(screen, '');
    writeRuntimeResultRefresh(screen, false);
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
        return;
      }

      button.disabled = false;
      button.setAttribute('data-runtime-execute-state', 'error');
      var message = payload && (payload.message || payload.error) ? (payload.message || payload.error) : 'Server execution failed.';
      setRuntimeExecuteStatus(screen, 'error', message);
      writeRuntimeOutboxDetailCopy(screen, '');
      writeRuntimeResultRefresh(screen, false);
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
    screen.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (control) {
      control.addEventListener('input', function () {
        writeIntentDraft(screen);
      });
      control.addEventListener('change', function () {
        writeIntentDraft(screen);
      });
    });
  });
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
