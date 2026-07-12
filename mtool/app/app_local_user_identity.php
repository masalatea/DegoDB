<?php

declare(strict_types=1);

const APP_LOCAL_USER_IDENTITY_VERSION = 'app-local-user-identity-v0';

/**
 * @param array<string,mixed> $principal
 * @return array{
 *     ok:bool,
 *     identity:array<string,mixed>,
 *     error:string
 * }
 */
function app_local_user_identity_from_principal(array $principal, array $options = []): array
{
    $authSource = app_local_user_identity_text($principal['auth_source'] ?? '');
    $subject = app_local_user_identity_text($principal['subject'] ?? $principal['sub'] ?? $principal['id'] ?? '');
    $issuer = app_local_user_identity_text($principal['issuer'] ?? $principal['iss'] ?? $authSource);
    $displayName = app_local_user_identity_text($principal['display_name'] ?? $principal['name'] ?? $subject);
    $email = app_local_user_identity_text($principal['email'] ?? '');
    $deviceId = app_local_user_identity_text($options['device_id'] ?? $principal['device_id'] ?? 'default-device');

    if ($authSource === '') {
        return app_local_user_identity_error('auth_source is required.');
    }
    if ($subject === '') {
        return app_local_user_identity_error('subject or id is required.');
    }
    if ($issuer === '') {
        return app_local_user_identity_error('issuer is required.');
    }
    if ($deviceId === '') {
        return app_local_user_identity_error('device_id is required.');
    }

    $identity = [
        'identity_version' => APP_LOCAL_USER_IDENTITY_VERSION,
        'local_user_id' => app_local_user_identity_local_user_id($issuer, $subject),
        'auth_source' => $authSource,
        'issuer' => $issuer,
        'subject' => $subject,
        'display_name' => $displayName !== '' ? $displayName : $subject,
        'email' => $email,
        'device_id' => $deviceId,
        'site' => app_local_user_identity_text($principal['site'] ?? ''),
        'site_roles' => app_local_user_identity_string_list($principal['site_roles'] ?? $principal['roles'] ?? []),
        'project_roles' => app_local_user_identity_project_roles($principal['project_roles'] ?? []),
        'scopes' => app_local_user_identity_string_list($principal['scopes'] ?? []),
        'profile_cached_at' => app_local_user_identity_text($options['profile_cached_at'] ?? gmdate('c')),
        'last_authenticated_at' => app_local_user_identity_text($options['last_authenticated_at'] ?? gmdate('c')),
    ];

    return [
        'ok' => true,
        'identity' => $identity,
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $identity
 * @return array<string,mixed>
 */
function app_local_user_identity_actor_snapshot(array $identity): array
{
    return [
        'actor_version' => 'managed-operation-sync-actor-v0',
        'local_user_id' => app_local_user_identity_text($identity['local_user_id'] ?? ''),
        'auth_source' => app_local_user_identity_text($identity['auth_source'] ?? ''),
        'issuer' => app_local_user_identity_text($identity['issuer'] ?? ''),
        'subject' => app_local_user_identity_text($identity['subject'] ?? ''),
        'display_name' => app_local_user_identity_text($identity['display_name'] ?? ''),
        'email' => app_local_user_identity_text($identity['email'] ?? ''),
        'device_id' => app_local_user_identity_text($identity['device_id'] ?? ''),
        'site' => app_local_user_identity_text($identity['site'] ?? ''),
        'site_roles' => app_local_user_identity_string_list($identity['site_roles'] ?? []),
        'project_roles' => app_local_user_identity_project_roles($identity['project_roles'] ?? []),
        'scopes' => app_local_user_identity_string_list($identity['scopes'] ?? []),
    ];
}

/**
 * @return array{ok:bool,applied:bool,error:string}
 */
function app_local_user_identity_apply_schema(PDO $pdo): array
{
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS "__app_local_user_identity" (
                "local_user_id" TEXT PRIMARY KEY,
                "auth_source" TEXT NOT NULL,
                "issuer" TEXT NOT NULL,
                "subject" TEXT NOT NULL,
                "display_name" TEXT NOT NULL,
                "email" TEXT NOT NULL,
                "device_id" TEXT NOT NULL,
                "identity_json" TEXT NOT NULL,
                "profile_cached_at" TEXT NOT NULL,
                "last_authenticated_at" TEXT NOT NULL,
                "updated_at" TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
        );
        $pdo->exec(
            'CREATE UNIQUE INDEX IF NOT EXISTS "idx_app_local_user_identity_issuer_subject_device"
             ON "__app_local_user_identity" ("issuer", "subject", "device_id")',
        );

        return ['ok' => true, 'applied' => true, 'error' => ''];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'applied' => false, 'error' => $throwable->getMessage()];
    }
}

/**
 * @param array<string,mixed> $identity
 * @return array{ok:bool,local_user_id:string,error:string}
 */
