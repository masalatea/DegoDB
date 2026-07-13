<?php

declare(strict_types=1);

require_once __DIR__ . '/sso_app_user_design_guidance.php';

function app_sso_app_user_driver(PDO $pdo): string
{
    return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
}

/**
 * Apply a deterministic proof schema for the active driver.
 * Production generation may provide an already-created schema; this remains
 * safe because every statement is CREATE TABLE IF NOT EXISTS.
 */
function app_sso_app_user_apply_schema(PDO $pdo): void
{
    $driver = app_sso_app_user_driver($pdo);
    if ($driver === 'sqlite') {
        app_sso_app_user_apply_sqlite_schema($pdo);
        return;
    }
    if ($driver === 'mysql') {
        app_sso_app_user_apply_mysql_schema($pdo);
        return;
    }
    throw new RuntimeException('unsupported SSO app-user runtime driver: ' . $driver);
}

/**
 * Apply the deterministic SQLite proof schema.
 * Production generation may use dialect-specific DDL with the same invariants.
 */
function app_sso_app_user_apply_sqlite_schema(PDO $pdo): void
{
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS app_user (
            app_user_id TEXT PRIMARY KEY,
            status TEXT NOT NULL DEFAULT \'enabled\',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )',
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS app_user_external_identity (
            app_user_external_identity_id INTEGER PRIMARY KEY AUTOINCREMENT,
            app_user_id TEXT NOT NULL,
            provider_key TEXT NOT NULL,
            issuer TEXT NOT NULL,
            subject TEXT NOT NULL,
            first_authenticated_at TEXT NOT NULL,
            last_authenticated_at TEXT NOT NULL,
            UNIQUE (issuer, subject),
            FOREIGN KEY (app_user_id) REFERENCES app_user (app_user_id)
        )',
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS app_user_profile (
            app_user_id TEXT PRIMARY KEY,
            display_name TEXT NOT NULL DEFAULT \'\',
            email TEXT NOT NULL DEFAULT \'\',
            profile_json TEXT NOT NULL DEFAULT \'{}\',
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (app_user_id) REFERENCES app_user (app_user_id)
        )',
    );
}

/**
 * Apply the deterministic MySQL/MariaDB proof schema.
 *
 * The external identity table intentionally uses the semantic SSO identity
 * pair as its key. That keeps promoted SQLite data usable for future JIT
 * logins without depending on a copied AUTOINCREMENT surrogate.
 */
