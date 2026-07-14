#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Focused sample21 Firebird DBAccess smoke.
 *
 * Proves the generated sample21 ebook catalog read-model DBAccess can read a
 * relationship-backed catalog shape from a Firebird profile without changing
 * generated classes.
 *
 * @param list<string> $argv
 * @return array{help:bool,dsn:string,user:string,password:string,pretty:bool,error:string}
 */
function app_cli_sample21_firebird_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'dsn' => trim((string) getenv('MTOOL_FIREBIRD_DSN')),
        'user' => trim((string) (getenv('MTOOL_FIREBIRD_USER') ?: '')),
        'password' => (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: ''),
        'pretty' => false,
        'error' => '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;
            continue;
        }
        if ($argument === '--pretty') {
            $parsed['pretty'] = true;
            continue;
        }
        if (str_starts_with($argument, '--dsn=')) {
            $parsed['dsn'] = trim(substr($argument, strlen('--dsn=')));
            continue;
        }
        if (str_starts_with($argument, '--user=')) {
            $parsed['user'] = trim(substr($argument, strlen('--user=')));
            continue;
        }
        if (str_starts_with($argument, '--password=')) {
            $parsed['password'] = substr($argument, strlen('--password='));
            continue;
        }

        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_sample21_firebird_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample21_firebird_dbaccess_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN
  MTOOL_FIREBIRD_USER
  MTOOL_FIREBIRD_PASSWORD

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Uses the generated sample21 DBAccess classes unchanged.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_sample21_firebird_smoke_result(
    bool $ok,
    string $stage,
    string $error,
    array $details = [],
    bool $mutationPerformed = false,
): array {
    return [
        'ok' => $ok,
        'stage' => $stage,
        'error' => $error,
        'mutation_performed' => $mutationPerformed,
        'details' => $details,
    ];
}

/** @return list<string> */
function app_sample21_firebird_drop_tables(): array
{
    return [
        'EBOOK_CATALOG_ITEM',
        'EBOOK_BOOK_GENRE',
        'EBOOK_BOOK_AUTHOR',
        'EBOOK_BOOK',
        'EBOOK_GENRE',
        'EBOOK_AUTHOR',
        'EBOOK_SERIES',
    ];
}

