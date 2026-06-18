<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         owner:array{
 *             login_id:string,
 *             role_code:string,
 *             can_administer:bool,
 *             membership_row_count:int,
 *             raw_role_codes:list<string>
 *         },
 *         members:list<array{
 *             login_id:string,
 *             role_code:string,
 *             can_administer:bool,
 *             membership_row_count:int,
 *             raw_role_codes:list<string>
 *         }>,
 *         unique_user_count:int,
 *         admin_user_count:int
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_membership_summary(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $projectStatement = $pdo->prepare(
            'SELECT id, owner_login_id
            FROM projects
            WHERE project_key = :project_key
            LIMIT 1'
        );
        $projectStatement->execute([
            ':project_key' => $projectKey,
        ]);

        $projectRow = $projectStatement->fetch();
        if (!is_array($projectRow)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        $projectId = (int) ($projectRow['id'] ?? 0);
        $ownerLoginId = trim((string) ($projectRow['owner_login_id'] ?? ''));

        $roleCodesSelect = $dialect === 'sqlite'
            ? '(SELECT group_concat(role_code, ",")
                FROM (
                    SELECT DISTINCT pm2.role_code AS role_code
                    FROM project_memberships AS pm2
                    WHERE pm2.project_id = project_memberships.project_id
                      AND pm2.login_id = project_memberships.login_id
                    ORDER BY pm2.role_code
                )) AS role_codes'
            : 'GROUP_CONCAT(DISTINCT role_code ORDER BY role_code SEPARATOR ",") AS role_codes';
        $statement = $pdo->prepare(
            'SELECT
                login_id,
                ' . $roleCodesSelect . ',
                MAX(can_administer) AS can_administer,
                COUNT(*) AS membership_row_count
            FROM project_memberships
            WHERE project_id = :project_id
            GROUP BY login_id
            ORDER BY login_id'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);

        $owner = [
            'login_id' => $ownerLoginId,
            'role_code' => 'owner',
            'can_administer' => true,
            'membership_row_count' => 0,
            'raw_role_codes' => ['owner'],
        ];
        $members = [];

        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $loginId = trim((string) ($row['login_id'] ?? ''));
            if ($loginId === '') {
                continue;
            }

            $roleCodes = array_values(
                array_filter(
                    array_map(
                        static fn (string $value): string => trim($value),
                        explode(',', (string) ($row['role_codes'] ?? '')),
                    ),
                    static fn (string $value): bool => $value !== '',
                ),
            );

            $canAdminister = (int) ($row['can_administer'] ?? 0) === 1;
            $rowCount = max(1, (int) ($row['membership_row_count'] ?? 0));
            $isOwner = $loginId === $ownerLoginId || in_array('owner', $roleCodes, true);

            $item = [
                'login_id' => $loginId,
                'role_code' => $isOwner
                    ? 'owner'
                    : ($canAdminister || in_array('admin', $roleCodes, true) ? 'admin' : 'member'),
                'can_administer' => $isOwner ? true : $canAdminister,
                'membership_row_count' => $rowCount,
                'raw_role_codes' => $roleCodes === [] ? ['member'] : $roleCodes,
            ];

            if ($isOwner) {
                $owner = $item;
                continue;
            }

            $members[] = $item;
        }

        $uniqueUserCount = $owner['login_id'] === '' ? count($members) : count($members) + 1;
        $adminUserCount = ($owner['login_id'] === '' ? 0 : 1)
            + count(
                array_filter(
                    $members,
                    static fn (array $item): bool => (bool) ($item['can_administer'] ?? false),
                ),
            );

        return [
            'ok' => true,
            'item' => [
                'owner' => $owner,
                'members' => $members,
                'unique_user_count' => $uniqueUserCount,
                'admin_user_count' => $adminUserCount,
            ],
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
 * @param list<array{
 *     login_id:string,
 *     role_code:string
 * }> $members
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_replace_project_memberships(array $app, string $projectKey, array $members): array
{
    $pdo = null;

    try {
        $pdo = app_create_config_pdo($app);
        $projectStatement = $pdo->prepare(
            'SELECT id, owner_login_id
            FROM projects
            WHERE project_key = :project_key
            LIMIT 1'
        );
        $projectStatement->execute([
            ':project_key' => $projectKey,
        ]);

        $projectRow = $projectStatement->fetch();
        if (!is_array($projectRow)) {
            return [
                'ok' => false,
                'error' => '更新対象の project が見つかりません。',
            ];
        }

        $projectId = (int) ($projectRow['id'] ?? 0);
        $ownerLoginId = trim((string) ($projectRow['owner_login_id'] ?? ''));
        if ($projectId <= 0 || $ownerLoginId === '') {
            return [
                'ok' => false,
                'error' => 'project membership の更新に必要な owner 情報が不足しています。',
            ];
        }

        $normalizedMembers = [];
        foreach ($members as $member) {
            $loginId = trim((string) ($member['login_id'] ?? ''));
            if ($loginId === '' || $loginId === $ownerLoginId) {
                continue;
            }

            $normalizedMembers[$loginId] = [
                'login_id' => $loginId,
                'role_code' => (($member['role_code'] ?? '') === 'admin') ? 'admin' : 'member',
            ];
        }

        $pdo->beginTransaction();

        // Normalize the table to one canonical row per login for the current first slice.
        $deleteStatement = $pdo->prepare(
            'DELETE FROM project_memberships
            WHERE project_id = :project_id'
        );
        $deleteStatement->execute([
            ':project_id' => $projectId,
        ]);

        $insertStatement = $pdo->prepare(
            'INSERT INTO project_memberships (
                project_id,
                login_id,
                role_code,
                can_administer
            ) VALUES (
                :project_id,
                :login_id,
                :role_code,
                :can_administer
            )'
        );

        $insertStatement->execute([
            ':project_id' => $projectId,
            ':login_id' => $ownerLoginId,
            ':role_code' => 'owner',
            ':can_administer' => 1,
        ]);

        foreach (array_values($normalizedMembers) as $member) {
            $insertStatement->execute([
                ':project_id' => $projectId,
                ':login_id' => $member['login_id'],
                ':role_code' => $member['role_code'],
                ':can_administer' => $member['role_code'] === 'admin' ? 1 : 0,
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
