<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param array{
 *     entities:list<array{
 *         source_name:string,
 *         data_file:string,
 *         dbaccess_file:string,
 *         data_path:string,
 *         dbaccess_path:string,
 *         has_data_file:bool,
 *         has_dbaccess_file:bool
 *     }>
 * } $catalog
 * @param array{
 *     source_name:string,
 *     data_file:string,
 *     dbaccess_file:string,
 *     data_path:string,
 *     dbaccess_path:string,
 *     has_data_file:bool,
 *     has_dbaccess_file:bool
 * } $currentEntity
 * @param array{
 *     name:string,
 *     line:int,
 *     signature:string
 * } $method
 * @return list<array{
 *     source_name:string,
 *     dbaccess_file:string,
 *     data_file:string,
 *     method_line:string,
 *     method_signature:string,
 *     available:bool,
 *     has_canonical_conflict:bool,
 *     canonical_error:string
 * }>
 */
function app_project_db_access_function_move_candidates(
    array $app,
    string $projectKey,
    array $catalog,
    array $currentEntity,
    array $method,
): array {
    $candidates = [];

    foreach ($catalog['entities'] as $entity) {
        if ($entity['source_name'] === $currentEntity['source_name']) {
            continue;
        }
        if (!$entity['has_dbaccess_file']) {
            continue;
        }

        $candidateMethods = app_generated_file_method_catalog($entity['dbaccess_path']);
        $candidateMethod = app_generated_file_find_method($candidateMethods, $method['name']);
        if ($candidateMethod === null) {
            continue;
        }

        $canonicalResult = app_fetch_db_access_function_metadata($app, $projectKey, $entity['source_name'], $method['name']);
        $canonicalError = $canonicalResult['ok'] ? '' : $canonicalResult['error'];
        $hasCanonicalConflict = $canonicalResult['ok'] && $canonicalResult['item'] !== null;

        $candidates[] = [
            'source_name' => $entity['source_name'],
            'dbaccess_file' => $entity['dbaccess_file'],
            'data_file' => $entity['data_file'],
            'method_line' => (string) $candidateMethod['line'],
            'method_signature' => $candidateMethod['signature'],
            'available' => $canonicalError === '' && !$hasCanonicalConflict,
            'has_canonical_conflict' => $hasCanonicalConflict,
            'canonical_error' => $canonicalError,
        ];
    }

    return $candidates;
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
function app_render_project_db_access_function_move_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_function_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $entity = $bootstrap['entity'];
    $method = $bootstrap['method'];
    $functionProfile = $bootstrap['function_profile'];
    $catalog = $bootstrap['generated_catalog'];
    $basePath = '/projects/' . rawurlencode($projectKey)
        . '/db-access/' . rawurlencode($entity['source_name'])
        . '/functions/' . rawurlencode($method['name']);

    $canonicalResult = app_fetch_db_access_function_metadata($app, $projectKey, $entity['source_name'], $method['name']);
    $canonicalError = $canonicalResult['ok'] ? '' : $canonicalResult['error'];
    $canonicalItem = $canonicalResult['ok'] ? $canonicalResult['item'] : null;
    $effectiveActionType = $canonicalItem !== null && $canonicalItem['action_type'] !== ''
        ? $canonicalItem['action_type']
        : $functionProfile['legacy_action_type'];

    $candidates = app_project_db_access_function_move_candidates($app, $projectKey, $catalog, $entity, $method);
    $availableDestinations = [];
    foreach ($candidates as $candidate) {
        if ($candidate['available']) {
            $availableDestinations[$candidate['source_name']] = $candidate;
        }
    }

    $errors = [];
    $moved = app_query_param('moved') === '1';
    $selectedDestination = app_post_param('destination_source_name');

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalError !== '') {
            $errors[] = $canonicalError;
        } elseif ($canonicalItem === null) {
            $errors[] = '先に function detail で canonical metadata を保存してください。';
        } elseif ($selectedDestination === '') {
            $errors[] = '移動先 DB Access を選択してください。';
        } elseif (!array_key_exists($selectedDestination, $availableDestinations)) {
            $errors[] = '選択した移動先は現在利用できません。再読み込みしてやり直してください。';
        } else {
            $destination = $availableDestinations[$selectedDestination];
            $moveResult = app_move_db_access_function($app, [
                'project_key' => $projectKey,
                'source_name' => $entity['source_name'],
                'function_name' => $method['name'],
                'destination_source_name' => $destination['source_name'],
                'destination_last_detected_dbaccess_file' => $destination['dbaccess_file'],
                'destination_last_detected_data_file' => $destination['data_file'],
            ]);

            if ($moveResult['ok']) {
                app_send_redirect_response(
                    $request,
                    '/projects/' . rawurlencode($projectKey)
                    . '/db-access/' . rawurlencode($destination['source_name'])
                    . '/functions/' . rawurlencode($method['name'])
                    . '/move?moved=1',
                );
                return;
            }

            $errors[] = $moveResult['error'];
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Move</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 82rem;
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
        .success {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
        }
        .error-list {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 8px;
        }
        form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
        }
        select {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
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
        button {
            margin-top: 1.25rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / move</p>

    <h1><?php echo app_h($project['name']); ?> Function Move</h1>
    <p>function を別の DB Access class へ付け替える画面です。現在の正規化 schema では child designer row は function id にぶら下がるため、move 自体は function の親 DB Access Class を付け替えるだけで済みます。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Current Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function: <code><?php echo app_h($method['name']); ?></code></li>
                <li>action type: <code><?php echo app_h($effectiveActionType); ?></code></li>
                <li>signature: <code><?php echo app_h($method['signature']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical State</h2>
            <?php if ($canonicalError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalError); ?></p>
            <?php elseif ($canonicalItem === null): ?>
                <p class="muted">未保存</p>
                <p class="muted">move の前に function detail を保存してください。</p>
            <?php else: ?>
                <ul>
                    <li>source of truth: <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code></li>
                    <li>function list order: <code><?php echo app_h($canonicalItem['function_list_order']); ?></code></li>
                    <li>updated: <code><?php echo app_h($canonicalItem['updated_at']); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>移動先は、generated dbaccess file に同名 method が存在する DB Access に限定する</li>
                <li>移動先に同名 canonical row が既にある場合は move しない</li>
                <li>DataClassBaseName や target table は move 後に必要なら手で見直す</li>
            </ul>
        </section>
    </div>

    <?php if ($moved): ?>
        <div class="success">function を <code><?php echo app_h($entity['source_name']); ?></code> へ移動しました。関連 designer row は function id に追従するため、そのまま残ります。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error-list">
            <strong>移動できませんでした。</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($canonicalItem !== null && $availableDestinations !== []): ?>
        <form method="post" action="<?php echo app_h($basePath); ?>/move">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">

            <label for="destination_source_name">Move Target DB Access</label>
            <select id="destination_source_name" name="destination_source_name">
                <option value="">選択してください</option>
                <?php foreach ($availableDestinations as $candidate): ?>
                    <option value="<?php echo app_h($candidate['source_name']); ?>"<?php echo $selectedDestination === $candidate['source_name'] ? ' selected' : ''; ?>>
                        <?php echo app_h($candidate['source_name']); ?> (line <?php echo app_h($candidate['method_line']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Move Function</button>
        </form>
    <?php elseif ($canonicalItem !== null): ?>
        <p>利用可能な move target はありません。</p>
    <?php endif; ?>

    <?php if ($candidates !== []): ?>
        <table>
            <thead>
            <tr>
                <th>candidate</th>
                <th>generated method</th>
                <th>state</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($candidates as $candidate): ?>
                <tr>
                    <td><code><?php echo app_h($candidate['source_name']); ?></code></td>
                    <td>
                        <code>line <?php echo app_h($candidate['method_line']); ?></code><br>
                        <span class="muted"><?php echo app_h($candidate['method_signature']); ?></span>
                    </td>
                    <td>
                        <?php if ($candidate['canonical_error'] !== ''): ?>
                            <span class="muted"><?php echo app_h($candidate['canonical_error']); ?></span>
                        <?php elseif ($candidate['has_canonical_conflict']): ?>
                            <span class="muted">同名 canonical row が既に存在</span>
                        <?php else: ?>
                            <span class="muted">move 可能</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>">Back to Function Detail</a></p>
    <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">Back to Function List</a></p>
</main>
</body>
</html>
    <?php
}
