<?php

declare(strict_types=1);

require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_membership_repository.php';
require_once __DIR__ . '/project_security_route_common.php';
require_once __DIR__ . '/response.php';

/**
 * @return list<array{
 *     title:string,
 *     status:string,
 *     summary:string,
 *     path:string
 * }>
 */
function app_project_security_modules(string $projectKey): array
{
    return [
        [
            'title' => 'Project Members',
            'status' => 'available',
            'summary' => 'owner / admin / member の project membership を canonical `project_memberships` として current route で更新します。',
            'path' => app_project_security_users_path($projectKey),
        ],
        [
            'title' => 'Page Security',
            'status' => 'available-partial',
            'summary' => '旧 `ProjectSecurityForEachPage` / `ProjectSecurityForEachPageDetails` を current canonical table へ保持し、project 配下の landing zone として編集できます。route policy への最終吸収は後段です。',
            'path' => app_project_security_pages_path($projectKey),
        ],
        [
            'title' => 'Host Assignments',
            'status' => 'available-partial',
            'summary' => '旧 host assignment の visible 4 列を current canonical row として編集できます。infra catalog への split は後段です。',
            'path' => app_project_host_assignments_path($projectKey),
        ],
    ];
}

function app_render_project_security_page(array $app, array $request): void
{
    $context = app_project_security_route_bootstrap($app, $request, ['GET']);
    if ($context === null) {
        return;
    }

    $projectKey = $context['project_key'];
    $project = $context['project'];
    $principal = $context['principal'];

    $membershipSummary = app_fetch_project_membership_summary($app, $projectKey);
    if (!$membershipSummary['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Security</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>membership summary の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($membershipSummary['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    $summary = $membershipSummary['item'] ?? [
        'owner' => [
            'login_id' => $project['owner_login_id'],
            'role_code' => 'owner',
            'can_administer' => true,
            'membership_row_count' => 0,
            'raw_role_codes' => ['owner'],
        ],
        'members' => [],
        'unique_user_count' => 1,
        'admin_user_count' => 1,
    ];
    $memberOnlyCount = count($summary['members']);
    $modules = app_project_security_modules($projectKey);

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Security</title>
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card,
        .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><?php echo app_h($projectKey); ?></a> / security</p>

    <h1><?php echo app_h($project['name']); ?> Security / Host Assignment</h1>
    <p>旧 `project_security*.php` と `project_host_assignment*.php` を project hub 配下へ寄せた slice です。current では project membership に加え、page security と host assignment も current canonical row として編集できます。より厳密な route policy / infra catalog への split は後段です。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project Context</h2>
            <ul>
                <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
                <li>owner: <code><?php echo app_h($summary['owner']['login_id']); ?></code></li>
                <li>members: <code><?php echo app_h((string) $summary['unique_user_count']); ?></code></li>
                <li>admins: <code><?php echo app_h((string) $summary['admin_user_count']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Current Runtime</h2>
            <ul>
                <li>site: <code><?php echo app_h($app['site']); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
                <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
                <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Current Scope</h2>
            <ul>
                <li>`security/users` は `project_memberships` を current source of truth にして更新します。</li>
                <li>旧 `ProjectUser` の 16 個の read/write bit は、まだ canonical schema へ昇格させていません。</li>
                <li>page security は normalized capability list、host assignment は visible 4 列の landing zone として current table へ保持します。</li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Why Partial</h2>
            <p class="muted">page security は最終的には current route / service policy へ、host assignment は infra catalog へ寄せたい領域です。いまは feature migration を優先し、project 配下の current landing zone で先に運用できるようにしています。</p>
        </section>
    </div>

    <section>
        <h2>Current Routes</h2>
        <table class="module-table">
            <thead>
            <tr>
                <th>module</th>
                <th>status</th>
                <th>path</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($modules as $module): ?>
                <tr>
                    <td>
                        <strong><?php echo app_h($module['title']); ?></strong><br>
                        <?php echo app_h($module['summary']); ?>
                    </td>
                    <td><span class="status-pill status-<?php echo app_h($module['status']); ?>"><?php echo app_h($module['status']); ?></span></td>
                    <td><a href="<?php echo app_h($module['path']); ?>"><code><?php echo app_h($module['path']); ?></code></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Membership Snapshot</h2>
        <?php if ($memberOnlyCount === 0): ?>
            <p class="muted">owner 以外の project member はまだ登録されていません。</p>
        <?php else: ?>
            <table class="module-table">
                <thead>
                <tr>
                    <th>login</th>
                    <th>role</th>
                    <th>raw rows</th>
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
        <p><a href="<?php echo app_h(app_project_security_users_path($projectKey)); ?>">members を編集する</a></p>
    </section>
</main>
</body>
</html>
    <?php
}
