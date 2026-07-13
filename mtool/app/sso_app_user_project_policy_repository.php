<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/sso_app_user_project_policy.php';

/** @return array{ok:bool,item:array<string,mixed>|null,error:string} */
function app_fetch_sso_app_user_project_policy(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_sso_app_user_project_policy_resolve_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            throw new RuntimeException('project not found.');
        }
        $statement = $pdo->prepare(
            'SELECT contract_version, enabled, policy_json, source_of_truth, created_at, updated_at
             FROM project_app_user_policies
             WHERE project_id = :project_id
             LIMIT 1',
        );
        $statement->execute([':project_id' => $projectId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return ['ok' => true, 'item' => null, 'error' => ''];
        }
        $decoded = json_decode((string) ($row['policy_json'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('stored SSO app-user policy is not an object.');
        }
        $normalized = app_sso_app_user_project_policy_normalize($decoded);
        if (!$normalized['ok']) {
            throw new RuntimeException('stored SSO app-user policy is invalid: ' . implode(' ', $normalized['errors']));
        }

        return [
            'ok' => true,
            'item' => [
                'policy' => $normalized['policy'],
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'item' => null, 'error' => $throwable->getMessage()];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>|null,warnings:list<string>,error:string}
 */
function app_upsert_sso_app_user_project_policy(
    array $app,
    string $projectKey,
    array $input,
    string $sourceOfTruth = 'manual',
): array {
    $normalized = app_sso_app_user_project_policy_normalize($input);
    if (!$normalized['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'warnings' => $normalized['warnings'],
            'error' => implode(' ', $normalized['errors']),
        ];
    }

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_sso_app_user_project_policy_resolve_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            throw new RuntimeException('project not found.');
        }
        $source = trim($sourceOfTruth);
        if ($source === '') {
            $source = 'manual';
        }
        $policy = $normalized['policy'];
        $existing = $pdo->prepare('SELECT id FROM project_app_user_policies WHERE project_id = :project_id LIMIT 1');
        $existing->execute([':project_id' => $projectId]);
        $existingId = (int) ($existing->fetchColumn() ?: 0);
        if ($existingId > 0) {
            $statement = $pdo->prepare(
                'UPDATE project_app_user_policies
                 SET contract_version = :contract_version,
                     enabled = :enabled,
                     policy_json = :policy_json,
                     source_of_truth = :source_of_truth,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND project_id = :project_id',
            );
            $statement->execute([
                ':contract_version' => $policy['contract_version'],
                ':enabled' => $policy['enabled'] ? 1 : 0,
                ':policy_json' => app_sso_app_user_project_policy_json($policy),
                ':source_of_truth' => $source,
                ':id' => $existingId,
                ':project_id' => $projectId,
            ]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO project_app_user_policies (
                    project_id, contract_version, enabled, policy_json, source_of_truth
                 ) VALUES (
                    :project_id, :contract_version, :enabled, :policy_json, :source_of_truth
                 )',
            );
            $statement->execute([
                ':project_id' => $projectId,
                ':contract_version' => $policy['contract_version'],
                ':enabled' => $policy['enabled'] ? 1 : 0,
                ':policy_json' => app_sso_app_user_project_policy_json($policy),
                ':source_of_truth' => $source,
            ]);
        }

        $fetched = app_fetch_sso_app_user_project_policy($app, $projectKey);
        return [
            'ok' => $fetched['ok'],
            'item' => $fetched['item'],
            'warnings' => $normalized['warnings'],
            'error' => $fetched['error'],
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'warnings' => $normalized['warnings'],
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_sso_app_user_project_policy_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $normalized = app_normalize_project_key($projectKey);
    if ($normalized === '' || !app_project_key_is_valid($normalized)) {
        return 0;
    }
    $statement = $pdo->prepare('SELECT id FROM projects WHERE project_key = :project_key LIMIT 1');
    $statement->execute([':project_key' => $normalized]);
    return max(0, (int) ($statement->fetchColumn() ?: 0));
}
