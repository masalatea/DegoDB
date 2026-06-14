<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/project_page_security_repository.php';
require_once __DIR__ . '/project_security_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

/**
 * @return array{
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string
 * }
 */
function app_project_security_pages_default_form_input(): array
{
    return [
        'server_name' => '',
        'script_name' => '',
        'security_types' => [],
        'notes' => '',
    ];
}

/**
 * @return array{
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string
 * }
 */
function app_project_security_pages_form_input_from_post(): array
{
    $rawSecurityTypes = $_POST['security_types'] ?? [];
    if (!is_array($rawSecurityTypes)) {
        $rawSecurityTypes = [];
    }

    $securityTypes = [];
    foreach ($rawSecurityTypes as $value) {
        if (!is_string($value) && !is_numeric($value)) {
            continue;
        }

        $securityTypes[] = trim((string) $value);
    }

    return [
        'server_name' => trim(app_post_param('server_name')),
        'script_name' => trim(app_post_param('script_name')),
        'security_types' => $securityTypes,
        'notes' => trim(app_post_param('notes')),
    ];
}

/**
 * @param array{
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string
 * } $input
 * @return array{
 *     input:array{
 *         server_name:string,
 *         script_name:string,
 *         security_types:list<string>,
 *         notes:string
 *     },
 *     errors:list<string>
 * }
 */
