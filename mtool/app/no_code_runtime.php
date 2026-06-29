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
            'fields' => app_no_code_runtime_render_fields($fields),
            'actions' => app_no_code_runtime_render_actions(
                is_array($screen['actions'] ?? null) ? $screen['actions'] : [],
                is_array($contract['actions'] ?? null) ? $contract['actions'] : [],
            ),
            'data' => app_no_code_runtime_render_data($screenType, $fields, $rows, $currentItem),
            'sync_status_hint' => (bool) ($screen['sync_status_hint'] ?? false),
        ],
        'error' => '',
    ];
}

function app_no_code_runtime_version(): string
{
    return 'no-code-runtime-v0';
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
        '<main class="no-code-preview" data-runtime-version="' . app_no_code_runtime_html_escape((string) ($runtimePreview['runtime_version'] ?? '')) . '">',
        '<header class="no-code-preview-header">',
        '<h1>' . app_no_code_runtime_html_escape((string) ($runtimePreview['project_key'] ?? 'No-code runtime')) . '</h1>',
        '<p>' . app_no_code_runtime_html_escape((string) ($runtimePreview['definition_version'] ?? '')) . '</p>',
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
    $contractKey = (string) ($render['contract_key'] ?? '');
    $fields = is_array($render['fields'] ?? null) ? $render['fields'] : [];
    $actions = is_array($render['actions'] ?? null) ? $render['actions'] : [];
    $data = is_array($render['data'] ?? null) ? $render['data'] : [];

    $body = $screenType === 'list'
        ? app_no_code_runtime_render_list_html($fields, is_array($data['rows'] ?? null) ? $data['rows'] : [])
        : app_no_code_runtime_render_item_screen_html(
            $screenType,
            $fields,
            is_array($data['item'] ?? null) ? $data['item'] : [],
        );

    return implode("\n", [
        '<section class="no-code-screen no-code-screen-' . app_no_code_runtime_html_escape($screenType) . '" data-screen-key="' . app_no_code_runtime_html_escape($screenKey) . '">',
        '<header class="no-code-screen-header">',
        '<div>',
        '<h2>' . app_no_code_runtime_html_escape($screenKey) . '</h2>',
        '<p>' . app_no_code_runtime_html_escape($contractKey . ' / ' . $screenType) . '</p>',
        '</div>',
        app_no_code_runtime_render_actions_html($actions),
        '</header>',
        $body,
        '</section>',
    ]);
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $rows
 */
function app_no_code_runtime_render_list_html(array $fields, array $rows): string
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
        $bodyRows[] = '<tr><td colspan="' . max(1, count($fields)) . '"></td></tr>';
    }

    return implode("\n", [
        '<div class="no-code-table-wrap">',
        '<table>',
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
function app_no_code_runtime_render_item_screen_html(string $screenType, array $fields, array $item): string
{
    if ($screenType === 'form') {
        return app_no_code_runtime_render_form_html($fields, $item);
    }

    $pairs = [];
    foreach ($fields as $field) {
        $fieldKey = (string) ($field['field_key'] ?? '');
        $value = is_array($item[$fieldKey] ?? null) ? $item[$fieldKey] : [];
        $pairs[] = '<dt>' . app_no_code_runtime_html_escape((string) ($field['label'] ?? $fieldKey)) . '</dt>'
            . '<dd>' . app_no_code_runtime_html_escape((string) ($value['display_value'] ?? '')) . '</dd>';
    }

    return '<dl class="no-code-detail">' . implode('', $pairs) . '</dl>';
}

/**
 * @param list<array<string,mixed>> $fields
 * @param array<string,mixed> $item
 */
function app_no_code_runtime_render_form_html(array $fields, array $item): string
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
        $attrs = ' name="' . app_no_code_runtime_html_escape($fieldKey) . '"'
            . ' id="field-' . app_no_code_runtime_html_escape($fieldKey) . '"'
            . ($readonly ? ' readonly' : '')
            . ($required ? ' required' : '');

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

        $controls[] = '<label for="field-' . app_no_code_runtime_html_escape($fieldKey) . '"><span>' . $label . '</span>' . $control . '</label>';
    }

    return '<form class="no-code-form" method="post">' . implode("\n", $controls) . '</form>';
}

/**
 * @param list<array<string,mixed>> $actions
 */
