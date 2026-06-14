<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/generated_runtime.php';
require_once __DIR__ . '/project_data_class_sync_service.php';
require_once __DIR__ . '/project_db_access_sync_service.php';
require_once __DIR__ . '/project_language_resource_route_common.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/project_scope_policy.php';
require_once __DIR__ . '/project_table_import_service.php';
require_once __DIR__ . '/response.php';

/**
 * @return list<array{
 *     title:string,
 *     status:string,
 *     summary:string,
 *     legacy_scope:string,
 *     planned_path:string,
 *     available_path:string
 * }>
 */
function app_project_available_modules(string $projectKey): array
{
    return [
        [
            'title' => 'Project 基本設定',
            'status' => 'available',
            'summary' => '旧 project_edit 系の入口です。現段階では project identity / slug / lifecycle / description を settings route で更新でき、Storage / DB / Proxy / option 群は後続でこの画面へ拡張します。',
            'legacy_scope' => 'project_edit.php, project_edit_include.php',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/settings',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/settings',
        ],
        [
            'title' => 'DB Table metadata',
            'status' => 'available',
            'summary' => 'この project の最初の起点です。DB 設計情報を import して `dbtable` / `dbtablecolumns` を管理する入口であり、後続の Data Class / DB Access / Source Output はここで取り込んだ metadata を基準にします。現在は canonical import を実行でき、未導入 project だけ runtime reference fallback を参照します。',
            'legacy_scope' => 'dbtables*.php (9 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/tables',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/tables',
        ],
        [
            'title' => 'Data Class',
            'status' => 'available',
            'summary' => 'table metadata を `dataclass` / `dataclassfields` へ同期し、Data Class を生成する入口です。現在は canonical sync を実行でき、同期済み row を優先表示しつつ runtime reference source は reference / fallback として扱います。',
            'legacy_scope' => 'dataclasses*.php (12 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/data-classes',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/data-classes',
        ],
        [
            'title' => 'DB Access / Query 設計',
            'status' => 'available',
            'summary' => 'Data Class を土台に `da` / `dafunc` を設計し、DB Access を生成する入口です。現段階では `dbaccess-*.php` の class / method をもとに、DB Access と function candidate を preview できます。',
            'legacy_scope' => 'da*.php + da_func*.php (56 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/db-access',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/db-access',
        ],
        [
            'title' => 'Source Output',
            'status' => 'available',
            'summary' => 'Data Class / DB Access metadata をもとに `ProjectSourceOutput` 単位で artifact を生成する入口です。現段階では `RUNTIME-DBCLASSES` が runtime reference を staging し、sync 済み canonical DB Access metadata があれば root `dbaccess-*` を wrapper 再生成した artifact を UI と CLI の両方から出力できます。',
            'legacy_scope' => 'project_source_output*.php (7 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/source-outputs',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/source-outputs',
        ],
        [
            'title' => 'Compare Output 設定',
            'status' => 'available',
            'summary' => '比較設定、追加 path、template asset、ignore rule asset を admin 側の canonical metadata / asset として管理できます。実行ジョブと review は lab 側 route で利用可能です。',
            'legacy_scope' => 'compare_output*.php settings + assets (22 files total のうち設定側)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/compare-output-settings',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/compare-output-settings',
        ],
    ];
}

/**
 * @param array{
 *     state:string,
 *     module_status:string,
 *     title:string,
 *     summary:string,
 *     readonly_message:string,
 *     editor_available:bool
 * } $languageResourceModuleState
 * @return list<array{
 *     title:string,
 *     status:string,
 *     summary:string,
 *     legacy_scope:string,
 *     planned_path:string,
 *     available_path:string
 * }>
 */
