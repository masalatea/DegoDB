<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_membership_repository.php';
require_once __DIR__ . '/project_security_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

/**
 * @param list<array{
 *     login_id:string,
 *     role_code:string
 * }> $members
 * @return list<array{
 *     login_id:string,
 *     role_code:string
 * }>
 */
function app_project_security_users_form_rows(array $members): array
{
    $rows = [];
    foreach ($members as $member) {
        $rows[] = [
            'login_id' => (string) ($member['login_id'] ?? ''),
            'role_code' => ((string) ($member['role_code'] ?? '')) === 'admin' ? 'admin' : 'member',
        ];
    }

    $blankCount = max(4, count($rows) === 0 ? 4 : 2);
    for ($i = 0; $i < $blankCount; $i++) {
        $rows[] = [
            'login_id' => '',
            'role_code' => 'member',
        ];
    }

    return $rows;
}

/**
 * @return list<array{
 *     login_id:string,
 *     role_code:string
 * }>
 */
function app_project_security_users_rows_from_post(): array
{
    $loginIds = $_POST['member_login_id'] ?? [];
    $roleCodes = $_POST['member_role_code'] ?? [];
    if (!is_array($loginIds)) {
        $loginIds = [];
    }
    if (!is_array($roleCodes)) {
        $roleCodes = [];
    }

    $rows = [];
    $rowCount = max(count($loginIds), count($roleCodes));
    for ($i = 0; $i < $rowCount; $i++) {
        $loginId = $loginIds[$i] ?? '';
        $roleCode = $roleCodes[$i] ?? 'member';
        $rows[] = [
            'login_id' => is_string($loginId) || is_numeric($loginId) ? trim((string) $loginId) : '',
            'role_code' => is_string($roleCode) || is_numeric($roleCode) ? trim((string) $roleCode) : 'member',
        ];
    }

    return $rows;
}

/**
 * @param list<array{
 *     login_id:string,
 *     role_code:string
 * }> $rows
 * @return array{
 *     members:list<array{
 *         login_id:string,
 *         role_code:string
 *     }>,
 *     errors:list<string>
 * }
 */
function app_project_security_users_validate_rows(array $rows, string $ownerLoginId): array
{
    $errors = [];
    $members = [];

    foreach ($rows as $index => $row) {
        $loginId = trim((string) ($row['login_id'] ?? ''));
        $roleCode = trim((string) ($row['role_code'] ?? 'member'));

        if ($loginId === '') {
            continue;
        }

        if (strlen($loginId) > 128) {
            $errors[] = 'login_id は 128 文字以内で入力してください。row ' . (string) ($index + 1) . '。';
            continue;
        }

        if (preg_match('/\s/u', $loginId) === 1) {
            $errors[] = 'login_id に空白は使えません。row ' . (string) ($index + 1) . '。';
            continue;
        }

        if ($loginId === $ownerLoginId) {
            $errors[] = 'owner は members 編集からは変更できません。`' . $loginId . '` は owner row です。';
            continue;
        }

        if ($roleCode !== 'admin' && $roleCode !== 'member') {
            $errors[] = 'role は `admin` または `member` を指定してください。row ' . (string) ($index + 1) . '。';
            continue;
        }

        if (isset($members[$loginId])) {
            $errors[] = '同じ login_id が複数回入力されています。`' . $loginId . '` を 1 回にしてください。';
            continue;
        }

        $members[$loginId] = [
            'login_id' => $loginId,
            'role_code' => $roleCode,
        ];
    }

    ksort($members);

    return [
        'members' => array_values($members),
        'errors' => $errors,
    ];
}

