<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';
require_once __DIR__ . '/project_db_access_sync_service.php';

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
function app_render_project_db_access_sync_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $catalog = $bootstrap['generated_catalog'];
    $preflight = app_project_db_access_sync_preflight($app, $projectKey);
    $classFunctionSyncPreflight = $preflight;
    $syncErrors = [];
    $syncResult = null;

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $syncErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (!$preflight['ok']) {
            $syncErrors[] = $preflight['error'];
        } else {
            $syncResult = app_project_db_access_sync_from_generated_catalog($app, $projectKey);
            if (!$syncResult['ok']) {
                if ($syncResult['errors'] !== []) {
                    $syncErrors = array_merge($syncErrors, $syncResult['errors']);
                } elseif ($syncResult['error'] !== '') {
                    $syncErrors[] = $syncResult['error'];
                }
            }
        }
    }

    $classFunctionSyncResult = $syncResult;

    $canonicalCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    $canonicalCatalogError = $canonicalCatalogResult['ok'] ? '' : $canonicalCatalogResult['error'];
    $canonicalClassCount = 0;
    $canonicalFunctionCount = 0;
    $canonicalBySource = [];

    if ($canonicalCatalogResult['ok']) {
        $canonicalClassCount = count($canonicalCatalogResult['items']);
        foreach ($canonicalCatalogResult['items'] as $item) {
            $canonicalBySource[$item['source_name']] = $item;
            $canonicalFunctionCount += $item['function_count'];
        }
    }

    $dbaccessCandidateCount = 0;
    $methodCandidateCount = 0;
    foreach ($catalog['entities'] as $entity) {
        if (!$entity['has_dbaccess_file']) {
            continue;
        }

        $dbaccessCandidateCount++;
        $methodCandidateCount += count(app_generated_file_method_names($entity['dbaccess_path']));
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Sync</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 84rem;
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
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card, .note-card, .result-card {
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
        .result-card {
            background: #f0fdf4;
            border-color: #86efac;
            margin-top: 1rem;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
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
        .button-row {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        button {
            border: none;
            border-radius: 999px;
            padding: 0.8rem 1.25rem;
            background: #0f766e;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / sync</p>

    <h1><?php echo app_h($project['name']); ?> DB Access Sync</h1>
    <p>DB Access 設計を canonical metadata へ寄せるための sync 入口です。現段階では runtime reference 内の <code>dbaccess-*.php</code> を基準に、canonical <code>project_db_access_classes</code> / <code>project_db_access_functions</code> を埋めます。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Runtime Reference</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>candidate entities: <code><?php echo app_h((string) $catalog['total_entities']); ?></code></li>
                <li>dbaccess candidates: <code><?php echo app_h((string) $dbaccessCandidateCount); ?></code></li>
                <li>method candidates: <code><?php echo app_h((string) $methodCandidateCount); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Metadata</h2>
            <?php if ($canonicalCatalogError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalCatalogError); ?></p>
            <?php else: ?>
                <ul>
                    <li>saved class rows: <code><?php echo app_h((string) $canonicalClassCount); ?></code></li>
                    <li>saved function rows: <code><?php echo app_h((string) $canonicalFunctionCount); ?></code></li>
                    <li>state: <code><?php echo app_h($canonicalClassCount > 0 ? 'active' : 'empty'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>Sync Scope</h2>
            <ul>
                <li>runtime reference sync は class/function row を更新し、<code>manual</code> / <code>seed-legacy</code> row を保持します</li>
                <li>query designer sub-resource はこの画面から legacy import しません。必要な初期値は canonical seed または一時移行手順で投入します</li>
                <li><code>original-codes</code> は参照用であり、新実装の runtime dependency にはしません</li>
                <li>現状の runtime reference は <code>MTOOL</code> 固定なので、対象 project も <code>MTOOL</code> に限定します</li>
            </ul>
        </section>
    </div>

    <?php if (!$classFunctionSyncPreflight['ok']): ?>
        <section class="note-card error-card">
            <h2>Class / Function Sync はまだ実行できません</h2>
            <p><?php echo app_h($classFunctionSyncPreflight['error']); ?></p>
        </section>
    <?php else: ?>
        <section class="summary-card">
            <h2>Class / Function Sync</h2>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h(app_csrf_token()); ?>">
                <input type="hidden" name="sync_action" value="class-function-sync">
                <div class="button-row">
                    <button type="submit">runtime reference から canonical metadata を sync</button>
                    <span class="muted">idempotent を前提にしているため、必要に応じて再実行できます。</span>
                </div>
            </form>
            <p class="muted">CLI では <code>docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=<?php echo app_h($projectKey); ?></code> を使えます。</p>
        </section>
    <?php endif; ?>

    <?php if ($classFunctionSyncResult !== null): ?>
        <section class="result-card">
            <h2>Last Class / Function Sync Result</h2>
            <ul>
                <li>class inserted: <code><?php echo app_h((string) $classFunctionSyncResult['summary']['class_inserted_count']); ?></code></li>
                <li>class updated: <code><?php echo app_h((string) $classFunctionSyncResult['summary']['class_updated_count']); ?></code></li>
                <li>function inserted: <code><?php echo app_h((string) $classFunctionSyncResult['summary']['function_inserted_count']); ?></code></li>
                <li>function updated: <code><?php echo app_h((string) $classFunctionSyncResult['summary']['function_updated_count']); ?></code></li>
                <li>stale classes kept as-is: <code><?php echo app_h((string) $classFunctionSyncResult['summary']['stale_class_count']); ?></code></li>
                <li>stale functions kept as-is: <code><?php echo app_h((string) $classFunctionSyncResult['summary']['stale_function_count']); ?></code></li>
                <li>stale sync-bootstrap functions pruned: <code><?php echo app_h((string) ($classFunctionSyncResult['summary']['stale_function_pruned_count'] ?? 0)); ?></code></li>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($syncErrors !== []): ?>
        <section class="note-card error-card">
            <h2>Sync Errors</h2>
            <ul>
                <?php foreach ($syncErrors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>candidate</th>
            <th>dbaccess file</th>
            <th>function candidate count</th>
            <th>canonical state</th>
            <th>preview</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($catalog['entities'] as $entity): ?>
            <?php $canonicalItem = $canonicalBySource[$entity['source_name']] ?? null; ?>
            <tr>
                <td><code><?php echo app_h($entity['source_name']); ?></code></td>
                <td><?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">missing</span>'; ?></td>
                <td><code><?php echo app_h((string) count(app_generated_file_method_names($entity['dbaccess_path']))); ?></code></td>
                <td>
                    <?php if (!$entity['has_dbaccess_file']): ?>
                        <span class="muted">sync target outside</span>
                    <?php elseif ($canonicalCatalogError !== ''): ?>
                        <span class="muted">db unavailable</span>
                    <?php elseif ($canonicalItem === null): ?>
                        <span class="muted">not synced yet</span>
                    <?php else: ?>
                        <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code><br>
                        <span class="muted">functions: <?php echo app_h((string) $canonicalItem['function_count']); ?></span><br>
                        <span class="muted">updated: <?php echo app_h($canonicalItem['updated_at']); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>">detail</a><br>
                    <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
    <?php
}
