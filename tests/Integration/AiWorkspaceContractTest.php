<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/ai_workspace_contract.php';

use PHPUnit\Framework\TestCase;

final class AiWorkspaceContractTest extends TestCase
{
    public function testResolvesProjectLocalWorkspaceWithoutFilesystemWrites(): void
    {
        $result = app_ai_workspace_resolve([
            'mtool_home' => '/tools/DegoDB',
            'project_root' => '/projects/my-app',
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('project-local', $result['profile']);
        self::assertSame('default.project-local', $result['decision_source']);
        self::assertSame('/projects/my-app/mtool-workspace', $result['workspace_root']);
        self::assertSame('/projects/my-app/mtool-workspace/mtool-project', $result['mtool_project_root']);
        self::assertFalse($result['diagnostics']['filesystem_writes']);
        self::assertFalse($result['diagnostics']['scan_started']);
        self::assertSame(
            '/projects/my-app/mtool-workspace/mtool-project/config/role-mapping.json',
            $result['manifests']['role_mapping'] ?? '',
        );
        self::assertSame(
            '/projects/my-app/mtool-workspace/design-briefs',
            $result['directories']['role.design_briefs'] ?? '',
        );
    }

    public function testCliWorkspaceRootWinsOverEnvAndProfile(): void
    {
        $result = app_ai_workspace_resolve([
            'mtool_home' => '/tools/DegoDB',
            'project_root' => '/projects/my-app',
            'workspace_root_cli' => '/tmp/mtool-work/my-app',
            'workspace_root_env' => '/env/workspace',
            'profile' => 'mtool-work',
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('external', $result['profile']);
        self::assertSame('cli.workspace_root', $result['decision_source']);
        self::assertSame('/tmp/mtool-work/my-app', $result['workspace_root']);
        self::assertSame(['env.workspace_root', 'profile.mtool-work'], $result['ignored_sources']);
    }

    public function testExplicitMtoolWorkProfileUsesMtoolHomeWorkProjectKey(): void
    {
        $result = app_ai_workspace_resolve([
            'mtool_home' => '/Users/matsue/dev/DegoDB',
            'project_root' => '/projects/My App',
            'project_key' => 'Sample AI Workspace Check',
            'profile' => 'mtool-work',
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('mtool-work', $result['profile']);
        self::assertSame('profile.mtool-work', $result['decision_source']);
        self::assertSame('/Users/matsue/dev/DegoDB/work/sample-ai-workspace-check', $result['workspace_root']);
        self::assertSame(
            '/Users/matsue/dev/DegoDB/work/sample-ai-workspace-check/mtool-project',
            $result['directories']['mtool_project'] ?? '',
        );
    }

    public function testExplicitExternalProfileRequiresExplicitWorkspaceRoot(): void
    {
        $result = app_ai_workspace_resolve([
            'profile' => 'external',
            'project_root' => '/projects/my-app',
        ]);

        self::assertFalse($result['ok']);
        self::assertSame('external', $result['profile']);
        self::assertSame('profile.external', $result['decision_source']);
        self::assertSame('', $result['workspace_root']);
        self::assertSame(['workspace_root_cli or workspace_root_env is required for profile external'], $result['errors']);
    }

    public function testExplicitProfilesDoNotInventWorkspaceRootsWhenRequiredRootsAreMissing(): void
    {
        $mtoolWork = app_ai_workspace_resolve([
            'profile' => 'mtool-work',
            'project_root' => '/projects/my-app',
        ]);
        self::assertFalse($mtoolWork['ok']);
        self::assertSame('', $mtoolWork['workspace_root']);
        self::assertSame('', $mtoolWork['mtool_project_root']);
        self::assertSame([], $mtoolWork['directories']);

        $projectLocal = app_ai_workspace_resolve([
            'profile' => 'project-local',
            'mtool_home' => '/tools/DegoDB',
        ]);
        self::assertFalse($projectLocal['ok']);
        self::assertSame('', $projectLocal['workspace_root']);
        self::assertSame('', $projectLocal['mtool_project_root']);
        self::assertSame([], $projectLocal['directories']);
    }

    public function testRoleMappingAllowsExistingNotesAndDisabledRoles(): void
    {
        $result = app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
            'role_mappings' => [
                'design_briefs' => [
                    'mapped_path' => '/Users/me/Obsidian/Design Notes',
                    'owner' => 'external',
                ],
                'logs' => [
                    'disabled' => true,
                ],
            ],
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('/Users/me/Obsidian/Design Notes', $result['role_mappings']['design_briefs']['mapped_path']);
        self::assertTrue($result['role_mappings']['design_briefs']['external']);
        self::assertSame('external', $result['role_mappings']['design_briefs']['owner']);
        self::assertTrue($result['role_mappings']['logs']['disabled']);
        self::assertNull($result['role_mappings']['logs']['mapped_path']);
        self::assertArrayNotHasKey('role.logs', $result['directories']);
        self::assertSame('/Users/me/Obsidian/Design Notes', $result['directories']['role.design_briefs'] ?? '');
    }

    public function testRoleMappingAcceptsStandardDirectoryNameAliases(): void
    {
        $result = app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
            'role_mappings' => [
                'design-briefs' => [
                    'mapped_path' => 'notes/design',
                ],
                'unknown-role' => [
                    'mapped_path' => 'nowhere',
                ],
            ],
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('notes/design', $result['role_mappings']['design_briefs']['mapped_path']);
        self::assertSame(
            '/projects/my-app/mtool-workspace/notes/design',
            $result['directories']['role.design_briefs'] ?? '',
        );
        self::assertSame(['unknown role mapping ignored: unknown-role'], $result['warnings']);
    }

    public function testRoleMappingRejectsRelativeParentTraversal(): void
    {
        $result = app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
            'role_mappings' => [
                'proposals' => [
                    'mapped_path' => '../docs/proposals',
                ],
                'inputs' => [
                    'mapped_path' => './inputs',
                ],
            ],
        ]);

        self::assertFalse($result['ok']);
        self::assertSame(['role mapping path must not contain parent traversal: proposals'], $result['errors']);
        self::assertTrue($result['role_mappings']['proposals']['disabled']);
        self::assertNull($result['role_mappings']['proposals']['mapped_path']);
        self::assertArrayNotHasKey('role.proposals', $result['directories']);
        self::assertSame('inputs', $result['role_mappings']['inputs']['mapped_path']);
    }

    public function testMtoolOwnedPathsAreReadOnlyExceptEditableConfig(): void
    {
        $resolution = app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
        ]);
        self::assertTrue($resolution['ok'], implode('; ', $resolution['errors']));

        $blocked = app_ai_workspace_evaluate_write_request(
            $resolution,
            '/projects/my-app/mtool-workspace/mtool-project/metadata/schema.json',
        );
        self::assertFalse($blocked['allowed']);
        self::assertSame('mtool_owned_read_only', $blocked['reason']);
        self::assertStringContainsString('Refusing direct AI write', $blocked['message']);

        $allowed = app_ai_workspace_evaluate_write_request(
            $resolution,
            '/projects/my-app/mtool-workspace/mtool-project/config/role-mapping.json',
        );
        self::assertTrue($allowed['allowed']);
        self::assertSame('explicit_editable_mtool_config', $allowed['reason']);
    }

    public function testMtoolOwnedRelativePathsAreAlsoReadOnly(): void
    {
        $resolution = app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
        ]);
        self::assertTrue($resolution['ok'], implode('; ', $resolution['errors']));

        $blocked = app_ai_workspace_evaluate_write_request(
            $resolution,
            'mtool-project/metadata/schema.json',
        );

        self::assertFalse($blocked['allowed']);
        self::assertSame('mtool_owned_read_only', $blocked['reason']);
        self::assertSame('mtool-project/metadata/schema.json', $blocked['relative_path']);
    }