function app_render_project_security_users_page(array $app, array $request): void
{
    $context = app_project_security_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($context === null) {
        return;
    }

    $projectKey = $context['project_key'];
    $project = $context['project'];

    $membershipSummary = app_fetch_project_membership_summary($app, $projectKey);
    if (!$membershipSummary['ok'] || $membershipSummary['item'] === null) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Members</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>project membership の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($membershipSummary['error'] ?? 'project membership not found'); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    $summary = $membershipSummary['item'];
    $owner = $summary['owner'];
    $rows = app_project_security_users_form_rows($summary['members']);
    $errors = [];
    $updated = app_query_param('updated') === '1';

    if (app_request_method_is($request, 'POST')) {
        $updated = false;
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $postedProjectKey = app_normalize_project_key(app_post_param('project_key'));
            if ($postedProjectKey !== $projectKey) {
                $errors[] = '更新対象の project key が route と一致しません。';
            }

            $rows = app_project_security_users_rows_from_post();
            $validation = app_project_security_users_validate_rows($rows, $owner['login_id']);
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $saveResult = app_replace_project_memberships($app, $projectKey, $validation['members']);
                if ($saveResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_project_security_users_path($projectKey) . '?updated=1',
                    );
                    return;
                }

                $errors[] = $saveResult['error'];
            }

            $rows = app_project_security_users_form_rows($errors === [] ? $validation['members'] : $rows);
        }
    }

    $anomalyMembers = array_values(
        array_filter(
            $summary['members'],
            static fn (array $member): bool => ((int) ($member['membership_row_count'] ?? 0)) > 1,
        ),
    );
    $csrfToken = app_csrf_token();
    $statusCode = $errors === [] ? 200 : 422;

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Members</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 74rem;
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
            background: #fefce8;
            border-color: #facc15;
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
        input[type="text"],
        select {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.55rem 0.7rem;
            font: inherit;
            background: #ffffff;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><?php echo app_h($projectKey); ?></a> / <a href="<?php echo app_h(app_project_security_path($projectKey)); ?>">security</a> / users</p>

    <h1><?php echo app_h($project['name']); ?> Project Members</h1>
    <p>`project_memberships` を current source of truth にした first slice です。ここでは project 単位の `owner / admin / member` だけを更新し、旧 `ProjectUser` の read/write bit 群はまだ扱いません。</p>

    <?php if ($updated): ?>
        <div class="message message-success">
            <strong>updated</strong><br>
            project membership を保存しました。
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
            <h2>Owner</h2>
            <ul>
                <li>login: <code><?php echo app_h($owner['login_id']); ?></code></li>
                <li>role: <code>owner</code></li>
                <li>can administer: <code>yes</code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Current Counts</h2>
            <ul>
                <li>total users: <code><?php echo app_h((string) $summary['unique_user_count']); ?></code></li>
                <li>members except owner: <code><?php echo app_h((string) count($summary['members'])); ?></code></li>
                <li>admins including owner: <code><?php echo app_h((string) $summary['admin_user_count']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Out Of Scope</h2>
            <ul>
                <li>旧 `ChatRead` / `dbtoolWrite` など 16 bit の feature flag。</li>
                <li>page 単位の `SERVER_NAME` / `SCRIPT_NAME` policy。</li>
                <li>host assignment と infra setting 連携。</li>
            </ul>
        </section>
    </div>

    <?php if ($anomalyMembers !== []): ?>
        <div class="message note-card">
            <strong>normalization note</strong><br>
            この project には複数 row を持つ membership がありました。保存すると current first slice のルールに従い、1 login = 1 row へ正規化されます。
        </div>
    <?php endif; ?>

    <section>
        <h2>Current Members</h2>
        <?php if ($summary['members'] === []): ?>
            <p class="muted">owner 以外の member はまだ登録されていません。</p>
        <?php else: ?>
            <table class="module-table">
                <thead>
                <tr>
                    <th>login</th>
                    <th>role</th>
                    <th>raw roles</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($summary['members'] as $member): ?>
                    <tr>
                        <td><code><?php echo app_h($member['login_id']); ?></code></td>
                        <td><code><?php echo app_h($member['role_code']); ?></code></td>
                        <td><code><?php echo app_h(implode(', ', $member['raw_role_codes'])); ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Edit Members</h2>
        <p class="muted">blank row は無視されます。既存 member を消したい場合は login を空にして保存してください。owner row はこの画面では変更できません。</p>
        <form method="post" action="<?php echo app_h(app_project_security_users_path($projectKey)); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="project_key" value="<?php echo app_h($projectKey); ?>">

            <table class="module-table">
                <thead>
                <tr>
                    <th>login id</th>
                    <th>role</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <tr>
                        <td>
                            <input
                                type="text"
                                name="member_login_id[]"
                                value="<?php echo app_h($row['login_id']); ?>"
                                autocomplete="off"
                                spellcheck="false"
                                aria-label="member login id row <?php echo app_h((string) ($index + 1)); ?>"
                            >
                        </td>
                        <td>
                            <select name="member_role_code[]" aria-label="member role row <?php echo app_h((string) ($index + 1)); ?>">
                                <option value="member"<?php echo $row['role_code'] === 'member' ? ' selected' : ''; ?>>member</option>
                                <option value="admin"<?php echo $row['role_code'] === 'admin' ? ' selected' : ''; ?>>admin</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <p><button type="submit">Save Memberships</button></p>
        </form>
    </section>
</main>
</body>
</html>
    <?php
}
