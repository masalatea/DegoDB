<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/generated_runtime.php';
require_once __DIR__ . '/project_permission.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_source_outputs_path(string $projectKey): string
{
    return '/projects/' . rawurlencode($projectKey) . '/source-outputs';
}

function app_project_source_output_detail_path(string $projectKey, string $sourceOutputKey): string
{
    return app_project_source_outputs_path($projectKey) . '/' . rawurlencode($sourceOutputKey);
}

function app_project_source_output_new_path(string $projectKey): string
{
    return app_project_source_outputs_path($projectKey) . '/new';
}

function app_project_source_output_edit_path(string $projectKey, string $sourceOutputKey): string
{
    return app_project_source_output_detail_path($projectKey, $sourceOutputKey) . '/edit';
}

function app_project_source_output_change_order_path(string $projectKey): string
{
    return app_project_source_outputs_path($projectKey) . '/change-order';
}

function app_project_source_output_download_path(string $projectKey, string $artifactKey): string
{
    return app_project_source_outputs_path($projectKey) . '/artifacts/' . rawurlencode($artifactKey) . '/download';
}

/**
 * @return list<string>
 */
function app_project_source_output_bridge_errors_from_request(): array
{
    $items = [];
    foreach ([($_GET['bridge_errors'] ?? null), ($_POST['bridge_errors'] ?? null)] as $rawValue) {
        if (is_array($rawValue)) {
            foreach ($rawValue as $rawItem) {
                if (!is_string($rawItem) && !is_numeric($rawItem)) {
                    continue;
                }

                $normalized = trim((string) $rawItem);
                if ($normalized === '') {
                    continue;
                }

                $items[$normalized] = $normalized;
            }
            continue;
        }

        if (!is_string($rawValue) && !is_numeric($rawValue)) {
            continue;
        }

        $normalized = trim((string) $rawValue);
        if ($normalized === '') {
            continue;
        }

        $items[$normalized] = $normalized;
    }

    return array_values($items);
}

/**
 * @return array<string,string>
 */
function app_project_source_output_legacy_metadata_request_field_map(): array
{
    return [
        'ProjectSourceOutput.PID' => 'legacy_project_source_output_pid',
        'CustomFileExtention' => 'legacy_only_CustomFileExtention',
        'DropboxBaseFolderPID' => 'legacy_only_DropboxBaseFolderPID',
        'UnitTestTemplateDir' => 'legacy_only_UnitTestTemplateDir',
        'UnitTestOutputDir' => 'legacy_only_UnitTestOutputDir',
        'TargetServerProjectSourceOutputPID' => 'legacy_only_TargetServerProjectSourceOutputPID',
        'CSNameSpace' => 'legacy_only_CSNameSpace',
        'JavaPackageName' => 'legacy_only_JavaPackageName',
        'AutoLoadFilePathForPHP' => 'legacy_only_AutoLoadFilePathForPHP',
        'JavaFunctionType' => 'legacy_only_JavaFunctionType',
        'DotNetLanguageResourceType' => 'legacy_only_DotNetLanguageResourceType',
    ];
}

/**
 * @return array<string,string>
 */
function app_project_source_output_legacy_metadata_labels(): array
{
    return [
        'ProjectSourceOutput.PID' => 'legacy ProjectSourceOutput PID',
        'CustomFileExtention' => 'legacy CustomFileExtention',
        'DropboxBaseFolderPID' => 'legacy DropboxBaseFolderPID',
        'UnitTestTemplateDir' => 'legacy UnitTestTemplateDir',
        'UnitTestOutputDir' => 'legacy UnitTestOutputDir',
        'TargetServerProjectSourceOutputPID' => 'legacy TargetServerProjectSourceOutputPID',
        'CSNameSpace' => 'legacy CSNameSpace',
        'JavaPackageName' => 'legacy JavaPackageName',
        'AutoLoadFilePathForPHP' => 'legacy AutoLoadFilePathForPHP',
        'JavaFunctionType' => 'legacy JavaFunctionType',
        'DotNetLanguageResourceType' => 'legacy DotNetLanguageResourceType',
    ];
}

/**
 * @param array<string,mixed> $metadata
 * @return array<string,string>
 */