    public function testMtoolOwnedRelativeTraversalPathsAreNormalizedBeforeGuard(): void
    {
        $resolution = app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
        ]);
        self::assertTrue($resolution['ok'], implode('; ', $resolution['errors']));

        $blocked = app_ai_workspace_evaluate_write_request(
            $resolution,
            '../mtool-workspace/mtool-project/metadata/schema.json',
        );

        self::assertFalse($blocked['allowed']);
        self::assertSame('mtool_owned_read_only', $blocked['reason']);
        self::assertSame('mtool-project/metadata/schema.json', $blocked['relative_path']);
    }

    public function testRootProjectPathIsPreserved(): void
    {
        $result = app_ai_workspace_resolve([
            'project_root' => '/',
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('/mtool-workspace', $result['workspace_root']);
        self::assertSame('/mtool-workspace/mtool-project', $result['mtool_project_root']);
    }

    public function testRelativeRootsAreWarningsBeforeAnyFilesystemWrites(): void
    {
        $result = app_ai_workspace_resolve([
            'mtool_home' => 'DegoDB',
            'project_root' => 'my-app',
            'workspace_root_cli' => 'tmp/mtool-work',
        ]);

        self::assertTrue($result['ok'], implode('; ', $result['errors']));
        self::assertSame('tmp/mtool-work', $result['workspace_root']);
        self::assertSame(
            [
                'mtool_home is not absolute; resolve it before filesystem writes',
                'project_root is not absolute; resolve it before filesystem writes',
                'workspace_root_cli is not absolute; resolve it before filesystem writes',
            ],
            $result['warnings'],
        );
    }

    public function testCopyPlanArtifactPathsAreStableAndSlugged(): void
    {
        self::assertSame(
            [
                'slug' => 'my-app-output',
                'markdown' => 'proposals/copy-plan-my-app-output.md',
                'json' => 'proposals/copy-plan-my-app-output.json',
            ],
            app_ai_workspace_copy_plan_artifact_paths('My App Output!'),
        );
    }

    public function testOnboardingPromptArtifactSummarizesValidWorkspacePlan(): void
    {
        $resolution = app_ai_workspace_resolve([
            'mtool_home' => '/tools/DegoDB',
            'project_root' => '/projects/my-app',
            'role_mappings' => [
                'design-briefs' => [
                    'mapped_path' => '/Users/me/Obsidian/Design Notes',
                    'owner' => 'external',
                ],
                'logs' => [
                    'disabled' => true,
                ],
            ],
        ]);
        self::assertTrue($resolution['ok'], implode('; ', $resolution['errors']));

        $artifact = app_ai_workspace_onboarding_prompt_artifact($resolution);

        self::assertTrue($artifact['ok']);
        self::assertSame('mtool-ai-workspace-onboarding-prompt-v0', $artifact['artifact_version']);
        self::assertSame('mtool-ai-workspace-onboarding-prompt-v0', $artifact['metadata']['artifact_version']);
        self::assertTrue($artifact['metadata']['approval_required']);
        self::assertTrue($artifact['metadata']['can_initialize']);
        self::assertFalse($artifact['metadata']['requires_warning_resolution']);
        self::assertFalse($artifact['metadata']['filesystem_writes']);
        self::assertFalse($artifact['metadata']['scan_started']);
        self::assertSame('project-local', $artifact['metadata']['profile']);
        self::assertSame('/projects/my-app/mtool-workspace', $artifact['metadata']['workspace_root']);
        self::assertSame('/projects/my-app/mtool-workspace/mtool-project', $artifact['metadata']['mtool_project_root']);
        self::assertSame('/Users/me/Obsidian/Design Notes', $artifact['metadata']['role_mappings']['design_briefs']['mapped_path']);
        self::assertTrue($artifact['metadata']['role_mappings']['logs']['disabled']);

        $prompt = $artifact['prompt_text'];
        self::assertStringContainsString('Mtool workspace onboarding confirmation', $prompt);
        self::assertStringContainsString('Selected profile: project-local', $prompt);
        self::assertStringContainsString('Workspace root: /projects/my-app/mtool-workspace', $prompt);
        self::assertStringContainsString('`mtool-project/` is Mtool-owned', $prompt);
        self::assertStringContainsString('design-briefs: /Users/me/Obsidian/Design Notes (external)', $prompt);
        self::assertStringContainsString('logs: disabled', $prompt);
        self::assertStringContainsString('No directories have been created', $prompt);
        self::assertStringContainsString('Do you want to approve this workspace plan', $prompt);
    }

    public function testOnboardingPromptArtifactIncludesWarningsBeforeWrites(): void
    {
        $resolution = app_ai_workspace_resolve([
            'project_root' => 'my-app',
        ]);
        self::assertTrue($resolution['ok'], implode('; ', $resolution['errors']));

        $artifact = app_ai_workspace_onboarding_prompt_artifact($resolution);

        self::assertTrue($artifact['ok']);
        self::assertFalse($artifact['metadata']['can_initialize']);
        self::assertTrue($artifact['metadata']['requires_warning_resolution']);
        self::assertSame(
            ['project_root is not absolute; resolve it before filesystem writes'],
            $artifact['metadata']['warnings'],
        );
        self::assertStringContainsString('Warnings to resolve before filesystem writes:', $artifact['prompt_text']);
        self::assertStringContainsString('project_root is not absolute', $artifact['prompt_text']);
        self::assertStringContainsString('Please resolve these warnings', $artifact['prompt_text']);
        self::assertStringContainsString('keep this workspace plan for review', $artifact['prompt_text']);
    }

    public function testOnboardingPromptArtifactExplainsResolverErrors(): void
    {
        $resolution = app_ai_workspace_resolve([
            'profile' => 'external',
            'project_root' => '/projects/my-app',
        ]);
        self::assertFalse($resolution['ok']);

        $artifact = app_ai_workspace_onboarding_prompt_artifact($resolution);

        self::assertFalse($artifact['ok']);
        self::assertTrue($artifact['metadata']['approval_required']);
        self::assertFalse($artifact['metadata']['can_initialize']);
        self::assertSame(['workspace_root_cli or workspace_root_env is required for profile external'], $artifact['metadata']['errors']);
        self::assertStringContainsString('I cannot initialize a workspace yet', $artifact['prompt_text']);
        self::assertStringContainsString('workspace_root_cli or workspace_root_env is required for profile external', $artifact['prompt_text']);
        self::assertStringContainsString('No directories were created', $artifact['prompt_text']);
    }

    public function testInitializationPreflightPlansDirectoriesAndManifestsWithoutWrites(): void
    {
        $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
            'role_mappings' => [
                'design-briefs' => [
                    'mapped_path' => '/Users/me/Obsidian/Design Notes',
                ],
                'logs' => [
                    'disabled' => true,
                ],
            ],
        ]));
        self::assertTrue($artifact['ok']);

        $preflight = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'apply',
            'approved' => true,
            'existing_paths' => [
                '/projects/my-app/mtool-workspace/mtool-project' => 'dir',
                '/projects/my-app/mtool-workspace/mtool-project/config/workspace.json' => 'file',
            ],
        ]);

        self::assertTrue($preflight['ok'], implode('; ', $preflight['errors']));
        self::assertTrue($preflight['can_apply']);
        self::assertFalse($preflight['filesystem_writes']);
        self::assertFalse($preflight['scan_started']);
        self::assertTrue($preflight['no_overwrite']);
        self::assertSame('/projects/my-app/mtool-workspace', $preflight['workspace_root']);
        self::assertContains(
            [
                'key' => 'mtool_project',
                'path' => '/projects/my-app/mtool-workspace/mtool-project',
                'reason' => 'already_exists',
            ],
            $preflight['reuse_directories'],
        );
        self::assertContains(
            [
                'key' => 'role.design_briefs',
                'path' => '/Users/me/Obsidian/Design Notes',
                'reason' => 'external_role',
            ],
            $preflight['skip_directories'],
        );
        self::assertContains(
            [
                'key' => 'workspace',
                'path' => '/projects/my-app/mtool-workspace/mtool-project/config/workspace.json',
                'reason' => 'no_overwrite_existing_file',
            ],
            $preflight['skip_manifests'],
        );
        self::assertStringContainsString('mtool-workspace/scans/', $preflight['gitignore_suggestion']);
        self::assertSame('resolver', $preflight['write_manifests'][0]['key']);
        self::assertSame('mtool-workspace-resolver-v0', $preflight['write_manifests'][0]['content']['manifest_version']);
    }

    public function testInitializationPreflightRequiresApprovalForApplyMode(): void
    {
        $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
        ]));

        $preflight = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'apply',
            'approved' => false,
        ]);

        self::assertFalse($preflight['ok']);
        self::assertFalse($preflight['can_apply']);
        self::assertSame(['explicit approval is required for initialization apply mode'], $preflight['errors']);
    }

    public function testInitializationPreflightBlocksWarningWithoutExplicitAcceptance(): void
    {
        $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
            'project_root' => 'my-app',
        ]));
        self::assertFalse($artifact['metadata']['can_initialize']);

        $preflight = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'apply',
            'approved' => true,
        ]);

        self::assertFalse($preflight['ok']);
        self::assertContains('workspace warnings must be resolved or explicitly accepted before initialization', $preflight['errors']);

        $accepted = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'apply',
            'approved' => true,
            'accepted_warnings' => true,
        ]);
        self::assertTrue($accepted['ok'], implode('; ', $accepted['errors']));
        self::assertTrue($accepted['can_apply']);
    }

    public function testInitializationPreflightBlocksDirectoryFileCollision(): void
    {
        $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
        ]));

        $preflight = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'dry-run',
            'existing_paths' => [
                '/projects/my-app/mtool-workspace/mtool-project/config' => 'file',
            ],
        ]);

        self::assertFalse($preflight['ok']);
        self::assertSame(
            ['directory path already exists as file: /projects/my-app/mtool-workspace/mtool-project/config'],
            $preflight['errors'],
        );
        self::assertFalse($preflight['can_apply']);
    }

    public function testInitializationPreflightRejectsInvalidOnboardingArtifact(): void
    {
        $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
            'profile' => 'external',
            'project_root' => '/projects/my-app',
        ]));
        self::assertFalse($artifact['ok']);

        $preflight = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'dry-run',
        ]);

        self::assertFalse($preflight['ok']);
        self::assertContains('onboarding artifact is not valid for initialization', $preflight['errors']);
        self::assertFalse($preflight['can_apply']);
    }

    public function testInitializationApplyCreatesMissingDirectoriesAndManifestsAfterApproval(): void
    {
        $root = self::makeTemporaryDirectory('mtool-ai-workspace-apply-');
        try {
            $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
                'project_root' => $root . '/project',
                'role_mappings' => [
                    'design_briefs' => [
                        'mapped_path' => $root . '/external-design-notes',
                    ],
                    'logs' => [
                        'disabled' => true,
                    ],
                ],
            ]));
            self::assertTrue($artifact['ok']);

            $preflight = app_ai_workspace_initialization_preflight($artifact, [
                'mode' => 'apply',
                'approved' => true,
            ]);
            self::assertTrue($preflight['can_apply'], implode('; ', $preflight['errors']));

            $applied = app_ai_workspace_initialization_apply($preflight);

            self::assertTrue($applied['ok'], implode('; ', $applied['errors']));
            self::assertTrue($applied['filesystem_writes']);
            self::assertFalse($applied['scan_started']);
            self::assertTrue(is_dir($root . '/project/mtool-workspace/mtool-project/config'));
            self::assertTrue(is_dir($root . '/project/mtool-workspace/inputs'));
            self::assertFalse(is_dir($root . '/external-design-notes'));
            self::assertFalse(is_dir($root . '/project/mtool-workspace/logs'));
            self::assertFileExists($root . '/project/mtool-workspace/mtool-project/config/workspace.json');
            self::assertFileExists($root . '/project/mtool-workspace/mtool-project/config/role-mapping.json');

            $workspaceManifest = json_decode(
                (string) file_get_contents($root . '/project/mtool-workspace/mtool-project/config/workspace.json'),
                true,
            );
            self::assertSame('mtool-workspace-v0', $workspaceManifest['manifest_version'] ?? '');
            self::assertSame($root . '/project/mtool-workspace', $workspaceManifest['workspace_root'] ?? '');
            self::assertContains(
                [
                    'key' => 'role.design_briefs',
                    'path' => $root . '/external-design-notes',
                    'reason' => 'external_role',
                ],
                $applied['skipped_directories'],
            );
        } finally {
            self::removeDirectory($root);
        }
    }

    public function testInitializationApplyDoesNotOverwriteManifestCreatedAfterPreflight(): void
    {
        $root = self::makeTemporaryDirectory('mtool-ai-workspace-no-overwrite-');
        try {
            $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
                'project_root' => $root . '/project',
            ]));
            $preflight = app_ai_workspace_initialization_preflight($artifact, [
                'mode' => 'apply',
                'approved' => true,
            ]);
            self::assertTrue($preflight['can_apply'], implode('; ', $preflight['errors']));

            $workspaceManifestPath = $root . '/project/mtool-workspace/mtool-project/config/workspace.json';
            self::assertTrue(mkdir(dirname($workspaceManifestPath), 0775, true));
            self::assertSame(8, file_put_contents($workspaceManifestPath, 'existing'));

            $applied = app_ai_workspace_initialization_apply($preflight);

            self::assertTrue($applied['ok'], implode('; ', $applied['errors']));
            self::assertSame('existing', file_get_contents($workspaceManifestPath));
            self::assertContains(
                [
                    'key' => 'workspace',
                    'path' => $workspaceManifestPath,
                    'reason' => 'no_overwrite_existing_file_at_apply',
                ],
                $applied['skipped_manifests'],
            );
            self::assertStringContainsString(
                'manifest already exists and was not overwritten at apply time',
                implode('; ', $applied['warnings']),
            );
        } finally {
            self::removeDirectory($root);
        }
    }

    public function testInitializationApplyRequiresSuccessfulApprovedApplyPreflight(): void
    {
        $artifact = app_ai_workspace_onboarding_prompt_artifact(app_ai_workspace_resolve([
            'project_root' => '/projects/my-app',
        ]));
        $preflight = app_ai_workspace_initialization_preflight($artifact, [
            'mode' => 'dry-run',
        ]);

        $applied = app_ai_workspace_initialization_apply($preflight);

        self::assertFalse($applied['ok']);
        self::assertFalse($applied['filesystem_writes']);
        self::assertSame(
            ['successful approved apply-mode preflight is required before initialization apply'],
            $applied['errors'],
        );
    }

    public function testInitializationCliEntryPreflightDefaultsToDryRunWithoutWrites(): void
    {
        $preflight = app_ai_workspace_initialization_cli_entry_preflight([
            'init_ai_workspace.php',
            '--project-root=/projects/my-app',
            '--json',
        ]);

        self::assertTrue($preflight['ok'], implode('; ', $preflight['errors']));
        self::assertSame('mtool/scripts/init_ai_workspace.php', $preflight['command_name']);
        self::assertStringContainsString('usage: php mtool/scripts/init_ai_workspace.php', $preflight['usage']);
        self::assertSame('dry-run', $preflight['parsed']['options']['mode']);
        self::assertTrue($preflight['parsed']['options']['json']);
        self::assertSame('/projects/my-app/mtool-workspace', $preflight['resolution']['workspace_root']);
        self::assertSame('dry-run', $preflight['initialization_preflight']['mode']);
        self::assertFalse($preflight['can_run_apply']);
        self::assertFalse($preflight['filesystem_writes']);
        self::assertFalse($preflight['scan_started']);
    }

    public function testInitializationCliEntryPreflightRequiresApproveForApply(): void
    {
        $blocked = app_ai_workspace_initialization_cli_entry_preflight([
            'init_ai_workspace.php',
            '--project-root=/projects/my-app',
            '--mode=apply',
        ]);

        self::assertFalse($blocked['ok']);
        self::assertFalse($blocked['can_run_apply']);
        self::assertContains('explicit approval is required for initialization apply mode', $blocked['errors']);

        $approved = app_ai_workspace_initialization_cli_entry_preflight([
            'init_ai_workspace.php',
            '--project-root=/projects/my-app',
            '--mode=apply',
            '--approve',
        ]);

        self::assertTrue($approved['ok'], implode('; ', $approved['errors']));
        self::assertTrue($approved['can_run_apply']);
        self::assertTrue($approved['initialization_preflight']['can_apply']);
    }

    public function testInitializationCliEntryPreflightCliWorkspaceRootWinsOverEnv(): void
    {
        $preflight = app_ai_workspace_initialization_cli_entry_preflight([
            'init_ai_workspace.php',
            '--project-root=/projects/my-app',
            '--workspace-root=/cli/workspace',
            '--profile=mtool-work',
        ], [
            'MTOOL_AI_WORKSPACE_ROOT' => '/env/workspace',
        ]);

        self::assertTrue($preflight['ok'], implode('; ', $preflight['errors']));
        self::assertSame('/cli/workspace', $preflight['resolution']['workspace_root']);
        self::assertSame('cli.workspace_root', $preflight['resolution']['decision_source']);
        self::assertSame(['env.workspace_root', 'profile.mtool-work'], $preflight['resolution']['ignored_sources']);
        self::assertSame(
            ['cli.--workspace-root', 'env.MTOOL_AI_WORKSPACE_ROOT', 'profile/default'],
            $preflight['input_precedence']['workspace_root'],
        );
    }

    public function testInitializationCliEntryPreflightParsesRoleMappingsAndExistingPathHints(): void
    {
        $preflight = app_ai_workspace_initialization_cli_entry_preflight([
            'init_ai_workspace.php',
            '--project-root=/projects/my-app',
            '--mode=apply',
            '--approve',
            '--external-role=design_briefs=/Users/me/Obsidian/Design Notes',
            '--disable-role=logs',
            '--existing-path=/projects/my-app/mtool-workspace/mtool-project/config/workspace.json:file',
        ]);

        self::assertTrue($preflight['ok'], implode('; ', $preflight['errors']));
        self::assertTrue($preflight['can_run_apply']);
        self::assertSame('/Users/me/Obsidian/Design Notes', $preflight['resolution']['role_mappings']['design_briefs']['mapped_path']);
        self::assertTrue($preflight['resolution']['role_mappings']['design_briefs']['external']);
        self::assertTrue($preflight['resolution']['role_mappings']['logs']['disabled']);
        self::assertContains(
            [
                'key' => 'workspace',
                'path' => '/projects/my-app/mtool-workspace/mtool-project/config/workspace.json',
                'reason' => 'no_overwrite_existing_file',
            ],
            $preflight['initialization_preflight']['skip_manifests'],
        );
    }

    public function testInitializationCliEntryPreflightReportsParseErrorsWithoutResolving(): void
    {
        $preflight = app_ai_workspace_initialization_cli_entry_preflight([
            'init_ai_workspace.php',
            '--unknown',
            '--role=broken',
            '--existing-path=/tmp/workspace:directory',
        ]);

        self::assertFalse($preflight['ok']);
        self::assertSame(
            [
                'unsupported option: --unknown',
                'role mapping must use ROLE=PATH',
                'existing path hint type must be file, dir, or unknown',
            ],
            $preflight['errors'],
        );
        self::assertSame([], $preflight['resolution']);
        self::assertSame([], $preflight['initialization_preflight']);
        self::assertFalse($preflight['filesystem_writes']);
    }

    public function testInitializationCliWrapperReturnsJsonDryRunWithoutFilesystemWrites(): void
    {
        $root = self::makeTemporaryDirectory('mtool-ai-workspace-cli-dry-run-');
        try {
            $result = self::runAiWorkspaceCli([
                '--project-root=' . $root . '/project',
                '--json',
            ]);

            self::assertSame(0, $result['exit_code'], $result['output']);
            $payload = json_decode($result['output'], true);
            self::assertIsArray($payload);
            self::assertSame('dry-run', $payload['preflight']['initialization_preflight']['mode'] ?? '');
            self::assertFalse($payload['preflight']['can_run_apply'] ?? true);
            self::assertNull($payload['apply'] ?? null);
            self::assertFalse(is_dir($root . '/project/mtool-workspace'));
        } finally {
            self::removeDirectory($root);
        }
    }

    public function testInitializationCliWrapperAppliesOnlyWithExplicitApproval(): void
    {
        $root = self::makeTemporaryDirectory('mtool-ai-workspace-cli-apply-');
        try {
            $blocked = self::runAiWorkspaceCli([
                '--project-root=' . $root . '/project',
                '--mode=apply',
                '--json',
            ]);

            self::assertSame(2, $blocked['exit_code']);
            $blockedPayload = json_decode($blocked['output'], true);
            self::assertIsArray($blockedPayload);
            self::assertContains(
                'explicit approval is required for initialization apply mode',
                $blockedPayload['preflight']['errors'] ?? [],
            );
            self::assertFalse(is_dir($root . '/project/mtool-workspace'));

            $applied = self::runAiWorkspaceCli([
                '--project-root=' . $root . '/project',
                '--mode=apply',
                '--approve',
                '--json',
            ]);

            self::assertSame(0, $applied['exit_code'], $applied['output']);
            $appliedPayload = json_decode($applied['output'], true);
            self::assertIsArray($appliedPayload);
            self::assertTrue($appliedPayload['preflight']['can_run_apply'] ?? false);
            self::assertTrue($appliedPayload['apply']['ok'] ?? false);
            self::assertTrue($appliedPayload['apply']['filesystem_writes'] ?? false);
            self::assertTrue(is_dir($root . '/project/mtool-workspace/mtool-project/config'));
            self::assertFileExists($root . '/project/mtool-workspace/mtool-project/config/workspace.json');
        } finally {
            self::removeDirectory($root);
        }
    }

    private static function makeTemporaryDirectory(string $prefix): string
    {
        $path = sys_get_temp_dir() . '/' . $prefix . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($path, 0775, true));
        return $path;
    }

    /**
     * @param list<string> $arguments
     * @return array{exit_code:int,output:string}
     */
    private static function runAiWorkspaceCli(array $arguments): array
    {
        $script = dirname(__DIR__, 2) . '/mtool/scripts/init_ai_workspace.php';
        $command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script);
        foreach ($arguments as $argument) {
            $command .= ' ' . escapeshellarg($argument);
        }
        $command .= ' 2>&1';

        $lines = [];
        $exitCode = 0;
        exec($command, $lines, $exitCode);

        return [
            'exit_code' => $exitCode,
            'output' => implode("\n", $lines),
        ];
    }

    private static function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        $items = scandir($path);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $child = $path . '/' . $item;
            if (is_dir($child) && !is_link($child)) {
                self::removeDirectory($child);
                continue;
            }
            @unlink($child);
        }
        @rmdir($path);
    }
}
