<?php

declare(strict_types=1);

/**
 * Side-effect-free contract for the AI/Mtool workspace layout.
 *
 * This file intentionally does not create directories, scan projects, or write
 * manifests. It only describes and resolves what Mtool would use after an
 * explicit workspace choice.
 *
 * @return array<string,mixed>
 */
function app_ai_workspace_contract(): array
{
    return [
        'contract_version' => 'mtool-ai-workspace-contract-v0',
        'profiles' => [
            'project-local' => [
                'default_workspace' => '<project_root>/mtool-workspace',
                'primary_use' => 'normal user workflows',
            ],
            'mtool-work' => [
                'default_workspace' => '<mtool_home>/work/<project-key>',
                'primary_use' => 'Mtool development and sample verification',
            ],
            'external' => [
                'default_workspace' => '<explicit workspace root>',
                'primary_use' => 'advanced user or organization policy',
            ],
        ],
        'fixed_directories' => [
            'mtool_project' => 'mtool-project',
            'mtool_project_config' => 'mtool-project/config',
            'mtool_project_db' => 'mtool-project/db',
            'mtool_project_metadata' => 'mtool-project/metadata',
            'mtool_project_output' => 'mtool-project/output',
            'mtool_project_runtime' => 'mtool-project/runtime',
        ],
        'standard_roles' => [
            'inputs' => [
                'standard_path' => 'inputs',
                'owner' => 'ai-user',
                'git_policy' => 'user-decision',
            ],
            'scans' => [
                'standard_path' => 'scans',
                'owner' => 'mtool-cache',
                'git_policy' => 'usually-ignore',
            ],
            'design_briefs' => [
                'standard_path' => 'design-briefs',
                'owner' => 'ai-user',
                'git_policy' => 'commit-when-useful',
            ],
            'task_packets' => [
                'standard_path' => 'task-packets',
                'owner' => 'ai-user',
                'git_policy' => 'commit-when-useful',
            ],
            'proposals' => [
                'standard_path' => 'proposals',
                'owner' => 'ai-user',
                'git_policy' => 'commit-reviewed',
            ],
            'review_artifacts' => [
                'standard_path' => 'review-artifacts',
                'owner' => 'ai-user',
                'git_policy' => 'commit-compact-reviewed',
            ],
            'generated' => [
                'standard_path' => 'generated',
                'owner' => 'mtool-generated',
                'git_policy' => 'usually-ignore-unless-promoted',
            ],
            'validation' => [
                'standard_path' => 'validation',
                'owner' => 'ai-user',
                'git_policy' => 'commit-compact-evidence',
            ],
            'logs' => [
                'standard_path' => 'logs',
                'owner' => 'local-runtime',
                'git_policy' => 'ignore',
            ],
        ],
        'manifests' => [
            'workspace' => 'mtool-project/config/workspace.json',
            'git_policy' => 'mtool-project/config/git-policy.json',
            'resolver' => 'mtool-project/config/resolver.json',
            'role_mapping' => 'mtool-project/config/role-mapping.json',
            'user_settings' => 'mtool-project/config/user-settings.json',
            'validation_summary' => 'validation/summary.json',
        ],
        'editable_mtool_config_files' => [
            'mtool-project/config/git-policy.json',
            'mtool-project/config/role-mapping.json',
            'mtool-project/config/user-settings.json',
        ],
        'copy_plan_artifacts' => [
            'markdown_pattern' => 'proposals/copy-plan-<slug>.md',
            'json_pattern' => 'proposals/copy-plan-<slug>.json',
        ],
        'read_only_guard_message' => 'Refusing direct AI write to Mtool-owned path. Write a proposal or task packet instead, or use an explicit Mtool command that owns this state.',
    ];
}

/**
 * Resolve a workspace layout without touching the filesystem.
 *
 * @param array<string,mixed> $input
 * @return array<string,mixed>
 */
