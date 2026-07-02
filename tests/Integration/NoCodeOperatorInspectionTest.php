<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_operator_inspection.php';

use PHPUnit\Framework\TestCase;

final class NoCodeOperatorInspectionTest extends TestCase
{
    public function testSummarizesNoCodeRuntimeArtifactsAndPreviewMetadata(): void
    {
        $workspaceRoot = sys_get_temp_dir() . '/dego-no-code-operator-inspection-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $sourceRoot = $workspaceRoot . '/work/source-outputs/SAMPLE30/NO-CODE-RUNTIME';
        mkdir($sourceRoot, 0777, true);

        file_put_contents($sourceRoot . '/screen-definition.json', json_encode([
            'definition_version' => 'no-code-screen-definition-v0',
            'project_key' => 'SAMPLE30',
            'contracts' => [
                [
                    'contract_key' => 'sync_task',
                    'screens' => [
                        [
                            'screen_key' => 'sync_task_list',
                            'screen_type' => 'list',
                            'actions' => [
                                ['action_key' => 'update_sync_task'],
                            ],
                            'sync_status_hint' => true,
                        ],
                        [
                            'screen_key' => 'sync_task_detail',
                            'screen_type' => 'detail',
                            'actions' => [
                                ['action_key' => 'update_sync_task'],
                            ],
                            'sync_status_hint' => true,
                        ],
                        [
                            'screen_key' => 'sync_task_form',
                            'screen_type' => 'form',
                            'actions' => [],
                            'sync_status_hint' => false,
                        ],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($sourceRoot . '/runtime-preview.json', json_encode([
            'runtime_version' => 'no-code-runtime-v0',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($sourceRoot . '/runtime-preview.html', '<!doctype html>');

        $summary = app_no_code_operator_inspection_from_catalog(
            [
                [
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'name' => 'No-Code Runtime',
                    'source_output_dir' => 'work/source-outputs/SAMPLE30/NO-CODE-RUNTIME',
                    'artifact_strategy' => 'no-code-runtime-json',
                ],
            ],
            [
                [
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'artifact_key' => '20260630010101-aaaa',
                    'created_at' => '2026-06-30T01:01:01+00:00',
                    'archive_exists' => true,
                ],
                [
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'artifact_key' => '20260630020202-bbbb',
                    'created_at' => '2026-06-30T02:02:02+00:00',
                    'archive_exists' => true,
                ],
            ],
            'SAMPLE30',
            $workspaceRoot,
        );

        self::assertTrue($summary['available']);
        self::assertSame('NO-CODE-RUNTIME', $summary['source_output_key']);
        self::assertSame(2, $summary['artifact_count']);
        self::assertSame('20260630020202-bbbb', $summary['latest_artifact']['artifact_key'] ?? '');
        self::assertSame('work/source-outputs/SAMPLE30/NO-CODE-RUNTIME', $summary['source_output_dir']);
        self::assertSame('ready', $summary['health']['state']);
        self::assertSame('Preview ready', $summary['health']['label']);
        self::assertSame(
            ['inspect-definition', 'inspect-artifact', 'review-preview', 'check-actions'],
            array_column($summary['workflow_steps'], 'key'),
        );
        self::assertSame(['ready', 'ready', 'ready', 'ready'], array_column($summary['workflow_steps'], 'state'));
        self::assertSame('Latest artifact archive is available.', $summary['workflow_steps'][1]['detail'] ?? '');
        self::assertSame('Preview JSON and HTML are available with generated screens.', $summary['workflow_steps'][2]['detail'] ?? '');
        self::assertSame('publishable', $summary['publish_readiness']['state'] ?? '');
        self::assertSame('Publish candidate ready', $summary['publish_readiness']['label'] ?? '');
        self::assertSame('NO-CODE-RUNTIME', $summary['publish_readiness']['source_output_key'] ?? '');
        self::assertSame('20260630020202-bbbb', $summary['publish_readiness']['artifact_key'] ?? '');
        self::assertTrue($summary['publish_readiness']['artifact_archive_exists'] ?? false);
        self::assertTrue($summary['publish_readiness']['preview_files_ready'] ?? false);
        self::assertSame(3, $summary['publish_readiness']['screen_count'] ?? 0);
        self::assertSame(2, $summary['publish_readiness']['action_count'] ?? 0);
        self::assertSame([], $summary['publish_readiness']['blocking_reasons'] ?? []);

        $preview = $summary['preview'];
        self::assertTrue($preview['screen_definition_exists']);
        self::assertTrue($preview['runtime_preview_exists']);
        self::assertTrue($preview['runtime_preview_html_exists']);
        self::assertSame('no-code-screen-definition-v0', $preview['definition_version']);
        self::assertSame('no-code-runtime-v0', $preview['runtime_version']);
        self::assertSame(1, $preview['contract_count']);
        self::assertSame(3, $preview['screen_count']);
        self::assertSame(2, $preview['action_count']);
        self::assertSame(2, $preview['sync_hint_screen_count']);
        self::assertSame(['sync_task_list', 'sync_task_detail', 'sync_task_form'], $preview['screen_keys']);
        self::assertSame(['update_sync_task'], $preview['action_keys']);
        self::assertSame([], $preview['errors']);
    }

    public function testReportsMissingNoCodeRuntimeDefinitionWithoutFailingInspection(): void
    {
        $workspaceRoot = sys_get_temp_dir() . '/dego-no-code-operator-inspection-missing-' . getmypid() . '-' . bin2hex(random_bytes(4));

        $summary = app_no_code_operator_inspection_from_catalog(
            [],
            [],
            'missing project',
            $workspaceRoot,
        );

        self::assertFalse($summary['available']);
        self::assertSame('work/source-outputs/MISSING-PROJECT/NO-CODE-RUNTIME', $summary['source_output_dir']);
        self::assertSame(0, $summary['artifact_count']);
        self::assertNull($summary['latest_artifact']);
        self::assertFalse($summary['preview']['screen_definition_exists']);
        self::assertFalse($summary['preview']['runtime_preview_exists']);
        self::assertFalse($summary['preview']['runtime_preview_html_exists']);
        self::assertSame(0, $summary['preview']['screen_count']);
        self::assertSame(0, $summary['preview']['action_count']);
        self::assertSame(0, $summary['preview']['sync_hint_screen_count']);
        self::assertSame('missing', $summary['health']['state']);
        self::assertContains('NO-CODE-RUNTIME definition is missing.', $summary['health']['reasons']);
        self::assertContains('screen-definition.json is missing.', $summary['health']['reasons']);
        self::assertSame(['blocked', 'blocked', 'blocked', 'blocked'], array_column($summary['workflow_steps'], 'state'));
        self::assertSame('NO-CODE-RUNTIME definition is missing.', $summary['workflow_steps'][0]['detail'] ?? '');
        self::assertSame('Latest artifact archive is missing or not generated yet.', $summary['workflow_steps'][1]['detail'] ?? '');
        self::assertSame('blocked', $summary['publish_readiness']['state'] ?? '');
        self::assertSame('Publish candidate blocked', $summary['publish_readiness']['label'] ?? '');
        self::assertContains('NO-CODE-RUNTIME definition is missing.', $summary['publish_readiness']['blocking_reasons'] ?? []);
        self::assertContains('Latest generated artifact is missing.', $summary['publish_readiness']['blocking_reasons'] ?? []);
        self::assertContains('Generated preview files are incomplete.', $summary['publish_readiness']['blocking_reasons'] ?? []);
        self::assertContains('Generated action surface is empty.', $summary['publish_readiness']['blocking_reasons'] ?? []);
    }

    public function testReportsWarningHealthWhenLatestArtifactArchiveIsMissing(): void
    {
        $workspaceRoot = sys_get_temp_dir() . '/dego-no-code-operator-inspection-warning-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $sourceRoot = $workspaceRoot . '/work/source-outputs/SAMPLE30/NO-CODE-RUNTIME';
        mkdir($sourceRoot, 0777, true);

        file_put_contents($sourceRoot . '/screen-definition.json', json_encode([
            'definition_version' => 'no-code-screen-definition-v0',
            'contracts' => [
                [
                    'contract_key' => 'sync_task',
                    'screens' => [
                        [
                            'screen_key' => 'sync_task_list',
                            'actions' => [],
                            'sync_status_hint' => true,
                        ],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($sourceRoot . '/runtime-preview.json', json_encode([
            'runtime_version' => 'no-code-runtime-v0',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($sourceRoot . '/runtime-preview.html', '<!doctype html>');

        $summary = app_no_code_operator_inspection_from_catalog(
            [
                [
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'source_output_dir' => 'work/source-outputs/SAMPLE30/NO-CODE-RUNTIME',
                ],
            ],
            [
                [
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'artifact_key' => '20260630020202-bbbb',
                    'archive_exists' => false,
                ],
            ],
            'SAMPLE30',
            $workspaceRoot,
        );

        self::assertSame('warning', $summary['health']['state']);
        self::assertSame('Needs operator review', $summary['health']['label']);
        self::assertContains('Latest artifact archive is missing.', $summary['health']['reasons']);
        self::assertSame('blocked', $summary['workflow_steps'][1]['state'] ?? '');
        self::assertSame('ready', $summary['workflow_steps'][2]['state'] ?? '');
        self::assertSame('blocked', $summary['publish_readiness']['state'] ?? '');
        self::assertContains('Latest generated artifact archive is missing.', $summary['publish_readiness']['blocking_reasons'] ?? []);
    }
}
