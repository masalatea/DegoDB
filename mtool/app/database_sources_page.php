<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/database_source_repository.php';
require_once __DIR__ . '/database_source_route_common.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

/**
 * @return array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     database_name:string,
 *     user_name:string,
 *     password:string,
 *     supports_live_schema_import:string,
 *     supports_proxy_runtime_read:string,
 *     proxy_runtime_priority:string,
 *     source_of_truth:string
 * }
 */
function app_database_source_default_form_input(): array
{
    return [
        'source_key' => '',
        'label' => '',
        'description' => '',
        'host' => '',
        'port' => '3306',
        'database_name' => '',
        'user_name' => '',
        'password' => '',
        'supports_live_schema_import' => '1',
        'supports_proxy_runtime_read' => '0',
        'proxy_runtime_priority' => '1000',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @param array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     source_of_truth:string
 * } $item
 * @return array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     database_name:string,
 *     user_name:string,
 *     password:string,
 *     supports_live_schema_import:string,
 *     supports_proxy_runtime_read:string,
 *     proxy_runtime_priority:string,
 *     source_of_truth:string
 * }
 */
function app_database_source_form_input_from_item(array $item): array
{
    return [
        'source_key' => (string) ($item['source_key'] ?? ''),
        'label' => (string) ($item['label'] ?? ''),
        'description' => (string) ($item['description'] ?? ''),
        'host' => (string) ($item['host'] ?? ''),
        'port' => (string) ($item['port'] ?? '3306'),
        'database_name' => (string) ($item['name'] ?? ''),
        'user_name' => (string) ($item['user'] ?? ''),
        'password' => (string) ($item['password'] ?? ''),
        'supports_live_schema_import' => !empty($item['supports_live_schema_import']) ? '1' : '0',
        'supports_proxy_runtime_read' => !empty($item['supports_proxy_runtime_read']) ? '1' : '0',
        'proxy_runtime_priority' => (string) ((int) ($item['proxy_runtime_priority'] ?? 1000)),
        'source_of_truth' => (string) ($item['source_of_truth'] ?? 'manual'),
    ];
}

/**
 * @return array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     database_name:string,
 *     user_name:string,
 *     password:string,
 *     supports_live_schema_import:string,
 *     supports_proxy_runtime_read:string,
 *     proxy_runtime_priority:string,
 *     source_of_truth:string
 * }
 */
function app_database_source_form_input_from_post(): array
{
    return [
        'source_key' => app_post_param('source_key'),
        'label' => app_post_param('label'),
        'description' => app_post_param('description'),
        'host' => app_post_param('host'),
        'port' => app_post_param('port', '3306'),
        'database_name' => app_post_param('database_name'),
        'user_name' => app_post_param('user_name'),
        'password' => app_post_param('password'),
        'supports_live_schema_import' => app_post_param('supports_live_schema_import') === '1' ? '1' : '0',
        'supports_proxy_runtime_read' => app_post_param('supports_proxy_runtime_read') === '1' ? '1' : '0',
        'proxy_runtime_priority' => app_post_param('proxy_runtime_priority', '1000'),
        'source_of_truth' => app_post_param('source_of_truth', 'manual'),
    ];
}