function app_ai_workspace_resolve(array $input): array
{
    $contract = app_ai_workspace_contract();
    $mtoolHome = app_ai_workspace_trim_path((string) ($input['mtool_home'] ?? ''));
    $projectRoot = app_ai_workspace_trim_path((string) ($input['project_root'] ?? ''));
    $projectKey = app_ai_workspace_project_key((string) ($input['project_key'] ?? ''), $projectRoot);
    $explicitProfile = (string) ($input['profile'] ?? '');
    $cliWorkspaceRoot = app_ai_workspace_trim_path((string) ($input['workspace_root_cli'] ?? ''));
    $envWorkspaceRoot = app_ai_workspace_trim_path((string) ($input['workspace_root_env'] ?? ''));
    $errors = [];
    $warnings = [];
    $ignoredSources = [];

    $allowedProfiles = array_keys($contract['profiles']);
    if ($explicitProfile !== '' && !in_array($explicitProfile, $allowedProfiles, true)) {
        $errors[] = 'unsupported profile: ' . $explicitProfile;
    }
    foreach ([
        'mtool_home' => $mtoolHome,
        'project_root' => $projectRoot,
        'workspace_root_cli' => $cliWorkspaceRoot,
        'workspace_root_env' => $envWorkspaceRoot,
    ] as $pathLabel => $pathValue) {
        if ($pathValue !== '' && !app_ai_workspace_is_absolute_path($pathValue)) {
            $warnings[] = $pathLabel . ' is not absolute; resolve it before filesystem writes';
        }
    }

    $workspaceRoot = '';
    $profile = '';
    $decisionSource = '';

    if ($cliWorkspaceRoot !== '') {
        $workspaceRoot = $cliWorkspaceRoot;
        $profile = 'external';
        $decisionSource = 'cli.workspace_root';
        if ($envWorkspaceRoot !== '') {
            $ignoredSources[] = 'env.workspace_root';
        }
        if ($explicitProfile !== '') {
            $ignoredSources[] = 'profile.' . $explicitProfile;
        }
    } elseif ($envWorkspaceRoot !== '') {
        $workspaceRoot = $envWorkspaceRoot;
        $profile = 'external';
        $decisionSource = 'env.workspace_root';
        if ($explicitProfile !== '') {
            $ignoredSources[] = 'profile.' . $explicitProfile;
        }
    } elseif ($explicitProfile === 'mtool-work') {
        if ($mtoolHome === '') {
            $errors[] = 'mtool_home is required for profile mtool-work';
            $workspaceRoot = '';
        } else {
            $workspaceRoot = app_ai_workspace_join($mtoolHome, 'work', $projectKey);
        }
        $profile = 'mtool-work';
        $decisionSource = 'profile.mtool-work';
    } elseif ($explicitProfile === 'project-local') {
        if ($projectRoot === '') {
            $errors[] = 'project_root is required for profile project-local';
            $workspaceRoot = '';
        } else {
            $workspaceRoot = app_ai_workspace_join($projectRoot, 'mtool-workspace');
        }
        $profile = 'project-local';
        $decisionSource = 'profile.project-local';
    } elseif ($explicitProfile === 'external') {
        $errors[] = 'workspace_root_cli or workspace_root_env is required for profile external';
        $profile = 'external';
        $decisionSource = 'profile.external';
    } elseif ($projectRoot !== '') {
        $workspaceRoot = app_ai_workspace_join($projectRoot, 'mtool-workspace');
        $profile = 'project-local';
        $decisionSource = 'default.project-local';
    } elseif ($mtoolHome !== '') {
        $workspaceRoot = app_ai_workspace_join($mtoolHome, 'work', $projectKey);
        $profile = 'mtool-work';
        $decisionSource = 'fallback.mtool-work';
        $warnings[] = 'project_root is missing; using mtool-work fallback';
    } else {
        $errors[] = 'workspace_root_cli, workspace_root_env, project_root, or mtool_home is required';
    }

    $mtoolProjectRoot = $workspaceRoot === '' ? '' : app_ai_workspace_join($workspaceRoot, 'mtool-project');
    $roleMappingInput = app_ai_workspace_normalize_requested_role_mappings(
        is_array($input['role_mappings'] ?? null) ? $input['role_mappings'] : [],
        $contract,
    );
    foreach ($roleMappingInput['warnings'] as $warning) {
        $warnings[] = $warning;
    }
    foreach ($roleMappingInput['errors'] as $error) {
        $errors[] = $error;
    }
    $roleMappings = app_ai_workspace_role_mappings($roleMappingInput['mappings'], $contract);

    $directories = app_ai_workspace_directories($workspaceRoot, $contract, $roleMappings);
    $manifests = app_ai_workspace_manifest_paths($workspaceRoot, $contract);

    return [
        'ok' => $errors === [],
        'contract_version' => $contract['contract_version'],
        'profile' => $profile,
        'workspace_root' => $workspaceRoot,
        'mtool_project_root' => $mtoolProjectRoot,
        'project_key' => $projectKey,
        'decision_source' => $decisionSource,
        'ignored_sources' => $ignoredSources,
        'warnings' => $warnings,
        'errors' => $errors,
        'directories' => $directories,
        'manifests' => $manifests,
        'role_mappings' => $roleMappings,
        'diagnostics' => [
            'side_effect_free' => true,
            'filesystem_writes' => false,
            'scan_started' => false,
            'mtool_owned_root' => $mtoolProjectRoot,
            'read_only_guard_message' => $contract['read_only_guard_message'],
        ],
    ];
}

/**
 * @param array<string,mixed> $requestedMappings
 * @param array<string,mixed> $contract
 * @return array<string,array<string,mixed>>
 */
function app_ai_workspace_role_mappings(array $requestedMappings, array $contract): array
{
    $roles = is_array($contract['standard_roles'] ?? null) ? $contract['standard_roles'] : [];
    $resolved = [];
    foreach ($roles as $role => $definition) {
        if (!is_array($definition)) {
            continue;
        }
        $requested = is_array($requestedMappings[$role] ?? null) ? $requestedMappings[$role] : [];
        $standardPath = (string) ($definition['standard_path'] ?? $role);
        $mappedPath = array_key_exists('mapped_path', $requested)
            ? app_ai_workspace_nullable_path($requested['mapped_path'])
            : $standardPath;
        $disabled = (bool) ($requested['disabled'] ?? false);
        if ($disabled) {
            $mappedPath = null;
        }

        $resolved[$role] = [
            'role' => $role,
            'standard_path' => $standardPath,
            'mapped_path' => $mappedPath,
            'disabled' => $disabled,
            'owner' => (string) ($requested['owner'] ?? $definition['owner'] ?? 'ai-user'),
            'git_policy' => (string) ($requested['git_policy'] ?? $definition['git_policy'] ?? 'user-decision'),
            'external' => is_string($mappedPath) && app_ai_workspace_is_absolute_path($mappedPath),
        ];
    }

    return $resolved;
}

/**
 * @param array<string,mixed> $requestedMappings
 * @param array<string,mixed> $contract
 * @return array{mappings:array<string,mixed>,warnings:list<string>,errors:list<string>}
 */
function app_ai_workspace_normalize_requested_role_mappings(array $requestedMappings, array $contract): array
{
    $roles = is_array($contract['standard_roles'] ?? null) ? $contract['standard_roles'] : [];
    $aliases = [];
    foreach ($roles as $role => $definition) {
        $roleKey = (string) $role;
        $aliases[$roleKey] = $roleKey;
        $aliases[app_ai_workspace_role_key($roleKey)] = $roleKey;
        if (is_array($definition) && is_string($definition['standard_path'] ?? null)) {
            $aliases[app_ai_workspace_role_key($definition['standard_path'])] = $roleKey;
        }
    }

    $normalized = [];
    $warnings = [];
    $errors = [];
    foreach ($requestedMappings as $rawRole => $mapping) {
        $canonicalRole = $aliases[app_ai_workspace_role_key((string) $rawRole)] ?? null;
        if ($canonicalRole === null) {
            $warnings[] = 'unknown role mapping ignored: ' . (string) $rawRole;
            continue;
        }
        if (is_array($mapping) && array_key_exists('mapped_path', $mapping)) {
            $mappedPath = app_ai_workspace_nullable_path($mapping['mapped_path']);
            if (is_string($mappedPath) && !app_ai_workspace_is_absolute_path($mappedPath)) {
                $cleanPath = app_ai_workspace_clean_relative_path($mappedPath);
                if ($cleanPath === null) {
                    $errors[] = 'role mapping path must not contain parent traversal: ' . (string) $rawRole;
                    $mapping['mapped_path'] = null;
                    $mapping['disabled'] = true;
                } else {
                    $mapping['mapped_path'] = $cleanPath;
                }
            }
        }
        $normalized[$canonicalRole] = $mapping;
    }

    return [
        'mappings' => $normalized,
        'warnings' => $warnings,
        'errors' => $errors,
    ];
}

/**
 * @param array<string,mixed> $resolution
 * @return array<string,mixed>
 */
