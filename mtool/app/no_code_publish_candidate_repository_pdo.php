<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/no_code_operator_inspection.php';

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_create_no_code_publish_candidate_from_readiness_snapshot(array $app, array $input): array
{
    try {
        $projectKey = app_no_code_publish_candidate_normalize_project_key((string) ($input['project_key'] ?? ''));
        $snapshot = $input['readiness_snapshot'] ?? null;
        if ($projectKey === '') {
            throw new RuntimeException('publish candidate requires project_key.');
        }
        if (!is_array($snapshot) || $snapshot === []) {
            throw new RuntimeException('publish candidate requires readiness_snapshot.');
        }

        $sourceOutputKey = app_normalize_no_code_operator_source_output_key(
            (string) ($input['source_output_key'] ?? ($snapshot['source_output_key'] ?? '')),
        );
        if ($sourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('publish candidate only accepts NO-CODE-RUNTIME source output.');
        }

        $snapshotSourceOutputKey = app_normalize_no_code_operator_source_output_key((string) ($snapshot['source_output_key'] ?? ''));
        if ($snapshotSourceOutputKey !== $sourceOutputKey) {
            throw new RuntimeException('publish candidate readiness snapshot source output does not match.');
        }

        $snapshotState = strtolower(trim((string) ($snapshot['state'] ?? '')));
        $expectedState = strtolower(trim((string) ($input['readiness_state'] ?? $snapshotState)));
        if ($expectedState !== $snapshotState) {
            throw new RuntimeException('publish candidate readiness state does not match.');
        }
        if ($snapshotState !== 'publishable') {
            throw new RuntimeException('publish candidate requires a publishable readiness snapshot.');
        }

        $artifactKey = trim((string) ($input['artifact_key'] ?? ($snapshot['artifact_key'] ?? '')));
        $snapshotArtifactKey = trim((string) ($snapshot['artifact_key'] ?? ''));
        if ($artifactKey === '' || $snapshotArtifactKey === '') {
            throw new RuntimeException('publish candidate requires artifact_key.');
        }
        if ($artifactKey !== $snapshotArtifactKey) {
            throw new RuntimeException('publish candidate artifact key does not match readiness snapshot.');
        }

        $actor = app_no_code_publish_candidate_actor($input['actor'] ?? null);
        if (!app_no_code_publish_candidate_actor_can_create($actor)) {
            throw new RuntimeException('publish candidate creation requires an operator or admin actor.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $projectKey);
        $revisionId = app_no_code_publish_candidate_new_revision_id();
        $blockingReasons = app_no_code_publish_candidate_string_list($snapshot['blocking_reasons'] ?? []);

        $statement = $pdo->prepare(
            'INSERT INTO no_code_publish_candidate_revisions (
                project_id,
                revision_id,
                source_output_key,
                artifact_key,
                artifact_archive_path,
                artifact_checksum,
                readiness_state,
                readiness_label,
                screen_count,
                action_count,
                preview_files_ready,
                artifact_archive_exists,
                blocking_reasons_json,
                snapshot_json,
                status,
                created_by
            ) VALUES (
                :project_id,
                :revision_id,
                :source_output_key,
                :artifact_key,
                :artifact_archive_path,
                :artifact_checksum,
                :readiness_state,
                :readiness_label,
                :screen_count,
                :action_count,
                :preview_files_ready,
                :artifact_archive_exists,
                :blocking_reasons_json,
                :snapshot_json,
                :status,
                :created_by
            )'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':revision_id' => $revisionId,
            ':source_output_key' => $sourceOutputKey,
            ':artifact_key' => $artifactKey,
            ':artifact_archive_path' => trim((string) ($input['artifact_archive_path'] ?? ($snapshot['artifact_archive_path'] ?? ''))),
            ':artifact_checksum' => trim((string) ($input['artifact_checksum'] ?? ($snapshot['artifact_checksum'] ?? ''))),
            ':readiness_state' => $snapshotState,
            ':readiness_label' => trim((string) ($snapshot['label'] ?? '')),
            ':screen_count' => max(0, (int) ($snapshot['screen_count'] ?? 0)),
            ':action_count' => max(0, (int) ($snapshot['action_count'] ?? 0)),
            ':preview_files_ready' => !empty($snapshot['preview_files_ready']) ? 1 : 0,
            ':artifact_archive_exists' => !empty($snapshot['artifact_archive_exists']) ? 1 : 0,
            ':blocking_reasons_json' => app_no_code_publish_candidate_json_text($blockingReasons),
            ':snapshot_json' => app_no_code_publish_candidate_json_text($snapshot),
            ':status' => 'draft_candidate',
            ':created_by' => $actor['id'],
        ]);

        return app_pdo_find_no_code_publish_candidate($app, $projectKey, $sourceOutputKey, $revisionId);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_list_no_code_publish_candidates_for_source_output(
    array $app,
    string $projectKey,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        if ($normalizedProjectKey === '' || $normalizedSourceOutputKey === '') {
            throw new RuntimeException('publish candidate list requires project_key and source_output_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT *
             FROM no_code_publish_candidate_revisions
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
             ORDER BY created_at DESC, id DESC'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_no_code_publish_candidate_item_from_row($normalizedProjectKey, $row);
            }
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_list_no_code_publish_candidate_transition_events(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
    string $revisionId,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        $normalizedRevisionId = trim($revisionId);
        if ($normalizedProjectKey === '' || $normalizedSourceOutputKey === '' || $normalizedRevisionId === '') {
            throw new RuntimeException('publish candidate transition event list requires project_key, source_output_key, and revision_id.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT *
             FROM no_code_publish_candidate_transition_events
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
               AND revision_id = :revision_id
             ORDER BY created_at ASC, id ASC'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
            ':revision_id' => $normalizedRevisionId,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_no_code_publish_candidate_transition_event_from_row($row);
            }
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_find_no_code_publish_candidate(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
    string $revisionId,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        $normalizedRevisionId = trim($revisionId);
        if ($normalizedProjectKey === '' || $normalizedSourceOutputKey === '' || $normalizedRevisionId === '') {
            throw new RuntimeException('publish candidate lookup requires project_key, source_output_key, and revision_id.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT *
             FROM no_code_publish_candidate_revisions
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
               AND revision_id = :revision_id
             LIMIT 1'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
            ':revision_id' => $normalizedRevisionId,
        ]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_no_code_publish_candidate_item_from_row($normalizedProjectKey, $row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_find_approved_no_code_publish_candidate_for_artifact(
    array $app,
    string $projectKey,
    string $artifactKey,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        $normalizedArtifactKey = trim($artifactKey);
        if (
            $normalizedProjectKey === ''
            || $normalizedSourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY
            || $normalizedArtifactKey === ''
        ) {
            throw new RuntimeException('approved publish candidate lookup requires project_key and artifact_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT *
             FROM no_code_publish_candidate_revisions
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
               AND artifact_key = :artifact_key
               AND status = :status
             ORDER BY updated_at DESC, id DESC
             LIMIT 1'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
            ':artifact_key' => $normalizedArtifactKey,
            ':status' => 'approved',
        ]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_no_code_publish_candidate_item_from_row($normalizedProjectKey, $row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_find_current_approved_no_code_publish_candidate(
    array $app,
    string $projectKey,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        if ($normalizedProjectKey === '' || $normalizedSourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('current approved publish candidate lookup requires project_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $selectedStatement = $pdo->prepare(
            'SELECT candidate.*
             FROM no_code_public_runtime_current_revisions current_revision
             INNER JOIN no_code_publish_candidate_revisions candidate
                ON candidate.id = current_revision.candidate_revision_id
             WHERE current_revision.project_id = :project_id
               AND current_revision.source_output_key = :source_output_key
               AND candidate.project_id = :candidate_project_id
               AND candidate.source_output_key = :candidate_source_output_key
               AND candidate.status = :status
             LIMIT 1'
        );
        $selectedStatement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
            ':candidate_project_id' => $projectId,
            ':candidate_source_output_key' => $normalizedSourceOutputKey,
            ':status' => 'approved',
        ]);
        $selectedRow = $selectedStatement->fetch();
        if (is_array($selectedRow)) {
            return [
                'ok' => true,
                'item' => app_no_code_publish_candidate_item_from_row($normalizedProjectKey, $selectedRow),
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT *
             FROM no_code_publish_candidate_revisions
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
               AND status = :status
             ORDER BY updated_at DESC, id DESC
             LIMIT 1'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
            ':status' => 'approved',
        ]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_no_code_publish_candidate_item_from_row($normalizedProjectKey, $row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_no_code_public_runtime_normalize_alias_key(string $aliasKey): string
{
    return strtolower(trim($aliasKey));
}

function app_no_code_public_runtime_alias_key_is_valid(string $aliasKey): bool
{
    $normalized = app_no_code_public_runtime_normalize_alias_key($aliasKey);
    if ($normalized === 'current' || $normalized === 'alias') {
        return false;
    }

    return preg_match('/^[a-z0-9][a-z0-9-]{1,62}[a-z0-9]$/', $normalized) === 1;
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_find_approved_no_code_publish_candidate_for_alias(
    array $app,
    string $projectKey,
    string $aliasKey,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedAliasKey = app_no_code_public_runtime_normalize_alias_key($aliasKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        if (
            $normalizedProjectKey === ''
            || !app_no_code_public_runtime_alias_key_is_valid($normalizedAliasKey)
            || $normalizedSourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY
        ) {
            throw new RuntimeException('public runtime alias lookup requires project_key and alias_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT candidate.*
             FROM no_code_public_runtime_aliases runtime_alias
             INNER JOIN no_code_publish_candidate_revisions candidate
                ON candidate.id = runtime_alias.candidate_revision_id
             WHERE runtime_alias.project_id = :project_id
               AND runtime_alias.alias_key = :alias_key
               AND runtime_alias.source_output_key = :source_output_key
               AND candidate.project_id = :candidate_project_id
               AND candidate.source_output_key = :candidate_source_output_key
               AND candidate.status = :status
             LIMIT 1'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':alias_key' => $normalizedAliasKey,
            ':source_output_key' => $normalizedSourceOutputKey,
            ':candidate_project_id' => $projectId,
            ':candidate_source_output_key' => $normalizedSourceOutputKey,
            ':status' => 'approved',
        ]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_no_code_publish_candidate_item_from_row($normalizedProjectKey, $row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_list_no_code_public_runtime_aliases_for_source_output(
    array $app,
    string $projectKey,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        if ($normalizedProjectKey === '' || $normalizedSourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('public runtime alias list requires project_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT runtime_alias.*, candidate.status AS candidate_status
             FROM no_code_public_runtime_aliases runtime_alias
             LEFT JOIN no_code_publish_candidate_revisions candidate
                ON candidate.id = runtime_alias.candidate_revision_id
             WHERE runtime_alias.project_id = :project_id
               AND runtime_alias.source_output_key = :source_output_key
             ORDER BY runtime_alias.alias_key ASC'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'project_key' => $normalizedProjectKey,
                'alias_key' => (string) ($row['alias_key'] ?? ''),
                'source_output_key' => app_normalize_no_code_operator_source_output_key((string) ($row['source_output_key'] ?? '')),
                'revision_id' => (string) ($row['revision_id'] ?? ''),
                'artifact_key' => (string) ($row['artifact_key'] ?? ''),
                'selected_by' => (string) ($row['selected_by'] ?? ''),
                'candidate_status' => app_no_code_publish_candidate_normalize_status((string) ($row['candidate_status'] ?? '')),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ];
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_list_no_code_public_runtime_alias_events_for_source_output(
    array $app,
    string $projectKey,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    try {
        $normalizedProjectKey = app_no_code_publish_candidate_normalize_project_key($projectKey);
        $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
        if ($normalizedProjectKey === '' || $normalizedSourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('public runtime alias event list requires project_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $normalizedProjectKey);
        $statement = $pdo->prepare(
            'SELECT *
             FROM no_code_public_runtime_alias_events
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
             ORDER BY created_at DESC, id DESC'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $normalizedSourceOutputKey,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_no_code_public_runtime_alias_event_from_row($normalizedProjectKey, $row);
            }
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_set_no_code_public_runtime_alias(array $app, array $input): array
{
    $pdo = null;
    try {
        $projectKey = app_no_code_publish_candidate_normalize_project_key((string) ($input['project_key'] ?? ''));
        $sourceOutputKey = app_normalize_no_code_operator_source_output_key((string) ($input['source_output_key'] ?? ''));
        $revisionId = trim((string) ($input['revision_id'] ?? ''));
        $aliasKey = app_no_code_public_runtime_normalize_alias_key((string) ($input['alias_key'] ?? ''));
        if ($projectKey === '' || $sourceOutputKey === '' || $revisionId === '' || $aliasKey === '') {
            throw new RuntimeException('public runtime alias selection requires project_key, source_output_key, revision_id, and alias_key.');
        }
        if ($sourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('public runtime alias selection only accepts NO-CODE-RUNTIME source output.');
        }
        if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
            throw new RuntimeException('public runtime alias key must be 3-64 lowercase letters, digits, or hyphens, and cannot be current or alias.');
        }

        $actor = app_no_code_publish_candidate_actor($input['actor'] ?? null);
        if (!app_no_code_publish_candidate_actor_can_transition($actor)) {
            throw new RuntimeException('public runtime alias selection requires an operator or admin actor.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $projectKey);
        $pdo->beginTransaction();

        $row = app_no_code_publish_candidate_find_row_for_update($pdo, $projectId, $sourceOutputKey, $revisionId);
        if ($row === null) {
            throw new RuntimeException('publish candidate was not found.');
        }

        $status = app_no_code_publish_candidate_normalize_status((string) ($row['status'] ?? ''));
        if ($status !== 'approved') {
            throw new RuntimeException('public runtime alias selection requires an approved publish candidate.');
        }

        $existingSelection = $pdo->prepare(
            'SELECT id
             FROM no_code_public_runtime_aliases
             WHERE project_id = :project_id
               AND alias_key = :alias_key
             LIMIT 1'
        );
        $existingSelection->execute([
            ':project_id' => $projectId,
            ':alias_key' => $aliasKey,
        ]);
        $selectionId = (int) ($existingSelection->fetchColumn() ?: 0);
        $eventType = $selectionId > 0 ? 'alias_updated' : 'alias_created';

        if ($selectionId > 0) {
            $update = $pdo->prepare(
                'UPDATE no_code_public_runtime_aliases
                 SET source_output_key = :source_output_key,
                     candidate_revision_id = :candidate_revision_id,
                     revision_id = :revision_id,
                     artifact_key = :artifact_key,
                     selected_by = :selected_by,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $update->execute([
                ':source_output_key' => $sourceOutputKey,
                ':candidate_revision_id' => (int) ($row['id'] ?? 0),
                ':revision_id' => $revisionId,
                ':artifact_key' => (string) ($row['artifact_key'] ?? ''),
                ':selected_by' => $actor['id'],
                ':id' => $selectionId,
            ]);
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO no_code_public_runtime_aliases (
                    project_id,
                    alias_key,
                    source_output_key,
                    candidate_revision_id,
                    revision_id,
                    artifact_key,
                    selected_by
                ) VALUES (
                    :project_id,
                    :alias_key,
                    :source_output_key,
                    :candidate_revision_id,
                    :revision_id,
                    :artifact_key,
                    :selected_by
                )'
            );
            $insert->execute([
                ':project_id' => $projectId,
                ':alias_key' => $aliasKey,
                ':source_output_key' => $sourceOutputKey,
                ':candidate_revision_id' => (int) ($row['id'] ?? 0),
                ':revision_id' => $revisionId,
                ':artifact_key' => (string) ($row['artifact_key'] ?? ''),
                ':selected_by' => $actor['id'],
            ]);
        }

        app_no_code_public_runtime_alias_insert_event($pdo, [
            'project_id' => $projectId,
            'alias_key' => $aliasKey,
            'source_output_key' => $sourceOutputKey,
            'candidate_revision_id' => (int) ($row['id'] ?? 0),
            'revision_id' => $revisionId,
            'artifact_key' => (string) ($row['artifact_key'] ?? ''),
            'event_type' => $eventType,
            'created_by' => $actor['id'],
            'metadata' => [
                'ui_source' => 'project-source-output-detail',
            ],
        ]);

        $pdo->commit();

        return app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey, $sourceOutputKey);
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_select_current_no_code_publish_candidate(array $app, array $input): array
{
    $pdo = null;
    try {
        $projectKey = app_no_code_publish_candidate_normalize_project_key((string) ($input['project_key'] ?? ''));
        $sourceOutputKey = app_normalize_no_code_operator_source_output_key((string) ($input['source_output_key'] ?? ''));
        $revisionId = trim((string) ($input['revision_id'] ?? ''));
        if ($projectKey === '' || $sourceOutputKey === '' || $revisionId === '') {
            throw new RuntimeException('current public revision selection requires project_key, source_output_key, and revision_id.');
        }
        if ($sourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('current public revision selection only accepts NO-CODE-RUNTIME source output.');
        }

        $actor = app_no_code_publish_candidate_actor($input['actor'] ?? null);
        if (!app_no_code_publish_candidate_actor_can_transition($actor)) {
            throw new RuntimeException('current public revision selection requires an operator or admin actor.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $projectKey);
        $pdo->beginTransaction();

        $row = app_no_code_publish_candidate_find_row_for_update($pdo, $projectId, $sourceOutputKey, $revisionId);
        if ($row === null) {
            throw new RuntimeException('publish candidate was not found.');
        }

        $status = app_no_code_publish_candidate_normalize_status((string) ($row['status'] ?? ''));
        if ($status !== 'approved') {
            throw new RuntimeException('current public revision selection requires an approved publish candidate.');
        }

        $existingSelection = $pdo->prepare(
            'SELECT id
             FROM no_code_public_runtime_current_revisions
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
             LIMIT 1'
        );
        $existingSelection->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $sourceOutputKey,
        ]);
        $selectionId = (int) ($existingSelection->fetchColumn() ?: 0);

        if ($selectionId > 0) {
            $update = $pdo->prepare(
                'UPDATE no_code_public_runtime_current_revisions
                 SET candidate_revision_id = :candidate_revision_id,
                     revision_id = :revision_id,
                     artifact_key = :artifact_key,
                     selected_by = :selected_by,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $update->execute([
                ':candidate_revision_id' => (int) ($row['id'] ?? 0),
                ':revision_id' => $revisionId,
                ':artifact_key' => (string) ($row['artifact_key'] ?? ''),
                ':selected_by' => $actor['id'],
                ':id' => $selectionId,
            ]);
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO no_code_public_runtime_current_revisions (
                    project_id,
                    source_output_key,
                    candidate_revision_id,
                    revision_id,
                    artifact_key,
                    selected_by
                ) VALUES (
                    :project_id,
                    :source_output_key,
                    :candidate_revision_id,
                    :revision_id,
                    :artifact_key,
                    :selected_by
                )'
            );
            $insert->execute([
                ':project_id' => $projectId,
                ':source_output_key' => $sourceOutputKey,
                ':candidate_revision_id' => (int) ($row['id'] ?? 0),
                ':revision_id' => $revisionId,
                ':artifact_key' => (string) ($row['artifact_key'] ?? ''),
                ':selected_by' => $actor['id'],
            ]);
        }

        $pdo->commit();

        return app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey, $sourceOutputKey);
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,alias_key:string,error:string}
 */
function app_pdo_delete_no_code_public_runtime_alias(array $app, array $input): array
{
    $pdo = null;
    try {
        $projectKey = app_no_code_publish_candidate_normalize_project_key((string) ($input['project_key'] ?? ''));
        $sourceOutputKey = app_normalize_no_code_operator_source_output_key((string) ($input['source_output_key'] ?? ''));
        $aliasKey = app_no_code_public_runtime_normalize_alias_key((string) ($input['alias_key'] ?? ''));
        if ($projectKey === '' || $sourceOutputKey === '' || $aliasKey === '') {
            throw new RuntimeException('public runtime alias deletion requires project_key, source_output_key, and alias_key.');
        }
        if ($sourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('public runtime alias deletion only accepts NO-CODE-RUNTIME source output.');
        }
        if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
            throw new RuntimeException('public runtime alias key is invalid.');
        }

        $actor = app_no_code_publish_candidate_actor($input['actor'] ?? null);
        if (!app_no_code_publish_candidate_actor_can_transition($actor)) {
            throw new RuntimeException('public runtime alias deletion requires an operator or admin actor.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $projectKey);
        $pdo->beginTransaction();

        $existing = $pdo->prepare(
            'SELECT *
             FROM no_code_public_runtime_aliases
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
               AND alias_key = :alias_key
             LIMIT 1'
        );
        $existing->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $sourceOutputKey,
            ':alias_key' => $aliasKey,
        ]);
        $aliasRow = $existing->fetch();
        if (!is_array($aliasRow)) {
            throw new RuntimeException('public runtime alias was not found.');
        }

        $statement = $pdo->prepare(
            'DELETE FROM no_code_public_runtime_aliases
             WHERE project_id = :project_id
               AND source_output_key = :source_output_key
               AND alias_key = :alias_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $sourceOutputKey,
            ':alias_key' => $aliasKey,
        ]);

        if ($statement->rowCount() < 1) {
            throw new RuntimeException('public runtime alias was not found.');
        }

        app_no_code_public_runtime_alias_insert_event($pdo, [
            'project_id' => $projectId,
            'alias_key' => $aliasKey,
            'source_output_key' => $sourceOutputKey,
            'candidate_revision_id' => (int) ($aliasRow['candidate_revision_id'] ?? 0),
            'revision_id' => (string) ($aliasRow['revision_id'] ?? ''),
            'artifact_key' => (string) ($aliasRow['artifact_key'] ?? ''),
            'event_type' => 'alias_deleted',
            'created_by' => $actor['id'],
            'metadata' => [
                'ui_source' => 'project-source-output-detail',
            ],
        ]);

        $pdo->commit();

        return [
            'ok' => true,
            'alias_key' => $aliasKey,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'alias_key' => '',
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>|null,event:array<string,mixed>|null,error:string}
 */
function app_pdo_transition_no_code_publish_candidate(array $app, array $input): array
{
    $pdo = null;
    try {
        $projectKey = app_no_code_publish_candidate_normalize_project_key((string) ($input['project_key'] ?? ''));
        $sourceOutputKey = app_normalize_no_code_operator_source_output_key((string) ($input['source_output_key'] ?? ''));
        $revisionId = trim((string) ($input['revision_id'] ?? ''));
        $transition = app_no_code_publish_candidate_normalize_transition((string) ($input['transition'] ?? ''));
        $expectedStatus = app_no_code_publish_candidate_normalize_status((string) ($input['expected_status'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));
        $metadata = is_array($input['metadata'] ?? null) ? $input['metadata'] : [];

        if ($projectKey === '' || $sourceOutputKey === '' || $revisionId === '') {
            throw new RuntimeException('publish candidate transition requires project_key, source_output_key, and revision_id.');
        }
        if ($sourceOutputKey !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
            throw new RuntimeException('publish candidate transition only accepts NO-CODE-RUNTIME source output.');
        }
        if ($transition === '') {
            throw new RuntimeException('publish candidate transition is not supported.');
        }

        $actor = app_no_code_publish_candidate_actor($input['actor'] ?? null);
        if (!app_no_code_publish_candidate_actor_can_transition($actor)) {
            throw new RuntimeException('publish candidate transition requires an operator or admin actor.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_no_code_publish_candidate_resolve_project_id($pdo, $projectKey);
        $pdo->beginTransaction();

        $row = app_no_code_publish_candidate_find_row_for_update($pdo, $projectId, $sourceOutputKey, $revisionId);
        if ($row === null) {
            throw new RuntimeException('publish candidate was not found.');
        }

        $fromStatus = app_no_code_publish_candidate_normalize_status((string) ($row['status'] ?? ''));
        if ($expectedStatus !== '' && $expectedStatus !== $fromStatus) {
            throw new RuntimeException('publish candidate status does not match expected_status.');
        }

        $toStatus = app_no_code_publish_candidate_transition_target_status($transition, $fromStatus);
        if ($toStatus === '') {
            throw new RuntimeException('publish candidate transition is not allowed from current status.');
        }
        if ($transition === 'reject' && $reason === '') {
            throw new RuntimeException('publish candidate reject transition requires a reason.');
        }

        $eventStatement = $pdo->prepare(
            'INSERT INTO no_code_publish_candidate_transition_events (
                candidate_revision_id,
                project_id,
                revision_id,
                source_output_key,
                transition,
                from_status,
                to_status,
                transition_reason,
                metadata_json,
                created_by
            ) VALUES (
                :candidate_revision_id,
                :project_id,
                :revision_id,
                :source_output_key,
                :transition,
                :from_status,
                :to_status,
                :transition_reason,
                :metadata_json,
                :created_by
            )'
        );
        $eventStatement->execute([
            ':candidate_revision_id' => (int) ($row['id'] ?? 0),
            ':project_id' => $projectId,
            ':revision_id' => $revisionId,
            ':source_output_key' => $sourceOutputKey,
            ':transition' => $transition,
            ':from_status' => $fromStatus,
            ':to_status' => $toStatus,
            ':transition_reason' => $reason,
            ':metadata_json' => app_no_code_publish_candidate_json_text($metadata),
            ':created_by' => $actor['id'],
        ]);
        $eventId = (int) $pdo->lastInsertId();

        $updateStatement = $pdo->prepare(
            'UPDATE no_code_publish_candidate_revisions
             SET status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND status = :from_status'
        );
        $updateStatement->execute([
            ':status' => $toStatus,
            ':id' => (int) ($row['id'] ?? 0),
            ':from_status' => $fromStatus,
        ]);
        if ($updateStatement->rowCount() !== 1) {
            throw new RuntimeException('publish candidate status update failed.');
        }

        $pdo->commit();

        $find = app_pdo_find_no_code_publish_candidate($app, $projectKey, $sourceOutputKey, $revisionId);
        return [
            'ok' => $find['ok'],
            'item' => $find['item'],
            'event' => [
                'id' => $eventId,
                'transition' => $transition,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $reason,
                'created_by' => $actor['id'],
            ],
            'error' => $find['error'],
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'item' => null,
            'event' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_no_code_publish_candidate_normalize_project_key(string $projectKey): string
{
    return app_normalize_no_code_operator_project_key($projectKey);
}

function app_no_code_publish_candidate_new_revision_id(): string
{
    return date('Ymd-His') . '-' . bin2hex(random_bytes(8));
}

function app_no_code_publish_candidate_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $statement = $pdo->prepare('SELECT id FROM projects WHERE project_key = :project_key LIMIT 1');
    $statement->execute([':project_key' => $projectKey]);
    $projectId = (int) ($statement->fetchColumn() ?: 0);
    if ($projectId <= 0) {
        throw new RuntimeException('project was not found.');
    }

    return $projectId;
}

/**
 * @return array<string,mixed>|null
 */
function app_no_code_publish_candidate_find_row_for_update(
    PDO $pdo,
    int $projectId,
    string $sourceOutputKey,
    string $revisionId,
): ?array {
    $statement = $pdo->prepare(
        'SELECT *
         FROM no_code_publish_candidate_revisions
         WHERE project_id = :project_id
           AND source_output_key = :source_output_key
           AND revision_id = :revision_id
         LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':source_output_key' => $sourceOutputKey,
        ':revision_id' => $revisionId,
    ]);
    $row = $statement->fetch();

    return is_array($row) ? $row : null;
}

/**
 * @param mixed $actorInput
 * @return array{id:string,roles:list<string>}
 */
function app_no_code_publish_candidate_actor(mixed $actorInput): array
{
    if (is_string($actorInput)) {
        return [
            'id' => trim($actorInput),
            'roles' => [],
        ];
    }
    if (!is_array($actorInput)) {
        return [
            'id' => '',
            'roles' => [],
        ];
    }

    $id = trim((string) ($actorInput['id'] ?? ($actorInput['login_id'] ?? ($actorInput['email'] ?? ''))));
    $roles = app_no_code_publish_candidate_string_list($actorInput['roles'] ?? []);

    return [
        'id' => $id,
        'roles' => array_map(static fn (string $role): string => strtolower(trim($role)), $roles),
    ];
}

/**
 * @param array{id:string,roles:list<string>} $actor
 */
function app_no_code_publish_candidate_actor_can_create(array $actor): bool
{
    if ($actor['id'] === '') {
        return false;
    }

    return array_intersect($actor['roles'], ['operator', 'admin']) !== [];
}

/**
 * @param array{id:string,roles:list<string>} $actor
 */
function app_no_code_publish_candidate_actor_can_transition(array $actor): bool
{
    return app_no_code_publish_candidate_actor_can_create($actor);
}

function app_no_code_publish_candidate_normalize_status(string $status): string
{
    return strtolower(trim($status));
}

function app_no_code_publish_candidate_normalize_transition(string $transition): string
{
    $normalized = strtolower(trim($transition));

    return in_array($normalized, ['request_review', 'approve', 'reject'], true) ? $normalized : '';
}

function app_no_code_publish_candidate_transition_target_status(string $transition, string $fromStatus): string
{
    if ($transition === 'request_review' && $fromStatus === 'draft_candidate') {
        return 'review_requested';
    }
    if ($transition === 'approve' && $fromStatus === 'review_requested') {
        return 'approved';
    }
    if ($transition === 'reject' && $fromStatus === 'review_requested') {
        return 'rejected';
    }

    return '';
}

/**
 * @param mixed $value
 * @return list<string>
 */
function app_no_code_publish_candidate_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        $text = trim((string) $item);
        if ($text !== '') {
            $items[] = $text;
        }
    }

    return array_values(array_unique($items));
}

/**
 * @param array<string,mixed>|list<string> $value
 */
function app_no_code_publish_candidate_json_text(array $value): string
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('publish candidate JSON generation failed.');
    }

    return $json;
}

/**
 * @return array<string,mixed>
 */
function app_no_code_publish_candidate_json_array(string $json): array
{
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * @param array{
 *     project_id:int,
 *     alias_key:string,
 *     source_output_key:string,
 *     candidate_revision_id:int,
 *     revision_id:string,
 *     artifact_key:string,
 *     event_type:string,
 *     created_by:string,
 *     metadata:array<string,mixed>
 * } $event
 */
function app_no_code_public_runtime_alias_insert_event(PDO $pdo, array $event): void
{
    $statement = $pdo->prepare(
        'INSERT INTO no_code_public_runtime_alias_events (
            project_id,
            alias_key,
            source_output_key,
            candidate_revision_id,
            revision_id,
            artifact_key,
            event_type,
            created_by,
            metadata_json
        ) VALUES (
            :project_id,
            :alias_key,
            :source_output_key,
            :candidate_revision_id,
            :revision_id,
            :artifact_key,
            :event_type,
            :created_by,
            :metadata_json
        )'
    );
    $candidateRevisionId = max(0, (int) $event['candidate_revision_id']);
    $statement->execute([
        ':project_id' => (int) $event['project_id'],
        ':alias_key' => app_no_code_public_runtime_normalize_alias_key((string) $event['alias_key']),
        ':source_output_key' => app_normalize_no_code_operator_source_output_key((string) $event['source_output_key']),
        ':candidate_revision_id' => $candidateRevisionId > 0 ? $candidateRevisionId : null,
        ':revision_id' => trim((string) $event['revision_id']),
        ':artifact_key' => trim((string) $event['artifact_key']),
        ':event_type' => trim((string) $event['event_type']),
        ':created_by' => trim((string) $event['created_by']),
        ':metadata_json' => app_no_code_publish_candidate_json_text($event['metadata']),
    ]);
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_no_code_public_runtime_alias_event_from_row(string $projectKey, array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'project_key' => $projectKey,
        'alias_key' => (string) ($row['alias_key'] ?? ''),
        'source_output_key' => (string) ($row['source_output_key'] ?? ''),
        'candidate_revision_id' => (int) ($row['candidate_revision_id'] ?? 0),
        'revision_id' => (string) ($row['revision_id'] ?? ''),
        'artifact_key' => (string) ($row['artifact_key'] ?? ''),
        'event_type' => (string) ($row['event_type'] ?? ''),
        'created_by' => (string) ($row['created_by'] ?? ''),
        'metadata' => app_no_code_publish_candidate_json_array((string) ($row['metadata_json'] ?? '{}')),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_no_code_publish_candidate_item_from_row(string $projectKey, array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'project_key' => $projectKey,
        'revision_id' => (string) ($row['revision_id'] ?? ''),
        'source_output_key' => (string) ($row['source_output_key'] ?? ''),
        'artifact_key' => (string) ($row['artifact_key'] ?? ''),
        'artifact_archive_path' => (string) ($row['artifact_archive_path'] ?? ''),
        'artifact_checksum' => (string) ($row['artifact_checksum'] ?? ''),
        'readiness_state' => (string) ($row['readiness_state'] ?? ''),
        'readiness_label' => (string) ($row['readiness_label'] ?? ''),
        'screen_count' => (int) ($row['screen_count'] ?? 0),
        'action_count' => (int) ($row['action_count'] ?? 0),
        'preview_files_ready' => ((int) ($row['preview_files_ready'] ?? 0)) === 1,
        'artifact_archive_exists' => ((int) ($row['artifact_archive_exists'] ?? 0)) === 1,
        'blocking_reasons' => app_no_code_publish_candidate_string_list(
            app_no_code_publish_candidate_json_array((string) ($row['blocking_reasons_json'] ?? '[]')),
        ),
        'snapshot' => app_no_code_publish_candidate_json_array((string) ($row['snapshot_json'] ?? '{}')),
        'status' => (string) ($row['status'] ?? ''),
        'created_by' => (string) ($row['created_by'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_no_code_publish_candidate_transition_event_from_row(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'candidate_revision_id' => (int) ($row['candidate_revision_id'] ?? 0),
        'revision_id' => (string) ($row['revision_id'] ?? ''),
        'source_output_key' => (string) ($row['source_output_key'] ?? ''),
        'transition' => (string) ($row['transition'] ?? ''),
        'from_status' => (string) ($row['from_status'] ?? ''),
        'to_status' => (string) ($row['to_status'] ?? ''),
        'reason' => (string) ($row['transition_reason'] ?? ''),
        'metadata' => app_no_code_publish_candidate_json_array((string) ($row['metadata_json'] ?? '{}')),
        'created_by' => (string) ($row['created_by'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}