/**
 * @param list<array{
 *     id:int,
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string,
 *     created_at:string,
 *     updated_at:string
 * }> $catalog
 * @param array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     database_name:string,
 *     user_name:string,
 *     password:string,
 *     supports_live_schema_import:string,
 *     supports_proxy_runtime_read:string,
 *     proxy_runtime_priority:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         source_key:string,
 *         label:string,
 *         description:string,
 *         host:string,
 *         port:string,
 *         database_name:string,
 *         user_name:string,
 *         password:string,
 *         supports_live_schema_import:string,
 *         supports_proxy_runtime_read:string,
 *         proxy_runtime_priority:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_database_source_validate_form_input(array $catalog, array $input, int $editingId = 0): array
{
    $normalized = [
        'source_key' => app_normalize_database_source_key((string) ($input['source_key'] ?? '')),
        'label' => trim((string) ($input['label'] ?? '')),
        'description' => trim((string) ($input['description'] ?? '')),
        'host' => trim((string) ($input['host'] ?? '')),
        'port' => trim((string) ($input['port'] ?? '3306')),
        'database_name' => trim((string) ($input['database_name'] ?? '')),
        'user_name' => trim((string) ($input['user_name'] ?? '')),
        'password' => (string) ($input['password'] ?? ''),
        'supports_live_schema_import' => ($input['supports_live_schema_import'] ?? '0') === '1' ? '1' : '0',
        'supports_proxy_runtime_read' => ($input['supports_proxy_runtime_read'] ?? '0') === '1' ? '1' : '0',
        'proxy_runtime_priority' => (string) max(0, (int) ($input['proxy_runtime_priority'] ?? 1000)),
        'source_of_truth' => 'manual',
    ];

    $errors = [];

    if ($normalized['source_key'] === '') {
        $errors[] = 'source key は必須です。';
    } elseif (!app_database_source_key_is_valid($normalized['source_key'])) {
        $errors[] = 'source key は lower_snake_case で入力してください。';
    } elseif (app_database_source_is_builtin_key($normalized['source_key'])) {
        $errors[] = 'built-in database source key は予約済みです。別の key を使ってください。';
    }

    foreach ($catalog as $item) {
        $itemId = (int) ($item['id'] ?? 0);
        if ($itemId === $editingId) {
            continue;
        }

        if (trim((string) ($item['source_key'] ?? '')) === $normalized['source_key']) {
            $errors[] = 'source key が重複しています。';
            break;
        }
    }

    foreach ([
        'label' => 191,
        'host' => 191,
        'port' => 16,
        'database_name' => 191,
        'user_name' => 191,
    ] as $field => $maxLength) {
        if ($normalized[$field] === '') {
            $errors[] = str_replace('_', ' ', $field) . ' は必須です。';
            continue;
        }

        if (mb_strlen($normalized[$field]) > $maxLength) {
            $errors[] = str_replace('_', ' ', $field) . ' は ' . (string) $maxLength . ' 文字以内にしてください。';
        }
    }

    if ($normalized['port'] !== '' && preg_match('/^[0-9]{1,5}$/', $normalized['port']) !== 1) {
        $errors[] = 'port は 1 から 65535 の整数で入力してください。';
    } elseif ((int) $normalized['port'] < 1 || (int) $normalized['port'] > 65535) {
        $errors[] = 'port は 1 から 65535 の範囲で入力してください。';
    }

    if (mb_strlen($normalized['description']) > 4000) {
        $errors[] = 'description は 4000 文字以内にしてください。';
    }

    if ($normalized['supports_live_schema_import'] !== '1' && $normalized['supports_proxy_runtime_read'] !== '1') {
        $errors[] = 'live schema import または proxy runtime read のどちらかは有効にしてください。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param list<array{id:int}> $items
 * @return array<int,array<string,mixed>>
 */
function app_database_source_index_by_id(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $indexed[(int) ($item['id'] ?? 0)] = $item;
    }

    return $indexed;
}

/**
 * @param array<string,mixed> $item
 */
function app_database_source_connection_summary(array $item): string
{
    $summary = trim((string) ($item['host'] ?? ''))
        . ':' . trim((string) ($item['port'] ?? ''))
        . '/' . trim((string) ($item['name'] ?? ''));
    $summary .= ' as ' . trim((string) ($item['user'] ?? ''));
    $summary .= ' / password=' . ((string) ($item['password'] ?? '') === '' ? 'blank' : 'configured');

    return $summary;
}

/**
 * @param array<string,mixed> $item
 */
