<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';
require_once __DIR__ . '/project_db_access_route_common.php';
require_once __DIR__ . '/runtime_storage_paths.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     generated?:array{
 *         dbclasses_root?:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_db_access_edit_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dbAccessKey = trim(app_route_param($request, 'db_access_key'));
    if ($dbAccessKey === '') {
        app_render_bad_request_page($app, $request, 'db access key が必要です。');
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dbAccessKey);
    if ($entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $legacyDaSchema = app_project_db_access_legacy_metadata_schema($app, 'da');
    $canonicalResult = app_fetch_db_access_class_metadata($app, $projectKey, $entity['source_name']);
    $canonicalError = $canonicalResult['ok'] ? '' : $canonicalResult['error'];
    $canonicalItem = $canonicalResult['ok'] ? $canonicalResult['item'] : null;

    $input = $canonicalItem !== null
        ? app_project_db_access_class_form_from_item($canonicalItem)
        : app_project_db_access_class_form_from_entity($entity);

    $errors = [];
    $updated = app_query_param('updated') === '1';

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $postedSourceName = trim(app_post_param('source_name'));
            if (strcasecmp($postedSourceName, $entity['source_name']) !== 0) {
                $errors[] = '更新対象の db access key が route と一致しません。';
            }

            $validation = app_validate_db_access_class_form([
                'source_name' => $entity['source_name'],
                'store_base_path' => app_post_param('store_base_path'),
                'is_autoload' => app_post_param('is_autoload', '0'),
                'notes' => app_post_param('notes'),
                'source_of_truth' => 'manual',
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $updateResult = app_upsert_db_access_class_metadata($app, [
                    'project_key' => $projectKey,
                    'source_name' => $input['source_name'],
                    'store_base_path' => $input['store_base_path'],
                    'is_autoload' => $input['is_autoload'],
                    'notes' => $input['notes'],
                    'source_of_truth' => $input['source_of_truth'],
                    'last_detected_dbaccess_file' => $entity['dbaccess_file'],
                    'last_detected_data_file' => $entity['data_file'],
                ]);

                if ($updateResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        '/projects/' . rawurlencode($projectKey)
                        . '/db-access/' . rawurlencode($entity['source_name'])
                        . '/edit?updated=1',
                    );
                    return;
                }

                $errors[] = $updateResult['error'];
            }
        }
    }

    $fieldPreviewRows = [
        [
            'field' => 'project_id',
            'preview' => 'route から解決',
            'note' => 'legacy `ProjectPID` は新実装では internal project id として保持する。',
        ],
        [
            'field' => 'source_name',
            'preview' => $entity['source_name'],
            'note' => '現在は generated basename と route key を同一 source_name として扱う。',
        ],
        [
            'field' => 'store_base_path',
            'preview' => $input['store_base_path'] !== '' ? $input['store_base_path'] : '(blank)',
            'note' => 'legacy `StoreBasePath`。空欄時は Source Output 側の既定値を使う前提。',
        ],
        [
            'field' => 'is_autoload',
            'preview' => $input['is_autoload'] === '1' ? '1' : '0',
            'note' => 'legacy `IsAutoload`。`option_all_source_include` を移植後に最終評価を調整する。',
        ],
        [
            'field' => 'notes',
            'preview' => $input['notes'] !== '' ? $input['notes'] : '(blank)',
            'note' => '新実装で補足メモを残すために追加した field。',
        ],
    ];

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Edit</title>
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
        code, pre {
            background: #edf2f7;
            border-radius: 6px;
        }
        code {
            padding: 0.1rem 0.3rem;
        }
        pre {
            padding: 1rem;
            overflow-x: auto;
            white-space: pre-wrap;
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
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        input[readonly] {
            background: #e2e8f0;
        }
        textarea {
            min-height: 8rem;
            resize: vertical;
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
        .error {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }
        .success {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / edit</p>

    <h1><?php echo app_h($project['name']); ?> DB Access Class Setting</h1>
    <p>DB Access class metadata を編集する画面です。runtime reference を参照しつつ、<code>db-config</code> 上の canonical class metadata を段階的に持ち始めます。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Current Entity</h2>
            <ul>
                <li>db access key: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>runtime dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                <li>runtime data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Status</h2>
            <?php if ($canonicalError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalError); ?></p>
            <?php elseif ($canonicalItem === null): ?>
                <p class="muted">未保存</p>
                <p class="muted">まだ `project_db_access_classes` に row がありません。</p>
            <?php else: ?>
                <ul>
                    <li>source of truth: <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code></li>
                    <li>updated: <code><?php echo app_h($canonicalItem['updated_at']); ?></code></li>
                    <li>StoreBasePath: <code><?php echo app_h($canonicalItem['store_base_path'] !== '' ? $canonicalItem['store_base_path'] : '(blank)'); ?></code></li>
                    <li>IsAutoload: <code><?php echo app_h($canonicalItem['is_autoload']); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>legacy `da.PID` はまだ持たず、`source_name` を stable key として使う</li>
                <li>`StoreBasePath` と `IsAutoload` だけ先に canonical 化する</li>
                <li>`option_all_source_include` や `ProjectSourceOutput` の影響はまだ未移植</li>
            </ul>
            <p class="muted">既存 volume に schema が無い場合は、`project_db_access_classes` table を適用してから保存できます。</p>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">db access class <code><?php echo app_h($entity['source_name']); ?></code> の canonical metadata を保存しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($canonicalError === ''): ?>
        <form method="post" action="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/edit">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="source_name" value="<?php echo app_h($entity['source_name']); ?>">

            <label for="source_name_readonly">db access key</label>
            <input id="source_name_readonly" value="<?php echo app_h($entity['source_name']); ?>" readonly>

            <label for="store_base_path">StoreBasePath</label>
            <input id="store_base_path" name="store_base_path" value="<?php echo app_h($input['store_base_path']); ?>" placeholder="例: <?php echo app_h(app_runtime_storage_runtime_source_repo_relative_path(app_runtime_storage_runtime_dbclasses_relative_path())); ?>">

            <label for="is_autoload">IsAutoload</label>
            <select id="is_autoload" name="is_autoload">
                <option value="0"<?php echo $input['is_autoload'] === '0' ? ' selected' : ''; ?>>0 (No)</option>
                <option value="1"<?php echo $input['is_autoload'] === '1' ? ' selected' : ''; ?>>1 (Yes)</option>
            </select>

            <label for="notes">notes</label>
            <textarea id="notes" name="notes" placeholder="legacy との差分メモや移行方針"><?php echo app_h($input['notes']); ?></textarea>

            <button type="submit">保存</button>
        </form>
    <?php endif; ?>

    <section>
        <h2>Canonical Field Draft</h2>
        <table>
            <thead>
            <tr>
                <th>field</th>
                <th>preview value</th>
                <th>note</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fieldPreviewRows as $row): ?>
                <tr>
                    <td><code><?php echo app_h($row['field']); ?></code></td>
                    <td><code><?php echo app_h($row['preview']); ?></code></td>
                    <td><?php echo app_h($row['note']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="summary-card">
        <h2>Legacy Persistence API</h2>
        <p>
            <?php echo $legacyDaSchema['dbaccess_methods'] === []
                ? '<span class="muted">none</span>'
                : '<code>' . app_h(implode('</code>, <code>', $legacyDaSchema['dbaccess_methods'])) . '</code>'; ?>
        </p>
        <p class="muted">`GetdaByName` があるため、entity basename から canonical `source_name` へ寄せる方針が取りやすい。</p>
    </section>

    <?php if ($legacyDaSchema['data_excerpt'] !== ''): ?>
        <section>
            <h2>`data-da.php` Preview</h2>
            <pre><?php echo app_h($legacyDaSchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($legacyDaSchema['dbaccess_excerpt'] !== ''): ?>
        <section>
            <h2>`dbaccess-da.php` Preview</h2>
            <pre><?php echo app_h($legacyDaSchema['dbaccess_excerpt']); ?></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
