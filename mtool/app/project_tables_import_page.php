<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/legacy_table_schema_reference.php';
require_once __DIR__ . '/project_scope_policy.php';
require_once __DIR__ . '/project_table_import_service.php';
require_once __DIR__ . '/project_table_route_common.php';
require_once __DIR__ . '/table_metadata_repository.php';

function app_project_table_import_status_label(string $status): string
{
    return match ($status) {
        'new' => 'new',
        'changed' => 'changed',
        'stale' => 'stale',
        default => 'same',
    };
}

function app_project_table_import_status_class(string $status): string
{
    return match ($status) {
        'new' => 'pill-new',
        'changed' => 'pill-changed',
        'stale' => 'pill-stale',
        default => 'pill-same',
    };
}

function app_project_tables_import_path(
    string $projectKey,
    string $sourceKey = '',
    string $tableName = '',
): string {
    $query = [];
    if ($sourceKey !== '') {
        $query['source'] = $sourceKey;
    }
    if ($tableName !== '') {
        $query['table'] = $tableName;
    }

    $path = '/projects/' . rawurlencode($projectKey) . '/tables/import';
    if ($query === []) {
        return $path;
    }

    return $path . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_tables_import_page(array $app, array $request): void
{
    $bootstrap = app_project_table_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $item = $bootstrap['project'];
    $projectKey = $bootstrap['project_key'];
    $scopePolicy = app_project_scope_policy($projectKey);
    $generatedRuntime = $bootstrap['generated_runtime'];
    $sourceOptions = app_project_table_import_source_options($projectKey, $app);
    $selectedSourceKey = app_project_table_import_source_normalize($projectKey, app_query_param('source'), $app);
    $focusedTableName = trim((string) app_query_param('table'));
    $importErrors = [];
    $applyResult = null;

    if (app_request_method_is($request, 'POST')) {
        $selectedSourceKey = app_project_table_import_source_normalize($projectKey, app_post_param('source_key'), $app);
        $focusedTableName = trim((string) app_post_param('table_name'));
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $importErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $applyResult = app_project_table_import_apply($app, $projectKey, $selectedSourceKey, $focusedTableName);
            if (!$applyResult['ok']) {
                if ($applyResult['errors'] !== []) {
                    $importErrors = array_merge($importErrors, $applyResult['errors']);
                } elseif ($applyResult['error'] !== '') {
                    $importErrors[] = $applyResult['error'];
                }
            }
        }
    }

    $preview = app_project_table_import_preview($app, $projectKey, $selectedSourceKey, $focusedTableName);
    if (!$preview['ok'] && $preview['errors'] !== []) {
        $importErrors = array_values(array_unique(array_merge($importErrors, $preview['errors'])));
    }

    $focusedTableDisplayName = $focusedTableName;
    if ($focusedTableDisplayName !== '' && $preview['ok'] && count($preview['tables']) === 1) {
        $focusedTableDisplayName = (string) ($preview['tables'][0]['name'] ?? $focusedTableDisplayName);
    }
    $focusedTableResolved = $focusedTableDisplayName !== '' && $preview['ok'] && count($preview['tables']) === 1;

    $canonicalSnapshot = app_fetch_table_metadata_snapshot($app, $projectKey);
    $canonicalSnapshotError = $canonicalSnapshot['ok'] ? '' : $canonicalSnapshot['error'];
    $canonicalTableCount = 0;
    $canonicalColumnCount = 0;
    if ($canonicalSnapshot['ok']) {
        $canonicalTableCount = count($canonicalSnapshot['items']);
        foreach ($canonicalSnapshot['items'] as $table) {
            $canonicalColumnCount += count($table['columns']);
        }
    }

    $legacyReference = app_load_legacy_table_schema_reference($projectKey);
    $legacyScopeSummary = null;
    if ($legacyReference['ok'] && $legacyReference['item'] !== null && strtoupper($projectKey) === 'MTOOL') {
        $legacyScopeSummary = app_mtool_self_host_legacy_scope_summary($legacyReference['item']);
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Tables Import</title>
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
        .pill-stale {
            background: #fee2e2;
            color: #b91c1c;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables">tables</a> / import</p>

    <h1><?php echo app_h($item['name']); ?> Table Import</h1>
    <p>外で決めた DB 設計情報を取り込み、<code>dbtable</code> / <code>dbtablecolumns</code> の canonical metadata を作る入口です。現在は built-in named database source の <code>live schema</code> / <code>lab live schema</code> に加え、admin settings で登録した external named database source と、一部の legacy module slice を import source として扱えます。</p>
    <p class="muted"><?php echo app_h($scopePolicy['summary']); ?></p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Import Source</h2>
            <ul>
                <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
                <li>selected source: <code><?php echo app_h($preview['summary']['source_label']); ?></code></li>
                <li>source schema: <code><?php echo app_h($preview['summary']['source_schema_name']); ?></code></li>
                <li>generated mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
            </ul>
            <?php if (count($sourceOptions) > 1): ?>
                <ul>
                    <?php foreach ($sourceOptions as $sourceOption): ?>
                        <li>
                            <?php if ($sourceOption['key'] === $preview['summary']['source_key']): ?>
                                <strong><code><?php echo app_h($sourceOption['label']); ?></code></strong>
                            <?php else: ?>
                                <a href="<?php echo app_h(app_project_tables_import_path($projectKey, $sourceOption['key'], $focusedTableName)); ?>"><code><?php echo app_h($sourceOption['label']); ?></code></a>
                            <?php endif; ?>
                            <span class="muted"><?php echo app_h($sourceOption['description']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="summary-card">
            <h2>Legacy Design Reference</h2>
            <?php if (!$legacyReference['ok'] || $legacyReference['item'] === null): ?>
                <p class="muted"><?php echo app_h($legacyReference['error']); ?></p>
            <?php else: ?>
                <ul>
                    <li>reference schema: <code><?php echo app_h($legacyReference['item']['source_schema_name']); ?></code></li>
                    <li>reference tables: <code><?php echo app_h((string) $legacyReference['item']['table_count']); ?></code></li>
                    <li>reference columns: <code><?php echo app_h((string) $legacyReference['item']['column_count']); ?></code></li>
                    <li>reference snapshot: <code>mtool/reference/mtool-legacy-table-schema.json</code></li>
                </ul>
                <?php if ($legacyScopeSummary !== null): ?>
                    <ul>
                        <li>current self-host mapped scope: <code><?php echo app_h((string) $legacyScopeSummary['mapped_reference_table_count']); ?></code> / <code><?php echo app_h((string) $legacyReference['item']['table_count']); ?></code> tables</li>
                        <li>remaining legacy tables outside current self-host slice: <code><?php echo app_h((string) $legacyScopeSummary['remaining_reference_table_count']); ?></code></li>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <section class="summary-card">
            <h2>Canonical Metadata</h2>
            <?php if ($canonicalSnapshotError !== ''): ?>
                <p class="muted"><?php echo app_h($canonicalSnapshotError); ?></p>
            <?php else: ?>
                <ul>
                    <li>saved tables: <code><?php echo app_h((string) $canonicalTableCount); ?></code></li>
                    <li>saved columns: <code><?php echo app_h((string) $canonicalColumnCount); ?></code></li>
                    <li>state: <code><?php echo app_h($canonicalTableCount > 0 ? 'active' : 'empty'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>Scope</h2>
            <ul>
                <li><?php echo app_h($scopePolicy['is_primary'] ? 'default の self-loop 対象は Project 1 (MTOOL) です。' : 'この project は reference/test data として扱い、default の self-loop 対象は Project 1 (MTOOL) のままです。'); ?></li>
                <li><code>live schema</code> は現在の app 接続先 DB、<code>lab live schema</code> は <code>db-lab</code> を source として読みます。external source は <a href="/settings/database-sources"><code>/settings/database-sources</code></a> で登録します</li>
                <li>canonical metadata の保存先は常に <code>db-config</code> で、preview source は切り替えられます。legacy reference のうち一部 module slice は scoped apply に対応します</li>
                <li>table metadata は選択した source を preview し、apply ではその source が管理する table scope だけを insert / update / delete します</li>
                <li>table metadata は column 名ベースで import し、source table の primary key 命名規約は current import の必須条件にしません</li>
                <li>full legacy design は別の reference snapshot で追跡し、現在の self-host slice がどこまで届いているかをここで見ます</li>
                <li>次の操作点は <a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/sync"><code>/data-classes/sync</code></a> です</li>
            </ul>
        </section>
    </div>

    <?php if ($focusedTableDisplayName !== ''): ?>
        <section class="summary-card">
            <h2>Focused Table</h2>
            <ul>
                <li>table: <code><?php echo app_h($focusedTableDisplayName); ?></code></li>
                <li>view: <code>dbtables_import_for_each.php</code> の純表示導線を current preview に寄せています</li>
                <li><a href="<?php echo app_h(app_project_tables_import_path($projectKey, $selectedSourceKey)); ?>">show all import diff</a></li>
                <?php if ($focusedTableResolved): ?>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($focusedTableDisplayName); ?>">table detail</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($focusedTableDisplayName); ?>/columns">column detail</a></li>
                <?php else: ?>
                    <li class="muted">指定 table は current import scope にまだ見つかっていません。</li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($legacyReference['ok'] && $legacyReference['item'] !== null && $legacyScopeSummary !== null): ?>
        <section class="summary-card">
            <h2>Current Self-Host Scope</h2>
            <p class="muted">current live schema import は full legacy design をまだそのまま吸っているわけではなく、明示的に対応付けた self-host slice を先に扱っています。</p>
            <table>
                <thead>
                <tr>
                    <th>current table</th>
                    <th>legacy source</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($legacyScopeSummary['mapped_pairs'] as $pair): ?>
                    <tr>
                        <td><code><?php echo app_h($pair['current_table_name']); ?></code></td>
                        <td><code><?php echo app_h($pair['legacy_table_name']); ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($legacyScopeSummary['remaining_reference_table_names'] !== []): ?>
                <details style="margin-top: 1rem;">
                    <summary>remaining legacy tables outside current self-host slice (<?php echo app_h((string) $legacyScopeSummary['remaining_reference_table_count']); ?>)</summary>
                    <p class="muted">これらは後続の module migration で順に取り込みます。</p>
                    <ul>
                        <?php foreach ($legacyScopeSummary['remaining_reference_table_names'] as $legacyTableName): ?>
                            <li><code><?php echo app_h($legacyTableName); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($preview['ok']): ?>
        <section class="summary-card">
            <h2><?php echo app_h($focusedTableDisplayName !== '' ? 'Focused Table Diff' : 'Current Diff'); ?></h2>
            <ul>
                <?php if ($focusedTableDisplayName !== ''): ?>
                    <li>focused table: <code><?php echo app_h($focusedTableDisplayName); ?></code></li>
                <?php endif; ?>
                <li>source tables: <code><?php echo app_h((string) ($preview['summary']['source_table_count'] ?? $preview['summary']['live_table_count'])); ?></code></li>
                <li>canonical tables: <code><?php echo app_h((string) $preview['summary']['canonical_table_count']); ?></code></li>
                <li>table new: <code><?php echo app_h((string) $preview['summary']['table_insert_count']); ?></code></li>
                <li>table changed: <code><?php echo app_h((string) $preview['summary']['table_changed_count']); ?></code></li>
                <li>table stale: <code><?php echo app_h((string) $preview['summary']['table_delete_count']); ?></code></li>
                <li>column new: <code><?php echo app_h((string) $preview['summary']['column_insert_count']); ?></code></li>
                <li>column changed: <code><?php echo app_h((string) $preview['summary']['column_update_count']); ?></code></li>
                <li>column stale: <code><?php echo app_h((string) $preview['summary']['column_delete_count']); ?></code></li>
            </ul>
            <?php if ($preview['summary']['source_apply_supported']): ?>
                <form method="post">
                    <input type="hidden" name="_csrf" value="<?php echo app_h(app_csrf_token()); ?>">
                    <input type="hidden" name="source_key" value="<?php echo app_h($preview['summary']['source_key']); ?>">
                    <?php if ($focusedTableDisplayName !== ''): ?>
                        <input type="hidden" name="table_name" value="<?php echo app_h($focusedTableDisplayName); ?>">
                    <?php endif; ?>
                    <div class="button-row">
                        <button type="submit">
                            <?php echo app_h($focusedTableDisplayName !== ''
                                ? $preview['summary']['source_label'] . ' から ' . $focusedTableDisplayName . ' を import'
                                : $preview['summary']['source_label'] . ' から canonical table metadata を import'); ?>
                        </button>
                        <span class="muted">再実行すると差分だけが更新されます。</span>
                    </div>
                </form>
                <p class="muted">CLI では <code>docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=<?php echo app_h($projectKey); ?> --source=<?php echo app_h($preview['summary']['source_key']); ?><?php echo $focusedTableDisplayName !== '' ? ' --table=' . app_h($focusedTableDisplayName) : ''; ?></code> を使えます。</p>
            <?php else: ?>
                <p class="muted">この source は preview only です。apply は <code>live schema</code> または <code>lab live schema</code> のような apply 対応 source を選んだときだけ使えます。</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($applyResult !== null && $applyResult['ok']): ?>
        <section class="result-card">
            <h2>Last Import Result</h2>
            <ul>
                <li>table inserted: <code><?php echo app_h((string) $applyResult['summary']['table_insert_count']); ?></code></li>
                <li>table changed: <code><?php echo app_h((string) $applyResult['summary']['table_changed_count']); ?></code></li>
                <li>table deleted: <code><?php echo app_h((string) $applyResult['summary']['table_delete_count']); ?></code></li>
                <li>column inserted: <code><?php echo app_h((string) $applyResult['summary']['column_insert_count']); ?></code></li>
                <li>column changed: <code><?php echo app_h((string) $applyResult['summary']['column_update_count']); ?></code></li>
                <li>column deleted: <code><?php echo app_h((string) $applyResult['summary']['column_delete_count']); ?></code></li>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($importErrors !== []): ?>
        <section class="note-card error-card">
            <h2>Import Errors</h2>
            <ul>
                <?php foreach ($importErrors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($preview['ok']): ?>
        <table>
            <thead>
            <tr>
                <th>table</th>
                <th>status</th>
                <th>columns</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($preview['tables'] as $table): ?>
                <tr>
                    <td><code><?php echo app_h($table['name']); ?></code></td>
                    <td><span class="pill <?php echo app_h(app_project_table_import_status_class($table['status'])); ?>"><?php echo app_h(app_project_table_import_status_label($table['status'])); ?></span></td>
                    <td>
                        source: <code><?php echo app_h((string) ($table['source_column_count'] ?? $table['live_column_count'])); ?></code><br>
                        canonical: <code><?php echo app_h((string) $table['canonical_column_count']); ?></code><br>
                        new/change/stale: <code><?php echo app_h((string) $table['column_insert_count']); ?></code> / <code><?php echo app_h((string) $table['column_update_count']); ?></code> / <code><?php echo app_h((string) $table['column_delete_count']); ?></code>
                    </td>
                    <td>
                        <a href="<?php echo app_h(app_project_tables_import_path($projectKey, $preview['summary']['source_key'], $table['name'])); ?>">focus</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>">detail</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/columns">columns</a>
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