function app_project_admin_modules(string $projectKey, array $languageResourceModuleState): array
{
    return [
        [
            'title' => 'Proxy 設定',
            'status' => 'available-partial',
            'summary' => 'custom proxy の canonical metadata / step / endpoint preview と、single target proxy の project-scoped navigator を current route で使えます。bulk update 系 POST はまだ legacy fallback を残します。',
            'legacy_scope' => 'da_edit_proxy_single_target.php, da_proxy_custom*.php (12 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/proxy/single',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/proxy/single',
        ],
        [
            'title' => 'HTML',
            'status' => 'available-partial',
            'summary' => 'HTML list / detail / parameter CRUD と global template settings は current route で扱えます。残りは generator/runtime 側の bootstrap dependency 圧縮です。',
            'legacy_scope' => 'html*.php (6 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/html',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/html',
        ],
        [
            'title' => 'Language Resource',
            'status' => (string) ($languageResourceModuleState['module_status'] ?? 'planned'),
            'summary' => match ((string) ($languageResourceModuleState['state'] ?? 'unknown')) {
                'file-canonical' => 'file-based canonical catalog を primary source として resources / groups / detail から表示できます。Lang 編集画面は作らず、AI / 人が repo file を直接編集する前提に切り替えます。DB 側は削除前確認用の移行ブリッジに下げます。',
                'reference' => 'copied legacy reference fallback で optional module を read-only 提供しています。Lang 編集画面は前提にせず、必要なら file canonical へ変換して repo file を直接編集します。',
                'empty' => 'LanguageResource optional module は未ロードです。current route は empty catalog の read-only 表示だけを提供し、MTOOL core の正式起動対象からは外します。',
                default => (string) ($languageResourceModuleState['summary'] ?? 'LanguageResource module state の解決に失敗しました。'),
            },
            'legacy_scope' => 'lang_res*.php (13 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/language-resources',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/language-resources',
        ],
        [
            'title' => 'Security / Host Assignment',
            'status' => 'available-partial',
            'summary' => 'project membership は `security/users` で owner / admin / member を更新できます。page security と host assignment も project 配下の current landing zone で編集でき、route policy / infra catalog への最終 split は後段です。',
            'legacy_scope' => 'project_security*.php + project_host_assignment*.php (10 files)',
            'planned_path' => '/projects/' . rawurlencode($projectKey) . '/security',
            'available_path' => '/projects/' . rawurlencode($projectKey) . '/security',
        ],
    ];
}

/**
 * @return list<array{
 *     title:string,
 *     status:string,
 *     summary:string,
 *     legacy_scope:string,
 *     planned_path:string,
 *     available_path:string
 * }>
 */
function app_project_lab_modules(string $projectKey): array
{
    return [
        [
            'title' => 'Build 実行',
            'status' => 'available',
            'summary' => '`/runs/builds/{project_key}` で selected source output definition を current generator から `generate + write output` し、結果を file-based job manifest として review できます。',
            'legacy_scope' => 'build_project*.php',
            'planned_path' => '/runs/builds/' . rawurlencode($projectKey),
            'available_path' => '/runs/builds/' . rawurlencode($projectKey),
        ],
        [
            'title' => 'Compare Output 実行',
            'status' => 'available',
            'summary' => '実験サイトの `/runs/compare-output/{project_key}` で canonical compare definition を読み、local compare output file を生成できます。recent jobs、job review、JSON API も利用できます。',
            'legacy_scope' => 'compare_output_do*.php + compare_output*.php execution',
            'planned_path' => '/runs/compare-output/' . rawurlencode($projectKey),
            'available_path' => '/runs/compare-output/' . rawurlencode($projectKey),
        ],
        [
            'title' => 'Endpoint Test',
            'status' => 'available-partial',
            'summary' => '実験サイトの `/runs/endpoints/{project_key}` で single-function proxy candidate または manual URL を使った JSON endpoint test を実行できます。legacy `endpoint_test_json_ajax.php` も known-project POST は current endpoint-test job service に接続され、custom proxy preview 互換の HTML 応答を返します。',
            'legacy_scope' => 'endpoint_test_json_ajax.php + endpoint_* includes',
            'planned_path' => '/runs/endpoints/' . rawurlencode($projectKey),
            'available_path' => '/runs/endpoints/' . rawurlencode($projectKey),
        ],
        [
            'title' => 'Swagger Viewer',
            'status' => 'available-partial',
            'summary' => '実験サイトの `/runs/swagger/{project_key}` で generated `openapi.json` を読み、single-function proxy contract を viewer 形式で確認できます。Try it out は browser fetch で動くため、base URL と CORS 条件は target endpoint 側に従います。',
            'legacy_scope' => 'new current-only OpenAPI / Swagger viewer',
            'planned_path' => '/runs/swagger/' . rawurlencode($projectKey),
            'available_path' => '/runs/swagger/' . rawurlencode($projectKey),
        ],
    ];
}

