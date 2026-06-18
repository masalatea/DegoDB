#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/database.php';

/**
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     action:string,
 *     backup_dir:string,
 *     backup_file:string,
 *     profile:string,
 *     keep_days:int,
 *     keep_count:int,
 *     confirm_restore:string,
 *     error:string
 * }
 */
function app_cli_config_store_sqlite_backup_parse_args(array $argv): array
{
    $result = [
        'ok' => true,
        'help' => false,
        'action' => 'backup',
        'backup_dir' => getenv('CONFIG_DB_BACKUP_DIR') ?: 'backups/config-db',
        'backup_file' => '',
        'profile' => 'sqlite-config-store',
        'keep_days' => (int) (getenv('CONFIG_DB_BACKUP_KEEP_DAYS') ?: '7'),
        'keep_count' => (int) (getenv('CONFIG_DB_BACKUP_KEEP_COUNT') ?: '7'),
        'confirm_restore' => getenv('CONFIRM_RESTORE') ?: '',
        'error' => '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $result['help'] = true;
            return $result;
        }

        if (str_starts_with($argument, '--action=')) {
            $result['action'] = trim(substr($argument, strlen('--action=')));
            continue;
        }

        if (str_starts_with($argument, '--backup-dir=')) {
            $result['backup_dir'] = trim(substr($argument, strlen('--backup-dir=')));
            continue;
        }

        if (str_starts_with($argument, '--backup-file=')) {
            $result['backup_file'] = trim(substr($argument, strlen('--backup-file=')));
            continue;
        }

        if (str_starts_with($argument, '--profile=')) {
            $result['profile'] = trim(substr($argument, strlen('--profile=')));
            continue;
        }

        if (str_starts_with($argument, '--keep-days=')) {
            $result['keep_days'] = max(0, (int) substr($argument, strlen('--keep-days=')));
            continue;
        }

        if (str_starts_with($argument, '--keep-count=')) {
            $result['keep_count'] = max(0, (int) substr($argument, strlen('--keep-count=')));
            continue;
        }

        if (str_starts_with($argument, '--confirm-restore=')) {
            $result['confirm_restore'] = trim(substr($argument, strlen('--confirm-restore=')));
            continue;
        }

        $result['ok'] = false;
        $result['error'] = 'unknown argument: ' . $argument;
        return $result;
    }

    if (!in_array($result['action'], ['backup', 'rotate', 'restore'], true)) {
        $result['ok'] = false;
        $result['error'] = 'unsupported action: ' . $result['action'];
    }

    return $result;
}

function app_cli_config_store_sqlite_backup_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/config_store_sqlite_backup.php --action=backup [--backup-dir=DIR] [--profile=NAME]
  php mtool/scripts/config_store_sqlite_backup.php --action=rotate [--backup-dir=DIR] [--keep-days=N] [--keep-count=N]
  php mtool/scripts/config_store_sqlite_backup.php --action=restore --backup-file=FILE --confirm-restore=yes [--backup-dir=DIR]
TEXT;
}

function app_config_store_sqlite_backup_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9._-]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-._');

    return $slug !== '' ? $slug : 'sqlite-config-store';
}

/**
 * @return array{path:string,dsn:string}
 */
function app_config_store_sqlite_backup_target(array $app): array
{
    $configDb = $app['config_db'] ?? [];
    if (!is_array($configDb) || (string) ($configDb['driver'] ?? '') !== 'sqlite') {
        throw new RuntimeException('config store is not SQLite. Set APP_CONFIG_STORE_DIR for folder-backed SQLite config store.');
    }

    $path = trim((string) ($configDb['name'] ?? ''));
    if ($path === '' || $path === ':memory:') {
        throw new RuntimeException('SQLite config store must be a file path.');
    }

    return [
        'path' => $path,
        'dsn' => (string) ($configDb['dsn'] ?? ''),
    ];
}

/**
 * @return array{ok:bool,backup_file:string,manifest_file:string,source_file:string}
 */
function app_config_store_sqlite_backup_create(PDO $pdo, string $sourceFile, string $backupDir, string $profile): array
{
    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
        throw new RuntimeException('failed to create backup dir: ' . $backupDir);
    }

    $slug = app_config_store_sqlite_backup_slug($profile);
    $backupFile = rtrim($backupDir, '/') . '/config_store-' . $slug . '-' . date('Ymd-His') . '.sqlite';
    $tmpFile = $backupFile . '.tmp';
    $manifestFile = $backupFile . '.manifest.json';

    if (is_file($tmpFile)) {
        unlink($tmpFile);
    }

    $pdo->exec('PRAGMA wal_checkpoint(PASSIVE)');
    $pdo->exec('VACUUM INTO ' . $pdo->quote($tmpFile));
    rename($tmpFile, $backupFile);

    $manifest = [
        'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        'profile' => $profile,
        'driver' => 'sqlite',
        'source_file' => $sourceFile,
        'backup_file' => $backupFile,
        'backup_method' => 'sqlite-vacuum-into',
        'backup_size_bytes' => filesize($backupFile) ?: 0,
    ];
    file_put_contents(
        $manifestFile,
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
    );

    return [
        'ok' => true,
        'backup_file' => $backupFile,
        'manifest_file' => $manifestFile,
        'source_file' => $sourceFile,
    ];
}