function app_sample21_firebird_create_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE EBOOK_SERIES (
            ID INTEGER NOT NULL PRIMARY KEY,
            NAME VARCHAR(160) NOT NULL,
            SLUG VARCHAR(160) NOT NULL UNIQUE
        )'
    );
    $pdo->exec(
        'CREATE TABLE EBOOK_AUTHOR (
            ID INTEGER NOT NULL PRIMARY KEY,
            NAME VARCHAR(160) NOT NULL,
            SLUG VARCHAR(160) NOT NULL UNIQUE
        )'
    );
    $pdo->exec(
        'CREATE TABLE EBOOK_GENRE (
            ID INTEGER NOT NULL PRIMARY KEY,
            NAME VARCHAR(120) NOT NULL,
            SLUG VARCHAR(120) NOT NULL UNIQUE
        )'
    );
    $pdo->exec(
        'CREATE TABLE EBOOK_BOOK (
            ID INTEGER NOT NULL PRIMARY KEY,
            EBOOK_SERIES_ID INTEGER,
            TITLE VARCHAR(255) NOT NULL,
            SLUG VARCHAR(180) NOT NULL UNIQUE,
            STATUS VARCHAR(32) NOT NULL,
            PUBLISHED_AT TIMESTAMP,
            SUMMARY BLOB SUB_TYPE TEXT,
            EPUB_STATUS VARCHAR(32) NOT NULL,
            PRIMARY_EPUB_URL VARCHAR(500) NOT NULL,
            UPDATED_AT TIMESTAMP NOT NULL,
            CONSTRAINT FK_SAMPLE21_BOOK_SERIES FOREIGN KEY (EBOOK_SERIES_ID) REFERENCES EBOOK_SERIES (ID)
        )'
    );
    $pdo->exec(
        'CREATE TABLE EBOOK_BOOK_AUTHOR (
            ID INTEGER NOT NULL PRIMARY KEY,
            EBOOK_BOOK_ID INTEGER NOT NULL,
            EBOOK_AUTHOR_ID INTEGER NOT NULL,
            DISPLAY_ORDER INTEGER NOT NULL,
            CONSTRAINT FK_SAMPLE21_BOOK_AUTHOR_BOOK FOREIGN KEY (EBOOK_BOOK_ID) REFERENCES EBOOK_BOOK (ID),
            CONSTRAINT FK_SAMPLE21_BOOK_AUTHOR_AUTHOR FOREIGN KEY (EBOOK_AUTHOR_ID) REFERENCES EBOOK_AUTHOR (ID)
        )'
    );
    $pdo->exec(
        'CREATE TABLE EBOOK_BOOK_GENRE (
            ID INTEGER NOT NULL PRIMARY KEY,
            EBOOK_BOOK_ID INTEGER NOT NULL,
            EBOOK_GENRE_ID INTEGER NOT NULL,
            CONSTRAINT FK_SAMPLE21_BOOK_GENRE_BOOK FOREIGN KEY (EBOOK_BOOK_ID) REFERENCES EBOOK_BOOK (ID),
            CONSTRAINT FK_SAMPLE21_BOOK_GENRE_GENRE FOREIGN KEY (EBOOK_GENRE_ID) REFERENCES EBOOK_GENRE (ID)
        )'
    );
    $pdo->exec(
        'CREATE TABLE EBOOK_CATALOG_ITEM (
            BOOK_ID INTEGER NOT NULL PRIMARY KEY,
            BOOK_TITLE VARCHAR(255) NOT NULL,
            BOOK_SLUG VARCHAR(180) NOT NULL,
            STATUS VARCHAR(32) NOT NULL,
            SERIES_NAME VARCHAR(160) NOT NULL,
            SERIES_SLUG VARCHAR(160) NOT NULL,
            AUTHOR_NAME VARCHAR(160) NOT NULL,
            AUTHOR_SLUG VARCHAR(160) NOT NULL,
            GENRE_NAME VARCHAR(120) NOT NULL,
            GENRE_SLUG VARCHAR(120) NOT NULL,
            PUBLISHED_AT TIMESTAMP,
            SUMMARY BLOB SUB_TYPE TEXT,
            EPUB_STATUS VARCHAR(32) NOT NULL,
            PRIMARY_EPUB_URL VARCHAR(500) NOT NULL
        )'
    );
}