/**
 * @param array{
 *     title:string,
 *     status:string,
 *     summary:string,
 *     legacy_scope:string,
 *     planned_path:string,
 *     available_path:string
 * } $module
 */
function app_project_module_status_label(array $module): string
{
    return match ($module['status']) {
        'available' => 'available now',
        'available-partial' => 'available partial',
        'optional-readonly' => 'optional readonly',
        'optional-off' => 'optional off',
        'blocked' => 'blocked',
        default => 'planned',
    };
}

function app_project_detail_upstream_status_label(string $status): string
{
    return match ($status) {
        'ok' => 'in sync',
        'needs-action' => 'action needed',
        'blocked' => 'blocked',
        default => 'reference',
    };
}

function app_project_detail_upstream_status_class(string $status): string
{
    return match ($status) {
        'ok' => 'status-ok',
        'needs-action' => 'status-needs-action',
        'blocked' => 'status-blocked',
        default => 'status-info',
    };
}

/**
 * @return list<array{
 *     title:string,
 *     status:string,
 *     summary:string,
 *     path:string
 * }>
 */
function app_project_detail_upstream_status_items(array $app, string $projectKey): array
{
    $policy = app_project_scope_policy($projectKey);
    if (!$policy['is_primary']) {
        return [
            [
                'title' => 'Scope',
                'status' => 'info',
                'summary' => $policy['summary'],
                'path' => '',
            ],
        ];
    }

    $items = [];

    $importPreview = app_project_table_import_preview($app, $projectKey, 'live-schema');
    if (!$importPreview['ok']) {
        $items[] = [
            'title' => 'DB import',
            'status' => 'blocked',
            'summary' => $importPreview['error'],
            'path' => '/projects/' . rawurlencode($projectKey) . '/tables/import',
        ];
    } else {
        $tableDiffCount = (int) $importPreview['summary']['table_insert_count']
            + (int) $importPreview['summary']['table_changed_count']
            + (int) $importPreview['summary']['table_delete_count'];
        $columnDiffCount = (int) $importPreview['summary']['column_insert_count']
            + (int) $importPreview['summary']['column_update_count']
            + (int) $importPreview['summary']['column_delete_count'];
        $items[] = [
            'title' => 'DB import',
            'status' => ($tableDiffCount > 0 || $columnDiffCount > 0) ? 'needs-action' : 'ok',
            'summary' => ($tableDiffCount > 0 || $columnDiffCount > 0)
                ? 'live schema との差分があります。table diff '
                    . $tableDiffCount
                    . ' / column diff '
                    . $columnDiffCount
                    . '。まず /tables/import を更新します。'
                : 'live schema と canonical dbtable/dbtablecolumns は一致しています。',
            'path' => '/projects/' . rawurlencode($projectKey) . '/tables/import',
        ];
    }

    $dataClassPreview = app_project_data_class_sync_preview($app, $projectKey);
    if (!$dataClassPreview['ok']) {
        $items[] = [
            'title' => 'Data Class sync',
            'status' => 'blocked',
            'summary' => $dataClassPreview['error'],
            'path' => '/projects/' . rawurlencode($projectKey) . '/data-classes/sync',
        ];
    } else {
        $classDiffCount = (int) $dataClassPreview['summary']['class_insert_count']
            + (int) $dataClassPreview['summary']['class_update_count'];
        $fieldDiffCount = (int) $dataClassPreview['summary']['field_insert_count']
            + (int) $dataClassPreview['summary']['field_update_count'];
        $items[] = [
            'title' => 'Data Class sync',
            'status' => ($classDiffCount > 0 || $fieldDiffCount > 0) ? 'needs-action' : 'ok',
            'summary' => ($classDiffCount > 0 || $fieldDiffCount > 0)
                ? 'canonical dataclass/dataclassfields に未反映の差分があります。class diff '
                    . $classDiffCount
                    . ' / field diff '
                    . $fieldDiffCount
                    . '。/data-classes/sync を先に進めます。'
                : 'canonical dataclass/dataclassfields は現在の table metadata と揃っています。',
            'path' => '/projects/' . rawurlencode($projectKey) . '/data-classes/sync',
        ];
    }

    $dbAccessPreflight = app_project_db_access_sync_preflight($app, $projectKey);
    $items[] = [
        'title' => 'DB Access sync',
        'status' => $dbAccessPreflight['ok'] ? 'ok' : 'blocked',
        'summary' => $dbAccessPreflight['ok']
            ? 'bootstrap dbclasses catalog は利用可能です。upstream を揃えたら /db-access/sync へ進めます。'
            : $dbAccessPreflight['error'],
        'path' => '/projects/' . rawurlencode($projectKey) . '/db-access/sync',
    ];

    return $items;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     },
 *     generated:array{
 *         root:string,
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_detail_page(array $app, array $request): void
{
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'project hub の参照には admin または config role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
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
    <title><?php echo app_h($app['site_name']); ?> - Project Hub</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>project hub の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($project['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    if ($project['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $item = $project['item'];
    $languageResourceModuleState = app_project_language_resource_module_state_for_project(
        $app,
        $projectKey,
        (int) ($item['legacy_project_pid'] ?? 0),
    );
    $availableModules = app_project_available_modules($projectKey);
    $adminModules = app_project_admin_modules($projectKey, $languageResourceModuleState);
    $labModules = app_project_lab_modules($projectKey);
    $generatedRuntime = app_generated_runtime_summary($app);
    $scopePolicy = app_project_scope_policy($projectKey);
    $upstreamStatusItems = app_project_detail_upstream_status_items($app, $projectKey);

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Hub</title>
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
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .start-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .module-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .module-table th,
        .module-table td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .status-pill {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .status-available {
            background: #dcfce7;
            color: #166534;
        }
        .status-available-partial {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .status-optional-readonly {
            background: #fef3c7;
            color: #92400e;
        }
        .status-optional-off {
            background: #e2e8f0;
            color: #475569;
        }
        .status-planned {
            background: #e2e8f0;
            color: #334155;
        }
        .status-ok {
            background: #dcfce7;
            color: #166534;
        }
        .status-needs-action {
            background: #fef3c7;
            color: #92400e;
        }
        .status-blocked {
            background: #fee2e2;
            color: #b91c1c;
        }
        .status-info {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .section-heading {
            margin-top: 2rem;
            margin-bottom: 0.25rem;
        }
        .muted {
            color: #475569;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .module-table a {
            white-space: nowrap;
        }
        .status-list {
            margin: 0;
            padding-left: 1.2rem;
        }
        .status-list li + li {
            margin-top: 0.75rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <code><?php echo app_h($item['project_key']); ?></code></p>

    <h1><?php echo app_h($item['name']); ?> Project ハブ</h1>
    <p>project 単位で metadata 管理と生成系モジュールへの導線を集約する入口です。既存で使える操作と未実装モジュールを分離して見えるようにしています。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project 概要</h2>
            <ul>
                <li>project key: <code><?php echo app_h($item['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($item['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($item['lifecycle_status']); ?></code></li>
                <li>owner: <code><?php echo app_h($item['owner_login_id']); ?></code></li>
                <li>members: <code><?php echo app_h((string) $item['member_count']); ?></code></li>
                <li>updated: <code><?php echo app_h($item['updated_at']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Context</h2>
            <ul>
                <li>site: <code><?php echo app_h($app['site']); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
                <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
                <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Loop Policy</h2>
            <p class="muted"><?php echo app_h($scopePolicy['summary']); ?></p>
            <ul>
                <?php foreach ($scopePolicy['details'] as $detail): ?>
                    <li><?php echo app_h($detail); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="summary-card start-card">
            <h2>最初にやること</h2>
            <p class="muted">この project では DB import が全ての起点です。外部 DB schema に差分がある場合は downstream の画面へ進む前にここへ戻ります。</p>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import"><code>/projects/<?php echo app_h($projectKey); ?>/tables/import</code></a> で DB 設計情報の import 状態を確認する</li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/sync"><code>/projects/<?php echo app_h($projectKey); ?>/data-classes/sync</code></a> で Data Class への sync を確認する</li>
                <li>その後に <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> と <a href="/projects/<?php echo rawurlencode($projectKey); ?>/source-outputs">source-outputs</a> を進める</li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Upstream Status</h2>
            <ul class="status-list">
                <?php foreach ($upstreamStatusItems as $statusItem): ?>
                    <li>
                        <span class="status-pill <?php echo app_h(app_project_detail_upstream_status_class($statusItem['status'])); ?>">
                            <?php echo app_h(app_project_detail_upstream_status_label($statusItem['status'])); ?>
                        </span>
                        <strong><?php echo app_h($statusItem['title']); ?></strong><br>
                        <span class="muted"><?php echo app_h($statusItem['summary']); ?></span>
                        <?php if ($statusItem['path'] !== ''): ?><br><a href="<?php echo app_h($statusItem['path']); ?>"><code><?php echo app_h($statusItem['path']); ?></code></a><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Reference</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>root: <code><?php echo app_h($generatedRuntime['root']); ?></code></li>
                <li>dbclasses root: <code><?php echo app_h($generatedRuntime['dbclasses_root']); ?></code></li>
                <li>loader: <code><?php echo app_h($generatedRuntime['dbclasses_loader']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
                <li>files: <code><?php echo app_h((string) $generatedRuntime['total_file_count']); ?></code> total / <code><?php echo app_h((string) $generatedRuntime['data_file_count']); ?></code> data / <code><?php echo app_h((string) $generatedRuntime['dbaccess_file_count']); ?></code> dbaccess</li>
                <li>project repository driver: <code><?php echo app_h($generatedRuntime['project_repository_driver']); ?></code></li>
                <li>experiment repository driver: <code><?php echo app_h($generatedRuntime['experiment_repository_driver']); ?></code></li>
            </ul>
        </section>
    </div>

    <section>
        <h2 class="section-heading">description</h2>
        <p><?php echo nl2br(app_h($item['description'])); ?></p>
    </section>

    <section>
        <h2 class="section-heading">現在使える操作</h2>
        <table class="module-table">
            <thead>
            <tr>
                <th>module</th>
                <th>status</th>
                <th>legacy</th>
                <th>planned route</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($availableModules as $module): ?>
                <tr>
                    <td>
                        <strong><?php echo app_h($module['title']); ?></strong><br>
                        <?php echo app_h($module['summary']); ?>
                    </td>
                    <td>
                        <span class="status-pill status-<?php echo app_h($module['status']); ?>">
                            <?php echo app_h(app_project_module_status_label($module)); ?>
                        </span>
                    </td>
                    <td><code><?php echo app_h($module['legacy_scope']); ?></code></td>
                    <td><code><?php echo app_h($module['planned_path']); ?></code></td>
                    <td><a href="<?php echo app_h($module['available_path']); ?>">open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2 class="section-heading">admin 側で再構築する設定モジュール</h2>
        <p class="muted">旧 `dev web/db/` の canonical な設定編集系は、すべて `admin` 側に寄せます。</p>
        <table class="module-table">
            <thead>
            <tr>
                <th>module</th>
                <th>status</th>
                <th>legacy</th>
                <th>target route</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($adminModules as $module): ?>
                <tr>
                    <td>
                        <strong><?php echo app_h($module['title']); ?></strong><br>
                        <?php echo app_h($module['summary']); ?>
                    </td>
                    <td>
                        <span class="status-pill status-<?php echo app_h($module['status']); ?>">
                            <?php echo app_h(app_project_module_status_label($module)); ?>
                        </span>
                    </td>
                    <td><code><?php echo app_h($module['legacy_scope']); ?></code></td>
                    <td><code><?php echo app_h($module['planned_path']); ?></code></td>
                    <td>
                        <?php if ($module['available_path'] !== ''): ?>
                            <a href="<?php echo app_h($module['available_path']); ?>">open</a>
                        <?php else: ?>
                            <span class="muted">planned</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2 class="section-heading">lab 側で再構築する実行モジュール</h2>
        <p class="muted">実行ジョブ、比較、endpoint test は `lab` 側へ分離します。</p>
        <table class="module-table">
            <thead>
            <tr>
                <th>module</th>
                <th>status</th>
                <th>legacy</th>
                <th>target route</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($labModules as $module): ?>
                <tr>
                    <td>
                        <strong><?php echo app_h($module['title']); ?></strong><br>
                        <?php echo app_h($module['summary']); ?>
                    </td>
                    <td>
                        <span class="status-pill status-<?php echo app_h($module['status']); ?>">
                            <?php echo app_h(app_project_module_status_label($module)); ?>
                        </span>
                    </td>
                    <td><code><?php echo app_h($module['legacy_scope']); ?></code></td>
                    <td><code><?php echo app_h($module['planned_path']); ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
    <?php
}