function app_ai_workspace_evaluate_write_request(array $resolution, string $targetPath): array
{
    $contract = app_ai_workspace_contract();
    $workspaceRoot = app_ai_workspace_trim_path((string) ($resolution['workspace_root'] ?? ''));
    $mtoolProjectRoot = app_ai_workspace_trim_path((string) ($resolution['mtool_project_root'] ?? ''));
    $normalizedTarget = app_ai_workspace_trim_path($targetPath);
    if ($workspaceRoot !== '' && $normalizedTarget !== '' && !app_ai_workspace_is_absolute_path($normalizedTarget)) {
        $normalizedTarget = app_ai_workspace_join($workspaceRoot, $normalizedTarget);
    }
    $normalizedTarget = app_ai_workspace_normalize_path_lexically($normalizedTarget);
    $relative = app_ai_workspace_relative_to_workspace($workspaceRoot, $normalizedTarget);

    $allowedConfigFiles = is_array($contract['editable_mtool_config_files'] ?? null)
        ? $contract['editable_mtool_config_files']
        : [];

    if ($relative !== null && in_array($relative, $allowedConfigFiles, true)) {
        return [
            'allowed' => true,
            'reason' => 'explicit_editable_mtool_config',
            'relative_path' => $relative,
            'message' => '',
        ];
    }

    if ($mtoolProjectRoot !== '' && app_ai_workspace_path_is_same_or_child($normalizedTarget, $mtoolProjectRoot)) {
        return [
            'allowed' => false,
            'reason' => 'mtool_owned_read_only',
            'relative_path' => $relative,
            'message' => $contract['read_only_guard_message'],
        ];
    }

    return [
        'allowed' => true,
        'reason' => 'outside_mtool_owned_area',
        'relative_path' => $relative,
        'message' => '',
    ];
}

/**
 * @return array{markdown:string,json:string,slug:string}
 */
function app_ai_workspace_copy_plan_artifact_paths(string $slug): array
{
    $safeSlug = app_ai_workspace_slug($slug);
    return [
        'slug' => $safeSlug,
        'markdown' => 'proposals/copy-plan-' . $safeSlug . '.md',
        'json' => 'proposals/copy-plan-' . $safeSlug . '.json',
    ];
}

/**
 * Build a reviewable onboarding prompt artifact from a resolver result.
 *
 * This is intentionally side-effect-free: it does not create directories, write
 * manifests, start scans, or mutate a user project. The artifact is meant for
 * AI/user confirmation before a later explicit workspace initialization step.
 *
 * @param array<string,mixed> $resolution
 * @return array<string,mixed>
 */
function app_ai_workspace_onboarding_prompt_artifact(array $resolution): array
{
    $ok = (bool) ($resolution['ok'] ?? false);
    $profile = (string) ($resolution['profile'] ?? '');
    $workspaceRoot = (string) ($resolution['workspace_root'] ?? '');
    $mtoolProjectRoot = (string) ($resolution['mtool_project_root'] ?? '');
    $warnings = app_ai_workspace_string_list($resolution['warnings'] ?? []);
    $errors = app_ai_workspace_string_list($resolution['errors'] ?? []);
    $directories = is_array($resolution['directories'] ?? null) ? $resolution['directories'] : [];
    $roleMappings = is_array($resolution['role_mappings'] ?? null) ? $resolution['role_mappings'] : [];
    $manifests = is_array($resolution['manifests'] ?? null) ? $resolution['manifests'] : [];
    $diagnostics = is_array($resolution['diagnostics'] ?? null) ? $resolution['diagnostics'] : [];

    $metadata = [
        'artifact_version' => 'mtool-ai-workspace-onboarding-prompt-v0',
        'contract_version' => (string) ($resolution['contract_version'] ?? ''),
        'ok' => $ok,
        'approval_required' => true,
        'can_initialize' => $ok && $warnings === [],
        'requires_warning_resolution' => $warnings !== [],
        'side_effect_free' => true,
        'filesystem_writes' => false,
        'scan_started' => false,
        'profile' => $profile,
        'workspace_root' => $workspaceRoot,
        'mtool_project_root' => $mtoolProjectRoot,
        'project_key' => (string) ($resolution['project_key'] ?? ''),
        'decision_source' => (string) ($resolution['decision_source'] ?? ''),
        'ignored_sources' => app_ai_workspace_string_list($resolution['ignored_sources'] ?? []),
        'warnings' => $warnings,
        'errors' => $errors,
        'directories' => $directories,
        'manifests' => $manifests,
        'role_mappings' => $roleMappings,
        'read_only_guard_message' => (string) ($diagnostics['read_only_guard_message'] ?? app_ai_workspace_contract()['read_only_guard_message']),
    ];

    return [
        'artifact_version' => $metadata['artifact_version'],
        'ok' => $ok,
        'prompt_text' => app_ai_workspace_onboarding_prompt_text($metadata),
        'metadata' => $metadata,
    ];
}

/**
 * @param array<string,mixed> $metadata
 */
