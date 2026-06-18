<?php

declare(strict_types=1);

require_once __DIR__ . '/user_db_contract.php';

function app_user_db_contract_runtime_usage(): string
{
    return <<<'TXT'
usage:
  php mtool/scripts/user_db_contract_runtime_smoke.php --dbaccess-root=PATH --dataclass-root=PATH --dialect=mysql|sqlite --output=PATH [--sample=KEY] [--sqlite-path=PATH] [--pretty]

TXT;
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_parse_options(array $argv): array
{
    $options = [
        'pretty' => false,
        'sample' => 'sample10-dbaccess-mini-crud-flow',
    ];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--pretty') {
            $options['pretty'] = true;
            continue;
        }

        if (str_starts_with($argument, '--') && str_contains($argument, '=')) {
            [$key, $value] = explode('=', substr($argument, 2), 2);
            $options[$key] = $value;
            continue;
        }

        throw new InvalidArgumentException('unsupported argument: ' . $argument);
    }

    return $options;
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_sample_definition(string $sample): array
{
    if ($sample === 'sample10-dbaccess-mini-crud-flow') {
        return app_user_db_contract_runtime_sample10_definition();
    }

    if ($sample === 'sample06-dbaccess-filter-sort-page') {
        return app_user_db_contract_runtime_sample06_definition();
    }

    if ($sample === 'sample08-dbaccess-join-read-model') {
        return app_user_db_contract_runtime_sample08_definition();
    }

    if ($sample === 'sample09-dbaccess-aggregate-report') {
        return app_user_db_contract_runtime_sample09_definition();
    }

    throw new InvalidArgumentException('unsupported runtime contract sample: ' . $sample);
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_sample06_definition(): array
{
    return [
        'sample' => 'sample06-dbaccess-filter-sort-page',
        'dataclass_files' => [
            'data-Announcement.php',
        ],
        'dbaccess_files' => [
            'dbaccess-Announcement.php',
        ],
        'fixture_sql' => [
            'mysql' => [
                'DROP TABLE IF EXISTS Announcement',
                "CREATE TABLE Announcement (
                    Id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    Title VARCHAR(255) NOT NULL,
                    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    PublishedAt DATETIME NULL
                )",
                "INSERT INTO Announcement (Id, Title, Status, PublishedAt) VALUES
                    (1, 'Welcome Release', 'published', '2026-05-20 09:00:00'),
                    (2, 'Planned Maintenance', 'draft', NULL),
                    (3, 'May Newsletter', 'published', '2026-05-22 08:30:00'),
                    (4, 'June Newsletter', 'published', '2026-06-01 07:30:00')",
            ],
            'sqlite' => [
                'DROP TABLE IF EXISTS Announcement',
                "CREATE TABLE Announcement (
                    Id INTEGER PRIMARY KEY AUTOINCREMENT,
                    Title TEXT NOT NULL,
                    Status TEXT NOT NULL DEFAULT 'draft',
                    PublishedAt TEXT NULL
                )",
                "INSERT INTO Announcement (Id, Title, Status, PublishedAt) VALUES
                    (1, 'Welcome Release', 'published', '2026-05-20 09:00:00'),
                    (2, 'Planned Maintenance', 'draft', NULL),
                    (3, 'May Newsletter', 'published', '2026-05-22 08:30:00'),
                    (4, 'June Newsletter', 'published', '2026-06-01 07:30:00')",
            ],
        ],
        'runner' => 'app_user_db_contract_runtime_run_sample06',
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_sample08_definition(): array
{
    return [
        'sample' => 'sample08-dbaccess-join-read-model',
        'dataclass_files' => [
            'data-BlogAuthor.php',
            'data-BlogPost.php',
            'data-BlogPostAuthorSummary.php',
        ],
        'dbaccess_files' => [
            'dbaccess-BlogPost.php',
        ],
        'fixture_sql' => [
            'mysql' => [
                'DROP TABLE IF EXISTS BlogPostAuthorSummary',
                'DROP TABLE IF EXISTS BlogPost',
                'DROP TABLE IF EXISTS BlogAuthor',
                "CREATE TABLE BlogAuthor (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    Name VARCHAR(255) NOT NULL,
                    IsActive TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (Id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "CREATE TABLE BlogPost (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    BlogAuthorId BIGINT UNSIGNED NOT NULL,
                    Title VARCHAR(255) NOT NULL,
                    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    PRIMARY KEY (Id),
                    KEY idx_blog_post_author_id (BlogAuthorId),
                    CONSTRAINT fk_blog_post_author
                        FOREIGN KEY (BlogAuthorId)
                        REFERENCES BlogAuthor (Id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "CREATE TABLE BlogPostAuthorSummary (
                    BlogPostId BIGINT UNSIGNED NOT NULL,
                    BlogPostTitle VARCHAR(255) NOT NULL,
                    BlogAuthorId BIGINT UNSIGNED NOT NULL,
                    BlogAuthorName VARCHAR(255) NOT NULL,
                    PRIMARY KEY (BlogPostId)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "INSERT INTO BlogAuthor (Id, Name, IsActive) VALUES
                    (1, 'Alice Editor', 1),
                    (2, 'Bob Archived', 0),
                    (3, 'Carol Writer', 1)",
                "INSERT INTO BlogPost (Id, BlogAuthorId, Title, Status) VALUES
                    (1, 1, 'Canonical Join Tutorial', 'published'),
                    (2, 2, 'Inactive Author Should Not Appear', 'published'),
                    (3, 3, 'Draft Posts Stay Hidden', 'draft'),
                    (4, 3, 'Roadmap Checkpoint', 'published')",
            ],
            'sqlite' => [
                'DROP TABLE IF EXISTS BlogPostAuthorSummary',
                'DROP TABLE IF EXISTS BlogPost',
                'DROP TABLE IF EXISTS BlogAuthor',
                "CREATE TABLE BlogAuthor (
                    Id INTEGER PRIMARY KEY AUTOINCREMENT,
                    Name TEXT NOT NULL,
                    IsActive INTEGER NOT NULL DEFAULT 1
                )",
                "CREATE TABLE BlogPost (
                    Id INTEGER PRIMARY KEY AUTOINCREMENT,
                    BlogAuthorId INTEGER NOT NULL,
                    Title TEXT NOT NULL,
                    Status TEXT NOT NULL DEFAULT 'draft',
                    FOREIGN KEY (BlogAuthorId) REFERENCES BlogAuthor (Id)
                )",
                "CREATE TABLE BlogPostAuthorSummary (
                    BlogPostId INTEGER NOT NULL PRIMARY KEY,
                    BlogPostTitle TEXT NOT NULL,
                    BlogAuthorId INTEGER NOT NULL,
                    BlogAuthorName TEXT NOT NULL
                )",
                "INSERT INTO BlogAuthor (Id, Name, IsActive) VALUES
                    (1, 'Alice Editor', 1),
                    (2, 'Bob Archived', 0),
                    (3, 'Carol Writer', 1)",
                "INSERT INTO BlogPost (Id, BlogAuthorId, Title, Status) VALUES
                    (1, 1, 'Canonical Join Tutorial', 'published'),
                    (2, 2, 'Inactive Author Should Not Appear', 'published'),
                    (3, 3, 'Draft Posts Stay Hidden', 'draft'),
                    (4, 3, 'Roadmap Checkpoint', 'published')",
            ],
        ],
        'runner' => 'app_user_db_contract_runtime_run_sample08',
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_sample09_definition(): array
{
    return [
        'sample' => 'sample09-dbaccess-aggregate-report',
        'dataclass_files' => [
            'data-SalesCategory.php',
            'data-SalesRecord.php',
            'data-SalesCategoryReport.php',
        ],
        'dbaccess_files' => [
            'dbaccess-SalesRecord.php',
        ],
        'fixture_sql' => [
            'mysql' => [
                'DROP TABLE IF EXISTS SalesCategoryReport',
                'DROP TABLE IF EXISTS SalesRecord',
                'DROP TABLE IF EXISTS SalesCategory',
                "CREATE TABLE SalesCategory (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    Name VARCHAR(255) NOT NULL,
                    IsActive TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (Id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "CREATE TABLE SalesRecord (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    SalesCategoryId BIGINT UNSIGNED NOT NULL,
                    Title VARCHAR(255) NOT NULL,
                    Status VARCHAR(20) NOT NULL DEFAULT 'open',
                    Amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    PRIMARY KEY (Id),
                    KEY idx_sales_record_category_id (SalesCategoryId),
                    CONSTRAINT fk_sales_record_category
                        FOREIGN KEY (SalesCategoryId)
                        REFERENCES SalesCategory (Id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "CREATE TABLE SalesCategoryReport (
                    SalesCategoryId BIGINT UNSIGNED NOT NULL,
                    SalesCategoryName VARCHAR(255) NOT NULL,
                    ClosedSaleCount INT UNSIGNED NOT NULL,
                    ClosedSaleTotalAmount DECIMAL(10,2) NOT NULL,
                    PRIMARY KEY (SalesCategoryId)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "INSERT INTO SalesCategory (Id, Name, IsActive) VALUES
                    (1, 'Hardware', 1),
                    (2, 'Software', 1),
                    (3, 'Legacy', 0)",
                "INSERT INTO SalesRecord (Id, SalesCategoryId, Title, Status, Amount) VALUES
                    (1, 1, 'Keyboard refresh', 'closed', 120.00),
                    (2, 1, 'Mouse replacement', 'closed', 80.00),
                    (3, 2, 'License renewal', 'closed', 40.00),
                    (4, 2, 'Future subscription', 'open', 500.00),
                    (5, 3, 'Unsupported backlog', 'closed', 999.00)",
            ],
            'sqlite' => [
                'DROP TABLE IF EXISTS SalesCategoryReport',
                'DROP TABLE IF EXISTS SalesRecord',
                'DROP TABLE IF EXISTS SalesCategory',
                "CREATE TABLE SalesCategory (
                    Id INTEGER PRIMARY KEY AUTOINCREMENT,
                    Name TEXT NOT NULL,
                    IsActive INTEGER NOT NULL DEFAULT 1
                )",
                "CREATE TABLE SalesRecord (
                    Id INTEGER PRIMARY KEY AUTOINCREMENT,
                    SalesCategoryId INTEGER NOT NULL,
                    Title TEXT NOT NULL,
                    Status TEXT NOT NULL DEFAULT 'open',
                    Amount NUMERIC NOT NULL DEFAULT 0.00,
                    FOREIGN KEY (SalesCategoryId) REFERENCES SalesCategory (Id)
                )",
                "CREATE TABLE SalesCategoryReport (
                    SalesCategoryId INTEGER NOT NULL PRIMARY KEY,
                    SalesCategoryName TEXT NOT NULL,
                    ClosedSaleCount INTEGER NOT NULL,
                    ClosedSaleTotalAmount NUMERIC NOT NULL
                )",
                "INSERT INTO SalesCategory (Id, Name, IsActive) VALUES
                    (1, 'Hardware', 1),
                    (2, 'Software', 1),
                    (3, 'Legacy', 0)",
                "INSERT INTO SalesRecord (Id, SalesCategoryId, Title, Status, Amount) VALUES
                    (1, 1, 'Keyboard refresh', 'closed', 120.00),
                    (2, 1, 'Mouse replacement', 'closed', 80.00),
                    (3, 2, 'License renewal', 'closed', 40.00),
                    (4, 2, 'Future subscription', 'open', 500.00),
                    (5, 3, 'Unsupported backlog', 'closed', 999.00)",
            ],
        ],
        'runner' => 'app_user_db_contract_runtime_run_sample09',
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_sample10_definition(): array
{
    return [
        'sample' => 'sample10-dbaccess-mini-crud-flow',
        'dataclass_files' => [
            'data-SupportTicket.php',
        ],
        'dbaccess_files' => [
            'dbaccess-SupportTicket.php',
        ],
        'fixture_sql' => [
            'mysql' => [
                'DROP TABLE IF EXISTS SupportTicket',
                "CREATE TABLE SupportTicket (
                    Id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    Title VARCHAR(255) NOT NULL,
                    Status VARCHAR(32) NOT NULL,
                    AssignedTo VARCHAR(255) NULL,
                    Body TEXT NULL,
                    UpdatedAt DATETIME NOT NULL
                )",
                "INSERT INTO SupportTicket (Id, Title, Status, AssignedTo, Body, UpdatedAt) VALUES
                    (1, 'First open ticket', 'open', 'alice', 'First body', '2026-06-17 09:00:00'),
                    (2, 'Second open ticket', 'open', 'bob', 'Second body', '2026-06-17 10:00:00'),
                    (3, 'Closed ticket', 'closed', 'carol', 'Closed body', '2026-06-16 09:00:00')",
            ],
            'sqlite' => [
                'DROP TABLE IF EXISTS SupportTicket',
                "CREATE TABLE SupportTicket (
                    Id INTEGER PRIMARY KEY AUTOINCREMENT,
                    Title TEXT NOT NULL,
                    Status TEXT NOT NULL,
                    AssignedTo TEXT NULL,
                    Body TEXT NULL,
                    UpdatedAt TEXT NOT NULL
                )",
                "INSERT INTO SupportTicket (Id, Title, Status, AssignedTo, Body, UpdatedAt) VALUES
                    (1, 'First open ticket', 'open', 'alice', 'First body', '2026-06-17 09:00:00'),
                    (2, 'Second open ticket', 'open', 'bob', 'Second body', '2026-06-17 10:00:00'),
                    (3, 'Closed ticket', 'closed', 'carol', 'Closed body', '2026-06-16 09:00:00')",
            ],
        ],
        'runner' => 'app_user_db_contract_runtime_run_sample10',
    ];
}

function app_user_db_contract_runtime_prepare_mysql_fixture(array $definition): void
{
    $host = (string) (getenv('MTOOL_RUNTIME_DB_HOST') ?: 'db-lab');
    $user = (string) (getenv('MTOOL_RUNTIME_DB_USER') ?: 'lab_app');
    $password = (string) getenv('MTOOL_RUNTIME_DB_PASSWORD');
    $database = (string) (getenv('MTOOL_RUNTIME_DB_NAME') ?: 'lab_app');
    $port = (int) (getenv('MTOOL_RUNTIME_DB_PORT') ?: 3306);

    $db = new mysqli($host, $user, $password, $database, $port);
    if ($db->connect_errno !== 0) {
        throw new RuntimeException('mysql fixture connection failed: ' . $db->connect_error);
    }

    foreach (app_user_db_contract_runtime_fixture_sql($definition, 'mysql') as $sql) {
        if (!$db->query($sql)) {
            throw new RuntimeException('mysql fixture SQL failed: ' . $db->error . ' sql=' . $sql);
        }
    }
}

function app_user_db_contract_runtime_prepare_sqlite_fixture(array $definition, string $sqlitePath): void
{
    $parentDir = dirname($sqlitePath);
    if ($parentDir !== '' && $parentDir !== '.' && !is_dir($parentDir)) {
        mkdir($parentDir, 0775, true);
    }

    if (is_file($sqlitePath)) {
        unlink($sqlitePath);
    }

    $db = new PDO('sqlite:' . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    foreach (app_user_db_contract_runtime_fixture_sql($definition, 'sqlite') as $sql) {
        $db->exec($sql);
    }
}

/**
 * @return list<string>
 */
function app_user_db_contract_runtime_fixture_sql(array $definition, string $dialect): array
{
    $sql = $definition['fixture_sql'][$dialect] ?? null;
    if (!is_array($sql)) {
        throw new RuntimeException('fixture SQL not defined for dialect: ' . $dialect);
    }

    return array_values(array_map('strval', $sql));
}

function app_user_db_contract_runtime_require_sample_files(
    array $definition,
    string $dbaccessRoot,
    string $dataclassRoot,
): void {
    foreach (($definition['dataclass_files'] ?? []) as $file) {
        require_once rtrim($dataclassRoot, '/') . '/' . (string) $file;
    }

    foreach (($definition['dbaccess_files'] ?? []) as $file) {
        require_once rtrim($dbaccessRoot, '/') . '/' . (string) $file;
    }
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_run_sample(
    array $definition,
    string $dbaccessRoot,
    string $dataclassRoot,
): array {
    app_user_db_contract_runtime_require_sample_files($definition, $dbaccessRoot, $dataclassRoot);

    $runner = (string) ($definition['runner'] ?? '');
    if ($runner === '' || !function_exists($runner)) {
        throw new RuntimeException('runtime contract runner not found: ' . $runner);
    }

    return $runner();
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_run_sample06(): array
{
    $access = new AnnouncementDBAccess();

    return [
        'operations' => [
            'published_limit_10' => app_user_db_contract_runtime_list_summary(
                $access->GetAnnouncementList('published', 10),
            ),
            'published_limit_1' => app_user_db_contract_runtime_list_summary(
                $access->GetAnnouncementList('published', 1),
            ),
            'draft_limit_10' => app_user_db_contract_runtime_list_summary(
                $access->GetAnnouncementList('draft', 10),
            ),
            'missing_status' => app_user_db_contract_runtime_list_summary(
                $access->GetAnnouncementList('archived', 10),
            ),
        ],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_run_sample08(): array
{
    $access = new BlogPostDBAccess();

    return [
        'operations' => [
            'published_active_author_summary' => app_user_db_contract_runtime_list_summary(
                $access->GetPublishedBlogPostAuthorSummaryList(),
            ),
        ],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_run_sample09(): array
{
    $access = new SalesRecordDBAccess();

    return [
        'operations' => [
            'closed_sales_category_report' => app_user_db_contract_runtime_list_summary(
                $access->GetClosedSalesCategoryReportList(),
            ),
        ],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_run_sample10(): array
{
    $access = new SupportTicketDBAccess();

    $listBefore = $access->GetSupportTicketList('open', 10);
    $detailBefore = $access->GetSupportTicket(1);

    $newTicket = new SupportTicketData();
    $newTicket->Title = 'New runtime ticket';
    $newTicket->Status = 'open';
    $newTicket->AssignedTo = 'dana';
    $newTicket->Body = 'New body';
    $newTicket->UpdatedAt = '2026-06-17 12:00:00';
    $insertResult = $access->InsertSupportTicket($newTicket);

    $updateTicket = new SupportTicketData();
    $updateTicket->Id = 2;
    $updateTicket->Title = 'Second ticket updated';
    $updateTicket->Status = 'closed';
    $updateTicket->AssignedTo = 'erin';
    $updateTicket->Body = 'Updated body';
    $updateTicket->UpdatedAt = '2026-06-17 13:00:00';
    $updateResult = $access->UpdateSupportTicket($updateTicket);
    $detailAfterUpdate = $access->GetSupportTicket(2);

    $deleteTicket = new SupportTicketData();
    $deleteTicket->Id = 3;
    $deleteResult = $access->DeleteSupportTicket($deleteTicket);
    $detailAfterDelete = $access->GetSupportTicket(3);

    $listAfter = $access->GetSupportTicketList('open', 10);

    return [
        'operations' => [
            'list_before' => app_user_db_contract_runtime_list_summary($listBefore),
            'detail_before' => app_user_db_contract_runtime_record_summary($detailBefore),
            'insert' => app_user_db_contract_runtime_write_summary($insertResult),
            'update' => app_user_db_contract_runtime_write_summary($updateResult),
            'detail_after_update' => app_user_db_contract_runtime_record_summary($detailAfterUpdate),
            'delete' => app_user_db_contract_runtime_write_summary($deleteResult),
            'detail_after_delete' => app_user_db_contract_runtime_record_summary($detailAfterDelete),
            'list_after' => app_user_db_contract_runtime_list_summary($listAfter),
        ],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_result(
    array $definition,
    string $dbaccessRoot,
    string $dataclassRoot,
    string $dialect,
): array {
    $result = app_user_db_contract_runtime_run_sample($definition, $dbaccessRoot, $dataclassRoot);

    return [
        'schema' => 'user-db-contract-runtime-v1',
        'sample' => (string) ($definition['sample'] ?? ''),
        'dialect' => $dialect,
        'operations' => $result['operations'] ?? [],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_list_summary(mixed $list): array
{
    if (!is_array($list)) {
        return [
            'ok' => false,
            'count' => 0,
            'records' => [],
        ];
    }

    return [
        'ok' => true,
        'count' => count($list),
        'records' => array_map('app_user_db_contract_runtime_object_summary', $list),
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_record_summary(mixed $record): array
{
    if (!is_object($record)) {
        return [
            'found' => false,
        ];
    }

    return [
        'found' => true,
        'record' => app_user_db_contract_runtime_object_summary($record),
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_object_summary(mixed $record): array
{
    if (!is_object($record)) {
        return [];
    }

    $values = get_object_vars($record);
    ksort($values, SORT_STRING);

    return array_map('app_user_db_contract_runtime_normalize_value', $values);
}

function app_user_db_contract_runtime_normalize_value(mixed $value): mixed
{
    if (is_string($value) && preg_match('/^-?\d+\.\d+$/', $value) === 1) {
        $normalized = rtrim(rtrim($value, '0'), '.');
        if ($normalized === '-0') {
            $normalized = '0';
        }
        if (preg_match('/^-?\d+$/', $normalized) === 1) {
            return (int) $normalized;
        }

        return (float) $normalized;
    }

    return $value;
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_runtime_write_summary(mixed $result): array
{
    return [
        'ok' => $result !== false,
    ];
}