function app_local_user_identity_save(PDO $pdo, array $identity): array
{
    $schema = app_local_user_identity_apply_schema($pdo);
    if (!$schema['ok']) {
        return ['ok' => false, 'local_user_id' => '', 'error' => $schema['error']];
    }

    try {
        $safeIdentity = app_local_user_identity_safe_snapshot($identity);
        $localUserId = app_local_user_identity_text($safeIdentity['local_user_id'] ?? '');
        if ($localUserId === '') {
            throw new RuntimeException('local_user_id is required.');
        }

        $json = json_encode($safeIdentity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $statement = $pdo->prepare(
            'INSERT INTO "__app_local_user_identity" (
                "local_user_id",
                "auth_source",
                "issuer",
                "subject",
                "display_name",
                "email",
                "device_id",
                "identity_json",
                "profile_cached_at",
                "last_authenticated_at"
             ) VALUES (
                :local_user_id,
                :auth_source,
                :issuer,
                :subject,
                :display_name,
                :email,
                :device_id,
                :identity_json,
                :profile_cached_at,
                :last_authenticated_at
             ) ON CONFLICT ("local_user_id") DO UPDATE SET
                "auth_source" = excluded."auth_source",
                "issuer" = excluded."issuer",
                "subject" = excluded."subject",
                "display_name" = excluded."display_name",
                "email" = excluded."email",
                "device_id" = excluded."device_id",
                "identity_json" = excluded."identity_json",
                "profile_cached_at" = excluded."profile_cached_at",
                "last_authenticated_at" = excluded."last_authenticated_at",
                "updated_at" = CURRENT_TIMESTAMP'
        );
        if ($statement === false) {
            throw new RuntimeException('failed to prepare App-local user identity save statement.');
        }
        $statement->execute([
            ':local_user_id' => $localUserId,
            ':auth_source' => app_local_user_identity_text($safeIdentity['auth_source'] ?? ''),
            ':issuer' => app_local_user_identity_text($safeIdentity['issuer'] ?? ''),
            ':subject' => app_local_user_identity_text($safeIdentity['subject'] ?? ''),
            ':display_name' => app_local_user_identity_text($safeIdentity['display_name'] ?? ''),
            ':email' => app_local_user_identity_text($safeIdentity['email'] ?? ''),
            ':device_id' => app_local_user_identity_text($safeIdentity['device_id'] ?? ''),
            ':identity_json' => $json,
            ':profile_cached_at' => app_local_user_identity_text($safeIdentity['profile_cached_at'] ?? ''),
            ':last_authenticated_at' => app_local_user_identity_text($safeIdentity['last_authenticated_at'] ?? ''),
        ]);

        return ['ok' => true, 'local_user_id' => $localUserId, 'error' => ''];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'local_user_id' => '', 'error' => $throwable->getMessage()];
    }
}

/**
 * @return array{ok:bool,identity:array<string,mixed>|null,error:string}
 */
function app_local_user_identity_restore(PDO $pdo, string $localUserId): array
{
    try {
        $statement = $pdo->prepare(
            'SELECT "identity_json" FROM "__app_local_user_identity" WHERE "local_user_id" = :local_user_id LIMIT 1',
        );
        if ($statement === false) {
            throw new RuntimeException('failed to prepare App-local user identity restore statement.');
        }
        $statement->execute([':local_user_id' => $localUserId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return ['ok' => true, 'identity' => null, 'error' => ''];
        }
        $decoded = json_decode((string) ($row['identity_json'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('stored App-local user identity is not an object.');
        }

        return ['ok' => true, 'identity' => app_local_user_identity_safe_snapshot($decoded), 'error' => ''];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'identity' => null, 'error' => $throwable->getMessage()];
    }
}

/**
 * @param array<string,mixed> $identity
 * @return array<string,mixed>
 */
function app_local_user_identity_safe_snapshot(array $identity): array
{
    $blocked = [
        'access_token' => true,
        'refresh_token' => true,
        'id_token' => true,
        'password' => true,
        'passwd' => true,
        'client_secret' => true,
        'secret' => true,
        'credential' => true,
        'credentials' => true,
        'claims' => true,
        'raw_claims' => true,
    ];

    $safe = [];
    foreach ($identity as $key => $value) {
        $normalizedKey = strtolower(trim((string) $key));
        if (isset($blocked[$normalizedKey])
            || preg_match('/(^|_)(password|passwd|secret|token|credential|credentials)($|_)/', $normalizedKey) === 1
        ) {
            continue;
        }
        if (is_array($value)) {
            $safe[$key] = app_local_user_identity_safe_snapshot($value);
            continue;
        }
        $safe[$key] = $value;
    }

    return $safe;
}

function app_local_user_identity_local_user_id(string $issuer, string $subject): string
{
    return 'usr_' . hash('sha256', $issuer . "\n" . $subject);
}

/**
 * @return array{ok:bool,identity:array<string,mixed>,error:string}
 */
function app_local_user_identity_error(string $error): array
{
    return [
        'ok' => false,
        'identity' => [],
        'error' => $error,
    ];
}

function app_local_user_identity_text(mixed $value): string
{
    return trim((string) $value);
}

/**
 * @return list<string>
 */
function app_local_user_identity_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        if (!is_string($item)) {
            continue;
        }
        $item = trim($item);
        if ($item !== '') {
            $items[] = $item;
        }
    }

    return array_values(array_unique($items));
}

/**
 * @return array<string,list<string>>
 */
function app_local_user_identity_project_roles(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $rolesByProject = [];
    foreach ($value as $projectKey => $roles) {
        if (!is_string($projectKey)) {
            continue;
        }
        $normalizedRoles = app_local_user_identity_string_list($roles);
        if ($normalizedRoles !== []) {
            $rolesByProject[$projectKey] = $normalizedRoles;
        }
    }

    return $rolesByProject;
}