function app_ai_workspace_onboarding_prompt_text(array $metadata): string
{
    $lines = [];
    $lines[] = 'Mtool workspace onboarding confirmation';
    $lines[] = '';

    if (!($metadata['ok'] ?? false)) {
        $lines[] = 'I cannot initialize a workspace yet because the resolver found errors.';
        $lines[] = '';
        $lines[] = 'Errors:';
        foreach (app_ai_workspace_string_list($metadata['errors'] ?? []) as $error) {
            $lines[] = '- ' . $error;
        }
        $warnings = app_ai_workspace_string_list($metadata['warnings'] ?? []);
        if ($warnings !== []) {
            $lines[] = '';
            $lines[] = 'Warnings:';
            foreach ($warnings as $warning) {
                $lines[] = '- ' . $warning;
            }
        }
        $lines[] = '';
        $lines[] = 'No directories were created, no manifests were written, and no scan was started.';
        $lines[] = 'Please adjust the workspace input and run the resolver again.';
        return implode("\n", $lines);
    }

    $lines[] = 'Mtool will use this side-effect-free workspace plan if you approve it.';
    $lines[] = '';
    $lines[] = 'Selected profile: ' . (string) ($metadata['profile'] ?? '');
    $lines[] = 'Decision source: ' . (string) ($metadata['decision_source'] ?? '');
    $lines[] = 'Workspace root: ' . (string) ($metadata['workspace_root'] ?? '');
    $lines[] = 'Mtool-owned project root: ' . (string) ($metadata['mtool_project_root'] ?? '');
    $lines[] = '';
    $lines[] = 'Ownership boundary:';
    $lines[] = '- `mtool-project/` is Mtool-owned.';
    $lines[] = '- AI/user agents may read Mtool-owned artifacts, but should not edit them directly.';
    $lines[] = '- Requested changes should be written as design briefs, task packets, or proposals.';
    $lines[] = '- Explicitly editable config files are limited to documented files under `mtool-project/config/`.';
    $lines[] = '';

    $warnings = app_ai_workspace_string_list($metadata['warnings'] ?? []);
    if ($warnings !== []) {
        $lines[] = 'Warnings to resolve before filesystem writes:';
        foreach ($warnings as $warning) {
            $lines[] = '- ' . $warning;
        }
        $lines[] = '';
        $lines[] = 'Please resolve these warnings or explicitly accept them before workspace initialization.';
        $lines[] = '';
    }

    $roleSummaries = app_ai_workspace_onboarding_role_summary(
        is_array($metadata['role_mappings'] ?? null) ? $metadata['role_mappings'] : [],
    );
    if ($roleSummaries !== []) {
        $lines[] = 'Workspace role mapping:';
        foreach ($roleSummaries as $summary) {
            $lines[] = '- ' . $summary;
        }
        $lines[] = '';
    }

    $directorySummary = app_ai_workspace_onboarding_directory_summary(
        is_array($metadata['directories'] ?? null) ? $metadata['directories'] : [],
    );
    if ($directorySummary !== []) {
        $lines[] = 'Directories that would be used or created later:';
        foreach ($directorySummary as $summary) {
            $lines[] = '- ' . $summary;
        }
        $lines[] = '';
    }

    $lines[] = 'No directories have been created, no manifests have been written, and no scan has been started.';
    if (($metadata['can_initialize'] ?? false) === true) {
        $lines[] = 'Do you want to approve this workspace plan and continue to explicit initialization?';
    } else {
        $lines[] = 'Do you want to keep this workspace plan for review before resolving the warnings?';
    }

    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $roleMappings
 * @return list<string>
 */
function app_ai_workspace_onboarding_role_summary(array $roleMappings): array
{
    $summaries = [];
    foreach ($roleMappings as $role => $mapping) {
        if (!is_array($mapping)) {
            continue;
        }
        $label = (string) ($mapping['standard_path'] ?? $role);
        if (($mapping['disabled'] ?? false) === true) {
            $summaries[] = $label . ': disabled';
            continue;
        }
        $mappedPath = $mapping['mapped_path'] ?? null;
        if (!is_string($mappedPath) || $mappedPath === '') {
            $summaries[] = $label . ': not mapped';
            continue;
        }
        $suffix = ($mapping['external'] ?? false) === true ? ' (external)' : '';
        $summaries[] = $label . ': ' . $mappedPath . $suffix;
    }
    return $summaries;
}

/**
 * @param array<string,mixed> $directories
 * @return list<string>
 */
function app_ai_workspace_onboarding_directory_summary(array $directories): array
{
    $priorityKeys = [
        'mtool_project',
        'mtool_project_config',
        'role.inputs',
        'role.design_briefs',
        'role.task_packets',
        'role.proposals',
        'role.review_artifacts',
        'role.validation',
        'role.logs',
    ];
    $summaries = [];
    foreach ($priorityKeys as $key) {
        if (is_string($directories[$key] ?? null) && $directories[$key] !== '') {
            $summaries[] = $key . ': ' . $directories[$key];
        }
    }
    return $summaries;
}

/**
 * Build a side-effect-free explicit initialization preflight plan.
 *
 * This function defines what a later initialization command may create/write,
 * but it does not touch the filesystem.
 *
 * @param array<string,mixed> $onboardingArtifact
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_ai_workspace_initialization_preflight(array $onboardingArtifact, array $options = []): array
{
    $metadata = is_array($onboardingArtifact['metadata'] ?? null) ? $onboardingArtifact['metadata'] : [];
    $mode = (string) ($options['mode'] ?? 'dry-run');
    $approved = (bool) ($options['approved'] ?? false);
    $acceptedWarnings = (bool) ($options['accepted_warnings'] ?? false);
    $existingPaths = app_ai_workspace_existing_path_map($options['existing_paths'] ?? []);
    $errors = [];
    $warnings = [];

    if (!in_array($mode, ['dry-run', 'apply'], true)) {
        $errors[] = 'unsupported initialization mode: ' . $mode;
    }
    if (($metadata['ok'] ?? false) !== true) {
        $errors[] = 'onboarding artifact is not valid for initialization';
    }
    if (app_ai_workspace_string_list($metadata['warnings'] ?? []) !== [] && !$acceptedWarnings) {
        $errors[] = 'workspace warnings must be resolved or explicitly accepted before initialization';
    }
    if ($mode === 'apply' && !$approved) {
        $errors[] = 'explicit approval is required for initialization apply mode';
    }

    $directoryPlan = app_ai_workspace_initialization_directory_plan($metadata, $existingPaths);
    foreach ($directoryPlan['errors'] as $error) {
        $errors[] = $error;
    }

    $manifestPlan = app_ai_workspace_initialization_manifest_plan($metadata, $existingPaths);
    foreach ($manifestPlan['warnings'] as $warning) {
        $warnings[] = $warning;
    }

    $gitignoreSuggestion = app_ai_workspace_initialization_gitignore_suggestion($metadata);
    $ok = $errors === [];

    return [
        'artifact_version' => 'mtool-ai-workspace-initialization-preflight-v0',
        'ok' => $ok,
        'mode' => $mode,
        'approved' => $approved,
        'accepted_warnings' => $acceptedWarnings,
        'can_apply' => $ok && $mode === 'apply' && $approved,
        'side_effect_free' => true,
        'filesystem_writes' => false,
        'scan_started' => false,
        'workspace_root' => (string) ($metadata['workspace_root'] ?? ''),
        'mtool_project_root' => (string) ($metadata['mtool_project_root'] ?? ''),
        'errors' => $errors,
        'warnings' => $warnings,
        'create_directories' => $directoryPlan['create_directories'],
        'reuse_directories' => $directoryPlan['reuse_directories'],
        'skip_directories' => $directoryPlan['skip_directories'],
        'write_manifests' => $manifestPlan['write_manifests'],
        'skip_manifests' => $manifestPlan['skip_manifests'],
        'gitignore_suggestion' => $gitignoreSuggestion,
        'no_overwrite' => true,
    ];
}

/**
 * Apply an explicitly approved workspace initialization preflight.
 *
 * This is the first filesystem-writing slice for the AI/Mtool workspace
 * contract. It only creates directories and writes missing manifest files that
 * were listed by a successful apply-mode preflight. Existing files are never
 * overwritten, even if they appeared after preflight.
 *
 * @param array<string,mixed> $preflight
 * @return array<string,mixed>
 */
function app_ai_workspace_initialization_apply(array $preflight): array
{
    $errors = [];
    $warnings = [];
    $createdDirectories = [];
    $reusedDirectories = [];
    $skippedDirectories = is_array($preflight['skip_directories'] ?? null) ? $preflight['skip_directories'] : [];
    $writtenManifests = [];
    $skippedManifests = is_array($preflight['skip_manifests'] ?? null) ? $preflight['skip_manifests'] : [];

    if (($preflight['can_apply'] ?? false) !== true || ($preflight['mode'] ?? '') !== 'apply') {
        $errors[] = 'successful approved apply-mode preflight is required before initialization apply';
    }
    if (($preflight['no_overwrite'] ?? false) !== true) {
        $errors[] = 'initialization apply requires no_overwrite preflight policy';
    }
    if ($errors !== []) {
        return [
            'artifact_version' => 'mtool-ai-workspace-initialization-apply-v0',
            'ok' => false,
            'filesystem_writes' => false,
            'scan_started' => false,
            'workspace_root' => (string) ($preflight['workspace_root'] ?? ''),
            'errors' => $errors,
            'warnings' => $warnings,
            'created_directories' => $createdDirectories,
            'reused_directories' => $reusedDirectories,
            'skipped_directories' => $skippedDirectories,
            'written_manifests' => $writtenManifests,
            'skipped_manifests' => $skippedManifests,
            'no_overwrite' => true,
        ];
    }

    $directoryEntries = is_array($preflight['create_directories'] ?? null) ? $preflight['create_directories'] : [];
    foreach ($directoryEntries as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $key = (string) ($entry['key'] ?? '');
        $path = app_ai_workspace_trim_path((string) ($entry['path'] ?? ''));
        if ($path === '') {
            $errors[] = 'directory path is empty for key: ' . $key;
            continue;
        }
        if (is_dir($path)) {
            $reusedDirectories[] = [
                'key' => $key,
                'path' => $path,
                'reason' => 'already_exists_at_apply',
            ];
            continue;
        }
        if (file_exists($path)) {
            $errors[] = 'directory path already exists as file at apply time: ' . $path;
            continue;
        }
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            $errors[] = 'failed to create directory: ' . $path;
            continue;
        }
        $createdDirectories[] = [
            'key' => $key,
            'path' => $path,
            'reason' => 'created',
        ];
    }

    $manifestEntries = is_array($preflight['write_manifests'] ?? null) ? $preflight['write_manifests'] : [];
    foreach ($manifestEntries as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $key = (string) ($entry['key'] ?? '');
        $path = app_ai_workspace_trim_path((string) ($entry['path'] ?? ''));
        if ($path === '') {
            $errors[] = 'manifest path is empty for key: ' . $key;
            continue;
        }
        if (file_exists($path)) {
            $skippedManifests[] = [
                'key' => $key,
                'path' => $path,
                'reason' => is_dir($path) ? 'no_overwrite_existing_dir_at_apply' : 'no_overwrite_existing_file_at_apply',
            ];
            $warnings[] = 'manifest already exists and was not overwritten at apply time: ' . $path;
            continue;
        }
        $parent = dirname($path);
        if (!is_dir($parent)) {
            if (file_exists($parent)) {
                $errors[] = 'manifest parent path already exists as file at apply time: ' . $parent;
                continue;
            }
            if (!mkdir($parent, 0775, true) && !is_dir($parent)) {
                $errors[] = 'failed to create manifest parent directory: ' . $parent;
                continue;
            }
            $createdDirectories[] = [
                'key' => 'manifest_parent.' . $key,
                'path' => $parent,
                'reason' => 'created',
            ];
        }
        $encoded = json_encode($entry['content'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($encoded)) {
            $errors[] = 'failed to encode manifest content: ' . $path;
            continue;
        }
        if (file_put_contents($path, $encoded . "\n", LOCK_EX) === false) {
            $errors[] = 'failed to write manifest: ' . $path;
            continue;
        }
        $writtenManifests[] = [
            'key' => $key,
            'path' => $path,
            'reason' => 'written',
        ];
    }

    return [
        'artifact_version' => 'mtool-ai-workspace-initialization-apply-v0',
        'ok' => $errors === [],
        'filesystem_writes' => $createdDirectories !== [] || $writtenManifests !== [],
        'scan_started' => false,
        'workspace_root' => (string) ($preflight['workspace_root'] ?? ''),
        'errors' => $errors,
        'warnings' => $warnings,
        'created_directories' => $createdDirectories,
        'reused_directories' => $reusedDirectories,
        'skipped_directories' => $skippedDirectories,
        'written_manifests' => $writtenManifests,
        'skipped_manifests' => $skippedManifests,
        'no_overwrite' => true,
    ];
}

/**
 * Build a side-effect-free preflight for the future CLI entry point.
 *
 * This function does not create the CLI wrapper and does not touch the
 * filesystem. It only defines how argv/env input should compose the resolver,
 * onboarding prompt artifact, and initialization preflight.
 *
 * @param list<string> $argv
 * @param array<string,string> $env
 * @return array<string,mixed>
 */
function app_ai_workspace_initialization_cli_entry_preflight(array $argv, array $env = []): array
{
    $parsed = app_ai_workspace_initialization_cli_parse_args($argv);
    $errors = app_ai_workspace_string_list($parsed['errors'] ?? []);
    $helpRequested = (bool) ($parsed['help_requested'] ?? false);
    $resolution = [];
    $onboardingArtifact = [];
    $initializationPreflight = [];

    if ($errors === [] && !$helpRequested) {
        $options = is_array($parsed['options'] ?? null) ? $parsed['options'] : [];
        $resolution = app_ai_workspace_resolve([
            'mtool_home' => (string) ($options['mtool_home'] ?? ''),
            'project_root' => (string) ($options['project_root'] ?? ''),
            'project_key' => (string) ($options['project_key'] ?? ''),
            'profile' => (string) ($options['profile'] ?? ''),
            'workspace_root_cli' => (string) ($options['workspace_root'] ?? ''),
            'workspace_root_env' => (string) ($env['MTOOL_AI_WORKSPACE_ROOT'] ?? ''),
            'role_mappings' => is_array($options['role_mappings'] ?? null) ? $options['role_mappings'] : [],
        ]);
        $onboardingArtifact = app_ai_workspace_onboarding_prompt_artifact($resolution);
        $initializationPreflight = app_ai_workspace_initialization_preflight($onboardingArtifact, [
            'mode' => (string) ($options['mode'] ?? 'dry-run'),
            'approved' => (bool) ($options['approved'] ?? false),
            'accepted_warnings' => (bool) ($options['accepted_warnings'] ?? false),
            'existing_paths' => is_array($options['existing_paths'] ?? null) ? $options['existing_paths'] : [],
        ]);
        foreach (app_ai_workspace_string_list($initializationPreflight['errors'] ?? []) as $error) {
            $errors[] = $error;
        }
    }

    return [
        'artifact_version' => 'mtool-ai-workspace-initialization-cli-entry-preflight-v0',
        'ok' => $errors === [],
        'command_name' => 'mtool/scripts/init_ai_workspace.php',
        'usage' => app_ai_workspace_initialization_cli_usage(),
        'help_requested' => $helpRequested,
        'parsed' => $parsed,
        'input_precedence' => [
            'workspace_root' => ['cli.--workspace-root', 'env.MTOOL_AI_WORKSPACE_ROOT', 'profile/default'],
            'mode' => ['cli.--mode', 'default.dry-run'],
            'approval' => ['cli.--approve only'],
            'warning_acceptance' => ['cli.--accept-warnings only'],
        ],
        'resolution' => $resolution,
        'onboarding_artifact' => $onboardingArtifact,
        'initialization_preflight' => $initializationPreflight,
        'can_run_apply' => (bool) ($initializationPreflight['can_apply'] ?? false),
        'filesystem_writes' => false,
        'scan_started' => false,
        'errors' => $errors,
    ];
}

function app_ai_workspace_initialization_cli_usage(): string
{
    return implode("\n", [
        'usage: php mtool/scripts/init_ai_workspace.php [options]',
        '',
        'Options:',
        '  --project-root=PATH          User project root. Defaults to project-local workspace when no workspace root is provided.',
        '  --mtool-home=PATH            Mtool repository/home path for mtool-work profile.',
        '  --project-key=KEY            Stable key for mtool-work profile.',
        '  --profile=NAME               project-local, mtool-work, or external.',
        '  --workspace-root=PATH        Explicit workspace root. Wins over env.MTOOL_AI_WORKSPACE_ROOT.',
        '  --mode=dry-run|apply         Defaults to dry-run.',
        '  --approve                    Required with --mode=apply.',
        '  --accept-warnings            Allows initialization when resolver warnings are accepted.',
        '  --role=ROLE=PATH             Map a standard role to a workspace-relative or absolute path.',
        '  --external-role=ROLE=PATH    Map a standard role to an external absolute path.',
        '  --disable-role=ROLE          Disable a standard role.',
        '  --existing-path=PATH:TYPE    Side-effect-free existing path hint for preflight tests; TYPE is file, dir, or unknown.',
        '  --json                       Request machine-readable JSON output from the future CLI wrapper.',
        '  --help                       Show usage.',
    ]);
}

/**
 * @param list<string> $argv
 * @return array<string,mixed>
 */
function app_ai_workspace_initialization_cli_parse_args(array $argv): array
{
    $options = [
        'mode' => 'dry-run',
        'approved' => false,
        'accepted_warnings' => false,
        'json' => false,
        'role_mappings' => [],
        'existing_paths' => [],
    ];
    $errors = [];
    $helpRequested = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $helpRequested = true;
            continue;
        }
        if ($argument === '--approve') {
            $options['approved'] = true;
            continue;
        }
        if ($argument === '--accept-warnings') {
            $options['accepted_warnings'] = true;
            continue;
        }
        if ($argument === '--json') {
            $options['json'] = true;
            continue;
        }
        if (str_starts_with($argument, '--project-root=')) {
            $options['project_root'] = substr($argument, strlen('--project-root='));
            continue;
        }
        if (str_starts_with($argument, '--mtool-home=')) {
            $options['mtool_home'] = substr($argument, strlen('--mtool-home='));
            continue;
        }
        if (str_starts_with($argument, '--project-key=')) {
            $options['project_key'] = substr($argument, strlen('--project-key='));
            continue;
        }
        if (str_starts_with($argument, '--profile=')) {
            $options['profile'] = substr($argument, strlen('--profile='));
            continue;
        }
        if (str_starts_with($argument, '--workspace-root=')) {
            $options['workspace_root'] = substr($argument, strlen('--workspace-root='));
            continue;
        }
        if (str_starts_with($argument, '--mode=')) {
            $options['mode'] = substr($argument, strlen('--mode='));
            continue;
        }
        if (str_starts_with($argument, '--role=')) {
            app_ai_workspace_initialization_cli_parse_role_mapping(substr($argument, strlen('--role=')), false, false, $options, $errors);
            continue;
        }
        if (str_starts_with($argument, '--external-role=')) {
            app_ai_workspace_initialization_cli_parse_role_mapping(substr($argument, strlen('--external-role=')), true, false, $options, $errors);
            continue;
        }
        if (str_starts_with($argument, '--disable-role=')) {
            app_ai_workspace_initialization_cli_parse_role_mapping(substr($argument, strlen('--disable-role=')), false, true, $options, $errors);
            continue;
        }
        if (str_starts_with($argument, '--existing-path=')) {
            app_ai_workspace_initialization_cli_parse_existing_path(substr($argument, strlen('--existing-path=')), $options, $errors);
            continue;
        }
        $errors[] = 'unsupported option: ' . $argument;
    }

    return [
        'ok' => $errors === [],
        'help_requested' => $helpRequested,
        'options' => $options,
        'errors' => $errors,
    ];
}

