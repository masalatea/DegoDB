<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_publish_candidate_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class NoCodePublishCandidateRepositorySqliteTest extends TestCase
{
    public function testBootstrapCreatesPublishCandidateRevisionSchema(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $pdo = app_create_config_pdo($app);

        $table = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'no_code_publish_candidate_revisions'"
        );
        self::assertSame('no_code_publish_candidate_revisions', $table->fetchColumn());
        $transitionTable = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'no_code_publish_candidate_transition_events'"
        );
        self::assertSame('no_code_publish_candidate_transition_events', $transitionTable->fetchColumn());
        $currentTable = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'no_code_public_runtime_current_revisions'"
        );
        self::assertSame('no_code_public_runtime_current_revisions', $currentTable->fetchColumn());
        $aliasTable = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'no_code_public_runtime_aliases'"
        );
        self::assertSame('no_code_public_runtime_aliases', $aliasTable->fetchColumn());
        $aliasEventTable = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'no_code_public_runtime_alias_events'"
        );
        self::assertSame('no_code_public_runtime_alias_events', $aliasEventTable->fetchColumn());

        $columns = $pdo->query('PRAGMA table_info(no_code_publish_candidate_revisions)')->fetchAll();
        self::assertContains('revision_id', array_column($columns, 'name'));
        self::assertContains('source_output_key', array_column($columns, 'name'));
        self::assertContains('artifact_key', array_column($columns, 'name'));
        self::assertContains('snapshot_json', array_column($columns, 'name'));
        self::assertContains('created_by', array_column($columns, 'name'));

        $transitionColumns = $pdo->query('PRAGMA table_info(no_code_publish_candidate_transition_events)')->fetchAll();
        self::assertContains('candidate_revision_id', array_column($transitionColumns, 'name'));
        self::assertContains('transition', array_column($transitionColumns, 'name'));
        self::assertContains('from_status', array_column($transitionColumns, 'name'));
        self::assertContains('to_status', array_column($transitionColumns, 'name'));
        self::assertContains('created_by', array_column($transitionColumns, 'name'));

        $currentColumns = $pdo->query('PRAGMA table_info(no_code_public_runtime_current_revisions)')->fetchAll();
        self::assertContains('candidate_revision_id', array_column($currentColumns, 'name'));
        self::assertContains('revision_id', array_column($currentColumns, 'name'));
        self::assertContains('source_output_key', array_column($currentColumns, 'name'));
        self::assertContains('artifact_key', array_column($currentColumns, 'name'));
        self::assertContains('selected_by', array_column($currentColumns, 'name'));

        $aliasColumns = $pdo->query('PRAGMA table_info(no_code_public_runtime_aliases)')->fetchAll();
        self::assertContains('alias_key', array_column($aliasColumns, 'name'));
        self::assertContains('candidate_revision_id', array_column($aliasColumns, 'name'));
        self::assertContains('revision_id', array_column($aliasColumns, 'name'));
        self::assertContains('source_output_key', array_column($aliasColumns, 'name'));
        self::assertContains('artifact_key', array_column($aliasColumns, 'name'));
        self::assertContains('selected_by', array_column($aliasColumns, 'name'));

        $aliasEventColumns = $pdo->query('PRAGMA table_info(no_code_public_runtime_alias_events)')->fetchAll();
        self::assertContains('alias_key', array_column($aliasEventColumns, 'name'));
        self::assertContains('candidate_revision_id', array_column($aliasEventColumns, 'name'));
        self::assertContains('revision_id', array_column($aliasEventColumns, 'name'));
        self::assertContains('source_output_key', array_column($aliasEventColumns, 'name'));
        self::assertContains('artifact_key', array_column($aliasEventColumns, 'name'));
        self::assertContains('event_type', array_column($aliasEventColumns, 'name'));
        self::assertContains('created_by', array_column($aliasEventColumns, 'name'));
        self::assertContains('metadata_json', array_column($aliasEventColumns, 'name'));
    }

    public function testCreatesListsAndFindsPublishCandidateFromReadinessSnapshot(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');

        $create = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'artifact_key' => '20260702-010203-abcdef12',
            'artifact_archive_path' => 'work/artifacts/source-outputs/SAMPLE28/20260702-010203-abcdef12/output.tar.gz',
            'artifact_checksum' => 'sha256:abc123',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => $this->publishableReadinessSnapshot(),
        ]);
        self::assertTrue($create['ok'], $create['error']);
        self::assertIsArray($create['item']);
        self::assertMatchesRegularExpression('/^[0-9]{8}-[0-9]{6}-[a-f0-9]{16}$/', $create['item']['revision_id'] ?? '');
        self::assertSame('draft_candidate', $create['item']['status'] ?? '');
        self::assertSame('publishable', $create['item']['readiness_state'] ?? '');
        self::assertSame('Publish candidate ready', $create['item']['readiness_label'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $create['item']['artifact_key'] ?? '');
        self::assertSame('sha256:abc123', $create['item']['artifact_checksum'] ?? '');
        self::assertSame(3, $create['item']['screen_count'] ?? 0);
        self::assertSame(2, $create['item']['action_count'] ?? 0);
        self::assertTrue($create['item']['preview_files_ready'] ?? false);
        self::assertTrue($create['item']['artifact_archive_exists'] ?? false);
        self::assertSame([], $create['item']['blocking_reasons'] ?? ['unexpected']);
        self::assertSame('operator@example.test', $create['item']['created_by'] ?? '');
        self::assertSame($this->publishableReadinessSnapshot(), $create['item']['snapshot'] ?? []);

        $second = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
            'readiness_snapshot' => array_merge($this->publishableReadinessSnapshot(), [
                'artifact_key' => '20260702-020304-bcdefa23',
                'screen_count' => 4,
            ]),
        ]);
        self::assertTrue($second['ok'], $second['error']);

        $list = app_pdo_list_no_code_publish_candidates_for_source_output($app, 'SAMPLE28', 'NO-CODE-RUNTIME');
        self::assertTrue($list['ok'], $list['error']);
        self::assertCount(2, $list['items']);
        self::assertSame('20260702-020304-bcdefa23', $list['items'][0]['artifact_key'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $list['items'][1]['artifact_key'] ?? '');

        $find = app_pdo_find_no_code_publish_candidate(
            $app,
            'SAMPLE28',
            'NO-CODE-RUNTIME',
            (string) ($create['item']['revision_id'] ?? ''),
        );
        self::assertTrue($find['ok'], $find['error']);
        self::assertSame($create['item']['revision_id'] ?? '', $find['item']['revision_id'] ?? '');
        self::assertSame($this->publishableReadinessSnapshot(), $find['item']['snapshot'] ?? []);
    }

    public function testRejectsNonPublishableOrMismatchedSnapshotsWithoutPersisting(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');

        $blocked = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => array_merge($this->publishableReadinessSnapshot(), [
                'state' => 'blocked',
                'label' => 'Publish candidate blocked',
                'blocking_reasons' => ['Generated preview files are incomplete.'],
            ]),
        ]);
        self::assertFalse($blocked['ok']);
        self::assertStringContainsString('publishable readiness snapshot', $blocked['error']);

        $mismatch = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'artifact_key' => '20260702-999999-deadbeef',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => $this->publishableReadinessSnapshot(),
        ]);
        self::assertFalse($mismatch['ok']);
        self::assertStringContainsString('artifact key does not match', $mismatch['error']);

        $wrongSource = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'DBACCESS-PHP',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => array_merge($this->publishableReadinessSnapshot(), [
                'source_output_key' => 'DBACCESS-PHP',
            ]),
        ]);
        self::assertFalse($wrongSource['ok']);
        self::assertStringContainsString('NO-CODE-RUNTIME', $wrongSource['error']);

        $viewer = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'viewer@example.test',
                'roles' => ['viewer'],
            ],
            'readiness_snapshot' => $this->publishableReadinessSnapshot(),
        ]);
        self::assertFalse($viewer['ok']);
        self::assertStringContainsString('operator or admin actor', $viewer['error']);

        $list = app_pdo_list_no_code_publish_candidates_for_source_output($app, 'SAMPLE28', 'NO-CODE-RUNTIME');
        self::assertTrue($list['ok'], $list['error']);
        self::assertSame([], $list['items']);
    }

    public function testFindIsScopedByProjectAndSourceOutput(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $this->seedProject($app, 'OTHER');

        $create = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => $this->publishableReadinessSnapshot(),
        ]);
        self::assertTrue($create['ok'], $create['error']);

        $otherProject = app_pdo_find_no_code_publish_candidate(
            $app,
            'OTHER',
            'NO-CODE-RUNTIME',
            (string) ($create['item']['revision_id'] ?? ''),
        );
        self::assertTrue($otherProject['ok'], $otherProject['error']);
        self::assertNull($otherProject['item']);

        $otherSourceOutput = app_pdo_find_no_code_publish_candidate(
            $app,
            'SAMPLE28',
            'DBACCESS-PHP',
            (string) ($create['item']['revision_id'] ?? ''),
        );
        self::assertTrue($otherSourceOutput['ok'], $otherSourceOutput['error']);
        self::assertNull($otherSourceOutput['item']);
    }

    public function testTransitionsDraftCandidateThroughReviewAndApproval(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $requestReview = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'request_review',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'metadata' => [
                'ui_source' => 'repository-test',
            ],
        ]);
        self::assertTrue($requestReview['ok'], $requestReview['error']);
        self::assertSame('review_requested', $requestReview['item']['status'] ?? '');
        self::assertSame('draft_candidate', $requestReview['event']['from_status'] ?? '');
        self::assertSame('review_requested', $requestReview['event']['to_status'] ?? '');

        $approve = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'approve',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($approve['ok'], $approve['error']);
        self::assertSame('approved', $approve['item']['status'] ?? '');
        self::assertSame('approve', $approve['event']['transition'] ?? '');

        $pdo = app_create_config_pdo($app);
        $events = $pdo->query(
            'SELECT transition, from_status, to_status, created_by
             FROM no_code_publish_candidate_transition_events
             ORDER BY id ASC'
        )->fetchAll();
        self::assertCount(2, $events);
        self::assertSame('request_review', $events[0]['transition'] ?? '');
        self::assertSame('approve', $events[1]['transition'] ?? '');
        self::assertSame('admin@example.test', $events[1]['created_by'] ?? '');
    }

    public function testListsTransitionEventsForCandidateInOrder(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $this->seedProject($app, 'OTHER');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $emptyEvents = app_pdo_list_no_code_publish_candidate_transition_events(
            $app,
            'SAMPLE28',
            'NO-CODE-RUNTIME',
            $revisionId,
        );
        self::assertTrue($emptyEvents['ok'], $emptyEvents['error']);
        self::assertSame([], $emptyEvents['items']);

        $requestReview = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'request_review',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'metadata' => [
                'ui_source' => 'repository-test',
            ],
        ]);
        self::assertTrue($requestReview['ok'], $requestReview['error']);

        $approve = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'approve',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($approve['ok'], $approve['error']);

        $events = app_pdo_list_no_code_publish_candidate_transition_events(
            $app,
            'SAMPLE28',
            'NO-CODE-RUNTIME',
            $revisionId,
        );
        self::assertTrue($events['ok'], $events['error']);
        self::assertCount(2, $events['items']);
        self::assertSame('request_review', $events['items'][0]['transition'] ?? '');
        self::assertSame('draft_candidate', $events['items'][0]['from_status'] ?? '');
        self::assertSame('review_requested', $events['items'][0]['to_status'] ?? '');
        self::assertSame(['ui_source' => 'repository-test'], $events['items'][0]['metadata'] ?? []);
        self::assertSame('approve', $events['items'][1]['transition'] ?? '');
        self::assertSame('admin@example.test', $events['items'][1]['created_by'] ?? '');

        $otherProjectEvents = app_pdo_list_no_code_publish_candidate_transition_events(
            $app,
            'OTHER',
            'NO-CODE-RUNTIME',
            $revisionId,
        );
        self::assertTrue($otherProjectEvents['ok'], $otherProjectEvents['error']);
        self::assertSame([], $otherProjectEvents['items']);
    }

    public function testApprovedArtifactLookupOnlyReturnsApprovedNoCodeRuntimeCandidates(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $draftLookup = app_pdo_find_approved_no_code_publish_candidate_for_artifact(
            $app,
            'SAMPLE28',
            '20260702-010203-abcdef12',
        );
        self::assertTrue($draftLookup['ok'], $draftLookup['error']);
        self::assertNull($draftLookup['item']);

        $requestReview = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'request_review',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($requestReview['ok'], $requestReview['error']);

        $approve = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'approve',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($approve['ok'], $approve['error']);

        $approvedLookup = app_pdo_find_approved_no_code_publish_candidate_for_artifact(
            $app,
            'SAMPLE28',
            '20260702-010203-abcdef12',
        );
        self::assertTrue($approvedLookup['ok'], $approvedLookup['error']);
        self::assertSame($revisionId, $approvedLookup['item']['revision_id'] ?? '');
        self::assertSame('approved', $approvedLookup['item']['status'] ?? '');

        $wrongArtifact = app_pdo_find_approved_no_code_publish_candidate_for_artifact(
            $app,
            'SAMPLE28',
            '20260702-020304-bcdefa23',
        );
        self::assertTrue($wrongArtifact['ok'], $wrongArtifact['error']);
        self::assertNull($wrongArtifact['item']);
    }

    public function testCurrentApprovedLookupReturnsLatestApprovedNoCodeRuntimeCandidate(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');

        $first = $this->createPublishableCandidate($app);
        $firstRevisionId = (string) ($first['item']['revision_id'] ?? '');
        $this->requestReviewAndApprove($app, $firstRevisionId);

        $second = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => array_merge($this->publishableReadinessSnapshot(), [
                'artifact_key' => '20260702-020304-bcdefa23',
                'screen_count' => 4,
            ]),
        ]);
        self::assertTrue($second['ok'], $second['error']);
        $secondRevisionId = (string) ($second['item']['revision_id'] ?? '');

        $draftCurrent = app_pdo_find_current_approved_no_code_publish_candidate($app, 'SAMPLE28');
        self::assertTrue($draftCurrent['ok'], $draftCurrent['error']);
        self::assertSame($firstRevisionId, $draftCurrent['item']['revision_id'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $draftCurrent['item']['artifact_key'] ?? '');

        $this->requestReviewAndApprove($app, $secondRevisionId);

        $current = app_pdo_find_current_approved_no_code_publish_candidate($app, 'SAMPLE28');
        self::assertTrue($current['ok'], $current['error']);
        self::assertSame($secondRevisionId, $current['item']['revision_id'] ?? '');
        self::assertSame('20260702-020304-bcdefa23', $current['item']['artifact_key'] ?? '');

        $selectFirst = app_pdo_select_current_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $firstRevisionId,
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($selectFirst['ok'], $selectFirst['error']);
        self::assertSame($firstRevisionId, $selectFirst['item']['revision_id'] ?? '');

        $selectedCurrent = app_pdo_find_current_approved_no_code_publish_candidate($app, 'SAMPLE28');
        self::assertTrue($selectedCurrent['ok'], $selectedCurrent['error']);
        self::assertSame($firstRevisionId, $selectedCurrent['item']['revision_id'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $selectedCurrent['item']['artifact_key'] ?? '');

        $otherProject = app_pdo_find_current_approved_no_code_publish_candidate($app, 'OTHER');
        self::assertFalse($otherProject['ok']);
        self::assertStringContainsString('project was not found', $otherProject['error']);
    }

    public function testCurrentPublicRevisionSelectionRequiresApprovedCandidateAndOperatorActor(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $draftSelection = app_pdo_select_current_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($draftSelection['ok']);
        self::assertStringContainsString('approved publish candidate', $draftSelection['error']);

        $this->requestReviewAndApprove($app, $revisionId);

        $viewerSelection = app_pdo_select_current_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'actor' => [
                'id' => 'viewer@example.test',
                'roles' => ['viewer'],
            ],
        ]);
        self::assertFalse($viewerSelection['ok']);
        self::assertStringContainsString('operator or admin actor', $viewerSelection['error']);

        $wrongSource = app_pdo_select_current_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'DBACCESS-PHP',
            'revision_id' => $revisionId,
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($wrongSource['ok']);
        self::assertStringContainsString('NO-CODE-RUNTIME', $wrongSource['error']);
    }

    public function testPublicRuntimeAliasSelectionFindsApprovedCandidateByAlias(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');

        $first = $this->createPublishableCandidate($app);
        $firstRevisionId = (string) ($first['item']['revision_id'] ?? '');
        $this->requestReviewAndApprove($app, $firstRevisionId);

        $second = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => array_merge($this->publishableReadinessSnapshot(), [
                'artifact_key' => '20260702-020304-bcdefa23',
                'screen_count' => 4,
            ]),
        ]);
        self::assertTrue($second['ok'], $second['error']);
        $secondRevisionId = (string) ($second['item']['revision_id'] ?? '');
        $this->requestReviewAndApprove($app, $secondRevisionId);

        self::assertFalse(app_no_code_public_runtime_alias_key_is_valid('current'));
        self::assertTrue(app_no_code_public_runtime_alias_key_is_valid('stable'));
        self::assertSame('stable', app_no_code_public_runtime_normalize_alias_key(' Stable '));

        $selectFirst = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $firstRevisionId,
            'alias_key' => ' Stable ',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($selectFirst['ok'], $selectFirst['error']);
        self::assertSame($firstRevisionId, $selectFirst['item']['revision_id'] ?? '');

        $aliasLookup = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, 'SAMPLE28', 'stable');
        self::assertTrue($aliasLookup['ok'], $aliasLookup['error']);
        self::assertSame($firstRevisionId, $aliasLookup['item']['revision_id'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $aliasLookup['item']['artifact_key'] ?? '');

        $selectSecond = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $secondRevisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($selectSecond['ok'], $selectSecond['error']);

        $updatedAliasLookup = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, 'SAMPLE28', 'stable');
        self::assertTrue($updatedAliasLookup['ok'], $updatedAliasLookup['error']);
        self::assertSame($secondRevisionId, $updatedAliasLookup['item']['revision_id'] ?? '');
        self::assertSame('20260702-020304-bcdefa23', $updatedAliasLookup['item']['artifact_key'] ?? '');

        $aliases = app_pdo_list_no_code_public_runtime_aliases_for_source_output($app, 'SAMPLE28');
        self::assertTrue($aliases['ok'], $aliases['error']);
        self::assertCount(1, $aliases['items']);
        self::assertSame('stable', $aliases['items'][0]['alias_key'] ?? '');
        self::assertSame($secondRevisionId, $aliases['items'][0]['revision_id'] ?? '');
        self::assertSame('approved', $aliases['items'][0]['candidate_status'] ?? '');

        $missingAlias = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, 'SAMPLE28', 'preview');
        self::assertTrue($missingAlias['ok'], $missingAlias['error']);
        self::assertNull($missingAlias['item']);
    }

    public function testPublicRuntimeAliasSelectionRequiresApprovedCandidateValidAliasAndOperatorActor(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $draftSelection = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($draftSelection['ok']);
        self::assertStringContainsString('approved publish candidate', $draftSelection['error']);

        $this->requestReviewAndApprove($app, $revisionId);

        $reservedAlias = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'alias_key' => 'current',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($reservedAlias['ok']);
        self::assertStringContainsString('alias key', $reservedAlias['error']);

        $viewerSelection = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'viewer@example.test',
                'roles' => ['viewer'],
            ],
        ]);
        self::assertFalse($viewerSelection['ok']);
        self::assertStringContainsString('operator or admin actor', $viewerSelection['error']);

        $wrongSource = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'DBACCESS-PHP',
            'revision_id' => $revisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($wrongSource['ok']);
        self::assertStringContainsString('NO-CODE-RUNTIME', $wrongSource['error']);
    }

    public function testPublicRuntimeAliasDeletionRemovesAliasAndRequiresOperatorActor(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');
        $this->requestReviewAndApprove($app, $revisionId);

        $select = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($select['ok'], $select['error']);

        $viewerDelete = app_pdo_delete_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'viewer@example.test',
                'roles' => ['viewer'],
            ],
        ]);
        self::assertFalse($viewerDelete['ok']);
        self::assertStringContainsString('operator or admin actor', $viewerDelete['error']);

        $delete = app_pdo_delete_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($delete['ok'], $delete['error']);
        self::assertSame('stable', $delete['alias_key']);

        $aliasLookup = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, 'SAMPLE28', 'stable');
        self::assertTrue($aliasLookup['ok'], $aliasLookup['error']);
        self::assertNull($aliasLookup['item']);

        $aliases = app_pdo_list_no_code_public_runtime_aliases_for_source_output($app, 'SAMPLE28');
        self::assertTrue($aliases['ok'], $aliases['error']);
        self::assertSame([], $aliases['items']);

        $missingDelete = app_pdo_delete_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($missingDelete['ok']);
        self::assertStringContainsString('not found', $missingDelete['error']);
    }

    public function testPublicRuntimeAliasLifecycleEventsRecordCreateUpdateAndDelete(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');

        $first = $this->createPublishableCandidate($app);
        $firstRevisionId = (string) ($first['item']['revision_id'] ?? '');
        $this->requestReviewAndApprove($app, $firstRevisionId);

        $second = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => array_merge($this->publishableReadinessSnapshot(), [
                'artifact_key' => '20260702-020304-bcdefa23',
            ]),
        ]);
        self::assertTrue($second['ok'], $second['error']);
        $secondRevisionId = (string) ($second['item']['revision_id'] ?? '');
        $this->requestReviewAndApprove($app, $secondRevisionId);

        $createAlias = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $firstRevisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($createAlias['ok'], $createAlias['error']);

        $updateAlias = app_pdo_set_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $secondRevisionId,
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($updateAlias['ok'], $updateAlias['error']);

        $deleteAlias = app_pdo_delete_no_code_public_runtime_alias($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'alias_key' => 'stable',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($deleteAlias['ok'], $deleteAlias['error']);

        $events = app_pdo_list_no_code_public_runtime_alias_events_for_source_output($app, 'SAMPLE28');
        self::assertTrue($events['ok'], $events['error']);
        self::assertCount(3, $events['items']);
        self::assertSame(['alias_deleted', 'alias_updated', 'alias_created'], array_column($events['items'], 'event_type'));
        self::assertSame('stable', $events['items'][0]['alias_key'] ?? '');
        self::assertSame($secondRevisionId, $events['items'][0]['revision_id'] ?? '');
        self::assertSame('20260702-020304-bcdefa23', $events['items'][0]['artifact_key'] ?? '');
        self::assertSame('admin@example.test', $events['items'][0]['created_by'] ?? '');
        self::assertSame('project-source-output-detail', $events['items'][0]['metadata']['ui_source'] ?? '');
        self::assertSame($firstRevisionId, $events['items'][2]['revision_id'] ?? '');

        $otherProjectEvents = app_pdo_list_no_code_public_runtime_alias_events_for_source_output($app, 'OTHER');
        self::assertFalse($otherProjectEvents['ok']);
        self::assertStringContainsString('project was not found', $otherProjectEvents['error']);
    }

    public function testRejectTransitionRequiresReasonAndReviewState(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $directReject = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'reject',
            'reason' => 'Not ready',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertFalse($directReject['ok']);
        self::assertStringContainsString('not allowed', $directReject['error']);

        $requestReview = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'request_review',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($requestReview['ok'], $requestReview['error']);

        $missingReason = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'reject',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertFalse($missingReason['ok']);
        self::assertStringContainsString('requires a reason', $missingReason['error']);

        $reject = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'reject',
            'reason' => 'Needs copy review.',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($reject['ok'], $reject['error']);
        self::assertSame('rejected', $reject['item']['status'] ?? '');
        self::assertSame('Needs copy review.', $reject['event']['reason'] ?? '');
    }

    public function testTransitionsFailClosedForStatusMismatchAndViewerActor(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app, 'SAMPLE28');
        $create = $this->createPublishableCandidate($app);
        $revisionId = (string) ($create['item']['revision_id'] ?? '');

        $statusMismatch = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'approve',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertFalse($statusMismatch['ok']);
        self::assertStringContainsString('expected_status', $statusMismatch['error']);

        $viewer = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'request_review',
            'actor' => [
                'id' => 'viewer@example.test',
                'roles' => ['viewer'],
            ],
        ]);
        self::assertFalse($viewer['ok']);
        self::assertStringContainsString('operator or admin actor', $viewer['error']);

        $find = app_pdo_find_no_code_publish_candidate($app, 'SAMPLE28', 'NO-CODE-RUNTIME', $revisionId);
        self::assertTrue($find['ok'], $find['error']);
        self::assertSame('draft_candidate', $find['item']['status'] ?? '');
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-no-code-publish-candidate-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            $storeDir,
        );
        $app = [
            'site' => 'admin',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        return $app;
    }

    private function seedProject(array $app, string $projectKey): void
    {
        $insert = app_pdo_insert_project($app, [
            'project_key' => $projectKey,
            'name' => $projectKey . ' Publish Candidate Test',
            'slug' => strtolower($projectKey) . '-publish-candidate-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'publish candidate repository sqlite smoke',
        ]);
        self::assertTrue($insert['ok'], $insert['error']);
    }

    /**
     * @return array{ok:bool,item:array<string,mixed>|null,error:string}
     */
    private function createPublishableCandidate(array $app): array
    {
        $create = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
            'readiness_snapshot' => $this->publishableReadinessSnapshot(),
        ]);
        self::assertTrue($create['ok'], $create['error']);

        return $create;
    }

    private function requestReviewAndApprove(array $app, string $revisionId): void
    {
        $requestReview = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'draft_candidate',
            'transition' => 'request_review',
            'actor' => [
                'id' => 'operator@example.test',
                'roles' => ['operator'],
            ],
        ]);
        self::assertTrue($requestReview['ok'], $requestReview['error']);

        $approve = app_pdo_transition_no_code_publish_candidate($app, [
            'project_key' => 'SAMPLE28',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'revision_id' => $revisionId,
            'expected_status' => 'review_requested',
            'transition' => 'approve',
            'actor' => [
                'id' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ]);
        self::assertTrue($approve['ok'], $approve['error']);
    }

    /**
     * @return array<string,mixed>
     */
    private function publishableReadinessSnapshot(): array
    {
        return [
            'state' => 'publishable',
            'label' => 'Publish candidate ready',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'source_output_dir' => 'work/source-outputs/SAMPLE28/NO-CODE-RUNTIME',
            'artifact_key' => '20260702-010203-abcdef12',
            'artifact_archive_exists' => true,
            'preview_files_ready' => true,
            'screen_count' => 3,
            'action_count' => 2,
            'blocking_reasons' => [],
        ];
    }
}
