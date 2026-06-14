<?php

declare(strict_types=1);

require_once __DIR__ . '/project_language_resource_route_common.php';

function app_project_language_resource_matches_search(
    array $resource,
    string $query,
    array $groupsByPid,
    array $captionsByResourcePid,
    array $additionalGroupsByResourcePid,
): bool {
    $normalizedQuery = trim($query);
    if ($normalizedQuery === '') {
        return true;
    }

    $haystacks = [
        (string) ($resource['resource_key'] ?? ''),
        (string) ($resource['key_name'] ?? ''),
        (string) ($resource['key_name_for_xcode'] ?? ''),
        (string) ($resource['sort_group'] ?? ''),
        (string) (($groupsByPid[(string) ($resource['legacy_group_pid'] ?? 0)]['name'] ?? '')),
    ];

    $captionItems = $captionsByResourcePid[(string) ($resource['legacy_resource_pid'] ?? 0)] ?? [];
    foreach ($captionItems as $caption) {
        $haystacks[] = (string) ($caption['caption'] ?? '');
        $haystacks[] = (string) ($caption['caption_auto_translated'] ?? '');
    }

    $additionalGroups = $additionalGroupsByResourcePid[(string) ($resource['legacy_resource_pid'] ?? 0)] ?? [];
    foreach ($additionalGroups as $assignment) {
        $haystacks[] = (string) (($groupsByPid[(string) ($assignment['legacy_group_pid'] ?? 0)]['name'] ?? ''));
    }

    foreach ($haystacks as $haystack) {
        if ($haystack !== '' && stripos($haystack, $normalizedQuery) !== false) {
            return true;
        }
    }

    return false;
}

function app_project_language_resource_selected_group_relation(
    array $resource,
    int $selectedGroupPid,
    array $additionalGroupsByResourcePid,
): string {
    if ($selectedGroupPid <= 0) {
        return 'all';
    }

    if ((int) ($resource['legacy_group_pid'] ?? 0) === $selectedGroupPid) {
        return 'base';
    }

    foreach (($additionalGroupsByResourcePid[(string) ($resource['legacy_resource_pid'] ?? 0)] ?? []) as $assignment) {
        if ((int) ($assignment['legacy_group_pid'] ?? 0) === $selectedGroupPid) {
            return 'additional';
        }
    }

    return 'none';
}

function app_render_project_language_resources_page(array $app, array $request): void
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
    $resourceFilePathsByKey = is_array($fileLocations['resource_file_paths_by_key'] ?? null)
        ? $fileLocations['resource_file_paths_by_key']
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
    $warnings = array_values(array_unique($warnings));

    $groupsByPid = app_project_language_resource_groups_by_pid($catalog['groups']);
    $captionsByResourcePid = app_project_language_resource_captions_by_resource_pid($catalog['captions']);
    $additionalGroupsByResourcePid = app_project_language_resource_additional_groups_by_resource_pid(
        $catalog['additional_group_assignments'],
    );
    $japaneseLanguagePid = app_project_language_resource_find_language_pid_by_suffix($catalog['languages'], 'ja');
    $englishLanguagePid = app_project_language_resource_find_language_pid_by_suffix($catalog['languages'], 'en');
    if ($englishLanguagePid <= 0) {
        $englishLanguagePid = app_project_language_resource_default_language_pid($catalog['languages']);
    }

    $sourceOutputCatalogResult = app_project_language_resource_source_output_catalog_by_legacy_pid($app, $projectKey);
    if (!$sourceOutputCatalogResult['ok']) {
        $warnings[] = $sourceOutputCatalogResult['error'];
    }
    $currentSourceOutputsByLegacyPid = $sourceOutputCatalogResult['items'];
    $warnings = array_values(array_unique(array_filter(
        $warnings,
        static fn (string $warning): bool => trim($warning) !== '',
    )));

    $groupOutputLabelsByGroupPid = [];
    foreach (($catalog['group_source_outputs'] ?? []) as $groupSourceOutput) {
        if (!is_array($groupSourceOutput)) {
            continue;
        }

        $legacyGroupPid = (int) ($groupSourceOutput['legacy_group_pid'] ?? 0);
        $legacyProjectSourceOutputPid = (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0);
        if ($legacyGroupPid <= 0 || $legacyProjectSourceOutputPid <= 0) {
            continue;
        }

        $currentSourceOutput = $currentSourceOutputsByLegacyPid[(string) $legacyProjectSourceOutputPid] ?? null;
        $label = $currentSourceOutput !== null
            ? (string) $currentSourceOutput['source_output_key']
            : 'legacy:' . $legacyProjectSourceOutputPid;
        $groupOutputLabelsByGroupPid[(string) $legacyGroupPid][$label] = $label;
    }

    $selectedGroupPid = (int) trim(app_query_param('group_pid'));
    $query = trim(app_query_param('q'));
    $selectedGroup = $groupsByPid[(string) $selectedGroupPid] ?? null;
    if ($selectedGroupPid > 0 && $selectedGroup === null) {
        $warnings[] = '指定された group は見つかりません。';
        $selectedGroupPid = 0;
    }

    $filteredResources = [];
    foreach ($catalog['resources'] as $resource) {
        if (
            app_project_language_resource_selected_group_relation(
                $resource,
                $selectedGroupPid,
                $additionalGroupsByResourcePid,
            ) === 'none'
        ) {
            continue;
        }
        if (
            !app_project_language_resource_matches_search(
                $resource,
                $query,
                $groupsByPid,
                $captionsByResourcePid,
                $additionalGroupsByResourcePid,
            )
        ) {
            continue;
        }

        $filteredResources[] = $resource;
    }

    app_send_html_response_headers($request, 200);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Language Resources</title>
    <style>
