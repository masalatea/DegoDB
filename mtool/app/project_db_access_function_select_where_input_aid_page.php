<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param array{
 *     data_class_candidate:string,
 *     target_table_candidate:string
 * } $names
 */
function app_project_db_access_function_select_where_input_aid_names(
    ?array $canonicalFunctionItem,
    array $entity,
): array {
    $dataClassCandidate = $entity['source_name'];
    $targetTableCandidate = $entity['source_name'];

    if ($canonicalFunctionItem !== null) {
        if (trim($canonicalFunctionItem['target_table_name']) !== '') {
            $targetTableCandidate = trim($canonicalFunctionItem['target_table_name']);
        }
        if (trim($canonicalFunctionItem['data_class_base_name']) !== '') {
            $dataClassCandidate = trim($canonicalFunctionItem['data_class_base_name']);
        } elseif ($targetTableCandidate !== '') {
            $dataClassCandidate = $targetTableCandidate;
        }
    }

    if ($targetTableCandidate === '') {
        $targetTableCandidate = $dataClassCandidate;
    }
    if ($dataClassCandidate === '') {
        $dataClassCandidate = $targetTableCandidate;
    }

    return [
        'data_class_candidate' => $dataClassCandidate,
        'target_table_candidate' => $targetTableCandidate,
    ];
}

/**
 * @param list<array{
 *     select_where_id:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $items
 * @return array<string,list<array{
 *     select_where_id:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }>>
 */
function app_project_db_access_function_select_where_group_by_column(array $items): array
{
    $grouped = [];
    foreach ($items as $item) {
        $key = $item['target_table_column_name'];
        if (!array_key_exists($key, $grouped)) {
            $grouped[$key] = [];
        }
        $grouped[$key][] = $item;
    }

    return $grouped;
}

/**
 * @param list<string> $left
 * @param list<string> $right
 * @return list<string>
 */