function app_project_security_pages_validate_form_input(array $input): array
{
    $normalized = [
        'server_name' => trim($input['server_name']),
        'script_name' => trim($input['script_name']),
        'security_types' => [],
        'notes' => trim($input['notes']),
    ];
    $errors = [];

    if ($normalized['server_name'] === '') {
        $errors[] = 'server name は必須です。';
    } elseif (mb_strlen($normalized['server_name']) > 128) {
        $errors[] = 'server name は 128 文字以内にしてください。';
    } elseif (preg_match('/\s/u', $normalized['server_name']) === 1) {
        $errors[] = 'server name に空白は使えません。';
    }

    if ($normalized['script_name'] === '') {
        $errors[] = 'script name は必須です。';
    } elseif (mb_strlen($normalized['script_name']) > 255) {
        $errors[] = 'script name は 255 文字以内にしてください。';
    } elseif ($normalized['script_name'][0] !== '/') {
        $errors[] = 'script name は `/` から始めてください。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    $allowedSecurityTypes = app_project_security_legacy_permission_codes();
    $seen = [];
    foreach ($input['security_types'] as $securityType) {
        if ($securityType === '') {
            continue;
        }

        if (!in_array($securityType, $allowedSecurityTypes, true)) {
            $errors[] = '未対応の security type が含まれています。`' . $securityType . '`';
            continue;
        }

        if (isset($seen[$securityType])) {
            continue;
        }

        $seen[$securityType] = true;
    }

    foreach ($allowedSecurityTypes as $securityType) {
        if (isset($seen[$securityType])) {
            $normalized['security_types'][] = $securityType;
        }
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param list<array{
 *     id:int,
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string,
 *     source_of_truth:string
 * }> $items
 * @return array<int,array{
 *     id:int,
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string,
 *     source_of_truth:string
 * }>
 */
function app_project_security_pages_index_by_id(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $indexed[(int) ($item['id'] ?? 0)] = $item;
    }

    return $indexed;
}

/**
 * @param list<string> $securityTypes
 * @return list<string>
 */
function app_project_security_pages_group_captions(array $securityTypes): array
{
    $captions = [];
    foreach (app_project_security_legacy_permission_groups() as $group) {
        $selected = [];
        if (in_array($group['read_code'], $securityTypes, true)) {
            $selected[] = 'read';
        }
        if (in_array($group['write_code'], $securityTypes, true)) {
            $selected[] = 'write';
        }

        if ($selected !== []) {
            $captions[] = $group['category'] . ': ' . implode('/', $selected);
        }
    }

    return $captions;
}

function app_render_project_security_pages_page(array $app, array $request): void
{
    $context = app_project_security_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($context === null) {
        return;
    }

    $projectKey = $context['project_key'];
    $project = $context['project'];
    $permissionGroups = app_project_security_legacy_permission_groups();
    $policyResult = app_fetch_project_page_security_policies($app, $projectKey);
    if (!$policyResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Page Security</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>page security policy の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($policyResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    $policies = $policyResult['items'];
    $policiesById = app_project_security_pages_index_by_id($policies);
    $selectedPolicyId = max(0, (int) app_query_param('page_policy_id', '0'));
    $selectedPolicy = $policiesById[$selectedPolicyId] ?? null;
    $formMode = $selectedPolicy === null ? 'create' : 'edit';
    $formInput = $selectedPolicy === null
        ? app_project_security_pages_default_form_input()
        : [
            'server_name' => $selectedPolicy['server_name'],
            'script_name' => $selectedPolicy['script_name'],
            'security_types' => $selectedPolicy['security_types'],
            'notes' => $selectedPolicy['notes'],
        ];
    $errors = [];
    $updated = app_query_param('updated') === '1';
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';

    if (app_request_method_is($request, 'POST')) {
        $updated = false;
        $created = false;
        $deleted = false;
        $formMode = app_post_param('page_policy_action') === 'update' ? 'edit' : 'create';
        $selectedPolicyId = max(0, (int) app_post_param('page_policy_id'));

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $postedProjectKey = app_normalize_project_key(app_post_param('project_key'));
            if ($postedProjectKey !== $projectKey) {
                $errors[] = '更新対象の project key が route と一致しません。';
            }

            $action = app_post_param('page_policy_action');
            if ($action === 'delete') {
                if ($selectedPolicyId <= 0) {
                    $errors[] = '削除対象の page security policy を選択してください。';
                } else {
                    $deleteResult = app_delete_project_page_security_policy($app, $projectKey, $selectedPolicyId);
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_security_pages_path($projectKey) . '?deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } else {
                $formInput = app_project_security_pages_form_input_from_post();
                $validation = app_project_security_pages_validate_form_input($formInput);
                $formInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    if ($action === 'update') {
                        if ($selectedPolicyId <= 0) {
                            $errors[] = '更新対象の page security policy を選択してください。';
                        } else {
                            $saveResult = app_update_project_page_security_policy(
                                $app,
                                $projectKey,
                                $selectedPolicyId,
                                $formInput,
                            );
                            if ($saveResult['ok']) {
                                app_send_redirect_response(
                                    $request,
                                    app_project_security_pages_path($projectKey)
                                    . '?page_policy_id=' . rawurlencode((string) $selectedPolicyId)
                                    . '&updated=1',
                                );
                                return;
                            }

                            $errors[] = $saveResult['error'];
                        }
                    } else {
                        $saveResult = app_create_project_page_security_policy($app, $projectKey, $formInput);
                        if ($saveResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_project_security_pages_path($projectKey)
                                . '?page_policy_id=' . rawurlencode((string) $saveResult['policy_id'])
                                . '&created=1',
                            );
                            return;
                        }

                        $errors[] = $saveResult['error'];
                    }
                }
            }
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
    <title><?php echo app_h($app['site_name']); ?> - Page Security</title>
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
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card,
        .note-card,
        .message {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .message-success {
            background: #dcfce7;
            border-color: #86efac;
        }
        .message-error {
            background: #fee2e2;
            border-color: #fca5a5;
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
        .chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }
        .chip {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .edit-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            background: #f8fafc;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
        label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        input[type="text"],
        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.55rem 0.7rem;
            font: inherit;
            background: #ffffff;
        }
        textarea {
            min-height: 6rem;
            resize: vertical;
        }
        .permission-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }
        .permission-grid th,
        .permission-grid td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.65rem 0.75rem;
            text-align: left;
            vertical-align: middle;
        }
        .checkbox-cell label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 400;
            margin: 0;
        }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        button {
            border: 1px solid #0f172a;
            border-radius: 999px;
            padding: 0.7rem 1.2rem;
            font: inherit;
            background: #0f172a;
            color: #ffffff;
            cursor: pointer;
        }
        .button-secondary {
            background: #ffffff;
            color: #0f172a;
        }
        .button-danger {
            background: #7f1d1d;
            border-color: #7f1d1d;
        }
        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #0f172a;
            border-radius: 999px;
            padding: 0.7rem 1.2rem;
            text-decoration: none;
            color: #0f172a;
            background: #ffffff;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><?php echo app_h($projectKey); ?></a> / <a href="<?php echo app_h(app_project_security_path($projectKey)); ?>">security</a> / pages</p>

    <h1><?php echo app_h($project['name']); ?> Page Security</h1>
    <p>旧 `ProjectSecurityForEachPage` / `ProjectSecurityForEachPageDetails` を、current では project 配下の landing zone として保持します。route policy への最終吸収は後段に残しますが、Phase 1 ではこの current table を source of truth にして page security を編集できます。</p>

    <?php if ($created): ?>
        <div class="message message-success">
            <strong>created</strong><br>
            page security policy を追加しました。
        </div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="message message-success">
            <strong>updated</strong><br>
            page security policy を保存しました。
        </div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="message message-success">
            <strong>deleted</strong><br>
            page security policy を削除しました。
        </div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="message message-error">
            <strong>save failed</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Current Counts</h2>
            <ul>
                <li>page policies: <code><?php echo app_h((string) count($policies)); ?></code></li>
                <li>selected policy: <code><?php echo app_h($selectedPolicy === null ? 'new' : (string) $selectedPolicy['id']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Current Interpretation</h2>
            <ul>
                <li>旧 `SERVER_NAME + SCRIPT_NAME + SecurityType` を normalized row と capability list で保持します。</li>
                <li>project 配下の route ですが、これは global policy を current 運用へ寄せるための convenience です。</li>
                <li>後段では current route / service policy へ再投影できます。</li>
            </ul>
        </section>
    </div>

    <section>
        <h2>Current Policies</h2>
        <?php if ($policies === []): ?>
            <p class="muted">page security policy はまだありません。</p>
        <?php else: ?>
            <table class="module-table">
                <thead>
                <tr>
                    <th>server</th>
                    <th>script</th>
                    <th>capabilities</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($policies as $policy): ?>
                    <?php $captions = app_project_security_pages_group_captions($policy['security_types']); ?>
                    <tr>
                        <td><code><?php echo app_h($policy['server_name']); ?></code></td>
                        <td><code><?php echo app_h($policy['script_name']); ?></code></td>
                        <td>
                            <?php if ($captions === []): ?>
                                <span class="muted">none</span>
                            <?php else: ?>
                                <div class="chip-list">
                                    <?php foreach ($captions as $caption): ?>
                                        <span class="chip"><?php echo app_h($caption); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_project_security_pages_path($projectKey) . '?page_policy_id=' . rawurlencode((string) $policy['id'])); ?>">edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="edit-card">
        <h2><?php echo $formMode === 'edit' ? 'Edit Policy' : 'Create Policy'; ?></h2>
        <p class="muted">この slice では legacy の 16 bit を 16 列へ戻さず、capability list として保持します。</p>

        <form method="post" action="<?php echo app_h(app_project_security_pages_path($projectKey)); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="project_key" value="<?php echo app_h($projectKey); ?>">
            <input type="hidden" name="page_policy_id" value="<?php echo app_h((string) $selectedPolicyId); ?>">
            <input type="hidden" name="page_policy_action" value="<?php echo app_h($formMode === 'edit' ? 'update' : 'create'); ?>">

            <div class="form-grid">
                <div>
                    <label for="server_name">server name</label>
                    <input
                        id="server_name"
                        name="server_name"
                        type="text"
                        value="<?php echo app_h($formInput['server_name']); ?>"
                        placeholder="example.local"
                    >
                </div>

                <div>
                    <label for="script_name">script name</label>
                    <input
                        id="script_name"
                        name="script_name"
                        type="text"
                        value="<?php echo app_h($formInput['script_name']); ?>"
                        placeholder="/db/project_host_assignment.php"
                    >
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <label>capabilities</label>
                <table class="permission-grid">
                    <thead>
                    <tr>
                        <th>category</th>
                        <th>read</th>
                        <th>write</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($permissionGroups as $group): ?>
                        <tr>
                            <td><?php echo app_h($group['category']); ?></td>
                            <td class="checkbox-cell">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="security_types[]"
                                        value="<?php echo app_h($group['read_code']); ?>"
                                        <?php echo in_array($group['read_code'], $formInput['security_types'], true) ? 'checked' : ''; ?>
                                    >
                                    <code><?php echo app_h($group['read_code']); ?></code>
                                </label>
                            </td>
                            <td class="checkbox-cell">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="security_types[]"
                                        value="<?php echo app_h($group['write_code']); ?>"
                                        <?php echo in_array($group['write_code'], $formInput['security_types'], true) ? 'checked' : ''; ?>
                                    >
                                    <code><?php echo app_h($group['write_code']); ?></code>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                <label for="notes">notes</label>
                <textarea id="notes" name="notes" placeholder="migration note / route policy memo"><?php echo app_h($formInput['notes']); ?></textarea>
            </div>

            <div class="button-row">
                <button type="submit"><?php echo $formMode === 'edit' ? 'Save Policy' : 'Create Policy'; ?></button>
                <?php if ($formMode === 'edit'): ?>
                    <a class="button-link" href="<?php echo app_h(app_project_security_pages_path($projectKey)); ?>">New Policy</a>
                    <button
                        type="submit"
                        class="button-danger"
                        onclick="this.form.page_policy_action.value='delete'; return confirm('この page security policy を削除しますか。');"
                    >Delete Policy</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>
</body>
</html>
    <?php
}
