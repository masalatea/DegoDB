<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/compare_output_job_service.php';
require_once __DIR__ . '/compare_output_repository.php';
require_once __DIR__ . '/compare_output_service.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

function app_lab_compare_output_path(string $projectKey, string $compareOutputKey = ''): string
{
    $path = '/runs/compare-output/' . rawurlencode($projectKey);
    if ($compareOutputKey !== '') {
        $path .= '?compare_output_key=' . rawurlencode($compareOutputKey);
    }

    return $path;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     },
 *     config_db:array{
 *         name:string
 *     },
 *     generated:array{
 *         root:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_lab_compare_output_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab' && $app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 実験用サイト または 設定変更用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['lab', 'admin'], $principal)) {
        app_render_forbidden_page($app, $request, 'compare output 実行には lab または admin role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if (!$projectResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Compare Output Run</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>compare output run の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($projectResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    if ($projectResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $project = $projectResult['item'];
    $csrfToken = app_csrf_token();
    $errors = [];
    $selectedCompareOutputKey = '';
    $selectedCompareOutput = null;
    $selectedAdditionalPaths = [];
    $selectedResolvedPaths = null;
    $generationResult = null;
    $createdJob = null;
    $recentJobs = [];
    $generateRequested = false;

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action'));
            $selectedCompareOutputKey = app_normalize_compare_output_key(app_post_param('compare_output_key'));

            if ($action === 'generate-compare-output-file') {
                if ($selectedCompareOutputKey === '' || !app_compare_output_key_is_valid($selectedCompareOutputKey)) {
                    $errors[] = '出力対象の compare output key が不正です。';
                } else {
                    $generateRequested = true;
                }
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $catalogResult = app_fetch_project_compare_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }
    $compareOutputs = $catalogResult['items'];

    if ($selectedCompareOutputKey === '') {
        $selectedCompareOutputKey = app_normalize_compare_output_key(app_query_param('compare_output_key'));
    }
    if ($selectedCompareOutputKey === '' && $compareOutputs !== []) {
        $selectedCompareOutputKey = app_normalize_compare_output_key($compareOutputs[0]['compare_output_key']);
    }

    if ($selectedCompareOutputKey !== '') {
        if (!app_compare_output_key_is_valid($selectedCompareOutputKey)) {
            $errors[] = 'compare output key の形式が不正です。';
        } else {
            $itemResult = app_fetch_project_compare_output_item($app, $projectKey, $selectedCompareOutputKey);
            if (!$itemResult['ok']) {
                $errors[] = $itemResult['error'];
            } elseif ($itemResult['item'] === null) {
                $errors[] = '指定された compare output が見つかりません。';
            } else {
                $selectedCompareOutput = $itemResult['item'];

                $additionalPathResult = app_fetch_project_compare_output_additional_path_catalog(
                    $app,
                    $projectKey,
                    $selectedCompareOutputKey,
                );
                if (!$additionalPathResult['ok']) {
                    $errors[] = $additionalPathResult['error'];
                } else {
                    $selectedAdditionalPaths = $additionalPathResult['items'];
                }

                $selectedResolvedPaths = app_compare_output_resolve_definition_paths($selectedCompareOutput);
            }
        }
    }

    if ($generateRequested && $selectedCompareOutput !== null && $errors === []) {
        $serviceResult = app_compare_output_job_create(
            $app,
            $projectKey,
            $selectedCompareOutput,
            $selectedAdditionalPaths,
            'lab-ui:' . $principal['id'],
        );
        if ($serviceResult['ok'] && $serviceResult['output'] !== null && $serviceResult['job'] !== null) {
            $generationResult = $serviceResult['output'];
            $createdJob = $serviceResult['job'];
        } else {
            $errors[] = $serviceResult['error'];
        }
    }

    if ($selectedCompareOutput !== null) {
        $jobCatalogResult = app_compare_output_job_list(
            $app,
            $projectKey,
            $selectedCompareOutput['compare_output_key'],
            10,
        );
        if ($jobCatalogResult['ok']) {
            $recentJobs = $jobCatalogResult['items'];
        } else {
            $errors[] = $jobCatalogResult['error'];
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $primaryDatabaseStatus = app_probe_database($app);
    $configDatabaseStatus = app_probe_config_database($app);
    $cliCommand = '';
    if ($selectedCompareOutput !== null) {
        $cliCommand = 'docker compose exec -T web-lab php /var/www/mtool/scripts/create_compare_output.php'
            . ' --project-key=' . $projectKey
            . ' --compare-output-key=' . $selectedCompareOutput['compare_output_key']
            . ' --requested-by=' . $principal['id'];
    }
    $isLabSite = $app['site'] === 'lab';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Compare Output Run</title>
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
        pre {
            background: #edf2f7;
            padding: 0.9rem 1rem;
            border-radius: 8px;
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
        .summary-card, .note-card, .error-card, .success-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #ecfdf5;
            border-color: #86efac;
        }
        .section-heading {
            margin-top: 2rem;
            margin-bottom: 0.25rem;
        }
        .muted {
            color: #475569;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1rem;
            font: inherit;
            cursor: pointer;
            text-decoration: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .selected-row {
            background: #f8fafc;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs">
        <a href="/dashboard">dashboard</a>
        <?php if ($isLabSite): ?>
            / <a href="/experiments">experiments</a> / <code><?php echo app_h($projectKey); ?></code> / compare-output
        <?php else: ?>
            / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / compare-output
        <?php endif; ?>
    </p>

    <h1><?php echo app_h($project['name']); ?> Compare Output 実行</h1>
    <p><code>admin</code> 側で管理された canonical compare output definition を read-only 参照し、local compare output file と review 用 job を生成する画面です。</p>

    <?php if ($generationResult !== null): ?>
        <section class="success-card">
            <h2>生成結果</h2>
            <ul>
                <li>output file: <code><?php echo app_h($generationResult['output_file_absolute_path']); ?></code></li>
                <li>deviation pairs: <code><?php echo app_h((string) $generationResult['deviation_pair_count']); ?></code></li>
                <li>checked pairs: <code><?php echo app_h((string) $generationResult['checked_pair_count']); ?></code></li>
                <li>bytes: <code><?php echo app_h((string) $generationResult['output_bytes']); ?></code></li>
                <li>requested by: <code><?php echo app_h($generationResult['requested_by']); ?></code></li>
                <?php if ($createdJob !== null): ?>
                    <li>job key: <code><?php echo app_h($createdJob['job_key']); ?></code></li>
                    <li>job detail: <code><?php echo app_h(app_lab_compare_output_job_path($createdJob['job_key'])); ?></code></li>
                <?php endif; ?>
            </ul>
            <?php if ($generationResult['warnings'] !== []): ?>
                <h3>Warnings</h3>
                <ul>
                    <?php foreach ($generationResult['warnings'] as $warning): ?>
                        <li><?php echo app_h($warning); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <h3>Rendered Output</h3>
            <pre><?php echo app_h($generationResult['rendered_content']); ?></pre>
            <?php if ($createdJob !== null): ?>
                <p><a href="<?php echo app_h(app_lab_compare_output_job_path($createdJob['job_key'])); ?>">job detail を開く</a></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="error-card">
            <h2>エラー</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($project['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($project['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($project['lifecycle_status']); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Database</h2>
            <ul>
                <li>primary db: <code><?php echo app_h($app['db']['name']); ?></code> / <code><?php echo app_h($primaryDatabaseStatus['label']); ?></code></li>
                <li>config db: <code><?php echo app_h($app['config_db']['name']); ?></code> / <code><?php echo app_h($configDatabaseStatus['label']); ?></code></li>
                <li>config detail: <code><?php echo app_h($configDatabaseStatus['detail']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Definition</h2>
            <ul>
                <li>compare outputs: <code><?php echo app_h((string) count($compareOutputs)); ?></code></li>
                <li>selected: <code><?php echo app_h($selectedCompareOutputKey !== '' ? $selectedCompareOutputKey : 'none'); ?></code></li>
                <li>additional paths: <code><?php echo app_h((string) count($selectedAdditionalPaths)); ?></code></li>
                <li>recent jobs: <code><?php echo app_h((string) count($recentJobs)); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Execution Context</h2>
            <ul>
                <li>reference root: <code><?php echo app_h($app['generated']['root']); ?></code></li>
                <li>work root: <code><?php echo app_h($app['work']['root']); ?></code></li>
                <li>route: <code>/runs/compare-output/<?php echo app_h($projectKey); ?></code></li>
                <li>mode: <code>read canonical definition from config db</code></li>
            </ul>
        </section>
    </div>

    <section>
        <h2 class="section-heading">Definitions</h2>
        <?php if ($compareOutputs === []): ?>
            <p class="muted">この project には compare output definition がまだありません。先に admin 側の `/projects/<?php echo app_h($projectKey); ?>/compare-output-settings` で定義を作成してください。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>definition</th>
                    <th>output target</th>
                    <th>compare target</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($compareOutputs as $compareOutput): ?>
                    <?php $isSelected = $selectedCompareOutputKey !== '' && $selectedCompareOutputKey === $compareOutput['compare_output_key']; ?>
                    <tr<?php echo $isSelected ? ' class="selected-row"' : ''; ?>>
                        <td>
                            <strong><code><?php echo app_h($compareOutput['compare_output_key']); ?></code></strong><br>
                            <?php echo app_h($compareOutput['name']); ?><br>
                            <span class="muted">updated: <?php echo app_h($compareOutput['updated_at']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h(app_compare_output_file_type_caption($compareOutput['output_file_type'])); ?></code><br>
                            <code><?php echo app_h($compareOutput['output_file_path']); ?></code>
                        </td>
                        <td>
                            <code><?php echo app_h($compareOutput['compare_path']); ?></code><br>
                            <span class="muted">source: <?php echo app_h($compareOutput['source_of_truth']); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_lab_compare_output_path($projectKey, $compareOutput['compare_output_key'])); ?>">select</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <?php if ($selectedCompareOutput !== null): ?>
        <section>
            <h2 class="section-heading">Selected Definition</h2>
            <ul>
                <li>name: <code><?php echo app_h($selectedCompareOutput['name']); ?></code></li>
                <li>output file type: <code><?php echo app_h($selectedCompareOutput['output_file_type']); ?></code></li>
                <li>compare tool file path: <code><?php echo app_h($selectedCompareOutput['compare_tool_file_path'] !== '' ? $selectedCompareOutput['compare_tool_file_path'] : '(blank)'); ?></code></li>
                <li>notes: <?php echo nl2br(app_h($selectedCompareOutput['notes'] !== '' ? $selectedCompareOutput['notes'] : '(blank)')); ?></li>
            </ul>

            <?php if ($selectedResolvedPaths !== null): ?>
                <h3>Resolved Paths</h3>
                <?php if ($selectedResolvedPaths['ok']): ?>
                    <ul>
                        <li>storage base: <code><?php echo app_h($selectedResolvedPaths['resolved_storage_base_path']); ?></code></li>
                        <li>compare root: <code><?php echo app_h($selectedResolvedPaths['compare_root_absolute_path']); ?></code></li>
                        <li>output file: <code><?php echo app_h($selectedResolvedPaths['output_file_absolute_path']); ?></code></li>
                    </ul>
                <?php else: ?>
                    <div class="error-card">
                        <strong>path resolve error:</strong>
                        <code><?php echo app_h($selectedResolvedPaths['error']); ?></code>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <h3>CLI</h3>
            <p class="muted">同じ生成 service は container 内 CLI からも実行できます。</p>
            <pre><?php echo app_h($cliCommand); ?></pre>

            <p class="muted">admin definition path: <code>/projects/<?php echo app_h($projectKey); ?>/compare-output-settings?compare_output_key=<?php echo app_h($selectedCompareOutput['compare_output_key']); ?></code> を admin site 側で開いて編集します。</p>

            <form method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="generate-compare-output-file">
                <input type="hidden" name="compare_output_key" value="<?php echo app_h($selectedCompareOutput['compare_output_key']); ?>">
                <div class="button-row">
                    <button class="button" type="submit">Generate Output File</button>
                </div>
            </form>

            <h3>Additional Paths</h3>
            <?php if ($selectedAdditionalPaths === []): ?>
                <p class="muted">additional path はありません。</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>key</th>
                        <th>path A</th>
                        <th>path B</th>
                        <th>mode</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($selectedAdditionalPaths as $additionalPath): ?>
                        <tr>
                            <td><code><?php echo app_h($additionalPath['additional_path_key']); ?></code></td>
                            <td>
                                <code><?php echo app_h(($additionalPath['path_a_base_path'] !== '' ? $additionalPath['path_a_base_path'] . ' :: ' : '') . $additionalPath['path_a']); ?></code>
                            </td>
                            <td>
                                <code><?php echo app_h(($additionalPath['path_b_base_path'] !== '' ? $additionalPath['path_b_base_path'] . ' :: ' : '') . $additionalPath['path_b']); ?></code>
                            </td>
                            <td><code><?php echo app_h($additionalPath['is_same_filename_only'] === '1' ? 'same-filename-only' : 'full-hash'); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Recent Jobs</h3>
            <?php if ($recentJobs === []): ?>
                <p class="muted">この compare output key の job はまだありません。</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>job</th>
                        <th>requested by</th>
                        <th>result</th>
                        <th>action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentJobs as $recentJob): ?>
                        <tr<?php echo $createdJob !== null && $recentJob['job_key'] === $createdJob['job_key'] ? ' class="selected-row"' : ''; ?>>
                            <td>
                                <code><?php echo app_h($recentJob['job_key']); ?></code><br>
                                <span class="muted"><?php echo app_h($recentJob['created_at']); ?></span>
                            </td>
                            <td><code><?php echo app_h($recentJob['requested_by']); ?></code></td>
                            <td>
                                pairs <code><?php echo app_h((string) $recentJob['deviation_pair_count']); ?></code><br>
                                warnings <code><?php echo app_h((string) $recentJob['warning_count']); ?></code>
                            </td>
                            <td><a href="<?php echo app_h(app_lab_compare_output_job_path($recentJob['job_key'])); ?>">open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