<?php echo app_project_language_resource_page_styles(); ?>
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/projects">Projects</a> / <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>"><?php echo app_h($projectKey); ?></a> / Language Resources</p>
    <h1>Language Resources</h1>
    <p><code><?php echo app_h($projectKey); ?></code> / <?php echo app_h($project['name']); ?> の language resource catalog を current admin で確認します。catalog source は <code><?php echo app_h(app_project_language_resource_catalog_source_caption($catalogSource)); ?></code> です。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Catalog</h2>
            <ul>
                <li>resources: <code><?php echo app_h((string) $catalog['resource_count']); ?></code></li>
                <li>groups: <code><?php echo app_h((string) $catalog['group_count']); ?></code></li>
                <li>captions: <code><?php echo app_h((string) $catalog['caption_count']); ?></code></li>
                <li>languages: <code><?php echo app_h((string) $catalog['language_count']); ?></code></li>
            </ul>
        </section>
        <section class="summary-card">
            <h2>Relations</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_language_resource_groups_path($projectKey)); ?>">groups summary</a></li>
                <li>group/source-output assignments: <code><?php echo app_h((string) $catalog['group_source_output_count']); ?></code></li>
                <li>additional group assignments: <code><?php echo app_h((string) $catalog['additional_group_assignment_count']); ?></code></li>
            </ul>
        </section>
        <section class="summary-card">
            <h2>Module State</h2>
            <ul>
                <li>state: <code><?php echo app_h($moduleState['title']); ?></code></li>
                <li>source: <code><?php echo app_h(app_project_language_resource_catalog_source_caption($catalogSource)); ?></code></li>
                <li>mode: <code>inspector</code></li>
                <li>generated at: <code><?php echo app_h((string) ($catalog['generated_at'] ?? '')); ?></code></li>
                <li>filtered rows: <code><?php echo app_h((string) count($filteredResources)); ?></code></li>
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
                <p class="muted">Lang 更新は current admin ではなく、repo 配下の JSON file を直接編集します。</p>
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

    <form method="get" class="toolbar">
        <label>
            Search
            <input type="search" name="q" value="<?php echo app_h($query); ?>" placeholder="resource key / key name / caption">
        </label>
        <label>
            Group
            <select name="group_pid">
                <option value="">All groups</option>
                <?php foreach ($catalog['groups'] as $group): ?>
                    <?php $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0); ?>
                    <option value="<?php echo app_h((string) $legacyGroupPid); ?>"<?php echo $selectedGroupPid === $legacyGroupPid ? ' selected' : ''; ?>>
                        <?php echo app_h((string) ($group['name'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div>
            <button type="submit">Filter</button>
        </div>
    </form>

    <div class="inline-actions">
        <a href="<?php echo app_h(app_project_language_resource_groups_path($projectKey)); ?>">Groups Summary</a>
        <a href="<?php echo app_h(app_project_language_resources_path($projectKey)); ?>">Clear Filter</a>
        <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>">Project Top</a>
    </div>

    <?php if ($filteredResources === []): ?>
        <section class="note-card">
            <p>一致する language resource はありません。</p>
        </section>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Resource</th>
                <th>Group</th>
                <th>Captions</th>
                <th>Flags</th>
                <th>Outputs</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($filteredResources as $resource): ?>
                <?php
                $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
                $legacyGroupPid = (int) ($resource['legacy_group_pid'] ?? 0);
                $group = $groupsByPid[(string) $legacyGroupPid] ?? null;
                $selectedGroupRelation = app_project_language_resource_selected_group_relation(
                    $resource,
                    $selectedGroupPid,
                    $additionalGroupsByResourcePid,
                );
                $isAdditionalGroupMatch = $selectedGroupRelation === 'additional';
                $captionItems = $captionsByResourcePid[(string) $legacyResourcePid] ?? [];
                $japaneseCaption = $japaneseLanguagePid > 0
                    ? trim((string) (($captionItems[(string) $japaneseLanguagePid]['caption'] ?? '')))
                    : '';
                $englishCaption = $englishLanguagePid > 0
                    ? trim((string) (($captionItems[(string) $englishLanguagePid]['caption'] ?? '')))
                    : '';
                $resourceAdditionalGroups = $additionalGroupsByResourcePid[(string) $legacyResourcePid] ?? [];
                $additionalGroupCount = count($resourceAdditionalGroups);
                $currentSourceOutputLabels = array_values(
                    $groupOutputLabelsByGroupPid[(string) $legacyGroupPid] ?? [],
                );
                $resourceFilePath = (string) ($resourceFilePathsByKey[(string) ($resource['resource_key'] ?? '')] ?? '');
                ?>
                <tr>
                    <td>
                        <strong><a href="<?php echo app_h(app_project_language_resource_detail_path($projectKey, (string) $resource['resource_key'])); ?>"><?php echo app_h((string) $resource['resource_key']); ?></a></strong><br>
                        <span class="muted">legacy PID: <code><?php echo app_h((string) $legacyResourcePid); ?></code></span><br>
                        <span class="muted">sort: <code><?php echo app_h((string) ($resource['sort_group'] ?? '')); ?></code></span><br>
                        <?php if ((string) ($resource['key_name_for_xcode'] ?? '') !== ''): ?>
                            <span class="muted">xcode key: <code><?php echo app_h((string) $resource['key_name_for_xcode']); ?></code></span><br>
                        <?php endif; ?>
                        <?php if ($resourceFilePath !== ''): ?>
                            <span class="muted path-meta">json: <code><?php echo app_h($resourceFilePath); ?></code></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo app_h((string) ($group['name'] ?? 'unknown')); ?></strong><br>
                        <span class="muted">group PID: <code><?php echo app_h((string) $legacyGroupPid); ?></code></span>
                        <?php if ($isAdditionalGroupMatch && $selectedGroup !== null): ?>
                            <br><span class="muted">matched via additional group: <code><?php echo app_h((string) ($selectedGroup['name'] ?? ('Group ' . $selectedGroupPid))); ?></code></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($japaneseCaption !== ''): ?>
                            <div><strong>JA:</strong> <?php echo app_h(app_project_language_resource_preview_text($japaneseCaption)); ?></div>
                        <?php endif; ?>
                        <?php if ($englishCaption !== ''): ?>
                            <div><strong>EN:</strong> <?php echo app_h(app_project_language_resource_preview_text($englishCaption)); ?></div>
                        <?php endif; ?>
                        <?php if ($japaneseCaption === '' && $englishCaption === ''): ?>
                            <span class="muted">no preview</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div>fixed: <code><?php echo app_h((int) ($resource['is_resource_fixed'] ?? 0) === 1 ? 'yes' : 'no'); ?></code></div>
                        <div>default if blank: <code><?php echo app_h((int) ($resource['use_default_if_caption_is_blank'] ?? 0) === 1 ? 'yes' : 'no'); ?></code></div>
                        <div>additional groups: <code><?php echo app_h((string) $additionalGroupCount); ?></code></div>
                        <?php if ($selectedGroupPid > 0): ?>
                            <div>selected group relation: <code><?php echo app_h($isAdditionalGroupMatch ? 'additional' : 'base'); ?></code></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($currentSourceOutputLabels === []): ?>
                            <span class="muted">none</span>
                        <?php else: ?>
                            <div class="chip-list">
                                <?php foreach ($currentSourceOutputLabels as $label): ?>
                                    <span class="chip"><?php echo app_h($label); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
