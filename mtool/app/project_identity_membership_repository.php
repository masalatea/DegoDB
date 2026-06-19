<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';

const APP_PROJECT_IDENTITY_MEMBERSHIP_ROLES = [
    'viewer',
    'editor',
    'publisher',
    'admin',
];

/**
 * @return array{ok:bool, items:list<array<string,mixed>>, error:string}
 */
function app_fetch_project_identity_memberships(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_project_identity_membership_resolve_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                principal_source,
                principal_subject,
                role_code,
                source_of_truth,
                created_at,
                updated_at
             FROM project_identity_memberships
             WHERE project_id = :project_id
             ORDER BY principal_source, principal_subject, role_code'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'principal_source' => (string) ($row['principal_source'] ?? ''),
                'principal_subject' => (string) ($row['principal_subject'] ?? ''),
                'role_code' => (string) ($row['role_code'] ?? ''),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
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
 * @param list<array{
 *     principal_source:string,
 *     principal_subject:string,
 *     role_code:string,
 *     source_of_truth?:string
 * }> $memberships
 * @return array{ok:bool, error:string}
 */
function app_replace_project_identity_memberships(array $app, string $projectKey, array $memberships): array
{
    $pdo = null;

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_project_identity_membership_resolve_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            return [
                'ok' => false,
                'error' => '更新対象の project が見つかりません。',
            ];
        }

        $normalized = [];
        foreach ($memberships as $membership) {
            $principalSource = app_project_identity_membership_normalize_principal_source(
                (string) ($membership['principal_source'] ?? ''),
            );
            $principalSubject = trim((string) ($membership['principal_subject'] ?? ''));
            $roleCode = app_project_identity_membership_normalize_role_code((string) ($membership['role_code'] ?? ''));
            $sourceOfTruth = trim((string) ($membership['source_of_truth'] ?? 'manual'));
            if ($principalSource === '' || $principalSubject === '' || $roleCode === '') {
                continue;
            }
            if ($sourceOfTruth === '') {
                $sourceOfTruth = 'manual';
            }

            $key = implode("\n", [$principalSource, $principalSubject, $roleCode]);
            $normalized[$key] = [
                'principal_source' => $principalSource,
                'principal_subject' => $principalSubject,
                'role_code' => $roleCode,
                'source_of_truth' => $sourceOfTruth,
            ];
        }

        $pdo->beginTransaction();
        $delete = $pdo->prepare(
            'DELETE FROM project_identity_memberships
             WHERE project_id = :project_id'
        );
        $delete->execute([
            ':project_id' => $projectId,
        ]);

        $insert = $pdo->prepare(
            'INSERT INTO project_identity_memberships (
                project_id,
                principal_source,
                principal_subject,
                role_code,
                source_of_truth
            ) VALUES (
                :project_id,
                :principal_source,
                :principal_subject,
                :role_code,
                :source_of_truth
            )'
        );
        foreach (array_values($normalized) as $membership) {
            $insert->execute([
                ':project_id' => $projectId,
                ':principal_source' => $membership['principal_source'],
                ':principal_subject' => $membership['principal_subject'],
                ':role_code' => $membership['role_code'],
                ':source_of_truth' => $membership['source_of_truth'],
            ]);
        }

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{id:string,auth_source:string} $principal
 * @return array{ok:bool, roles:list<string>, error:string}
 */
function app_fetch_project_identity_roles_for_principal(array $app, string $projectKey, array $principal): array
{
    try {
        $principalSource = app_project_identity_membership_normalize_principal_source(
            (string) ($principal['auth_source'] ?? ''),
        );
        $principalSubject = trim((string) ($principal['id'] ?? ''));
        if ($principalSource === '' || $principalSubject === '') {
            return [
                'ok' => true,
                'roles' => [],
                'error' => '',
            ];
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_project_identity_membership_resolve_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            return [
                'ok' => true,
                'roles' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT role_code
             FROM project_identity_memberships
             WHERE project_id = :project_id
               AND principal_source = :principal_source
               AND principal_subject = :principal_subject
             ORDER BY role_code'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':principal_source' => $principalSource,
            ':principal_subject' => $principalSubject,
        ]);

        $roles = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $roleCode = app_project_identity_membership_normalize_role_code((string) ($row['role_code'] ?? ''));
            if ($roleCode !== '') {
                $roles[] = $roleCode;
            }
        }

        return [
            'ok' => true,
            'roles' => array_values(array_unique($roles)),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'roles' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_project_identity_membership_normalize_principal_source(string $principalSource): string
{
    $normalized = strtolower(trim($principalSource));
    return preg_match('/^[a-z][a-z0-9_.:-]{0,63}$/', $normalized) === 1 ? $normalized : '';
}

function app_project_identity_membership_normalize_role_code(string $roleCode): string
{
    $normalized = strtolower(trim($roleCode));
    return in_array($normalized, APP_PROJECT_IDENTITY_MEMBERSHIP_ROLES, true) ? $normalized : '';
}

function app_project_identity_membership_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return 0;
    }

    $statement = $pdo->prepare(
        'SELECT id
         FROM projects
         WHERE project_key = :project_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $normalizedProjectKey,
    ]);

    $row = $statement->fetch();
    return is_array($row) ? max(0, (int) ($row['id'] ?? 0)) : 0;
}