function app_sample21_firebird_seed(PDO $pdo): void
{
    $seriesInsert = $pdo->prepare('INSERT INTO EBOOK_SERIES (ID, NAME, SLUG) VALUES (?, ?, ?)');
    $seriesInsert->execute([1, 'Starter Series', 'starter-series']);
    $seriesInsert->execute([2, 'Archive Series', 'archive-series']);

    $authorInsert = $pdo->prepare('INSERT INTO EBOOK_AUTHOR (ID, NAME, SLUG) VALUES (?, ?, ?)');
    $authorInsert->execute([1, 'Aki Author', 'aki-author']);
    $authorInsert->execute([2, 'Mika Writer', 'mika-writer']);

    $genreInsert = $pdo->prepare('INSERT INTO EBOOK_GENRE (ID, NAME, SLUG) VALUES (?, ?, ?)');
    $genreInsert->execute([1, 'Guides', 'guides']);
    $genreInsert->execute([2, 'References', 'references']);

    $bookInsert = $pdo->prepare(
        'INSERT INTO EBOOK_BOOK (
            ID, EBOOK_SERIES_ID, TITLE, SLUG, STATUS, PUBLISHED_AT, SUMMARY, EPUB_STATUS, PRIMARY_EPUB_URL, UPDATED_AT
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $bookInsert->execute([1, 1, 'First Ebook', 'first-ebook', 'published', '2026-06-21 09:00:00', 'First Firebird catalog summary', 'available', '/ebooks/first.epub', '2026-06-21 09:30:00']);
    $bookInsert->execute([2, 1, 'Second Ebook', 'second-ebook', 'published', '2026-06-22 09:00:00', 'Second Firebird catalog summary', 'planned', '', '2026-06-22 09:30:00']);
    $bookInsert->execute([3, 2, 'Draft Ebook', 'draft-ebook', 'draft', null, 'Draft summary should not be public', 'none', '', '2026-06-23 09:30:00']);

    $bookAuthorInsert = $pdo->prepare('INSERT INTO EBOOK_BOOK_AUTHOR (ID, EBOOK_BOOK_ID, EBOOK_AUTHOR_ID, DISPLAY_ORDER) VALUES (?, ?, ?, ?)');
    $bookAuthorInsert->execute([1, 1, 1, 1]);
    $bookAuthorInsert->execute([2, 2, 1, 1]);
    $bookAuthorInsert->execute([3, 3, 2, 1]);

    $bookGenreInsert = $pdo->prepare('INSERT INTO EBOOK_BOOK_GENRE (ID, EBOOK_BOOK_ID, EBOOK_GENRE_ID) VALUES (?, ?, ?)');
    $bookGenreInsert->execute([1, 1, 1]);
    $bookGenreInsert->execute([2, 2, 1]);
    $bookGenreInsert->execute([3, 3, 2]);

    $catalogInsert = $pdo->prepare(
        'INSERT INTO EBOOK_CATALOG_ITEM (
            BOOK_ID, BOOK_TITLE, BOOK_SLUG, STATUS, SERIES_NAME, SERIES_SLUG, AUTHOR_NAME, AUTHOR_SLUG,
            GENRE_NAME, GENRE_SLUG, PUBLISHED_AT, SUMMARY, EPUB_STATUS, PRIMARY_EPUB_URL
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $catalogInsert->execute([1, 'First Ebook', 'first-ebook', 'published', 'Starter Series', 'starter-series', 'Aki Author', 'aki-author', 'Guides', 'guides', '2026-06-21 09:00:00', 'First Firebird catalog summary', 'available', '/ebooks/first.epub']);
    $catalogInsert->execute([2, 'Second Ebook', 'second-ebook', 'published', 'Starter Series', 'starter-series', 'Aki Author', 'aki-author', 'Guides', 'guides', '2026-06-22 09:00:00', 'Second Firebird catalog summary', 'planned', '']);
    $catalogInsert->execute([3, 'Draft Ebook', 'draft-ebook', 'draft', 'Archive Series', 'archive-series', 'Mika Writer', 'mika-writer', 'References', 'references', null, 'Draft summary should not be public', 'none', '']);
}

/** @return array<string,mixed> */
function app_sample21_firebird_dbaccess_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample21_firebird_smoke_result(
            false,
            'runtime_preflight',
            'pdo_firebird_extension_missing',
            [
                'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
                'required_driver' => 'firebird',
            ],
        );
    }
    if (trim($dsn) === '') {
        return app_sample21_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    }

    try {
        $pdo = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );

        foreach (app_sample21_firebird_drop_tables() as $tableName) {
            try {
                $pdo->exec('DROP TABLE ' . $tableName);
            } catch (Throwable) {
                // Disposable smoke tables may not exist.
            }
        }

        app_sample21_firebird_create_schema($pdo);
        app_sample21_firebird_seed($pdo);

        $relationshipCounts = [
            'series' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_SERIES')->fetchColumn(),
            'authors' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_AUTHOR')->fetchColumn(),
            'genres' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_GENRE')->fetchColumn(),
            'books' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_BOOK')->fetchColumn(),
            'book_authors' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_BOOK_AUTHOR')->fetchColumn(),
            'book_genres' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_BOOK_GENRE')->fetchColumn(),
            'catalog_items' => (int) $pdo->query('SELECT COUNT(*) FROM EBOOK_CATALOG_ITEM')->fetchColumn(),
        ];

        putenv('MTOOL_RUNTIME_DB_DSN=' . $dsn);
        putenv('MTOOL_RUNTIME_DB_USER=' . $user);
        putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $password);

        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample21-ebook-catalog-api-demo/reference/DATACLASS-PHP/data-EbookCatalogItem.php';
        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample21-ebook-catalog-api-demo/reference/DBACCESS-PHP/dbaccess-EbookCatalogItem.php';

        $dbAccess = new EbookCatalogItemDBAccess();
        $rows = $dbAccess->GetPublicEbookCatalogList('aki-author', 'guides', 'starter-series', '%Ebook%', 10);
        if (!is_array($rows)) {
            return app_sample21_firebird_smoke_result(false, 'catalog_read', 'dbaccess_returned_non_array', [
                'type' => get_debug_type($rows),
            ], true);
        }
        $summary = array_map(
            static fn (object $row): array => [
                'bookId' => (int) ($row->bookId ?? 0),
                'bookTitle' => (string) ($row->bookTitle ?? ''),
                'bookSlug' => (string) ($row->bookSlug ?? ''),
                'seriesName' => (string) ($row->seriesName ?? ''),
                'authorName' => (string) ($row->authorName ?? ''),
                'genreName' => (string) ($row->genreName ?? ''),
                'epubStatus' => (string) ($row->epubStatus ?? ''),
            ],
            $rows,
        );
        $expected = [
            [
                'bookId' => 2,
                'bookTitle' => 'Second Ebook',
                'bookSlug' => 'second-ebook',
                'seriesName' => 'Starter Series',
                'authorName' => 'Aki Author',
                'genreName' => 'Guides',
                'epubStatus' => 'planned',
            ],
            [
                'bookId' => 1,
                'bookTitle' => 'First Ebook',
                'bookSlug' => 'first-ebook',
                'seriesName' => 'Starter Series',
                'authorName' => 'Aki Author',
                'genreName' => 'Guides',
                'epubStatus' => 'available',
            ],
        ];
        if ($summary !== $expected) {
            return app_sample21_firebird_smoke_result(false, 'catalog_read', 'unexpected_catalog_rows', [
                'expected' => $expected,
                'actual' => $summary,
            ], true);
        }

        $detail = $dbAccess->GetPublicEbookBook('first-ebook');
        if (
            !$detail instanceof EbookCatalogItemData
            || (int) $detail->bookId !== 1
            || (string) $detail->primaryEpubUrl !== '/ebooks/first.epub'
        ) {
            return app_sample21_firebird_smoke_result(false, 'book_detail', 'unexpected_book_detail', [
                'type' => get_debug_type($detail),
                'book_id' => is_object($detail) ? (int) ($detail->bookId ?? 0) : null,
                'primary_epub_url' => is_object($detail) ? (string) ($detail->primaryEpubUrl ?? '') : null,
            ], true);
        }

        $missing = $dbAccess->GetPublicEbookBook('missing-ebook');
        if ($missing !== null) {
            return app_sample21_firebird_smoke_result(false, 'missing_book', 'missing_book_returned_record', [
                'type' => get_debug_type($missing),
            ], true);
        }

        return app_sample21_firebird_smoke_result(true, 'ok', '', [
            'sample' => 'sample21-ebook-catalog-api-demo',
            'pdo_driver' => (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'tables' => array_values(app_sample21_firebird_drop_tables()),
            'relationship_counts' => $relationshipCounts,
            'catalog_rows' => $summary,
            'detail_book_slug' => (string) $detail->bookSlug,
            'missing_book' => $missing,
        ], true);
    } catch (Throwable $throwable) {
        return app_sample21_firebird_smoke_result(false, 'connection_or_dbaccess', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
        ], true);
    }
}

$parsed = app_cli_sample21_firebird_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample21_firebird_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample21_firebird_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample21_firebird_dbaccess_smoke(
    $parsed['dsn'],
    $parsed['user'],
    $parsed['password'],
);

fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0),
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);