/**
 * @param array<string,mixed> $options
 * @param list<string> $errors
 */
function app_ai_workspace_initialization_cli_parse_role_mapping(
    string $spec,
    bool $external,
    bool $disabled,
    array &$options,
    array &$errors,
): void {
    if ($disabled) {
        $role = trim($spec);
        if ($role === '') {
            $errors[] = 'role mapping requires a role name';
            return;
        }
        $options['role_mappings'][$role] = ['disabled' => true];
        return;
    }
    $parts = explode('=', $spec, 2);
    if (count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '') {
        $errors[] = 'role mapping must use ROLE=PATH';
        return;
    }
    $options['role_mappings'][trim($parts[0])] = [
        'mapped_path' => trim($parts[1]),
        'owner' => $external ? 'external' : 'ai-user',
    ];
}

/**
 * @param array<string,mixed> $options
 * @param list<string> $errors
 */
function app_ai_workspace_initialization_cli_parse_existing_path(
    string $spec,
    array &$options,
    array &$errors,
): void {
    $separator = strrpos($spec, ':');
    if ($separator === false || $separator === 0 || $separator === strlen($spec) - 1) {
        $errors[] = 'existing path hint must use PATH:TYPE';
        return;
    }
    $path = substr($spec, 0, $separator);
    $type = substr($spec, $separator + 1);
    if (!in_array($type, ['file', 'dir', 'unknown'], true)) {
        $errors[] = 'existing path hint type must be file, dir, or unknown';
        return;
    }
    $options['existing_paths'][] = [
        'path' => $path,
        'type' => $type,
    ];
}