function app_sso_app_user_apply_mysql_schema(PDO $pdo): void
{
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `app_user` (
            `app_user_id` VARCHAR(40) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT \'enabled\',
            `created_at` VARCHAR(40) NOT NULL DEFAULT \'\',
            `updated_at` VARCHAR(40) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`app_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin',
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `app_user_external_identity` (
            `app_user_id` VARCHAR(40) NOT NULL,
            `provider_key` VARCHAR(80) NOT NULL,
            `issuer` VARCHAR(255) NOT NULL,
            `subject` VARCHAR(255) NOT NULL,
            `first_authenticated_at` VARCHAR(40) NOT NULL,
            `last_authenticated_at` VARCHAR(40) NOT NULL,
            PRIMARY KEY (`issuer`, `subject`),
            CONSTRAINT `fk_external_identity_app_user`
                FOREIGN KEY (`app_user_id`) REFERENCES `app_user` (`app_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin',
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `app_user_profile` (
            `app_user_id` VARCHAR(40) NOT NULL,
            `display_name` VARCHAR(255) NOT NULL DEFAULT \'\',
            `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            `profile_json` JSON NOT NULL,
            `updated_at` VARCHAR(40) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`app_user_id`),
            CONSTRAINT `fk_profile_app_user`
                FOREIGN KEY (`app_user_id`) REFERENCES `app_user` (`app_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin',
    );
}

/**
 * Resolve a principal only after the caller has completed SSO protocol
 * validation. This function does not validate tokens or client assertions.
 *
 * @param array<string,mixed> $principal
 * @param array<string,mixed> $policy
 * @return array<string,mixed>
 */
function app_sso_app_user_resolve_verified_principal(PDO $pdo, array $principal, array $policy): array
{
    $issuer = rtrim(trim((string) ($principal['issuer'] ?? $principal['iss'] ?? '')), '/');
    $subject = trim((string) ($principal['subject'] ?? $principal['sub'] ?? ''));
    if ($issuer === '' || $subject === '') {
        return app_sso_app_user_runtime_result(false, 'invalid_principal', '', false, [], 'issuer and subject are required.');
    }

    $mode = strtolower(trim((string) ($policy['provisioning_mode'] ?? '')));
    if (!in_array($mode, ['jit', 'invitation-only'], true)) {
        return app_sso_app_user_runtime_result(false, 'invalid_policy', '', false, [], 'provisioning_mode must be jit or invitation-only.');
    }

    $profileFields = app_sso_app_user_string_list($policy['sso_profile_fields'] ?? []);
    $forbidden = app_sso_app_user_forbidden_fields($profileFields);
    if ($forbidden !== []) {
        return app_sso_app_user_runtime_result(
            false,
            'invalid_policy',
            '',
            false,
            [],
            'forbidden SSO profile fields: ' . implode(', ', $forbidden) . '.',
        );
    }

    $providerKey = trim((string) ($policy['provider_key'] ?? 'oidc'));
    if ($providerKey === '') {
        return app_sso_app_user_runtime_result(false, 'invalid_policy', '', false, [], 'provider_key is required.');
    }

    app_sso_app_user_apply_schema($pdo);
    $existing = app_sso_app_user_fetch_by_external_identity($pdo, $issuer, $subject);
    if ($existing !== null) {
        if (($existing['status'] ?? '') !== 'enabled') {
            return app_sso_app_user_runtime_result(false, 'account_disabled', (string) $existing['app_user_id'], false, [], 'app user is not enabled.');
        }
        $profile = app_sso_app_user_safe_profile($principal, $profileFields);
        app_sso_app_user_update_profile($pdo, (string) $existing['app_user_id'], $profile);
        app_sso_app_user_touch_identity($pdo, $issuer, $subject);

        return app_sso_app_user_runtime_result(true, 'restored', (string) $existing['app_user_id'], false, $profile, '');
    }

    if ($mode === 'invitation-only') {
        return app_sso_app_user_runtime_result(false, 'enrollment_required', '', false, [], 'unknown SSO identity is not invited.');
    }

    $ownsTransaction = !$pdo->inTransaction();
    try {
        if ($ownsTransaction) {
            $pdo->beginTransaction();
        }
        // Recheck inside the write transaction to reduce duplicate JIT races.
        $existing = app_sso_app_user_fetch_by_external_identity($pdo, $issuer, $subject);
        if ($existing !== null) {
            $profile = app_sso_app_user_safe_profile($principal, $profileFields);
            app_sso_app_user_update_profile($pdo, (string) $existing['app_user_id'], $profile);
            app_sso_app_user_touch_identity($pdo, $issuer, $subject);
            if ($ownsTransaction) {
                $pdo->commit();
            }
            return app_sso_app_user_runtime_result(true, 'restored', (string) $existing['app_user_id'], false, $profile, '');
        }

        $appUserId = 'usr_' . bin2hex(random_bytes(16));
        $now = gmdate('c');
        $insertUser = $pdo->prepare('INSERT INTO app_user (app_user_id, status, created_at, updated_at) VALUES (:app_user_id, \'enabled\', :created_at, :updated_at)');
        $insertUser->execute([':app_user_id' => $appUserId, ':created_at' => $now, ':updated_at' => $now]);

        $insertIdentity = $pdo->prepare(
            'INSERT INTO app_user_external_identity (
                app_user_id, provider_key, issuer, subject, first_authenticated_at, last_authenticated_at
             ) VALUES (
                :app_user_id, :provider_key, :issuer, :subject, :first_authenticated_at, :last_authenticated_at
             )',
        );
        $insertIdentity->execute([
            ':app_user_id' => $appUserId,
            ':provider_key' => $providerKey,
            ':issuer' => $issuer,
            ':subject' => $subject,
            ':first_authenticated_at' => $now,
            ':last_authenticated_at' => $now,
        ]);

        $profile = app_sso_app_user_safe_profile($principal, $profileFields);
        app_sso_app_user_update_profile($pdo, $appUserId, $profile);
        if ($ownsTransaction) {
            $pdo->commit();
        }

        return app_sso_app_user_runtime_result(true, 'created', $appUserId, true, $profile, '');
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return app_sso_app_user_runtime_result(false, 'persistence_failed', '', false, [], $throwable->getMessage());
    }
}

