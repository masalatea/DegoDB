<?php

declare(strict_types=1);

require_once __DIR__ . '/project_language_resource_route_common.php';

function app_render_project_language_resource_groups_page(array $app, array $request): void
{
    $context = app_project_language_resource_route_bootstrap($app, $request, ['GET']);
    if ($context === null) {
        return;
    }

    $projectKey = $context['project_key'];
    $project = $context['project'];
    $catalog = $context['reference'];
    $catalogSource = $context['catalog_source'];
    $fileLocations = is_array($context['file_locations'] ?? null) ? $context['file_locations'] : [];
    $moduleState = app_project_language_resource_module_state(
        $catalogSource,
        $catalog,
        $context['reference_error'],
    );
    $fileCatalogBacked = $catalogSource === 'file-canonical' && ((bool) ($fileLocations['exists'] ?? false));
    $manifestFilePath = (string) ($fileLocations['manifest_path'] ?? '');
    $groupFilePathsByPid = is_array($fileLocations['group_file_paths_by_pid'] ?? null)
        ? $fileLocations['group_file_paths_by_pid']
        : [];

    $warnings = app_project_language_resource_bridge_errors_from_request();
    if ($context['reference_error'] !== '') {
        $warnings[] = $context['reference_error'];
    }
    if ($fileCatalogBacked) {
        foreach ((is_array($fileLocations['warnings'] ?? null) ? $fileLocations['warnings'] : []) as $warning) {
            if (!is_string($warning) || trim($warning) === '') {
                continue;
            }

            $warnings[] = trim($warning);
        }
    }

    $groupsByPid = app_project_language_resource_groups_by_pid($catalog['groups']);
    $resourcesByGroupPid = app_project_language_resource_resources_by_group_pid($catalog['resources']);
    $languagesByPid = app_project_language_resource_languages_by_pid($catalog['languages']);
    $groupLanguagesByGroupPid = app_project_language_resource_group_languages_by_group_pid($catalog['group_languages']);
    $groupSourceOutputsByGroupPid = app_project_language_resource_group_source_outputs_by_group_pid(
        $catalog['group_source_outputs'],
    );

    $sourceOutputCatalogResult = app_project_language_resource_source_output_catalog_by_legacy_pid($app, $projectKey);
    if (!$sourceOutputCatalogResult['ok']) {
        $warnings[] = $sourceOutputCatalogResult['error'];
    }
    $currentSourceOutputsByLegacyPid = $sourceOutputCatalogResult['items'];
    $warnings = array_values(array_unique(array_filter(
        $warnings,
        static fn (string $warning): bool => trim($warning) !== '',
    )));

    $selectedGroupPid = (int) trim(app_query_param('group_pid'));
    $selectedGroup = $groupsByPid[(string) $selectedGroupPid] ?? null;
    if ($selectedGroupPid > 0 && $selectedGroup === null) {
        $warnings[] = '指定された group は見つかりません。';
        $selectedGroupPid = 0;
    }

    $visibleGroups = $selectedGroup !== null ? [$selectedGroup] : $catalog['groups'];

    app_send_html_response_headers($request, 200);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Language Resource Groups</title>
    <style>
<?php echo app_project_language_resource_page_styles(); ?>
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/projects">Projects</a> / <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>"><?php echo app_h($projectKey); ?></a> / <a href="<?php echo app_h(app_project_language_resources_path($projectKey)); ?>">Language Resources</a> / Groups</p>
    <h1>Language Resource Groups</h1>
    <p><code><?php echo app_h($projectKey); ?></code> / <?php echo app_h($project['name']); ?> の group 構成を current admin で確認します。catalog source は <code><?php echo app_h(app_project_language_resource_catalog_source_caption($catalogSource)); ?></code> です。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Counts</h2>
            <ul>
                <li>groups: <code><?php echo app_h((string) $catalog['group_count']); ?></code></li>
                <li>resources: <code><?php echo app_h((string) $catalog['resource_count']); ?></code></li>
                <li>group/source outputs: <code><?php echo app_h((string) $catalog['group_source_output_count']); ?></code></li>
            </ul>
        </section>
        <section class="summary-card">
            <h2>Module State</h2>
            <ul>
                <li>state: <code><?php echo app_h($moduleState['title']); ?></code></li>
                <li>source: <code><?php echo app_h(app_project_language_resource_catalog_source_caption($catalogSource)); ?></code></li>
                <li>mode: <code>inspector</code></li>
                <li>generated at: <code><?php echo app_h((string) ($catalog['generated_at'] ?? '')); ?></code></li>
                <li>visible groups: <code><?php echo app_h((string) count($visibleGroups)); ?></code></li>
            </ul>
            <p class="muted"><?php echo app_h($moduleState['summary']); ?></p>
        </section>
        <?php if ($fileCatalogBacked): ?>
            <section class="summary-card">
                <h2>Files</h2>
                <ul>
                    <li>root<span class="path-meta"><code><?php echo app_h((string) ($fileLocations['root_path'] ?? '')); ?></code></span></li>
                    <li>manifest<span class="path-meta"><code><?php echo app_h($manifestFilePath); ?></code></span></li>
                </ul>
                <p class="muted">group 設定も `group.json` を直接編集する前提です。</p>
            </section>
        <?php endif; ?>
    </div>

    <?php foreach ($warnings as $warning): ?>
        <section class="warning-card">
            <p><?php echo app_h($warning); ?></p>
        </section>
    <?php endforeach; ?>

    <?php if (trim((string) ($moduleState['readonly_message'] ?? '')) !== ''): ?>
        <section class="warning-card">
            <p><?php echo app_h((string) $moduleState['readonly_message']); ?></p>
        </section>
    <?php endif; ?>

    <div class="inline-actions">
        <a href="<?php echo app_h(app_project_language_resources_path($projectKey)); ?>">Resources List</a>
        <?php if ($selectedGroupPid > 0): ?>
            <a href="<?php echo app_h(app_project_language_resource_groups_path($projectKey)); ?>">Clear Selection</a>
        <?php endif; ?>
        <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>">Project Top</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>Group</th>
            <th>Resources</th>
            <th>Languages</th>
            <th>Outputs</th>
            <th>Filename Hints</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($visibleGroups as $group): ?>
            <?php
            $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0);
            $resources = $resourcesByGroupPid[(string) $legacyGroupPid] ?? [];
            $groupLanguages = $groupLanguagesByGroupPid[(string) $legacyGroupPid] ?? [];
            $groupSourceOutputs = $groupSourceOutputsByGroupPid[(string) $legacyGroupPid] ?? [];
            $groupFilePath = (string) ($groupFilePathsByPid[(string) $legacyGroupPid] ?? '');
            ?>
            <tr>
                <td>
                    <strong><?php echo app_h((string) ($group['name'] ?? '')); ?></strong><br>
                    <span class="muted">group PID: <code><?php echo app_h((string) $legacyGroupPid); ?></code></span><br>
                    <span class="muted">updated: <code><?php echo app_h((string) ($group['last_modified_dt'] ?? '')); ?></code></span><br>
                    <?php if ($groupFilePath !== ''): ?>
                        <span class="muted path-meta">json: <code><?php echo app_h($groupFilePath); ?></code></span><br>
                    <?php endif; ?>
                    <a href="<?php echo app_h(app_project_language_resource_groups_path($projectKey) . '?group_pid=' . $legacyGroupPid); ?>">View</a>
                    /
                    <a href="<?php echo app_h(app_project_language_resources_path($projectKey) . '?group_pid=' . $legacyGroupPid); ?>">Open Resources</a>
                </td>
                <td>
                    <strong><?php echo app_h((string) count($resources)); ?></strong>
                    <?php if ($resources !== []): ?>
                        <div class="muted">example: <code><?php echo app_h((string) ($resources[0]['resource_key'] ?? '')); ?></code></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="chip-list">
                        <?php foreach ($groupLanguages as $groupLanguage): ?>
                            <?php $language = $languagesByPid[(string) ($groupLanguage['legacy_language_pid'] ?? 0)] ?? null; ?>
                            <?php if ($language !== null): ?>
                                <span class="chip"><?php echo app_h((string) ($language['filename_suffix'] ?? '')); ?> / <?php echo app_h((string) ($language['caption'] ?? '')); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </td>
                <td>
                    <?php if ($groupSourceOutputs === []): ?>
                        <span class="muted">none</span>
                    <?php else: ?>
                        <div class="chip-list">
                            <?php foreach ($groupSourceOutputs as $groupSourceOutput): ?>
                                <?php
                                $legacyProjectSourceOutputPid = (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0);
                                $currentSourceOutput = $currentSourceOutputsByLegacyPid[(string) $legacyProjectSourceOutputPid] ?? null;
                                $label = $currentSourceOutput !== null
                                    ? (string) $currentSourceOutput['source_output_key']
                                    : 'legacy:' . $legacyProjectSourceOutputPid;
                                ?>
                                <span class="chip"><?php echo app_h($label); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div>php suffix: <code><?php echo app_h((string) ($group['filename_suffix_for_php'] ?? '')); ?></code></div>
                    <div>default suffix: <code><?php echo app_h((string) ($group['filename_suffix'] ?? '')); ?></code></div>
                    <div>xcode: <code><?php echo app_h((string) ($group['filename_for_xcode'] ?? '')); ?></code></div>
                    <div>fn prefix/suffix: <code><?php echo app_h((string) ($group['function_name_prefix'] ?? '')); ?></code> / <code><?php echo app_h((string) ($group['function_name_suffix'] ?? '')); ?></code></div>
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