function app_project_db_access_function_select_where_merge_candidates(array $left, array $right): array
{
    $merged = [];
    $seen = [];

    foreach (array_merge($left, $right) as $fieldName) {
        $normalized = trim($fieldName);
        if ($normalized === '') {
            continue;
        }
        $key = strtolower($normalized);
        if (array_key_exists($key, $seen)) {
            continue;
        }
        $seen[$key] = true;
        $merged[] = $normalized;
    }

    return $merged;
}

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
function app_render_project_db_access_function_select_where_input_aid_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_function_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $entity = $bootstrap['entity'];
    $method = $bootstrap['method'];
    $functionProfile = $bootstrap['function_profile'];
    $catalog = $bootstrap['generated_catalog'];
    $methodCount = count($bootstrap['method_catalog']);
    $basePath = '/projects/' . rawurlencode($projectKey)
        . '/db-access/' . rawurlencode($entity['source_name'])
        . '/functions/' . rawurlencode($method['name']);
    $listPath = $basePath . '/select-where';

    $canonicalFunctionResult = app_fetch_db_access_function_metadata(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $canonicalFunctionError = $canonicalFunctionResult['ok'] ? '' : $canonicalFunctionResult['error'];
    $canonicalFunctionItem = $canonicalFunctionResult['ok'] ? $canonicalFunctionResult['item'] : null;
    $effectiveActionType = $canonicalFunctionItem !== null && $canonicalFunctionItem['action_type'] !== ''
        ? $canonicalFunctionItem['action_type']
        : $functionProfile['legacy_action_type'];
    $isSelectFunction = in_array($effectiveActionType, ['SELECTSINGLE', 'SELECTLIST'], true);

    $catalogResult = app_fetch_db_access_function_select_where_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $rowsByColumn = app_project_db_access_function_select_where_group_by_column($items);

    $names = app_project_db_access_function_select_where_input_aid_names($canonicalFunctionItem, $entity);
    $dataClassEntity = app_generated_catalog_find_entity($catalog, $names['data_class_candidate']);
    $targetTableEntity = app_generated_catalog_find_entity($catalog, $names['target_table_candidate']);

    $dataClassFields = $dataClassEntity !== null
        ? app_generated_file_property_names($dataClassEntity['data_path'])
        : [];
    $targetTableFields = $targetTableEntity !== null
        ? app_generated_file_property_names($targetTableEntity['data_path'])
        : [];
    $fieldCandidates = app_project_db_access_function_select_where_merge_candidates($dataClassFields, $targetTableFields);
    $targetTableFieldSet = array_fill_keys($targetTableFields, true);
    $suggestedWhereOrder = (string) count($items);

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Where Input Aid</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 88rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            border-radius: 6px;
            padding: 0.1rem 0.3rem;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="<?php echo app_h($basePath); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <a href="<?php echo app_h($listPath); ?>">select-where</a> / input-aid</p>

    <h1><?php echo app_h($project['name']); ?> Select Where Input Aid</h1>
    <p><code>select where</code> 条件候補を補助表示する画面です。canonical <code>dataclassfields</code> / <code>dbtablecolumns</code> と alias graph はまだ無いため、まずは generated <code>data-*.php</code> の public property を候補として表示し、既存 row の有無と add/edit 導線を確認できるようにしています。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function: <code><?php echo app_h($method['name']); ?></code></li>
                <li>action type: <code><?php echo app_h($effectiveActionType); ?></code></li>
                <li>signature: <code><?php echo app_h($method['signature']); ?></code></li>
                <li>function catalog size: <code><?php echo app_h((string) $methodCount); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Candidate Source</h2>
            <ul>
                <li>data class candidate: <code><?php echo app_h($names['data_class_candidate']); ?></code></li>
                <li>target table candidate: <code><?php echo app_h($names['target_table_candidate']); ?></code></li>
                <li>field candidate count: <code><?php echo app_h((string) count($fieldCandidates)); ?></code></li>
                <li>saved where rows: <code><?php echo app_h((string) count($items)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Generated Availability</h2>
            <ul>
                <li>data class file: <?php echo $dataClassEntity !== null && $dataClassEntity['has_data_file'] ? '<code>' . app_h($dataClassEntity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                <li>target table file: <?php echo $targetTableEntity !== null && $targetTableEntity['has_data_file'] ? '<code>' . app_h($targetTableEntity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                <li>designer state: <code><?php echo app_h($catalogError === '' ? 'active' : 'db unavailable'); ?></code></li>
            </ul>
            <?php if ($canonicalFunctionError !== ''): ?>
                <p class="muted"><?php echo app_h($canonicalFunctionError); ?></p>
            <?php elseif ($canonicalFunctionItem === null): ?>
                <p class="muted">canonical function metadata は未保存ですが、generated candidate を使った preview は確認できます。</p>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>候補一覧は generated property 名ベースであり、実 DB column 定義ではない</li>
                <li>base target table 候補向けの簡易 add/edit 導線のみで、join alias の組み立ては手入力前提</li>
                <li>`anotherfield` / joined table 条件の候補導出はまだ未実装</li>
            </ul>
            <?php if (!$isSelectFunction): ?>
                <p class="muted">この route は主に select 系 function 向けです。現在の action type は <code><?php echo app_h($effectiveActionType); ?></code> です。</p>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($catalogError !== ''): ?>
        <p class="muted"><?php echo app_h($catalogError); ?></p>
    <?php elseif ($fieldCandidates === []): ?>
        <p>候補 field はまだ見つかっていません。generated data class / target table の確認か、canonical metadata の保存を先に行ってください。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>field candidate</th>
                <th>current state</th>
                <th>target table hint</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fieldCandidates as $index => $fieldName): ?>
                <?php $existingRows = $rowsByColumn[$fieldName] ?? []; ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h($fieldName); ?></code></td>
                    <td>
                        <?php if ($existingRows === []): ?>
                            <span class="muted">Not Exist</span>
                        <?php else: ?>
                            <code><?php echo app_h((string) count($existingRows)); ?> row(s)</code><br>
                            <?php foreach ($existingRows as $existingRow): ?>
                                <span class="muted">
                                    id=<?php echo app_h($existingRow['select_where_id']); ?>
                                    / <?php echo app_h($existingRow['target_table_name'] !== '' ? $existingRow['target_table_name'] : '(blank table)'); ?>
                                    / <?php echo app_h(app_db_access_select_where_parameter_type_caption($existingRow['parameter_type'])); ?>
                                    / <?php echo app_h(app_db_access_select_where_join_type_caption($existingRow['join_type'])); ?>
                                    / order=<?php echo app_h($existingRow['where_order']); ?>
                                </span><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (array_key_exists($fieldName, $targetTableFieldSet)): ?>
                            <span class="muted">target table candidate に存在</span>
                        <?php else: ?>
                            <span class="muted">data class candidate 由来のみ</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php foreach ($existingRows as $existingRow): ?>
                            <a href="<?php echo app_h($listPath); ?>/<?php echo rawurlencode($existingRow['select_where_id']); ?>">edit #<?php echo app_h($existingRow['select_where_id']); ?></a><br>
                        <?php endforeach; ?>
                        <a href="<?php echo app_h($listPath); ?>/new?target_table_name=<?php echo rawurlencode($names['target_table_candidate']); ?>&target_table_column_name=<?php echo rawurlencode($fieldName); ?>&parameter_type=argument&relational_operator=%3D&where_order=<?php echo rawurlencode($suggestedWhereOrder); ?>">add</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <section class="summary-grid">
        <section class="summary-card">
            <h2>Legacy Route</h2>
            <ul>
                <li>legacy screen: <code>da_func_select_where_input_aid.php</code></li>
                <li>current mode: <code>generated candidate preview</code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>次の導線</h2>
            <ul>
                <li><a href="<?php echo app_h($listPath); ?>">select-where designer</a></li>
                <li><a href="<?php echo app_h($listPath); ?>/new">add new select-where</a></li>
                <li><a href="<?php echo app_h($basePath); ?>">function detail</a></li>
                <li><a href="<?php echo app_h($basePath); ?>/source">function source</a></li>
            </ul>
        </section>
    </section>
</main>
</body>
</html>
    <?php
}
