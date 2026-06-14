<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/project_data_class_route_common.php';
require_once __DIR__ . '/project_data_class_sync_service.php';

function app_project_data_class_sync_status_label(string $status): string
{
    return match ($status) {
        'new' => 'new',
        'changed' => 'changed',
        default => 'same',
    };
}

function app_project_data_class_sync_status_class(string $status): string
{
    return match ($status) {
        'new' => 'pill-new',
        'changed' => 'pill-changed',
        default => 'pill-same',
    };
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
function app_render_project_data_classes_sync_page(array $app, array $request): void
{
    $bootstrap = app_project_data_class_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $syncErrors = [];
    $syncResult = null;

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $syncErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $syncResult = app_project_data_class_sync_apply($app, $projectKey);
            if (!$syncResult['ok']) {
                if ($syncResult['errors'] !== []) {
                    $syncErrors = array_merge($syncErrors, $syncResult['errors']);
                } elseif ($syncResult['error'] !== '') {
                    $syncErrors[] = $syncResult['error'];
                }
            }
        }
    }

    $preview = app_project_data_class_sync_preview($app, $projectKey);
    if (!$preview['ok'] && $preview['errors'] !== []) {
        $syncErrors = array_values(array_unique(array_merge($syncErrors, $preview['errors'])));
    }

    $canonicalSnapshot = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    $canonicalSnapshotError = $canonicalSnapshot['ok'] ? '' : $canonicalSnapshot['error'];
    $canonicalDataClassCount = 0;
    $canonicalFieldCount = 0;
    if ($canonicalSnapshot['ok']) {
        $canonicalDataClassCount = count($canonicalSnapshot['items']);
        foreach ($canonicalSnapshot['items'] as $dataClass) {
            $canonicalFieldCount += count($dataClass['fields']);
        }
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Classes Sync</title>
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
        .pill {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .pill-same {
            background: #dcfce7;
            color: #166534;
        }
        .pill-new {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .pill-changed {
            background: #fef3c7;
            color: #92400e;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes">data-classes</a> / sync</p>

    <h1><?php echo app_h($project['name']); ?> Data Class Sync</h1>
    <p><code>dbtable</code> / <code>dbtablecolumns</code> を <code>dataclass</code> / <code>dataclassfields</code> へ同期する入口です。first slice では plain table-to-class sync だけを扱い、non-table derived class は削除せずに残します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Source</h2>
            <ul>
                <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
                <li>generated mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>next downstream: <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/sync"><code>/db-access/sync</code></a></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Data Class</h2>
            <?php if ($canonicalSnapshotError !== ''): ?>
                <p class="muted"><?php echo app_h($canonicalSnapshotError); ?></p>
            <?php else: ?>
                <ul>
                    <li>saved classes: <code><?php echo app_h((string) $canonicalDataClassCount); ?></code></li>
                    <li>saved fields: <code><?php echo app_h((string) $canonicalFieldCount); ?></code></li>
                    <li>state: <code><?php echo app_h($canonicalDataClassCount > 0 ? 'active' : 'empty'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>Scope</h2>
            <ul>
                <li>sync 元は canonical table metadata です</li>
                <li>class name は first pass で table name と同名にします</li>
                <li>datatype と order は table metadata から更新します</li>
                <li>stale class / stale field は warning として残し、first slice では削除しません</li>
            </ul>
        </section>
    </div>

    <?php if ($preview['ok']): ?>
        <section class="summary-card">
            <h2>Current Diff</h2>
            <ul>
                <li>imported tables: <code><?php echo app_h((string) $preview['summary']['table_count']); ?></code></li>
                <li>canonical classes: <code><?php echo app_h((string) $preview['summary']['canonical_data_class_count']); ?></code></li>
                <li>class new: <code><?php echo app_h((string) $preview['summary']['class_insert_count']); ?></code></li>
                <li>class changed: <code><?php echo app_h((string) $preview['summary']['class_update_count']); ?></code></li>
                <li>field new: <code><?php echo app_h((string) $preview['summary']['field_insert_count']); ?></code></li>
                <li>field changed: <code><?php echo app_h((string) $preview['summary']['field_update_count']); ?></code></li>
                <li>stale classes kept: <code><?php echo app_h((string) $preview['summary']['stale_class_count']); ?></code></li>
                <li>stale fields kept: <code><?php echo app_h((string) $preview['summary']['stale_field_count']); ?></code></li>
            </ul>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h(app_csrf_token()); ?>">
                <div class="button-row">
                    <button type="submit">canonical table metadata から data class を sync</button>
                    <span class="muted">再実行すると不足分と差分だけが更新されます。</span>
                </div>
            </form>
            <p class="muted">CLI では <code>docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=<?php echo app_h($projectKey); ?></code> を使えます。</p>
        </section>
    <?php endif; ?>

    <?php if ($syncResult !== null && $syncResult['ok']): ?>
        <section class="result-card">
            <h2>Last Sync Result</h2>
            <ul>
                <li>class inserted: <code><?php echo app_h((string) $syncResult['summary']['class_insert_count']); ?></code></li>
                <li>class changed: <code><?php echo app_h((string) $syncResult['summary']['class_update_count']); ?></code></li>
                <li>field inserted: <code><?php echo app_h((string) $syncResult['summary']['field_insert_count']); ?></code></li>
                <li>field changed: <code><?php echo app_h((string) $syncResult['summary']['field_update_count']); ?></code></li>
                <li>stale classes kept: <code><?php echo app_h((string) $syncResult['summary']['stale_class_count']); ?></code></li>
                <li>stale fields kept: <code><?php echo app_h((string) $syncResult['summary']['stale_field_count']); ?></code></li>
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

    <?php if ($preview['ok']): ?>
        <table>
            <thead>
            <tr>
                <th>data class</th>
                <th>status</th>
                <th>fields</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($preview['classes'] as $dataClass): ?>
                <tr>
                    <td><code><?php echo app_h($dataClass['name']); ?></code></td>
                    <td><span class="pill <?php echo app_h(app_project_data_class_sync_status_class($dataClass['status'])); ?>"><?php echo app_h(app_project_data_class_sync_status_label($dataClass['status'])); ?></span></td>
                    <td>
                        table: <code><?php echo app_h((string) $dataClass['table_field_count']); ?></code><br>
                        canonical: <code><?php echo app_h((string) $dataClass['canonical_field_count']); ?></code><br>
                        new/change/stale: <code><?php echo app_h((string) $dataClass['field_insert_count']); ?></code> / <code><?php echo app_h((string) $dataClass['field_update_count']); ?></code> / <code><?php echo app_h((string) $dataClass['stale_field_count']); ?></code>
                    </td>
                    <td>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/<?php echo rawurlencode($dataClass['name']); ?>">detail</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/<?php echo rawurlencode($dataClass['name']); ?>/fields">fields</a>
                    </td>
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