/**
 * @param array<string,mixed> $metadata
 * @param array<string,string> $existingPaths
 * @return array{create_directories:list<array<string,string>>,reuse_directories:list<array<string,string>>,skip_directories:list<array<string,string>>,errors:list<string>}
 */
function app_ai_workspace_initialization_directory_plan(array $metadata, array $existingPaths): array
{
    $directories = is_array($metadata['directories'] ?? null) ? $metadata['directories'] : [];
    $roleMappings = is_array($metadata['role_mappings'] ?? null) ? $metadata['role_mappings'] : [];
    $create = [];
    $reuse = [];
    $skip = [];
    $errors = [];

    foreach ($directories as $key => $path) {
        if (!is_string($path) || $path === '') {
            continue;
        }
        $reason = app_ai_workspace_initialization_directory_skip_reason((string) $key, $roleMappings);
        if ($reason !== '') {
            $skip[] = [
                'key' => (string) $key,
                'path' => $path,
                'reason' => $reason,
            ];
            continue;
        }
        $existingType = $existingPaths[$path] ?? '';
        if ($existingType === 'file') {
            $errors[] = 'directory path already exists as file: ' . $path;
            continue;
        }
        if ($existingType === 'dir') {
            $reuse[] = [
                'key' => (string) $key,
                'path' => $path,
                'reason' => 'already_exists',
            ];
            continue;
        }
        $create[] = [
            'key' => (string) $key,
            'path' => $path,
            'reason' => 'missing',
        ];
    }

    return [
        'create_directories' => $create,
        'reuse_directories' => $reuse,
        'skip_directories' => $skip,
        'errors' => $errors,
    ];
}