function app_no_code_runtime_render_actions_html(array $actions): string
{
    $buttons = [];
    foreach ($actions as $action) {
        if (!is_array($action)) {
            continue;
        }

        $enabled = (bool) ($action['enabled'] ?? false);
        $buttons[] = '<button type="button" data-action-key="' . app_no_code_runtime_html_escape((string) ($action['action_key'] ?? '')) . '"'
            . ' data-operation-key="' . app_no_code_runtime_html_escape((string) ($action['operation_key'] ?? '')) . '"'
            . ' data-operation-type="' . app_no_code_runtime_html_escape((string) ($action['operation_type'] ?? '')) . '"'
            . ' data-action-enabled="' . ($enabled ? 'true' : 'false') . '"'
            . ($enabled ? '' : ' disabled')
            . '>' . app_no_code_runtime_html_escape((string) ($action['label'] ?? $action['action_key'] ?? '')) . '</button>';
    }

    return '<nav class="no-code-actions">' . implode('', $buttons) . '</nav>';
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
        '.no-code-preview-header { margin-bottom: 24px; }',
        '.no-code-preview-header h1 { margin: 0; font-size: 28px; line-height: 1.2; }',
        '.no-code-preview-header p, .no-code-screen-header p { margin: 6px 0 0; color: #5f6b7a; font-size: 13px; }',
        '.no-code-screen { background: #ffffff; border: 1px solid #d8dee8; border-radius: 8px; padding: 18px; margin-bottom: 18px; }',
        '.no-code-screen-header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px; }',
        '.no-code-screen h2 { margin: 0; font-size: 18px; line-height: 1.25; }',
        '.no-code-actions { display: flex; flex-wrap: wrap; gap: 8px; }',
        '.no-code-actions button { min-height: 34px; border: 1px solid #9fb3c8; background: #eef4fb; color: #102a43; border-radius: 6px; padding: 0 12px; font: inherit; }',
        '.no-code-actions button:disabled { border-color: #c8d1dc; background: #eef1f4; color: #7b8794; }',
        '.no-code-table-wrap { overflow-x: auto; }',
        'table { width: 100%; border-collapse: collapse; table-layout: fixed; }',
        'th, td { border-bottom: 1px solid #e4e8ef; padding: 10px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }',
        'th { font-size: 12px; text-transform: uppercase; color: #52606d; background: #f4f6f8; }',
        '.no-code-detail { display: grid; grid-template-columns: minmax(120px, 220px) 1fr; gap: 0; margin: 0; }',
        '.no-code-detail dt, .no-code-detail dd { border-bottom: 1px solid #e4e8ef; padding: 10px; margin: 0; overflow-wrap: anywhere; }',
        '.no-code-detail dt { color: #52606d; background: #f4f6f8; }',
        '.no-code-form { display: grid; gap: 12px; max-width: 720px; }',
        '.no-code-form label { display: grid; gap: 6px; font-size: 13px; color: #52606d; }',
        '.no-code-form input, .no-code-form textarea { box-sizing: border-box; width: 100%; min-height: 36px; border: 1px solid #bcccdc; border-radius: 6px; padding: 8px 10px; font: inherit; color: #1f2933; background: #ffffff; }',
        '.no-code-form textarea { min-height: 96px; resize: vertical; }',
        '@media (max-width: 680px) { .no-code-preview { padding: 14px; } .no-code-screen-header { display: grid; } .no-code-detail { grid-template-columns: 1fr; } .no-code-detail dt { border-bottom: 0; } }',
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

  function actionError(error, intent) {
    return {
      ok: false,
      executed: false,
      intent: intent || null,
      error: error
    };
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
      if (!present && field.required) {
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
      error: ''
    };
  }

  function collectScreenInput(button) {
    var screen = button.closest('.no-code-screen');
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

  window.__noCodeRuntimePreview = preview;
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
      window.noCodeRuntimeDispatchAction(button.getAttribute('data-action-key') || '', collectScreenInput(button));
    });
  });
}());
JS;
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,mixed> $input
 * @param callable(array<string,mixed>):array<string,mixed> $dispatcher
 * @return array{ok:bool,executed:bool,intent:array<string,mixed>,result:array<string,mixed>|null,error:string}
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
    ];
}

/**
 * @return array{ok:bool,executed:bool,intent:array<string,mixed>,result:array<string,mixed>|null,error:string}
 */
function app_no_code_runtime_dispatch_error(string $error, array $intent = []): array
{
    return [
        'ok' => false,
        'executed' => false,
        'intent' => $intent,
        'result' => null,
        'error' => $error,
    ];
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
        if (!$present && (bool) ($field['required'] ?? false)) {
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
