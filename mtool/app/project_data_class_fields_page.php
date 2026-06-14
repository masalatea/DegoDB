<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/project_data_class_route_common.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_data_class_fields_page(array $app, array $request): void
{
    $bootstrap = app_project_data_class_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dataClassKey = trim(app_route_param($request, 'data_class_key'));
    if ($dataClassKey === '') {
        app_render_bad_request_page($app, $request, 'data class key が必要です。');
        return;
    }

    $canonicalItem = app_fetch_data_class_metadata_item($app, $projectKey, $dataClassKey);
    if (!$canonicalItem['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItem['error']);
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dataClassKey);
    if ($entity === null && $canonicalItem['item'] !== null) {
        $entity = app_generated_catalog_find_entity($catalog, $canonicalItem['item']['name']);
    }

    if ($canonicalItem['item'] === null && $entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $deleted = app_query_param('deleted') === '1';

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Class Fields</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 78rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        .success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_data_classes_path($projectKey)); ?>">data-classes</a> / <a href="<?php echo app_h(app_project_data_class_detail_path($projectKey, $dataClassKey)); ?>"><code><?php echo app_h($dataClassKey); ?></code></a> / fields</p>

    <?php if ($canonicalItem['item'] !== null): ?>
        <?php $dataClass = $canonicalItem['item']; ?>
        <h1><?php echo app_h($project['name']); ?> Field Detail</h1>
        <p>canonical <code>dataclassfields</code> の一覧です。</p>

        <?php if ($deleted): ?>
            <div class="success">data class field metadata row を削除しました。</div>
        <?php endif; ?>

        <section class="summary-card">
            <h2>Data Class</h2>
            <ul>
                <li>name: <code><?php echo app_h($dataClass['name']); ?></code></li>
                <li>field count: <code><?php echo app_h((string) count($dataClass['fields'])); ?></code></li>
                <li><a href="<?php echo app_h(app_project_data_class_field_new_path($projectKey, $dataClass['name'])); ?>">new field</a></li>
                <li><a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $dataClass['name'])); ?>">data class edit</a></li>
            </ul>
        </section>

        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>field</th>
                <th>datatype</th>
                <th>ref class</th>
                <th>ref field</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataClass['fields'] as $field): ?>
                <tr>
                    <td><?php echo app_h((string) $field['field_list_order']); ?></td>
                    <td><code><?php echo app_h($field['name']); ?></code></td>
                    <td><code><?php echo app_h($field['datatype']); ?></code></td>
                    <td><?php echo $field['ref_data_class_name'] !== '' ? '<code>' . app_h($field['ref_data_class_name']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td><?php echo $field['ref_data_class_field_name'] !== '' ? '<code>' . app_h($field['ref_data_class_field_name']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td><a href="<?php echo app_h(app_project_data_class_field_edit_path($projectKey, $dataClass['name'], $field['name'])); ?>">edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?php $fields = app_generated_file_property_names($entity['data_path']); ?>
        <h1><?php echo app_h($project['name']); ?> Data Class Field Preview</h1>
        <p>canonical row がまだ無いため、<code>data-*.php</code> の public property を fallback preview として表示しています。</p>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?>">sync</a></li>
                <li><a href="<?php echo app_h(app_project_data_class_detail_path($projectKey, $entity['source_name'])); ?>">data class detail</a></li>
                <li><a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $entity['source_name'])); ?>">create canonical row</a></li>
            </ul>
        </section>

        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>field candidate</th>
                <th>current status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fields as $index => $fieldName): ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h($fieldName); ?></code></td>
                    <td><span class="muted">datatype 未解決 / canonical field metadata 未作成</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