function app_project_source_output_normalize_legacy_metadata(array $metadata): array
{
    $normalized = [];
    foreach (app_project_source_output_legacy_metadata_request_field_map() as $field => $_requestFieldName) {
        if (!array_key_exists($field, $metadata)) {
            continue;
        }

        $rawValue = $metadata[$field];
        if (!is_string($rawValue) && !is_numeric($rawValue)) {
            continue;
        }

        $value = trim((string) $rawValue);
        if ($value === '') {
            continue;
        }

        if ($field === 'ProjectSourceOutput.PID') {
            if (!ctype_digit($value) || (int) $value <= 0) {
                continue;
            }

            $value = (string) ((int) $value);
        }

        $normalized[$field] = $value;
    }

    return $normalized;
}

/**
 * @return array<string,string>
 */
function app_project_source_output_legacy_metadata_from_request(): array
{
    $metadata = [];

    foreach (app_project_source_output_legacy_metadata_request_field_map() as $field => $requestFieldName) {
        $rawValue = $_POST[$requestFieldName] ?? ($_GET[$requestFieldName] ?? null);
        if (!is_string($rawValue) && !is_numeric($rawValue)) {
            continue;
        }

        $metadata[$field] = trim((string) $rawValue);
    }

    return app_project_source_output_normalize_legacy_metadata($metadata);
}

function app_project_source_output_legacy_metadata_has_values(array $metadata): bool
{
    return app_project_source_output_normalize_legacy_metadata($metadata) !== [];
}

function app_project_source_output_legacy_metadata_strip_block(string $notes): string
{
    $stripped = preg_replace(
        '/\n?\[\[legacy-source-output\]\]\n?(.*?)\n?\[\[\/legacy-source-output\]\]\n?/su',
        "\n",
        $notes,
    );
    if (!is_string($stripped)) {
        $stripped = $notes;
    }

    $stripped = trim($stripped);
    $stripped = preg_replace("/\n{3,}/", "\n\n", $stripped);

    return is_string($stripped) ? trim($stripped) : trim($notes);
}

/**
 * @return array<string,string>
 */
function app_project_source_output_legacy_metadata_from_notes(string $notes): array
{
    $metadata = [];

    if (
        preg_match(
            '/\[\[legacy-source-output\]\]\n?(.*?)\n?\[\[\/legacy-source-output\]\]/su',
            $notes,
            $matches,
        ) === 1
    ) {
        $block = trim((string) ($matches[1] ?? ''));
        $lines = preg_split("/\r\n|\n|\r/", $block);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (!is_string($line) || !str_contains($line, '=')) {
                    continue;
                }

                [$field, $value] = explode('=', $line, 2);
                $field = trim($field);
                $value = trim($value);
                if (!array_key_exists($field, app_project_source_output_legacy_metadata_request_field_map())) {
                    continue;
                }

                $metadata[$field] = $value;
            }
        }
    }

    if (
        !isset($metadata['ProjectSourceOutput.PID'])
        && preg_match('/\bProjectSourceOutput\.PID\s*=\s*(\d+)/u', $notes, $matches) === 1
    ) {
        $metadata['ProjectSourceOutput.PID'] = (string) ((int) ($matches[1] ?? 0));
    }

    return app_project_source_output_normalize_legacy_metadata($metadata);
}

/**
 * @return array{
 *     user_notes:string,
 *     legacy_metadata:array<string,string>
 * }
 */
function app_project_source_output_split_notes(string $notes): array
{
    return [
        'user_notes' => app_project_source_output_legacy_metadata_strip_block($notes),
        'legacy_metadata' => app_project_source_output_legacy_metadata_from_notes($notes),
    ];
}

function app_project_source_output_notes_with_legacy_metadata(string $notes, array $legacyMetadata): string
{
    $userNotes = app_project_source_output_legacy_metadata_strip_block($notes);
    $normalizedMetadata = app_project_source_output_normalize_legacy_metadata($legacyMetadata);
    if ($normalizedMetadata === []) {
        return $userNotes;
    }

    $lines = [];
    foreach (app_project_source_output_legacy_metadata_request_field_map() as $field => $_requestFieldName) {
        if (!isset($normalizedMetadata[$field])) {
            continue;
        }

        $lines[] = $field . '=' . $normalizedMetadata[$field];
    }

    $block = "[[legacy-source-output]]\n"
        . implode("\n", $lines)
        . "\n[[/legacy-source-output]]";

    return $userNotes === '' ? $block : $userNotes . "\n\n" . $block;
}

/**
 * @return list<array{
 *     field:string,
 *     label:string,
 *     value:string,
 *     note:string
 * }>
 */
