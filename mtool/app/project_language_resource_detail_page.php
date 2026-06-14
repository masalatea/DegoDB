<?php

declare(strict_types=1);

require_once __DIR__ . '/project_language_resource_route_common.php';

function app_render_project_language_resource_detail_page(array $app, array $request): void
{
    $context = app_project_language_resource_item_route_bootstrap($app, $request, ['GET']);
    if ($context === null) {
        return;
    }

    $projectKey = $context['project_key'];
    $project = $context['project'];
    $catalog = $context['reference'];
    $catalogSource = $context['catalog_source'];
    $fileLocations = is_array($context['file_locations'] ?? null) ? $context['file_locations'] : [];
    $resource = $context['resource'];
    $moduleState = app_project_language_resource_module_state(
        $catalogSource,
        $catalog,
        $context['reference_error'],
    );

    $warnings = app_project_language_resource_bridge_errors_from_request();
    if ($context['reference_error'] !== '') {
        $warnings[] = $context['reference_error'];
    }

    $groupsByPid = app_project_language_resource_groups_by_pid($catalog['groups']);
    $languagesByPid = app_project_language_resource_languages_by_pid($catalog['languages']);
    $captionsByResourcePid = app_project_language_resource_captions_by_resource_pid($catalog['captions']);
    $additionalGroupsByResourcePid = app_project_language_resource_additional_groups_by_resource_pid(
        $catalog['additional_group_assignments'],
    );
    $groupSourceOutputsByGroupPid = app_project_language_resource_group_source_outputs_by_group_pid(
        $catalog['group_source_outputs'],
    );
    $groupLanguagesByGroupPid = app_project_language_resource_group_languages_by_group_pid($catalog['group_languages']);

    $baseGroup = $groupsByPid[(string) ($resource['legacy_group_pid'] ?? 0)] ?? null;
    $captionItems = $captionsByResourcePid[(string) ($resource['legacy_resource_pid'] ?? 0)] ?? [];
    $additionalGroups = $additionalGroupsByResourcePid[(string) ($resource['legacy_resource_pid'] ?? 0)] ?? [];
    $groupSourceOutputs = $groupSourceOutputsByGroupPid[(string) ($resource['legacy_group_pid'] ?? 0)] ?? [];
    $groupLanguages = $groupLanguagesByGroupPid[(string) ($resource['legacy_group_pid'] ?? 0)] ?? [];

    $sourceOutputCatalogResult = app_project_language_resource_source_output_catalog_by_legacy_pid($app, $projectKey);
    if (!$sourceOutputCatalogResult['ok']) {
        $warnings[] = $sourceOutputCatalogResult['error'];
    }
    $currentSourceOutputsByLegacyPid = $sourceOutputCatalogResult['items'];

    $fileCatalogBacked = $catalogSource === 'file-canonical' && ((bool) ($fileLocations['exists'] ?? false));
    $manifestFilePath = (string) ($fileLocations['manifest_path'] ?? '');
    $groupFilePathsByPid = is_array($fileLocations['group_file_paths_by_pid'] ?? null)
        ? $fileLocations['group_file_paths_by_pid']
        : [];
    $resourceFilePathsByKey = is_array($fileLocations['resource_file_paths_by_key'] ?? null)
        ? $fileLocations['resource_file_paths_by_key']
        : [];
    $baseGroupFilePath = (string) ($groupFilePathsByPid[(string) ($resource['legacy_group_pid'] ?? 0)] ?? '');
    $resourceFilePath = (string) ($resourceFilePathsByKey[(string) ($resource['resource_key'] ?? '')] ?? '');
    if ($fileCatalogBacked) {
        foreach ((is_array($fileLocations['warnings'] ?? null) ? $fileLocations['warnings'] : []) as $warning) {
            if (!is_string($warning) || trim($warning) === '') {
                continue;
            }

            $warnings[] = trim($warning);
        }
    }
    $warnings = array_values(array_unique(array_filter(
        $warnings,
        static fn (string $warning): bool => trim($warning) !== '',
    )));

    app_send_html_response_headers($request, 200);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Language Resource Detail</title>
    <style>
<?php echo app_project_language_resource_page_styles(); ?>
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/projects">Projects</a> / <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>"><?php echo app_h($projectKey); ?></a> / <a href="<?php echo app_h(app_project_language_resources_path($projectKey)); ?>">Language Resources</a> / <code><?php echo app_h((string) $resource['resource_key']); ?></code></p>
    <h1>Language Resource Detail</h1>
    <p><code><?php echo app_h($projectKey); ?></code> / <?php echo app_h($project['name']); ?> の resource を current admin で確認します。catalog source は <code><?php echo app_h(app_project_language_resource_catalog_source_caption($catalogSource)); ?></code> です。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Identity</h2>
            <ul>
                <li>resource key: <code><?php echo app_h((string) $resource['resource_key']); ?></code></li>
                <li>legacy PID: <code><?php echo app_h((string) ($resource['legacy_resource_pid'] ?? 0)); ?></code></li>
                <li>key name: <code><?php echo app_h((string) ($resource['key_name'] ?? '')); ?></code></li>
                <li>xcode key: <code><?php echo app_h((string) ($resource['key_name_for_xcode'] ?? '')); ?></code></li>
            </ul>
        </section>
        <section class="summary-card">
            <h2>Behavior</h2>
            <ul>
                <li>sort group: <code><?php echo app_h((string) ($resource['sort_group'] ?? '')); ?></code></li>
                <li>fixed: <code><?php echo app_h((int) ($resource['is_resource_fixed'] ?? 0) === 1 ? 'yes' : 'no'); ?></code></li>
                <li>use default if blank: <code><?php echo app_h((int) ($resource['use_default_if_caption_is_blank'] ?? 0) === 1 ? 'yes' : 'no'); ?></code></li>
                <li>uwp target property: <code><?php echo app_h((string) ($resource['uwp_target_property'] ?? '')); ?></code></li>
            </ul>
        </section>
        <section class="summary-card">
            <h2>Relations</h2>
            <ul>
                <li>base group: <code><?php echo app_h((string) ($baseGroup['name'] ?? 'unknown')); ?></code></li>
                <li>captions: <code><?php echo app_h((string) count($captionItems)); ?></code></li>
                <li>additional groups: <code><?php echo app_h((string) count($additionalGroups)); ?></code></li>
                <li>group outputs: <code><?php echo app_h((string) count($groupSourceOutputs)); ?></code></li>
            </ul>
        </section>
        <section class="summary-card">
            <h2>Module State</h2>
            <ul>
                <li>state: <code><?php echo app_h($moduleState['title']); ?></code></li>
                <li>source: <code><?php echo app_h(app_project_language_resource_catalog_source_caption($catalogSource)); ?></code></li>
                <li>mode: <code>inspector</code></li>
                <li>generated at: <code><?php echo app_h((string) ($catalog['generated_at'] ?? '')); ?></code></li>
            </ul>
            <p class="muted"><?php echo app_h($moduleState['summary']); ?></p>
        </section>
        <?php if ($fileCatalogBacked): ?>
            <section class="summary-card">
                <h2>Files</h2>
                <ul>
                    <li>root<span class="path-meta"><code><?php echo app_h((string) ($fileLocations['root_path'] ?? '')); ?></code></span></li>
                    <li>manifest<span class="path-meta"><code><?php echo app_h($manifestFilePath); ?></code></span></li>
                    <li>group.json<span class="path-meta"><code><?php echo app_h($baseGroupFilePath); ?></code></span></li>
                    <li>resource.json<span class="path-meta"><code><?php echo app_h($resourceFilePath); ?></code></span></li>
                </ul>
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

    <?php if ($fileCatalogBacked && $resourceFilePath !== ''): ?>
        <section class="note-card">
            <h2>Edit JSON</h2>
            <p class="muted">この resource の変更は current admin ではなく、次の JSON file を直接編集します。</p>
            <p class="path-meta"><code><?php echo app_h($resourceFilePath); ?></code></p>
        </section>
    <?php endif; ?>

    <div class="inline-actions">
        <a href="<?php echo app_h(app_project_language_resources_path($projectKey) . '?group_pid=' . (int) ($resource['legacy_group_pid'] ?? 0)); ?>">Back to Group Filter</a>
        <a href="<?php echo app_h(app_project_language_resource_groups_path($projectKey) . '?group_pid=' . (int) ($resource['legacy_group_pid'] ?? 0)); ?>">View Group</a>
        <a href="<?php echo app_h(app_project_language_resource_groups_path($projectKey)); ?>">Groups Summary</a>
        <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>">Project Top</a>
    </div>

    <?php if ($baseGroup !== null): ?>
        <section class="note-card" style="margin-top: 1.5rem;">
            <h2>Base Group</h2>
            <ul>
                <li>name: <code><?php echo app_h((string) ($baseGroup['name'] ?? '')); ?></code></li>
                <li>legacy group pid: <code><?php echo app_h((string) ($baseGroup['legacy_group_pid'] ?? 0)); ?></code></li>
                <li>php suffix: <code><?php echo app_h((string) ($baseGroup['filename_suffix_for_php'] ?? '')); ?></code></li>
                <li>default suffix: <code><?php echo app_h((string) ($baseGroup['filename_suffix'] ?? '')); ?></code></li>
                <li>xcode file: <code><?php echo app_h((string) ($baseGroup['filename_for_xcode'] ?? '')); ?></code></li>
            </ul>
            <?php if ($baseGroupFilePath !== ''): ?>
                <p class="path-meta"><code><?php echo app_h($baseGroupFilePath); ?></code></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section style="margin-top: 1.5rem;">
        <h2>Group Languages</h2>
        <?php if ($groupLanguages === []): ?>
            <p class="muted">group language binding はありません。</p>
        <?php else: ?>
            <div class="chip-list">
                <?php foreach ($groupLanguages as $groupLanguage): ?>
                    <?php $language = $languagesByPid[(string) ($groupLanguage['legacy_language_pid'] ?? 0)] ?? null; ?>
                    <?php if ($language !== null): ?>
                        <span class="chip"><?php echo app_h((string) ($language['filename_suffix'] ?? '')); ?> / <?php echo app_h((string) ($language['caption'] ?? '')); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section style="margin-top: 1.5rem;">
        <h2>Additional Group Snapshot</h2>
        <?php if ($additionalGroups === []): ?>
            <p class="muted">No additional group assignment.</p>
        <?php else: ?>
            <div class="chip-list">
                <?php foreach ($additionalGroups as $assignment): ?>
                    <?php $group = $groupsByPid[(string) ($assignment['legacy_group_pid'] ?? 0)] ?? null; ?>
                    <span class="chip"><?php echo app_h((string) ($group['name'] ?? ('legacy:' . (string) ($assignment['legacy_group_pid'] ?? 0)))); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section style="margin-top: 1.5rem;">
        <h2>Group Output Targets</h2>
        <?php if ($groupSourceOutputs === []): ?>
            <p class="muted">No target source output assignment.</p>
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
    </section>

    <section style="margin-top: 1.5rem;">
        <h2>Captions Snapshot</h2>
        <?php if ($captionItems === []): ?>
            <p class="muted">caption row はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Language</th>
                    <th>Caption</th>
                    <th>Auto Translated</th>
                </tr>
                </thead>
                <tbody>
                <?php ksort($captionItems, SORT_NATURAL); ?>
                <?php foreach ($captionItems as $caption): ?>
                    <?php $language = $languagesByPid[(string) ($caption['legacy_language_pid'] ?? 0)] ?? null; ?>
                    <tr>
                        <td>
                            <strong><?php echo app_h((string) ($language['caption'] ?? 'Unknown')); ?></strong><br>
                            <span class="muted">suffix: <code><?php echo app_h((string) ($language['filename_suffix'] ?? '')); ?></code></span>
                        </td>
                        <td class="caption-cell"><?php echo app_h((string) ($caption['caption'] ?? '')); ?></td>
                        <td class="caption-cell"><?php echo app_h((string) ($caption['caption_auto_translated'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
    <?php
}
