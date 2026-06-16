<?php

declare(strict_types=1);

$mtooldb = null;
$last_sql_command_for_mtooldb = '';
$time_for_reconnect_mtooldb_if_necessary = time();

function connect_error_for_mtooldb(string $message): void
{
    error_log($message);

    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('HTTP/1.0 503 Service Temporarily Unavailable');
    }
}

function connect_mtooldb_if_not_yet(): void
{
    global $mtooldb;

    if ($mtooldb instanceof mysqli) {
        return;
    }

    $host = (string) ($GLOBALS['CustomMySQLDBServerNameFormtooldb'] ?? getenv('MTOOL_PROXY_DB_HOST') ?: 'localhost');
    $port = (int) ($GLOBALS['CustomMySQLDBPortFormtooldb'] ?? getenv('MTOOL_PROXY_DB_PORT') ?: '3306');
    $user = (string) ($GLOBALS['CustomMySQLDBUserFormtooldb'] ?? getenv('MTOOL_PROXY_DB_USER') ?: '');
    $password = (string) ($GLOBALS['CustomMySQLDBPasswordFormtooldb'] ?? getenv('MTOOL_PROXY_DB_PASSWORD') ?: '');
    $database = (string) ($GLOBALS['CustomMySQLDBNameFormtooldb'] ?? getenv('MTOOL_PROXY_DB_NAME') ?: 'mtool');

    $mtooldb = mysqli_init();
    if (!$mtooldb instanceof mysqli) {
        throw new RuntimeException('mysqli_init に失敗しました。');
    }

    $connected = @$mtooldb->real_connect($host, $user, $password, $database, $port);
    if (!$connected || $mtooldb->connect_errno) {
        $message = 'database connect failed: ' . $mtooldb->connect_error;
        connect_error_for_mtooldb($message);
        throw new RuntimeException($message);
    }

    if (!$mtooldb->set_charset('utf8mb4')) {
        $message = 'utf8mb4 character set を設定できませんでした: ' . $mtooldb->error;
        connect_error_for_mtooldb($message);
        throw new RuntimeException($message);
    }
}

function reconnect_mtooldb_if_necessary(): void
{
    global $mtooldb;
    global $time_for_reconnect_mtooldb_if_necessary;

    if (!$mtooldb instanceof mysqli) {
        return;
    }

    if (abs(time() - $time_for_reconnect_mtooldb_if_necessary) <= 10) {
        return;
    }

    @$mtooldb->ping();
    $time_for_reconnect_mtooldb_if_necessary = time();
}

require_once __DIR__ . '/data-AuthTask.php';
require_once __DIR__ . '/dbaccess-AuthTask.php';