function app_project_source_output_legacy_metadata_rows(array $legacyMetadata): array
{
    $normalizedMetadata = app_project_source_output_normalize_legacy_metadata($legacyMetadata);
    $labels = app_project_source_output_legacy_metadata_labels();
    $rows = [];

    foreach (app_project_source_output_legacy_metadata_request_field_map() as $field => $_requestFieldName) {
        if (!isset($normalizedMetadata[$field])) {
            continue;
        }

        $rows[] = [
            'field' => $field,
            'label' => $labels[$field] ?? $field,
            'value' => $normalizedMetadata[$field],
            'note' => $field === 'ProjectSourceOutput.PID'
                ? 'legacy row mapping / reorder reset 用に保持します。'
                : 'current schema 未移植のため structured notes block に退避しています。',
        ];
    }

    return $rows;
}

function app_project_source_output_render_legacy_metadata_hidden_inputs(array $legacyMetadata): void
{
    $normalizedMetadata = app_project_source_output_normalize_legacy_metadata($legacyMetadata);
    foreach (app_project_source_output_legacy_metadata_request_field_map() as $field => $requestFieldName) {
        if (!isset($normalizedMetadata[$field])) {
            continue;
        }
        ?>
        <input type="hidden" name="<?php echo app_h($requestFieldName); ?>" value="<?php echo app_h($normalizedMetadata[$field]); ?>">
        <?php
    }
}

function app_project_source_outputs_format_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float) $bytes;
    $unitIndex = 0;

    while ($value >= 1024 && $unitIndex < count($units) - 1) {
        $value /= 1024;
        $unitIndex++;
    }

    if ($unitIndex === 0) {
        return (string) $bytes . ' ' . $units[$unitIndex];
    }

    return number_format($value, 1) . ' ' . $units[$unitIndex];
}

/**
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     },
 *     project:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     },
 *     project_key:string,
 *     generated_runtime:array
 * }|null
 */
function app_project_source_output_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return null;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return null;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'source output の参照には admin または config role が必要です。');
        return null;
    }

    $normalizedAllowedMethods = array_values(
        array_filter(
            array_map(
                static fn (string $method): string => strtoupper(trim($method)),
                $allowedMethods,
            ),
            static fn (string $method): bool => $method !== '',
        ),
    );
    if ($normalizedAllowedMethods === []) {
        $normalizedAllowedMethods = ['GET'];
    }

    if (!in_array(strtoupper($request['method']), $normalizedAllowedMethods, true)) {
        app_render_method_not_allowed_page($app, $request, $normalizedAllowedMethods);
        return null;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return null;
    }

    $project = app_fetch_project_by_key($app, $projectKey);
    if (!$project['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Source Output</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>source output の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($project['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($project['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $permission = app_project_permission_can_with_audit(
        $app,
        $projectKey,
        $principal,
        'source_output.publish',
        'source_output',
    );
    if (!$permission['ok']) {
        app_render_internal_error_page($app, $request);
        return null;
    }
    if (!$permission['allowed']) {
        app_render_forbidden_page($app, $request, 'source output の参照には project publisher 以上の権限が必要です。');
        return null;
    }

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
        'project' => $project['item'],
        'project_key' => $projectKey,
        'generated_runtime' => app_generated_runtime_summary($app),
    ];
}

/**
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array,
 *     project:array,
 *     project_key:string,
 *     generated_runtime:array,
 *     source_output_key:string,
 *     source_output:array{
 *         source_output_key:string,
 *         name:string,
 *         program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         source_template_dir:string,
 *         source_output_dir:string,
 *         source_temp_output_dir:string,
 *         proxy_base_url:string,
 *         autoload_filename_suffix:string,
 *         source_text_char_code:string,
 *         runtime_source_relative_path:string,
 *         artifact_strategy:string,
 *         target_binding_type:string,
 *         output_archive_format:string,
 *         source_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }
 * }|null
 */
function app_project_source_output_item_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    $bootstrap = app_project_source_output_route_bootstrap($app, $request, $allowedMethods);
    if ($bootstrap === null) {
        return null;
    }

    $sourceOutputKey = app_normalize_source_output_key(app_route_param($request, 'source_output_key'));
    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        app_render_bad_request_page($app, $request, 'source output key の形式が不正です。');
        return null;
    }

    $itemResult = app_fetch_project_source_output_item($app, $bootstrap['project_key'], $sourceOutputKey);
    if (!$itemResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Source Output</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>source output detail の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($bootstrap['project_key']); ?></code></li>
        <li>source output key: <code><?php echo app_h($sourceOutputKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($itemResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($itemResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $bootstrap['source_output_key'] = $sourceOutputKey;
    $bootstrap['source_output'] = $itemResult['item'];

    return $bootstrap;
}