/**
 * @return array{deleted_files:list<string>}
 */
function app_config_store_sqlite_backup_rotate(string $backupDir, int $keepDays, int $keepCount): array
{
    if (!is_dir($backupDir)) {
        return ['deleted_files' => []];
    }

    $deletedFiles = [];
    $now = time();
    $backupFiles = glob(rtrim($backupDir, '/') . '/config_store-*.sqlite') ?: [];
    rsort($backupFiles);

    foreach ($backupFiles as $backupFile) {
        $delete = false;
        if ($keepDays > 0 && is_file($backupFile)) {
            $mtime = filemtime($backupFile);
            $delete = is_int($mtime) && $mtime < ($now - ($keepDays * 86400));
        }

        if ($delete) {
            $manifestFile = $backupFile . '.manifest.json';
            unlink($backupFile);
            $deletedFiles[] = $backupFile;
            if (is_file($manifestFile)) {
                unlink($manifestFile);
                $deletedFiles[] = $manifestFile;
            }
        }
    }

    $remaining = glob(rtrim($backupDir, '/') . '/config_store-*.sqlite') ?: [];
    usort(
        $remaining,
        static fn (string $a, string $b): int => (filemtime($b) ?: 0) <=> (filemtime($a) ?: 0),
    );
    if ($keepCount > 0) {
        foreach (array_slice($remaining, $keepCount) as $oldBackup) {
            $manifestFile = $oldBackup . '.manifest.json';
            unlink($oldBackup);
            $deletedFiles[] = $oldBackup;
            if (is_file($manifestFile)) {
                unlink($manifestFile);
                $deletedFiles[] = $manifestFile;
            }
        }
    }

    return ['deleted_files' => $deletedFiles];
}

/**
 * @return array{ok:bool,restored_file:string,pre_restore_backup_file:string,integrity_check:string}
 */
function app_config_store_sqlite_backup_restore(array $app, string $backupFile, string $backupDir, string $profile): array
{
    if (!is_file($backupFile)) {
        throw new RuntimeException('backup file not found: ' . $backupFile);
    }

    $target = app_config_store_sqlite_backup_target($app);
    $sourceFile = $target['path'];
    $pdo = app_create_config_pdo($app);
    $preRestore = app_config_store_sqlite_backup_create($pdo, $sourceFile, $backupDir, $profile . '-pre-restore');
    $pdo = null;

    $targetDir = dirname($sourceFile);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('failed to create SQLite config store dir: ' . $targetDir);
    }

    $tmpTarget = $sourceFile . '.restore-tmp';
    copy($backupFile, $tmpTarget);
    rename($tmpTarget, $sourceFile);

    $restorePdo = app_create_pdo_from_db_config($app['config_db']);
    $integrityCheck = (string) $restorePdo->query('PRAGMA integrity_check')->fetchColumn();

    return [
        'ok' => $integrityCheck === 'ok',
        'restored_file' => $sourceFile,
        'pre_restore_backup_file' => $preRestore['backup_file'],
        'integrity_check' => $integrityCheck,
    ];
}

$parsed = app_cli_config_store_sqlite_backup_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_config_store_sqlite_backup_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_config_store_sqlite_backup_usage() . PHP_EOL);
    exit(64);
}

try {
    $app = app_bootstrap();
    $target = app_config_store_sqlite_backup_target($app);

    if ($parsed['action'] === 'backup') {
        $pdo = app_create_config_pdo($app);
        $result = app_config_store_sqlite_backup_create(
            $pdo,
            $target['path'],
            $parsed['backup_dir'],
            $parsed['profile'],
        );
    } elseif ($parsed['action'] === 'rotate') {
        $result = app_config_store_sqlite_backup_rotate(
            $parsed['backup_dir'],
            $parsed['keep_days'],
            $parsed['keep_count'],
        );
        $result['ok'] = true;
        $result['backup_dir'] = $parsed['backup_dir'];
    } else {
        if ($parsed['confirm_restore'] !== 'yes') {
            throw new RuntimeException('CONFIRM_RESTORE=yes or --confirm-restore=yes is required because this overwrites SQLite config store state.');
        }

        $result = app_config_store_sqlite_backup_restore(
            $app,
            $parsed['backup_file'],
            $parsed['backup_dir'],
            $parsed['profile'],
        );
        if (!$result['ok']) {
            throw new RuntimeException('restored SQLite integrity_check failed: ' . $result['integrity_check']);
        }
    }

    fwrite(
        STDOUT,
        json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
    );
    exit(0);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