/**
 * @param array<string,array<string,mixed>> $roleMappings
 */
function app_ai_workspace_initialization_directory_skip_reason(string $key, array $roleMappings): string
{
    if (!str_starts_with($key, 'role.')) {
        return '';
    }
    $role = substr($key, strlen('role.'));
    $mapping = is_array($roleMappings[$role] ?? null) ? $roleMappings[$role] : [];
    if (($mapping['disabled'] ?? false) === true) {
        return 'role_disabled';
    }
    if (($mapping['external'] ?? false) === true) {
        return 'external_role';
    }
    return '';
}

/**
 * @param array<string,mixed> $metadata
 * @param array<string,string> $existingPaths
 * @return array{write_manifests:list<array<string,mixed>>,skip_manifests:list<array<string,string>>,warnings:list<string>}
 */
function app_ai_workspace_initialization_manifest_plan(array $metadata, array $existingPaths): array
{
    $manifests = is_array($metadata['manifests'] ?? null) ? $metadata['manifests'] : [];
    $write = [];
    $skip = [];
    $warnings = [];
    $manifestKeys = ['workspace', 'resolver', 'role_mapping', 'git_policy', 'user_settings'];
    foreach ($manifestKeys as $key) {
        $path = is_string($manifests[$key] ?? null) ? $manifests[$key] : '';
        if ($path === '') {
            continue;
        }
        $existingType = $existingPaths[$path] ?? '';
        if ($existingType !== '') {
            $skip[] = [
                'key' => $key,
                'path' => $path,
                'reason' => 'no_overwrite_existing_' . $existingType,
            ];
            $warnings[] = 'manifest already exists and will not be overwritten: ' . $path;
            continue;
        }
        $write[] = [
            'key' => $key,
            'path' => $path,
            'content' => app_ai_workspace_initialization_manifest_content($key, $metadata),
        ];
    }

    return [
        'write_manifests' => $write,
        'skip_manifests' => $skip,
        'warnings' => $warnings,
    ];
}

/**
 * @param array<string,mixed> $metadata
 * @return array<string,mixed>
 */
function app_ai_workspace_initialization_manifest_content(string $key, array $metadata): array
{
    if ($key === 'workspace') {
        return [
            'manifest_version' => 'mtool-workspace-v0',
            'profile' => (string) ($metadata['profile'] ?? ''),
            'workspace_root' => (string) ($metadata['workspace_root'] ?? ''),
            'mtool_project_root' => (string) ($metadata['mtool_project_root'] ?? ''),
            'project_key' => (string) ($metadata['project_key'] ?? ''),
        ];
    }
    if ($key === 'resolver') {
        return [
            'manifest_version' => 'mtool-workspace-resolver-v0',
            'decision_source' => (string) ($metadata['decision_source'] ?? ''),
            'ignored_sources' => app_ai_workspace_string_list($metadata['ignored_sources'] ?? []),
            'warnings' => app_ai_workspace_string_list($metadata['warnings'] ?? []),
            'errors' => app_ai_workspace_string_list($metadata['errors'] ?? []),
        ];
    }
    if ($key === 'role_mapping') {
        return [
            'manifest_version' => 'mtool-workspace-role-mapping-v0',
            'roles' => is_array($metadata['role_mappings'] ?? null) ? $metadata['role_mappings'] : [],
        ];
    }
    if ($key === 'git_policy') {
        return [
            'manifest_version' => 'mtool-workspace-git-policy-v0',
            'no_overwrite' => true,
            'gitignore_suggestion_only' => true,
        ];
    }
    if ($key === 'user_settings') {
        return [
            'manifest_version' => 'mtool-workspace-user-settings-v0',
            'settings' => [],
        ];
    }
    return [
        'manifest_version' => 'mtool-workspace-unknown-v0',
    ];
}

/**
 * @param array<string,mixed> $metadata
 */