function app_database_source_capability_summary(array $item): string
{
    $capabilities = [];
    if (!empty($item['supports_live_schema_import'])) {
        $capabilities[] = 'import';
    }
    if (!empty($item['supports_proxy_runtime_read'])) {
        $capabilities[] = 'proxy';
    }
    if (!empty($item['is_canonical_store'])) {
        $capabilities[] = 'canonical';
    }

    return $capabilities === [] ? 'disabled' : implode(', ', $capabilities);
}

/**
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string
 * } $request
 */
function app_render_database_sources_page(array $app, array $request): void
{
    $bootstrap = app_database_source_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $catalogResult = app_fetch_database_sources($app);
    $catalog = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $catalogById = app_database_source_index_by_id($catalog);
    $mergedCatalog = app_database_source_catalog($app);
    $builtinSources = array_values(array_filter(
        $mergedCatalog,
        static fn (array $item): bool => app_database_source_is_builtin_key((string) ($item['key'] ?? '')),
    ));

    usort(
        $builtinSources,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['key'] ?? ''),
            (string) ($right['key'] ?? ''),
        ),
    );

    $selectedSourceId = max(0, (int) app_query_param('source_id', '0'));
    $selectedSource = $catalogById[$selectedSourceId] ?? null;
    $formMode = $selectedSource === null ? 'create' : 'edit';
    $formInput = $selectedSource === null
        ? app_database_source_default_form_input()
        : app_database_source_form_input_from_item($selectedSource);
    $errors = [];
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $deleted = app_query_param('deleted') === '1';

    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }

    if (app_request_method_is($request, 'POST')) {
        $created = false;
        $updated = false;
        $deleted = false;
        $formMode = app_post_param('source_action') === 'update' ? 'edit' : 'create';
        $selectedSourceId = max(0, (int) app_post_param('source_id'));

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('source_action'));
            if ($action === 'delete') {
                if ($selectedSourceId <= 0) {
                    $errors[] = '削除対象の database source を選択してください。';
                } else {
                    $deleteResult = app_delete_database_source($app, $selectedSourceId);
                    if ($deleteResult['ok']) {
                        app_send_redirect_response($request, app_database_sources_path() . '?deleted=1');
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } else {
                $formInput = app_database_source_form_input_from_post();
                $validation = app_database_source_validate_form_input(
                    $catalog,
                    $formInput,
                    $formMode === 'edit' ? $selectedSourceId : 0,
                );
                $formInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $payload = [
                        'source_key' => $formInput['source_key'],
                        'label' => $formInput['label'],
                        'description' => $formInput['description'],
                        'host' => $formInput['host'],
                        'port' => $formInput['port'],
                        'database_name' => $formInput['database_name'],
                        'user_name' => $formInput['user_name'],
                        'password' => $formInput['password'],
                        'supports_live_schema_import' => $formInput['supports_live_schema_import'] === '1',
                        'supports_proxy_runtime_read' => $formInput['supports_proxy_runtime_read'] === '1',
                        'proxy_runtime_priority' => (int) $formInput['proxy_runtime_priority'],
                        'source_of_truth' => $formInput['source_of_truth'],
                    ];

                    if ($formMode === 'edit') {
                        if ($selectedSourceId <= 0) {
                            $errors[] = '更新対象の database source を選択してください。';
                        } else {
                            $updateResult = app_update_database_source($app, $selectedSourceId, $payload);
                            if ($updateResult['ok']) {
                                app_send_redirect_response(
                                    $request,
                                    app_database_sources_path() . '?updated=1&source_id=' . rawurlencode((string) $selectedSourceId),
                                );
                                return;
                            }

                            $errors[] = $updateResult['error'];
                        }
                    } else {
                        $createResult = app_create_database_source($app, $payload);
                        if ($createResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_database_sources_path() . '?created=1&source_id=' . rawurlencode((string) $createResult['source_id']),
                            );
                            return;
                        }

                        $errors[] = $createResult['error'];
                    }
                }
            }
        }
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Database Sources</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 90rem;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(22rem, 32rem);
            gap: 1.5rem;
            align-items: start;
        }
        .card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #ffffff;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #f0fdf4;
            border-color: #86efac;
        }
        .muted {
            color: #475569;
        }
        .form-grid {
            display: grid;
            gap: 0.75rem;
        }
        .inline-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        label {
            display: block;
            font-weight: 700;
        }
        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            margin-top: 0.35rem;
            padding: 0.65rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
            box-sizing: border-box;
        }
        textarea {
            min-height: 8rem;
            resize: vertical;
        }
        .checkbox-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }
        .checkbox-row label {
            font-weight: 400;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        button,
        .button-link {
            display: inline-block;
            border: none;
            border-radius: 999px;
            padding: 0.75rem 1.15rem;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .button-link.secondary,
        button.secondary {
            background: #475569;
        }
        button.danger {
            background: #b91c1c;
        }
        .section-spacer {
            margin-top: 1.5rem;
        }
        @media (max-width: 960px) {
            .layout {
                grid-template-columns: 1fr;
            }
            .inline-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<main>
    <p><a href="/dashboard">dashboard</a> / <code>/settings/database-sources</code></p>

    <h1>Database Sources</h1>
    <p>admin 側で external named database source を登録するページです。canonical metadata store は引き続き <code>config_db</code> で、ここで作る source は import source や lab runtime read の接続候補としてだけ使います。</p>

    <?php if ($created || $updated || $deleted): ?>
        <section class="card success-card">
            <?php if ($created): ?>
                <p>database source を作成しました。</p>
            <?php elseif ($updated): ?>
                <p>database source を更新しました。</p>
            <?php else: ?>
                <p>database source を削除しました。</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="card error-card section-spacer">
            <h2>Errors</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="layout section-spacer">
        <section>
            <section class="card">
                <h2>Built-in Catalog</h2>
                <p class="muted">`db` / `config_db` / `lab_db` は app config 側の built-in source です。ここでは read-only に見せます。</p>
                <table>
                    <thead>
                    <tr>
                        <th>Key</th>
                        <th>Role</th>
                        <th>Connection</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($builtinSources as $item): ?>
                        <tr>
                            <td>
                                <strong><code><?php echo app_h((string) ($item['key'] ?? '')); ?></code></strong><br>
                                <?php echo app_h((string) ($item['label'] ?? '')); ?><br>
                                <span class="muted"><?php echo app_h((string) ($item['source_of_truth'] ?? '')); ?></span>
                            </td>
                            <td><?php echo app_h(app_database_source_capability_summary($item)); ?></td>
                            <td><code><?php echo app_h(app_database_source_connection_summary($item)); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="card section-spacer">
                <h2>External Catalog</h2>
                <p class="muted">registered source は project table import の option に `named live schema / ...` として現れます。proxy runtime read を有効にした source は explicit `db_source_key` 指定でも使えます。</p>
                <?php if ($catalog === []): ?>
                    <p class="muted">external database source はまだありません。</p>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Key</th>
                            <th>Role</th>
                            <th>Connection</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($catalog as $item): ?>
                            <tr>
                                <td>
                                    <strong><code><?php echo app_h($item['source_key']); ?></code></strong><br>
                                    <?php echo app_h($item['label']); ?><br>
                                    <span class="muted"><?php echo app_h($item['source_of_truth']); ?></span>
                                </td>
                                <td>
                                    <code><?php echo app_h(app_database_source_capability_summary($item)); ?></code><br>
                                    <span class="muted">priority=<?php echo app_h((string) $item['proxy_runtime_priority']); ?></span>
                                </td>
                                <td>
                                    <code><?php echo app_h(app_database_source_connection_summary($item)); ?></code><br>
                                    <span class="muted"><?php echo app_h($item['description']); ?></span>
                                </td>
                                <td><a href="<?php echo app_h(app_database_sources_path() . '?source_id=' . rawurlencode((string) $item['id'])); ?>">edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </section>

        <section class="card">
            <h2><?php echo $formMode === 'edit' ? 'Edit External Source' : 'Create External Source'; ?></h2>
            <p class="muted">source key は lower_snake_case で管理します。canonical store はここでは切り替えません。</p>

            <form method="post" action="<?php echo app_h(app_database_sources_path()); ?>">
                <input type="hidden" name="_csrf" value="<?php echo app_h(app_csrf_token()); ?>">
                <input type="hidden" name="source_id" value="<?php echo app_h((string) $selectedSourceId); ?>">
                <input type="hidden" name="source_of_truth" value="<?php echo app_h($formInput['source_of_truth']); ?>">

                <div class="form-grid">
                    <label>
                        source key
                        <input type="text" name="source_key" value="<?php echo app_h($formInput['source_key']); ?>" placeholder="reporting_db">
                    </label>
                    <label>
                        label
                        <input type="text" name="label" value="<?php echo app_h($formInput['label']); ?>" placeholder="reporting db">
                    </label>
                    <label>
                        description
                        <textarea name="description" placeholder="admin-managed reporting schema import source"><?php echo app_h($formInput['description']); ?></textarea>
                    </label>
                    <div class="inline-grid">
                        <label>
                            host
                            <input type="text" name="host" value="<?php echo app_h($formInput['host']); ?>" placeholder="db-reporting">
                        </label>
                        <label>
                            port
                            <input type="text" name="port" value="<?php echo app_h($formInput['port']); ?>" placeholder="3306">
                        </label>
                    </div>
                    <div class="inline-grid">
                        <label>
                            database name
                            <input type="text" name="database_name" value="<?php echo app_h($formInput['database_name']); ?>" placeholder="reporting_app">
                        </label>
                        <label>
                            user name
                            <input type="text" name="user_name" value="<?php echo app_h($formInput['user_name']); ?>" placeholder="reporting_user">
                        </label>
                    </div>
                    <label>
                        password
                        <input type="password" name="password" value="<?php echo app_h($formInput['password']); ?>">
                    </label>
                    <label>
                        proxy runtime priority
                        <input type="text" name="proxy_runtime_priority" value="<?php echo app_h($formInput['proxy_runtime_priority']); ?>" placeholder="1000">
                    </label>
                    <div>
                        <span>capabilities</span>
                        <div class="checkbox-row">
                            <label>
                                <input type="checkbox" name="supports_live_schema_import" value="1"<?php echo $formInput['supports_live_schema_import'] === '1' ? ' checked' : ''; ?>>
                                live schema import
                            </label>
                            <label>
                                <input type="checkbox" name="supports_proxy_runtime_read" value="1"<?php echo $formInput['supports_proxy_runtime_read'] === '1' ? ' checked' : ''; ?>>
                                proxy runtime read
                            </label>
                        </div>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" name="source_action" value="<?php echo $formMode === 'edit' ? 'update' : 'create'; ?>">
                        <?php echo $formMode === 'edit' ? 'Save Changes' : 'Create Source'; ?>
                    </button>
                    <a class="button-link secondary" href="<?php echo app_h(app_database_sources_path()); ?>">New Source</a>
                    <?php if ($formMode === 'edit'): ?>
                        <button class="danger" type="submit" name="source_action" value="delete" onclick="return confirm('この database source を削除しますか？');">Delete</button>
                    <?php endif; ?>
                </div>
            </form>

            <div class="section-spacer">
                <h3>Current Rule</h3>
                <ul>
                    <li><code>config_db</code> は canonical metadata store のまま固定です</li>
                    <li><code>supports_live_schema_import</code> を有効にした source は table import option に出ます</li>
                    <li><code>supports_proxy_runtime_read</code> は published proxy relay の named source candidate に使います</li>
                </ul>
            </div>
        </section>
    </div>
</main>
</body>
</html>
    <?php
}