/** @return array<string,mixed>|null */
function app_sso_app_user_fetch_by_external_identity(PDO $pdo, string $issuer, string $subject): ?array
{
    $statement = $pdo->prepare(
        'SELECT u.app_user_id, u.status
         FROM app_user_external_identity i
         INNER JOIN app_user u ON u.app_user_id = i.app_user_id
         WHERE i.issuer = :issuer AND i.subject = :subject
         LIMIT 1',
    );
    $statement->execute([':issuer' => $issuer, ':subject' => $subject]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

/** @param list<string> $fields @return array<string,mixed> */
function app_sso_app_user_safe_profile(array $principal, array $fields): array
{
    $profile = [];
    foreach ($fields as $field) {
        if (!array_key_exists($field, $principal)) {
            continue;
        }
        $value = $principal[$field];
        if (is_scalar($value) || $value === null) {
            $profile[$field] = $value === null ? '' : (string) $value;
        }
    }
    ksort($profile);
    return $profile;
}

/** @param array<string,mixed> $profile */
function app_sso_app_user_update_profile(PDO $pdo, string $appUserId, array $profile): void
{
    $json = json_encode($profile, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $now = gmdate('c');
    $driver = app_sso_app_user_driver($pdo);
    if ($driver === 'sqlite') {
        $statement = $pdo->prepare(
            'INSERT INTO app_user_profile (app_user_id, display_name, email, profile_json, updated_at)
             VALUES (:app_user_id, :display_name, :email, :profile_json, :updated_at)
             ON CONFLICT (app_user_id) DO UPDATE SET
                display_name = excluded.display_name,
                email = excluded.email,
                profile_json = excluded.profile_json,
                updated_at = excluded.updated_at',
        );
    } elseif ($driver === 'mysql') {
        $statement = $pdo->prepare(
            'INSERT INTO app_user_profile (app_user_id, display_name, email, profile_json, updated_at)
             VALUES (:app_user_id, :display_name, :email, :profile_json, :updated_at)
             ON DUPLICATE KEY UPDATE
                display_name = VALUES(display_name),
                email = VALUES(email),
                profile_json = VALUES(profile_json),
                updated_at = VALUES(updated_at)',
        );
    } else {
        throw new RuntimeException('unsupported SSO app-user runtime driver: ' . $driver);
    }
    $statement->execute([
        ':app_user_id' => $appUserId,
        ':display_name' => (string) ($profile['display_name'] ?? ''),
        ':email' => (string) ($profile['email'] ?? ''),
        ':profile_json' => $json,
        ':updated_at' => $now,
    ]);
}

function app_sso_app_user_touch_identity(PDO $pdo, string $issuer, string $subject): void
{
    $statement = $pdo->prepare(
        'UPDATE app_user_external_identity
         SET last_authenticated_at = :last_authenticated_at
         WHERE issuer = :issuer AND subject = :subject',
    );
    $statement->execute([
        ':last_authenticated_at' => gmdate('c'),
        ':issuer' => $issuer,
        ':subject' => $subject,
    ]);
}

/** @return array<string,mixed> */
function app_sso_app_user_runtime_result(
    bool $ok,
    string $status,
    string $appUserId,
    bool $created,
    array $profile,
    string $error,
): array {
    return [
        'ok' => $ok,
        'status' => $status,
        'app_user_id' => $appUserId,
        'created' => $created,
        'profile' => $profile,
        'error' => $error,
    ];
}