function app_ai_workspace_initialization_gitignore_suggestion(array $metadata): string
{
    $workspaceRoot = (string) ($metadata['workspace_root'] ?? '');
    $profile = (string) ($metadata['profile'] ?? '');
    $lines = [
        '# Suggested Mtool workspace ignores. Review before applying.',
    ];
    if ($profile === 'project-local') {
        $lines[] = 'mtool-workspace/scans/';
        $lines[] = 'mtool-workspace/generated/';
        $lines[] = 'mtool-workspace/logs/';
        $lines[] = 'mtool-workspace/mtool-project/db/';
        $lines[] = 'mtool-workspace/mtool-project/runtime/';
    } elseif ($profile === 'mtool-work') {
        $lines[] = 'work/';
    } elseif ($workspaceRoot !== '') {
        $lines[] = '# External workspace selected: ' . $workspaceRoot;
        $lines[] = '# Add ignore rules in the repository that owns that path, if needed.';
    }
    return implode("\n", $lines);
}

/**
 * @param mixed $value
 * @return array<string,string>
 */
function app_ai_workspace_existing_path_map(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }
    $map = [];
    foreach ($value as $key => $item) {
        if (is_string($key)) {
            $type = is_string($item) ? $item : 'unknown';
            $map[app_ai_workspace_trim_path($key)] = $type;
            continue;
        }
        if (is_array($item) && is_string($item['path'] ?? null)) {
            $map[app_ai_workspace_trim_path($item['path'])] = is_string($item['type'] ?? null) ? $item['type'] : 'unknown';
        } elseif (is_string($item)) {
            $map[app_ai_workspace_trim_path($item)] = 'unknown';
        }
    }
    return $map;
}

function app_ai_workspace_trim_path(string $path): string
{
    $trimmed = str_replace('\\', '/', trim($path));
    if ($trimmed === '/') {
        return '/';
    }
    if (preg_match('/^[A-Za-z]:\/$/', $trimmed) === 1) {
        return $trimmed;
    }
    return rtrim($trimmed, '/');
}

function app_ai_workspace_nullable_path(mixed $path): ?string
{
    if ($path === null) {
        return null;
    }
    $trimmed = trim((string) $path);
    return $trimmed === '' ? null : app_ai_workspace_trim_path($trimmed);
}

function app_ai_workspace_join(string ...$parts): string
{
    $joined = '';
    foreach ($parts as $index => $part) {
        $trimmed = $index === 0 ? app_ai_workspace_trim_path($part) : trim(str_replace('\\', '/', $part), '/');
        if ($trimmed === '') {
            continue;
        }
        if ($joined === '') {
            $joined = $trimmed;
        } elseif ($joined === '/' || preg_match('/^[A-Za-z]:\/$/', $joined) === 1) {
            $joined .= $trimmed;
        } else {
            $joined .= '/' . $trimmed;
        }
    }
    return $joined;
}

function app_ai_workspace_clean_relative_path(string $path): ?string
{
    $segments = [];
    foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }
        if ($segment === '..') {
            return null;
        }
        $segments[] = $segment;
    }
    return implode('/', $segments);
}

function app_ai_workspace_normalize_path_lexically(string $path): string
{
    $path = str_replace('\\', '/', $path);
    $isAbsolute = str_starts_with($path, '/');
    $drivePrefix = '';
    if (preg_match('/^([A-Za-z]:)(\/.*)?$/', $path, $matches) === 1) {
        $drivePrefix = $matches[1];
        $path = (string) ($matches[2] ?? '');
        $isAbsolute = true;
    }

    $segments = [];
    foreach (explode('/', $path) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }
        if ($segment === '..') {
            if ($segments !== []) {
                array_pop($segments);
            } elseif (!$isAbsolute) {
                $segments[] = '..';
            }
            continue;
        }
        $segments[] = $segment;
    }

    $normalized = implode('/', $segments);
    if ($drivePrefix !== '') {
        return $drivePrefix . '/' . $normalized;
    }
    if ($isAbsolute) {
        return '/' . $normalized;
    }
    return $normalized;
}

function app_ai_workspace_project_key(string $projectKey, string $projectRoot): string
{
    $candidate = trim($projectKey);
    if ($candidate === '' && $projectRoot !== '') {
        $candidate = basename(app_ai_workspace_trim_path($projectRoot));
    }
    if ($candidate === '') {
        $candidate = 'workspace';
    }
    return app_ai_workspace_slug($candidate);
}

function app_ai_workspace_slug(string $value): string
{
    $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', trim($value)));
    $slug = trim($slug, '-');
    return $slug === '' ? 'workspace' : $slug;
}

function app_ai_workspace_role_key(string $value): string
{
    $key = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', trim($value)));
    return trim($key, '_');
}

/**
 * @param mixed $value
 * @return list<string>
 */
function app_ai_workspace_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }
    return array_values(array_map('strval', $value));
}

function app_ai_workspace_is_absolute_path(string $path): bool
{
    return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
}

/**
 * @param array<string,mixed> $contract
 * @param array<string,array<string,mixed>> $roleMappings
 * @return array<string,string>
 */
function app_ai_workspace_directories(string $workspaceRoot, array $contract, array $roleMappings): array
{
    if ($workspaceRoot === '') {
        return [];
    }

    $directories = [];
    foreach (($contract['fixed_directories'] ?? []) as $key => $relativePath) {
        $directories[(string) $key] = app_ai_workspace_join($workspaceRoot, (string) $relativePath);
    }
    foreach ($roleMappings as $role => $mapping) {
        if (($mapping['disabled'] ?? false) || !is_string($mapping['mapped_path'] ?? null)) {
            continue;
        }
        $mappedPath = (string) $mapping['mapped_path'];
        $directories['role.' . $role] = app_ai_workspace_is_absolute_path($mappedPath)
            ? $mappedPath
            : app_ai_workspace_join($workspaceRoot, $mappedPath);
    }

    return $directories;
}

/**
 * @param array<string,mixed> $contract
 * @return array<string,string>
 */
function app_ai_workspace_manifest_paths(string $workspaceRoot, array $contract): array
{
    if ($workspaceRoot === '') {
        return [];
    }

    $paths = [];
    foreach (($contract['manifests'] ?? []) as $key => $relativePath) {
        $paths[(string) $key] = app_ai_workspace_join($workspaceRoot, (string) $relativePath);
    }
    return $paths;
}

function app_ai_workspace_relative_to_workspace(string $workspaceRoot, string $targetPath): ?string
{
    if ($workspaceRoot === '') {
        return null;
    }
    if ($targetPath === $workspaceRoot) {
        return '';
    }
    $prefix = $workspaceRoot . '/';
    if (!str_starts_with($targetPath, $prefix)) {
        return null;
    }
    return substr($targetPath, strlen($prefix));
}

function app_ai_workspace_path_is_same_or_child(string $targetPath, string $parentPath): bool
{
    return $targetPath === $parentPath || str_starts_with($targetPath, $parentPath . '/');
}
